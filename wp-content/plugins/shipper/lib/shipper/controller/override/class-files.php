<?php

/**
 * Author: Hoang Ngo
 */
class Shipper_Controller_Override_Files extends Shipper_Controller_Override {
	public function apply_overrides() {
		$model      = new Shipper_Model_Stored_MigrationExclusion();
		$exclusions = $model->get( Shipper_Model_Stored_MigrationExclusion::KEY_EXCLUSIONS_FS );
		if ( empty( $exclusions ) ) {
			return false;
		}

		add_filter(
			'shipper_path_include_file',
			array( $this, 'maybe_include' ), 10, 2
		);
	}

	/**
	 * Excludes files according to package settings
	 *
	 * @param bool $include Whether to include a path.
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function maybe_include( $include, $path ) {
		if ( empty( $include ) ) {
			return $include;
		}
		$model      = new Shipper_Model_Stored_MigrationExclusion();
		$exclusions = $model->get( Shipper_Model_Stored_MigrationExclusion::KEY_EXCLUSIONS_FS );
		$exclusions = array_filter( $exclusions );
		if ( empty( $exclusions ) ) {
			return $include;
		}

		$result = false;
		foreach ( $exclusions as $exclusion ) {
			$result = (bool) stristr( $path, $exclusion );
			if ( ! empty( $result ) ) {
				break;
			}
		}

		return ! $result;
	}

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		add_action( 'shipper_before_process_package_or_files', array( &$this, 'apply_overrides' ) );
	}
}