<?php
/**
 * Shipper package controllers: package advanced settings overrides.
 *
 * Responsible for setting PHP FS/DB limits
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package advanced overrides implementation class
 */
class Shipper_Controller_Override_Package_Settings
	extends Shipper_Controller_Override_Package {

	private $_files_buffer = 0;
	private $_max_rows = 0;

	/**
	 * No scope - we're applying overrides ourself.
	 */
	public function get_scope() {
		return false;
	}

	public function apply_overrides() {
		$model = new Shipper_Model_Stored_Options;
		$this->_files_buffer = $model->get(
			Shipper_Model_Stored_Options::KEY_PACKAGE_ZIP_LIMIT
		);
		$this->_max_rows = $model->get(
			Shipper_Model_Stored_Options::KEY_PACKAGE_DB_LIMIT
		);

		if ( $this->_files_buffer ) {
			add_filter(
				'shipper_dumped_statements_max_bytes',
				array( $this, 'apply_fs_bytes_limit' )
			);
			add_filter(
				'shipper_dumped_statements_limit',
				array( $this, 'apply_fs_paths_limit' )
			);
		}

		if ( $this->_max_rows ) {
			add_filter(
				'shipper_export_tables_row_limit',
				array( $this, 'apply_db_limit' )
			);
		}
	}

	public function apply_fs_paths_limit( $limit ) {
		return $limit > 1 ? 1000 : $limit;
	}

	public function apply_fs_bytes_limit( $limit ) {
		return (int) $this->_files_buffer;
	}

	public function apply_db_limit( $limit ) {
		return (int) $this->_max_rows;
	}

}