<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Module_Minify_Housekeeper
 *
 * Housekeeping duties to clean old minification files
 */
class WP_Hummingbird_Module_Minify_Housekeeper {

	/**
	 * Init method.
	 */
	public function init() {
		if ( ! wp_next_scheduled( 'wphb_minify_clear_files' ) ) {
			wp_schedule_event( time(), 'daily', 'wphb_minify_clear_files' );
		}

		add_action( 'wphb_minify_clear_files', array( $this, 'clear_expired_groups' ) );
	}

	/**
	 * Clear all minified and expired groups
	 *
	 * Sometimes minification module will not clear them by itself because they
	 * blong to a plugin or theme that is deactivated so minification won't get them anymore.
	 * This cron job will clear the expired files once a day
	 */
	public static function clear_expired_groups() {
		$maybe_clear_page_cache = false;

		$groups = WP_Hummingbird_Module_Minify_Group::get_minify_groups();
		foreach ( $groups as $group ) {
			$instance = WP_Hummingbird_Module_Minify_Group::get_instance_by_post_id( $group->ID );
			if ( ( $instance instanceof WP_Hummingbird_Module_Minify_Group ) && $instance->is_expired() && $instance->file_id ) {
				$instance->delete_file();
				wp_delete_post( $instance->file_id, true );
				$maybe_clear_page_cache = true;
			}
		}

		if ( $maybe_clear_page_cache ) {
			self::maybe_clear_page_cache();
		}
	}

	/**
	 * When clearing expired assets, it is important that the page cache is also purged,
	 * otherwise that leads to various errors on the site.
	 *
	 * @since 2.0.0
	 */
	private static function maybe_clear_page_cache() {
		$caching_enabled    = WP_Hummingbird_Settings::get_setting( 'enabled', 'page_cache' );
		$minify_cdn_enabled = WP_Hummingbird_Settings::get_setting( 'use_cdn', 'minify' );
		if ( $caching_enabled && $minify_cdn_enabled ) {
			WP_Hummingbird_Utils::get_module( 'page_cache' )->clear_cache();
		}
	}

}
