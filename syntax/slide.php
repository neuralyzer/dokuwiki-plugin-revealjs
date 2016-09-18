<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_slide extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('-{4,}.*?>', $mode, 'plugin_revealjs_slide');
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
        dbg($match);
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
            $renderer->slide_number += 1;
            if (is_a($renderer, 'renderer_plugin_revealjs')) {
                //$renderer->doc .= DOKU_LF.'<section>';
            }
            else {
                if ($renderer->slide_number > 1 && $this->getConf('show_slide_details')) {
                    $renderer->doc .= DOKU_LF.'<div class="slide-indicator" style="border-top:1px dotted silver;color:silver;font-family: monospace;"><div style="float:left"><sup>Slide '.
                        $renderer->slide_number .'</sup></div><div style="float:right;padding:1px 0 0 0.5em;text-align:right;"><img src="/dokuwiki/lib/exe/fetch.php?w=80&amp;h=60&amp;cache=&amp;tok=5b94b9&amp;media=wiki:dokuwiki-128.png" class="media" title="Background for next section in reveal.js mode" alt="Background for next section in reveal.js mode" style="width:60px;height:45px;margin:0;"><br><sup>Background</sup></div><div style="clear:left;"></div></div>'.DOKU_LF;
                    $renderer->slide_indicator_special_hr = true;
                }
            }
            return true;
        }
        return false;
    }
}
