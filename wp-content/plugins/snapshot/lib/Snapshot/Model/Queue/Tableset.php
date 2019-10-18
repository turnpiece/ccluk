<?php // phpcs:ignore

/**
 * Tableset queue implementation
 */
class Snapshot_Model_Queue_Tableset extends Snapshot_Model_Queue {

	/**
	 * Internal session reference
	 *
	 * @var object Snapshot_Helper_Session
	 */
	private $_session;

	/**
	 * Utility factory method
	 *
	 * Spawns an instance with all database tables preconfigured
	 *
	 * @param string $idx Index passed to constructor
	 * @param bool $include_other Whether to include other, randomly prefixed tables (optional, defaults to true)
	 *
	 * @return Snapshot_Model_Queue_Tableset Pre-configured instance
	 */
	public static function all ($idx, $include_other= true) {
		$me = new self($idx);
		$me->clear();

		$all_tables = self::get_all_tables($include_other);
		if (empty($all_tables)) return $me;

		foreach ($all_tables as $table) {
			$me->add_source($table);
		}

		return $me;
	}

	/**
	 * Utility table listing method
	 *
	 * This is for *full* backups only!
	 *
	 * @param bool $include_other Whether to include other, randomly prefixed tables (optional, defaults to true)
	 *
	 * @return array
	 */
	public static function get_all_tables ($include_other= true) {
		$all_tables = apply_filters('snapshot_queue_tableset_full', (is_multisite() && $include_other), $include_other)
			? self::get_all_database_tables_ms() // Include others on MS - we want all
			: Snapshot_Helper_Utility::get_database_tables() // Yeah, let's go with selection
		;
		$result = array();

		if (empty($all_tables)) return $result;

		foreach ($all_tables as $key => $sources) {
			if ('other' === $key && !$include_other) continue;
			foreach ($sources as $table) {
				$result[] = $table;
			}
		}

		return $result;
	}

