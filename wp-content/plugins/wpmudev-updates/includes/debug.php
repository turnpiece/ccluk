<?php

/*
Creates debug output on current environment.

Adapted from Query Monitor plugin, Copyright 2013 John Blackbourn, https://github.com/johnbillion/QueryMonitor
*/

class WPMUDEV_Debug {

	public $data;
	var $php_vars = array(
		'max_execution_time',
		'open_basedir',
		'memory_limit',
		'upload_max_filesize',
		'post_max_size',
		'display_errors',
		'log_errors',
		'track_errors',
		'session.auto_start',
		'session.cache_expire',
		'session.cache_limiter',
		'session.cookie_domain',
		'session.cookie_httponly',
		'session.cookie_lifetime',
		'session.cookie_path',
		'session.cookie_secure',
		'session.gc_divisor',
		'session.gc_maxlifetime',
		'session.gc_probability',
		'session.referer_check',
		'session.save_handler',
		'session.save_path',
		'session.serialize_handler',
		'session.use_cookies',
		'session.use_only_cookies'
	);

	function __construct() {
		$this->process();
	}

	public static function get_error_levels( $error_reporting ) {

		$levels = array();

		$constants = array(
			'E_ERROR',
			'E_WARNING',
			'E_PARSE',
			'E_NOTICE',
			'E_CORE_ERROR',
			'E_CORE_WARNING',
			'E_COMPILE_ERROR',
			'E_COMPILE_WARNING',
			'E_USER_ERROR',
			'E_USER_WARNING',
			'E_USER_NOTICE',
			'E_STRICT',
			'E_RECOVERABLE_ERROR',
			'E_DEPRECATED',
			'E_USER_DEPRECATED',
			'E_ALL'
		);

		foreach ( $constants as $level ) {
			if ( defined( $level ) ) {
				$c = constant( $level );
				if ( $error_reporting & $c ) {
					$levels[ $c ] = $level;
				}
			}
		}

		return $levels;
	}

	public static function format_constant( $constant ) {
		if ( ! defined( $constant ) ) {
			return 'undefined';
		} else if ( ! is_bool( $constant ) ) {
			return constant( $constant );
		} else if ( ! constant( $constant ) ) {
			return 'false';
		} else {
			return 'true';
		}
	}

