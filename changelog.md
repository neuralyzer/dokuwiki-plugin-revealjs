Changes
=======

### 2018-09-02 (by github.com/jmetzger)

- Fixed problems with Greebo release

### 2017-01-30 (by github.com/ogobrecht)

- Improved slide handling: Since slides are indicated by headers you are relative inflexible regarding the header levels and sizes on vertical (nested) slides - they are normally simply to small
  - New conf option horizontal_slide_level: Headers on this level or above starting a horizontal slide. Levels below starting a vertical (nested) slide - no effect on slides, which are indicated by alternative slide indicators
  - New conf option enlarge_vertical_slide_headers: Increase headers on slides below horizontal_slide_level - no effect on slides, which are indicated by alternative slide indicators
  - New alternative slide indicator:
    - `---->` opens a new slide with default transition in default speed (open previous slides will be closed implicitly)
    - Full example - parameters are parsed dynamically like in CSS, the parameter order is not important: `---- orange wiki:dokuwiki-128.png 10% repeat bg-slide zoom-in fade-out slow no-footer ---->`
      - All possible HTML color names and codes are supported: `red`, `#f00`, `#ff0000`, `rgb(255,0,0)`, `rgba(255,0,0,0.5)`, `hsl(0,100%,50%)`, `hsla(0,100%,50%,0.5)`
      - Background images are recognized case insensitive by the endings gif, png, jpg, jpeg, svg
      - Background image size is recognized by postfix `%` and `px` or by keywords `auto`, `contain` and `cover` (cover is the default in Reveal.js) - example: `10%` or `250px`
      - Background image position is recognized by keywords `top`, `bottom`, l`eft`, `right`, `center` (center is the default in Reveal.js) - examples: `top left`, `bottom center`
      - Background image repeat is recognized by the keyword `repeat` (no-repeat is the default in Reveal.js)
      - Background transition: prefix `bg-` followed by `none`, `fade`, `slide`, `convex`, `concave` or `zoom`
      - Slide transition: `none`, `fade`, `slide`, `convex`, `concave` or `zoom` followed by optional postfix `-in` or `-out` for different transitions on one slide
      - Transition speed: `default`, `fast`, `slow`
    - `---->>` opens a new slide container for vertical (nested) slides and a new slide with the given options - example: `---- red zoom ---->>`
    - The next `---->>` will close the previous container (and slide) implicitly
    - Technical details:
      - In the rendering the slide mode changes from "headers driven" to "special horizontal rule driven" - headers are no longer interesting in this mode for slide changes
      - You can create of course a whole presentation with this alternative slide indicator
      - if you want to leave this slide mode you need a way to explicit close a slide or container:
      - `<<----` closes a slide container (and possibly open slide inside)
      - `<----` closes a slide
  - Improved existing background indicator {{background>orange bg-zoom}}: allowing now all options from the new slide indicator (see above)
- Improved wiki page rendering:
  - New conf option show_slide_details (default on): Show slide details on wiki page: start of slide and options, background preview, start of notes
- Improved section editing:
  - Slide background definition or alternative slide indicators must be noted before the headers/slide content
  - The normal section editing has the problem, that it is starting on the headers, so the background definition or alternative slide indicators are on the wrong section
  - With the section editing improvement the whole slide can be edited at once including the background definitions
- New conf option for the start button:
  - You can now select between the default one(start_button.png), the one from the s5 plugin (start_button.screen.png) and an own one (start_button.local.png), which is upgrade safe and must be copied into the plugin directory
  - If you are able to edit the page, then a export to PDF link is rendered under the start slideshow button - this is the reveal.js default export and works only in Chromium and Chrome - more infos [here][0]
- Introducing new syntax for [speaker notes][1]:
  - `<notes>` - no parameters
  - No changes on wiki pages
  - On a slideshow the content is wrapped into `<aside class="notes">`
  - Lists in notes are always NOT incremental, because the list is unvisible and you would have to press the next key for each entry without any obvious effect
- Introducing new syntax for [fragments][2]:
  - `<fragment>` for inline usage (only formatting and substitutions supported)
  - `<fragment-block>`for any wiki content
  - `<fragment-list>` to overwrite the global option build_all_lists (if false)
  - `<no-fragment-list>` to overwrite the global option build_all_lists (if true)
  - Support for style and index where possible - see also the source code file example_presentation.dokuwiki and [this reveal onlin demo][3]
- Improved blockquote handling:
  - The nesting is suppressed on the slideshow to support the way Reveal is showing notes
- Improved DokuWiki text formatting on the slides:
  - Underlined text is now underlined and no longer italic
  - Subscript and superscript are now a bit smaller than the default text
- New conf option for image borders:
  - In DokuWiki in the default theme images have no borders
  - In Reveal the images have borders
  - Now you can configure this globally in the plugin settings and since all options are overwritable per wiki page you can have different settings on different pages/presentations
- New conf options for slide size:
  - Default size in Reveal.js is 960x700
  - Sometimes you need to align this if you have content that does not fit on this size
  - As all options this one is overwritable per page/presentation - see also example_presentation.dokuwiki
- New theme dokuwiki:
  - This is a copy of the theme solarized: the only change is the aligned background color, which match the background color of the default DokuWiki theme
  - Images are looking good with activated borders
- Upgrade to reveal.js 3.4.1

[0]: https://github.com/hakimel/reveal.js#pdf-export
[1]: https://github.com/hakimel/reveal.js#speaker-notes
[2]: https://github.com/hakimel/reveal.js#fragments
[3]: http://lab.hakim.se/reveal-js/#/7/1

### 2016-09-15

  * Merge commits from ogobrecht enabling to override configuration options and improve IE support


###     2016-09-07

   * Add option to open presentation in new window and make this behavior default
   * Nicer description of the reveal.js options


###     2016-02-11

   * fix listitem_open method signature


###     2016-02-04


  * Upgrade to reveal.js 3.2

###     2015-07-09

  *  Upgrade to reveal.js 3.1


###  2015-03-28

  *  add support for footers
  *  add option to insert a button which starts the presentation together with theme selection similar to the S5 plugin
  *  slide background are also supported


###     2015-01-27

  *  Add background syntax.


###     2015-01-15

  * Update to reveal.js 3.0.
  * Substitute theme beige_white by the new reveal.js theme white.
  * The theme beige_white is removed.
  * Default values have changed. Controls are not shown by default anymore.


###    2014-12-09

  * Add configuration option to build all lists by default
  * Remove black border in theme “beige_white”


###    2014-10-31

 *  Load MathJax over secure connection if available


###    2014-10-28

 * Add support for the Reveal.js (highlight.js) syntax highlighter


###    2014-10-11
  * Initial release
