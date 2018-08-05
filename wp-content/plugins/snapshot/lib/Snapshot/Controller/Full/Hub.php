<?php // phpcs:ignore

/**
 * Hub actions controller
 *
 * @since v3.0.5-BETA-4
 */
class Snapshot_Controller_Full_Hub extends Snapshot_Controller_Full {

	const ACTION_CLEAR_API_CACHE = 'clear_cache';
	const ACTION_SET_KEY = 'set_key';
	const ACTION_SCHEDULE_BACKUPS = 'schedule_backups';
	const ACTION_START_BACKUP = 'start_backup';
	const ACTION_STOP_BACKUP = 'stop_backup';
	const ACTION_DELETE_BACKUP = 'delete_backup';
	const ACTION_RESTORE_BACKUP = 'restore_backup';
	const ACTION_DEACTIVATE_BACKUPS = 'deactivate_backups';

	const OPTIONS_FLAG = 'snapshot-automate-run';

	private $_running = false;

	/**
	 * Internal instance reference
	 *
	 * @var object Snapshot_Controller_Full_Ajax instance
	 */
	private static $_instance;

	/**
	 * Overrides parent constructor to add
	 * the options flag
	 */
	protected function __construct () {
		parent::__construct();
		$cron = Snapshot_Controller_Full_Cron::get();
		add_action($cron->get_filter('cron_error_stop'), array($this, 'clear_flag'));
		add_site_option(self::OPTIONS_FLAG, '');
	}

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Full_Ajax instance
	 */
	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Dispatch Hub actions handling.
	 */
	public function run () {
		if ($this->is_running()) return false;

		add_filter( 'wdp_register_hub_action', array($this, 'register_endpoints') );

		$this->_running = true;
	}

	/**
	 * Runs on deactivation
	 */
	public function deactivate () {}

	/**
	 * Checks to see if we're running already
	 *
	 * @return bool
	 */
	public function is_running () {
		return $this->_running;
	}

	/**
	 * Clears the options flag
	 *
	 * @return void
	 */
	public function clear_flag () {
		delete_site_option(self::OPTIONS_FLAG);
	}

	/**
	 * Checks to see whether the current backup is
	 * automate-initiated
	 *
	 * Uses internal flag to perform the check.
	 *
	 * @return bool
	 */
	public function is_doing_automated_backup () {
		$flag = get_site_option(self::OPTIONS_FLAG);
		return !empty($flag);
	}

	/**
	 * Gets the list of known Hub actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions () {
		$known = array(
			self::ACTION_CLEAR_API_CACHE,
			self::ACTION_SET_KEY,
			self::ACTION_SCHEDULE_BACKUPS,
			self::ACTION_START_BACKUP,
			self::ACTION_STOP_BACKUP,
			self::ACTION_DELETE_BACKUP,
			self::ACTION_RESTORE_BACKUP,
			self::ACTION_DEACTIVATE_BACKUPS,
		);
		return $known;
	}

	/**
	 * Registers handlers for actions pushed from the Hub
	 *
	 * @param array Known actions
	 *
	 * @return array Augmented actions
	 */
	public function register_endpoints ($actions) {
		if (!is_array($actions)) return $actions;

		$known = $this->get_known_actions();
		if (!is_array($known)) return $actions;

		foreach ($known as $action_raw_name) {
			$method = "json_{$action_raw_name}";
			if (!is_callable(array($this, $method))) continue; // We don't know how to handle this action

			$action_name = "snapshot_{$action_raw_name}";
			$actions[$action_name] = array($this, $method);
		}

		return $actions;
	}

	/**
	 * Cache clearing implementation helper
	 *
	 * Clears API creds cache.
	 * Called by the JSON request handler.
	 *
	 * @return array|WP_Error Status array on success, error object on failure
	 */
	public function clear_api_cache() {
		$status = false;

		$api = Snapshot_Model_Full_Remote_Api::get();
		$api->clean_up_api();
		Snapshot_Helper_Log::info('API cache cleaned up, attempting to re-connect now', 'Remote');

		$status = $api->connect();

		return empty($status)
			? new WP_Error(self::ACTION_CLEAR_API_CACHE, 'Error re-connecting to refresh API cache')
			: array('code' => 0)
		;
	}

