<?php // phpcs:ignore

/**
 * Deals with managed (full) backups restoration
 */
class Snapshot_Helper_Restore {

	private $_archive;
	private $_manifest;
	private $_queues;
	private $_seed;
	private $_destination;
	private $_session;
	private $_fileset_estimate;
	private $_tableset_estimate;
	private $_current_step;
	private $copyWarningNumber;
	private $copyWarningString;

	private function __construct() {}

	public static function from( $archive ) {
		$me = new self();
		$me->set_archive_path( $archive );
		$me->_spawn_queues();
		return $me;
	}

	public function to( $path ) {
		$this->_destination = untrailingslashit( wp_normalize_path( realpath( $path ) ) );
	}


	public function get_archive_path() {
		return $this->_archive;
	}

	public function set_archive_path( $archive ) {
		$fullpath = realpath( $archive );
		if ( empty( $fullpath ) || ! is_readable( $fullpath ) ) {
			return false;
		}

		$this->_archive = wp_normalize_path( $fullpath );
		$status = ! ! ( $this->_archive );
		if ( $status ) {
			$this->_seed = sha1_file( $this->_archive );
		}

		return $status;
	}

	public function get_manifest() {
		if ( ! empty( $this->_manifest ) ) {
			return $this->_manifest;
		}

		if ( empty( $this->_archive ) ) {
			Snapshot_Helper_Log::warn( 'Unable to fetch manifest from unknown archive.' );
			return false;
		}
		$zip = Snapshot_Helper_Zip::get( $this->_archive );

		$root = $this->_get_root();
		$manifest_file = Snapshot_Model_Manifest::get_file_name();
		$manifest_path = wp_normalize_path( $root . '/' . $manifest_file );

		if ( is_writable( $manifest_path ) ) {
			unlink( $manifest_path );
		}

		$status = $zip->extract_specific( $root, array( $manifest_file ) );
		if ( empty( $status ) ) {
			Snapshot_Helper_Log::warn( 'Unable to extract manifest.' );
			return false;
		}

		$this->_manifest = Snapshot_Model_Manifest::consume( $manifest_path );

		unlink( $manifest_path );

		return $this->_manifest;
	}

	public function get_intermediate_destination() {
		if ( empty( $this->_seed ) ) {
			Snapshot_Helper_Log::info( 'Unable determine intermediate location from unknown seed.' );
			return false;
		}
		return $this->_get_path( $this->_seed );
	}

	public function get_queues() {
		if ( empty( $this->_queues ) ) {
			$this->_spawn_queues();
		}
		return $this->_queues;
	}

	public function get_current_queues() {
		return $this->_queues;
	}

	/**
	 * Estimates the total steps the restore will take to run
	 *
	 * @return int
	 */
	public function get_total_steps_estimate() {
		$queues = $this->get_queues();

		if ( ! empty( $queues['fileset'] ) && empty( $this->_fileset_estimate ) ) {
			$this->_fileset_estimate = (int) $this->_get_session_value( 'fileset', 'estimate', 0 );
			if ( empty( $this->_fileset_estimate ) ) {
				$all_files = $this->_get_files_list();
				$chunk_size = $queues['fileset']->get_chunk_size();
				$this->_fileset_estimate = ceil( count( $all_files ) / $chunk_size );
				$this->_set_session_value( 'fileset', 'estimate', $this->_fileset_estimate );
			}
		}

		if ( ! empty( $queues['bhfileset'] ) && empty( $this->_fileset_estimate ) ) {
			$this->_fileset_estimate = (int) $this->_get_session_value( 'fileset', 'estimate', 1 );
		}

		if ( empty( $this->_tableset_estimate ) ) {
			$this->_tableset_estimate = (int) $this->_get_session_value( 'tableset', 'estimate', 0 );
			if ( empty( $this->_tableset_estimate ) ) {
				$all_tables = $this->_get_tables_list();
				$this->_tableset_estimate = count( $all_tables );
				$this->_set_session_value( 'tableset', 'estimate', $this->_tableset_estimate );
			}
		}

		$size = (int) $this->_fileset_estimate + (int) $this->_tableset_estimate;
		return $size;
	}

