<?php // phpcs:ignore

class Snapshot_Model_Queue_Fileset extends Snapshot_Model_Queue {

	private $_current;

	public function get_type () {
		return 'fileset';
	}

	public function get_root () {
		if (!($this->_current instanceof Snapshot_Model_Fileset)) return false;
		return $this->_current->get_site_root();
	}

	public function get_files () {
		$chunk_size = $this->get_chunk_size();
		$chunk = 0;
		$result = array();

		$src = $this->_get_next_source();
		if (empty($src)) return $result;

		$info = $this->_get_source_info($src);

		if (!empty($info['chunk']))
			$chunk = (int)$info['chunk'];
		$source = Snapshot_Model_Fileset::get_source($src);

		$this->_current = $source;

		$start = $chunk * $chunk_size;

		$all_files = array();
		if (defined('SNAPSHOT_FILESET_USE_PRECACHE') && SNAPSHOT_FILESET_USE_PRECACHE) {
			if ($this->has_cached_source_files()) {
				$all_files = $this->get_cached_source_files();
				if ( !is_array($all_files) || empty($all_files) )
					$all_files = $source->get_files();
			} else {
				$all_files = $source->get_files();
				$this->set_cached_source_files($all_files);
			}
		} else {
			$all_files = $source->get_files();
		}

		$files = array_slice($all_files, $start, $chunk_size);
		$files = $this->preprocess_fileset($files, $chunk);

		$info['chunk'] = $chunk + 1;
		if ( $start + $chunk_size >= count($all_files) )
			$info['done'] = true;
		$this->_update_source($src, $info);

		Snapshot_Helper_Log::note("Fetching [" . count($files) . "] files as chunk {$info['chunk']}", "Queue");

		return $files;
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
		return (array)apply_filters(
			'snapshot_queue_fileset_preprocess',
			$this->detect_large_files($files, $chunk),
			$chunk
		);
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
		if (!defined('SNAPSHOT_FILESET_USE_PRECACHE')) return false;
		if (!SNAPSHOT_FILESET_USE_PRECACHE) return false;

		$cache = $this->get_cached_source_files();
		return !empty($cache) && is_array($cache);
	}

	/**
	 * Gets pre-cached sources
	 *
	 * @return array
	 */
	public function get_cached_source_files () {
		if (!defined('SNAPSHOT_FILESET_USE_PRECACHE')) return array();
		if (!SNAPSHOT_FILESET_USE_PRECACHE) return array();

		return $this->_get('precached', array());
	}

	/**
	 * Sets and stores pre-cached sources
	 *
	 * @param array $files Source to set
	 */
	public function set_cached_source_files ($files) {
		if (!defined('SNAPSHOT_FILESET_USE_PRECACHE')) return false;
		if (!SNAPSHOT_FILESET_USE_PRECACHE) return false;

		return $this->_set('precached', $files);
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

		foreach ($sources as $src) {
			$source = Snapshot_Model_Fileset::get_source($src);
			if (!$source) continue;
			$size += $source->get_items_count();
		}

		if ($size > 0) {
			$steps = $size / $this->get_chunk_size();
			$size = ($steps < (int)$steps)
				? (int)$steps + 1
				: (int)$steps
			;
		}

		return $size;
	}

	public function get_chunk_size () {
		if (defined('SNAPSHOT_FILESET_CHUNK_SIZE') && is_numeric(SNAPSHOT_FILESET_CHUNK_SIZE)) {
			$size = intval(SNAPSHOT_FILESET_CHUNK_SIZE);
			if ($size) return $size;
		}
		return 250;
	}

	public function get_prefix () {
		return 'www';
	}
}