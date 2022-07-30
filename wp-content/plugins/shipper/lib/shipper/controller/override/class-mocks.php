<?php
/**
 * Shipper controllers: mocks overrides
 *
 * @package shipper
 */

/**
 * Mocks overrides controller class
 */
class Shipper_Controller_Override_Mocks extends Shipper_Controller_Override {

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		$this->prepare_quick_exports();
		$this->prepare_import_mocks();

		// Force local API mock mode.
		if ( $this->get_constants()->get( 'SHIPPER_MOCK_API' ) ) {
			add_filter( 'shipper_api_mock_local', '__return_true' );
		}
	}

	/**
	 * Prepares the filters for quick exports
	 */
	public function prepare_quick_exports() {
		$constants = $this->get_constants();

		$fs = $constants->get( 'SHIPPER_QUICK_EXPORT_FS' );
		$db = $constants->get( 'SHIPPER_QUICK_EXPORT_DB' );
		// Force whole migration process to be very short, useful for quick tests.
		if ( $constants->is_defined( 'SHIPPER_QUICK_EXPORT' ) ) {
			$fs = $constants->is_defined( 'SHIPPER_QUICK_EXPORT_FS' )
				? $fs
				: true;
			$db = $constants->is_defined( 'SHIPPER_QUICK_EXPORT_DB' )
				? $db
				: true;
		}

		// Force-skip content files.
		if ( ! empty( $fs ) ) {
			add_filter( 'shipper_path_include_file', array( $this, 'skip_content_files' ), 10, 2 );
		}

		// Force-skip most tables.
		if ( ! empty( $db ) ) {
			add_filter( 'shipper_path_include_table', array( $this, 'skip_most_tables' ), 10, 2 );
		}
	}

	/**
	 * Prepares the filters for import mocking
	 */
	public function prepare_import_mocks() {
		$constants = $this->get_constants();

		$fs = $constants->get( 'SHIPPER_MOCK_IMPORT_FS' );
		$db = $constants->get( 'SHIPPER_MOCK_IMPORT_DB' );
		// Force whole migration import process into mock mode.
		if ( $constants->is_defined( 'SHIPPER_MOCK_IMPORT' ) ) {
			$fs = $constants->is_defined( 'SHIPPER_MOCK_IMPORT_FS' )
				? $fs
				: true;
			$db = $constants->is_defined( 'SHIPPER_MOCK_IMPORT_DB' )
				? $db
				: true;
		}

		// Force-mock files import.
		if ( ! empty( $fs ) ) {
			add_filter( 'shipper_import_mock_files', '__return_true' );
		}

		// Force-mock tables import.
		if ( ! empty( $db ) ) {
			add_filter( 'shipper_import_mock_tables', '__return_true' );
		}
	}

	/**
	 * Force-skip content files
	 *
	 * @param bool   $include Whether to include this file.
	 * @param string $path File path.
	 *
	 * @return bool
	 */
	public function skip_content_files( $include, $path ) {
		if ( empty( $include ) ) {
			return $include; }
		return ! preg_match( '/' . preg_quote( WP_CONTENT_DIR, '/' ) . '/', $path );
	}

	/**
	 * Force-skip most tables
	 *
	 * Skip all tables except options and sitemeta.
	 *
	 * @param bool   $include Whether to include this table.
	 * @param string $table Table name.
	 *
	 * @return bool
	 */
	public function skip_most_tables( $include, $table ) {
		if ( empty( $include ) ) {
			return $include; }
		global $table_prefix;
		return "{$table_prefix}options" === $table ||
			"{$table_prefix}sitemeta" === $table ||
			"{$table_prefix}blogs" === $table;
	}
}