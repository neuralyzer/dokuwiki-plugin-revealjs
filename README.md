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
<div class= "plugin_include_content ....> ...</div>
```

at such places that the closing and opening div tags interfere with the reveal.js section tags.

