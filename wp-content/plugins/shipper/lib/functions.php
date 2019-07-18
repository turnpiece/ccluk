<?php
/**
 * Various functions, compatibility layer, etc
 *
 * @package shipper
 */

if ( ! function_exists( 'get_called_class' ) ) {

	/**
	 * PHP 5.2 compatibility layer implementation.
	 *
	 * Used for singleton inheritance.
	 *
	 * @see https://stackoverflow.com/a/7904487
	 *
	 * @return string class
	 */
	function get_called_class() {
		$bt = debug_backtrace();
		$lines = file( $bt[1]['file'] );
		preg_match(
			'/([a-zA-Z0-9\_]+)::' . $bt[1]['function'] . '/',
			$lines[ $bt[1]['line'] -1 ],
			$matches
		);
		return $matches[1];
	}
}


/**
 * Wrapper for (possibly non-existent) `hex2bin`
 *
 * PHP 5.3 compatibility layer implementation.
 *
 * Used for file storage hashing.
 *
 * @see http://php.net/manual/en/function.hex2bin.php
 *
 * @param string $str String to encode.
 *
 * @return string
 */
function shipper_hex2bin( $str ) {
	// @codingStandardsIgnoreLine Wrapped in existence check
	if ( function_exists( 'hex2bin' ) ) { return hex2bin( $str ); }

	$sbin = '';
	$len = strlen( $str );
	for ( $i = 0; $i < $len; $i += 2 ) {
		$sbin .= pack( 'H*', substr( $str, $i, 2 ) );
	}

	return $sbin;
}

/**
 * Checks if a particular error code is represented in errors
 *
 * @param string $errstr Error code fragment.
 * @param array  $errors Errors array, or error object instance.
 *
 * @return bool
 */
function shipper_has_error( $errstr, $errors ) {
	if ( ! is_array( $errors ) ) { $errors = array( $errors ); }

	$delimiter = Shipper_Model::SCOPE_DELIMITER;

	foreach ( $errors as $error ) {
		if ( ! is_wp_error( $error ) ) { continue; }
		$code = $error->get_error_code();

		$pos = stripos( $code, "{$errstr}{$delimiter}" );
		if ( 0 === $pos ) {
			// Position zero - we matched error up to delimiter.
			return true;
		}

		$string = "{$delimiter}{$errstr}";
		$pos = stripos( $code, $string );
		if ( false !== $pos && strlen( $code ) === $pos + strlen( $string ) ) {
			// We found the substring, and it matches from delimiter to EOS.
			return true;
		}
	}

	return false;
}

/**
 * Gets *a* (super)admin user
 *
 * @return WP_User|bool A super-admin user, or (bool)false on failure
 */
function shipper_get_admin_user() {
	$user = false;
	if( is_user_logged_in() ) {
		$user 	= wp_get_current_user();
	} else {
		if ( is_multisite() ) {
			$super_ids = array();
			$supers = get_super_admins();
			foreach ( $supers as $super ) {
				$user_data = get_user_by( 'login', $super );
				array_push( $super_ids, $user_data->ID );
			}

			// Get the super admin that logged in most recently, in case Defender's Login Duration is on.
			$admins = get_users( array( 'blog_id' => 0, 'include' => $super_ids, 'meta_key' => 'last_login_time', 'orderby' => 'meta_value', 'order' => 'DESC', 'number' => 1 ) );

			// If no super admins were returned, no last_login_time is being recorded, so we can take whoever.
			if ( empty( $admins ) ) {
				$admins = get_users( array( 'blog_id' => 0, 'include' => $super_ids, 'number' => 1 ) );
			}
		} else {
			// Get the admin that logged in most recently, in case Defender's Login Duration is on.
			$admins = get_users( array( 'role' => 'administrator', 'meta_key' => 'last_login_time', 'orderby' => 'meta_value', 'order' => 'DESC', 'number' => 1 ) );

			// If no admins were returned, no last_login_time is being recorded, so we can take whoever.
			if ( empty( $admins ) ) {
				$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
			}
		}

		$user = $admins[0];
	}

	if ( empty( $user ) || ! is_object( $user ) || empty( $user->user_login ) ) {
		return false;
	}
	return $user;
}

/**
 * Gets user first name, or display name as fallback.
 *
 * @param int $user_id Optional user ID (defaults to current user).
 *
 * @return string
 */
