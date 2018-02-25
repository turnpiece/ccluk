<?php

/**
 * Try to cast a source URL to a path
 *
 * @param $src
 *
 * @return string
 */
function wphb_src_to_path( $src ) {
	$path = ltrim( parse_url( $src, PHP_URL_PATH ), '/' );
	$path = path_join( $_SERVER['DOCUMENT_ROOT'], $path );

	return apply_filters( 'wphb_src_to_path', $path, $src );
}

/**
 * Return the server type (Apache, NGINX...)
 *
 * @return string Server type
 */
function wphb_get_server_type() {
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
function wphb_get_servers() {
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
 * Get servers dropdown
 *
 * @param array $args
 * @param bool  $cloudflare  Add Cloudflare to the server list.
 */
function wphb_get_servers_dropdown( $args = array(), $cloudflare = true ) {
	$defaults = array(
		'class'    => '',
		'id'       => '',
		'name'     => 'wphb-server-type',
		'selected' => false,
	);

	$args = wp_parse_args( $args, $defaults );

	$servers = wphb_get_servers();

	if ( ! $cloudflare ) {
		unset( $servers['cloudflare'] );
	}

	if ( ! $args['id'] ) {
		$args['id'] = $args['name'];
	}

	if ( ! $args['selected'] ) {
		$args['selected'] = wphb_get_server_type();
	}

	?>
		<select name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?>">
			<?php foreach ( $servers as $server => $server_name ) : ?>
				<option value="<?php echo esc_attr( $server ); ?>" <?php selected( $server, $args['selected'] ); ?>><?php echo esc_html( $server_name ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php
}

function wphb_membership_modal() {
	/* @noinspection PhpIncludeInspection */
	include_once( WPHB_DIR_PATH . 'admin/views/modals/membership-modal.php' );
}

/**
 * Modal that is shown when shitching between basic and advanced minification views.
 *
 * @since 1.7.2
 *
 * @param string $view  Minification view. Accepts: 'basic', 'advanced'.
 */
function wphb_minification_view_modal( $view ) {
	if ( 'basic' === $view ) {
		/* @noinspection PhpIncludeInspection */
		include_once( WPHB_DIR_PATH . 'admin/views/modals/minification-advanced-modal.php' );
	} else {
		/* @noinspection PhpIncludeInspection */
		include_once( WPHB_DIR_PATH . 'admin/views/modals/minification-basic-modal.php' );
	}
}

/**
 * Modal for minification check files process
 *
 * @since 1.5.0
 */
function wphb_check_files_modal() {
	/* @noinspection PhpIncludeInspection */
	include_once( WPHB_DIR_PATH . 'admin/views/modals/check-files-modal.php' );
}

/**
 * Bulk update modal
 *
 * @since 1.5.0
 */
function wphb_bulk_update_modal() {
	/* @noinspection PhpIncludeInspection */
	include_once( WPHB_DIR_PATH . 'admin/views/modals/bulk-update-modal.php' );
}

/**
 * Check performance modal
 *
 * @since 1.5.0
 */
function wphb_check_performance_modal() {
	/* @noinspection PhpIncludeInspection */
	include_once( WPHB_DIR_PATH . 'admin/views/modals/check-performance-modal.php' );
}

/**
 * Quick setup modal (shows on first start)
 *
 * @since 1.5.0
 */
function wphb_quick_setup_modal() {
	/* @noinspection PhpIncludeInspection */
	include_once( WPHB_DIR_PATH . 'admin/views/modals/quick-setup-modal.php' );
}

/**
 * Dismiss report modal (performance report)
 *
 * @since 1.6.2
 */
function wphb_dismiss_report_modal() {
	/* @noinspection PhpIncludeInspection */
	include_once( WPHB_DIR_PATH . 'admin/views/modals/dismiss-report-modal.php' );
}

/**
 * Check if user is a paid one in WPMU DEV
 *
 * @return bool
 */
function wphb_is_member() {
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
function wphb_is_dashboard_logged_in() {
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

function wphb_plugin_page_link() {
	return 'https://premium.wpmudev.org/project/wp-hummingbird/?utm_source=hummingbird&utm_medium=plugin&utm_campaign=hummingbird_pro_modal_upgrade';
}

function wphb_update_membership_link() {
	return 'https://premium.wpmudev.org/membership/#profile-menu-tabs?utm_source=hummingbird&utm_medium=plugin&utm_campaign=hummingbird_pro_modal_upgrade';
}
function wphb_live_chat_link() {
	return 'https://premium.wpmudev.org/live-support/';
}

function wphb_support_link() {
	if ( wphb_is_member() ) {
		return 'https://premium.wpmudev.org/forums/forum/support#question';
	} else {
		return 'https://wordpress.org/support/plugin/hummingbird-performance';
	}
}

/**
 * Enqueues admin scripts
 *
 * @param int $ver Current version number of scripts.
 */
function wphb_enqueue_admin_scripts( $ver ) {
	wp_enqueue_script( 'wphb-admin', WPHB_DIR_URL . 'admin/assets/js/admin.min.js', array( 'jquery', 'underscore' ), $ver );

	$i10n = array(
		'recheckURL' => add_query_arg( array(
			'view' => 'browser',
			'run'  => 'true',
		), wphb_get_admin_menu_url( 'caching' ) ),
		'htaccessErrorURL' => add_query_arg( array(
			'view'           => 'browser',
			'htaccess-error' => 'true',
		), wphb_get_admin_menu_url( 'caching' ) ),
		'cacheEnabled' => WP_Hummingbird_Module_Server::is_htaccess_written( 'caching' ),
	);
	wp_localize_script( 'wphb-admin', 'wphbCachingStrings', $i10n );

	if ( wphb_can_execute_php() ) {
		$i10n = array(
			'checkFilesNonce'       => wp_create_nonce( 'wphb-minification-check-files' ),
			'chartNonce'            => wp_create_nonce( 'wphb-chart' ),
			'finishedCheckURLsLink' => wphb_get_admin_menu_url( 'minification' ),
			'discardAlert'          => __( 'Are you sure? All your changes will be lost', 'wphb' ),
		);
		wp_localize_script( 'wphb-admin', 'wphbMinificationStrings', $i10n );
	}

	$i10n = array(
		'finishedTestURLsLink' => wphb_get_admin_menu_url( 'performance' ),
		'removeButtonText'     => __( 'Remove', 'wphb' ),
		'youLabelText'         => __( 'You', 'wphb' ),
	);
	wp_localize_script( 'wphb-admin', 'wphbPerformanceStrings', $i10n );

	$i10n = array(
		'finishedTestURLsLink' => wphb_get_admin_menu_url( '' ),
	);
	wp_localize_script( 'wphb-admin', 'wphbDashboardStrings', $i10n );

	$toggle_uptime_nonce = wp_create_nonce( 'wphb-toggle-uptime' );
	$i10n = array(
		'enableUptimeURL' => add_query_arg(
			array(
				'_wpnonce' => $toggle_uptime_nonce,
				'action'   => 'enable',
			),
			wphb_get_admin_menu_url( 'uptime' )
		),
		'disableUptimeURL' => add_query_arg(
			array(
				'_wpnonce' => $toggle_uptime_nonce,
				'action'   => 'disable',
			),
			wphb_get_admin_menu_url( 'uptime' )
		),

	);
	wp_localize_script( 'wphb-admin', 'wphbUptimeStrings', $i10n );

	// @TODO We are moving all strings/settings to a unique object instead of splitting it. Starting with Minification screen
	/** @var WP_Hummingbird_Module_Cloudflare $cf */
	$cf = wphb_get_module( 'cloudflare' );
	$i10n = array(
		'cloudflare' => array(
			'is' => array(
				'connected' => $cf->is_connected() && $cf->is_zone_selected(),
			),
		),
		'nonces' => array(
			'HBFetchNonce' => wp_create_nonce( 'wphb-fetch' ),
		),
	);

	if ( wphb_can_execute_php() ) {
		$i10n = array_merge( $i10n, array(
			'minification' => array(
				'is' => array(
					'scanning' => wphb_minification_is_scanning_files(),
					'scanned'  => wphb_minification_is_scan_finished(),
				),
				'get' => array(
					'currentScanStep' => wphb_minification_get_current_scan_step(),
					'totalSteps'      => wphb_minification_get_scan_steps_number(),
					'showCDNModal'    => ! is_multisite(),
				),
			),
			'strings' => array(
				'discardAlert' => __( 'Are you sure? All your changes will be lost', 'wphb' ),
			),
			'links' => array(
				'minification' => wphb_get_admin_menu_url( 'minification' ),
			),
		) );
	}

	wp_localize_script( 'wphb-admin', 'wphb', $i10n );
}

/**
 * @return WP_Hummingbird_API
 */
function wphb_get_api() {
	/* @var WP_Hummingbird $hummingbird */
	$hummingbird = WP_Hummingbird::get_instance();
	return $hummingbird->core->api;
}

function wphb_get_caching_frequencies() {
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

function wphb_get_caching_cloudflare_frequencies() {
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
 * Convert CloudFlare frequency to normal. Used when updating the custom code in browser caching.
 *
 * @param bool $cloudflare_frequency  Cloudflare frequency to convert.
 * @return string Caching frequency.
 */
function wphb_convert_cloudflare_frequency( $cloudflare_frequency ) {
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
 * Prepare dropdown select with caching expiry settings.
 *
 * @param array $args        Arguments list.
 * @param bool  $cloudflare  Get Cloudflare frequencies.
 */
function wphb_get_caching_frequencies_dropdown( $args = array(), $cloudflare = false ) {
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
		$frequencies = wphb_get_caching_cloudflare_frequencies();
	} else {
		$frequencies = wphb_get_caching_frequencies();
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
 * Credits to: http://stackoverflow.com/a/11389893/1502521
 *
 * @param $seconds
 *
 * @return string|void
 */
function wphb_human_read_time_diff( $seconds ) {
	if ( ! $seconds ) {
		return false;
	}

	$year_in_seconds   = 60 * 60 * 24 * 365;
	$month_in_seconds  = 60 * 60 * 24 * 30;
	$day_in_seconds    = 60 * 60 * 24;
	$hour_in_seconds   = 60 * 60;
	$minute_in_seconds = 60;

	$minutes = 0;
	$hours = 0;
	$days = 0;
	$months = 0;
	$years = 0;

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
		$diff_time = sprintf( _n( '%d year', '%d years', $years, 'wphb' ), $years );
	} elseif ( $diff->m ) {
		$diff_time = sprintf( _n( '%d month', '%d months', $diff->m, 'wphb' ), $diff->m );
	} elseif ( $diff->d ) {
		$diff_time = sprintf( _n( '%d day', '%d days', $diff->d, 'wphb' ), $diff->d );
	} elseif ( $diff->h ) {
		$diff_time = sprintf( _n( '%d hour', '%d hours', $diff->h, 'wphb' ), $diff->h );
	} elseif ( $diff->i ) {
		$diff_time = sprintf( _n( '%d minute', '%d minutes', $diff->i, 'wphb' ), $diff->i );
	} else {
		$diff_time = sprintf( _n( '%d second', '%d seconds', $diff->s, 'wphb' ), $diff->s );
	}

	return $diff_time;
}

function wphb_get_recommended_caching_values() {
	return array(
		'css' => array(
			'label' => __( '8 days', 'wphb' ),
			'value' => 8 * 24 * 3600,
		),
		'javascript' => array(
			'label' => __( '8 days', 'wphb' ),
			'value' => 8 * 24 * 3600,
		),
		'media' => array(
			'label' => __( '8 days', 'wphb' ),
			'value' => 8 * 24 * 3600,
		),
		'images' => array(
			'label' => __( '8 days', 'wphb' ),
			'value' => 8 * 24 * 3600,
		),
	);
}

function wphb_get_admin_menu_url( $page = '' ) {
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
 * Return the needed capability for admin pages
 *
 * @return string
 */
function wphb_get_admin_capability() {
	$cap = 'manage_options';
	if ( is_multisite() && is_network_admin() ) {
		$cap = 'manage_network';
	}

	return apply_filters( 'wphb_admin_capability', $cap );
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
function wphb_get_code_snippet( $module, $server_type = '', $expiry_times = array() ) {

	/** @var WP_Hummingbird_Module_Server $module */
	$module = wphb_get_module( $module );
	if ( ! $module ) {
		return '';
	}

	if ( ! $server_type ) {
		$server_type = wphb_get_server_type();
	}

	return apply_filters( 'wphb_code_snippet', $module->get_server_code_snippet( $server_type, $expiry_times ), $server_type, $module );
}

/**
 * Get days of week
 *
 * @return mixed|void
 * @since 1.4.5
 */
function wphb_get_days_of_week() {
	$timestamp = strtotime( 'next Monday' );
	if ( 7 === get_option( 'start_of_week' ) ) {
		$timestamp = strtotime( 'next Sunday' );
	}
	$days      = array();
	for ( $i = 0; $i < 7; $i ++ ) {
		$days[]    = strftime( '%A', $timestamp );
		$timestamp = strtotime( '+1 day', $timestamp );
	}

	return apply_filters( 'wphb_scan_get_days_of_week', $days );
}

/**
 * Return times frame for selectbox
 *
 * @since 1.4.5
 */
function wphb_get_times() {
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
 * Display start a live chat link for pro user or open support ticket for non-pro user.
 */
function _wphb_still_having_trouble_link() {
	if ( wphb_is_member() ) {
		?>
		<p><?php esc_html_e( 'Still having trouble? ', 'wphb' ); ?><a target="_blank" href="<?php echo wphb_live_chat_link(); ?>"><?php esc_html_e( 'Start a live chat.', 'wphb' ); ?></a></p>
		<?php
	} else {
		?>
		<p><?php esc_html_e( 'Still having trouble? ', 'wphb' ); ?><a target="_blank" href="<?php echo wphb_support_link(); ?>"><?php esc_html_e( 'Open a support ticket.', 'wphb' ); ?></a></p>
		<?php
	}
}

/**
 * Get avatar URL.
 *
 * @param $get_avatar string User email.
 * @return mixed
 * @since 1.4.5
 */
function wphb_get_avatar_url( $get_avatar ) {
	preg_match( "/src='(.*?)'/i", $get_avatar, $matches );

	return $matches[1];
}

/**
 * Get display name
 *
 * @param $id int User ID.
 * @return null|string
 * @since 1.4.5
 */
function wphb_get_display_name( $id ) {
	$user = get_user_by( 'id', $id );
	if ( ! is_object( $user ) ) {
		return null;
	}
	if ( ! empty( $user->user_nicename ) ) {
		return $user->user_nicename;
	} else {
		return $user->user_firstname . ' ' . $user->user_lastname;
	}
}

/**
 * Get documentation URL.
 *
 * @param $page string Page slug.
 * @param $view string View slug.
 * @return string
 * @since 1.7.0
 */
function wphb_get_documentation_url( $page, $view = '' ) {
	switch ( $page ) {
		case 'wphb-performance':
			if ( 'reports' === $view ) :
				$anchor = '#chapter-7';
			else :
				$anchor = '#chapter-1';
			endif;
			break;
		case 'wphb-caching':
			$anchor = '#chapter-2';
			break;
		case 'wphb-gzip':
			$anchor = '#chapter-3';
			break;
		case 'wphb-minification':
			$anchor = '#chapter-4';
			break;
		case 'wphb-uptime':
			$anchor = '#chapter-6';
			break;
		default:
			$anchor = '';
	}

	return 'https://premium.wpmudev.org/docs/wpmu-dev-plugins/hummingbird/' . $anchor;
}

/**
 * Get the proper HTTP2 status via a curl call.
 *
 * @since 1.7.1
 */
function wphb_get_http2_status() {
	if ( isset( $_SERVER['SERVER_PROTOCOL'] ) && 'HTTP/2.0' === $_SERVER['SERVER_PROTOCOL'] ) {
		return true;
	}

	$ch = curl_init();
	curl_setopt_array( $ch, array(
		CURLOPT_URL            => get_home_url(),
		CURLOPT_HEADER         => true,
		CURLOPT_NOBODY         => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTP_VERSION   => 3, // cURL will attempt to make an HTTP/2.0 request (can downgrade to HTTP/1.1)
	) );
	$response = curl_exec( $ch );

	if ( false !== $response && ( 0 === strpos( $response, 'HTTP/2.0' ) || 0 === strpos( $response,  'HTTP/2' ) ) ) {
		$status = true;
	} elseif ( false !== $response ) {
		$status = false;
	} else {
		$status = new WP_Error( curl_error( $ch ) );
	}

	curl_close( $ch );

	return $status;
}

/**
 * Get Current username info
 */
function wphb_get_current_user_info() {
	$current_user = wp_get_current_user();

	if ( ! ( $current_user instanceof WP_User ) ) {
		return false;
	}

	if ( ! empty( $current_user->user_firstname ) ) { // First we try to grab user First Name
		$display_name = $current_user->user_firstname;
	} else { // Grab user nicename
		$display_name = $current_user->user_nicename;
	}

	return $display_name;
}