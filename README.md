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


### Build all lists

Whether to build up all lists point by point. Two values

  * false
  * true

The default is false



Supported dokuwiki syntax
-------------------------

Apart of the ordinary things like headlines, tables, italic, bold etc. the following syntax elements are supported:

  * alignment of images: either left or right or centered
  * dokuwiki plugin wrap's ``<wrap lo></wrap>`` and ``<WRAP lo></WRAP>`` produce also in the presentation smaller text.
