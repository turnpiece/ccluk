<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Upgrade
 *
 * Handle any installation upgrade or install tasks
 */
class Forminator_Upgrade {

	/**
	 * Initialise data before plugin is fully loaded
	 *
	 * @since 1.0
	 */
	public static function init() {

		/**
		 * Initialize the plugin data
		 */
		$old_version = get_site_option( 'forminator_version' );
		if ( $old_version ) {
			$version_changed = version_compare( $old_version, FORMINATOR_VERSION, 'lt' );
		} else {
			$version_changed = true;
		}
		if ( $version_changed ) {
			Forminator_Database_Tables::install_database_tables();
			update_site_option( 'forminator_version', FORMINATOR_VERSION );
		}

	}
}