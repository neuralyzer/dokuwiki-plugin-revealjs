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
    var $slide_open = false;
    var $column_open = false;
    var $base='';
    var $tpl='';
    var $next_slide_with_background = false;
    var $next_slide_without_footer = false;
    var $background_image_url;

    public function add_background_to_next_slide($image_url){
        $this->background_image_url = $image_url;
        $this->next_slide_with_background = true;
    }

    public function next_slide_without_footer(){
        $this->next_slide_without_footer = true;
    }

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
        global $conf;
        global $lang;

        // call the parent
        parent::document_start();

        // store the content type headers in metadata
        $headers = array(
            'Content-Type' => 'text/html; charset=utf-8'
        );
       $theme = isset($_GET['theme'])?$_GET['theme']:$this->getConf('theme');
       p_set_metadata($ID,array('format' => array('revealjs' => $headers) ));
        $this->base = DOKU_BASE.'lib/plugins/revealjs/';
       $this->doc = '
<!DOCTYPE html>
<html lang="'.$conf['lang'].'" dir="'.$lang['direction'].'">

	<head>
		<meta charset="utf-8">

		<title>'.tpl_pagetitle($ID, true).'</title>

		<meta name="description" content="A framework for easily creating beautiful presentations using HTML">
		<meta name="author" content="Hakim El Hattab">

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">

                <link rel="stylesheet" href="'.$this->base.'css/reveal.css">
		<link rel="stylesheet" href="'.$this->base.'css/theme/'.$theme.'.css" id="theme">
                <link rel="stylesheet" href="'.$this->base.'doku-substitutes.css"> 

		<!-- Code syntax highlighting -->
		<link rel="stylesheet" href="'.$this->base.'lib/css/zenburn.css">

		<!-- Printing and PDF exports -->
		<script>
			var link = document.createElement( \'link\' );
			link.rel = \'stylesheet\';
			link.type = \'text/css\';
			link.href = window.location.search.match( /print-pdf/gi ) ? \''.$this->base.'css/print/pdf.css\' : \''.$this->base.'css/print/paper.css\';
			document.getElementsByTagName( \'head\' )[0].appendChild( link );
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

        if($this->slide_open){
            $this->doc .= '</section>'.DOKU_LF; //close previous slide
            if ( $this->column_open ) { // close nested section
                      $this->doc .= '</section>'.DOKU_LF;
                      $this->column_open = false;
             }
        }
        $show_controls = $this->getConf('controls') ? 'true' : 'false';
        $show_progress_bar = $this->getConf('show_progress_bar') ? 'true' : 'false';
        $this->doc .= '</div></div>
		<script src="'.$this->base.'lib/js/head.min.js"></script>
		<script src="'.$this->base.'js/reveal.js"></script>

		<script>

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				controls: '. $show_controls .',
				progress: '. $show_progress_bar .',
				history: true,
				center: true,

				transition: \''.$this->getConf('transition').'\', // none/fade/slide/convex/concave/zoom
				math: {
                                   mathjax: \'//cdn.mathjax.org/mathjax/latest/MathJax.js\',
                                   config: \'TeX-AMS_HTML-full\'  // See http://docs.mathjax.org/en/latest/config-files.html
                                },

				dependencies: [
					{ src: \''.$this->base.'lib/js/classList.js\', condition: function() { return !document.body.classList; } },
					{ src: \''.$this->base.'plugin/markdown/marked.js\', condition: function() { return !!document.querySelector( \'[data-markdown]\' ); } },
					{ src: \''.$this->base.'plugin/markdown/markdown.js\', condition: function() { return !!document.querySelector( \'[data-markdown]\' ); } },
					{ src: \''.$this->base.'plugin/highlight/highlight.js\', async: true, condition: function() { return !!document.querySelector( \'pre code\' ); }, callback: function() { hljs.initHighlightingOnLoad(); } },
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

    /*
    *
     * Creates a new section possibliy including the background image.
     */
    function create_slide_section($nested_slide){
        $this->doc .= '<section';
        if ($nested_slide) {
            if ($this->next_slide_with_background){
               $this->doc .= ' data-background="'.$this->background_image_url.'"';
               $this->next_slide_with_background = false;
            } 
             if ($this->next_slide_without_footer) {
                $this->doc .= ' data-state="no-footer"';
                $this->next_slide_without_footer = false;
             }
        }
        $this->doc .= '>';
        $this->doc .= DOKU_LF;
    }


    /**
     * This is what creates new slides
     *
     * A new column is started for each H1 or H2 header
     * A new vertical slide for each H3 header
     */
    function header($text, $level, $pos) {
        if($level <= 3){
            if($this->slide_open){
                $this->doc .= '</section>'.DOKU_LF; //close previous slide
                if ( ($this->column_open) && ($level <= 2) ) { // close nested section
                      $this->doc .= '</section>'.DOKU_LF;
                      $this->column_open = false;
                }
            }
            if ( $level <= 2 ) {   //first slide of possibly following nested ones if level is 2
                 $this->create_slide_section(false);
                 $this->column_open = true;
            }
            $this->create_slide_section(true); # always without background to not to have a background for a whole subsection
            $this->slide_open = true;
        }
        $this->doc .= '<h'.$level.'>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</h'.$level.'>'.DOKU_LF;
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
        $this->doc .= '<table class="doku_revealjs_table">'.
            DOKU_LF;
    }

    /**
     * Close a table
     *
     * @param int $pos byte position in the original source
     */
    function table_close($pos = null) {
        $this->doc .= '</table>'.DOKU_LF; 
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


    /**
    * Open a list item
    *
    * @param int $level the nesting level
    *
    * Default: build list item per item.
    * This is called "fragment" in reveal.js
    */
    function listitem_open($level) {
        if($this->getConf('build_all_lists')) {
          $this->doc .= '<li class="fragment">';
       } else {
          $this->doc .= '<li>'; 
       }
    }

  
    /**
     * Don't use Geshi. Overwrite ths Geshi function.
     * @author Emmanuel Klinger
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string $type     code|file
     * @param string $text     text to show
     * @param string $language programming language to use for syntax highlighting
     * @param string $filename file path label
     */
    function _highlight($type, $text, $language = null, $filename = null) {
        global $ID;
        global $lang;

        if($filename) {
            // add icon
            list($ext) = mimetype($filename, false);
            $class = preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
            $class = 'mediafile mf_'.$class;

            $this->doc .= '<dl class="'.$type.'">'.DOKU_LF;
            $this->doc .= '<dt><a href="'.exportlink($ID, 'code', array('codeblock' => $this->_codeblock)).'" title="'.$lang['download'].'" class="'.$class.'">';
            $this->doc .= hsc($filename);
            $this->doc .= '</a></dt>'.DOKU_LF.'<dd>';
        }

        if($text{0} == "\n") {
            $text = substr($text, 1);
        }
        if(substr($text, -1) == "\n") {
            $text = substr($text, 0, -1);
        }

        if(is_null($language)) {
            //@author Emmanuel: This line is changed from the original
            $this->doc .= '<pre><code>'.$this->_xmlEntities($text).'</code></pre>'.DOKU_LF;
        } else {
            //@author Emmanuel: This line is changed from the original
            $this->doc .= '<pre><code class="'.$language.'">'.$this->_xmlEntities($text).'</code></pre>'.DOKU_LF;
        }

        if($filename) {
            $this->doc .= '</dd></dl>'.DOKU_LF;
        }

        $this->_codeblock++;
    }

}