	/**
	 *
	 * Fetches all database tables on a multisite.
	 *
	 * The results are a flat array, packed as one key, "all"
	 * This is for *full* backups only!
	 *
	 * @uses $wpdb global
	 *
	 * @return array
	 */
	public static function get_all_database_tables_ms () {
		global $wpdb;
		$tables = array();

		$db_name = Snapshot_Helper_Utility::get_db_name();
		if (empty($db_name) && defined('DB_NAME'))
			$db_name = DB_NAME;
		if (empty($db_name))
			return array();

		$all_tables = $wpdb->get_col($wpdb->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = %s', $db_name));
		if (empty($all_tables)) return array();

		foreach ($all_tables as $tbl) {
			if (!empty($tbl))
				$tables[] = $tbl;
		}

		return array('all' => $tables);
	}

	/**
	 * Returns queue type
	 *
	 * @return string
	 */
	public function get_type () {
		return 'tableset';
	}

	/**
	 * Gets files root path
	 *
	 * @return string
	 */
	public function get_root () {
		return trailingslashit(
			trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupBackupFolderFull')) . Snapshot_Helper_String::conceal($this->get_idx())
		);
	}

	/**
	 * Gets a list of files to pack
	 *
	 * @return array
	 */
	public function get_files () {
		$chunk_size = $this->get_chunk_size();
		$total = 0;
		$chunk = $total;
		$result = array();

		$src = $this->_get_next_source();
		if (empty($src)) return $result;
//if (preg_match('/link/', $src)) return false;
		$info = $this->_get_source_info($src);

		if (!empty($info['chunk']))
			$chunk = (int)$info['chunk'];
		if (!empty($info['total']))
			$total = (int)$info['total'];

		$start = $chunk * $chunk_size;
		$source = new Snapshot_Model_Database_Backup();
		$file = $this->_get_temp_file_name($src);

		$fp = fopen($file, 'a'); // phpcs:ignore
		if (!$fp) return $result;

		fseek($fp, 0, SEEK_END);
		$source->set_fp($fp);

		$source->backup_table($src, $start, $chunk_size, $total);

		fclose($fp); // phpcs:ignore

		$info['chunk'] = $chunk + 1;
		if ($start + $chunk_size >= $total) {
			$info['done'] = true;
			$result[] = $file;
		}
		$this->_update_source($src, $info);

		Snapshot_Helper_Log::note("Fetching table {$src} (chunk {$info['chunk']})", "Queue");

		return $result;
	}

	/**
	 * Gets total steps for this queue
	 *
	 * Can potentially take quite a bit of time to run
	 *
	 * @return int
	 */
	public function get_total_steps () {
		$size = 0;
		$sources = $this->get_sources();
		if (empty($sources)) return $size;

		$chunk_size = $this->get_chunk_size();
		foreach ($sources as $src) {
			$info = $this->_get_source_info($src);
			$steps = 1;
			if (!empty($info['total']) && (int)$info['total']) {
				$steps = (int)$info['total'] / $chunk_size;
				if ($steps > (int)$steps)
					$steps = (int)$steps + 1;
				$steps = $steps ? $steps : 1;
			}
			$size += $steps;
		}

		return $size;
	}

	/**
	 * Gets processing chunk size
	 *
	 * @return number
	 */
	public function get_chunk_size () {
		if (!empty($this->_chunk_size)) return $this->_chunk_size;

		if (defined('SNAPSHOT_TABLESET_CHUNK_SIZE') && is_numeric(SNAPSHOT_TABLESET_CHUNK_SIZE)) {
			$size = intval(SNAPSHOT_TABLESET_CHUNK_SIZE);
			if ($size) return $size;
		}

		$fallback = parent::get_chunk_size();
		if (defined('SNAPSHOT_TABLESET_FALLBACK_CHUNK_SIZE') && is_numeric(SNAPSHOT_TABLESET_FALLBACK_CHUNK_SIZE)) {
			$size = intval(SNAPSHOT_TABLESET_FALLBACK_CHUNK_SIZE);
			if ($size)
				$fallback = $size;
		}

		$config = WPMUDEVSnapshot::instance()->config_data['config'];
		return !empty($config['segmentSize'])
			? $config['segmentSize']
			: $fallback
		;
	}

	/**
	 * Clears the entire sources queue
	 *
	 * @return bool
	 */
	public function clear () {
		$all = $this->_get('sources', array());
		foreach ($all as $src => $info) {
			$file = $this->_get_temp_file_name($src);
			if ( is_writable( $file ) )
				unlink($file);
		}
		parent::clear();
		return true;
	}

	/**
	 * Gets next undone source
	 *
	 * Overrides the default queue one.
	 *
	 * @return array|false Source info as array, or (bool)false
	 */
	public function get_current_source() {
		$src = $this->_get_next_source();
		if (empty($src)) return false;

		$nfo = $this->_get_source_info($src, false);
		if (empty($nfo) || !is_array($nfo)) return false;

		return array_merge(
            $nfo, array(
				'chunk' => $src . ':' . $nfo['chunk'],
			)
        );
	}

	/**
	 * Gets default values for a source
	 *
	 * @param string $src Source
	 *
	 * @return array
	 */
	protected function _get_source_defaults ($src) {
		$defaults = parent::_get_source_defaults($src);
		$table = Snapshot_Helper_Utility::get_table_segments($src, $this->get_chunk_size());
		$defaults['total'] = !empty($table['rows_total']) && is_numeric($table['rows_total'])
			? (int)$table['rows_total']
			: 0
		;
		return $defaults;
	}

	/**
	 * Gets temporary file name
	 *
	 * @param string $src Source name
	 *
	 * @return string
	 */
	private function _get_temp_file_name ($src) {
		$src = preg_replace('/[^-_a-z0-9]/i', '', $src);
		return $this->get_root() . $src . '.sql';
	}
}