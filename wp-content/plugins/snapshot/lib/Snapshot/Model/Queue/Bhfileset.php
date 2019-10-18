<?php // phpcs:ignore

class Snapshot_Model_Queue_Bhfileset extends Snapshot_Model_Queue {

	private $_lister;

	public function __construct($idx) {
		parent::__construct($idx);
		$this->_lister = new Snapshot_Model_Bflister(
			new Snapshot_Model_Storage_Sitemeta( 'bhfileset' )
		);
	}

	public function clear() {
		$this->_lister->reset();
	}

	/**
	 * Checks if the FS iteration is done with
	 *
	 * @return bool
	 */
	public function is_done() {
		return $this->_lister->is_done();
	}

	public function get_type () {
		return 'fileset';
	}

	public function get_root () {
		return untrailingslashit(wp_normalize_path(ABSPATH));
	}

	public function get_files () {
		return $this->_lister->get_files();
	}

	/**
	 * Dispatches the queued files preprocessing
	 *
	 * @param array $files Array of file names
	 * @param int $chunk Current chunk
	 *
	 * @return array Preprocessed files
	 */
	public function preprocess_fileset ($files, $chunk) {
		return $files;
	}

	/**
	 * Detects large files in queue chunk
	 *
	 * @param array $files Files being preprocessed
	 * @param int $chunk Current chunk
	 *
	 * @todo implement a solution for calculating filesize for files > 2GB on 32bit PHP
	 *
	 * @return array Preprocessed files
	 */
	public function detect_large_files ($files, $chunk) {
		if (!is_array($files)) return $files;

		$threshold = (float)self::get_size_threshold();
		if (!$threshold) return $files;

		$result = array();
		foreach ($files as $file) {
			$size = filesize($file);
			if (false !== $size && ($size < 0 || $size > $threshold)) { // Negative size takes care of integer overflow
				// This file is larger than we expected, we might have issues here
				Snapshot_Helper_Log::warn("Processing a large file: {$file} ({$size})", "Queue");

				// @TODO Perhaps even drop extremely large files here, maybe
				// 1 or more GBs and over... Not for now though.

				// Reject oversized files - false by default
				if (apply_filters('snapshot_queue_fileset_reject_oversized', false, $file, $size, $chunk)) {
					Snapshot_Helper_Log::warn("Rejecting {$file} because of the size constraint", "Queue");
					continue;
				}
			}
			$result[] = $file;
		}

		return $result;
	}

	/**
	 * Gets the internal "large file" threshold
	 *
	 * Zero threshold means unlimited
	 *
	 * @return float Threshold, in bytes
	 */
	public static function get_size_threshold () {
		$threshold = 1073741824; // 1Gb
		if (defined('SNAPSHOT_FILESET_LARGE_FILE_SIZE') && SNAPSHOT_FILESET_LARGE_FILE_SIZE) {
			$threshold = intval(SNAPSHOT_FILESET_LARGE_FILE_SIZE);
		}

		return (float)apply_filters(
			'snapshot_queue_fileset_filesize_threshold',
			$threshold
		);
	}

	public function add_source ($src) {
		if (!Snapshot_Model_Fileset::is_source($src)) return false;
		return parent::add_source($src);
	}

	/**
	 * Checks whether we have pre-cached sources available
	 *
	 * @return bool
	 */
	public function has_cached_source_files () {
		return false;
	}

	/**
	 * Gets pre-cached sources
	 *
	 * @return array
	 */
	public function get_cached_source_files () {
		return array();
	}

	/**
	 * Sets and stores pre-cached sources
	 *
	 * @param array $files Source to set
	 */
	public function set_cached_source_files ($files) {
		return false;
	}

	/**
	 * Gets total steps for this queue
	 *
	 * Can potentially take quite a bit of time to run
	 *
	 * @return int
	 */
	public function get_total_steps () {
		return $this->_lister->get_total_steps();
	}

	public function get_chunk_size () {
		return $this->_lister->get_paths_limit();
	}

	public function get_prefix () {
		return 'www';
	}
}