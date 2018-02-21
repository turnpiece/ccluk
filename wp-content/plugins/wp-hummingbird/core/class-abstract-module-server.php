<?php

/**
 * Class WP_Hummingbird_Module_Server
 *
 * A parent class for those modules that offers a piece of code to
 * setup the server (gzip and caching)
 */
abstract class WP_Hummingbird_Module_Server extends WP_Hummingbird_Module {

	protected $transient_slug = false;

	protected $status;

	public function run() {}

	/**
	 * Initializes the module. Always executed even if the module is deactivated.
	 */
	public function init() {
		// Fetch status of selected module.
		$this->status = $this->get_analysis_data();
		if ( false === $this->status ) {
			// Force only when we don't have any data yet.
			$this->status = $this->get_analysis_data( true );
		}
	}

	/**
	 * Return the analized data for the module
	 *
	 * @param bool $force If set to true, cache will be cleared before getting the data.
	 * @param bool $check_api If set to true, the api will be checked.
	 *
	 * @return mixed Analysis data
	 */
	public function get_analysis_data( $force = false, $check_api = false ) {
		if ( ! $this->transient_slug ) {
			return false;
		}

		$transient = 'wphb-' . $this->transient_slug . '-data';
		$results = get_site_option( $transient );

		if ( $force ) {

			$this->clear_cache();


			if ( $check_api ) {
				$results = $this->analize_data( true );
			} else {
				$results = $this->analize_data();
			}

			update_site_option( $transient, $results );

		}

		return $results;
	}

	/**
	 * Analize the data
	 *
	 * @param bool $check_api If set to true, the api will be checked.
	 *
	 * @return mixed
	 */
	protected abstract function analize_data( $check_api = false );

	/**
	 * Implement abstract parent method for clearing cache.
	 */
	public function clear_cache() {
		delete_site_option( 'wphb-' . $this->transient_slug . '-data' );
	}

	/**
	 * Get the server code snippet
	 *
	 * @param string $server Server name (nginx,apache...).
	 * @param array  $expiry_times Type expiry times (javascript, css...).
	 *
	 * @return string
	 */
	public function get_server_code_snippet( $server, $expiry_times = array() ) {
		$method = 'get_' . str_replace( array( '-', ' ' ), '', strtolower( $server ) ) . '_code';
		if ( ! method_exists( $this, $method ) ) {
			return '';
		}

		return call_user_func_array( array( $this, $method ), array( $expiry_times ) );
	}

	/**
	 * Check if .htaccess is writable.
	 *
	 * @return bool
	 */
	public static function is_htaccess_writable() {
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$home_path = get_home_path();
		$writable = ( ! file_exists( $home_path . '.htaccess' ) && is_writable( $home_path ) ) || is_writable( $home_path . '.htaccess' );
		return $writable;
	}

	/**
	 * Check if .htaccess has Hummingbird caching or gzip rules in place.
	 *
	 * @param string $module  Module slug.
	 *
	 * @return bool
	 */
	public static function is_htaccess_written( $module = '' ) {
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		if ( ! function_exists( 'extract_from_markers' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		}

		$existing_rules  = array_filter( extract_from_markers( get_home_path() . '.htaccess', 'WP-HUMMINGBIRD-' . strtoupper( $module ) ) );
		return ! empty( $existing_rules );
	}

	/**
	 * Add rules .htaccess file.
	 *
	 * @param $module
	 *
	 * @return bool
	 */
	public static function save_htaccess( $module ) {
		if ( self::is_htaccess_written( $module ) ) {
			return false;
		}

		$htaccess_file = get_home_path() . '.htaccess';

		if ( self::is_htaccess_writable() ) {
			$code = wphb_get_code_snippet( $module, 'apache' );
			$code = explode( "\n", $code );
			return insert_with_markers( $htaccess_file, 'WP-HUMMINGBIRD-' . strtoupper( $module ), $code );
		}

		return false;
	}

	/**
	 * Remove rules from .htaccess file.
	 *
	 * @param $module
	 *
	 * @return bool
	 */
	public static function unsave_htaccess( $module ) {
		if ( ! self::is_htaccess_written( $module ) ) {
			return false;
		}

		$htaccess_file = get_home_path() . '.htaccess';

		if ( self::is_htaccess_writable() ) {
			return insert_with_markers( $htaccess_file, 'WP-HUMMINGBIRD-' . strtoupper( $module ), '' );
		}

		return false;
	}

}