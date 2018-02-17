<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wphb_et_divi_theme_active() {
	$theme = wp_get_theme();
	return ( 'divi' === strtolower( $theme->get( 'Name' ) ) || 'divi' === strtolower( $theme->get_template() ) );
}

if ( ! function_exists( 'wphb_divi_after_init' ) ) :
	function wphb_divi_after_init() {
		if ( wphb_et_divi_theme_active() ) {
			remove_action( 'wp_head', 'et_add_custom_css', 100 );
			add_action( 'wp_head', 'et_add_custom_css', 9999 );

			remove_action( 'wp_head', 'et_divi_add_customizer_css' );
			add_action( 'wp_head', 'et_divi_add_customizer_css', 9998 );
		}

		if ( wphb_et_visual_builder_active() || wphb_et_divi_builder_active() ) {
			add_filter( 'wp_hummingbird_is_active_module_minify', '__return_false', 500 );
		}
	}
endif;

function wphb_et_visual_builder_active() {
	return false !== strpos( $_SERVER['REQUEST_URI'], '?et_fb=1' );
}

function wphb_et_divi_builder_active() {
	return is_admin() && function_exists( 'et_builder_should_load_framework' ) && et_builder_should_load_framework();
}

function wphb_et_divi_essential_scripts() {
	return array(
		'et-builder-modules-global-functions-script',
		'et-builder-modules-script',
		'divi-custom-script',
		'et-frontend-builder', // This is already handled by `wphb_divi_after_init()` , including it here to hide it in HB dashboard.
	);
}

function wphb_et_maybe_exclude_divi_essential_scripts( $action, $handle, $type ) {
	if ( is_array( $handle ) && isset( $handle['handle'] ) ) {
		$handle = $handle['handle'];
	}

	/**
	 * Fixes issue, where background video is not loading with js error.
	 * @since 1.7.2
	 */
	if ( 'wp-mediaelement' === $handle ) {
		$data = wp_scripts()->get_data( 'mediaelement', 'data' );
		wp_scripts()->add_inline_script( 'wp-mediaelement', $data, 'before' );
	}

	if ( 'scripts' === $type && in_array( $handle, wphb_et_divi_essential_scripts() ) ) {
		return false;
	}

	return $action;
}

if ( wphb_et_divi_theme_active() || class_exists( 'ET_Builder_Plugin' ) ) {
	add_action( 'init', 'wphb_divi_after_init', 1 );
	add_filter( 'wphb_minify_resource', 'wphb_et_maybe_exclude_divi_essential_scripts', 10, 3 );
	add_filter( 'wphb_combine_resource', 'wphb_et_maybe_exclude_divi_essential_scripts', 10, 3 );
	add_filter( 'wphb_minification_display_enqueued_file', 'wphb_et_maybe_exclude_divi_essential_scripts', 10, 3 );
}