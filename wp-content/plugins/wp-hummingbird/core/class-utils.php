<?php
/**
 * Class WP_Hummingbird_Utils holds common functions used by the plugin.
 *
 * Class has the following structure:
 * I.   General helper functions
 * II.  Layout functions
 * III. Time and date functions
 * IV.  Link and url functions
 * V.   Modules functions
 *
 * @package Hummingbird
 * @since 1.8
 */

class WP_Hummingbird_Utils {

	/***************************
	 *
	 * I. General helper functions
	 *
	 * is_member()
	 * is_dash_logged_in()
	 * src_to_path()
	 * enqueue_admin_scripts()
	 * get_modal()
	 * get_admin_capability()
	 * get_display_name()
	 * get_current_user_info()
	 * can_execute_php()
	 * get_http2_status()
	 * get_status()
	 * calculate_sum()
	 * sanitize_bool()
	 * format_bytes()
	 * format_interval()
	 ***************************/

	/**
	 * Check if user is a paid one in WPMU DEV
	 *
	 * @return bool
	 */
	public static function is_member() {
		if ( function_exists( 'is_wpmudev_member' ) ) {
			return is_wpmudev_member();
		}

		return false;
	}

	/**
	 * Check if WPMU DEV Dashboard Plugin is logged in
	 *
	 * @return bool
	 */
	public static function is_dash_logged_in() {
		if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			return false;
		}

		if ( ! is_object( WPMUDEV_Dashboard::$api ) ) {
			return false;
		}

		if ( ! method_exists( WPMUDEV_Dashboard::$api, 'has_key' ) ) {
			return false;
		}

