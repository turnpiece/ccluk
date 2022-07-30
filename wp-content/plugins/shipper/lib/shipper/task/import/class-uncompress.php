<?php
/**
 * Shipper tasks: import, non-config files copier
 *
 * This task iterates over all extracted files and classifies them to
 * config/non-config ones. The non-config files will be copied to their
 * respective new locations, while the config ones will be recorded for
 * processing in subsequent steps.
 *
 * @package shipper
 */

/**
 * Files copying class
 */
class Shipper_Task_Import_Uncompress extends Shipper_Task_Import {

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$dumped = new Shipper_Model_Dumped_Packagelist();

		$pos         = $this->get_initialized_position();
		$shipper_pos = $pos;

		$statements = $dumped->get_statements( $pos, 1 );
		$migration  = new Shipper_Model_Stored_Migration();
		$domain     = $migration->get_source();
		$s3_dirname = Shipper_Helper_Fs_Path::clean_fname( $domain );
		foreach ( $statements as $item ) {
			$source      = trailingslashit( $s3_dirname ) . $item['destination'];
			$destination = Shipper_Helper_Fs_Path::get_temp_dir() . pathinfo( $source, PATHINFO_BASENAME );
			$remote      = new Shipper_Helper_Fs_Remote();
			Shipper_Helper_Log::debug(
				sprintf(
					/* translators: %1$s %1$s: dest path and size. */
					__( 'About to download package %1$s size %2$s', 'shipper' ),
					pathinfo( $item['destination'], PATHINFO_BASENAME ),
					$item['size']
				)
			);
			$progress = $remote->download( $source, $destination );
			if ( $progress->is_done() ) {
				// unzip it.
				$archive = Shipper_Model_Archive::get( $destination );
				$archive->extract( Shipper_Helper_Fs_Path::get_temp_dir() );
				$shipper_pos ++;
			} else {
				/* translators: %s: file name. */
				Shipper_Helper_Log::debug( sprintf( 'Downloading %s', $source ) );
				// download not done, so we wait a bit.
				return false;
			}
		}

		$is_done     = empty( $statements );
		$shipper_pos = $is_done
			? 0
			: $shipper_pos;

		if ( ! $this->set_initialized_position( $shipper_pos ) ) {
			// List got re-initialized - cancel.
			return true;
		}

		return $is_done;
	}

	/**
	 * Gets the filelist position, initializing if necessary
	 *
	 * @param object $filelist Optional filelist model instance to use.
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
			/* translators: %1$d %2$d: get_current_step and total steps. */
			__( '( %1$d of %2$d total )', 'shipper' ),
			$this->get_current_step(),
			$this->get_total_steps()
		);

		return sprintf(
			/* translators: %s: description. */
			__( 'Uncompress package %s', 'shipper' ),
			$desc
		);
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

		$current ++;

		return $current;
	}

	/**
	 * Cleanup data, move file cursor to it's initial position
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function cleanup() {
		$file_list = new Shipper_Model_Stored_Filelist();
		$file_list->cleanup();
	}
}