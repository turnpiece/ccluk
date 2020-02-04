<?php
/**
 * Shipper package export tasks: files gathering task
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Files gathering task class
 */
class Shipper_Task_Package_Gather extends Shipper_Task_Export_Files {

	public function apply( $args = array() ) {
		add_filter( 'shipper_blacklist_skip_wp_core', '__return_false' );
		add_filter( 'shipper_exclude_self_files', '__return_false' );
		/**
		 * add a timer here to make sure if the current request done too quickly, we will
		 * force it to continue instead of quit and restart with new request
		 */
		$result = parent::apply( $args );
		if ( $result ) {
			return $result;
		}

		remove_filter( 'shipper_blacklist_skip_wp_core', '__return_false' );
		remove_filter( 'shipper_exclude_self_files', '__return_false' );

		return $result;
	}

	public function is_config_included() {
		return true;
	}
}