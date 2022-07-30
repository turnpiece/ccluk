<?php
/**
 * Shipper controllers: paths overrides
 *
 * @package shipper
 */

/**
 * Paths overrides controller class
 */
class Shipper_Controller_Override_Paths extends Shipper_Controller_Override {

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		$this->preprocess_constants();
		$constants = $this->get_constants();

		if ( $constants->get( 'PATH_PROCESS_PATHS_LIMIT' ) ) {
			add_filter(
				'shipper_path_process_paths_limit',
				array( $this, 'apply_path_processing_limit' )
			);
		}

		if ( $constants->get( 'WORKING_DIRECTORY_ROOT' ) ) {
			add_filter(
				'shipper_paths_working_dir_root',
				array( $this, 'apply_working_directory_root' )
			);
		}
		if ( $constants->get( 'WORKING_DIRECTORY' ) ) {
			add_filter(
				'shipper_paths_working_dir',
				array( $this, 'apply_working_directory' )
			);
		}
	}

	/**
	 * Preprocesses constants to apply options
	 *
	 * @param object $model Optional Shipper_Model_Stored_Options instance (used in tests).
	 */
	public function preprocess_constants( $model = false ) {
		$constants = $this->get_constants();
		if ( ! is_object( $model ) ) {
			$model = new Shipper_Model_Stored_Options();
		}

		if ( $model->get( Shipper_Model_Stored_Options::KEY_UPLOADS ) ) {
			$path = Shipper_Helper_Fs_Path::get_uploads_dir() . 'shipper-working';
			$constants->add_override( 'WORKING_DIRECTORY_ROOT', $path );
		}
	}

	/**
	 * Applies path processing limit override
	 *
	 * @param int $limit Path limit.
	 *
	 * @return int
	 */
	public function apply_path_processing_limit( $limit ) {
		$override = $this->get_constants()->get( 'PATH_PROCESS_PATHS_LIMIT' );
		return ! empty( $override )
			? $override
			: $limit;
	}

	/**
	 * Applies working directory override
	 *
	 * @param string $path Directory path.
	 *
	 * @return string
	 */
	public function apply_working_directory( $path ) {
		$override = $this->get_constants()->get( 'WORKING_DIRECTORY' );
		return ! empty( $override )
			? $override
			: $path;
	}

	/**
	 * Applies working directory root override
	 *
	 * @param string $path Directory root path.
	 *
	 * @return string
	 */
	public function apply_working_directory_root( $path ) {
		$override = $this->get_constants()->get( 'WORKING_DIRECTORY_ROOT' );
		return ! empty( $override )
			? $override
			: $path;
	}
}