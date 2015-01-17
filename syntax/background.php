<?php


if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_background extends DokuWiki_Syntax_Plugin {



    /**
     * Get the type of syntax this plugin defines.
     *
     * @param none
     * @return String <tt>'substition'</tt> (i.e. 'substitution').
     * @public
     * @static
     */
    function getType(){
        return 'substition';
    }


    /**
     * Where to sort in?
     *
     * @param none
     * @return Integer <tt>6</tt>.
     * @public
     * @static
     */
    function getSort(){
        return 999;
    }


    /**
     * Connect lookup pattern to lexer.
     *
     * @param $aMode String The desired rendermode.
     * @return none
     * @public
     * @see render()
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern("{{background>.+?}}", $mode,'plugin_revealjs_background');
//      $this->Lexer->addEntryPattern('<TEST>',$mode,'plugin_test');
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
    function handle($match, $state, $pos, &$handler){
        $match = substr($match, 13, -2); // strip markup
        return array($match);
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
    function render($mode, &$renderer, $data) {
        //if($mode == 'xhtml' &&  is_a($renderer, 'renderer_plugin_revealjs')){
        if($mode == 'xhtml'){
            $renderer->doc .= $data[0];            // ptype = 'normal'
            return true;
        }
        return false;
    }
}