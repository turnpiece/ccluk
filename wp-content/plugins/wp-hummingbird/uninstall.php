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
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( class_exists( 'WP_Hummingbird' ) ) {
	return;
}

if ( ! class_exists( 'WP_Hummingbird_Settings' ) ) {
	/* @noinspection PhpIncludeInspection */
	include_once plugin_dir_path( __FILE__ ) . '/core/class-settings.php';
}
$settings = WP_Hummingbird_Settings::get_settings( 'settings' );

if ( $settings['remove_settings'] ) {
	delete_option( 'wphb_styles_collection' );
	delete_option( 'wphb_scripts_collection' );

	delete_option( 'wphb_process_queue' );
	delete_transient( 'wphb-minification-errors' );
	delete_option( 'wphb-minify-server-errors' );
	delete_option( 'wphb-minification-files-scanned' );

	delete_option( 'wphb_settings' );
	delete_site_option( 'wphb_settings' );

	delete_site_option( 'wphb_version' );

	delete_site_option( 'wphb-quick-setup' );
	delete_site_option( 'wphb-free-install-date' );

	delete_site_option( 'wphb-gzip-api-checked' );
	delete_site_option( 'wphb-caching-api-checked' );

	delete_site_transient( 'wphb-uptime-remotely-enabled' );

	// Clean notices.
	delete_option( 'wphb-notice-cache-cleaned-show' );   // per subsite.
	delete_site_option( 'wphb-notice-free-rated-show' ); // network wide.
	delete_site_option( 'wphb-cloudflare-dash-notice' ); // network wide.
	delete_site_option( 'wphb-cloudflare-dash-notice' );
	delete_site_option( 'wphb-notice-free-deactivated-dismissed' );
	delete_site_option( 'wphb-notice-free-deactivated-show' );
	// Asset optimization notices.
	delete_option( 'wphb-notice-http2-info-show' );
	delete_option( 'wphb-notice-minification-optimized-show' );
	// Uptime notices.
	delete_site_option( 'wphb-notice-uptime-info-show' );

	// Clean all cron.
	wp_clear_scheduled_hook( 'wphb_performance_report' );
	wp_clear_scheduled_hook( 'wphb_uptime_report' );
	if ( wp_next_scheduled( 'wphb_minify_clear_files' ) ) {
		wp_clear_scheduled_hook( 'wphb_minify_clear_files' );
	}
}


if ( $settings['remove_data'] ) {
	global $wpdb;

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

	// Reports & data.
	delete_site_option( 'wphb-caching-data' );
	delete_site_option( 'wphb-gzip-data' );

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
}
