<?php // phpcs:ignore

/**
 * Remotes handling hub - API and storage.
 */
class Snapshot_Model_Full_Remote extends Snapshot_Model_Full_Abstract {

	public function get_errors () {
		$errors = array_merge(
			array_values(parent::get_errors()),
			array_values(Snapshot_Model_Full_Remote_Api::get()->get_errors()),
			array_values(Snapshot_Model_Full_Remote_Help::get()->get_errors()),
			array_values(Snapshot_Model_Full_Remote_Storage::get()->get_errors())
		);

		// Do we have a current API error?
		$error = Snapshot_Model_Transient::get($this->get_filter('api_error'), false);
		if (!empty($error)) {
			$message = is_array($error) && !empty($error['message'])
				? $error['message']
				: $this->get_default_api_meta_error_message()
			;
			$errors = array($message);
		}
		return $errors;
	}

	public function has_errors () {
		$errors = parent::has_errors();
		if ($errors) return true;

		if (
			Snapshot_Model_Full_Remote_Api::get()->has_errors()
			||
			Snapshot_Model_Full_Remote_Help::get()->has_errors()
			||
			Snapshot_Model_Full_Remote_Storage::get()->has_errors()
		) return true;

		// Do we have a current API error?
		$error = Snapshot_Model_Transient::get($this->get_filter('api_error'), false);
		return !empty($error);
	}

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type () {
		return 'remote';
	}

	public function reset_api () {
		return Snapshot_Model_Full_Remote_Api::get()->reset_api();
	}

	public function remove_token () {
		return Snapshot_Model_Full_Remote_Api::get()->remove_token();
	}

	public function has_api_info () {
		return Snapshot_Model_Full_Remote_Api::get()->has_api_info();
	}

	public function has_api_error () {
		return Snapshot_Model_Full_Remote_Api::get()->has_api_error();
	}

	public function get_default_api_meta_error_message () {
		return Snapshot_Model_Full_Remote_Api::get()->get_default_api_meta_error_message();
	}

	public function get_current_site_management_link () {
		return Snapshot_Model_Full_Remote_Help::get()->get_current_site_management_link();
	}

	public function get_current_secret_key_link () {
		return Snapshot_Model_Full_Remote_Help::get()->get_current_secret_key_link();
	}

	/**
	 * Gets all backups
	 *
	 * Asks local cache for the list of backups
	 *
	 * @return array
	 */
	public function get_backups () {
		$backups = $this->_get_local_list();
		return (array)apply_filters(
			$this->get_filter('get_backups'),
			$backups
		);
	}

	/**
	 * Resets backups cache, which forces backups reload
	 *
	 * @uses $this->reset_api_caches()
	 *
	 * @param bool $forced Optional API cleanup, defaults to false
	 *
	 * @return bool
	 */
	public function reset_backups_cache ($forced= false) {
		Snapshot_Model_Transient::delete($this->get_filter("backups"));
		return $this->reset_api_caches($forced);
	}

	/**
	 * Resets API errors cache and, optionally, API cache as well
	 *
	 * @param bool $forced Optional API cleanup, defaults to false
	 *
	 * @return bool
	 */
	public function reset_api_caches ($forced= false) {
		// Also reset any API error, so we actually force new request if needed
		Snapshot_Model_Transient::delete($this->get_filter("api_error"));

		if ($forced) {
			// If forceful reset, forget API info we got
			Snapshot_Model_Full_Remote_Api::get()->clean_up_api();
			// Also expire token, so we force the exchange
			Snapshot_Model_Transient::expire($this->get_filter("token"));
		}

		return true;
	}

	/**
	 * Get the local incarnation of remote backup file
	 *
	 * If the local backup doesn't exist, fetch it first
	 *
	 * @param int $timestamp Timestamp to resolve to file name
	 *
	 * @return mixed Local file full path on success, (bool)false on failure
	 */
	public function get_backup ($timestamp) {
		$path = trailingslashit(wp_normalize_path(WPMUDEVSnapshot::instance()->get_setting('backupRestoreFolderFull')));
		$file = $path . Snapshot_Helper_Backup::FINAL_PREFIX . '-' . $timestamp;
		$all = glob($file . '*.zip');

		if (empty($all)) return $this->_fetch_backup_file($timestamp);

		return reset($all);
	}

