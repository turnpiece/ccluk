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
class Shipper_Task_Export_PackageUpload extends Shipper_Task_Export {

	/**
	 * Actually uploads the exported archive
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->has_done_anything = true;

		$packages    = new Shipper_Model_Dumped_Packagelist();
		$migration   = new Shipper_Model_Stored_Migration();
		$dest_root   = Shipper_Helper_Fs_Path::clean_fname( $migration->get_destination() );
		$pos         = $this->get_initialized_position();
		$shipper_pos = $pos;
		$data        = $packages->get_statements( $pos );
		if ( empty( $data ) ) {
			// mean this is done, reset the pointer.
			return true;
		}

		if ( ! empty( $data ) ) {
			$data = $data[0];
		}
		$path = $data['source'];

		if ( ! is_readable( $path ) ) {
			Shipper_Helper_Log::write(
				/* translators: %s: file path. */
				sprintf( 'File unreadable: %s', $path )
			);
			$this->set_initialized_position( $shipper_pos + 1 );

			return false;
		}

		$filesize = filesize( $path );
		Shipper_Helper_Log::debug(
			/* translators: %1$s %2$s: file path, and file size*/
			sprintf( 'Uploading %1$s, size: %2$s', $path, size_format( $filesize ) )
		);
		$remote = new Shipper_Helper_Fs_Remote();
		try {
			$destination = trailingslashit( $dest_root ) . $data['destination'];
			$progress    = $remote->upload(
				$path,
				$destination
			);
		} catch ( Exception $e ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$s %2$s: file path, error message. */
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
					/* translators: %1$s %2$s: file path, error message. */
					__( 'Unable to upload %1$s: %2$s', 'shipper' ),
					$path,
					$progress->get_error()
				)
			);
			$this->set_initialized_position( $shipper_pos + 1 );

			return false;
		}

		if ( $progress->is_done() ) {
			$shipper_pos ++;
			if ( ! $this->set_initialized_position( $shipper_pos ) ) {
				// List got re-initialized - cancel.

				return true;
			}
			// remove the package.
			@unlink( $path );
		}

		$statements = $packages->get_statements( $shipper_pos );
		$is_done    = empty( $statements );
		if ( $is_done ) {
			$this->set_initialized_position( 0 );
		}

		// We are not done until we deplete large files!
		return $is_done;
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
	 * Get initialized position.
	 *
	 * @param false $filelist list of files.
	 *
	 * @return false|int|mixed
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
	 * Gets current queue position, initializing it if necessary
	 *
	 * @param object $filelist Stored filelist model to use (optional, used in tests).
	 *
	 * @return int
	 */
	public function get_initialized_position1( $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$pos          = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		$current_task = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURRENT_TASK, false );
		if ( __CLASS__ !== $current_task ) {
			$pos = false;
		}
		if ( false === $pos ) {
			$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, 0 );
			$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURRENT_TASK, __CLASS__ );
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
			$dumped = new Shipper_Model_Dumped_Packagelist();
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
			/* translators: %1$s %2$s: current step and total step. */
			__( '( %1$d of %2$d total )', 'shipper' ),
			$this->get_current_step(),
			$this->get_total_steps()
		);

		return sprintf(
			/* translators: %s: description. */
			__( 'Upload package %s', 'shipper' ),
			$desc
		);
	}
}