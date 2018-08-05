<?php // phpcs:ignore

/**
 * DEV remote help handling model helper
 */
class Snapshot_Model_Full_Remote_Help extends Snapshot_Model_Full {

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
 return 'remote'; }

	private function __construct () {}
	private function __clone () {}

	/**
	 * Gets the singleton instance
	 *
	 * @return Snapshot_Model_Full_Remote_Help
	 */
	public static function get () {
		if (empty(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Gets help URL
	 *
	 * @param string $url URL key
	 *
	 * @return string
	 */
	public function get_help_url ($url= false) {
		$fallback = trailingslashit(Snapshot_Model_Full_Remote_Api::get()->get_dev_remote_host()) . 'hub/my-websites/';

		$urls = $this->get_help_urls();
		if (empty($urls)) return $fallback;

		$help_url = !empty($urls[$url])
			? $urls[$url]
			: $fallback
		;

		return apply_filters(
			$this->get_filter('help_url'),
			$help_url,
			$url
		);
	}

	/**
	 * Fetches the list of remote help URLs, refreshing it as necessary
	 *
	 * @return array
	 */
	public function get_help_urls () {
		$urls = Snapshot_Model_Transient::get_any(
			$this->get_filter('help_urls'),
			array()
		);

		// No cache? Attempt to populate it now
		if (empty($urls)) {
			$this->refresh_help_urls();
			$urls = Snapshot_Model_Transient::get_any(
				$this->get_filter('help_urls'),
				array()
			);
		}

		return $urls;
	}

	/**
	 * Refreshes the remote helpful URLs list for later local usage
	 *
	 * @return bool
	 */
	public function refresh_help_urls () {
		$domain = Snapshot_Model_Full_Remote_Api::get()->get_domain();
		if (empty($domain)) return false;

		$response = Snapshot_Model_Full_Remote_Api::get()->get_dev_api_unprotected_response(
            'get-urls', array(
				'domain' => $domain,
			)
		);
		if (is_wp_error($response)) return false;
		if (200 !== (int)wp_remote_retrieve_response_code($response)) return false;

		$raw = wp_remote_retrieve_body($response);
		$list = json_decode($raw, true);
		if (empty($list)) return false;

		Snapshot_Model_Transient::set(
			$this->get_filter('help_urls'),
			$list
		);

		return true;
	}

	/**
	 * Gets current site DEV management link
	 *
	 * Wrapper for `get_help_url` method call
	 *
	 * @return string
	 */
	public function get_current_site_management_link () {
		return $this->get_help_url('hub-backups');
	}

	/**
	 * Gets current site key link
	 *
	 * @uses get_current_site_management_link
	 *
	 * @return string
	 */
	public function get_current_secret_key_link () {
		return $this->get_current_site_management_link() . '#key';
	}

}