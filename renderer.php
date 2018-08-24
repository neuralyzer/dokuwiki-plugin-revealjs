<?php
/**
 * Renderer for XHTML output
 *
 * @author Emmanuel Klinger <emmanuel.klinger@gmail.com>
 * @author Ottmar Gobrecht <ottmar.gobrecht@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';

/**
 * The Renderer
 */
class renderer_plugin_revealjs extends Doku_Renderer_xhtml {
    var $base = '';
    var $tpl = '';
    var $slide_indicator_headers = true;
    var $slide_number = 0;
    var $slide_open = false;
    var $column_open = false;
    var $notes_open = false;
    var $quote_open = false;
    var $fragment_list_open = false;
    var $no_fragment_list_open = false;
    var $fragment_style = '';
    var $next_slide_background_color = '';
    var $next_slide_background_image = '';
    var $next_slide_background_size = '';
    var $next_slide_background_position = '';
    var $next_slide_background_repeat = '';
    var $next_slide_background_transition = '';
    var $next_slide_transition = '';
    var $next_slide_transition_speed  = '';
    var $next_slide_no_footer = false;

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

        // merge URL params into plugin conf - changing params direct in the URL is only working, when page is not cached (~~NOCACHE~~)
        if (count($_GET)){
            if (!array_key_exists('plugin', $conf)) {
                $conf['plugin'] = array('revealjs' => $_GET);
            }
            elseif (!array_key_exists('revealjs', $conf['plugin'])) {
                $conf['plugin']['revealjs'] = $_GET;
            }
            else {
                $conf['plugin']['revealjs'] = array_merge($conf['plugin']['revealjs'], $_GET);
            }
        }

        // call the parent
        parent::document_start();

        // store the content type headers in metadata
        $headers = array(
            'Content-Type' => 'text/html; charset=utf-8'
        );

        p_set_metadata($ID,array('format' => array('revealjs' => $headers) ));
        $this->base = DOKU_BASE.'lib/plugins/revealjs/';
        $this->doc = '<!DOCTYPE html>
<html lang="'.$conf['lang'].'" dir="'.$lang['direction'].'">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <title>'.tpl_pagetitle($ID, true).'</title>

    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">

    <link rel="stylesheet" href="'.$this->base.'css/reveal.css">
    <link rel="stylesheet" href="'.$this->base.'css/theme/'.$this->getConf('theme').'.css" id="theme">
    <link rel="stylesheet" href="'.$this->base.'doku-substitutes.css">

    <!-- Code syntax highlighting -->
    <link rel="stylesheet" href="'.$this->base.'lib/css/zenburn.css">