	/**
	 * Cache clearing Hub request handler
	 *
	 * Clears API creds cache.
	 * Fires on membership upgrade, if the user was out of space.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_clear_cache ($params, $action, $request = false) {
		Snapshot_Helper_Log::info('Cache cleanup request received, attempting to process', 'Remote');
		$status = $this->clear_api_cache();

		if (is_wp_error($status)) {
			Snapshot_Helper_Log::info('Issue encountered with cache cleanup/refresh', 'Remote');
			$this->send_response_error($status, $request);
		} else {
			Snapshot_Helper_Log::info('Cache successfully refreshed', 'Remote');
			$this->send_response_success($status);
		}
	}

	/**
	 * Extracts valid key token from supplied params
	 *
	 * @param object $params Parameters passed in json body
	 *
	 * @return string|bool Token as string, or false on failure
	 */
	public function get_valid_key_token ($params) {
		$token = is_object($params) && isset($params->token)
			? $params->token
			: (is_array($params) && isset($params['token'])
				? $params['token']
				: false
			)
		;
		return $token;
	}

	/**
	 * Handles key setting from params using token
	 *
	 * Uses the provided OTP token, which snasphot exchanges
	 * for the actual key
	 *
	 * @param string $token Token to exchange for key
	 *
	 * @return bool
	 */
	public function set_key_from_params_token ($token) {
		$rmt = Snapshot_Model_Full_Remote_Key::get();
		$key = $rmt->get_remote_key($token);

		if (empty($key)) {
			return new WP_Error(self::ACTION_SET_KEY, 'Error exchanging token');
		}

		return $rmt->set_key($key)
			? true
			: new WP_Error(self::ACTION_SET_KEY, 'Key already set')
		;
	}

	/**
	 * Trigger new key exchange
	 *
	 * Provides a OTP token, then snapshot should fetch the real key using
	 * that, responding with success or error message.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_set_key ($params, $action, $request = false) {
		Snapshot_Helper_Log::info('OTP set key request received', 'Remote');

		$token = $this->get_valid_key_token($params);
		if (empty($token)) {
			return $this->send_response_error(
				new WP_Error(self::ACTION_SET_KEY, 'Invalid token'),
				$request
			);
		}

		$status = $this->set_key_from_params_token($token);

		if ($status && !is_wp_error($status)) {
			Snapshot_Helper_Log::info('Key set', 'Remote');
			return $this->send_response_success($status, $request);
		} else {
			$status = is_wp_error($status)
				? $status
				: new WP_Error(self::ACTION_SET_KEY, 'Problem setting key')
			;
			Snapshot_Helper_Log::info('Problem setting key from token', 'Remote');
			$this->send_response_error($status, $request);
		}
	}

	/**
	 * Deactivate backups implementation
	 *
	 * @return bool|WP_Error
	 */
	public function deactivate_backups () {
		if (!$this->_model->is_active()) {
			return new WP_Error(self::ACTION_DEACTIVATE_BACKUPS, "Managed backups already inactive");
		}
		$this->_model->set_config('active', false);
		$this->_model->set_config('secret-key', false);
		$this->clear_api_cache();

		return false === $this->_model->is_active();
	}

	/**
	 * Deactivate backups request handler
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_deactivate_backups ($params, $action, $request = false) {
		Snapshot_Helper_Log::info("Managed backups deactivation request received", 'Remote');
		$status = $this->deactivate_backups();
		if ($status && !is_wp_error($status)) {
			Snapshot_Helper_Log::info('Backups deactivated', 'Remote');
			return $this->send_response_success($status, $request);
		} else {
			$status = is_wp_error($status)
				? $status
				: new WP_Error(self::ACTION_DEACTIVATE_BACKUPS, 'Problem deactivating backups')
			;
			Snapshot_Helper_Log::info('Problem deactivating backups', 'Remote');
			$this->send_response_error($status, $request);
		}
	}

	/**
	 * Validates the params passed to schedule backups action
	 *
	 * @param object $params API-passed params
	 *
	 * @return bool Valid or not
	 */
	public function validate_schedule_params ($params) {
		$status = true;
		if (!isset($params->active))
			$status = false;

		$frequencies = array_keys($this->_model->get_frequencies());
		if (!isset($params->frequency) || !in_array($params->frequency, $frequencies, true)) {
			$status = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, "Invalid parameter: frequency");
		}
		$freq = !is_wp_error($status)
			? $params->frequency
			: false
		;

