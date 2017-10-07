<?php
/*
Plugin Name: Featured image as map marker
Description: Adds a shortcode attribute that will force your overlay map to use associated post featured image as map marker icon.
Example:     [map query="post_type=posts" overlay="yes" featured_image="yes"]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Fimm_UserPages {

	private $_use_featured_image = false;

	private function __construct() {}

	public static function serve () {
		$me = new Agm_Fimm_UserPages;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_filter('agm-shortcode-defaults', array($this, 'set_attribute_defaults'));
		add_filter('agm-shortcode-process', array($this, 'set_shortcode_flag'));
		add_filter('agm-create-tag', array($this, 'process_map'));
	}

	function set_attribute_defaults ($atts) {
		$atts['featured_image'] = false;
		return $atts;
	}

	function set_shortcode_flag ($atts) {
		if (isset($atts['featured_image']) && $atts['featured_image']) $this->_use_featured_image = true;
		return $atts;
	}

	function process_map ($map) {
		if (!$this->_use_featured_image) return $map;

		$markers = $map['markers'];
		if (!$markers) return $map;

		foreach ($markers as $mid => $marker) {
			if (!isset($marker['post_ids']) || !isset($marker['post_ids'][0])) continue;
			$post_id = $marker['post_ids'][0];
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'thumbnail');
			if (!$image) continue;
			$markers[$mid]['icon'] = $image[0];
		}
		$map['markers'] = $markers;
		$this->_use_featured_image = false; // Reset flag

		return $map;
	}
}

if (!is_admin()) Agm_Fimm_UserPages::serve();