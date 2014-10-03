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


Every new H1 or H2 section, that is  6 equal signs and 6 equal signs open a new slide horizontally.
New H3 sections are appended vertically if they follow after an H2 section.

**Caution**: Only H2 sections open the vertical axis. If an H3 section follows after an H1 section it is appended horizontally.