		if (!isset($params->time) || !is_numeric($params->time)) {
			$status = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, "Invalid parameter: time");
		}
		if (!isset($params->limit) || !is_numeric($params->limit)) {
			$status = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, "Invalid parameter: limit");
		}

		if (!isset($params->offset)) {
			if ('daily' !== $freq) {
				// Only invalid if not set for non-daily frequencies.
				$status = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, "Missing parameter: offset");
			}
		} else {
			if (!is_numeric($params->offset)) {
				// If present, offset has to be numeric.
				$status = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, "Invalid parameter: offset");
			}
		}

		if (!empty($status) && !is_wp_error($status)) {
			Snapshot_Helper_Log::info("Reschedule params are all valid", "Remote");
		} else {
			Snapshot_Helper_Log::warn("Invalid reschedule parameters passed from service", "Remote");
		}

		return $status;
	}

	/**
	 * Applies valid schedule changes
	 *
	 * @param object $params API-passed params
	 *
	 * @return bool Status
	 */
	public function apply_schedule_change ($params) {
		if (!$params->active) {
			Snapshot_Helper_Log::info("Automated rescheduling, cron disabled", "Remote");
			$this->_model->set_config('frequency', false);
			$this->_model->set_config('schedule_time', false);
			$this->_model->set_config('disable_cron', true);
			Snapshot_Controller_Full_Cron::get()->stop();
		} else {
			Snapshot_Helper_Log::info("Automated rescheduling, cron enabled, with settings", "Remote");
			$this->_model->set_config('frequency', $params->frequency);
			$this->_model->set_config('schedule_time', $params->time);

			if (in_array($params->frequency, array('weekly', 'monthly'), true)) {
				$this->_model->set_config('schedule_offset', $params->offset);
			}

			$this->_model->set_config('disable_cron', false);
			Snapshot_Controller_Full_Cron::get()->reschedule();
		}

		Snapshot_Model_Full_Remote_Storage::get()->set_max_backups_limit($params->limit);

		return $this->_model->update_remote_schedule();
	}

	/**
	 * Constructs the schedule change response
	 *
	 * @param object $params API-passed params
	 *
	 * @return array Response params
	 */
	public function construct_schedule_response ($params) {
		Snapshot_Helper_Log::info("Automated rescheduling, response creation", "Remote");
		$response = array();
		$domain = Snapshot_Model_Full_Remote_Api::get()->get_domain();
		if (!empty($domain)) {
			$lmodel = new Snapshot_Model_Full_Local();
			$frequency = $params->frequency;
			$time = $params->time;
			$offset = !empty($params->offset)
				? $params->offset
				: false
			;

			// If there's no cron jobs allowed, send nothing
			if ($this->_model->get_config('disable_cron', false)) {
				$frequency = '';
				$time = 0;
			}

			// Build our arguments
			$response = array(
				'domain' => $domain,
				'backup_freq' => $frequency,
				'backup_time' => $time,
				'backup_offset' => $offset,
				'backup_limit' => Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit(),
				'local_full_backups' => wp_json_encode($lmodel->get_backups()),
			);
			Snapshot_Helper_Log::info("Automated rescheduling, created response array", "Remote");
		} else {
			Snapshot_Helper_Log::warn("Unable to create response array", "Remote");
			$response = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, 'Could not resolve domain');
		}

		return $response;
	}

	/**
	 * Update Snapshot backup schedule settings
	 *
	 * @param object $params Parameters passed in json body
	 *      $active bool Whether schedule is active or unactive
	 *      $frequency string|bool daily/weekly/monthly (defaults to not changing)
	 *      $time integer|bool Offset in seconds from UTC midnight (1-82800) (defaults to not changing)
	 *      $limit integer How many backups to keep before rotating (default 3)
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_schedule_backups ($params, $action, $request = false) {
		//save settings, and return the same object as normally gets sent
		//to REST api (so we can skip that callback eventually when triggered
		//remotely)
		Snapshot_Helper_Log::info("Attempting automated reschedule", "Remote");

		// Step 1: validate stuff
		$status = $this->validate_schedule_params($params);

		if (empty($status) || is_wp_error($status)) {
			// Bye!
			$status = is_wp_error($status)
				? $status
				: new WP_Error(self::ACTION_SCHEDULE_BACKUPS, 'Invalid schedule parameters')
			;
			return $this->send_response_error($status, $request);
		}

		// Valid stuff, let's go
		$this->apply_schedule_change($params);

		// Now, construct the response
		$status = $this->construct_schedule_response($params);
		if (empty($status))
			$status = new WP_Error(self::ACTION_SCHEDULE_BACKUPS, 'Error constructing response');

		return is_wp_error($status)
			? $this->send_response_error($status, $request)
			: $this->send_response_success($status, $request)
		;
	}

	/**
	 * Get current backup session
	 *
	 * @return bool|Snapshot_Helper_Session object instance, or (bool)false on failure
	 */
	public function get_session () {
		$session_idx = $this->_get_backup_type();
		return !empty($session_idx)
			? Snapshot_Helper_Backup::get_session($session_idx)
			: false
		;
	}

	/**
	 * Actually performs a new full backup start
	 *
	 * @param object $params Parameters passed in json body
	 *
	 * @return WP_Error|bool Status
	 */
	public function start_backup ($params = false) {
		$cron = Snapshot_Controller_Full_Cron::get();
		$via_automate = true;

		if (is_object($params) && isset($params->via_automate)) {
			$via_automate = !empty($params->via_automate);
		}

		Snapshot_Helper_Log::info("Booting backup", "Remote");

		if (!$this->_is_backup_processing_ready()) {
			Snapshot_Helper_Log::error("Error starting remote backup: not ready", "Remote");
			$this->clear_flag(); // Just in case
			return new WP_Error(self::ACTION_START_BACKUP, "Managed backups not ready");
		}

		if ($cron->is_running()) {
			Snapshot_Helper_Log::info("Scheduled backup already running", "Remote");
			// Already running. Bye!
			return new WP_Error(self::ACTION_START_BACKUP, "Backup already running");
		}

		if ($this->_model->get_config('disable_cron', false)) {
			Snapshot_Helper_Log::info("Scheduled backups disabled, re-enabling", "Remote");
			$this->_model->set_config('disable_cron', false);
		}

		if (!empty($via_automate)) {
			Snapshot_Helper_Log::info("About to start automated backup", "Remote");
			update_site_option(self::OPTIONS_FLAG, 'true');
		} else {
			Snapshot_Helper_Log::info("About to start regular managed backup", "Remote");
			$this->clear_flag();
		}

		$cron->start_backup(); // Now, let's go
		//$cron->force_actual_start();

		$status = $cron->is_running();
		Snapshot_Helper_Log::info("Remotely triggered backup started", "Remote");

		if (empty($status))
			$status = new WP_Error(self::ACTION_START_BACKUP, "Backup not running");

		return $status;
	}

	/**
	 * Handles a new full backup start request
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_start_backup ($params, $action, $request = false) {
		Snapshot_Helper_Log::info("Remote backup initiating request received", "Remote");

		// No remotely triggered backups if the key has not been set
		$secret_key = $this->_model->get_config('secret-key');
		if (empty($secret_key)) {
			$msg = "Error starting remote backup: no key";
			Snapshot_Helper_Log::error($msg, "Remote");
			return $this->send_response_error(new WP_Error(self::ACTION_START_BACKUP, $msg), $request);
		}

		$status = $this->start_backup($params);
		if (empty($status))
			$status = new WP_Error(self::ACTION_START_BACKUP, 'Error starting backup');

		return !is_wp_error($status)
			? $this->send_response_success(true, $request)
			: $this->send_response_error($status, $request)
		;
	}

	/**
	 * Actually performs backup stop action
	 *
	 * @return bool|WP_Error Status
	 */
	public function stop_backup () {
		$cron = Snapshot_Controller_Full_Cron::get();

		if (!$cron->is_running()) {
			Snapshot_Helper_Log::info("Cancelling backup: apparently not running", "Remote");
			// Already running. Bye!
			return new WP_Error(self::ACTION_STOP_BACKUP, "Backup not running");
		}

		return $cron->reschedule();
	}

	/**
	 * Handles a full backup stop request
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_stop_backup ($params, $action, $request = false) {
		Snapshot_Helper_Log::info("Remote backup cancelling request received", "Remote");

		$status = $this->stop_backup();
		if (empty($status))
			$status = new WP_Error(self::ACTION_STOP_BACKUP, 'Error stopping backup');

		if (!is_wp_error($status)) {
			// Notify back that we're cancelled
			Snapshot_Controller_Full_Reporter::get()->send_backup_finished_report(false);
		}

		return !is_wp_error($status)
			? $this->send_response_success(true, $request)
			: $this->send_response_error($status, $request)
		;
	}

	/**
	 * Gets valid backup ID (timestamp) from params
	 *
	 * @param object $params Parameters passed in json body
	 *      $backup_id string Internal ID of the backup to delete
	 *
	 * @return WP_Error|int Timestamp on success, WP_Error on failure
	 */
	public function get_valid_backup_id ($params) {
		if (!is_object($params)) {
			return new WP_Error('backup_id_validation', "Invalid parameters");
		}

		if (!isset($params->backup)) {
			return new WP_Error('backup_id_validation', "Required parameter backup ID is not present");
		}

		$backup_id = $params->backup;

		if (empty($backup_id)) {
			return new WP_Error('backup_id_validation', "Invalid parameter: backup ID");
		}

		if (!Snapshot_Helper_Backup::is_full_backup($backup_id)) {
			return new WP_Error('backup_id_validation', "Invalid parameter: backup ID is not a valid backup");
		}

		$timestamp = (int)$this->_model->get_file_timestamp_from_name($backup_id);
		if (empty($timestamp)) {
			return new WP_Error('backup_id_validation', "Invalid parameter: backup ID doesn't resolve");
		}

		return $timestamp;
	}

	/**
	 * Actually remove backup
	 *
	 * @param int $timestamp Backup timestamp
	 *
	 * @return bool
	 */
	public function delete_backup ($timestamp) {
		$status = $this->_model->delete_backup($timestamp);

		if (!empty($status)) {
			// Update all settings, new list included
			$this->_model->update_remote_schedule();
			// Refresh backups list
			//Snapshot_Model_Full_Remote_Storage::refresh_backups_list();
		}

		return true;
	}

	/**
	 * Deletes a backup
	 *
	 * @param object $params Parameters passed in json body
	 *      $backup_id string Internal ID of the backup to delete
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_delete_backup ($params, $action, $request = false) {
		Snapshot_Helper_Log::info("Remote backup deleting request received", "Remote");

		$backup_id = $this->get_valid_backup_id($params);
		if (is_wp_error($backup_id)) {
			return $this->send_response_error($backup_id, $request);
		}

		$status = $this->delete_backup($backup_id);
		if (empty($status))
			$status = new WP_Error(self::ACTION_DELETE_BACKUP, "Deleting backup failed");

		return !is_wp_error($status)
			? $this->send_response_success(true, $request)
			: $this->send_response_error($status, $request)
		;
	}

	public function restore_backup ($backup_id) {
		$cron = Snapshot_Controller_Full_Cron::get();
		$cron->kickstart_restore_process($backup_id);

		return true;
	}

	/**
	 * Restores a backup
	 *
	 * @param object $params Parameters passed in json body
	 *      $backup_id string Internal ID of the backup to delete
	 * @param string $action The action name that was called
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return void
	 */
	public function json_restore_backup ($params, $action, $request = false) {
		Snapshot_Helper_Log::info("Remote backup restoring request received", "Remote");

		$backup_id = $this->get_valid_backup_id($params);

		if (is_wp_error($backup_id)) {
			return $this->send_response_error($backup_id, $request);
		}

		$status = $this->restore_backup($backup_id);
		if (empty($status))
			$status = new WP_Error(self::ACTION_RESTORE_BACKUP, "Deleting backup failed");

		return !is_wp_error($status)
			? $this->send_response_success(true, $request)
			: $this->send_response_error($status, $request)
		;
	}

	/**
	 * Wraps error sending response
	 *
	 * If we have enough info to build appropriate response
	 * and we have access to request object, use that to send back more
	 * meaningful, verbose response.
	 * Otherwise default to WP JSON response handling.
	 *
	 * @param WP_Error|mixed $info Info on what went wrong
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return bool
	 */
	public function send_response_error ($info, $request = false) {
		$status = array();
		if (is_wp_error($info)) {
			$code = $info->get_error_code();
			$status = array(
				'code' => $code,
				'message' => $info->get_error_message($code),
				'data' => $info->get_error_data($code),
			);
		}
		if (!empty($status) && is_object($request) && is_callable(array($request, 'send_json_error'))) {
			return $request->send_json_error($status);
		}
		return wp_send_json_error($status);
	}

	/**
	 * Wraps success sending response
	 *
	 * If we have enough info to build appropriate response
	 * and we have access to request object, use that to send back more
	 * meaningful, verbose response.
	 * Otherwise default to WP JSON response handling.
	 *
	 * @param mixed $info Info status
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object
	 *
	 * @return bool
	 */
	public function send_response_success ($info, $request = false) {
		$status = $info;
		if (!empty($status) && is_object($request) && is_callable(array($request, 'send_json_success'))) {
			return $request->send_json_success($status);
		}
		return wp_send_json_success($status);
	}
}