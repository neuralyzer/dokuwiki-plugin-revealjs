<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_revealjs_fragmentblock extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('<\/?(?:no-)?fragment-(?:block|list)\b.*?>', $mode, 'plugin_revealjs_fragmentblock');
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
        list($type, $param1, $param2) = preg_split("/\s+/", substr($match, 1, -1), 3);
        if ($param1) {
            if ($this->_is_valid_style($param1)) $style = $param1;
            elseif (intval($param1) > 0) $index = $param1;
        }
        if ($param2) {
            if (intval($param2) > 0) $index = $param2;
            elseif ($this->_is_valid_style($param2)) $style = $param2;
        }
        return array($type, $style, $index);
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
        if($mode == 'xhtml' && is_a($renderer, 'renderer_plugin_revealjs')) {
            list($type, $style, $index) = $data;
            switch ($type) {
                case 'fragment-block' :
                    $renderer->doc .= DOKU_LF.'<div class="fragment' . ($style ? ' '.$style : '') . '"' .
                        ($index ? ' data-fragment-index="'.$index.'"' : '') . '>';
                    break;
                case '/fragment-block' :
                    $renderer->doc .= '</div>'.DOKU_LF;
                    break;
                case 'fragment-list' :
                    $renderer->fragment_list_open = true;
                    $renderer->fragment_style = $style;
                    break;
                case '/fragment-list' :
                    $renderer->fragment_list_open = false;
                    $renderer->fragment_style = '';
                    break;
                case 'no-fragment-list' :
                    $renderer->no_fragment_list_open = true;
                    break;
                case '/no-fragment-list' :
                    $renderer->no_fragment_list_open = false;
                    break;
            }
            return true;
        }
        return false;
    }


    /**
     * Validate fragment style: $style
     */
    private function _is_valid_style($style) {
        $pattern = '/^(?:grow|shrink|fade-(?:in|out|up|down|left|right)|current-visible|highlight(?:-current)?-(?:red|green|blue))$/';
        if (preg_match($pattern, $style)) return $style;
        return '';
    }

}