function shipper_get_user_name( $user_id = false ) {
	$user_id = ! empty( $user_id ) && is_numeric( $user_id )
		? (int) $user_id
		: get_current_user_id();
	$user = new WP_User( $user_id );
	$name = __( 'Anonymous', 'shipper' );

	if ( $user->exists() && $user->has_prop( 'user_firstname' ) ) {
		$fn = $user->get( 'user_firstname' );
		$name = ! empty( $fn ) ? $fn : $user->get( 'display_name' );
	}

	return (string) $name;
}

/**
 * Checks if the user can perform shipping action
 *
 * @TODO: implement for users other than current.
 *
 * @param int $user_id Optional user ID - defaults to current user.
 *
 * @return bool
 */
function shipper_user_can_ship( $user_id = false ) {
	if ( ! empty( $user_id ) ) {
		// Implement for non-current user.
		return false;
	}
	return current_user_can(
		Shipper_Controller_Admin::get()->get_capability()
	);
}

/**
 * Waits for cancel lock, for a number of seconds.
 *
 * @param string $identifier Optional identifier for this await, used in filtering.
 * @param float  $secs Optional number of seconds to await, defaults to 30.
 * @param float  $step Optional obligatory sleep interval before the lock is checked.
 *
 * @return bool True if lock is encountered, false otherwise
 */
function shipper_await_cancel( $identifier = '', $secs = 30, $step = 1 ) {
	if ( empty( $identifier ) ) {
		$identifier = 'generic';
	}
	$micro = 1000000;

	/**
	 * Cancel await max time
	 *
	 * Default 30 secs, in microseconds.
	 *
	 * @param int $time Time to spend awaiting cancel lock, in microseconds.
	 *
	 * @return int
	 */
	$max = apply_filters(
		"shipper_await_cancel_{$identifier}_max",
		(int) ( (float) $secs * $micro )
	);

	/**
	 * Cancel await step
	 *
	 * Default 1 sec, in microseconds.
	 *
	 * @param int $time Time to spend hibernating between cancel lock checks, in microseconds.
	 *
	 * @return int
	 */
	$tick = apply_filters(
		"shipper_await_cancel_{$identifier}_step",
		(int) ( (float) $step * $micro )
	);

	$locks = new Shipper_Helper_Locks;

	// Not within waiting range, all good.
	if ( $max <= $tick ) {
		return $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL );
	}

	foreach ( range( 1, $max, $tick ) as $tock ) {
		usleep( $tick );
		if ( $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
			return true; // Done if cancel-locked.
		}
	}

	return false; // Haven't encountered a lock.
}

/**
 * Gets domain-based unique identifier for a site
 *
 * Unique identifier will be a special-chars safe string.
 *
 * @param string $prefix Optional prefix.
 *
 * @return string
 */
function shipper_get_site_uniqid( $prefix = '' ) {
	$id = untrailingslashit(
		shipper_get_protocol_agnostic( network_site_url(), true )
	);

	$clean_rx = '[^-_a-zA-Z0-9]';

	if ( ! empty( $prefix ) ) {
		$prefix = preg_replace( "/{$clean_rx}/", '-', $prefix );
	}
	$id = preg_replace( "/{$clean_rx}/", '-', $id );

	/**
	 * Gets domain-based unique site identifier
	 *
	 * @param string $uniqid Safe-ranged identifier.
	 * @param string $prefix Optional prefix.
	 *
	 * @return string
	 */
	$id = apply_filters(
		'shipper_site_uniqid',
		preg_replace( '/-+/', '-', $id ),
		$prefix
	);

	return ! empty( $prefix )
		? sprintf( '%s-%s', $prefix, $id )
		: $id
	;
}

/**
 * Checks if a directory is web-accessible.
 *
 * @param string $dir Directory path.
 *
 * @return bool
 */
function shipper_is_dir_visible( $dir = '' ) {
	// If nothing passed, assume cwd.
	if ( empty( $dir ) ) { return true; }

	$dir = wp_normalize_path( realpath( $dir ) );
	// No such directory, we should be good.
	if ( empty( $dir ) ) { return false; }

	$rx = preg_quote( ABSPATH, '/' );

	return ! ! preg_match( "/^{$rx}/", $dir );
}

/**
 * Attempts to convert full path to an URL
 *
 * @param string $dir Directory path.
 *
 * @return string Empty string on failure, URL on success
 */
