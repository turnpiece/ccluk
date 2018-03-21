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
		// Only run tests in admin.
		if ( ! is_admin() ) {
			return;
		}

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
	 * Return the server type (Apache, NGINX...)
	 *
	 * @return string Server type
	 */
	public static function get_server_type() {
		global $is_apache, $is_IIS, $is_iis7, $is_nginx;

		$type = get_site_option( 'wphb-server-type' );
		$user_type = get_user_meta( get_current_user_id(), 'wphb-server-type', true );
		if ( $user_type ) {
			$type = $user_type;
		}

		if ( ! $type ) {
			$type = '';

			if ( $is_apache ) {
				// It's a common configuration to use nginx in front of Apache.
				// Let's make sure that this server is Apache
				$response = wp_remote_get( home_url() );

				if ( is_wp_error( $response ) ) {
					// Bad luck
					$type = 'apache';
				} else {
					$server = strtolower( wp_remote_retrieve_header( $response, 'server' ) );
					// Could be LiteSpeed too
					$type = strpos( $server, 'nginx' ) !== false ? 'nginx' : 'apache';
					update_site_option( 'wphb-server-type', $type );
				}
			} elseif ( $is_nginx ) {
				$type = 'nginx';
				update_site_option( 'wphb-server-type', $type );
			} elseif ( $is_IIS ) {
				$type = 'IIS';
				update_site_option( 'wphb-server-type', $type );
			} elseif ( $is_iis7 ) {
				$type = 'IIS 7';
				update_site_option( 'wphb-server-type', $type );
			}
		}

		return apply_filters( 'wphb_get_server_type', $type );
	}

	/**
	 * Get a list of server types
	 *
	 * @return array
	 */
	public static function get_servers() {
		return array(
			'apache'     => 'Apache',
			'LiteSpeed'  => 'LiteSpeed',
			'nginx'      => 'NGINX',
			'iis'        => 'IIS',
			'iis-7'      => 'IIS 7',
			'cloudflare' => 'Cloudflare',
		);
	}

	/**
	 * Get code snippet for a module and server type
	 *
	 * @param string $module Module name.
	 * @param string $server_type Server type (nginx, apache...).
	 * @param array  $expiry_times Type expiry times (javascript, css...).
	 *
	 * @return string Code snippet
	 */
	public static function get_code_snippet( $module, $server_type = '', $expiry_times = array() ) {
		/** @var WP_Hummingbird_Module_Server $module */
		$module = WP_Hummingbird_Utils::get_module( $module );
		if ( ! $module ) {
			return '';
		}

		if ( ! $server_type ) {
			$server_type = self::get_server_type();
		}

		return apply_filters( 'wphb_code_snippet', $module->get_server_code_snippet( $server_type, $expiry_times ), $server_type, $module );
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
			$code = self::get_code_snippet( $module, 'apache' );
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