	/**
	 * Gets current restore processing step
	 *
	 * @return int
	 */
	public function get_current_step() {
		if ( ! empty( $this->_current_step ) ) {
			return (int) $this->_current_step;
		}
		$this->_current_step = (int) $this->_get_session_value( 'general', 'current_step', 0 );

		return (int) $this->_current_step;
	}

	/**
	 * Returns current restore status estimate, in percentages
	 *
	 * @return float Percentage, or -1 on missing estimate
	 */
	public function get_current_status_estimate() {
		$total = $this->get_total_steps_estimate();
		if ( empty( $total ) ) {
			return -1;
		}

		$current = $this->get_current_step();
		if ( empty( $current ) ) {
			return 0;
		}

		$status = ($current / $total) * 100;

		return $status;
	}

	/**
	 * Updates current processing step
	 *
	 * @return bool
	 */
	public function increase_processing_step() {
		$step = $this->get_current_step();
		$this->_current_step = $step + 1;
		return $this->_set_session_value( 'general', 'current_step', $this->_current_step );
	}

	/**
	 * Check if the queues restore is done
	 *
	 * @return bool
	 */
	public function is_done() {
		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		foreach ( $queues as $idx => $queue ) {
			if ( ! $this->_queue_done( $idx ) ) {
				return false;
			}
		}
		return true;
	}

	public function clear() {
		Snapshot_Helper_Log::info( 'Starting post-restoration cleanup' );

		if ( $this->_session ) {
			$this->_session->data = array();
			$this->_session->save_session();
		}

		if ( defined( 'SNAPSHOT_MB_BREADTH_FIRST' ) && SNAPSHOT_MB_BREADTH_FIRST ) {
			$lister = new Snapshot_Model_Bflister( new Snapshot_Model_Storage_Session( $this->get_bhf_namespace() ) );
			$lister->reset();
		}

		Snapshot_Helper_Utility::recursive_rmdir( $this->get_intermediate_destination() );

		Snapshot_Helper_Log::info( 'Post-restoration cleanup complete' );

		return true;
	}

	/**
	 * Process the entire files queue, working directly with archive
	 *
	 * @return bool
	 */
	public function process_files() {
		$zip = Snapshot_Helper_Zip::get( $this->_archive );
		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		$files = array();
		$status = false;

		foreach ( $queues as $type => $queue ) {
			if ( $this->_queue_done( $type ) ) {
				continue;
			}

			if ( 'fileset' === $type && $queue instanceof Snapshot_Model_Queue_Bhfileset ) {
				$type = 'bhfileset';
			}

			$method = '_process_' . $type . '_queue';
			if ( ! is_callable( array( $this, $method ) ) ) {
				continue;
			}

			$status = call_user_func_array( array( $this, $method ), array( $queue ) );
			break;
		}

		if ( ! empty( $status ) ) {
			$this->increase_processing_step();
		}

		if ( $this->is_done() ) {
			Snapshot_Helper_Log::info( 'Restoration from queues complete' );
		}

		return $status;
	}

	/**
	 * Gets breadth-first storage namespace for this restore item
	 *
	 * @return string
	 */
	public function get_bhf_namespace() {
		$bhf = "bhfileset-restore-{$this->_seed}";
		return $bhf;
	}


