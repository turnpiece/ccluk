<?php
/*
* Title		: CMB Icomoon Extension 
*Description: Adds a field Option to <a href="https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress">Custom Metaboxes and Fields</a>
* Documentation	: URI: https://github.com/retlehs/cmb-extensions
* License	:
* Version	: 0.1
* Author    : Ben Word (with modifications from Benjamin Ritner)
* Author URI: http://benword.com/
 */
 
 
$extensions = array(
  'kad_icons',
);

foreach ($extensions as $extension) {
  include_once($extension . '.php');
  add_action('cmb_render_' . $extension, 'cmb_' . $extension, 10, 2);
}

/**
 * Enqueue CSS & JS
 */
function icon_extension_admin_scripts() {
  wp_register_style('icon_extension_css', get_template_directory_uri() . '/lib/icons/css/icon-select.css', false, null);
  wp_enqueue_style('icon_extension_css');
  
  wp_register_style('icon_css', get_template_directory_uri() . '/assets/css/icons.css', false, null);
  wp_enqueue_style('icon_css');

  wp_register_script('icon_extension_js', get_template_directory_uri() . '/lib/icons/js/icon-select.js', false, null, false);
  wp_enqueue_script('icon_extension_js');
  wp_register_style('redux-field-kad-icons-css', get_template_directory_uri() . '/themeoptions/extensions/edd/kad_icons/field_kad_icons.css', time(), true);
  wp_enqueue_style('redux-field-kad-icons-css');
  wp_register_style('redux-field-kad-slides-css', get_template_directory_uri() . '/themeoptions/extensions/edd/kad_slides/field_kad_slides.css', time(), true);
  wp_enqueue_style('redux-field-kad-slides-css');
}

add_action('admin_enqueue_scripts', 'icon_extension_admin_scripts');
