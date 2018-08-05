<?php // phpcs:ignore

/**
 * Full backup creation helper
 */
class Snapshot_Helper_Backup {

	const FINAL_PREFIX = 'full_backup';

	/**
	 * Holds current step numeric reference
	 *
	 * @var int
	 */
	private $_current_step = 0;

	/**
	 * Estimated runtime, in total steps
	 *
	 * @var int
	 */
	private $_estimate = 0;

	/**
	 * Internal errors reference
	 *
	 * @var array
	 */
	private $_errors;

	/**
	 * Internal blog ID reference
	 *
	 * @var number
	 */
	private $_blog_id;

	/**
	 * Internal queues reference
	 *
	 * @var array
	 */
	private $_queues = array();

	/**
	 * Internal backup index reference
	 *
	 * Used for backup resolution
	 *
	 * @var string
	 */
	private $_idx;

	/**
	 * Internal backup timestamp reference
	 *
	 * @var int
	 */
	private $_timestamp;

	/**
	 * Creates the backup helper instance
	 */
	public function __construct() {}

	/**
	 * Gets the current blog ID
	 *
	 * @return int
	 */
	public function get_blog_id() {
		return (int) $this->_blog_id;
	}

	/**
	 * Sets the current blog ID
	 *
	 * @param int $blog_id Blog ID.
	 */
	public function set_blog_id( $blog_id = null ) {
		$this->_blog_id = $blog_id;
		return $this->_blog_id;
	}

	/**
	 * Create a backup location
	 * Can be called multiple times, won't re-create the location
	 *
	 * @param string $idx End location.
	 *
	 * @return bool
	 */
	public function create( $idx ) {
		$status = false; // Start with assumed failure.

		if ( ! preg_match( '/^[-_a-z0-9]+$/', $idx ) ) {
			return $this->_set_error( sprintf( __( 'Invalid destination: %s', SNAPSHOT_I18N_DOMAIN ), $idx ) );
		}

		$path = $this->resolve_backup( $idx );
		if ( empty( $path ) ) {
			return $status;
		}

		$this->_idx = $idx;
		$this->_timestamp = time();

		$status = true;
		return $status;
	}

	/**
	 * Add a queue to current backup
	 *
	 * @param Snapshot_Model_Queue $queue Queue instance.
	 *
	 * @return bool
	 */
	public function add_queue( $queue ) {
		if ( ! ($queue instanceof Snapshot_Model_Queue) ) {
			return false;
		}
		$this->_queues[] = $queue;
		return true;
	}

	/**
	 * Loads the session instance
	 *
	 * @param string $idx Index of the session to load.
	 *
	 * @return object Snapshot_Helper_Session instance
	 */
	public static function get_session( $idx ) {
		$loc = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupSessionFolderFull' ) );
		$session = new Snapshot_Helper_Session( $loc, Snapshot_Helper_String::conceal( "backup_{$idx}" ) );
		return $session;
	}

	/**
	 * Check if a given archive name is a full backup
	 *
	 * @param string $filename Filename to check.
	 * @param int    $timestamp Optional timestamp.
	 *
	 * @return bool
	 */
	public static function is_full_backup( $filename, $timestamp = false ) {
		$timestamp = (int) $timestamp;
		if ( empty( $timestamp ) ) {
			$timestamp = '[0-9]+';
		}
		return (bool) preg_match(
			'/' .
				preg_quote( self::FINAL_PREFIX, '/' ) . '-' . $timestamp . '-(full|automated)-[A-Za-z0-9]+\.zip$' .
			'/',
			$filename
		);
	}

	/**
	 * Checks if a given archive name is a full *and* automated backup
	 *
	 * @param string $filename Filename to check.
	 * @param int    $timestamp Optional timestamp.
	 *
	 * @return bool
	 */
	public static function is_automated_backup( $filename, $timestamp = false ) {
		if ( ! self::is_full_backup( $filename, $timestamp ) ) {
			return false;
		}

		return (bool) preg_match( '/-automated-/', $filename );
	}

	/**
	 * Saves backup progress indicators
	 *
	 * This is achieved by saving current state in the session
	 *
	 * @return bool
	 */
	public function save() {
		$session = self::get_session( $this->_idx );
		$session->data = array(
			'queues' => $this->get_queues(),
			'timestamp' => $this->_timestamp,
			'current_step' => $this->_current_step,
		);
		if ( ! empty( $this->_estimate ) ) {
			$session->data['total_estimate'] = $this->_estimate;
		}

		return $session->save_session();
	}

