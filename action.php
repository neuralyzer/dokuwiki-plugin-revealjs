<?php

if (!defined('DOKU_INC')) {die();}
if (!defined('DOKU_PLUGIN')) {define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');}
require_once DOKU_PLUGIN . 'action.php';

class action_plugin_revealjs extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('RENDERER_CONTENT_POSTPROCESS', 'BEFORE', $this, '_renderer_content_postprocess');
    }

    function _renderer_content_postprocess(&$event, $param) {
        if ($_GET['do'] !== 'export_revealjs' && $this->getConf('revealjs_active')) {
            /* close last edit section correctly (missing </div>), because we close sections
            only when a new one is opened - and this logic fails for the last section in the
            document */
            $search = '<!-- EDIT';
            $replace = '</div><!-- EDIT';
            $pos = strrpos($event->data[1],$search);
            if ($pos !== false) {
                $event->data[1] = substr_replace($event->data[1], $replace, $pos, strlen($search));
                //dbglog('Plugin revealjs - hook RENDERER_CONTENT_POSTPROCESS - closing last edit section (missing </div>).', __FILE__.' line '.__LINE__);
            }

            // correct link for PDF export and fixing unknown slide directions in wiki page with jQuery
            $event->data[1] .='
<script>
jQuery(document).ready(function(){
    jQuery(".slide-export-link a:last").each(function(){
        var elem = jQuery(this);
        var count = (elem.attr("href").match(/\?/g) || []).length;
        if (count == 0) {
            elem.attr("href", elem.attr("href").replace("&print-pdf","?print-pdf"));
        }
    });'.($this->getConf('slides_with_unknown_direction')?'
    jQuery(".fix-my-direction").each(function(){
        var elem = jQuery(this);
        elem.removeClass("fix-my-direction");
        if (elem.next().find("h1'.($this->getConf('horizontal_slide_level')==2?',h2':'').'").length > 0) {
            elem.text("→" + elem.text());
        }
        else {
            elem.text("↓" + elem.text());
        }
    });':'').'
});
</script>';
        }
    }

}
