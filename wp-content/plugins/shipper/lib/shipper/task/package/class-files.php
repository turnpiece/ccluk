<?php
/**
 * Shipper tasks: files packaging task
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Files packaging class
 */
class Shipper_Task_Package_Files extends Shipper_Task_Package {

	private $_current_position;

	public function get_total_steps() {
		$dumped = new Shipper_Model_Dumped_Filelist;

		return $dumped->get_statements_count();
	}

	public function get_current_step() {
		return ! empty( $this->_current_position )
			? $this->_current_position
			: $this->get_initialized_position();
	}

	public function apply( $args = array() ) {
		$position = $this->get_initialized_position();
		$files    = $this->get_next_files();

		if ( empty( $files ) ) {
			// We are done now. Clear marker and move on to next task.
			$this->set_initialized_position( 0 );
			// Also set totals, for when we get asked again.
			$this->_current_position = $this->get_total_steps();

			return true;
		}

		$zip = self::get_zip();

		foreach ( $files as $file ) {
			if ( empty( $file['source'] ) || empty( $file['destination'] ) ) {
				continue;
			}

			$source = $file['source'];
			if ( ! is_readable( $source ) ) {
				Shipper_Helper_Log::write(
					"Skipping unreadable source {$source}"
				);
				continue;
			}

			$destination = wp_normalize_path( $file['destination'] );

			if ( ! $zip->add_file( $source, $destination ) ) {
				throw new Shipper_Exception(
					sprintf( __( 'Shipper couldn\'t archive file: %s', 'shipper' ), $source )
				);

				return false;
			}

		}
		$zip->close();

		$this->set_initialized_position( $position + count( $files ) );

		return false;
	}

	/**
	 * Gets the next chunk of file statements to process
	 *
	 * @param object $dumped Optional Shipper_Model_Dumped_Filelist instance (used in tests).
	 *
	 * @return array
	 */
	public function get_next_files( $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Filelist;
		}

		$pos = $this->get_initialized_position();

		/**
		 * Number of file statements to package in one step
		 *
		 * @param int $limit Maximum number of files.
		 *
		 * @return int
		 */
		$max_statements = (int) apply_filters(
			'shipper_export_max_package_statements',
			250
		);

		return $dumped->get_statements( $pos, $max_statements );
	}

	public function get_initialized_position( $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist;
		}

		$pos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $pos ) {
			$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, 0 );
			$filelist->save();
			$pos = 0;
		}

		return $pos;
	}

	public function set_initialized_position( $position, $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist;
		}

		$newpos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $newpos ) {
			return false;
		}

		$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, $position );
		$filelist->save();

		return true;
	}
}