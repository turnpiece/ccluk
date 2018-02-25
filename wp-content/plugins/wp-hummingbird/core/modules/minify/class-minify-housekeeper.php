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

	function init() {
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
		$groups = WP_Hummingbird_Module_Minify_Group::get_minify_groups();
		foreach ( $groups as $group ) {
			$instance = WP_Hummingbird_Module_Minify_Group::get_instance_by_post_id( $group->ID );
			if ( ( $instance instanceof WP_Hummingbird_Module_Minify_Group ) && $instance->is_expired() && $instance->file_id ) {
				$instance->delete_file();
				wp_delete_post( $instance->file_id, true );
			}
		}
	}

}