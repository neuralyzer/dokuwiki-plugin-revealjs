<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_header extends DokuWiki_Syntax_Plugin {

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
        /* We want to override the DokuWiki default header handling in cases, where
        our Reveal.js plugin is active. Therefore we reuse the header pattern from
        DokuWiki with a lower sort number to be able to fetch the headers before DokuWiki.
        See also /inc/parser/parser.php around line 282 */
        $this->Lexer->addSpecialPattern('[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)', $mode, 'plugin_revealjs_header');
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
        /* We reuse and adapt here the default DokuWiki header handler code. See also
        /inc/parser/handler.php around line 97. */

        // get level and title
        $title = trim($match);
        $level = 7 - strspn($title,'=');
        if($level < 1) $level = 1;
        $title = trim($title,'= ');
        if ($this->getConf('revealjs_active') || $_GET['do']=='export_revealjs') {
            /* We are now on a reveal.js activated page and we want to do our
            own section handling to be able to get all relevant content from
            one slide into one edit section. Since sections are header driven,
            we have to do also our own header handling.
            Because we are in a plugin context, we are automatically called and
            do our work in the render function (see below).*/
            // pass
        }
        else {
            /* This is the DokuWiki default header/section handling, we use it on NON Reveal.js
            activated pages (keyword ~~REVEAL~~ not found on page). See also theme.php */
            $handler->_addCall('header', array($title, $level, $pos), $pos);
        }
        return array($title, $level, $pos);
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
            list($text, $level, $pos) = $data;
            $horizontal_slide_level = $this->getConf('horizontal_slide_level');
            /* examples:
            horizontal_slide_level=2 ==> headers level 1-3 open slides, headers 4-6 are only content on slides
            horizontal_slide_level=1 ==> headers level 1-2 open slides, headers 3-6 are only content on slides */

            // rendering the slideshow
            if (is_a($renderer, 'renderer_plugin_revealjs')){
                // slide indicator: special horizontal rules ---- section orange zoom slow no-footer ---->
                if (!$renderer->slide_indicator_headers) {
                    $renderer->doc .= '<h'. $level .'>';
                    $renderer->doc .= $renderer->_xmlEntities($text);
                    $renderer->doc .= '</h'. $level .'>'.DOKU_LF;
                }
                // slide indicator: headers
                else {
                    // check, if we have to open a slide
                    if ($level <= $horizontal_slide_level + 1) {
                        // check, if we have to open a column
                        if ($level <= $horizontal_slide_level) {
                            $renderer->open_slide_container();
                        }
                        $renderer->open_slide();
                    }
                    $level_calculated = ($level > $horizontal_slide_level && $this->getConf('enlarge_vertical_slide_headers') ? $level - 1 : $level);
                    $renderer->doc .= '<h'. $level_calculated .'>';
                    $renderer->doc .= $renderer->_xmlEntities($text);
                    $renderer->doc .= '</h'. $level_calculated .'>'.DOKU_LF;
                }
            }

            // rendering the normal wiki page
            else if ($this->getConf('revealjs_active') ) {
                /* could be, that {{no-footer}} is used before and we need to align the
                start position definition for the section editing */
                if ($renderer->wikipage_next_slide_no_footer_position > 0) {
                    $pos = $renderer->wikipage_next_slide_no_footer_position;
                    $renderer->wikipage_next_slide_no_footer_position = 0;
                }
                /**
                 * Render a heading (aligned copy of /inc/parser/xhtml.php around line 184)
                 *
                 * @param string $text  the text to display
                 * @param int    $level header level
                 * @param int    $pos   byte position in the original source
                 */
                if (!$text) return; //skip empty headlines
                $hid = $this->_headerToLink($text, $renderer->wikipage_unique_headers);
                //only add items within configured levels
                $renderer->toc_additem($hid, $text, $level);
                // handle section editing
                if ($renderer->wikipage_slide_indicator_headers &&
                    $level <= $horizontal_slide_level + 1 &&
                    $renderer->wikipage_slide_edit_section_open &&
                    !$renderer->wikipage_slide_background_defined) {
                    $renderer->wikipage_slide_edit_section_open = false;
                    $renderer->doc .= DOKU_LF.'</div>'.DOKU_LF;
                    $renderer->finishSectionEdit($pos - 1);
                }
                if ($renderer->wikipage_slide_indicator_headers &&
                    $level <= $horizontal_slide_level + 1 &&
                    !$renderer->wikipage_slide_edit_section_open) {
                    $renderer->wikipage_slide_number += 1;
                    
		    $sectionEditStartData = ['target' => 'section'];
		    if (!defined('SEC_EDIT_PATTERN')) {
			// backwards-compatibility for Frusterick Manners (2017-02-19)
			$sectionEditStartData = 'section';
		    }
		    
		    /* write slide details to page - we need to use a fake header (<h1 style="display:none...) here
                    to force dokuwiki to show correct section edit highlighting by hoovering the edit button */
                    $renderer->doc .= DOKU_LF.DOKU_LF.'<h2 style="display:none;" class="' .
                        $renderer->startSectionEdit($pos, $sectionEditStartData, 'Slide '.$renderer->wikipage_slide_number).'"></h2>' . ($this->getConf('show_slide_details') ?
                        '<div class="slide-details-hr'.($renderer->wikipage_slide_number == 1 ? ' first-slide' : '').'"></div>' .
                        '<div class="slide-details-text">'.($level <= $horizontal_slide_level?'→':'↓') .
                        ' Slide '.$renderer->wikipage_slide_number.($renderer->wikipage_next_slide_no_footer ? ' no-footer' : '').'</div>' : '');
                    // open new edit section
                    $renderer->wikipage_slide_edit_section_open = true;
                    $renderer->doc .= DOKU_LF.'<div class="level2">'.DOKU_LF;
                    $renderer->wikipage_next_slide_no_footer = false;
                }
                // write the header
                $renderer->doc .= DOKU_LF.'<h'.$level.' id="'.$hid.'">'.$renderer->_xmlEntities($text)."</h$level>";
                $renderer->wikipage_slide_background_defined = false;
            }
            return true;
        }
        return false;
    }

    /**
     * Creates a linkid from a headline
     *
     * Local version for this plugin which can work on a string instead of an array.
     * See also /inc/pageutils.php around line 222
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ottmar Gobrecht <ottmar.gobrecht@gmail.com>
     * @param string  $title The headline title
     * @return string
     */
    function _headerToLink($title, &$check) {
        $title = str_replace(array(':','.'),'',cleanID($title));
        $new = ltrim($title,'0123456789_-');
        if(empty($new)){
            $title = 'section'.preg_replace('/[^0-9]+/','',$title); //keep numbers from headline
        }
        else {
            $title = $new;
        }
        // make sure titles are unique
        $count = 1;
        while (strpos($check, '{'.$title.'}')) {
            $title .= $count;
            $count += 1;
        }
        $check .= '{'.$title.'}';
        return $title;
    }

}
