dokuwiki-plugin-revealjs
========================

Reval.js plugin for dokuwiki

This started as a fork of Andreas Gohr's S5 plugin https://www.dokuwiki.org/plugin:s5.

It uses Reveal.js https://github.com/hakimel/reveal.js/.

Install
-------

Paste the address git config http://github.com/neuralyzer/dokuwiki-plugin-revealjs/zipball/master in the manual installation field or use Dokuwiki's extension manager.


Usage
-----


Every new H1 or H2 section, that is  6 equal signs or 5 equal signs open a new slide horizontally.
New H3 sections (4 equal signs) are appended vertically if they follow after an H2 section.

**Caution**: Only H2 sections open the vertical axis. If an H3 section follows after an H1 section it is appended horizontally.

Add ``~~REVEAL~~`` to a page to insert a button for presentation start.

Check also the source code of the [example presentation](example_presentation.dokuwiki)

Include plugin compatibility
----------------------------



Edit in the file dokuwiki/lib/plugin/include/syntax/wrap.php in the function render the line

```
if ($mode == 'xhtml') {
```
to

```
if ( ($mode == 'xhtml') && (! is_a($renderer, 'renderer_plugin_revealjs')) ) {
```
The include plugin will otherwise put some

```
<div class= "plugin_include_content ..." ...> ...</div>
```

at such places that the closing and opening div tags interfere with the reveal.js section tags.



MathJax compatibility
----------------------

At the moment this plugin loads MathJax from the MathJax CDN directly whether the Dokuwiki MathJax plugin is installed or not. It ignores Dokuwiki's MathJax plugin and the custom settings you might have made.



Configuration options
---------------------

Configuration is done in DokuWiki's configuration manager.

![Reveal.js configuration](revealjs_configuration.png)


### Available themes


Available themes are the Reveal.js themes. Possible values:

  * black
  * white
  * beige
  * blood
  * league
  * default
  * moon
  * night
  * serif
  * simple
  * sky
  * solarized

The default is white.


### Controls

Show the reveal.js controls. Two values

  * false
  * true

The default is false.


### Progres bar

Show the reveal.js progress bar. Two values

  * false
  * true

The default is false.


### Build all lists

Whether to build up all lists point by point. Two values

  * false
  * true

The default is false


### Transition

The slide transition. Possible settings:


  * none
  * fade
  * slide
  * convex
  * concave
  * zoom

The default is fade.


### Build all lists

Whether to build up all bullet point lists item by item by for every slide.

The default is false.



Supported dokuwiki syntax
-------------------------

Apart of the ordinary things like headlines, tables, italic, bold etc. the following syntax elements are supported:

  * alignment of images: either left or right or centered
  * dokuwiki plugin wrap's ``<wrap lo></wrap>`` and ``<WRAP lo></WRAP>`` produce also in the presentation smaller text.
  * ``<WRAP clear></WRAP>`` for clearing of floats


Extra syntax
------------

### Theme selection and button for presentation start

Putting on the page somewhere a
```
~~REVEAL~~
```
will insert at this position a button. A click on this button then starts the presentation with the default theme.

Alternatively, to select a theme put a
```
~~REVEAL theme_name~~
```
somehere with ``theme_name`` replaced by one of the reveal.js themes as listed under "Available themes".

All other options are also overwritable in a wiki page by using the URL query parameter syntax:
```
~~REVEAL theme=sky&transition=convex&controls=1&show_progress_bar=1&build_all_lists=0&open_in_new_window=1~~
```
Please note that boolean values must be numeric (1 or 0). If you want to be able to change the options directly in the URL after the presentation has started, then you have to disable DokuWiki's caching by putting `~~NOCACHE~~` at the top of the page.

### Slide background

The plugin introduces the syntax

```
   {{background>value}}
```

Where `value` can be either a Dokuwiki image identifier, e.g.

```
  value = :images:my_images:image1.png
```

or a color in hex code preceded by `"#"`, e.g.

```
  value = #ff0022
```

The so defined background will be applied to the next slide. I.e. the background tag has to preceed the heading opening
the next slide and will only apply to that slide. For example


```
{{background>:images:image1.png}}
===== my heading=====

slide with background

===== my second heading=====

slide without background
```

produces one slide with background and a second slide without background.


### Footers

Sometimes you might want to have a footer for all the pages. This footer might contain your company's logo or similar things. Footers are most conveniently added using in addition the dokuwiki plugin "wrap". To get a footer on each page put at the very beginning of your document, i.e. before the first heading but possibly after a ``~~NOCACHE`` or ``~~REVEAL~~`` the following block
```
<wrap footer>Footer content here.</wrap>
```
This inserts a footer on every single page. If you want the footer to disappear for a specific page place before that page's heading a ``{{no-footer}}``. For example
```
{{no-footer}}
===== my heading=====

slide without footer


{{no-footer}}
{{background>:images:image1.png}}
===== my heading=====

slide without footer and with background

```

PDF export
----------

Presentations can be exported as PDF.
To do so append a ``&print-pdf`` to the URL.

For example if the URL of your DokuWiki reveal.js presentation is usually
```
http://example-dokuwiki.com/doku.php?do=export_revealjs&id=example:page
```
you would have to change this manually in the address bar of you browser to

```
http://example-dokuwiki.com/doku.php?do=export_revealjs&id=example:page&print-pdf
```
After that the presentation looks weird in the browser but can be printed via you browser's print function.
Officially only Chromium and Chrome are supported for PDF export. Check also the  [Reveal.js PDF export documentation](https://github.com/hakimel/reveal.js#pdf-export).
