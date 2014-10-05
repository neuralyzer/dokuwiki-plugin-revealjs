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
    var $level2open = false;
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
            if ( $this->level2open ) { // close nested section
                      $this->doc .= '</section>'.DOKU_LF;
                      $this->level2open = false;
             }
        }
        $this->doc .= '</div></div>
		<script src="'.$this->base.'lib/js/head.min.js"></script>
		<script src="'.$this->base.'js/reveal.min.js"></script>

		<script>

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				controls: '.$this->getConf('revealjs_controls').',
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
     * A new nested slide for each H3 header
     */
    function header($text, $level, $pos) {
        if($level <= 3){
            if($this->slideopen){
                $this->doc .= '</section>'.DOKU_LF; //close previous slide
                if ( ($this->level2open) && ($level <= 2) ) { // close nested section
                      $this->doc .= '</section>'.DOKU_LF;
                      $this->level2open = false;
                }
            }
            $this->doc .= '<section>'.DOKU_LF;
            if ( $level == 2 ) {   //first slide of possibly following nested ones if level is 2
                 $this->doc .= '<section>'.DOKU_LF;
                 $this->level2open = true; 
            } 
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

/**
     * Start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @param int $pos     byte position in the original source
     */
    function table_open($maxcols = null, $numrows = null, $pos = null) {
        // initialize the row counter used for classes
        $this->_counter['row_counter'] = 0;
        $class                         = 'table';
        if($pos !== null) {
            $class .= ' '.$this->startSectionEdit($pos, 'table');
        }
        $this->doc .= '<table>'.
            DOKU_LF;
    }

    /**
     * Close a table
     *
     * @param int $pos byte position in the original source
     */
    function table_close($pos = null) {
        $this->doc .= '</table>'.DOKU_LF;
        if($pos !== null) {
            $this->finishSectionEdit($pos);
        }
    }

    /**
     * Open a table header
     */
    function tablethead_open() {
        $this->doc .= DOKU_TAB.'<thead>'.DOKU_LF;
    }

    /**
     * Close a table header
     */
    function tablethead_close() {
        $this->doc .= DOKU_TAB.'</thead>'.DOKU_LF;
    }

    /**
     * Open a table row
     */
    function tablerow_open() {
        // initialize the cell counter used for classes
        $this->_counter['cell_counter'] = 0;
        $class                          = 'row'.$this->_counter['row_counter']++;
        $this->doc .= DOKU_TAB.'<tr>'.DOKU_LF.DOKU_TAB.DOKU_TAB;
    }

    /**
     * Close a table row
     */
    function tablerow_close() {
        $this->doc .= DOKU_LF.DOKU_TAB.'</tr>'.DOKU_LF;
    }

    /**
     * Open a table header cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
        $class = 'class="col'.$this->_counter['cell_counter']++;
        if(!is_null($align)) {
            $class .= ' '.$align.'align';
        }
        $class .= '"';
        $this->doc .= '<th ';
        if($colspan > 1) {
            $this->_counter['cell_counter'] += $colspan - 1;
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        if($rowspan > 1) {
            $this->doc .= ' rowspan="'.$rowspan.'"';
        }
        $this->doc .= '>';
    }

    /**
     * Close a table header cell
     */
    function tableheader_close() {
        $this->doc .= '</th>';
    }

    /**
     * Open a table cell
     *
     * @param int    $colspan
     * @param string $align left|center|right
     * @param int    $rowspan
     */
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
        $class = 'class="col'.$this->_counter['cell_counter']++;
        if(!is_null($align)) {
            $class .= ' '.$align.'align';
        }
        $class .= '"';
        $this->doc .= '<td ';
        if($colspan > 1) {
            $this->_counter['cell_counter'] += $colspan - 1;
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        if($rowspan > 1) {
            $this->doc .= ' rowspan="'.$rowspan.'"';
        }
        $this->doc .= '>';
    }

    /**
     * Close a table cell
     */
    function tablecell_close() {
        $this->doc .= '</td>';
    }


}