	/**
	 * Loads backup state from session
	 *
	 * @param string $idx Backup index to load.
	 *
	 * @return mixed Snapshot_Helper_Backup instance on success, (bool)false on failure
	 */
	public static function load( $idx ) {
		$session = self::get_session( $idx );
		$session->load_session();
		if ( empty( $session->data ) || ! is_array( $session->data ) ) {
			return false;
		}

		$me = new self();
		$me->create( $idx );

		$queues = ! empty( $session->data['queues'] ) && is_array( $session->data['queues'] )
			? $session->data['queues']
			: array()
		;
		if ( empty( $queues ) ) {
			return false;
		}

		foreach ( $queues as $item ) {
			if ( empty( $item['type'] ) || empty( $item['sources'] ) ) {
				continue;
			}
			$class = 'Snapshot_Model_Queue_' . ucfirst( $item['type'] );
			$queue = new $class($idx);
			foreach ( $item['sources'] as $source ) {
				$queue->add_source( $source );
			}
			$me->add_queue( $queue );
		}

		$me->_timestamp = ! empty( $session->data['timestamp'] )
			? $session->data['timestamp']
			: time();

		$me->_current_step = ! empty( $session->data['current_step'] ) && is_numeric( $session->data['current_step'] )
			? (int) $session->data['current_step']
			: 0
		;

		$me->_estimate = ! empty( $session->data['total_estimate'] ) && is_numeric( $session->data['total_estimate'] )
			? (int) $session->data['total_estimate']
			: 0
		;

		return $me;
	}

	/**
	 * Get queues list
	 *
	 * @return array
	 */
	public function get_queues() {
		if ( empty( $this->_queues ) ) {
			return array();
		}

		$result = array();
		foreach ( $this->_queues as $queue ) {
			if ( ! ($queue instanceof Snapshot_Model_Queue) ) {
				continue;
			}
			$result[] = array(
				'type' => strtolower( preg_replace( '/Snapshot_Model_Queue_/', '', get_class( $queue ) ) ),
				'sources' => $queue->get_sources(),
			);
		}
		return $result;
	}

	/**
	 * Estimates the total steps the backup will take to run
	 *
	 * @return int
	 */
	public function get_total_steps_estimate() {
		$size = $this->_estimate;
		if ( ! empty( $size ) ) {
			return $size;
		}

		if ( empty( $this->_queues ) ) {
			return $size;
		}
		if ( $this->will_do_system_backup() ) {
			return 2; // Two steps: FS and DB.
		}
		foreach ( $this->_queues as $queue ) {
			$size += $queue->get_total_steps();
		}

		$this->_estimate = $size;

		return $size;
	}

	/**
	 * Returns current backup status estimate, in percentages
	 *
	 * @return float Percentage, or -1 on missing estimate
	 */
	public function get_current_status_estimate() {
		$total = $this->_estimate;
		if ( empty( $total ) ) {
			$total = $this->get_total_steps_estimate();
			if ( empty( $total ) ) {
				return -1;
			}
		}

		$current = $this->_current_step;
		if ( empty( $current ) ) {
			return 0;
		}

		$status = ($current / $total) * 100;

		return $status;
	}

