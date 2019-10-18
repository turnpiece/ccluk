<?php // phpcs:ignore

/**
 * Handles secret key exchange
 *
 * @since v3.0.5-BETA-3
 */
class Snapshot_Model_Full_Remote_Key extends Snapshot_Model_Full {

	/**
	 * Singleton instance
	 *
	 * @var object
	 */
	private static $_instance;

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

	/**
	 * Constructor - never to the outside world.
	 */
	private function __construct () {}

	/**
	 * No public clones
	 */
	private function __clone () {}

	/**
	 * Gets the singleton instance
	 *
	 * @return Snapshot_Model_Full_Remote_Key
	 */
	public static function get () {
		if (empty(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Gets the secret key
	 *
	 * If a secret key is already present, return that.
	 * Otherwise, make a new secret key request.
	 *
	 * @return string|bool Secret key as string, (bool)false on failure
	 */
	public function get_key () {
		$key = $this->get_config('secret-key', false);
		if (!empty($key)) return $key;

		$key = $this->get_remote_key();
		if (!empty($key)) {
			$this->set_key($key);
			return $key;
		}

		return false;
	}

	/**
	 * Drops the local key
	 *
	 * @return bool Status
	 */
	public function drop_key () {
		$key = $this->get_config('secret-key', false);
		if (empty($key)) return true;

		$this->set_config('secret-key', false);
		$this->set_config('active', false);

		$key = $this->get_config('secret-key', false);

		return empty($key);
	}

	/**
	 * Sets local key to supplied value
	 *
	 * Also attempts to perform connected model actions.
	 *
	 * @param string $key New key to set
	 *
	 * @return bool Status
	 */
	public function set_key ($key) {
		$old = $this->get_config('secret-key', false);
		$changed = false;

		if ($old !== $key) {
			$this->set_config('secret-key', $key);
			$this->set_config('active', !empty($key));

			if (!empty($key)) {
				$model = new Snapshot_Model_Full_Backup();
				$model->remote()->remove_token();
				Snapshot_Controller_Full_Cron::get()->reschedule();
				$model->update_remote_schedule();
			}

			$changed = true;
		}

		return $changed;
	}

	/**
	 * Resets the local key
	 *
	 * Drops the local key, then (re-)issues the remote getting request.
	 *
	 * @return bool Status
	 */
	public function reset_key () {
		if (!$this->drop_key()) return false; // Something went wrong right here

		$key = $this->get_remote_key();
		if (empty($key)) return false;

		return $this->set_key($key);
	}

	/**
	 * Gets secret key instance from API
	 *
	 * Issues a key getting request, and returns the key answer.
	 *
	 * If the token is not set, we will be triggering a completely
	 * different path, in which the API will ping us back with the
	 * OTP token, so we can re-start the procedure and actually get the key.
	 *
	 * @param string $otp_token Optional OTP token to send
	 *
	 * @return string|bool Secret key as string, or (bool)false on failure
	 */
	public function get_remote_key ($otp_token= false) {
		$api = Snapshot_Model_Full_Remote_Api::get();
		$key = $api->get_dashboard_api_key();

		if (empty($key)) return false;

		$signature = new Snapshot_Model_Full_Remote_Signature();

		$timestamp = (int)gmdate("U");
		$nonce = wp_generate_password(64, true);
		$domain = $api->get_domain();

		$parts = array(
			'key' => $key,
			'timestamp' => $timestamp,
			'nonce' => $signature->get_signature(array($nonce), "{$key}-{$domain}"),
		);
		$body = array(
			'timestamp' => $timestamp,
			'nonce' => $nonce,
			'hash' => $signature->get_signature($parts, $key),
			'domain' => $domain,
		);

		if (!empty($otp_token)) {
			// We have an OTP token, so let's put it to use
			// and expect the response with the key back
			$body['token'] = $otp_token;
		}

		$response = $api->get_dev_api_unprotected_response('get-key', $body);
		if (empty($response) || is_wp_error($response)) return false; // Errored out

		if (200 !== (int)wp_remote_retrieve_response_code($response)) return false; // Not 200 OK

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) return false;

		$body = json_decode($body, true);
		if (empty($body['key'])) return false;

		return $body['key'];
	}
}