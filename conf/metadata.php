<?php
$meta['theme'] = array('multichoice','_choices' => array('beige','black','blood','dokuwiki','league','moon',
                                                         'night','serif', 'simple', 'sky', 'solarized', 'white'));
$meta['controls'] = array('onoff');
$meta['build_all_lists'] = array('onoff');
$meta['transition'] = array('multichoice','_choices' => array('none', 'fade', 'slide', 'convex', 'concave', 'zoom'));
$meta['show_progress_bar'] = array('onoff');
$meta['open_in_new_window'] = array('onoff');
$meta['horizontal_slide_level'] = array('multichoice','_choices' => array(1, 2));
$meta['enlarge_vertical_slide_headers'] = array('onoff');
$meta['show_image_borders'] = array('onoff');
$meta['show_slide_details'] = array('onoff');
$meta['start_button'] = array('multichoice','_choices' => array('start_button.png', 'start_button.screen.png', 'start_button.local.png'));
$meta['size'] = array('string', '_pattern' => '/^(|\d+x\d+)$/');
