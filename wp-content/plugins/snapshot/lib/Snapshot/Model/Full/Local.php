<?php // phpcs:ignore

class Snapshot_Model_Full_Local extends Snapshot_Model_Full_Abstract {

	const MAX_BACKUPS = 3;

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type () {
		return 'local';
	}

	/**
	 * Gets a list of backups
	 *
	 * @return array A list of full backup items
	 */
	public function get_backups () {
		return apply_filters(
			$this->get_filter('get_backups'),
			$this->_get_raw_backup_items()
		);
	}

	/**
	 * Gets a local backup file instance path
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return mixed Path to backup if local file exists, (bool)false otherwise
	 */
	public function get_backup ($timestamp) {
		$backups = $this->_get_raw_backup_files();
		$pattern = preg_quote(Snapshot_Helper_Backup::FINAL_PREFIX . '-' . $timestamp . '-', '/') . '.*\.zip$';
		$result = false;

		if (empty($backups)) return $result;

		foreach ($backups as $path) {
			if (!preg_match("/{$pattern}/", $path)) continue;
			$result = $path;
			break;
		}

		return $result;
	}

	/**
	 * Deletes a local backup instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function delete_backup ($timestamp) {
		$path = $this->get_backup($timestamp);
		if (empty($path)) return false;

		if ( ! is_writable( $path ) ) return false;

		return unlink($path);
	}

	/**
	 * Check if the timestamp backup exists locally
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 _*/
	public function has_backup ($timestamp) {
		$path = $this->get_backup($timestamp);
		return !empty($path);
	}

	/**
	 * Rotates local backups
	 *
	 * @return bool
	 */
	public function rotate_backups () {
		$to_remove = array();
		$raw_list = $this->_get_raw_backup_items();
		$count = count($raw_list);

		$max_items = defined('SNAPSHOT_MAX_LOCAL_MANAGED_BACKUPS') && SNAPSHOT_MAX_LOCAL_MANAGED_BACKUPS
			? (int)constant(SNAPSHOT_MAX_LOCAL_MANAGED_BACKUPS)
			: self::MAX_BACKUPS
		;

		if (empty($max_items) || $count <= $max_items) return true; // Already there

		Snapshot_Helper_Log::info("Preparing to rotate local backups: {$max_items} to keep around");

		$max_removal = count($raw_list) - $max_items;
		$oldest_item = $this->_get_oldest_file_item($raw_list);
		$oldest = !empty($oldest_item)
			? $oldest_item['name']
			: false
		;
		foreach (range(1, $max_removal) as $idx) {
			$item = $this->_get_newer_file_item($raw_list, $oldest);
			if (empty($item)) {
				break; // No more oldest files
			}

			$oldest = $item['name'];

			$to_remove[] = $item['timestamp'];
		}

		if (empty($to_remove)) return true; // Done already

		$status = true;
		foreach ($to_remove as $rmv) {
			Snapshot_Helper_Log::note("Removing local backup item {$rmv}");
			if (!$this->delete_backup($rmv)) {
				Snapshot_Helper_Log::error("Error rotating local backup item {$rmv}");
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Get a list of raw local full backup filepaths
	 *
	 * @return array
	 */
	private function _get_raw_backup_files () {
		$root = trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull'));
		$pattern = Snapshot_Helper_Backup::FINAL_PREFIX . '*.zip';
		$list = glob($root . $pattern);

		return !empty($list)
			? $list
			: array()
		;

	}

	/**
	 * Gets a list of backup items from local files
	 *
	 * @return array
	 */
	private function _get_raw_backup_items () {
		$list = $this->_get_raw_backup_files();

		$result = array();
		foreach ($list as $raw) {
			$timestamp = $this->_get_file_timestamp_from_name(basename($raw));
			if (empty($timestamp)) continue;

			$result[] = array(
				'name' => basename($raw),
				'size' => filesize($raw),
				'timestamp' => $timestamp,
				'local' => true,
			);
		}

		return $result;
	}

}