	/**
	 * Restores the files from Bhfileset queue
	 *
	 * @param object $q Queue.
	 *
	 * @return bool Status
	 */
	private function _process_bhfileset_queue( $q ) {
		$prefix = $q->get_prefix();
		$source = untrailingslashit( $this->get_intermediate_destination() . $prefix );
		$destination = trailingslashit( wp_normalize_path( $this->_destination ) );

		$lister = new Snapshot_Model_Bflister( new Snapshot_Model_Storage_Session( $this->get_bhf_namespace() ) );
		$lister
			->set_excludable( false )
			->set_root( $source );

		Snapshot_Helper_Log::info( sprintf( 'Restoring fileset chunk %d of %d', $lister->get_current_step(), $lister->get_total_steps() ) );

		$done = $lister->is_done();
		if ( ! ! $done ) {
			return true;
		}

		$file_items = $lister->get_files();
		foreach ( $file_items as $item ) {
			$filepath = preg_replace( '/^' . preg_quote( $source, '/' ) . '/i', '', $item );
			$path = trim( wp_normalize_path( dirname( $filepath ) ), '/' );
			$fullpath = trailingslashit( wp_normalize_path( "{$destination}{$path}" ) );

			if ( ! is_dir( $fullpath ) ) {
				wp_mkdir_p( $fullpath );
			}

			// Attempt regular copy first.
			if ( ! copy( $item, $fullpath . basename( $item ) ) ) {
				$status = false;
				global $wp_filesystem;
				// Fall back to WP stuff.
				if ( is_callable( array( $wp_filesystem, 'copy' ) ) ) {
					$res = $wp_filesystem->copy( $item, $fullpath . basename( $item ) );
					if ( $res ) {
						$status = true;
					}
				}
				if ( ! $status ) {
					Snapshot_Helper_Log::error( 'Error copying file: ' . basename( $item ) );
				}
			}
			// error_log("Restoring file from {$item} to: " . var_export($fullpath . basename($item),1));
		}

		$done = $lister->is_done();

		$this->_set_session_value( 'general', 'current_step', $lister->get_current_step() );

		if ( ! $done ) {
			$this->_set_session_value( 'fileset', 'estimate', $lister->get_total_steps() );
		} else {
			Snapshot_Helper_Log::info( 'Fileset restoration complete' );
			$this->_set_session_value( 'fileset', 'done', true );
			$lister->reset();
		}

		return $done;
	}


	private function _process_fileset_queue( $q ) {
		$chunk_size = $q->get_chunk_size();
		$chunk = (int) $this->_get_session_value( 'fileset', 'chunk', 0 );
		$start = $chunk * $chunk_size;

		$status = true;

		$prefix = $q->get_prefix();
		$source = untrailingslashit( $this->get_intermediate_destination() . $prefix );
		$destination = trailingslashit( wp_normalize_path( $this->_destination ) );

		$all_files = $this->_get_files_list( $prefix );
		if ( empty( $all_files ) ) {
			return false;
		}

		/*
        * // Enable for debugging
        $max = count($all_files) / $chunk_size;
        $chunk++;
        error_log("Restored fileset chunk {$chunk}");
        $this->_set_session_value('fileset', 'chunk', $chunk);
        $done = $chunk >= $max;
        if ($done) error_log("Fileset restoration complete");
        $this->_set_session_value('fileset', 'done', $done);
        return true;
		*/

		$files = array_slice( $all_files, $start, $chunk_size );
		foreach ( $files as $file ) {
			$filepath = preg_replace( '/^' . preg_quote( $source, '/' ) . '/i', '', $file );
			$path = trim( wp_normalize_path( dirname( $filepath ) ), '/' );
			$fullpath = trailingslashit( wp_normalize_path( "{$destination}{$path}" ) );

			if ( ! is_dir( $fullpath ) ) {
				wp_mkdir_p( $fullpath );
			}

			$this->copyWarningNumber = null;
			$this->copyWarningString = null;

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			set_error_handler(array($this, 'copyWarning'));

			// Attempt regular copy first.
			if ( ! copy( $file, $fullpath . basename( $file ) ) ) {
				$status = false;
				global $wp_filesystem;
				// Fall back to WP stuff.
				if ( is_callable( array( $wp_filesystem, 'copy' ) ) ) {
					$res = $wp_filesystem->copy( $file, $fullpath . basename( $file ) );
					if ( $res ) {
						$status = true;
					}
				}
			}
			restore_error_handler();
			if ( $this->get_copy_warning() && ! $status) {
				break;
			}
		}

		if ( $status ) {
			Snapshot_Helper_Log::info( "Restored fileset chunk {$chunk}" );

			$chunk++;
			$done = ! ! ($start + $chunk_size >= count( $all_files ));

			if ( $done ) {
				Snapshot_Helper_Log::info( 'Fileset restoration complete' );
			}

			$this->_set_session_value( 'fileset', 'chunk', $chunk );
			$this->_set_session_value( 'fileset', 'done', $done );
		} else {
			if ( ! $this->get_copy_warning() ){
				Snapshot_Helper_Log::warn( "There has been an issue restoring fileset chunk {$chunk}" );
			} else {
				Snapshot_Helper_Log::warn( "There has been an issue restoring fileset chunk {$chunk}: " . $this->copyWarningString );
			}
		}

		return $status;
	}

