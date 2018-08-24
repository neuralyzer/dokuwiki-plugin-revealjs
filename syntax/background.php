<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_background extends DokuWiki_Syntax_Plugin {

    public function getType() { return 'substition'; }
    public function getSort() { return 32; }
    public function getPType() { return 'block'; }


    /**
     * Connect lookup pattern to lexer.
     *
     * @param $aMode String The desired rendermode.
     * @return none
     * @public
     * @see render()
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('----+>>?|----+[^\n]*?----+>>?|<<?----+|{{background>.+?}}', $mode, 'plugin_revealjs_background');
    }


    /**
     * Handler to prepare matched data for the rendering process.
     *
     * @param $aMatch String The text matched by the patterns.
     * @param $aState Integer The lexer state for the match.
     * @param $aPos Integer The character position of the matched text.
     * @param $aHandler Object Reference to the Doku_Handler object.
     * @return Integer The current lexer state for the match.
     * @public
     * @see render()
     * @static
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $transition_count = 0;
        $position_count = 0;
        $data = array();
        $data['position'] = $pos;
        $data['first_chars'] = substr($match, 0, 2);
        $data['last_chars'] = substr($match, -2);
        $params = preg_split("/\s+/",
            ($data['first_chars'] == '{{' ?
                substr($match, 13, -2) :
                trim($match,'-<> ')
            ), 12);
        foreach ($params as $param) {
            if (!$data['background_color'] && $this->_is_valid_color($param)) {
                $data['background_color'] = $param;
            }
            elseif (!$data['background_image'] && $this->_is_valid_image($param)) {
                $data['background_image'] = $param;
            }
            elseif (!$data['background_size'] && $this->_is_valid_size($param)) {
                $data['background_size'] = $param;
            }
            elseif ($position_count == 0 && $this->_is_valid_position($param)) {
                $position_count += 2;
                $data['background_position'] = $param;
            }
            elseif ($position_count < 2 && in_array($param, array('top','bottom','left','right','center'))) {
                $position_count += 1;
                if (!$data['background_position']) $data['background_position'] = $param;
                else $data['background_position'] .= ' '.$param;
            }
            elseif (!$data['background_repeat'] && in_array($param, array('repeat','no-repeat'))) {
                $data['background_repeat'] = $param;
            }
            elseif (!$data['background_transition'] && $this->_is_valid_bg_transition($param)) {
                $data['background_transition'] = $param;
            }
            elseif ($transition_count < 2 && $this->_is_valid_transition($param)) {
                $transition_count += 1;
                if (!$data['transition']) $data['transition'] = $param;
                else $data['transition'] .= ' '.$param;
            }
            elseif (!$data['transition_speed'] && in_array($param, array('default','fast','slow'))) {
                $data['transition_speed'] = $param;
            }
            elseif (!$data['no_footer'] && $param == 'no-footer') {
                $data['no_footer'] = true;
            }
        }
        return $data;
    }

    /**
     * Handle the actual output creation.
     *
     * @param $aFormat String The output format to generate.
     * @param $aRenderer Object A reference to the renderer object.
     * @param $aData Array The data created by the <tt>handle()</tt>
     * method.
     * @return Boolean <tt>TRUE</tt> if rendered successfully, or
     * <tt>FALSE</tt> otherwise.
     * @public
     * @see handle()
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        global $conf;
        if($mode == 'xhtml') {

            // rendering the slideshow
            if (is_a($renderer, 'renderer_plugin_revealjs')){
                $renderer->next_slide_background_color = $data['background_color'];
                $renderer->next_slide_background_image =
                    $data['background_image'] && substr($data['background_image'], 0, 4) == 'http' ?
                    $data['background_image'] :
                    ml($data['background_image']); /*DokuWiki build link to media file*/
                $renderer->next_slide_background_size = $data['background_size'];
                $renderer->next_slide_background_position = str_replace(',', ' ', $data['background_position']); // we replace "," with " ", because we needed this to distinguish between image size and image position
                $renderer->next_slide_background_repeat = $data['background_repeat'];
                $renderer->next_slide_background_transition = substr($data['background_transition'],3); // we cut off "bg-" for Reveal.js (we had "bg-" only to distinguish between background transition and slide transition)
                $renderer->next_slide_transition = $data['transition'];
                $renderer->next_slide_transition_speed = $data['transition_speed'];
                /* could be, that {{no-footer}} is used before a {{background>xxx}} definition and
                $renderer->next_slide_no_footer is already set to true, so we merge here both with a logical or */
                $renderer->next_slide_no_footer = ($data['no_footer'] || $renderer->next_slide_no_footer);
                if ($data['last_chars'] == '->') {
                    $renderer->open_slide();
                    $renderer->slide_indicator_headers = false;
                }
                elseif ($data['last_chars'] == '>>') {
                    $renderer->open_slide_container();
                    $renderer->open_slide();
                    $renderer->slide_indicator_headers = false;
                }
                elseif ($data['first_chars'] == '<-') {
                    $renderer->close_slide();
                    $renderer->slide_indicator_headers = true;
                }
                elseif ($data['first_chars'] == '<<') {
                    $renderer->close_slide_container();
                    $renderer->slide_indicator_headers = true;
                }
            }

            // rendering the normal wiki page
            elseif ($this->getConf('revealjs_active')) {
                /* could be, that {{no-footer}} is used before and we need to align the
                start position definition for the section editing */
                if ($renderer->wikipage_next_slide_no_footer_position > 0) {
                    $data['position'] = $renderer->wikipage_next_slide_no_footer_position;
                    $renderer->wikipage_next_slide_no_footer_position = 0;
                }
                // process slide details view
                if ($data['background_color']) {
                    $slide_details_text .= ' '.$data['background_color'];
                    $slide_details_background .= 'background-color:'.$data['background_color'].';';
                }
                if ($data['background_image']) {
                    $slide_details_text .= ' '.$data['background_image'];
                    $slide_details_background .= 'background-image: url("'.
                        (substr($data['background_image'], 0, 4) == 'http' ?
                            $data['background_image'] :
                            ml($data['background_image'])).
                        '");';
                }
                if ($data['background_size']) {
                    $slide_details_text .= ' '.$data['background_size'];
                    $slide_details_background .= 'background-size:'.$data['background_size'].';';
                }
                if ($data['background_position']) {
                    $slide_details_text .= ' '.$data['background_position'];
                    $slide_details_background .= 'background-position:'.
                        str_replace(',', ' ', $data['background_position']).';';
                }
                if ($data['background_repeat']) {
                    $slide_details_text .= ' '.$data['background_repeat'];
                    $slide_details_background .= 'background-repeat:'.$data['background_repeat'].';';
                }
                if ($data['background_transition']) {
                    $slide_details_text .= ' '.$data['background_transition'];
                }
                if ($data['transition']) {
                    $slide_details_text .= ' '.$data['transition'];
                }
                if ($data['transition_speed']) {
                    $slide_details_text .= ' '.$data['transition_speed'];
                }
                /* could be, that {{no-footer}} is used before a {{background>xxx}} definition and
                $renderer->next_slide_no_footer is already set to true, so we merge here both with a logical or */
                if ($data['no_footer'] || $renderer->wikipage_next_slide_no_footer) {
                    $slide_details_text .= ' no-footer';
                    $renderer->wikipage_next_slide_no_footer = false;
                }
                // handle section editing
                if (in_array($data['last_chars'], array('->','>>','}}'))) {
                    $renderer->wikipage_slide_number += 1;
                    // close edit section, if open
                    if($renderer->wikipage_slide_edit_section_open) {
                        $renderer->wikipage_slide_edit_section_open = false;
                        $renderer->doc .= DOKU_LF.'</div>'.DOKU_LF;
                        $renderer->finishSectionEdit($data['position']- 1);
                    }
                    // calculate slide direction
                    if ($data['last_chars'] == '>>') {
                        $slide_direction = '→';
                    }
                    elseif ($data['last_chars'] == '->') {
                        $slide_direction = '↓';
                    }
                    else {
                        $slide_direction = '';
                        $conf['plugin']['revealjs']['slides_with_unknown_direction'] = true;
                    }
		    
		    $sectionEditStartData = ['target' => 'section'];
		    if (!defined('SEC_EDIT_PATTERN')) {
			// backwards-compatibility for Frusterick Manners (2017-02-19)
			$sectionEditStartData = 'section';
		    }
		    
                    /* write slide details to page - we need to use a fake header (<h1 style="display:none...) here
                    to force dokuwiki to show correct section edit highlighting by hoovering the edit button */
                    $renderer->doc .= DOKU_LF.DOKU_LF.'<h2 style="display:none;" class="' .
                        $renderer->startSectionEdit($data['position'], $sectionEditStartData, 'Slide '.$renderer->wikipage_slide_number).'"></h2>' . ($this->getConf('show_slide_details') ?
                        '<div class="slide-details-hr'.($renderer->wikipage_slide_number == 1 ? ' first-slide' : '').'"></div>' .
                        ($data['background_color'] || $data['background_image'] ?
                            '<div class="slide-details-background" style='."'".$slide_details_background."'".'></div>' :
                            '') .
                        '<div class="slide-details-text'.($slide_direction==''?' fix-my-direction':'').'">'.$slide_direction .
                        ' Slide '.$renderer->wikipage_slide_number.$slide_details_text.'</div>' : '');
                    // open new edit section
                    $renderer->wikipage_slide_edit_section_open = true;
                    $renderer->doc .= DOKU_LF.'<div class="level2">'.DOKU_LF;
                    /* Only the special horizontal row slide indicator changes the
                    indicator mode */
                    if (in_array($data['last_chars'], array('->','>>'))) {
                        $renderer->wikipage_slide_indicator_headers = false;
                    }
                    /* for slide indicator mode "headers" we signaling here the
                    header function that a section is already open */
                    if ($data['last_chars'] == '}}') {
                        $renderer->wikipage_slide_background_defined = true;
                    }
                }
                elseif (in_array($data['first_chars'], array('<-','<<'))) {
                    $renderer->wikipage_slide_indicator_headers = true;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Validate slide transition
     */
    private function _is_valid_transition($val) {
        $pattern = '/^(?:none|fade|slide|convex|concave|zoom)(?:-in|-out)?$/';
        if (preg_match($pattern, $val)) return $val;
        return '';
    }


    /**
     * Validate background transition
     */
    private function _is_valid_bg_transition($val) {
        $pattern = '/^bg-(?:none|fade|slide|convex|concave|zoom)$/';
        if (preg_match($pattern, $val)) return $val;
        return '';
    }


    /**
     * Validate HTML color
     */
    private function _is_valid_color($val) {
        $named = array('aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure', 'beige', 'bisque', 'black', 'blanchedalmond', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'fuchsia', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'grey', 'green', 'greenyellow', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgreen', 'lightgray', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'lime', 'limegreen', 'linen', 'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace', 'olive', 'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'silver', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan', 'teal', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white', 'whitesmoke', 'yellow', 'yellowgreen');

        if (in_array($val, $named)) {
            return $val;
        }
        else {
            $pattern = '/^(#([\da-f]{3}){1,2}|(rgb|hsl)a\((\d{1,3}%?,){3}(1|0?\.\d+)\)|(rgb|hsl)\(\d{1,3}%?(,\d{1,3}%?){2}\))$/';
            if (preg_match($pattern, $val)) return $val;
            return '';
        }
    }

    /**
     * Validate image
     */
    private function _is_valid_image($val) {
        $pattern = '/^.+\.(?:gif|png|jpg|jpeg|svg)$/i';
        if (preg_match($pattern, $val)) return $val;
        return '';
    }

    /**
     * Validate size
     */
    private function _is_valid_size($val) {
        $pattern = '/^\d+(?:px|%)|auto|contain|cover$/';
        if (preg_match($pattern, $val)) return $val;
        return '';
    }

    /**
     * Validate position
     */
    private function _is_valid_position($val) {
        $pattern = '/^\d+(?:px|%),\d+(?:px|%)$/';
        if (preg_match($pattern, $val)) return $val;
        return '';
    }
}
