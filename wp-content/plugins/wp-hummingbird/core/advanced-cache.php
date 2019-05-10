<?php
/**
 * Hummingbird Advanced Tools module
 *
 * @package Hummingbird
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Load necessary modules for caching.
 */

if ( ! class_exists( 'WP_Hummingbird_Module_Page_Cache' ) ) {
	if ( is_dir( WP_CONTENT_DIR . '/plugins/wp-hummingbird/' ) ) {
		$path = WP_CONTENT_DIR . '/plugins/wp-hummingbird/';
	} else {
		$path = WP_CONTENT_DIR . '/plugins/hummingbird-performance/';
	}

	include_once $path . 'core/class-utils.php';
	include_once $path . 'core/class-abstract-module.php';
	include_once $path . 'core/modules/class-module-page-cache.php';

	if ( ! method_exists( 'WP_Hummingbird_Module_Page_Cache', 'serve_cache' ) ) {
		return;
	}

	define( 'WPHB_ADVANCED_CACHE', true );
	WP_Hummingbird_Module_Page_Cache::serve_cache();
}
