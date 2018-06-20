<?php
/**
 * Uninstall file.
 *
 * @package Hummingbird
 */

// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( class_exists( 'WP_Hummingbird' ) ) {
	return;
}

global $wpdb;

delete_option( 'wphb_styles_collection' );
delete_option( 'wphb_scripts_collection' );

$option_names = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT option_name FROM $wpdb->options
					WHERE option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s",
		'%wphb-min-scripts%',
		'%wphb-scripts%',
		'%wphb-min-styles%',
		'%wphb-styles%',
		'%wphb-last-report%'
	)
); // Db call ok; no-cache ok.

foreach ( $option_names as $name ) {
	delete_option( $name );
}

delete_option( 'wphb_process_queue' );
delete_transient( 'wphb-minification-errors' );
delete_option( 'wphb-minify-server-errors' );
delete_option( 'wphb-minification-files-scanned' );

delete_option( 'wphb_settings' );
delete_site_option( 'wphb_settings' );

delete_site_option( 'wphb_version' );
delete_site_option( 'wphb-pro' );

delete_site_option( 'wphb-quick-setup' );
delete_site_option( 'wphb-free-install-date' );

delete_site_option( 'wphb-caching-data' );
delete_site_option( 'wphb-gzip-data' );

delete_site_option( 'wphb-last-report' );

// Clean notices.
delete_option( 'wphb-notice-cache-cleaned-show' );   // per subsite
delete_site_option( 'wphb-notice-free-rated-show' ); // network wide
delete_site_option( 'wphb-cloudflare-dash-notice' ); // network wide
// Asset optimization notices.
delete_option( 'wphb-notice-http2-info-show' );
delete_option( 'wphb-notice-minification-optimized-show' );
// Uptime notices.
delete_site_option( 'wphb-notice-uptime-info-show' );

// Clean all cron.
wp_clear_scheduled_hook( 'wphb_performance_scan' );

if ( ! class_exists( 'WP_Hummingbird_Filesystem' ) ) {
	/* @noinspection PhpIncludeInspection */
	include_once plugin_dir_path( __FILE__ ) . '/core/class-filesystem.php';
}
$fs = WP_Hummingbird_Filesystem::instance();
if ( ! is_wp_error( $fs->status ) ) {
	$fs->clean_up();
}

if ( ! class_exists( 'WP_Hummingbird_Logger' ) ) {
	/* @noinspection PhpIncludeInspection */
	include_once plugin_dir_path( __FILE__ ) . '/core/class-logger.php';
}
WP_Hummingbird_Logger::cleanup();