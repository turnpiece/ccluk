<?php
/**
 * Shipper tasks: import, remote (S3) archive cleanup task.
 *
 * @package shipper
 */

/**
 * Shipper import remote scrub class
 */
class Shipper_Task_Import_Scrubremote extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return apply_filters( 'shipper_import_skip_scrub', false )
			? __( 'Scrubbing the remote leftovers: skipping', 'shipper' )
			: __( 'Scrubbing any remote leftovers', 'shipper' );
	}

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
		if ( apply_filters( 'shipper_import_skip_scrub', false ) ) {
			// Allow to explicitly skip this step.
			return true;
		}

		$migration = new Shipper_Model_Stored_Migration();
		$domain    = Shipper_Helper_Fs_Path::clean_fname( $migration->get_source() );
		$remote    = new Shipper_Helper_Fs_Remote();

		$pos         = $this->get_initialized_position();
		$shipper_pos = $pos;
		$statements  = $this->get_file_statements( $pos );
		$objects     = array();

		foreach ( $statements as $data ) {
			$file      = trailingslashit( $domain ) . $data['destination'];
			$objects[] = array(
				'Key' => $remote->get_remote_path( $file ),
			);
		}

		if ( empty( $statements ) && empty( $objects ) ) {
			$files   = new Shipper_Model_Dumped_Filelist();
			$large   = new Shipper_Model_Dumped_Largelist();
			$package = new Shipper_Model_Dumped_Packagelist();
			// Done, remove meta files and parent object.
			$objects[] = array(
				'Key' => $remote->get_remote_path(
					trailingslashit( $domain ) . 'meta/migration_manifest.json'
				),
			);
			$objects[] = array(
				'Key' => $remote->get_remote_path(
					trailingslashit( $domain ) . 'meta/' . $files->get_file_name()
				),
			);
			$objects[] = array(
				'Key' => $remote->get_remote_path(
					trailingslashit( $domain ) . 'meta/' . $large->get_file_name()
				),
			);
			$objects[] = array(
				'Key' => $remote->get_remote_path(
					trailingslashit( $domain ) . 'meta/' . $package->get_file_name()
				),
			);
			$objects[] = array(
				'Key' => $remote->get_remote_path(
					trailingslashit( $domain )
				),
			);
		}

		if ( ! empty( $objects ) ) {
			$result = false;
			try {
				$s3    = $remote->get_remote_storage_handler();
				$creds = $remote->get_creds();
				$s3->deleteObjects(
					array(
						'Bucket' => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
						'Delete' => array(
							'Objects' => $objects,
						),
					)
				);
				$result = true;
			} catch ( Exception $e ) {
				Shipper_Helper_Log::write( 'NOTICE: remote export not scrubbed' );
				Shipper_Helper_Log::write( $e->getMessage() );
			}
		}

		$is_done     = empty( $statements );
		$shipper_pos = $is_done
			? 0
			: $shipper_pos + count( $statements );
		$this->set_initialized_position( $shipper_pos );

		return $is_done;
	}

	/**
	 * Get file statements
	 *
	 * @param int   $pos pointer position on a file.
	 * @param false $dumped list of dumped files.
	 *
	 * @return array
	 */
	public function get_file_statements( $pos, $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Filelist();
		}
		$list = $dumped->get_statements( $pos, 999, 1000 );

		if ( empty( $list ) ) {
			// So we went over the count in regular files list.
			// Let's go through large files now too.
			$pos = $pos - $dumped->get_statements_count();
			if ( $pos < 0 ) {
				$pos = 0;
			}
			$dumped = new Shipper_Model_Dumped_Largelist();
			$list   = $dumped->get_statements( $pos, 999, 10000 );
		}

		return $list;
	}

	/**
	 * Get initialized position
	 *
	 * @param false $filelist array of files.
	 *
	 * @return false|int|mixed
	 */
	public function get_initialized_position( $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$pos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_SCRUB_CURSOR, false );
		if ( false === $pos ) {
			$filelist->set( Shipper_Model_Stored_Filelist::KEY_SCRUB_CURSOR, 0 );
			$filelist->save();
			$pos = 0;
		}

		return $pos;
	}

	/**
	 * Set initialized pointer position
	 *
	 * @param int   $position pointer position in a file.
	 * @param false $filelist list of files.
	 *
	 * @return bool
	 */
	public function set_initialized_position( $position, $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$newpos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_SCRUB_CURSOR, false );
		if ( false === $newpos ) {
			return false;
		}

		$filelist->set( Shipper_Model_Stored_Filelist::KEY_SCRUB_CURSOR, $position );
		$filelist->save();

		return true;
	}
}