<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_notes extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('<\/?notes>', $mode, 'plugin_revealjs_notes');
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
        return $match;
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
        if($mode == 'xhtml') {
            if (is_a($renderer, 'renderer_plugin_revealjs')) {
                switch ($data) {
                    case '<notes>' :
                        $renderer->doc .= DOKU_LF.'<aside class="notes">';
                        $renderer->notes_open = true;
                        break;
                    case '</notes>' :
                        $renderer->doc .= '</aside>'.DOKU_LF;
                        $renderer->notes_open = false;
                        break;
                }
            }
            else if ($this->getConf('revealjs_active') && $this->getConf('show_slide_details')) {
                switch ($data) {
                    case '<notes>' :
                        $renderer->doc .=
                            '<div class="slide-notes-hr">Notes'.($slide_details_text).'</div>'.DOKU_LF;
                        break;
                }
            }
            return true;
        }
        return false;
    }
}
