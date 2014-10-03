<?php
/**
 * Renderer for XHTML output
 *
 * @author Emmanuel Klinger <emmanuel.klinger@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';

/**
 * The Renderer
 */
class renderer_plugin_revealjs extends Doku_Renderer_xhtml {
    var $slideopen = false;
    var $base='';
    var $tpl='';

    /**
     * the format we produce
     */
    function getFormat(){
        // this should be 'revealjs' usally, but we inherit from the xhtml renderer
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
        $this->base = DOKU_BASE.'lib/plugins/revealjs/';
       $this->doc = '
<!doctype html>
<html lang="en">

	<head>
		<meta charset="utf-8">

		<title>'.tpl_pagetitle($ID, true).'</title>

		<meta name="description" content="A framework for easily creating beautiful presentations using HTML">
		<meta name="author" content="Hakim El Hattab">

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<link rel="stylesheet" href="'.$this->base.'css/reveal.min.css">
		<link rel="stylesheet" href="'.$this->base.'css/theme/'.$this->getConf('revealjs_theme').'.css" id="theme">

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
		<script src="'.$this->base.'lib/js/head.min.js"></script>
		<script src="'.$this->base.'js/reveal.min.js"></script>

		<script>

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				controls: true,
				progress: true,
				history: true,
				center: true,

				theme: Reveal.getQueryHash().theme, // available themes are in /css/theme
				transition: Reveal.getQueryHash().transition || \'default\', // default/cube/page/concave/zoom/linear/fade/none
				dependencies: [
					{ src: \''.$this->base.'lib/js/classList.js\', condition: function() { return !document.body.classList; } },
					{ src: \''.$this->base.'plugin/markdown/marked.js\', condition: function() { return !!document.querySelector( \'[data-markdown]\' ); } },
					{ src: \''.$this->base.'plugin/markdown/markdown.js\', condition: function() { return !!document.querySelector( \'[data-markdown]\' ); } },
					{ src: \''.$this->base.'plugin/highlight/highlight.js\', async: true, callback: function() { hljs.initHighlightingOnLoad(); } },
					{ src: \''.$this->base.'plugin/zoom-js/zoom.js\', async: true, condition: function() { return !!document.body.classList; } },
					{ src: \''.$this->base.'plugin/notes/notes.js\', async: true, condition: function() { return !!document.body.classList; } },

// MathJax
        { src: \''.$this->base.'plugin/math/math.js\', async: true }
				]
			});

		</script>
                       </body>
                       </html>';
    }

    /**
     * This is what creates new slides
     *
     * A new slide is started for each H2 header
     */
    function header($text, $level, $pos) {
        if($level < 3){
            if($this->slideopen){
                $this->doc .= '</section>'.DOKU_LF; //close previous slide
            }
            $this->doc .= '<section>'.DOKU_LF;
            $this->slideopen = true;
        }
        $this->doc .= '<h'.($level).'>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</h'.($level).'>'.DOKU_LF;
    }

    /**
     * Top-Level Sections are slides
     */
    function section_open($level) {
        if($level < 3){
           // $this->doc .= '<section>'.DOKU_LF;
        }else{
            //$this->doc .= '<section>'.DOKU_LF;
        }
        // we don't use it
    }

   function section_close() {
    
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

