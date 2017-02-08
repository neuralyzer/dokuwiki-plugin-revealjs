<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_theme extends DokuWiki_Syntax_Plugin {

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
         $this->Lexer->addSpecialPattern('~~REVEAL[^~]*~~',$mode,'plugin_revealjs_theme');
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
        $data = array();
        /* Merge options from URL params into conf. URL params should not be
        overwritten here, we want to be able to change parameters in the
        URL. This is the reason to distinguish between the presentation
        renderer and the page renderer. */
        if ($_GET['do'] === 'export_revealjs') {
            // pass -> merge itself is done in the renderer.php in earlier stage, because needed there
        }
        /* Merge options from page into conf */
        else {
            // parse options
            if ($match !== '~~REVEAL~~') {
                $options = trim(substr($match,8,-2));
                // ensure that only whitespaces do not result in "theme="
                if ($options != '') {
                    // parse multiple options (example: theme=moon&controls=1&build_all_lists=1)
                    if (strpos($options, '=') !== false) {
                        parse_str($options, $data);
                    }
                    // if only one option this must be the theme (backward compatibility)
                    else {
                        $data['theme'] = $options;
                    }
                }
            }
            // merge options (needed in parsing phase for other syntax modules)
            $this->_merge_options_into_conf($data);
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
        global $ID, $conf;
        if($mode == 'xhtml'){
            if (is_a($renderer, 'renderer_plugin_revealjs')){
                // pass
            }
            else {
                /* Merge options again (needed in rendering phase for other syntax modules). Because of
                DokuWikis caching we have no guarantee that the options merge from the parsing phase
                will take place, so we do it here again. */
                $this->_merge_options_into_conf($data);
                // create button to start the presentation
                $target = $this->getConf('open_in_new_window') ? '_blank' : '_self';
                // hide senseless options for the url params to shorten the link
                unset($data['open_in_new_window']);
                unset($data['show_slide_details']);
                unset($data['start_button']);
                // create the link
                $renderer->doc .= '<div class="slide-export-link"><a target="'.$target.'" href="'.
                    exportlink($ID,'revealjs',count($data)?$data:null).'" title="'.
                    $this->getLang('view_presentation').'"><img src="'.DOKU_BASE.'lib/plugins/revealjs/'.
                    $this->getConf('start_button').'" align="right" alt="'.
                    $this->getLang('view_presentation').'"/></a>'.
                    ($this->getConf('user_can_edit') ?
                        '<br><nobr><a target="'.$target.'" href="'.exportlink($ID,'revealjs',count($data)?$data:null).
                        '&print-pdf" title="'.$this->getLang('print_pdf').'">Print PDF</a></nobr>' :
                        '').
                    '</div>';
                /* Prepare vars for own header handling since the needed ones
                are protected and array types - both reasons wy this is not working
                from within a plugin. See also /inc/parser/xhtml.php line 37 */
                $renderer->wikipage_unique_headers = '';
                $renderer->wikipage_slide_edit_section_open = false;
                $renderer->wikipage_slide_indicator_headers = true;
                $renderer->wikipage_slide_background_defined = false;
                $renderer->wikipage_slide_number = 0;
                $renderer->wikipage_next_slide_no_footer = false;
                $renderer->wikipage_next_slide_no_footer_position = 0;
            }
            return true;
        }
        return false;
    }

    /**
     * Merge options from page into plugin conf
     */
    private function _merge_options_into_conf($data) {
        global $ID, $conf;
        // merge options
        if (!array_key_exists('plugin', $conf)) {
            $conf['plugin'] = array('revealjs' => $data);
        }
        elseif (!array_key_exists('revealjs', $conf['plugin'])) {
            $conf['plugin']['revealjs'] = $data;
        }
        else {
            $conf['plugin']['revealjs'] = array_merge($conf['plugin']['revealjs'], $data);
        }
        /* Set state for revealjs and user edit right - needed in other syntax modules and also
        in action plugin for section editing. Sadly this is needed on parse and on render time,
        so we merge the options twice here in the theme.php. */
        $conf['plugin']['revealjs']['revealjs_active'] = true;
        $conf['plugin']['revealjs']['user_can_edit'] = auth_quickaclcheck($ID) >= AUTH_EDIT;
    }
}
