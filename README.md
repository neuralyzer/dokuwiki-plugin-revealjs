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


### Available themes


Available themes are the Reval.js themes. Possible values:

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