	private function _get_files_list( $pfx = '' ) {
		$source = untrailingslashit( $this->get_intermediate_destination() . $pfx );
		$all_files = Snapshot_Helper_Utility::scandir( $source );
		return is_array( $all_files ) ? $all_files : array();
	}

	private function _get_tables_list() {
		$all_files = $this->_get_files_list();
		$all_tables = array();
		$source = untrailingslashit( $this->get_intermediate_destination() );

		foreach ( $all_files as $file ) {
			if ( ! preg_match( '/\.sql$/i', $file ) ) {
				continue;
			}
			$filepath = preg_replace( '/^' . preg_quote( $source, '/' ) . '/i', '', $file );
			$filepath = trim( wp_normalize_path( dirname( $filepath ) ), '/' );
			if ( 0 !== strlen( $filepath ) ) {
				continue; // Not top level... not interested.
			}
			$all_tables[] = $file;
		}

		return $all_tables;
	}

	private function _process_tableset_queue( $q ) {
		$tables = $this->_get_session_value( 'tableset', 'tables', array() );
		if ( ! is_array( $tables ) ) {
			$tables = array();
		}
		$source = untrailingslashit( $this->get_intermediate_destination() );

		$all_tables = $this->_get_tables_list();
		if ( empty( $all_tables ) ) {
			return true; // No sqls found.
		}
		$status = true;
		$db = new Snapshot_Model_Database_Backup();

		/*
        * // Enable for debugging
        $tables[] = array_shift($all_tables);
        $count = count($tables);
        error_log("Restoring {$count} table");
        $this->_set_session_value('tableset', 'tables', $tables);
        if (count($tables) >= count($all_tables)) {
        error_log("Restore tableset done");
        $this->_set_session_value('tableset', 'done', true);
        }
        return true;
		 */

		do_action( 'snapshot_full_backups_restore_tables', $all_tables, $tables );

		// global $wp_filesystem;

		foreach ( $all_tables as $table_file ) {
			$table = basename( $table_file );
			if ( in_array( $table, $tables, true ) ) {
				continue;
			}

			Snapshot_Helper_Log::info( "Begin restoring table: {$table}" );

			$sql = file_get_contents( $table_file ); // phpcs:ignore
			$db->restore_databases( $sql );

			if ( count( $db->errors ) ) {
				$status = false;
				Snapshot_Helper_Log::error( "There has been an error restoring {$table}" );
			} else {
				if ( $this->_postprocess_table( $table ) ) {
					$tables[] = $table;
					$this->_set_session_value( 'tableset', 'tables', $tables );
					if ( count( $tables ) === count( $all_tables ) ) {
						if ( $this->_postprocess_global_tables() ) {
							$this->_set_session_value( 'tableset', 'done', true );
						} else {
							Snapshot_Helper_Log::warn( 'There has been an issue prostprocessing global tables' );
						}
					}
				} else {
					Snapshot_Helper_Log::warn( "There has been an issue prostprocessing table {$table}" );
				}
			}
			break; // Do one table at the time.
		}

		return $status;

	}

	/**
	 * Post-process tables.
	 *
	 * Eventually will replace Snapshot::snapshot_ajax_restore_convert_db_content()
	 * In current iteration, full backups just restore to whatever the stored point was.
	 * No post-processing
	 *
	 * @param string $table_name Table name to post-process.
	 *
	 * @return bool
	 */
	private function _postprocess_table( $table_name ) {
		return true; // Full backups don't do table post-processing.
	}