	/**
	 * Check if the queues backup is done
	 *
	 * @return bool
	 */
	public function is_done() {
		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		foreach ( $queues as $queue ) {
			if ( ! $queue->is_done() ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Call clear on all queues
	 *
	 * @return bool
	 */
	public function clear() {
		$this->_create_manifest();
		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		foreach ( $queues as $queue ) {
			$queue->clear();
		}
		// And now! clean up ourselves.
		$this->_queues = array();
		$this->save();
		return true;
	}


	/**
	 * Stops and removes currently processing backup
	 *
	 * @return bool
	 */
	public function stop_and_remove() {
		$this->clear(); // Clear all queues.

		// Now drop all intermediate backup files.
		$intermediate_path = $this->get_archive_path( $this->_idx );
		if ( ! is_writable( $intermediate_path ) ) {
			return false;
		}

		return unlink( $intermediate_path );
	}

	/**
	 * Last step in a backup - postprocess the created archive
	 *
	 * @return bool
	 */
	public function postprocess() {
		// Lastly, move archive - first, get the intermediate archive path.
		$intermediate_path = $this->get_archive_path( $this->_idx );
		if ( ! file_exists( $intermediate_path ) ) {
			return false;
		}

		// Next up, create the new filename.
		$destination = $this->get_destination_path();
		if ( file_exists( $destination ) ) {
			return false;
		}

		// Lastly, move it.
		return rename( $intermediate_path, $destination );
	}

	/**
	 * Fetch backup timestamp
	 *
	 * @return int UNIX timestamp
	 */
	public function get_timestamp() {
		return ! empty( $this->_timestamp )
			? (int) $this->_timestamp
			: 0
		;
	}

	/**
	 * Gets the final destination filename.
	 *
	 * @return string
	 */
	public function get_destination_filename() {
		static $filename;

		if ( empty( $filename ) ) {
			$intermediate_path = $this->get_archive_path( $this->_idx );
			$type = Snapshot_Controller_Full_Hub::get()->is_doing_automated_backup()
				? 'automated'
				: $this->_idx
			;
			$filename = self::FINAL_PREFIX . '-' . $this->get_timestamp() . '-' . $type . '-' . Snapshot_Helper_Utility::get_file_checksum( $intermediate_path ) . '.zip';
		}

		return $filename;
	}

	/**
	 * Get full destination path.
	 *
	 * @return string
	 */
	public function get_destination_path() {
		$filename = $this->get_destination_filename();
		$destination = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupBaseFolderFull' ) );
		return "{$destination}{$filename}";
	}

	/**
	 * Get full path for a certain backup
	 *
	 * @param string $idx End location.
	 *
	 * @return string Full backup path
	 */
	public function get_path( $idx ) {
		$destination = preg_replace( '/[^-_a-z0-9]/', '', Snapshot_Helper_String::conceal( basename( $idx ) ) );
		if ( empty( $destination ) ) {
			return $this->_set_error( sprintf( __( 'Invalid destination: %s', SNAPSHOT_I18N_DOMAIN ), $idx ) );
		}

		return trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupBackupFolderFull' ) ) . $destination;
	}

	/**
	 * Get archive file name (basename)
	 *
	 * @return string Archive file basename
	 */
	public function get_archive_name() {
		return Snapshot_Helper_String::conceal( 'archive.zip' );
	}

	/**
	 * Archive path getter
	 *
	 * @param string $idx End location.
	 *
	 * @return string Full archive path
	 */
	public function get_archive_path( $idx ) {
		return trailingslashit( $this->get_path( $idx ) ) . $this->get_archive_name();
	}

	/**
	 * Resolve the backup full path
	 * If destination doesn't exist, it will be created
	 *
	 * @param string $idx End location.
	 *
	 * @return mixed (bool)false on failure, (string)full path on success
	 */
	public function resolve_backup( $idx ) {
		$path = $this->get_path( $idx );
		if ( empty( $path ) ) {
			return false;
		}

		if ( file_exists( $path ) ) {
			return true; // Already done, moving on.
		}
		wp_mkdir_p( $path );

		if ( ! file_exists( $path ) ) {
			return $this->_set_error( sprintf( __( 'Backup path creation failed: %s', SNAPSHOT_I18N_DOMAIN ), $path ) );
		}
		if ( ! is_writable( $path ) ) {
			return $this->_set_error( sprintf( __( 'Backup path not writable: %s', SNAPSHOT_I18N_DOMAIN ), $path ) );
		}

		return $path;
	}

	/**
	 * Checks to determine if we're to do backup using syscalls
	 *
	 * @uses SNAPSHOT_ATTEMPT_SYSTEM_BACKUP define state to allow/disallow
	 *
	 * @return bool
	 */
	public function will_do_system_backup() {
		// First up, are we expected to try system backup?
		if ( ! (defined( 'SNAPSHOT_ATTEMPT_SYSTEM_BACKUP' ) && SNAPSHOT_ATTEMPT_SYSTEM_BACKUP) ) {
			return false;
		}
		return $this->supports_system_backup();
	}

	/**
	 * Checks whether we're able to use system tools to perform backup
	 *
	 * @uses SNAPSHOT_NO_SYSTEM_BACKUP define to shortcircuit detection
	 *
	 * @return bool
	 */
	public function supports_system_backup() {
		if ( defined( 'SNAPSHOT_NO_SYSTEM_BACKUP' ) && SNAPSHOT_NO_SYSTEM_BACKUP ) {
			return false;
		}

		if (
			! Snapshot_Helper_System::is_available( 'escapeshellarg' )
			||
			! Snapshot_Helper_System::is_available( 'escapeshellcmd' )
			||
			! Snapshot_Helper_System::is_available( 'exec' )
		) {
			return false;
		}

		return Snapshot_Helper_System::has_command( 'zip' )
			&& Snapshot_Helper_System::has_command( 'ln' )
			&& Snapshot_Helper_System::has_command( 'rm' )
			&& Snapshot_Helper_System::has_command( 'mysqldump' );
	}

	/**
	 * Process the entire files queue, working directly with archive
	 *
	 * @return bool
	 */
	public function process_files() {
		if ( $this->will_do_system_backup() ) {
			return $this->process_archive_system();
		} else {
			Snapshot_Helper_Log::warn( 'Unable to perform requested system backup, proceeding with builtin' );
			// Carry on as normal...
		}

		$path = $this->get_archive_path( $this->_idx );

		$zip = Snapshot_Helper_Zip::get( $path );
		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		$files = array();
		$status = false;

		foreach ( $queues as $queue ) {
			if ( $queue->is_done() ) {
				continue;
			}

			$current_source = $queue->get_current_source();

			$status = true; // We still have a queue to process.
			$files = $queue->get_files();

			$zip->set_root( $queue->get_root() );
			$prefix = $queue->get_prefix();

			$next_source = $queue->get_current_source();

			if ( ! empty( $files ) ) {
				$status = $zip->add( $files, $prefix );
			} elseif ( ! empty( $current_source['chunk'] ) && ! empty( $next_source['chunk'] ) ) {
				$status = $current_source['chunk'] !== $next_source['chunk'];
			} else {
				$status = false;
			}

			break;
		}

		if ( $status ) {
			$this->_current_step++;
		}

		return $status;
	}

	/**
	 * Iterates through queues and processes them through syscalls
	 *
	 * @return bool
	 */
	public function process_archive_system() {
		Snapshot_Helper_Log::info( 'Attempting system backup' );

		// This will potentially take a while, so let's extend the time we're allowed to run.
		if ( ! function_exists( 'ini_get' ) ) {
			Snapshot_Helper_Log::warn( 'Could not probe for safe mode' );
		} else {
			if ( ! ! ini_get( 'safe_mode' ) ) {
				Snapshot_Helper_Log::warn( 'Safe mode is on, will not attempt extending exec time' );
			} else {
				if ( ! function_exists( 'set_time_limit' ) ) {
					Snapshot_Helper_Log::warn( 'Unable to extend exec time limit' );
				} elseif ( set_time_limit( 0 ) ) {
					Snapshot_Helper_Log::info( 'Removed exec time limit constraint' );
				} else {
					Snapshot_Helper_Log::warn( 'Unable to remove exec time constraint' );
				}
			}
		}

		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		$status = false;

		foreach ( $queues as $queue ) {
			if ( $queue->is_done() ) {
				continue;
			}

			if ( $queue instanceof Snapshot_Model_Queue_Fileset ) {
				$status = $this->system_process_files( $queue );
				if ( $status ) {
					$queue->set_done();
					$this->_current_step++;
				}
				break;
			}

			if ( $queue instanceof Snapshot_Model_Queue_Tableset ) {
				$status = $this->system_process_tables( $queue );
				if ( $status ) {
					$queue->set_done();
					$this->_current_step++;
				}
				break;
			}
		}

		return $status;
	}

	/**
	 * Renders and packs up tableset from queue using syscalls
	 *
	 * Requires zip and mysqldump system utilities
	 *
	 * @param Snapshot_Model_Queue_Tableset $queue Queue to process.
	 *
	 * @return bool
	 */
	public function system_process_tables( $queue ) {
		if ( ! ($queue instanceof Snapshot_Model_Queue_Tableset) ) {
			return false;
		}

		$tables = $queue->get_sources();
		if ( empty( $tables ) ) {
			return true; // Nothing to do here.
		}
		$dump_path = Snapshot_Helper_System::get_command( 'mysqldump' );
		if ( empty( $dump_path ) ) {
			return false;
		}

		$zip_path = Snapshot_Helper_System::get_command( 'zip' );
		if ( empty( $zip_path ) ) {
			return false;
		}

		$backup_path = $this->get_path( $this->_idx );
		$db_name = escapeshellcmd( DB_NAME );
		$db_user = escapeshellcmd( DB_USER );
		$db_pass = escapeshellcmd( DB_PASSWORD );

		$connection = '';
		if ( Snapshot_Helper_System::is_socket_connection( DB_HOST ) ) {
			$db_socket = escapeshellcmd( Snapshot_Helper_System::get_raw_db_mode( DB_HOST ) );

			$connection = "--socket={$db_socket}";
		} else {
			$db_host = escapeshellcmd( Snapshot_Helper_System::get_db_host( DB_HOST ) );
			$db_port = escapeshellcmd( Snapshot_Helper_System::get_db_port( DB_HOST ) );

			$connection = "-h{$db_host} -P{$db_port}";
		}

		$include_sqls = array();
		foreach ( $tables as $table ) {
			$table = escapeshellcmd( $table );
			$tblfile = "{$table}.sql";

			// Go to where we need to be first.
			$command = "cd {$backup_path}";
			// Actual mysqldump command string.
			$command .= " && {$dump_path} {$connection} -u{$db_user}";
			if ( ! empty( $db_pass ) ) {
				$command .= " -p{$db_pass}";
			}
			$command .= " {$db_name} {$table} > {$tblfile}";

			$status = Snapshot_Helper_System::run(
				$command,
				sprintf( 'dump table %s to file %s', $table, $tblfile )
			);
			if ( is_wp_error( $status ) ) {
				Snapshot_Helper_System::run(
					"cd {$backup_path} && rm {$tblfile}",
					'clean up'
				); // Attempt cleanup.
			} else {
				$include_sqls[] = $tblfile;
			}
		}

		if ( empty( $include_sqls ) ) {
			Snapshot_Helper_Log::error( 'No table files rendered, nothing to back up' );
			return false;
		}

		// We're here, let's add to the archive.
		$archive = $this->get_archive_name();
		$all_sql_files = join( ' ', $include_sqls );

		$command = "cd {$backup_path}";
		$command .= " && {$zip_path} {$archive} {$all_sql_files}";
		$command .= " && rm {$all_sql_files}";

		$status = Snapshot_Helper_System::run( $command, 'back up rendered SQL files' );
		if ( is_wp_error( $status ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Packs up fileset from queue using syscalls
	 *
	 * Requires zip and ln system utilities
	 *
	 * @uses SNAPSHOT_SYSTEM_ZIP_ONLY define to force alternative zip-only based workflow
	 *
	 * @param Snapshot_Model_Queue_Fileset $queue Queue to process.
	 *
	 * @return bool
	 */
	public function system_process_files( $queue ) {
		if ( ! ($queue instanceof Snapshot_Model_Queue_Fileset) ) {
			return false;
		}

		$src = $queue->get_current_source_type();
		$source = Snapshot_Model_Fileset::get_source( $src );

		$zip_path = Snapshot_Helper_System::get_command( 'zip' );
		if ( empty( $zip_path ) ) {
			return false;
		}

		$ln_path = Snapshot_Helper_System::get_command( 'ln' );
		if ( empty( $ln_path ) ) {
			return false;
		}

		$find_path = Snapshot_Helper_System::get_command( 'find' );
		$has_find = ! empty( $find_path ) && ! (defined( 'SNAPSHOT_SYSTEM_ZIP_ONLY' ) && SNAPSHOT_SYSTEM_ZIP_ONLY);

		$backup_path = $this->get_path( $this->_idx );
		$source_root = $source->get_root();
		$target_prefix = $queue->get_prefix();
		$archive = $this->get_archive_name();
		$exclusions = $this->get_system_exclusion_paths( $source, $has_find );

		// Start by cleaning up everything.
		$command = "cd {$backup_path} && rm -f ./*";
		// Add symbolic link to target prefix directory.
		// This is to force processed files into proper zipped structure.
		$command .= " && ln -s {$source_root} {$target_prefix}";
		// Actual zip command.
		if ( $has_find ) {
			Snapshot_Helper_Log::info( 'Attempt backing up everything using find (quicker)' );
			$max_size = Snapshot_Model_Queue_Fileset::get_size_threshold();
			$command .= " && {$find_path} -L www";
			if ( ! empty( $exclusions ) ) {
				$command .= ' ' . join( ' ', $exclusions );
			}
			if ( ! empty( $max_size ) ) {
				$command .= ' -not \( -type f -size +' . $max_size . 'c -prune \)';
			}
			$command .= ' -print';
			$command .= " | {$zip_path} {$archive} -@";
		} else {
			// No find - process files via zip utility.
			// This can take _a while_ to work through.
			$command .= " && {$zip_path} -r {$archive} .";
			if ( ! empty( $exclusions ) ) {
				$command .= ' ' . join( ' ', $exclusions );
			}
		}
		// Clean up - remove symlink.
		$command .= " && rm {$target_prefix}";

		$status = Snapshot_Helper_System::run( $command, 'clean up initially and back up files' );
		if ( is_wp_error( $status ) ) {
			Snapshot_Helper_System::run(
				"cd {$backup_path} && rm -f ./*",
				'clean up'
			); // Attempt cleanup.
			return false;
		}

		return true;
	}

	/**
	 * Gets exclusion paths converted to be source-relative
	 *
	 * Used for system files processing (i.e. zip utility call)
	 *
	 * @param Snapshot_Model_Fileset $source Instance object.
	 * @param bool                   $format Optional format parameter - (bool)true for find, (bool)false for zip.
	 *
	 * @return array
	 */
	public function get_system_exclusion_paths( $source, $format = false ) {
		$raw_exclusions = Snapshot_Model_Fileset::get_excluded_paths();
		$exclusions = array();

		foreach ( $raw_exclusions as $excl ) {
			$excl = preg_replace(
				'/' . preg_quote( trailingslashit( $source->get_root() ), '/' ) . '/',
				'',
				$excl
			);
			if ( empty( $excl ) ) {
				continue;
			}
			$exclusions[] = $format
				? '-not \( -path \'*' . escapeshellcmd( trailingslashit( $excl ) ) . '*\' -prune \)'
				: '-x ' . escapeshellarg( '*' . trailingslashit( $excl ) . '*' );
		}
		return $exclusions;
	}

	/**
	 * Gets the first undone queue
	 *
	 * @return object|false Snapshot_Model_Queue instance, or false
	 */
	public function get_current_queue() {
		$queues = is_array( $this->_queues ) ? $this->_queues : array();
		foreach ( $queues as $queue ) {
			if ( $queue->is_done() ) {
				continue;
			}

			return $queue;
		}
		return false;
	}

	/**
	 * Get all recorded errors.
	 *
	 * @return array All errors encountered this far
	 */
	public function errors() {
		return $this->_errors;
	}

	/**
	 * Get manifest object for this backup
	 *
	 * @return object Snapshot_Model_Manifest instance
	 */
	public function get_manifest() {
		static $manifest;
		if ( empty( $manifest ) ) {
			$manifest = Snapshot_Model_Manifest::create( $this );
		}

		return $manifest;
	}

	/**
	 * Create a manifest file for this backup
	 *
	 * @param string $path Path to create the file in.
	 *
	 * @return string File path
	 */
	public function create_manifest_file( $path = '' ) {
		$manifest = $this->get_manifest();
		$path = ! empty( $path ) ? $path : trailingslashit( $this->get_path( $this->_idx ) );
		$file = $path . Snapshot_Model_Manifest::get_file_name();

		file_put_contents( $file, $manifest->get_flat() ); //phpcs:ignore

		return $file;
	}

	/**
	 * Creates and packs manifest file
	 *
	 * @return bool
	 */
	private function _create_manifest() {
		$path = trailingslashit( $this->get_path( $this->_idx ) );
		$archive = $this->get_archive_path( $this->_idx );
		$zip = Snapshot_Helper_Zip::get( $archive );

		$file = $this->create_manifest_file( $path );
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$zip->set_root( $path );
		$status = $zip->add( array( $file ) );

		if ( is_writable( $file ) ) {
			unlink( $file );
		}

		return $status;
	}

	/**
	 * Add error to the queue
	 *
	 * @param string $string Error message.
	 */
	private function _set_error( $string ) {
		$this->_errors[] = $string;
		return false;
	}

}