	/**
	 * Gets freshest backup from the list
	 *
	 * @return mixed Freshest backup item or (bool)false on failure
	 */
	public function get_freshest_backup () {
		return $this->_get_newest_file_item($this->get_backups());
	}

	/**
	 * Deletes a remote backup instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function delete_backup ($timestamp) {
		$status = $this->_delete_backup($timestamp);
		if ($status) $this->_refresh();
		return $status;
	}

	/**
	 * Pushes the final backup to service remote storage.
	 *
	 * It will check for storage size remaining first.
	 *
	 * @param Snapshot_Helper_Backup $backup Backup helper to send away
	 *
	 * @return bool
	 */
	public function send_backup ( Snapshot_Helper_Backup $backup) {
		$path = $backup->get_destination_path();
		if (empty($path)) {
			Snapshot_Helper_Log::warn("Unable to determine destination path for upload", "Remote");

			// Also log error, this is an issue that should break after a while
			Snapshot_Model_Full_Error::get()->add(Snapshot_Model_Full_Error::ERROR_UPLOAD);

			return false;
		}

		return Snapshot_Model_Full_Remote_Storage::get()->send_backup_file($path);
	}

	public function has_enough_space_for ($path) {
		return Snapshot_Model_Full_Remote_Storage::get()->has_enough_space_for($path);
	}

	public function get_used_remote_space () {
		return Snapshot_Model_Full_Remote_Storage::get()->get_used_remote_space();
	}

	public function get_total_remote_space () {
		return Snapshot_Model_Full_Remote_Storage::get()->get_total_remote_space();
	}

	public function get_free_remote_space () {
		return Snapshot_Model_Full_Remote_Storage::get()->get_free_remote_space();
	}

	/**
	 * Actually continue item upload for this backup
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function continue_item_upload ($timestamp) {
		$file_pattern = Snapshot_Helper_Backup::FINAL_PREFIX . '-' . $timestamp . '*.zip';
		$local_path = trailingslashit(wp_normalize_path(WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull')));
		$all = glob("{$local_path}{$file_pattern}");

		// No local files still present
		if (empty($all)) {
			Snapshot_Helper_Log::warn("Error continuing upload, unable to determine target paths for: {$timestamp}", "Remote");

			// Also log error, this is an issue that should break after a while
			Snapshot_Model_Full_Error::get()->add(Snapshot_Model_Full_Error::ERROR_UPLOAD);

			return false;
		}
		$path = reset($all);

		if (empty($path) || !file_exists($path)) {
			Snapshot_Helper_Log::warn("Unable to determine target path for continuing the upload", "Remote");

			// Also log error, this is an issue that should break after a while
			Snapshot_Model_Full_Error::get()->add(Snapshot_Model_Full_Error::ERROR_UPLOAD);

			return false;
		}

		Snapshot_Helper_Log::info("Found remote file continuing upload: {$path}", "Remote");

		return Snapshot_Model_Full_Remote_Storage::get()->send_backup_file($path);
	}

	public function get_cache_expiration () {
		return Snapshot_Model_Full_Remote_Storage::get()->get_cache_expiration();
	}

	public function get_dashboard_api_key () {
		return Snapshot_Model_Full_Remote_Api::get()->get_dashboard_api_key();
	}

	/**
	 * Sends the remote schedule update request
	 *
	 * @param string $frequency Backup frequency
	 * @param int $time Backup schedule time increment
	 * @param int $timestamp Optional last backup timestamp
	 *
	 * @return bool
	 */
	public function update_schedule ($frequency, $time, $timestamp= false) {
		if (empty($frequency) || !in_array($frequency, array('daily', 'weekly', 'monthly'), true)) return false;
		if (!is_numeric($time) || $time < 0 || $time > DAY_IN_SECONDS) return false;

		$domain = Snapshot_Model_Full_Remote_Api::get()->get_domain();
		if (empty($domain)) return false;

		$lmodel = new Snapshot_Model_Full_Local();

		// If there's no cron jobs allowed, send nothing.
		if ($this->get_config('disable_cron', false)) {
			$frequency = '';
			$time = 0;
		}

		// Get offset.
		$offset = $this->get_config('schedule_offset', 0);

		// Build our arguments
		$args = array(
			'domain' => $domain,
			'backup_freq' => $frequency,
			'backup_time' => $time,
			'backup_offset' => (int)$offset,
			'backup_limit' => Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit(),
			'local_full_backups' => wp_json_encode($lmodel->get_backups()),
		);

		// Also include last backup timestamp, if supplied.
		if (!empty($timestamp) && is_numeric($timestamp)) {
			$args['last_backup'] = $timestamp;
		}

		$response = Snapshot_Model_Full_Remote_Api::get()->get_dev_api_response('register-settings', $args);
		if (is_wp_error($response)) return false;

		return 200 === (int)wp_remote_retrieve_response_code($response);
	}

