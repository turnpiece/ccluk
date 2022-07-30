<?php
/**
 * Shipper models: Server system info
 *
 * @package shipper
 */

/**
 * Server info model class
 */
class Shipper_Model_System_Server extends Shipper_Model {

	const TYPE = 'type';
	const OS   = 'os';

	const WORKING_DIR = 'working_directory';
	const STORAGE_DIR = 'storage_directory';
	const TEMP_DIR    = 'temp_directory';
	const LOG_DIR     = 'log_directory';

	const ACCESS_PROTECTED = 'access_protected';

	const SRV_APACHE = 'apache';
	const SRV_IIS    = 'microsoft-iis';
	const SRV_NGINX  = 'nginx';
	const SRV_OTHER  = 'other';

	/**
	 * Constructor
	 *
	 * Populates internal data structure
	 */
	public function __construct() {
		$this->populate();
	}

	/**
	 * Populates internal data structure
	 */
	public function populate() {
		$server = ! empty( $_SERVER['SERVER_SOFTWARE'] )
			? esc_url_raw( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) )
			: '';
		if ( false !== stripos( $server, self::SRV_APACHE ) ) {
			$this->set( self::TYPE, self::SRV_APACHE );
		} elseif ( false !== stripos( $server, self::SRV_IIS ) ) {
			$this->set( self::TYPE, self::SRV_IIS );
		} elseif ( false !== stripos( $server, self::SRV_NGINX ) ) {
			$this->set( self::TYPE, self::SRV_NGINX );
		} else {
			$this->set( self::TYPE, self::SRV_OTHER );
		}

		$os = php_uname( 's' );
		$this->set( self::OS, ( ! empty( $os ) ? $os : 'unknown' ) );

		$this->populate_server_paths_info();

		$this->check_password_protection();
	}

	/**
	 * Checks for password protection on AJAX endpoint
	 */
	public function check_password_protection() {
		$resp = wp_remote_head(
			admin_url( 'admin-ajax.php' ),
			array(
				'timeout' => 1,
			)
		);
		$code = (int) wp_remote_retrieve_response_code( $resp );

		$protected = 401 === $code;
		$this->set( self::ACCESS_PROTECTED, $protected );
	}

	/**
	 * Populates FS paths info
	 */
	public function populate_server_paths_info() {
		$this->populate_server_path_info(
			self::WORKING_DIR,
			Shipper_Helper_Fs_Path::get_working_dir()
		);
		$this->populate_server_path_info(
			self::TEMP_DIR,
			Shipper_Helper_Fs_Path::get_temp_dir()
		);
		$this->populate_server_path_info(
			self::STORAGE_DIR,
			Shipper_Helper_Fs_Path::get_storage_dir()
		);
		$this->populate_server_path_info(
			self::LOG_DIR,
			Shipper_Helper_Fs_Path::get_log_dir()
		);
	}

	/**
	 * Populates info for a particular path
	 *
	 * @param string $key One of the *_DIR constants.
	 * @param string $dir Full path to a directory.
	 */
	public function populate_server_path_info( $key, $dir ) {
		$this->set( $key, $dir );

		$this->set(
			sprintf( '%s_writable', $key ),
			file_exists( $dir ) && is_dir( $dir ) && is_writable( $dir )
		);

		$this->set(
			sprintf( '%s_visible', $key ),
			(int) shipper_is_dir_visible( $dir )
		);

		$url  = shipper_path_to_url( $dir );
		$code = 100;
		if ( ! empty( $url ) ) {
			$resp = wp_remote_head(
				$url,
				array(
					'timeout' => 1,
				)
			);
			$code = (int) wp_remote_retrieve_response_code( $resp );
		}
		$this->set(
			sprintf( '%s_accessible', $key ),
			(int) ( $code >= 200 && $code < 300 )
		);
	}

	/**
	 * Converts internal server type representation to a string
	 *
	 * @param string $type Internal server type constant value.
	 *
	 * @return string
	 */
	public static function get_type_name( $type = '' ) {
		if ( self::SRV_APACHE === $type ) {
			return __( 'Apache', 'shipper' );
		}

		if ( self::SRV_IIS === $type ) {
			return __( 'Microsoft IIS', 'shipper' );
		}

		if ( self::SRV_NGINX === $type ) {
			return __( 'Nginx', 'shipper' );
		}

		return __( 'something else', 'shipper' );
	}

	/**
	 * Get value formatted nicely for output
	 *
	 * @param string $key Value key.
	 * @param mixed  $fallback What to use as fallback.
	 *
	 * @return string
	 */
	public function get_output_value( $key, $fallback = false ) {
		if ( self::TYPE === $key ) {
			return $this->get_type_name( $this->get( $key, $fallback ) );
		}

		if ( preg_match( '/_directory_(writable|visible|accessible)/', $key ) ) {
			$value = $this->get( $key, $fallback );
			return ! empty( $value )
				? __( 'Yes', 'shipper' )
				: __( 'No', 'shipper' );
		}

		return $this->get( $key, $fallback );
	}
}