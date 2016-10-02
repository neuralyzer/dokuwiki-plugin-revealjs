<?php

if (!defined('DOKU_INC')) {die();}
if (!defined('DOKU_PLUGIN')) {define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');}
require_once DOKU_PLUGIN . 'action.php';

class action_plugin_revealjs extends DokuWiki_Action_Plugin {
    //function getInfo() {return array('author' => 'github.com/ogobrecht', 'name' => 'Reveal.js DokuWiki Plugin', 'url' => 'https://github.com/neuralyzer/dokuwiki-plugin-revealjs');}

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, '_parser_cache_use');
        $controller->register_hook('RENDERER_CONTENT_POSTPROCESS', 'BEFORE', $this, '_renderer_content_postprocess');
    }

    // taken from DokuWiki plugin editsections (copyright by Christophe Drevet <dr4ke@dr4ke.net>)
    function _parser_cache_use(&$event, $ags) {
        global $ID;
        if ( auth_quickaclcheck($ID) >= AUTH_EDIT ) {
            // disable cache only for writers
            $event->_default = 0;
            //dbglog('Plugin revealjs - hook PARSER_CACHE_USE - cache is disabled - user can edit page.', __FILE__.' line '.__LINE__);
        }
    }

    /* close last edit section correctly (missing </div>), because we close sections
    only when a new one is opened - and this logic fails for the last section in the
    document */
    function _renderer_content_postprocess(&$event, $param) {
        $search = '<!-- EDIT';
        $replace = '</div><!-- EDIT';
        $pos = strrpos($event->data[1],$search);
        // replacement only for wiki pages, not for presentation
        if ($_GET['do'] !== 'export_revealjs' &&
            $this->getConf('revealjs_active_and_user_can_edit_and_show_slide_details') &&
            $pos !== false) {
            $event->data[1] = substr_replace($event->data[1], $replace, $pos, strlen($search));
            //dbglog('Plugin revealjs - hook RENDERER_CONTENT_POSTPROCESS - closing last edit section (missing </div>).', __FILE__.' line '.__LINE__);
        }
        if ($this->getConf('slides_with_unknown_direction')) {
            $event->data[1] .='
<script>
jQuery(document).ready(function(){
    jQuery(".fix-my-direction").each(function(){
        element = jQuery(this);
        element.removeClass("fix-my-direction");
        if (element.next().find("h1'.($this->getConf('horizontal_slide_level')==2?',h2':'').'").length > 0) {
            element.text("→" + element.text());
        }
        else {
            element.text("↓" + element.text());
        }
    });
});
</script>';
            //dbglog('Plugin revealjs - hook RENDERER_CONTENT_POSTPROCESS - fixing unknown slide direction in wiki page with jQuery.', __FILE__.' line '.__LINE__);
        }
    }

}
