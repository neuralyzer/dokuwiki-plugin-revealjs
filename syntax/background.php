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


    /**
     * Connect lookup pattern to lexer.
     *
     * @param $aMode String The desired rendermode.
     * @return none
     * @public
     * @see render()
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{background>.+?}}', $mode, 'plugin_revealjs_background');
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
        $content = substr($match, 13, -2); // strip markup
        return array($content);
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
        if($mode == 'xhtml'){
            $is_color = substr($data[0], 0, 1) === '#';
            $background_data = $is_color ? $data[0] : ml($data[0]);
            if (is_a($renderer, 'renderer_plugin_revealjs')){
                $renderer->add_background_to_next_slide($background_data);
            } else {
                if (!$is_color){  //background is an image
                    $renderer->doc .= 'Background: ';
                    $renderer->doc .= $renderer->_media($data[0], 'Background for next section in reveal.js mode',
                                                    null, 80, 60, null, true);
                } else{
                    //$renderer->doc .= '<div style="background-color: '.$background_data.';">Background: '.$background_data.'</div>';
                    $renderer->doc .= '<div style="background-color: '.$background_data.';"><div style="display: inline; color: white;">Background: '.$background_data.',</div><div style="display: inline; color: black;">Background: '.$background_data.'</div></div>';
                }
            }
            return true;
        }
        return false;
    }
}