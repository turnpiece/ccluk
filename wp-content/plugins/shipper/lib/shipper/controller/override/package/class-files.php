<?php
/**
 * Shipper package controllers: package files overrides.
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package file overrides implementation class
 */
class Shipper_Controller_Override_Package_Files extends Shipper_Controller_Override_Package {

	/**
	 * Word to look for (wordpress-core) skipping WordPress Core files.
	 *
	 * @since 1.2.7
	 */
	const WORDPRESS_CORE = 'wordpress-core';

	/**
	 * Get scope.
	 *
	 * @return string
	 */
	public function get_scope() {
		return Shipper_Model_Stored_PackageMeta::KEY_EXCLUSIONS_FS;
	}

	/**
	 * Apply overrides.
	 *
	 * @return void
	 */
	public function apply_overrides() {
		add_action( 'shipper_package_migration_gather_tick_before', array( $this, 'on_files_gather' ) );
		add_action( 'shipper_package_migration_gather_tick_after', array( $this, 'after_files_gather' ) );
	}

	/**
	 * On files gather callback
	 *
	 * @return void
	 */
	public function on_files_gather() {
		if ( ! in_array( self::WORDPRESS_CORE, $this->get_exclusions(), true ) ) {
			/**
			 * Skip WordPress Core files, once user will insert `wordpress-core` on file exclusion check.
			 *
			 * @since 1.2.7
			 */
			add_filter( 'shipper_blacklist_skip_wp_core', '__return_false' );
		}

		add_filter( 'shipper_exclude_self_files', '__return_false' );

		$blacklist = new Shipper_Model_Fs_Blacklist();

		$blacklisted_dirs = array(
			Shipper_Helper_Fs_Path::get_working_dir(),
			Shipper_Helper_Fs_Path::get_log_dir(),
		);

		$exclusions_on_preflight = shipper_get_relative_to_absolute_path( $this->get_exclusions() );
		$blacklisted_dirs        = array_merge( $blacklisted_dirs, $exclusions_on_preflight );

		$blacklist->add_directories( $blacklisted_dirs );
		$exclusions = new Shipper_Model_Stored_Exclusions();

		foreach ( $blacklist->get_directories() as $directory ) {
			$exclusions->set( $directory, md5( $directory ) );
		}

		$blacklist_files = array_merge( $blacklist->get_files(), shipper_get_file_extensions( $this->get_exclusions() ) );

		foreach ( $blacklist_files as $file ) {
			$exclusions->set( $file, md5( $file ) );
		}

		$exclusions->save();
	}

	/**
	 * After files gather callback
	 *
	 * @return void
	 */
	public function after_files_gather() {
		if ( ! in_array( self::WORDPRESS_CORE, $this->get_exclusions(), true ) ) {
			/**
			 * WordPress Core files were skipped, So let's remove the filter now.
			 *
			 * @since 1.2.7
			 */
			remove_filter( 'shipper_blacklist_skip_wp_core', '__return_false' );
		}

		remove_filter( 'shipper_exclude_self_files', '__return_false' );
	}
}