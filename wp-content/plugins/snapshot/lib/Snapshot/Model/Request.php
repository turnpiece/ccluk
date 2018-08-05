<?php // phpcs:ignore

abstract class Snapshot_Model_Request {

	protected $_data;

	/**
	 * Returns nonce name for action
	 *
	 * @param string $action Action for nonce name
	 *
	 * @return string Nonce name
	 */
	public static function nonce_name ($action) {
		return "{$action}-nonce";
	}

	/**
	 * Render nonce field
	 *
	 * @param string $action Action for nonce
	 */
	public static function nonce ($action) {
		wp_nonce_field($action, self::nonce_name($action));
	}

	/**
	 * Check if request array is empty
	 *
	 * @return bool
	 */
	public function is_empty () {
		return empty($this->_data);
	}

	/**
	 * Check if key exists in request
	 *
	 * @param string $key Key to check for
	 *
	 * @return bool
	 */
	public function has ($key) {
		return isset($this->_data[$key]);
	}

	/**
	 * Get value from request
	 *
	 * @param string $key Key to fetch
	 * @param mixed $fallback Optional fallback value
	 *
	 * @return mixed Value or fallback
	 */
	public function value ($key, $fallback= false) {
		return isset($this->_data[$key])
			? $this->_data[$key]
			: $fallback

		;
	}

	/**
	 * Verifies nonce action
	 *
	 * @param string $action Nonce action to check
	 *
	 * @return bool
	 */
	public function is_valid_action ($action) {
		$value = $this->value(self::nonce_name($action));
		if (empty($value)) return false;
		return wp_verify_nonce($value, $action);
	}

	/**
	 * Check if a value in request array is true-ish
	 *
	 * "True-ish" is defined as being one of the positive values
	 *
	 * @param string $key Key to check
	 *
	 * @return bool
	 */
	public function is_true ($key) {
		$value = $this->value($key);
		if (empty($value)) return false;

		return in_array($value, $this->_get_positives(), true);
	}

	/**
	 * Checks if a value is within the submitted range
	 *
	 * @param string $key Key to check
	 * @param array $range Range of values to check against
	 *
	 * @return bool
	 */
	public function is_in_range ($key, $range= array()) {
		$value = $this->value($key);
		return in_array($value, $range, true);
	}

	/**
	 * Checks if a value is within the submitted numeric range
	 *
	 * @param string $key Key to check
	 * @param array $range Range of values to check against
	 *
	 * @return bool
	 */
	public function is_in_numeric_range ($key, $range= array()) {
		$value = intval( $this->value($key) );
		return in_array($value, $range, true);
	}

	/**
	 * Check if a valud in request array is numeric
	 *
	 * @param string $key Key to check
	 *
	 * @return bool
	 */
	public function is_numeric ($key) {
		if (!$this->has($key)) return false;
		return is_numeric($this->value($key));
	}

	/**
	 * Returns an array of values that define "true-ish"-ness
	 *
	 * @return array Positive values
	 */
	protected function _get_positives () {
		return array(
			'1',
			'yes',
			'true'
		);
	}
}