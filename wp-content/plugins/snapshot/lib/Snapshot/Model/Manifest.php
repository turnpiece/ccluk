<?php // phpcs:ignore

class Snapshot_Model_Manifest {

	const FALLBACK = 0;
	const LINE_DELIMITER = "\n";
	const ENTRY_DELIMITER = ':';

	private $_data = array();
	private $_backup;

	private function __construct() {}

	public static function create( Snapshot_Helper_Backup $backup ) {
		$me = new self();
		$me->_create_manifest( $backup );
		return $me;
	}

	public static function consume( $path ) {
		$me = new self();
		$me->_consume_manifest( $path );
		return $me;
	}

	public static function get_file_name() {
		return 'snapshot_manifest.txt';
	}


	public function get( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			return $this->_data[ $key ];
		}
		return $this->_get_live_value( $key );
	}

	public function get_all() {
		return $this->_data;
	}

	public function get_flat() {
		if ( empty( $this->_data ) ) {
			return '';
		}

		$result = '';
		$delimiter = self::ENTRY_DELIMITER;
		$eol = self::LINE_DELIMITER;
		foreach ( $this->_data as $key => $value ) {
			$value = maybe_serialize( $value );
			$result .= $key . $delimiter . $value . $eol;
		}
		return $result;
	}

	public function get_headers() {
		return array(
			'SNAPSHOT_VERSION',
			'WP_BLOG_ID',
			'MULTISITE',
			'WP_MULTISITE_MAIN_SITE',
			'WP_HOME',
			'WP_SITEURL',
			'WP_BLOG_NAME',
			'WP_BLOG_DOMAIN',
			'WP_BLOG_PATH',
			'WP_VERSION',
			'WP_DB_VERSION',
			'WP_DB_NAME',
			'WP_DB_BASE_PREFIX',
			'WP_DB_PREFIX',
			'WP_DB_CHARSET_COLLATE',
			'WP_UPLOAD_PATH',
			'WP_UPLOAD_URLS',
			'SEGMENT_SIZE',
			'QUEUES',
		);
	}

	public function get_fallback_value() {
		return self::FALLBACK;
	}


	protected function _get_snapshot_version() {
		return '3.0';
	}

	protected function _get_wp_blog_id() {
		if ( ! empty( $this->_backup ) && is_callable( array( $this->_backup, 'get_blog_id' ) ) ) {
			return $this->_backup->get_blog_id();
		}

		global $wpdb;
		return (int) $wpdb->blogid;
	}

	protected function _get_multisite() {
		return is_multisite() ? 1 : 0;
	}

	protected function _get_wp_multisite_main_site() {
		$blog_id = $this->_get_wp_blog_id();
		return is_multisite() && is_main_site( $blog_id ) ? 1 : 0;
	}

	protected function _get_wp_home() {
		$blog_id = $this->_get_wp_blog_id();
		return is_multisite()
			? get_blog_option( $blog_id, 'home' )
			: get_option( 'home' );
	}

	protected function _get_wp_siteurl() {
		$blog_id = $this->_get_wp_blog_id();
		return is_multisite()
			? get_blog_option( $blog_id, 'siteurl' )
			: get_option( 'siteurl' );
	}

	protected function _get_wp_blog_name() {
		if ( is_multisite() ) {
			$blog_id = $this->_get_wp_blog_id();
			$blog_details = get_blog_details( $blog_id );
			return $blog_details->blogname;
		} else {
			return get_option( 'blogname' );
		}
	}

	protected function _get_wp_blog_domain() {
		if ( is_multisite() ) {
			$blog_id = $this->_get_wp_blog_id();
			$blog_details = get_blog_details( $blog_id );
			return $blog_details->domain;
		} else {
			$parts = wp_parse_url( $this->_get_wp_home() );
			return isset( $parts['host'] )
				? $parts['host']
				: $this->get_fallback_value();
		}
	}

	protected function _get_wp_blog_path() {
		if ( is_multisite() ) {
			$blog_id = $this->_get_wp_blog_id();
			$blog_details = get_blog_details( $blog_id );
			return $blog_details->path;
		} else {
			$parts = wp_parse_url( $this->_get_wp_home() );
			return isset( $parts['path'] )
				? $parts['path']
				: $this->get_fallback_value();
		}
	}

	protected function _get_wp_version() {
		global $wp_version;
		return $wp_version;
	}

	protected function _get_wp_db_version() {
		global $wp_db_version;
		return $wp_db_version;
	}

	protected function _get_wp_db_name() {
		return Snapshot_Helper_Utility::get_db_name();
	}

	protected function _get_wp_db_base_prefix() {
		global $wpdb;
		return $wpdb->base_prefix;
	}

	protected function _get_wp_db_prefix() {
		global $wpdb;
		return $wpdb->get_blog_prefix( $this->_get_wp_blog_id() );
	}

	protected function _get_wp_db_charset_collate () {
		global $wpdb;
		return $wpdb->get_charset_collate();
	}

	protected function _get_wp_upload_path() {
		return Snapshot_Helper_Utility::get_blog_upload_path( $this->_get_wp_blog_id(), 'basedir' );
	}

	protected function _get_wp_upload_urls() {
		return Snapshot_Helper_Utility::get_blog_upload_path( $this->_get_wp_blog_id(), 'baseurl' );
	}

	protected function _get_segment_size() {
		return (int) WPMUDEVSnapshot::instance()->config_data['config']['segmentSize'];
	}

	protected function _get_queues() {
		$queues = array();
		if ( ! empty( $this->_backup ) && is_callable( array( $this->_backup, 'get_queues' ) ) ) {
			$queues = $this->_backup->get_queues();
			// Normalize manifest queues for backwards compatibility.
			if ( is_array( $queues ) ) {
				foreach ( $queues as $idx => $queue ) {
					if ( empty( $queue['type'] ) ) {
						continue;
					}
					if ( 'bhfileset' !== $queue['type'] ) {
						continue;
					}
					$queues[ $idx ]['type'] = 'fileset';
					break;
				}
			} else {
				$queues = array();
			}
		}
		return $queues;
	}


	private function _get_live_value( $header ) {
		$headers = $this->get_headers();
		if ( ! in_array( $header, $headers, true ) ) {
			return $this->get_fallback_value();
		}

		$method_name = '_get_' . strtolower( preg_replace( '/[^_a-z]/i', '_', $header ) );
		if ( ! is_callable( array( $this, $method_name ) ) ) {
			$method_name = 'get_fallback_value';
		}

		if ( ! is_callable( array( $this, $method_name ) ) ) {
			return $this->get_fallback_value();
		}

		return call_user_func( array( $this, $method_name ) );
	}

	private function _create_manifest( Snapshot_Helper_Backup $backup ) {
		$this->_backup = $backup;
		$headers = $this->get_headers();
		foreach ( $headers as $header ) {
			$this->_data[ $header ] = $this->_get_live_value( $header );
		}
	}

	private function _consume_manifest( $path ) {
		$fullpath = realpath( $path );
		if ( empty( $fullpath ) || ! is_readable( $fullpath ) ) {
			return false;
		}

		$raw = file_get_contents( $fullpath ); // phpcs:ignore

		if ( empty( $raw ) ) {
			return false;
		}

		$data = explode( self::LINE_DELIMITER, $raw );
		if ( empty( $data ) ) {
			return false;
		}

		foreach ( $data as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			list($key, $value) = explode( self::ENTRY_DELIMITER, $line, 2 );
			$value = maybe_unserialize( $value );
			$value = $this->_untrim( $value );

			$this->_data[ $key ] = $value;
		}

		return true;
	}

	private function _untrim( $value ) {
		if ( ! is_array( $value ) ) {
			if ( is_numeric( $value ) && ! strstr( $value, '.' ) ) {
				$value = (int) $value;
			} else {
				$value = trim( $value );
			}

			return $value;
		}
		foreach ( $value as $key => $val ) {
			$value[ $key ] = $this->_untrim( $val );
		}
		return $value;
	}

}