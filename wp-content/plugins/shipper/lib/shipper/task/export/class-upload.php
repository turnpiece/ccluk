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
class Shipper_Task_Export_Upload extends Shipper_Task_Export {

	/**
	 * Actually uploads the exported archive
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->has_done_anything = true;

		$remote = new Shipper_Helper_Fs_Remote();
		$dumped = new Shipper_Model_Dumped_Filelist();

		$pos         = $this->get_initialized_position();
		$shipper_pos = $pos;

		$is_done = true;

		$migration = new Shipper_Model_Stored_Migration();
		$dest_root = Shipper_Helper_Fs_Path::clean_fname( $migration->get_destination() );

		$batch = array();

		/**
		 * Number of file statements to upload in one step
		 *
		 * @since v1.0.1
		 *
		 * @param int $limit Maximum number of files.
		 *
		 * @return int
		 */
		$max_statements = (int) apply_filters(
			'shipper_export_max_upload_statements',
			100
		);
		$statements     = $dumped->get_statements( $pos, $max_statements );
		foreach ( $statements as $data ) {
			$destination = trailingslashit( $dest_root ) . $data['destination'];
			$cmd         = $remote->get_upload_command( $data['source'], $destination );
			if ( ! empty( $cmd ) ) {
				// Only add to batch if we were able to create the upload command.
				$batch[] = $cmd;
			}
		}

		if ( ! empty( $batch ) ) {
			$status = $remote->execute_batch_queue( $batch );
			if ( empty( $status ) ) {
				Shipper_Helper_Log::write( 'Something went wrong uploading batch, will re-try' );
				// Attempt re-try.
				return false;
			}
		}
		$batch = null;

		$is_done     = empty( $statements );
		$shipper_pos = $is_done
			? 0
			: $shipper_pos + count( $statements );

		if ( ! $this->set_initialized_position( $shipper_pos ) ) {
			// List got re-initialized - cancel.
			return true;
		}

		return $is_done;
	}

	/**
	 * Gets total steps this task is expected to take
	 *
	 * @return int
	 */
	public function get_total_steps() {
		$total = $this->get_total_files();
		return $total;
	}

	/**
	 * Gets where we are with the task
	 *
	 * @return int
	 */
	public function get_current_step() {
		$current = $this->get_initialized_position();
		if ( empty( $current ) && $this->has_done_anything() ) {
			return $this->get_total_files();
		}
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
	 * Get initialized position
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
	 * Set initialized position
	 *
	 * @param int   $position file cursor position.
	 * @param false $filelist list of files.
	 *
	 * @return bool
	 */
	public function set_initialized_position( $position, $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$newpos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $newpos ) {
			return false; }

		$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, $position );
		$filelist->save();

		return true;
	}

	/**
	 * Get total files
	 *
	 * @param false $dumped list of total files.
	 *
	 * @return int
	 */
	public function get_total_files( $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Filelist();
		}
		return $dumped->get_statements_count();
	}

	/**
	 * Gets task job description
	 *
	 * @return string
	 */
	public function get_work_description() {
		// If current position is 0 and the task has already begun, which means we are out of files to upload, return the total files count instead.
		$pos = ( empty( $this->get_initialized_position() ) && $this->has_done_anything() ) ? $this->get_total_files() : $this->get_initialized_position();

		$desc = sprintf(
			/* translators: %1$d %2$d: position and total file count. */
			__( '( %1$d of %2$d total )', 'shipper' ),
			$pos,
			$this->get_total_files()
		);
		return sprintf(
			/* translators: %s: gathered files. */
			__( 'Upload gathered files %s', 'shipper' ),
			$desc
		);
	}
}