	function process() {
		global $wp_version, $wpdb, $wpmudev_un;

		$mysql_vars = array(
			'key_buffer_size'    => true, # Key cache size limit
			'max_allowed_packet' => false, # Individual query size limit
			'max_connections'    => false, # Max number of client connections
			'query_cache_limit'  => true, # Individual query cache size limit
			'query_cache_size'   => true, # Total cache size limit
			'query_cache_type'   => 'ON' # Query cache on or off
		);

		$variables = $wpdb->get_results( "
			SHOW VARIABLES
			WHERE Variable_name IN ( '" . implode( "', '", array_keys( $mysql_vars ) ) . "' )
		" );

		if ( is_resource( $wpdb->dbh ) ) {
			$version = mysql_get_server_info( $wpdb->dbh );
			$driver  = 'mysql';
		} else if ( is_object( $wpdb->dbh ) and method_exists( $wpdb->dbh, 'db_version' ) ) {
			$version = $wpdb->dbh->db_version();
			$driver  = get_class( $wpdb->dbh );
		} else {
			$version = $driver = '<span class="qm-warn">' . __( 'Unknown', 'wpmudev' ) . '</span>';
		}

		$this->data['db'] = array(
			'version'   => $version,
			'driver'    => $driver,
			'vars'      => $mysql_vars,
			'variables' => $variables
		);

		$this->data['php']['version'] = phpversion();

		foreach ( $this->php_vars as $setting ) {
			$this->data['php']['variables'][ $setting ] = @ini_get( $setting );
		}

		$this->data['php']['extensions'] = get_loaded_extensions();
		natcasesort( $this->data['php']['extensions'] );

		$this->data['php']['error_reporting'] = error_reporting();

		# @TODO put WP's other debugging constants in here, eg. SCRIPT_DEBUG
		$this->data['wp'] = array(
			'Version'                => $wp_version,
			'ABSPATH'                => self::format_constant( 'ABSPATH' ),
			'WP_CONTENT_DIR'         => self::format_constant( 'WP_CONTENT_DIR' ),
			'WP_PLUGINS_DIR'         => self::format_constant( 'WP_PLUGINS_DIR' ),
			'SUNRISE'                => self::format_constant( 'SUNRISE' ),
			'UPLOADBLOGSDIR'         => self::format_constant( 'UPLOADBLOGSDIR' ),
			'UPLOADS'                => self::format_constant( 'UPLOADS' ),
			'SUBDOMAIN_INSTALL'      => self::format_constant( 'SUBDOMAIN_INSTALL' ),
			'DOMAIN_CURRENT_SITE'    => self::format_constant( 'DOMAIN_CURRENT_SITE' ),
			'PATH_CURRENT_SITE'      => self::format_constant( 'PATH_CURRENT_SITE' ),
			'SITE_ID_CURRENT_SITE'   => self::format_constant( 'SITE_ID_CURRENT_SITE' ),
			'BLOGID_CURRENT_SITE'    => self::format_constant( 'BLOGID_CURRENT_SITE' ),
			'COOKIE_DOMAIN'          => self::format_constant( 'COOKIE_DOMAIN' ),
			'COOKIEPATH'             => self::format_constant( 'COOKIEPATH' ),
			'SITECOOKIEPATH'         => self::format_constant( 'SITECOOKIEPATH' ),
			'DISABLE_WP_CRON'        => self::format_constant( 'DISABLE_WP_CRON' ),
			'ALTERNATE_WP_CRON'      => self::format_constant( 'ALTERNATE_WP_CRON' ),
			'DISALLOW_FILE_MODS'     => self::format_constant( 'DISALLOW_FILE_MODS' ),
			'WP_HTTP_BLOCK_EXTERNAL' => self::format_constant( 'WP_HTTP_BLOCK_EXTERNAL' ),
			'WP_ACCESSIBLE_HOSTS'    => self::format_constant( 'WP_ACCESSIBLE_HOSTS' ),
		);

		$server = explode( ' ', $_SERVER['SERVER_SOFTWARE'] );
		$server = explode( '/', reset( $server ) );

		if ( isset( $server[1] ) ) {
			$server_version = $server[1];
		} else {
			$server_version = 'Unknown';
		}

		$this->data['server'] = array(
			'name'    => $server[0],
			'version' => $server_version,
			'address' => $_SERVER['SERVER_ADDR'],
			'host'    => @php_uname( 'n' )
		);

		$remote_get                               = wp_remote_get( $wpmudev_un->server_url );
		$remote_post                              = wp_remote_post( $wpmudev_un->server_url );
		$remote_paypal                            = wp_remote_post( "https://api-3t.paypal.com/nvp", array( 'body' => '"METHOD=SetExpressCheckout&VERSION=63.0&USER=xxxxx&PWD=xxxxx&SIGNATURE=xxxxx' ) );
		$this->data['remote']['WPMU DEV: GET']    = is_wp_error( $remote_get ) ? $remote_get->get_error_message() : wp_remote_retrieve_response_message( $remote_get );
		$this->data['remote']['WPMU DEV: POST']   = is_wp_error( $remote_post ) ? $remote_post->get_error_message() : wp_remote_retrieve_response_message( $remote_post );
		$this->data['remote']['PayPal API: POST'] = is_wp_error( $remote_paypal ) ? $remote_paypal->get_error_message() : wp_remote_retrieve_response_message( $remote_paypal );
	}

	function output_html() {

		echo '<table class="form-table widefat wpmudev-debug">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . __( 'Environment', 'wpmudev' ) . '</th>';
		echo '<th>' . __( 'Configuration Details', 'wpmudev' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		echo '<tr valign="top">
		        <th scope="row">PHP</th>
		        <td>';

		echo '<table>';
		echo '<tr>';
		echo '<td>Version</td>';
		echo "<td>{$this->data['php']['version']}</td>";
		echo '</tr>';

		foreach ( $this->data['php']['variables'] as $key => $val ) {
			echo '<tr>';
			echo "<td>{$key}</td>";
			echo "<td>{$val}</td>";
			echo '</tr>';
		}

		$error_levels = implode( '<br/>', self::get_error_levels( $this->data['php']['error_reporting'] ) );
		echo '<tr>';
		echo '<td>error_reporting</td>';
		echo "<td>{$this->data['php']['error_reporting']}<br><span class='qm-info'>{$error_levels}</span></td>";
		echo '</tr>';

		$extensions = implode( ', ', $this->data['php']['extensions'] );
		echo '<tr>';
		echo '<td>Extensions</td>';
		echo "<td><span class='qm-info'>{$extensions}</span></td>";
		echo '</tr>';

		echo '</table>';

		echo '</td></tr>';

		if ( isset( $this->data['db'] ) ) {

			echo '<tr valign="top">
		        <th scope="row">MySQL</th>
		        <td>';

			echo '<table>';
			echo '<tr>';
			echo '<td>Version</td>';
			echo '<td>' . $this->data['db']['version'] . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td>Driver</td>';
			echo '<td>' . $this->data['db']['driver'] . '</td>';
			echo '</tr>';

			foreach ( $this->data['db']['variables'] as $setting ) {

				$key = $setting->Variable_name;
				$val = $setting->Value;

				if ( is_numeric( $val ) and ( $val >= ( 1024 * 1024 ) ) ) {
					$val = size_format( $val );
				}

				echo "<tr>";

				$key = esc_html( $key );
				$val = esc_html( $val );

				echo "<td>{$key}</td>";
				echo "<td>{$val}</td>";

				echo '</tr>';
			}
			echo '</table>';

			echo '</td></tr>';
		}


		echo '<tr valign="top">
		        <th scope="row">WordPress</th>
		        <td>';
		echo '<table>';
		foreach ( $this->data['wp'] as $key => $val ) {
			echo "<tr>";
			echo "<td>{$key}</td>";
			echo "<td>{$val}</td>";
			echo '</tr>';
		}
		echo '</table>';
		echo '</td></tr>';


		echo '<tr valign="top">
		        <th scope="row">' . __( 'Web Server', 'wpmudev' ) . '</th>
		        <td>';
		echo '<table>';
		echo '<tr>';
		echo '<td>Software</td>';
		echo "<td>{$this->data['server']['name']}</td>";
		echo '</tr>';

		echo '<tr>';
		echo '<td>Version</td>';
		echo "<td>{$this->data['server']['version']}</td>";
		echo '</tr>';

		echo '<tr>';
		echo '<td>Address</td>';
		echo "<td>{$this->data['server']['address']}</td>";
		echo '</tr>';

		echo '<tr>';
		echo '<td>Host</td>';
		echo "<td>{$this->data['server']['host']}</td>";
		echo '</tr>';
		echo '</table>';
		echo '</td></tr>';


		echo '<tr valign="top">
		        <th scope="row">' . __( 'Remote HTTP Requests', 'wpmudev' ) . '</th>
		        <td>';
		echo '<table>';
		foreach ( $this->data['remote'] as $key => $val ) {
			echo "<tr>";
			echo "<td>{$key}</td>";
			echo "<td>{$val}</td>";
			echo '</tr>';
		}
		echo '</table>';
		echo '</td></tr>';


		echo '</tbody>';
		echo '</table>';

	}

}