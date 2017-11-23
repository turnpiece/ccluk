<?php

/**
 * Flush all WP Hummingbird Cache
 */
function wphb_flush_cache( $clear_settings = true ) {
	// Minification data.
	wphb_clear_minification_cache( $clear_settings );

	// GZip data.
	wphb_clear_gzip_cache();
	wphb_unsave_htaccess( 'gzip' );

	// Caching data.
	wphb_clear_caching_cache();
	wphb_unsave_htaccess( 'caching' );

	// Last report.
	wphb_performance_clear_cache();

	// Last Uptime report.
	wphb_uptime_clear_cache();

	if ( $clear_settings ) {
		wphb_cloudflare_disconnect();
	}

	delete_metadata( 'user', '', 'wphb-server-type', '', true );
	delete_site_option( 'wphb-is-cloudflare' );
}

/**
 * Clear all data saved in Minification
 *
 * @param bool $clear_settings If set to true will set Minification settings to default (that includes files positions).
 */
function wphb_clear_minification_cache( $clear_settings = true ) {
	if ( wphb_can_execute_php() ) {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = wphb_get_module( 'minify' );
		$minify_module->clear_cache( $clear_settings );
	}
	// Clear page caching.
	wphb_clear_page_cache();
}

/**
 * Delete all the pending process queue for minification
 */
function wphb_delete_pending_process_queue() {
	WP_Hummingbird_Module_Minify::clear_pending_process_queue();
}


/**
 * Clear GZip cache
 */
function wphb_clear_gzip_cache() {
	$gzip_module = wphb_get_module( 'gzip' );
	/* @var WP_Hummingbird_Module_GZip $gzip_module */
	$gzip_module->clear_analysis_data();
}

/**
 * Clear the Caching Module cache
 */
function wphb_clear_caching_cache() {
	$module = wphb_get_module( 'caching' );
	/* @var WP_Hummingbird_Module_Caching $module */
	$module->clear_analysis_data();
}

function wphb_clear_page_cache() {
	/* @var WP_Hummingbird_Module_Page_Caching $module */
	$module = wphb_get_module( 'page-caching' );
	$module->purge_cache_dir();
}

function wphb_performance_clear_cache() {
	WP_Hummingbird_Module_Performance::clear_cache();
}

function wphb_uptime_clear_cache() {
	WP_Hummingbird_Module_Uptime::clear_cache();
}