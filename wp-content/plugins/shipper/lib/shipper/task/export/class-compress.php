<?php
/**
 * Shipper tasks: files export
 *
 * Will export files to a ZIP archive, ready for migration.
 *
 * @package shipper
 */

/**
 * Compress files as zip and upload as multipart so we can reduce the time
 */
class Shipper_Task_Export_Compress extends Shipper_Task_Export {
	/**
	 * Actually uploads the exported archive
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->has_done_anything = true;

		$dumped      = new Shipper_Model_Dumped_Filelist();
		$pos         = $this->get_initialized_position();
		$zip_name    = 'package-' . uniqid() . '.zip';
		$shipper_pos = $pos;

		$zip_path = Shipper_Helper_Fs_Path::get_temp_dir() . $zip_name;
		$zip      = Shipper_Model_Archive::get(
			$zip_path
		);

		/**
		 * Number of file statements to upload in one step
		 *
		 * @param int $limit Maximum number of files.
		 *
		 * @return int
		 * @since v1.0.1
		 */
		$memory_limit = $this->get_memory_limit();
		Shipper_Helper_Log::debug( 'Memory detected ' . $memory_limit . ' MB' );

		$max_statements = (int) apply_filters(
			'shipper_export_max_upload_statements',
			// fallback.
			0
		);
		$max_size = $memory_limit <= 256 ? 25 : 50;
		// use maxisze for determine the time.
		$max_size = (int) apply_filters( 'shipper_export_max_upload_package_size', $max_size );

		$statements = $dumped->get_statements( $pos, $max_statements, $max_size );
		Shipper_Helper_Log::debug( 'total statements ' . ( count( $statements ) ) );
		$timer = Shipper_Helper_Timer_Basic::get();
		$timer->start( 'compress' );
		foreach ( $statements as $data ) {
			// we compress it as zip.
			$zip->add_file( $data['source'], wp_normalize_path( $data['destination'] ) );
			$shipper_pos ++;
		}
		$zip->close();
		$timer->stop( 'compress' );
		Shipper_Helper_Log::debug( 'Time for compression: ' . $timer->diff( 'compress' ) );

		if ( is_readable( $zip_path ) ) {
			Shipper_Helper_Log::debug( sprintf( 'Allowed size: %sMB while real size %sMB', $max_size, round( filesize( $zip_path ) / 1024 / 1024, 2 ) ) );
		}

		$is_done     = empty( $statements );
		$shipper_pos = $is_done
			? 0
			: $shipper_pos;

		if ( ! $this->set_initialized_position( $shipper_pos ) ) {
			// List got re-initialized - cancel.
			return true;
		}

		// upload it now.
		if ( ! $is_done ) {
			$destination = "files/$zip_name";
			// add it into a dump file.
			$package_list = new Shipper_Model_Dumped_Packagelist();
			$package_list->add_statement(
				array(
					'source'      => $zip_path,
					'destination' => $destination,
					'size'        => is_readable( $zip_path ) ? filesize( $zip_path ) : 0,
				)
			);
			$package_list->close();
		}

		return $is_done;
	}

	/**
	 * Get memory limit
	 *
	 * @return float|int|string
	 */
	private function get_memory_limit() {
		$migration    = new Shipper_Model_Stored_Migration();
		$memory_limit = $migration->get( 'memory_limit' );

		// convert all to M.
		if ( preg_match( '/^(\d+)(.)$/', $memory_limit, $matches ) ) {
			if ( strtoupper( $matches[2] ) === 'M' ) {
				$memory_limit = $matches[1];
			} elseif ( strtoupper( $matches[2] ) === 'K' ) {
				$memory_limit = $matches[1] / 1024; // nnnK -> nnn KB.
			} elseif ( strtoupper( $matches[2] ) === 'G' ) {
				$memory_limit = $matches[1] * 1024;
			}
		}

		return $memory_limit;
	}

	/**
	 * Gets total steps this task is expected to take
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return $this->get_total_files();
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
		$replacer = new Shipper_Helper_Replacer_File( Shipper_Helper_Codec::ENCODE );
		$replacer->add_codec( new Shipper_Helper_Codec_Rewrite() );
		$replacer->add_codec( new Shipper_Helper_Codec_Paths() );

		return $replacer->transform( $path );
	}

	/**
	 * Gets destination type
	 *
	 * @TODO implement
	 */
	public function get_destination_type() {
		return Shipper_Model_Stored_Migration::COMPONENT_FS;
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
	 * @param int   $position position.
	 * @param false $filelist file list.
	 *
	 * @return bool
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
	 * Get total files
	 *
	 * @param false $dumped files array.
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
			/* translators: %1$d %2$d: file position and total files count. */
			__( '( %1$d of %2$d total )', 'shipper' ),
			$pos,
			$this->get_total_files()
		);

		return sprintf(
			/* translators: %s: gathered files.*/
			__( 'Compress gathered files %s', 'shipper' ),
			$desc
		);
	}
}