	/**
	 * Post-process global tables
	 *
	 * Eventually will replace Snapshot::_postprocess_global_tables()
	 * In current iteration, full backups just restore to whatever the stored point was.
	 * No post-processing
	 *
	 * @return bool
	 */
	private function _postprocess_global_tables() {
		return true; // Full backups don't do global post-processing.
	}

	private function _get_root() {
		return trailingslashit(wp_normalize_path(
			trailingslashit( wp_normalize_path( WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) ) ) . '_imports'
		));
	}

	private function _get_path( $frag ) {
		$frag = preg_replace( '/[^-_a-z0-9]/', '', $frag );
		if ( empty( $frag ) ) {
			return false;
		}

		return trailingslashit( wp_normalize_path( $this->_get_root() . $frag ) );
	}

	private function _extract() {
		$fullpath = $this->get_intermediate_destination();

		if ( empty( $fullpath ) ) {
			Snapshot_Helper_Log::warn( 'Unable to determine the intermediate location for restoring.' );
			return false; // Something went wrong.
		}
		if ( is_dir( $fullpath ) ) {
			return true; // Already extracted.
		}
		wp_mkdir_p( $fullpath );
		if ( ! is_dir( $fullpath ) ) {
			Snapshot_Helper_Log::warn( "Unable to create intermediate location: {$fullpath}" );
			return false; // Couldn't create.
		}

		$zip = Snapshot_Helper_Zip::get( $this->_archive );
		return $zip->extract( $fullpath );
	}

	private function _spawn_queues() {
		$fullpath = $this->get_intermediate_destination();
		if ( empty( $fullpath ) ) {
			return false;
		}

		$manifest = $this->get_manifest();
		if ( ! $manifest ) {
			return false;
		}
		$queues = $manifest->get( 'QUEUES' );

		// Boot session.
		$loc = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupSessionFolderFull' ) );
		$this->_session = new Snapshot_Helper_Session( $loc, $this->_seed );
		$this->_session->load_session();

		if ( ! $this->_extract() ) {
			return false;
		}

		foreach ( $queues as $raw ) {
			if ( empty( $raw['type'] ) || empty( $raw['sources'] ) ) {
				continue;
			}
			$queue_type = ucfirst( $raw['type'] );

			if ( ! ! preg_match( '/fileset$/i', $queue_type ) ) {
				$queue_type = defined( 'SNAPSHOT_MB_BREADTH_FIRST' ) && SNAPSHOT_MB_BREADTH_FIRST
					? 'Bhfileset'
					: 'Fileset'
				;
			}

			$class_name = 'Snapshot_Model_Queue_' . $queue_type;
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			$queue = new $class_name('restore');

			$this->_queues[ $raw['type'] ] = $queue;
		}
		if ( ! empty( $this->_queues ) ) {
			ksort( $this->_queues ); // Sort queues, fileset coming before tableset alphabetically.
		}
		return $this->_queues;
	}

	private function _queue_done( $type ) {
		return $this->_get_session_value( $type, 'done' );
	}

	private function _get_session_value( $section, $key, $fallback = false ) {
		if ( ! isset( $this->_session->data[ $section ] ) ) {
			return $fallback;
		}
		if ( ! isset( $this->_session->data[ $section ][ $key ] ) ) {
			return $fallback;
		}

		return $this->_session->data[ $section ][ $key ];
	}

	private function _set_session_value( $section, $key, $value ) {
		if ( empty( $this->_session->data[ $section ] ) ) {
			$this->_session->data[ $section ] = array();
		}

		$this->_session->data[ $section ][ $key ] = $value;
		return $this->_session->save_session();
	}

	/**
	* Method to handle warnings, used for warnings handling around
	* backup zip copy.
	*/
	public function copyWarning($errno, $errstr) {
		$this->copyWarningNumber = $errno;
		$this->copyWarningString = $errstr;
	}

	public function get_copy_warning() {
		return $this->copyWarningNumber;
	}
}