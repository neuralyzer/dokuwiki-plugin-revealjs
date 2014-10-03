<?php
/**
 * Renderer for XHTML output
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';

/**
 * The Renderer
 */
class renderer_plugin_s5 extends Doku_Renderer_xhtml {
    var $slideopen = false;
    var $base='';
    var $tpl='';

    /**
     * the format we produce
     */
    function getFormat(){
        // this should be 's5' usally, but we inherit from the xhtml renderer
        // and produce XHTML as well, so we can gain magically compatibility
        // by saying we're the 'xhtml' renderer here.
        return 'xhtml';
    }


    /**
     * Initialize the rendering
     */
    function document_start() {
        global $ID;

        // call the parent
        parent::document_start();

        // store the content type headers in metadata
        $headers = array(
            'Content-Type' => 'text/html; charset=utf-8'
        );
        $this->base = DOKU_BASE.'lib/plugins/s5/';
    }

    /**
     * Print the header of the page
     *
     * Gets called when the very first H1 header is discovered. It includes
     * all the S5 CSS and JavaScript magic
     */
    function s5_init($title){
        global $conf;
        global $lang;
        global $INFO;
        global $ID;

        //throw away any previous content
        $this->doc = '
<html lang="en">

	<head>
		<meta charset="utf-8">

		<title>reveal.js - The HTML Presentation Framework</title>

		<meta name="description" content="A framework for easily creating beautiful presentations using HTML">
		<meta name="author" content="Hakim El Hattab">

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<link rel="stylesheet" href="'.$this->base.'css/reveal.min.css">
		<link rel="stylesheet" href="'.$this->base.'css/theme/default.css" id="theme">

		<!-- For syntax highlighting -->
		<link rel="stylesheet" href="'.$this->base.'lib/css/zenburn.css">

		<!-- If the query includes \'print-pdf\', include the PDF print sheet -->
		<script>
			if( window.location.search.match( /print-pdf/gi ) ) {
				var link = document.createElement( \'link\' );
				link.rel = \'stylesheet\';
				link.type = \'text/css\';
				link.href = '.$this->base.'\'css/print/pdf.css\';
				document.getElementsByTagName( \'head\' )[0].appendChild( link );
			}
		</script>

		<!--[if lt IE 9]>
		<script src='.$this->base.'"lib/js/html5shiv.js"></script>
		<![endif]-->
	</head>
<body>

		<div class="reveal">

			<!-- Any section element inside of this container is displayed as a slide -->
			<div class="slides">
';
    }

    /**
     * Closes the document
     */
    function document_end(){
        // we don't care for footnotes and toc
        // but cleanup is nice
        $this->doc = preg_replace('#<p>\s*</p>#','',$this->doc);

        if($this->slideopen){
            $this->doc .= '</section>'.DOKU_LF; //close previous slide
        }
        $this->doc .= '</div></div>
                       </body>
                       </html>';
    }

    /**
     * This is what creates new slides
     *
     * A new slide is started for each H2 header
     */
    function header($text, $level, $pos) {
        if($level == 1){
            if(!$this->slideopen){
                $this->s5_init($text); // this is the first slide
                $level = 2;
            }else{
                return;
            }
        }

        if($level == 2){
            if($this->slideopen){
                $this->doc .= '</section>'.DOKU_LF; //close previous slide
            }
            $this->doc .= '<section>'.DOKU_LF;
            $this->slideopen = true;
        }
        $this->doc .= '<h'.($level-1).'>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</h'.($level-1).'>'.DOKU_LF;
    }

    /**
     * Top-Level Sections are slides
     */
    function section_open($level) {
        if($level < 3){
            $this->doc .= '<section>'.DOKU_LF;
        }else{
            $this->doc .= '<section>'.DOKU_LF;
        }
        // we don't use it
    }

    /**
     * Throw away footnote
     */
    function footnote_close() {
        // recover footnote into the stack and restore old content
        $footnote = $this->doc;
        $this->doc = $this->store;
        $this->store = '';
    }

    /**
     * No acronyms in a presentation
     */
    function acronym($acronym){
        $this->doc .= $this->_xmlEntities($acronym);
    }

}

