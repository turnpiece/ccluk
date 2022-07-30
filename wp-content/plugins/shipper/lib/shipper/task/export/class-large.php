<?php
/**
 * Shipper export tasks: upload archive
 *
 * Triggers once local export is done, and transmits the
 * generated archive to remote storage.
 *
 * @package shipper
 */

/**
 * Export upload class
 */
class Shipper_Task_Export_Large extends Shipper_Task_Export {

	/**
	 * Actually uploads the exported archive
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration();
		$large     = new Shipper_Model_Dumped_Largelist();
		$dest_root = Shipper_Helper_Fs_Path::clean_fname( $migration->get_destination() );

		$pos         = $this->get_initialized_position();
		$shipper_pos = $pos;

		$data = $large->get_statements( $pos );
		if ( empty( $data ) ) {
			return true;
		}

		if ( ! empty( $data ) ) {
			$data = $data[0];
		}
		$path = $data['source'];

		if ( ! is_readable( $path ) ) {
			Shipper_Helper_Log::write(
				sprintf( 'File unreadable: %s', $path )
			);
			$this->set_initialized_position( $shipper_pos + 1 );

			return false;
		}

		$filesize = filesize( $path );
		Shipper_Helper_Log::write(
			sprintf( 'Uploading %1$s, size: %2$s', $path, size_format( $filesize ) )
		);
		$remote = new Shipper_Helper_Fs_Remote();
		try {
			$progress                = $remote->upload(
				$path,
				trailingslashit( $dest_root ) . $data['destination']
			);
			$this->has_done_anything = true;
		} catch ( Exception $e ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$s %2$s: file path, and message. */
					__( 'Unable to upload %1$s: %2$s', 'shipper' ),
					$path,
					$e->getMessage()
				)
			);
			$this->set_initialized_position( $shipper_pos + 1 );

			return false;
		}

		if ( $progress->has_error() ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$s %2$s: file path, and message. */
					__( 'Unable to upload %1$s: %2$s', 'shipper' ),
					$path,
					$progress->get_error()
				)
			);
			$this->set_initialized_position( $shipper_pos + 1 );

			return false;
		}

		if ( $progress->is_done() ) {
			Shipper_Helper_Log::write( 'upload done => ' . $path );
			$shipper_pos ++;

			Shipper_Helper_Log::write( 'shipper pos after upload => ' . $shipper_pos );

			if ( ! $this->set_initialized_position( $shipper_pos ) ) {
				// List got re-initialized - cancel.
				return true;
			}
		}

		// We are not done until we deplete large files!
		return false;
	}

	/**
	 * Gets total steps this task is expected to take
	 *
	 * @return int
	 */
	public function get_total_steps() {
		$total = $this->get_total_files();
		if ( 0 === $total ) {
			return 0;
		}

		$total ++;

		return $total;
	}

	/**
	 * Gets where we are with the task
	 *
	 * @return int
	 */
	public function get_current_step() {
		$current = $this->get_initialized_position();
		if ( ! $this->has_done_anything() ) {
			return 0;
		}
		$current ++;

		return $current;
	}

	/**
	 * Gets the task source path
	 *
	 * Proxies archive path getting.
	 *
	 * @param string $path Unused.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string.
	 */
	public function get_source_path( $path, $migration ) {
		return $this->get_archive_path( $migration->get( 'destination' ) );
	}

	/**
	 * Gets destination type
	 *
	 * @TODO implement
	 */
	public function get_destination_type() {
		return 'cloud';
	}

	/**
	 * Gets current queue position, initializing it if necessary
	 *
	 * @param object $filelist Stored filelist model to use (optional, used in tests).
	 *
	 * @return int
	 */
	public function get_initialized_position( $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$pos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $pos ) {
			$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, 0 );
			$filelist->save();
			$pos = 0;
		}

		return $pos;
	}

	/**
	 * Sets current queue position
	 *
	 * Shorts out if necessary.
	 *
	 * @param int    $position Position to set.
	 * @param object $filelist Stored filelist model instance to use (optional, used in tests).
	 *
	 * @return bool False if short-circuited, true on success
	 */
	public function set_initialized_position( $position, $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$newpos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $newpos ) {
			return false;
		}

		$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, $position );
		$filelist->save();

		return true;
	}

	/**
	 * Gets total files to be processed by the task
	 *
	 * @param object $dumped Dumped largelist instance to use (optional).
	 *
	 * @return int
	 */
	public function get_total_files( $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Largelist();
		}

		return $dumped->get_statements_count();
	}

	/**
	 * Gets task job description
	 *
	 * @return string
	 */
	public function get_work_description() {
		$desc = sprintf(
			/* translators: %1$s %2$s: current step, and step total. */
			__( '( %1$d of %2$d total )', 'shipper' ),
			$this->get_current_step(),
			$this->get_total_steps()
		);

		return sprintf(
			/* translators: %s: task description. */
			__( 'Upload large files %s', 'shipper' ),
			$desc
		);
	}
}