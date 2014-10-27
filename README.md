dokuwiki-plugin-revealjs
========================


Reval.js plugin for dokuwiki

This started as a fork of Andreas Gohr's S5 plugin https://www.dokuwiki.org/plugin:s5.

It makes use of Reveal.js https://github.com/hakimel/reveal.js/.

Install
-------

Paste the address git config https://github.com/neuralyzer/dokuwiki-plugin-revealjs/zipball/master in the manual installation field.


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



MathJax coompatibility
----------------------

At the moment this plugin loads MathJax from the MathJax CDN directly whether the Dokuwiki MathJax plugin is installed or not. It ignores Dokuwiki's MathJax plugin and the custom settings you might have made. Many browsers (Firefox, Chromium) might complain about non-secure content being loaded form the page when attempting to display in revealjs presentation mode. Firefox does so so by displaying a shield icon left to the URL bar, Chromium by displaying a shield icon right to the URL bar. Enabling unsafe scripts fixes the problem.



Configuration options
---------------------


### Available themes


Available themes are the Reval.js themes

  * beige
  * blood
  * default
  * moon
  * night
  * serif
  * simple
  * sky
  * solarized

Plus additionally the theme

  * beige_white

which is a simple modification of the beige theme. The only change to the original beige theme is that the background is white instead of the beige radial gradient.

The default is beige_white


### Controls

Show the reveal.js controls. Two values

  * false
  * true

The default is true.



Supported dokuwiki syntax
-------------------------

So far the following syntax elements are supported:

  * alignment of images: either left or right or centered
  * dokuwiki plugin wrap's ``<wrap lo></wrap>`` and ``<WRAP lo></WRAP>`` produce also in the presentation smaller text
