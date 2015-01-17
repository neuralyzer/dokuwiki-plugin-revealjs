<?php
/**
 * Plugin Now: Inserts a timestamp.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// must be run within DokuWiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_now extends DokuWiki_Syntax_Plugin {

    public function getType() { return 'substition'; }
    public function getSort() { return 32; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[NOW\]',$mode,'plugin_now');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($match, $state, $pos);
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        // $data is what the function handle return'ed.
        if($mode == 'xhtml'){
            /** @var Doku_Renderer_xhtml $renderer */
            $renderer->doc .= 'hello test';
            return true;
        }
        return false;
    }
}

