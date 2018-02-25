<?php
/**
 * Hummingbird Page Caching
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Load necessary modules for caching.
 */

if ( ! class_exists( 'WP_Hummingbird_Module_Page_Caching' ) ) {
	if ( is_dir( WP_CONTENT_DIR . '/plugins/wp-hummingbird/' ) ) {
		$path = WP_CONTENT_DIR . '/plugins/wp-hummingbird/';
	} else {
		$path = WP_CONTENT_DIR . '/plugins/hummingbird-performance/';
	}

	include_once( $path . 'helpers/wp-hummingbird-helpers-core.php' );
	include_once( $path . 'core/class-abstract-module.php' );
	include_once( $path . 'core/modules/class-module-page-caching.php' );

	if ( ! method_exists( 'WP_Hummingbird_Module_Page_Caching', 'serve_cache' ) ) {
		return;
	}

	define( 'WPHB_ADVANCED_CACHE', true );
	WP_Hummingbird_Module_Page_Caching::serve_cache();
}