		return WPMUDEV_Dashboard::$api->has_key();
	}

	/**
	 * Try to cast a source URL to a path
	 *
	 * @param string $src  Source.
	 *
	 * @return string
	 */
	public static function src_to_path( $src ) {
		$path = wp_parse_url( $src );

		// Scheme will not be set on a URL.
		$url = isset( $path['scheme'] );

		$path = ltrim( $path['path'], '/' );

		/**
		 * DOCUMENT_ROOT does not always store the correct path. For example, Bedrock appends /wp/ to the default dir.
		 * So if the source is a URL, we can safely use DOCUMENT_ROOT, else see if ABSPATH is defined.
		 */
		if ( $url ) {
			$path = path_join( $_SERVER['DOCUMENT_ROOT'], $path );
		} else {
			$root = defined( 'ABSPATH' ) ? ABSPATH : $_SERVER['DOCUMENT_ROOT'];
			$path = path_join( $root, $path );
		}

		return apply_filters( 'wphb_src_to_path', $path, $src );
	}

	/**
	 * Enqueues admin scripts
	 *
	 * @param int $ver Current version number of scripts.
	 */
	public static function enqueue_admin_scripts( $ver ) {
		wp_enqueue_script( 'wphb-admin', WPHB_DIR_URL . 'admin/assets/js/wphb-app.min.js', array( 'jquery', 'underscore' ), $ver, true );

		$i10n = array(
			'errorCachePurge'        => __(
				'There was an error during the cache purge. Check folder permissions are 755
										for /wp-content/wphb-cache or delete directory manually.',
				'wphb'
			),
			'successGravatarPurge'   => __( 'Gravatar cache purged.', 'wphb' ),
			'successPageCachePurge'  => __( 'Page cache purged.', 'wphb' ),
			'errorRecheckStatus'     => __( 'There was an error re-checking the caching status, please try again later.', 'wphb' ),
			'successRecheckStatus'   => __( 'Browser caching status updated.', 'wphb' ),
			'successCloudflarePurge' => __( 'Cloudflare cache successfully purged. Please wait 30 seconds for the purge to complete.', 'wphb' ),
		);
		wp_localize_script( 'wphb-admin', 'wphbCachingStrings', $i10n );

		$i10n = array(
			'finishedTestURLsLink' => self::get_admin_menu_url( 'performance' ) . '&view=audits',
			'removeButtonText'     => __( 'Remove', 'wphb' ),
			'youLabelText'         => __( 'You', 'wphb' ),
			'scanRunning'          => __( 'Running speed test...', 'wphb' ),
			'scanAnalyzing'        => __( 'Analyzing data and preparing report...', 'wphb' ),
			'scanWaiting'          => __( 'Test is taking a little longer than expected, hang in there…', 'wphb' ),
			'scanComplete'         => __( 'Test complete! Reloading…', 'wphb' ),
		);
		wp_localize_script( 'wphb-admin', 'wphbPerformanceStrings', $i10n );

		$i10n = array(
			'finishedTestURLsLink' => self::get_admin_menu_url(),
		);
		wp_localize_script( 'wphb-admin', 'wphbDashboardStrings', $i10n );

		$url  = add_query_arg( '_wpnonce', wp_create_nonce( 'wphb-toggle-uptime' ), self::get_admin_menu_url( 'uptime' ) );
		$i10n = array(
			'enableUptimeURL'  => add_query_arg( 'action', 'enable', $url ),
			'disableUptimeURL' => add_query_arg( 'action', 'disable', $url ),
		);
		wp_localize_script( 'wphb-admin', 'wphbUptimeStrings', $i10n );

		/* @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf   = self::get_module( 'cloudflare' );
		$i10n = array(
			'cloudflare' => array(
				'is' => array(
					'connected' => $cf->is_connected() && $cf->is_zone_selected(),
				),
			),
			'nonces'     => array(
				'HBFetchNonce' => wp_create_nonce( 'wphb-fetch' ),
			),
			'urls'       => array(
				'cachingEnabled' => add_query_arg( 'view', 'caching', self::get_admin_menu_url( 'caching' ) ),
                'resetSettings'  => add_query_arg( 'wphb-clear', 'all', WP_Hummingbird_Utils::get_admin_menu_url() ),
			),
			'strings'    => array(
				'htaccessUpdated'       => __( 'Your .htaccess file has been updated', 'wphb' ),
				'htaccessUpdatedFailed' => __( 'There was an error updating the .htaccess file', 'wphb' ),
				'errorSettingsUpdate'   => __( 'Error updating settings', 'wphb' ),
				'successUpdate'         => __( 'Settings updated', 'wphb' ),
				'deleteAll'             => __( 'Delete All', 'wphb' ),
				'db_delete'             => __( 'Are you sure you wish to delete', 'wphb' ),
				'db_entries'            => __( 'database entries', 'wphb' ),
				'db_backup'             => __( 'Make sure you have a current backup just in case.', 'wphb' ),
				'successRecipientAdded' => __( ' has been added as a recipient but you still need to save your changes below to set this live.', 'wphb' ),
			),
		);

		if ( self::can_execute_php() ) {
			/* @var WP_Hummingbird_Module_Minify $minify_module */
			$minify_module = self::get_module( 'minify' );

			$is_scanning = $minify_module->scanner->is_scanning();

			if ( $minify_module->is_on_page() || $is_scanning ) {
				$i10n = array_merge_recursive(
					$i10n,
					array(
						'minification' => array(
							'is'  => array(
								'scanning' => $is_scanning,
								'scanned'  => $minify_module->scanner->is_files_scanned(),
							),
							'get' => array(
								'currentScanStep' => $minify_module->scanner->get_current_scan_step(),
								'totalSteps'      => $minify_module->scanner->get_scan_steps(),
								'showCDNModal'    => ! is_multisite(),
							),
						),
						'strings'      => array(
							'discardAlert'  => __( 'Are you sure? All your changes will be lost', 'wphb' ),
							'queuedTooltip' => __( 'This file is queued for compression. It will get optimized when someone visits a page that requires it.', 'wphb' ),
							'excludeFile'   => __( "Don't load file", 'wphb' ),
							'includeFile'   => __( 'Re-include', 'wphb' ),
						),
						'links'        => array(
							'minification' => self::get_admin_menu_url( 'minification' ),
						),
					)
				);
			} // End if().
		}

		wp_localize_script( 'wphb-admin', 'wphb', $i10n );
	}

	/**
	 * Get modal file by type.
	 *
	 * @param string $type Accepts: bulk-update, check-files, check-performance, dismiss-report, membership,
	 *                     minification-advanced, minification-basic, quick-setup, database-cleanup.
	 */
	public static function get_modal( $type ) {
		if ( empty( $type ) ) {
			return;
		}

		$type = strtolower( $type );
		$file = WPHB_DIR_PATH . "admin/views/modals/{$type}-modal.php";

		if ( file_exists( $file ) ) {
			/* @noinspection PhpIncludeInspection */
			include_once $file;
		}
	}

	/**
	 * Return the needed capability for admin pages.
	 *
	 * @return string
	 */
	public static function get_admin_capability() {
		$cap = 'manage_options';

		if ( is_multisite() && is_network_admin() ) {
			$cap = 'manage_network';
		}

		return apply_filters( 'wphb_admin_capability', $cap );
	}

	/**
	 * Get display name.
	 *
	 * @since 1.4.5
	 *
	 * @param int $id User ID.
	 *
	 * @return null|string
	 */
	public static function get_display_name( $id ) {
		$user = get_user_by( 'id', $id );

		if ( ! is_object( $user ) ) {
			return null;
		}

		if ( ! empty( $user->user_nicename ) ) {
			return $user->user_nicename;
		}

		return $user->user_firstname . ' ' . $user->user_lastname;
	}

	/**
	 * Get Current username info
	 */
	public static function get_current_user_info() {
		$current_user = wp_get_current_user();

		if ( ! ( $current_user instanceof WP_User ) ) {
			return false;
		}

		if ( ! empty( $current_user->user_firstname ) ) { // First we try to grab user First Name.
			return $current_user->user_firstname;
		}

		// Grab user nicename.
		return $current_user->user_nicename;
	}

	/**
	 * Get the default user data for the report.
     *
     * @since 1.9.4
     *
     * @return array
	 */
	public static function get_user_for_report() {
		/** Current user @var WP_User $user */
		$user = wp_get_current_user();

		if ( empty( $user->first_name ) && empty( $user->last_name ) ) {
			$name = $user->user_login;
		} else {
			$name = $user->first_name . ' ' . $user->last_name;
		}

		return array(
			'name'  => $name,
			'email' => $user->user_email,
		);
    }

	/**
	 * Check php version compatibility.
	 *
	 * Asset Optimization requires at least php version 5.3 to work.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public static function can_execute_php() {
		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the HTTP2 status via a curl call if SERVER_PROTOCOL is not set.
	 *
	 * @since 1.7.1
	 */
	public static function get_http2_status() {
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) && 'HTTP/2.0' === $_SERVER['SERVER_PROTOCOL'] ) { // Input var ok.
			return true;
		}

		$status = false;

		$ch = curl_init();
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL            => get_home_url(),
				CURLOPT_HEADER         => true,
				CURLOPT_NOBODY         => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTP_VERSION   => 3, // cURL will attempt to make an HTTP/2.0 request (can downgrade to HTTP/1.1).
			)
		);
		$response = curl_exec( $ch );

		if ( false !== $response && ( 0 === strpos( $response, 'HTTP/2.0' ) || 0 === strpos( $response, 'HTTP/2' ) ) ) {
			$status = true;
		}

		curl_close( $ch );

		return $status;
	}

	/**
	 * Get gzip or caching status data.
	 *
	 * @param string $module  Accepts: 'caching', 'gzip'.
	 * @param bool   $api     Query API (if needed).
	 *
	 * @return array|bool
	 */
	public static function get_status( $module, $api = false ) {
		if ( ! in_array( $module, array( 'gzip', 'caching' ), true ) ) {
			return false;
		}

		/* @var WP_Hummingbird_Module_GZip|WP_Hummingbird_Module_Caching $mod */
		$mod = self::get_module( $module );

		// Get caching/gzip data from the api.
		if ( $api ) {
			$mod->get_analysis_data( true, true );
			return $mod->status;
		}

		$mod->get_analysis_data();
		return $mod->status;
	}

	/**
	 * This function will calculate the sum of file sizes in an array.
	 *
	 * We need this, because Asset Optimization module will store 'original_size' and 'compressed_size' values as
	 * strings, and such strings will contain &nbsp; instead of spaces, thus making it impossible to sum all the
	 * values with array_sum().
	 *
	 * @since 1.9.2
	 *
	 * @param array $arr  Array of items with sizes as strings.
	 *
	 * @return int|mixed
	 */
	public static function calculate_sum( $arr ) {
		$sum = 0;

		foreach ( $arr as $item => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}

			// Remove spaces.
			$sum += (float) str_replace( '&nbsp;', '', $value );
		}

		return $sum;
	}

	/**
	 * WP function. Available in WP since 4.7.
	 *
	 * @param string|bool $value  Value to sanitize.
	 *
	 * @return bool
	 */
	public static function sanitize_bool( $value ) {
		// String values are translated to `true`; make sure 'false' is false.
		if ( is_string( $value ) ) {
			$value = strtolower( $value );
			if ( in_array( $value, array( 'false', '0' ), true ) ) {
				$value = false;
			}
		}

		// Everything else will map nicely to boolean.
		return (bool) $value;
	}

	/**
	 * Return the file size in a humanly readable format.
	 *
	 * Taken from http://www.php.net/manual/en/function.filesize.php#91477
	 *
	 * @since 2.0.0
	 *
	 * @param int $bytes      Number of bytes.
	 * @param int $precision  Precision.
	 *
	 * @return string
	 */
	public static function format_bytes( $bytes, $precision = 1 ) {
		$units  = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$bytes  = max( $bytes, 0 );
		$pow    = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow    = min( $pow, count( $units ) - 1 );
		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	/**
	 * Convert seconds to a readable value.
	 *
	 * @since 2.0.0
	 *
	 * @param int $seconds  Number of seconds.
	 *
	 * @return string
	 */
	public static function format_interval( $seconds ) {
		if ( 3600 <= $seconds && 86400 > $seconds ) {
			return floor( $seconds / HOUR_IN_SECONDS ) . ' h';
		}

		if ( 86400 <= $seconds && 2419200 > $seconds ) {
			return floor( $seconds / DAY_IN_SECONDS ) . ' d';
		}

		if ( 2419200 <= $seconds && 31536000 > $seconds ) {
			return floor( $seconds / MONTH_IN_SECONDS ) . ' m';
		}

		if ( 31536000 < $seconds && 26611200 >= $seconds ) {
			return floor( $seconds / YEAR_IN_SECONDS ) . ' y';
		}
	}

	/***************************
	 *
	 * II. Layout functions
	 *
	 * get_servers_dropdown()
	 * get_caching_frequencies_dropdown()
	 * get_caching_frequencies()
	 * get_cloudflare_frequencies()
	 * get_recommended_caching_values()
	 * convert_cloudflare_frequency()
	 * get_browser_caching_types()
	 ***************************/

	/**
	 * Get servers dropdown.
	 *
	 * @param array $args        Arguments.
	 * @param bool  $cloudflare  Add Cloudflare to the server list.
	 */
	public static function get_servers_dropdown( $args = array(), $cloudflare = true ) {
		$defaults = array(
			'class'    => '',
			'id'       => '',
			'name'     => 'wphb-server-type',
			'selected' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$servers = WP_Hummingbird_Module_Server::get_servers();

		if ( ! $cloudflare ) {
			unset( $servers['cloudflare'] );
		}

		if ( ! $args['id'] ) {
			$args['id'] = $args['name'];
		}

		if ( ! $args['selected'] ) {
			$args['selected'] = WP_Hummingbird_Module_Server::get_server_type();
		}

		?>
		<select name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>">
			<?php foreach ( $servers as $server => $server_name ) : ?>
				<option value="<?php echo esc_attr( $server ); ?>" <?php selected( $server, $args['selected'] ); ?>><?php echo esc_html( $server_name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Prepare dropdown select with caching expiry settings.
	 *
	 * @param array $args        Arguments list.
	 * @param bool  $cloudflare  Get Cloudflare frequencies.
	 */
	public static function get_caching_frequencies_dropdown( $args = array(), $cloudflare = false ) {
		$defaults = array(
			'selected'  => false,
			'name'      => 'expiry-select',
			'id'        => false,
			'class'     => '',
			'data-type' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! $args['id'] ) {
			$args['id'] = $args['name'];
		}

		if ( $cloudflare ) {
			$frequencies = self::get_cloudflare_frequencies();
		} else {
			$frequencies = self::get_caching_frequencies();
		}

		?>
		<select id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>" data-type="<?php echo esc_attr( $args['data-type'] ); ?>">
			<?php foreach ( $frequencies as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $args['selected'], $key ); ?>><?php echo $value; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Get an array of caching frequencies.
	 *
	 * @return array
	 */
	public static function get_caching_frequencies() {
		return array(
			'1h/A3600'     => __( '1 hour', 'wphb' ),
			'3h/A10800'    => __( '3 hours', 'wphb' ),
			'4h/A14400'    => __( '4 hours', 'wphb' ),
			'5h/A18000'    => __( '5 hours', 'wphb' ),
			'6h/A21600'    => __( '6 hours', 'wphb' ),
			'12h/A43200'   => __( '12 hours', 'wphb' ),
			'16h/A57600'   => __( '16 hours', 'wphb' ),
			'20h/A72000'   => __( '20 hours', 'wphb' ),
			'1d/A86400'    => __( '1 day', 'wphb' ),
			'2d/A172800'   => __( '2 days', 'wphb' ),
			'3d/A259200'   => __( '3 days', 'wphb' ),
			'4d/A345600'   => __( '4 days', 'wphb' ),
			'5d/A432000'   => __( '5 days', 'wphb' ),
			'8d/A691200'   => __( '8 days', 'wphb' ),
			'16d/A1382400' => __( '16 days', 'wphb' ),
			'24d/A2073600' => __( '24 days', 'wphb' ),
			'1M/A2592000'  => __( '1 month', 'wphb' ),
			'2M/A5184000'  => __( '2 months', 'wphb' ),
			'3M/A7776000'  => __( '3 months', 'wphb' ),
			'6M/A15552000' => __( '6 months', 'wphb' ),
			'1y/A31536000' => __( '1 year', 'wphb' ),
		);
	}

	/**
	 * Get an array of caching frequencies for Cloudflare.
	 *
	 * @return array
	 */
	public static function get_cloudflare_frequencies() {
		return array(
			7200     => __( '2 hours', 'wphb' ),
			10800    => __( '3 hours', 'wphb' ),
			14400    => __( '4 hours', 'wphb' ),
			18000    => __( '5 hours', 'wphb' ),
			28800    => __( '8 hours', 'wphb' ),
			43200    => __( '12 hours', 'wphb' ),
			57600    => __( '16 hours', 'wphb' ),
			72000    => __( '20 hours', 'wphb' ),
			86400    => __( '1 day', 'wphb' ),
			172800   => __( '2 days', 'wphb' ),
			259200   => __( '3 days', 'wphb' ),
			345600   => __( '4 days', 'wphb' ),
			432000   => __( '5 days', 'wphb' ),
			691200   => __( '8 days', 'wphb' ),
			1382400  => __( '16 days', 'wphb' ),
			2073600  => __( '24 days', 'wphb' ),
			2592000  => __( '1 month', 'wphb' ),
			5184000  => __( '2 months', 'wphb' ),
			15552000 => __( '6 months', 'wphb' ),
			31536000 => __( '1 year', 'wphb' ),
		);
	}

	/**
	 * Get recommended caching values.
	 *
	 * @return array
	 */
	public static function get_recommended_caching_values() {
		return array(
			'css'        => array(
				'label' => __( '8+ days', 'wphb' ),
				'value' => 8 * 24 * 3600,
			),
			'javascript' => array(
				'label' => __( '8+ days', 'wphb' ),
				'value' => 8 * 24 * 3600,
			),
			'media'      => array(
				'label' => __( '8+ days', 'wphb' ),
				'value' => 8 * 24 * 3600,
			),
			'images'     => array(
				'label' => __( '8+ days', 'wphb' ),
				'value' => 8 * 24 * 3600,
			),
		);
	}

	/**
	 * Convert Cloudflare frequency to normal. Used when updating the custom code in browser caching.
	 *
	 * @param  int $cloudflare_frequency  Cloudflare frequency to convert.
	 *
	 * @return string  Caching frequency.
	 */
	public static function convert_cloudflare_frequency( $cloudflare_frequency ) {
		$frequencies = array(
			7200     => '2h/A7200',
			10800    => '3h/A10800',
			14400    => '4h/A14400',
			18000    => '5h/A18000',
			28800    => '8h/A28800',
			43200    => '12h/A43200',
			57600    => '16h/A57600',
			72000    => '20h/A72000',
			86400    => '1d/A86400',
			172800   => '2d/A172800',
			259200   => '3d/A259200',
			345600   => '4d/A345600',
			432000   => '5d/A432000',
			691200   => '8d/A691200',
			1382400  => '16d/A1382400',
			2073600  => '24d/A2073600',
			2592000  => '1M/A2592000',
			5184000  => '2M/A5184000',
			15552000 => '6M/A15552000',
			31536000 => '1y/A31536000',
		);

		return $frequencies[ $cloudflare_frequency ];
	}


	/**
	 * Get default caching types for HB or CloudFlare.
	 *
	 * @since 1.7.1
	 * @return array
	 */
	public static function get_browser_caching_types() {
		$caching_types = array();

		$caching_types['javascript'] = 'txt | xml | js';
		$caching_types['css']        = 'css';
		$caching_types['media']      = 'flv | ico | pdf | avi | mov | ppt | doc | mp3 | wmv | wav | mp4 | m4v | ogg | webm | aac | eot | ttf | otf | woff | svg';
		$caching_types['images']     = 'jpg | jpeg | png | gif | swf | webp';

		/* @var WP_Hummingbird_Module_Cloudflare $cloudflare */
		$cloudflare = self::get_module( 'cloudflare' );

		if ( $cloudflare->is_connected() && $cloudflare->is_zone_selected() ) {
			$caching_types['javascript'] = 'txt | xml | js';
			$caching_types['css']        = 'css';
			$caching_types['media']      = 'flv | ico | pdf | avi | mov | ppt | doc | mp3 | wmv | wav | mp4 | m4v | ogg | webm | aac | eot | ttf | otf | woff | svg';
			$caching_types['images']     = 'jpg | jpeg | png | gif | swf | webp';
			$caching_types['cloudflare'] = 'bmp | pict | csv | pls | tif | tiff | eps | ejs | midi | mid | woff2 | svgz | docx | xlsx | xls | pptx | ps | class | jar';
		}

		return $caching_types;
	}

	/***************************
	 *
	 * III. Time and date functions
	 *
	 * human_read_time_diff()
	 * get_days_of_week()
	 * get_times()
	 * get_days_of_month()
	 ***************************/

	/**
	 * Credits to: http://stackoverflow.com/a/11389893/1502521
	 *
	 * @param int $seconds  Seconds.
	 *
	 * @return string
	 */
	public static function human_read_time_diff( $seconds ) {
		if ( ! $seconds ) {
			return false;
		}

		$year_in_seconds   = 60 * 60 * 24 * 365;
		$month_in_seconds  = 60 * 60 * 24 * 30;
		$day_in_seconds    = 60 * 60 * 24;
		$hour_in_seconds   = 60 * 60;
		$minute_in_seconds = 60;

		$minutes = 0;
		$hours   = 0;
		$days    = 0;
		$months  = 0;
		$years   = 0;

		while ( $seconds >= $year_in_seconds ) {
			$years ++;
			$seconds = $seconds - $year_in_seconds;
		}

		while ( $seconds >= $month_in_seconds ) {
			$months ++;
			$seconds = $seconds - $month_in_seconds;
		}

		while ( $seconds >= $day_in_seconds ) {
			$days ++;
			$seconds = $seconds - $day_in_seconds;
		}

		while ( $seconds >= $hour_in_seconds ) {
			$hours++;
			$seconds = $seconds - $hour_in_seconds;
		}

		while ( $seconds >= $minute_in_seconds ) {
			$minutes++;
			$seconds = $seconds - $minute_in_seconds;
		}

		$diff = new stdClass();

		$diff->y = $years;
		$diff->m = $months;
		$diff->d = $days;
		$diff->h = $hours;
		$diff->i = $minutes;
		$diff->s = $seconds;

		if ( $diff->y || ( 11 == $diff->m && 30 <= $diff->d ) ) {
			$years = $diff->y;
			if ( 11 == $diff->m && 30 <= $diff->d ) {
				$years++;
			}
			/* translators: %d: year */
			$diff_time = sprintf( _n( '%d year', '%d years', $years, 'wphb' ), $years );
		} elseif ( $diff->m ) {
			/* translators: %d: month */
			$diff_time = sprintf( _n( '%d month', '%d months', $diff->m, 'wphb' ), $diff->m );
		} elseif ( $diff->d ) {
			/* translators: %d: day */
			$diff_time = sprintf( _n( '%d day', '%d days', $diff->d, 'wphb' ), $diff->d );
		} elseif ( $diff->h ) {
			/* translators: %d: hour */
			$diff_time = sprintf( _n( '%d hour', '%d hours', $diff->h, 'wphb' ), $diff->h );
		} elseif ( $diff->i ) {
			/* translators: %d: minute */
			$diff_time = sprintf( _n( '%d minute', '%d minutes', $diff->i, 'wphb' ), $diff->i );
		} else {
			/* translators: %d: second */
			$diff_time = sprintf( _n( '%d second', '%d seconds', $diff->s, 'wphb' ), $diff->s );
		}

		return $diff_time;
	}

	/**
	 * Get days of the week.
	 *
	 * @since 1.4.5
	 *
	 * @return mixed
	 */
	public static function get_days_of_week() {
		$timestamp = strtotime( 'next Monday' );
		if ( 7 === get_option( 'start_of_week' ) ) {
			$timestamp = strtotime( 'next Sunday' );
		}
		$days = array();
		for ( $i = 0; $i < 7; $i ++ ) {
			$days[]    = strftime( '%A', $timestamp );
			$timestamp = strtotime( '+1 day', $timestamp );
		}

		return apply_filters( 'wphb_scan_get_days_of_week', $days );
	}

	/**
	 * Return times frame for select box
	 *
	 * @since 1.4.5
	 *
	 * @return mixed
	 */
	public static function get_times() {
		$data = array();
		for ( $i = 0; $i < 24; $i ++ ) {
			foreach ( apply_filters( 'wphb_scan_get_times_interval', array( '00' ) ) as $min ) {
				$time          = $i . ':' . $min;
				$data[ $time ] = apply_filters( 'wphb_scan_get_times_hour_min', $time );
			}
		}

		return apply_filters( 'wphb_scan_get_times', $data );
	}

	/**
	 * Return days of the month.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	public static function get_days_of_month() {
		$days = array();
		for ( $i = 1; $i <= 28; $i++ ) {
			$days[] = $i;
		}

		return apply_filters( 'wphb_scan_get_days_of_week', $days );
	}

	/**
	 * Local time to UTC.
	 *
	 * @since 1.4.5
	 *
	 * @param string $time  Time string.
	 *
	 * @return false|int
	 */
	public static function local_to_utc( $time ) {
		$tz = get_option( 'timezone_string' );
		if ( ! $tz ) {
			$gmt_offset = get_option( 'gmt_offset' );
			if ( 0 === $gmt_offset ) {
				return strtotime( $time );
			}
			$tz = self::get_timezone_string( $gmt_offset );
		}

		if ( ! $tz ) {
			$tz = 'UTC';
		}
		$timezone = new DateTimeZone( $tz );
		try {
			$time = new DateTime( $time, $timezone );
		} catch ( Exception $e ) {
			error_log( '[' . current_time( 'mysql' ) . '] - Error in local_to_utc(). Error: ' . $e->getMessage() );
		}

		// Had to switch because of PHP 5.2 compatibility issues.
		if ( self::can_execute_php() ) {
			return $time->getTimestamp();
		}

		return $time->format( 'U' );
	}

	/**
	 * Get time zone string.
	 *
	 * @since  1.4.5
	 *
	 * @param  string $timezone  Time zone.
	 *
	 * @return false|string
	 */
	private static function get_timezone_string( $timezone ) {
		$timezone = explode( '.', $timezone );
		if ( isset( $timezone[1] ) ) {
			$timezone[1] = 30;
		} else {
			$timezone[1] = '00';
		}
		$offset                  = implode( ':', $timezone );
		list( $hours, $minutes ) = explode( ':', $offset );
		$seconds                 = $hours * 60 * 60 + $minutes * 60;
		$tz                      = timezone_name_from_abbr( '', $seconds, 1 );
		if ( false === $tz ) {
			$tz = timezone_name_from_abbr( '', $seconds, 0 );
		}

		return $tz;
	}

	/***************************
	 *
	 * IV. Link and url functions
	 *
	 * get_link()
	 * get_documentation_url()
	 * _still_having_trouble_link()
	 * get_admin_menu_url()
	 * get_avatar_url()
	 ***************************/

	/**
	 * Return URL link.
	 *
	 * @param string $link_for Accepts: 'chat', 'plugin', 'support', 'smush', 'docs'.
	 * @param string $campaign  Utm campaign tag to be used in link. Default: 'hummingbird_pro_modal_upgrade'.
	 *
	 * @return string
	 */
	public static function get_link( $link_for, $campaign = 'hummingbird_pro_modal_upgrade' ) {
		$domain   = 'https://premium.wpmudev.org';
		$wp_org   = 'https://wordpress.org';
		$utm_tags = "?utm_source=hummingbird&utm_medium=plugin&utm_campaign={$campaign}";

		switch ( $link_for ) {
			case 'chat':
				$link = "{$domain}/live-support/{$utm_tags}";
				break;
			case 'plugin':
				$link = "{$domain}/project/wp-hummingbird/{$utm_tags}";
				break;
			case 'support':
				if ( self::is_member() ) {
					$link = "{$domain}/forum/support#question{$utm_tags}";
				} else {
					$link = "{$wp_org}/support/plugin/hummingbird-performance";
				}
				break;
			case 'docs':
				$link = "{$domain}/docs/wpmu-dev-plugins/hummingbird/{$utm_tags}";
				break;
			case 'smush':
				if ( self::is_member() ) {
					// Return the pro plugin URL.
					$url  = WPMUDEV_Dashboard::$ui->page_urls->plugins_url;
					$link = $url . '#pid=912164';
				} else {
					// Return the free URL.
					$link = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-smushit' ), 'install-plugin_wp-smushit' );
				}
				break;
			case 'smush-plugin':
				$link = "{$domain}/project/wp-smush-pro/{$utm_tags}";
				break;
			case 'hosting':
				$link = "{$domain}/hub/hosting/{$utm_tags}";
				break;
			default:
				$link = '';
				break;
		}

		return $link;
	}

	/**
	 * Get documentation URL.
	 *
	 * @since 1.7.0
	 *
	 * @param string $page  Page slug.
	 * @param string $view  View slug.
	 *
	 * @return string
	 */
	public static function get_documentation_url( $page, $view = '' ) {
		switch ( $page ) {
			case 'wphb-performance':
				if ( 'reports' === $view ) {
					$anchor = '#reports-pro';
				} else {
					$anchor = '#performance-report';
				}
				break;
			case 'wphb-caching':
				$anchor = '#caching';
				break;
			case 'wphb-gzip':
				$anchor = '#gzip-compression';
				break;
			case 'wphb-minification':
				$anchor = '#minification';
				break;
			case 'wphb-uptime':
				$anchor = '#uptime-monitoring-pro';
				break;
			default:
				$anchor = '';
		}

		return 'https://premium.wpmudev.org/docs/wpmu-dev-plugins/hummingbird/' . $anchor;
	}

	/**
	 * Display start a live chat link for pro user or open support ticket for non-pro user.
	 */
	public static function _still_having_trouble_link() {
		esc_html_e( 'Still having trouble? ', 'wphb' );
		if ( self::is_member() ) :
			?>
			<a target="_blank" href="<?php echo self::get_link( 'chat' ); ?>">
				<?php esc_html_e( 'Start a live chat.', 'wphb' ); ?>
			</a>
		<?php else : ?>
			<a target="_blank" href="<?php echo self::get_link( 'support' ); ?>">
				<?php esc_html_e( 'Open a support ticket.', 'wphb' ); ?>
			</a>
		<?php
		endif;
	}

	/**
	 * Get url for plugin module page.
	 *
	 * @param string $page
	 *
	 * @return string
	 */
	public static function get_admin_menu_url( $page = '' ) {
		/* @var WP_Hummingbird $hummingbird */
		$hummingbird = WP_Hummingbird::get_instance();

		if ( is_object( $hummingbird->admin ) ) {
			$page_slug = empty( $page ) ? 'wphb' : 'wphb-' . $page;
			if ( $page = $hummingbird->admin->get_admin_page( $page_slug ) ) {
				return $page->get_page_url();
			}
		}

		return '';
	}

	/**
	 * Get avatar URL.
	 *
	 * @since 1.4.5
	 *
	 * @param string $get_avatar User email.
	 *
	 * @return mixed
	 */
	public static function get_avatar_url( $get_avatar ) {
		preg_match( "/src='(.*?)'/i", $get_avatar, $matches );

		return $matches[1];
	}

	/***************************
	 *
	 * V. Modules functions
	 *
	 * get_api()
	 * get_modules()
	 * get_module()
	 * get_active_cache_modules()
	 * get_number_of_issues()
	 * minified_files_count()
	 * remove_quick_setup()
	 ***************************/

	/**
	 * Get API.
	 *
	 * @return WP_Hummingbird_API
	 */
	public static function get_api() {
		/* @var WP_Hummingbird $hummingbird */
		$hummingbird = WP_Hummingbird::get_instance();
		return $hummingbird->core->api;
	}

	/**
	 * Return the list of modules and their object instances
	 *
	 * Do not try to load before 'wp_hummingbird_loaded' action has been executed
	 *
	 * @return mixed
	 */
	private static function get_modules() {
		/* @var WP_Hummingbird $hummingbird */
		$hummingbird = WP_Hummingbird::get_instance();
		return $hummingbird->core->modules;
	}

	/**
	 * Get a module instance
	 *
	 * @param string $module Module slug.
	 *
	 * @return bool|WP_Hummingbird_Module|WP_Hummingbird_Module_Page_Cache|WP_Hummingbird_Module_GZip|WP_Hummingbird_Module_Minify|WP_Hummingbird_Module_Cloudflare|WP_Hummingbird_Module_Uptime|WP_Hummingbird_Module_Performance
	 */
	public static function get_module( $module ) {
		$modules = self::get_modules();
		return isset( $modules[ $module ] ) ? $modules[ $module ] : false;
	}

	/**
	 * Get a module instance
	 *
	 * @since 1.9.3
	 *
	 * @param string $module Module slug.
	 *
	 * @return bool|WP_Hummingbird_Module
	 */
	public static function get_pro_module( $module ) {
		/* @var WP_Hummingbird $hummingbird */
		$hummingbird = WP_Hummingbird::get_instance();
		return isset( $hummingbird->pro->modules[ $module ] ) ? $hummingbird->pro->modules[ $module ] : false;
	}

	/**
	 * Return human readable names of active modules that have a cache.
	 *
	 * Checks Page, Gravatar & Asset Optimization.
	 *
	 * @return array
	 */
	public static function get_active_cache_modules() {
		$modules = array(
			'page_cache' => __( 'Page', 'wphb' ),
			'cloudflare' => __( 'CloudFlare', 'wphb' ),
			'gravatar'   => __( 'Gravatar', 'wphb' ),
			'minify'     => __( 'Asset Optimization', 'wphb' ),
		);

		// Remove minification module where php is not supported.
		if ( ! self::can_execute_php() ) {
			unset( $modules['minify'] );
		}

		$hb_modules = self::get_modules();

		foreach ( $modules as $module => $module_name ) {
			// If inactive, skip to next step.
			if ( 'cloudflare' !== $module && isset( $hb_modules[ $module ] ) && ! $hb_modules[ $module ]->is_active() ) {
				unset( $modules[ $module ] );
			}

			// Fix CloudFlare clear cache appearing on dashboard if it had been previously enabled but then uninstalled and reinstalled HB.
			// TODO: do we need this?
			/* @var WP_Hummingbird_Module_Cloudflare $module */
			if ( 'cloudflare' === $module && isset( $hb_modules[ $module ] ) && ! $hb_modules[ $module ]->is_connected() && ! $hb_modules[ $module ]->is_zone_selected() ) {
				unset( $modules[ $module ] );
			}
		}

		return $modules;
	}

	/**
	 * Get the number of issues for selected module
	 *
	 * @since 1.8.1 Added $report parameter.
	 *
	 * @param string     $module Module name.
	 * @param bool|array $report Current report.
	 *
	 * @return int
	 */
	public static function get_number_of_issues( $module, $report = false ) {
		$issues = 0;

		switch ( $module ) {
			case 'caching':
				if ( ! $report ) {
					$report = self::get_status( 'caching' );
				}

				// No report - break.
				if ( ! $report ) {
					break;
				}

				$recommended = self::get_recommended_caching_values();

				foreach ( $report as $type => $value ) {
					if ( empty( $value ) || ( $recommended[ $type ]['value'] > $value ) ) {
						$issues++;
					}
				}
				break;
			case 'gzip':
				if ( ! $report ) {
					$report = self::get_status( 'gzip' );
				}

				// No report - break.
				if ( ! $report ) {
					break;
				}

				$invalid = 0;
				foreach ( $report as $item => $type ) {
					if ( ! $type || 'privacy' === $type ) {
						$invalid++;
					}
				}

				$issues = $invalid;
				break;
		}

		return $issues;
	}

	/**
	 * Return the number of files used by minification.
	 *
	 * @since 1.4.5
	 *
	 * @param bool $only_minified  Only minified files.
	 *
	 * @return int
	 */
	public static function minified_files_count( $only_minified = false ) {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = self::get_module( 'minify' );

		// Get files count.
		$collection = $minify_module->get_resources_collection();
		// Remove those assets that we don't want to display.
		foreach ( $collection['styles'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' ) ) {
				unset( $collection['styles'][ $key ] );
			}

			// Keep only minified files.
			if ( $only_minified && ! preg_match( '/\.min\.(css|js)/', basename( $item['src'] ) ) ) {
				unset( $collection['styles'][ $key ] );
			}
		}
		foreach ( $collection['scripts'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' ) ) {
				unset( $collection['scripts'][ $key ] );
			}

			// Kepp only minified files.
			if ( $only_minified && ! preg_match( '/\.min\.(css|js)/', basename( $item['src'] ) ) ) {
				unset( $collection['scripts'][ $key ] );
			}
		}

		return ( count( $collection['scripts'] ) + count( $collection['styles'] ) );
	}

	/**
	 * Remove quick setup
	 *
	 * @since 1.5.0
	 */
	public static function remove_quick_setup() {
		$quick_setup             = get_option( 'wphb-quick-setup' );
		$quick_setup['finished'] = true;
		update_option( 'wphb-quick-setup', $quick_setup );
	}

	/**
	 * Flag to hide wpmudev branding image.
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	public static function hide_wpmudev_branding() {
		if ( self::is_member() ) {
			return apply_filters( 'wpmudev_branding_hide_branding', false );
		}

		return false;

	}

	/**
	 * Flag to hide wpmudev doc link.
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	public static function hide_wpmudev_doc_link() {
		if ( self::is_member() ) {
			return apply_filters( 'wpmudev_branding_hide_doc_link', false );
		}

		return false;

	}

}