	public function get_help_url ($url= false) {
		return Snapshot_Model_Full_Remote_Help::get()->get_help_url($url);
	}

	public function get_help_urls () {
		return Snapshot_Model_Full_Remote_Help::get()->get_help_urls();
	}

	public function refresh_help_urls () {
		return Snapshot_Model_Full_Remote_Help::get()->refresh_help_urls();
	}

	/**
	 * Resolve the timestamp to remote file and attempt deleting it
	 *
	 * @param int $timestamp Timestamp to resolve to file name
	 *
	 * @return bool
	 */
	private function _delete_backup ($timestamp) {
		$file_pattern = Snapshot_Helper_Backup::FINAL_PREFIX . '-' . $timestamp;
		$local_path = trailingslashit(wp_normalize_path(WPMUDEVSnapshot::instance()->get_setting('backupRestoreFolderFull')));
		$status = false;

		$backups = $this->get_backups();
		$backup = false;
		foreach ($backups as $item) {
			if (empty($item['name'])) continue;
			if (!preg_match('/^' . preg_quote($file_pattern, '/') . '.*\.zip$/', $item['name'])) continue;

			// Take first
			$backup = $item['name'];
			break;
		}

		if (empty($backup)) return $status;

		return Snapshot_Model_Full_Remote_Storage::get()->delete_remote_file($backup);
	}

	/**
	 * Returns the backup item name for a timestamp
	 *
	 * @param int $timestamp Timestamp to resolve to file name
	 *
	 * @return string Backup item name or (bool)false on failure
	 */
	private function _resolve_backup_item ($timestamp) {
		$file_pattern = Snapshot_Helper_Backup::FINAL_PREFIX . '-' . $timestamp;

		$backups = $this->get_backups();
		$backup = false;

		foreach ($backups as $item) {
			if (empty($item['name'])) continue;
			if (!preg_match('/^' . preg_quote($file_pattern, '/') . '.*\.zip$/', $item['name'])) continue;

			// Take first
			$backup = $item['name'];
			break;
		}

		return $backup;
	}

	/**
	 * Returns the storage download link for the file
	 *
	 * @param int $timestamp Timestamp to resolve to file name
	 *
	 * @return string Remote storage link or (bool)false on failure
	 */
	public function get_backup_link ($timestamp) {
		$backup = $this->_resolve_backup_item($timestamp);
		if (empty($backup)) return false;

		return Snapshot_Model_Full_Remote_Storage::get()->get_backup_link($backup);
	}

	/**
	 * Downloads the requested backup file from remote storage
	 *
	 * @param int $timestamp Timestamp to resolve to file name
	 *
	 * @return string Local path
	 */
	private function _fetch_backup_file ($timestamp) {
		$backup = $this->_resolve_backup_item($timestamp);
		if (empty($backup)) return false;

		return Snapshot_Model_Full_Remote_Storage::get()->fetch_backup_file($backup);
	}

	public function get_backup_rotation_list ($path) {
		return Snapshot_Model_Full_Remote_Storage::get()->get_backup_rotation_list($path);
	}

	/**
	 * Gets a list of all backups from local cache
	 *
	 * Will refresh cache if it expired
	 *
	 * @return array Array of backups
	 */
	private function _get_local_list () {
		$backups = Snapshot_Model_Transient::get_any($this->get_filter("backups"), false);
		if (false === $backups) {
			$this->_refresh();
			return $this->_get_local_list();
		}
		if (Snapshot_Model_Transient::is_expired($this->get_filter("backups"))) $this->_refresh();
		return $backups;
	}

	private function _refresh () {
		return Snapshot_Model_Full_Remote_Storage::get()->refresh_backups_list();
	}

}