function shipper_path_to_url( $dir ) {
	if ( empty( $dir ) ) { return ''; }

	$dir = wp_normalize_path( realpath( $dir ) );
	if ( empty( $dir ) ) { return ''; }

	$attempts = array(
		preg_quote( WP_CONTENT_DIR, '/' ) => WP_CONTENT_URL,
		preg_quote( WP_PLUGIN_DIR, '/' ) => WP_PLUGIN_URL,
		preg_quote( ABSPATH, '/' ) => home_url(),
	);

	foreach ( $attempts as $rx => $url ) {
		if ( preg_match( "/^{$rx}/", $dir ) ) {
			return trailingslashit( preg_replace( "/^{$rx}/", $url, $dir ) );
		}
	}

	return '';
}

/**
 * Deletes a file
 *
 * Boolean-checking wrapper around `wp_delete_file`
 *
 * @param string $path The path to the file to delete.
 *
 * @return bool
 */
function shipper_delete_file( $path ) {
	wp_delete_file( $path );
	return ! file_exists( $path );
}

/**
 * Returns protocol-agnostic URL representation
 *
 * @param string $url URL to process.
 * @param bool   $is_clean Use root slashes if true (defaults to false).
 *
 * @return string
 */
function shipper_get_protocol_agnostic( $url, $is_clean = false ) {
	$root = empty( $is_clean )
		? '//'
		: ''
	;
	return preg_replace( '/^https?:\/\//i', $root, $url );
}

/**
 * Returns protocol-agnostic network home url
 *
 * @return string
 */
function shipper_network_home_url() {
	return shipper_get_protocol_agnostic( network_home_url() );
}

/**
 * Cache flushing wrapper
 *
 * This is here because object cache flushes can be prevented.
 */
function shipper_flush_cache() {
	global $wp_object_cache;
	if ( is_object( $wp_object_cache ) && is_callable( array( $wp_object_cache, 'flush' ) ) ) {
		Shipper_Helper_Log::debug( 'Force-flush object cache' );
		$wp_object_cache->flush( 0 );
		return true;
	} else {
		if ( is_callable( 'wp_cache_flush' ) ) {
			wp_cache_flush();
			return true;
		}
		Shipper_Helper_Log::write( 'Unable to flush object cache' );
	}
	return false;
}

/**
 * Gets Shipper-specific User-Agent string
 *
 * @return string
 */
function shipper_get_user_agent() {
	return sprintf(
		'Mozilla/5.0 (compatible; WPMU DEV Shipper/%1$s; +https://premium.wpmudev.org)',
		SHIPPER_VERSION
	);
}

/**
 * Ensure we have decent compatibility with broken hosts
 *
 * @param string $path Path to glob.
 *
 * @return array
 */
function shipper_glob_all( $path ) {
	return defined( 'GLOB_BRACE' )
		? glob( trailingslashit( $path ) . '{,.}[!.,!..]*', GLOB_BRACE )
		: glob( trailingslashit( $path ) . '[!.,!..]*' );
}

/**
 * Checks wheter an array has all keys
 *
 * @param array $keys Keys to check for presence.
 * @param array $array Array to check.
 *
 * @return bool
 */
function shipper_array_keys_exist( $keys, $array ) {
	return count( $keys ) === count( array_intersect( $keys, array_keys( $array ) ) );
}

/**
 * Gets a list of users allowed to access WPMU DEV Dashboard
 *
 * @since v1.0.3
 *
 * @return array A list of user IDs
 */
function shipper_get_dashboard_users() {
	$dash_users = array();
	if (
		class_exists( 'WPMUDEV_Dashboard' ) &&
		! empty( WPMUDEV_Dashboard::$site ) &&
		is_callable( array( WPMUDEV_Dashboard::$site, 'get_allowed_users' ) )
	) {
		$dash_users = WPMUDEV_Dashboard::$site->get_allowed_users( true );
	}

	$dash_users = ! empty( $dash_users ) && is_array( $dash_users )
		? $dash_users
		: array();

	/**
	 * List of users allowed to access WPMU DEV Dashboard
	 *
	 * Used in tests.
	 *
	 * @since v1.0.3
	 *
	 * @param array $dash_users A list of users allowed to access WPMU DEV Dashboard.
	 *
	 * @return array A list of user IDs
	 */
	return (array) apply_filters(
		'shipper_dashboard_users',
		$dash_users
	);
}

/**
 * Returns a list of users allowed to access Shipper pages.
 *
 * If none set, defaults to users allowed to access WPMU DEV Dashboard.
 *
 * @since v1.0.3
 *
 * @return array A list of user IDs.
 */
function shipper_get_allowed_users() {
	$opts = new Shipper_Model_Stored_Options;
	return $opts->get(
		Shipper_Model_Stored_Options::KEY_USER_ACCESS,
		shipper_get_dashboard_users()
	);
}
