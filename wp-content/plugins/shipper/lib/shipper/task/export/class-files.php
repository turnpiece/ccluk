<?php
/**
 * Shipper tasks: files export
 *
 * Will export files to a ZIP archive, ready for migration.
 *
 * @package shipper
 */

/**
 * Files export task class
 */
class Shipper_Task_Export_Files extends Shipper_Task_Export {

	/**
	 * Task runner method
	 *
	 * Returns (bool)true when the export is done, and
	 * (bool)false otherwise.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration;

		$storage      = new Shipper_Model_Stored_Filelist;
		$this->_files = new Shipper_Helper_Fs_List( $storage );
		$dumped       = new Shipper_Model_Dumped_Filelist;
		$large        = new Shipper_Model_Dumped_Largelist;

		if ( $this->_files->is_done() ) {
			return true;
		}

		// Update status flag first.
		$this->_has_done_anything = true;

		$exclusions = new Shipper_Model_Fs_Blacklist;
		$exclusions->add_directory(
			Shipper_Helper_Fs_Path::get_working_dir()
		);
		$exclusions->add_directory(
			Shipper_Helper_Fs_Path::get_log_dir()
		);

		foreach ( $this->_files->get_files() as $item ) {
			if ( empty( $item['path'] ) ) {
				continue;
			}

			$source = $this->get_source_path( $item['path'], $migration );
			if ( empty( $source ) ) {
				continue;
			}

			$destination = $this->get_destination_path( $item['path'] );

			if ( ! is_readable( $source ) ) {
				$this->add_error(
					self::ERR_ACCESS,
					sprintf( __( 'Shipper couldn\'t read file: %s', 'shipper' ), $source )
				);
			}

			if ( $exclusions->is_excluded( $item['path'] ) ) {
				Shipper_Helper_Log::debug(
					sprintf(
						__( 'Skipping excluded item: %s', 'shipper' ),
						$item['path']
					)
				);
				continue;
			}

			$target_line = array(
				'source'      => $source,
				'destination' => $destination,
				'size'        => $item['size'],
			);
			if ( $item['size'] > Shipper_Model_Stored_Migration::get_file_size_threshold() ) {
				$large->add_statement( $target_line );
			} else {
				$dumped->add_statement( $target_line );
			}
		}

		$dumped->close();
		$large->close();

		return $this->_files->is_done();
	}

	/**
	 * Gets readable source path for a file.
	 *
	 * As a side-effect, will migrate individual config file
	 * and return a path to temp file with changes.
	 *
	 * @param string $path Absolute file path.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string|false
	 */
	public function get_source_path( $path, $migration ) {
		if ( ! Shipper_Helper_Fs_Path::is_config_file( $path ) ) {
			return $path;
		}

		if ( Shipper_Helper_Fs_Path::is_wp_config( $path ) && ! $this->is_config_included() ) {
			Shipper_Helper_Log::write( 'Skipping wp-config in export' );

			return false;
		}

		$replacer = new Shipper_Helper_Replacer_File( Shipper_Helper_Codec::ENCODE );
		$replacer->add_codec( new Shipper_Helper_Codec_Rewrite );
		$replacer->add_codec( new Shipper_Helper_Codec_Paths );

		return $replacer->transform( $path );
	}

	/**
	 * Checks whether to include the wp-config in gathered files list
	 *
	 * @return bool
	 */
	public function is_config_included() {
		$model = new Shipper_Model_Stored_Options;

		return $model->get( Shipper_Model_Stored_Options::KEY_SKIPCONFIG );
	}

	/**
	 * Gets destination type
	 *
	 * Used for classifying output files in the ZIP structure.
	 *
	 * @return string
	 */
	public function get_destination_type() {
		return Shipper_Model_Stored_Migration::COMPONENT_FS;
	}

	/**
	 * Gets the number of steps required to finalize this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		if ( ! isset( $this->_files ) ) {
			$storage      = new Shipper_Model_Stored_Filelist;
			$this->_files = new Shipper_Helper_Fs_List( $storage );
		}

		return $this->_files->get_total_steps();
	}

	/**
	 * Gets the current position in current task finalization
	 *
	 * @return int
	 */
	public function get_current_step() {
		if ( ! isset( $this->_files ) ) {
			$storage      = new Shipper_Model_Stored_Filelist;
			$this->_files = new Shipper_Helper_Fs_List( $storage );
		}

		return $this->_files->get_current_step();
	}

	/**
	 * Gets export task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		if ( ! isset( $this->_files ) ) {
			$storage      = new Shipper_Model_Stored_Filelist;
			$this->_files = new Shipper_Helper_Fs_List( $storage );
		}
		$files = $this->_files->get_files();
		$size  = array_sum( wp_list_pluck( $files, 'size' ) );

		$current = '';
		if ( ! empty( $size ) ) {
			$current = sprintf(
				__( ' (%1$d files, %2$s)', 'shipper' ),
				count( $files ), size_format( $size )
			);
		}

		return sprintf(
			__( 'Gather files for upload %s', 'shipper' ),
			$current
		);
	}

}