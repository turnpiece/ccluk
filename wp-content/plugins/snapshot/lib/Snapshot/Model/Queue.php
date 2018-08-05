<?php // phpcs:ignore

/**
 * Queue abstraction
 */
abstract class Snapshot_Model_Queue {

	/**
	 * Queue index
	 *
	 * @var string
	 */
	private $_idx;

	/**
	 * Gets a set of files to back up, regardless of source
	 *
	 * Always returns a chunk of full file paths to process, or
	 * an empty array if we're not there just yet. To be used
	 * together with `is_done` call.
	 *
	 * @return array Chunk of full file paths to back up
	 */
	abstract public function get_files ();

	/**
	 * Gets the absolute root path for the current chunk.
	 *
	 * Used to relativize paths for backup zip archive contents.
	 *
	 * @return string Queue chunk root path
	 */
	abstract public function get_root ();

	/**
	 * Gets queue type.
	 *
	 * Used for session storage resolution
	 *
	 * @return string Queue type
	 */
	abstract public function get_type ();

	/**
	 * Gets total steps for this queue
	 *
	 * Can potentially take quite a bit of time to run
	 *
	 * @return int
	 */
	abstract public function get_total_steps ();

	/**
	 * Session instance
	 *
	 * @var object
	 */
	private $_session;

	/**
	 * Chunk size
	 *
	 * @var int
	 */
	protected $_chunk_size;


	/**
	 * Constructor
	 *
	 * Sets up session and internal index references
	 *
	 * @param string $idx Queue index
	 */
	public function __construct ($idx) {
		$loc = trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupSessionFolderFull'));
		$this->_session = new Snapshot_Helper_Session($loc, Snapshot_Helper_String::conceal($idx));
		$this->_session->load_session();

		$this->_idx = $idx;
	}

	/**
	 * Gets the current queue index
	 *
	 * @return string Queue index
	 */
	public function get_idx () {
		return $this->_idx;
	}

	/**
	 * Gets next undone source
	 *
	 * @return array|false Source info as array, or (bool)false
	 */
	public function get_current_source() {
		$src = $this->_get_next_source();
		if (empty($src)) return false;

		return $this->_get_source_info($src, false);
	}

	/**
	 * Gets next undone source type
	 *
	 * @return string|false Source type as string, or (bool)false
	 */
	public function get_current_source_type () {
		$src = $this->_get_next_source();

		return empty($src)
			? false
			: $src
		;
	}

	/**
	 * Add a source to current queue
	 *
	 * @param string $src Source to be processed in chunks
	 *
	 * @return bool
	 */
	public function add_source ($src) {
		$info = $this->_get_source_info($src);
		if (empty($info)) $this->_update_source($src);

		return true;
	}

	/**
	 * Gets sources list
	 *
	 * @return array
	 */
	public function get_sources () {
		return array_keys($this->_get('sources', array()));
	}

	/**
	 * Get the current chunk size
	 *
	 * @return int
	 */
	public function get_chunk_size () {
		if (!empty($this->_chunk_size)) return $this->_chunk_size;
		return 5;
	}

	/**
	 * Sets preferred chunk size
	 *
	 * If this call is made, that's what we'll use
	 *
	 * @param int $size Chunk size to use
	 */
	public function set_chunk_size ($size= 0) {
		if (!is_numeric($size)) return false;
		$this->_chunk_size = (int)$size;
	}

	/**
	 * Check if the entire queue is done and processed
	 *
	 * Note that this is different from getting an empty
	 * array from `get_files()` call. This is how we determine
	 * if we're done with this queue
	 *
	 * @return bool
	 */
	public function is_done () {
		$src = $this->_get_next_source();
		return empty($src);
	}

	/**
	 * Sets all sources as done
	 *
	 * @return bool
	 */
	public function set_done () {
		$sources = $this->_get('sources', array());
		foreach ($sources as $idx => $info) {
			$sources[$idx] = is_array($sources[$idx]) ? $sources[$idx] : array();
			$sources[$idx]['done'] = true;
		}
		return $this->_set('sources', $sources);
	}

	/**
	 * Reset current session and clean up
	 */
	public function clear () {
		$this->_session->data[$this->get_type()] = array();
		$this->_session->save_session();
	}

	/**
	 * Gets storage prefix
	 *
	 * This is used as path to final archive storage,
	 * relative to archive root.
	 *
	 * @return string Prefix
	 */
	public function get_prefix () {
		return '';
	}

	/**
	 * Updates a source with info hash
	 *
	 * @param string $src Source key to update
	 * @param array $info Info hash
	 */
	protected function _update_source ($src, $info= array()) {
		if (!is_array($info))
			$info = array();
		$new_info = array_merge($this->_get_source_defaults($src), $info);

		$sources = $this->_get('sources', array());
		$previous = $this->_get_source_info($src);

		$source = array_merge($previous, $new_info);

		$sources[$src] = $source;

		$this->_set('sources', $sources);
	}

	/**
	 * Get stored info hash for a source
	 *
	 * @param string $src Source key
	 * @param array $fallback Optional fallback array
	 *
	 * @return array
	 */
	protected function _get_source_info ($src, $fallback= array()) {
		$all = $this->_get('sources', array());
		foreach ($all as $idx => $info) {
			if ($idx === $src) return $info;
		}
		return $fallback;
	}

	/**
	 * Gets next source to process
	 *
	 * Next source is the first one in the queue
	 * that isn't "done" already
	 *
	 * @return mixed Either a (string)source key for next source, or (bool)false
	 */
	protected function _get_next_source () {
		$sources = $this->_get('sources', array());
		foreach ($sources as $idx => $info) {
			if (empty($info['done'])) return $idx;
		}
		return false;
	}

	/**
	 * Internal source defaults getter
	 *
	 * @param string $src Source key
	 *
	 * @return array Source defaults
	 */
	protected function _get_source_defaults ($src) {
		return array(
			'chunk' => 0,
			'done' => false,
		);
	}

	/**
	 * Internal session storage getter
	 *
	 * @param string $key Session storage key
	 * @param mixed $fallback Optional fallback
	 *
	 * @return mixed Whatever was in `$fallback` argument, defaults to (bool)false
	 */
	protected function _get ($key, $fallback= false) {
		$this->_session->load_session();
		$type = $this->get_type();
		return isset($this->_session->data[$type][$key])
			? $this->_session->data[$type][$key]
			: $fallback
		;
	}

	/**
	 * Internal session storage setter
	 *
	 * @param string $key Sesion storage key
	 * @param mixed $value Value to store
	 */
	protected function _set ($key, $value) {
		$type = $this->get_type();
		if (empty($this->_session->data[$type]))
			$this->_session->data[$type] = array();

		$this->_session->data[$type][$key] = $value;
		$this->_session->save_session();
	}
}