    ' . ($this->getConf('show_image_borders') ?
    '<!-- Image borders are switched on -->' :
    '<!-- Image borders are switched off -->
    <style>.reveal img { border: none !important; box-shadow: none !important; background: none !important; } .level1, .level2, .level3, .level4, .level5 {min-height:300px;}</style>') . '

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

<!-- page content start ------------------------------------------------------->';
    }


    /**
     * Closes the document
     */
    function document_end(){
        // we don't care for footnotes and toc
        // but cleanup is nice
        $this->doc = preg_replace('#<p>\s*</p>#','',$this->doc);

        // cleanup quotes - too much whitspace from declaration: ">    valid quote"
        $this->doc = preg_replace('/<blockquote>&ldquo;\s*/u','<blockquote>&ldquo;',$this->doc);

        // close maybe open slide and column
        $this->close_slide_container();

        $show_controls = $this->getConf('controls') ? 'true' : 'false';
        $show_progress_bar = $this->getConf('show_progress_bar') ? 'true' : 'false';
        $size = explode("x", $this->getConf('size'));
        $this->doc .= '
<!-- page content stop -------------------------------------------------------->

        </div><!-- slides -->
    </div><!-- reveal -->

    <script src="'.$this->base.'lib/js/head.min.js"></script>
    <script src="'.$this->base.'js/reveal.js"></script>
    <script>
        Reveal.initialize({
            width: '. ($size[0] ? $size[0] : 960) .',
            height: '. ($size[1] ? $size[1] : 700) .',
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

    /**
     * Creates a slide container (section), possibly containing nested slides (sections)
     */
    function open_slide_container () {
        $this->close_slide_container();
        $this->doc .= '<section>'.DOKU_LF;
        $this->column_open = true;
    }

    /**
     * Creates a slide (section)
     */
    function open_slide () {
        $this->close_slide();
        $this->doc .= '  <section';
        if ($this->next_slide_background_color) {
            $this->doc .= ' data-background-color="'.$this->next_slide_background_color.'"';
            $this->next_slide_background_color = '';
        }
        if ($this->next_slide_background_image) {
            $this->doc .= ' data-background-image="'.$this->next_slide_background_image.'"';
            $this->next_slide_background_image = '';
        }
        if ($this->next_slide_background_size) {
            $this->doc .= ' data-background-size="'.$this->next_slide_background_size.'"';
            $this->next_slide_background_size = '';
        }
        if ($this->next_slide_background_position) {
            $this->doc .= ' data-background-position="'.$this->next_slide_background_position.'"';
            $this->next_slide_background_position = '';
        }
         $data['background_position'];
        if ($this->next_slide_background_repeat) {
            $this->doc .= ' data-background-repeat="'.$this->next_slide_background_repeat.'"';
            $this->next_slide_background_repeat = '';
        }
        if ($this->next_slide_background_transition) {
            $this->doc .= ' data-background-transition="'.$this->next_slide_background_transition.'"';
            $this->next_slide_background_transition = '';
        }
        if ($this->next_slide_transition) {
            $this->doc .= ' data-transition="'.$this->next_slide_transition.'"';
            $this->next_slide_transition = '';
        }
        if ($this->next_slide_transition_speed) {
            $this->doc .= ' data-transition-speed="'.$this->next_slide_transition_speed.'"';
            $this->next_slide_transition_speed  = '';
        }
        if ($this->next_slide_no_footer) {
            $this->doc .= ' data-state="no-footer"';
            $this->next_slide_no_footer = false;
        }
        $this->doc .= '>'.DOKU_LF;

        // mark slide as open
        $this->slide_open = true;
    }

    /**
     * Closes a slide container (section)
     */
    function close_slide_container () {
        $this->close_slide();
        if ($this->column_open) {
            $this->doc .= '</section>'.DOKU_LF;
            $this->column_open = false;
        }
    }

    /**
     * Closes a slide (section)
     */
    function close_slide () {
        if ($this->slide_open) {
            $this->doc .= '  </section>'.DOKU_LF;
            $this->slide_open = false;
        }
    }

    /**
     * DokuWiki sections are not used on a slideshow - so we redeclare it here only
     */
    function section_open($level) {}
    function section_close() {}

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
    function table_open($maxcols = null, $numrows = null, $pos = null, $classes = NULL) {
        // initialize the row counter used for classes
        $this->_counter['row_counter'] = 0;
        $class                         = 'table';
        if($pos !== null) {
	    
	    $sectionEditStartData = ['target' => 'table'];
	    if (!defined('SEC_EDIT_PATTERN')) {
		// backwards-compatibility for Frusterick Manners (2017-02-19)
		$sectionEditStartData = 'table';
	    }
	    
            $class .= ' '.$this->startSectionEdit($pos, $sectionEditStartData);
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
    function tablerow_open($classes = NULL) {
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
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1, $classes = NULL) {
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
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1, $classes = NULL) {
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
    function listitem_open($level, $node=false) {
        if( !$this->notes_open
            && !$this->no_fragment_list_open
            && ($this->getConf('build_all_lists') || $this->fragment_list_open) ) {
        $this->doc .= '<li class="fragment' . ($this->fragment_style ? ' '.$this->fragment_style : '') . '">';
        }
        else {
            $this->doc .= '<li>';
        }
    }


    /**
     * Start a block quote
     */
    function quote_open() {
        if (!$this->quote_open) {
            $this->doc .= '<blockquote>&ldquo;';
            $this->quote_open = true;
        }
    }

    /**
     * Stop a block quote
     */
    function quote_close() {
        if ($this->quote_open) {
            $this->doc .= '&rdquo;</blockquote>'.DOKU_LF;
            $this->quote_open = false;
        }
    }


    /**
     * Don't use Geshi. Overwrite the Geshi function.
     * @author Emmanuel Klinger
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string $type     code|file
     * @param string $text     text to show
     * @param string $language programming language to use for syntax highlighting
     * @param string $filename file path label
     * @param string $options highlight options - not used 													     
     */
    function _highlight($type, $text, $language = null, $filename = null, $options = null) {
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
