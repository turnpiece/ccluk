<?php
/**
 * Shipper check tasks: files preflight check
 *
 * @package shipper
 */

/**
 * Files check task class
 */
class Shipper_Task_Check_Files extends Shipper_Task_Check {

	/**
	 * Internal lister instance
	 *
	 * @var object Shipper_Helper_Fs_List instance
	 */
	private $_fs;

	/**
	 * Runs the task
	 *
	 * @param array $args Unused.
	 *
	 * @return bool Whether we encountered any errors
	 */
	public function apply( $args = array() ) {
		$chks = array(
			'file_sizes',
			'file_names',
			'package_sizes',
		);
		$storage = new Shipper_Model_Stored_Filelist;
		$fs = new Shipper_Helper_Fs_List( $storage );
		$fs->set_excludable( false ); // List everything.
		$this->set_fs( $fs );

		foreach ( $chks as $chk ) {
			$method = "are_{$chk}_valid";
			if ( is_callable( array( $this, $method ) ) ) {
				$check = call_user_func( array( $this, $method ), $storage );
				$this->add_check( $check );
			}
		}

		return $this->has_errors();
	}

	/**
	 * Internal filesystem lister object setter
	 *
	 * Used in tests.
	 *
	 * @param object $fs Shipper_Helper_Fs_List instance.
	 */
	public function set_fs( $fs ) {
		$this->_fs = $fs;
	}

	/**
	 * Checks if the file sizes are within limits
	 *
	 * @param object $storage Shipper_Model_Stored_Filelist instance.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function are_file_sizes_valid( $storage ) {
		$check = new Shipper_Model_Check( __( 'Large Files', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		$threshold = Shipper_Model_Stored_Migration::get_file_size_threshold();

		$files = isset( $this->_fs ) ? $this->_fs->get_files() : array();

		$oversized = $storage->get( 'oversized', array() );

		foreach ( $files as $file ) {
			if ( ! is_array( $file ) ) { continue; }
			if ( ! empty( $file['path'] ) ) {
				$myself = wp_normalize_path( dirname( SHIPPER_PLUGIN_FILE ) );
				$filepath = wp_normalize_path( $file['path'] );
				if ( preg_match(
					'/^' . preg_quote( $myself, '/' ) . '/',
					$filepath
				) ) { continue; }
			}
			if ( $file['size'] > $threshold ) {
				$oversized[] = $file;
			}
		}

		$storage->set( 'oversized', $oversized );
		$storage->save();

		if ( ! empty( $oversized ) ) {
			$ex_model = new Shipper_Model_Stored_Exclusions;
			$exclusions = array_keys( $ex_model->get_data() );
			foreach ( $oversized as $oversized_item ) {
				if ( ! in_array( $oversized_item['path'], $exclusions, true ) ) {
					$status = Shipper_Model_Check::STATUS_WARNING;
					break;
				}
			}
			$tpl = new Shipper_Helper_Template;
			$markup = $tpl->get('modals/check/preflight-row-files', array(
				'files' => $oversized,
			));
			$check->set( 'message', $markup );
		}
		$check->set( 'check_type', 'file_sizes' );

		return $check->complete( $status );
	}

	/**
	 * Checks if the file paths are valid
	 *
	 * @param object $storage Shipper_Model_Stored_Filelist instance.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function are_file_names_valid( $storage ) {
		$check = new Shipper_Model_Check( __( 'Filenames', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		$threshold = 256; // For Win32.

		$files = isset( $this->_fs ) ? $this->_fs->get_files() : array();

		$invalid = $storage->get( 'invalid', array() );

		foreach ( $files as $file ) {
			if ( ! is_array( $file ) ) { continue; }
			if ( strlen( $file['path'] ) > $threshold && count( $invalid ) < 100 ) {
				$invalid[] = $file;
			}
		}

		$storage->set( 'invalid', $invalid );
		$storage->save();

		if ( ! empty( $invalid ) ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			$tpl = new Shipper_Helper_Template;
			$markup = $tpl->get('modals/check/preflight-row-files', array(
				'files' => $invalid,
			));
			$check->set( 'message', $markup );
		}
		$check->set( 'check_type', 'file_names' );

		return $check->complete( $status );
	}

	/**
	 * Checks if the overall package size is within limits
	 *
	 * @param object $storage Shipper_Model_Stored_Filelist instance.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function are_package_sizes_valid( $storage ) {
		$check = new Shipper_Model_Check( __( 'Package size', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		$threshold = Shipper_Model_Stored_Migration::get_package_size_threshold();

		$files_total_size = $storage->get( 'files_total_size', 0 );
		$exclusions_model = new Shipper_Model_Stored_Exclusions;
		$exclusions = array_keys( $exclusions_model->get_data() );

		$files = isset( $this->_fs ) ? $this->_fs->get_files() : array();

		foreach ( $files as $file ) {
			if ( ! is_array( $file ) ) { continue; }
			if ( in_array( $file['path'], $exclusions, true ) ) { continue; } // Already in exclusion list, let's not worry about it.
			$files_total_size += $file['size'];
		}

		$storage->set( 'files_total_size', $files_total_size );
		$storage->save();

		$db_size = $storage->get( 'db_total_size', 0 );
		if ( empty( $db_size ) ) {
			$db_size = $this->update_db_size( $storage );
		}
		$package_size = $db_size + $files_total_size;

		$estimate = new Shipper_Model_Stored_Estimate;
		$estimate->set( 'package_size', $package_size )->save();

		if ( $package_size > $threshold ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
		}
		$check->set( 'check_type', 'package_size' );

		$tpl = new Shipper_Helper_Template;
		$check->set(
			'message',
			$tpl->get('pages/preflight/wizard-files-package_size-full', array(
				'package_size' => $package_size,
				'files_size' => $files_total_size,
				'db_size' => $db_size,
				'threshold' => $threshold,
			))
		);
		$check->set(
			'short_message',
			$tpl->get('pages/preflight/wizard-files-package_size-summary', array(
				'package_size' => $package_size,
				'files_size' => $files_total_size,
				'db_size' => $db_size,
				'threshold' => $threshold,
			))
		);

		return $check->complete( $status );
	}

	/**
	 * Gets the DB size info
	 *
	 * Updates the cached size as a side-effect.
	 *
	 * @param object $storage Shipper_Model_Stored_Filelist instance.
	 *
	 * @return int Overall DB size
	 */
	public function update_db_size( $storage ) {
		global $wpdb;
		$result = (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT sum(data_length+index_length) FROM information_schema.tables WHERE table_schema=%s GROUP BY table_schema', DB_NAME )
		);
		$storage->set( 'db_total_size', $result );
		return $result;
	}

	/**
	 * Checks if we're done querying the FS
	 *
	 * @return bool
	 */
	public function is_done() {
		if ( ! isset( $this->_fs ) ) { return false; }
		return $this->_fs->is_done();
	}

	/**
	 * Discard any stored check progress
	 *
	 * @return bool
	 */
	public function restart() {
		$storage = new Shipper_Model_Stored_Filelist;

		$storage->clear();
		$storage->save();

		return true;
	}
}