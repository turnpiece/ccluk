<?php // phpcs:ignore

/**
 * Snapshot actions reporter controller
 *
 * Responsible for communicating current backup states
 * back to the Hub.
 *
 * @since v3.1.6-beta.1
 */
class Snapshot_Controller_Full_Reporter extends Snapshot_Controller_Full {

	const STATUS_ERROR = 'error';
	const STATUS_START = 'start';
	const STATUS_PROCESS = 'process';
	const STATUS_UPLOAD = 'upload';
	const STATUS_FINISH = 'finish';
	const STATUS_RESTORE = 'restore';
	const STATUS_RESTORE_FINISH = 'restore_finish';

	private $_running = false;

	/**
	 * Internal instance reference
	 *
	 * @var object Snapshot_Controller_Full_Ajax instance
	 */
	private static $_instance;

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
	 * Dispatch controller actions handling.
	 */
	public function run () {
		if ($this->is_running()) return false;

		$cron = Snapshot_Controller_Full_Cron::get();

		// Send backup start report
		add_action($this->get_filter('backup_start'), array($this, 'send_backup_start_report'));
		add_action($cron->get_filter('backup_start'), array($this, 'send_backup_start_report'));

		// Send backup state response back to the hub, on successful step update
		add_action($this->get_filter('backup_processing'), array($this, 'send_backup_status_report'));
		add_action($cron->get_filter('backup_processing'), array($this, 'send_backup_status_report'));

		// Send backup uploading info to the hub
		add_action($this->get_filter('backup_finishing'), array($this, 'send_backup_uploading_report'));
		add_action($cron->get_filter('backup_finishing'), array($this, 'send_backup_uploading_report'));

		// Send backup finished info to the hub
		add_action($this->get_filter('backup_finished'), array($this, 'send_backup_finished_report'));
		add_action($cron->get_filter('backup_finished'), array($this, 'send_backup_finished_report'));

		// Send backup error/cleanup info to the hub
		add_action($this->get_filter('error'), array($this, 'send_backup_creation_error_report'), 10, 3);
		add_action($cron->get_filter('cron_error_stop'), array($this, 'send_backup_creation_error_report'), 10, 3);

		// Add restore hooks
		//add_action($this->get_filter('restore_processing'), array($this, 'send_restore_status_report'));
		//add_action($cron->get_filter('restore_processing'), array($this, 'send_restore_status_report'));

		//add_action($this->get_filter('restore_finished'), array($this, 'send_restore_finished_report'));
		//add_action($cron->get_filter('restore_finished'), array($this, 'send_restore_finished_report'));

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
	 * Checks whether the current percentage is in reportable range
	 *
	 * @param float $percentage Status percentage as floating point.
	 * @param int $increment Reportable range increment.
	 *
	 * @return bool
	 */
	public function is_reportable_increment ($percentage, $increment) {
		$percentage = (float)$percentage;
		if (0 === $percentage) return true;

		$test = (int)($percentage * 10);

		return 0 === $test % $increment && 0 === $percentage % $increment;
	}

	/**
	 * Formats the report general params and sends to remote
	 *
	 * @param array $report Specific report to send.
	 *
	 * @return bool
	 */
	public function send_report ($report) {
		$remote = Snapshot_Model_Full_Remote_Api::get();

		$domain = $remote->get_domain();
		$report['domain'] = $domain;

		return $this->_get_report_response($report, $remote);
	}

	/**
	 * Actually sends report to the Hub
	 *
	 * @param array $report Report to send.
	 * @param Snapshot_Model_Full_Remote_Api $remote Remote API instance.
	 *
	 * @return bool
	 */
	protected function _get_report_response ($report, $remote) {
		$response = $remote->get_dev_api_unprotected_response('current-local-status', $report);
		if (is_wp_error($response)) return false;

		$response_code = (int)wp_remote_retrieve_response_code($response);

		return 200 === (int)$response_code;
	}

	/**
	 * Sends backup starting status report
	 *
	 * @param Snapshot_Helper_Backup Backup instance.
	 *
	 * @return bool
	 */
	public function send_backup_start_report ($backup) {
		return $this->send_report(array(
			'status' => self::STATUS_START,
			'info' => 0,
		));
	}

	/**
	 * Sends backup processing status report
	 *
	 * @param Snapshot_Helper_Backup Backup instance.
	 *
	 * @return bool
	 */
	public function send_backup_status_report ($backup) {
		$total = (float)$backup->get_current_status_estimate();
		if ($total < 0) return false;

		if (!$this->is_reportable_increment($total, 5)) return false;

		$total = ceil($total);
		return $this->send_report(array(
			'status' => self::STATUS_PROCESS,
			'info' => $total,
	   	));
	}

	/**
	 * Sends backup uploading report
	 *
	 * @param Snapshot_Helper_Backup Backup instance.
	 *
	 * @return bool
	 */
	public function send_backup_uploading_report ($backup) {
		return $this->send_report(array(
			'status' => self::STATUS_UPLOAD,
			'info' => 100,
	   	));
	}

	/**
	 * Sends backup finished report
	 *
	 * @param Snapshot_Helper_Backup Backup instance.
	 *
	 * @return bool
	 */
	public function send_backup_finished_report ($backup) {
		return $this->send_report(array(
			'status' => self::STATUS_FINISH,
			'info' => 100,
		));
	}

	/**
	 * Sends out backup encountered a non-recoverable error report
	 *
	 * @param string $state Backup step.
	 * @param string $action Concrete action within step.
	 * @param string $message Optional user-friendly message
	 *
	 * @return bool
	 */
	public function send_backup_creation_error_report ($state, $action, $message) {
		$message = !empty($message) ? $message : "Error in backup {$state} performing {$action}";
		return $this->send_report(array(
			'status' => self::STATUS_ERROR,
			'info' => $message,
		));
	}

	/**
	 * Sends restore processing status report
	 *
	 * @param Snapshot_Helper_Restore Restore instance.
	 *
	 * @return bool
	 */
	public function send_restore_status_report ($restore) {
		$total = (float)$restore->get_current_status_estimate();
		if ($total < 0) return false;

		if (!$this->is_reportable_increment($total, 5)) return false;

		$total = ceil($total);
		return $this->send_report(array(
			'status' => self::STATUS_RESTORE,
			'info' => $total,
	   	));
	}

	/**
	 * Sends restore finished report
	 *
	 * @param Snapshot_Helper_Restore Restore instance.
	 *
	 * @return bool
	 */
	public function send_restore_finished_report ($restore) {
		return $this->send_report(array(
			'status' => self::STATUS_RESTORE_FINISH,
			'info' => 100,
		));
	}

}