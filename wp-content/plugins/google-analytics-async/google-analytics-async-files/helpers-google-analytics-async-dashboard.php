<?php
/**
 * Current settings are different depending on what are we seeing
 * - If is_network_admin() these are get_site_option( 'ga2_settings' )
 * - Otherwise, these are get_option( 'ga2_settings' )
 */
function ga_plus_get_current_settings() {
	global $google_analytics_async;
	return $google_analytics_async->current_settings;
}

/**
 * Get the settings saved for the current blog
 */
function ga_plus_get_current_site_settings() {
	global $google_analytics_async;
	return $google_analytics_async->settings;
}

/**
 * Get the settings for the whole network
 */
function ga_plus_get_network_settings() {
	global $google_analytics_async;
	return $google_analytics_async->network_settings;
}

function ga_plus_get_settings( $source ) {
	switch ( $source ) {
		case 'network': {
			$settings = ga_plus_get_network_settings();
			break;
		}
		case 'site': {
			$settings = ga_plus_get_current_site_settings();
			break;
		}
		default: {
			$settings = ga_plus_get_current_settings();
			break;
		}
	}
	return $settings;
}

/**
 * @param string $option_name
 * @param mixed $option_value
 * @param string $source site|network
 *
 * @internal param array $params
 */
function ga_plus_update_setting( $option_name, $option_value, $source = 'site' ) {
	global $google_analytics_async;
	$is_network = $source === 'network' ? 'network' : '';
	if ( '' === $option_name ) {
		// Save all settings
		$google_analytics_async->save_options( $option_value , $is_network );
	}
	else {
		// Save just one setting
		$params = array( $option_name => $option_value );
		$google_analytics_async->save_options( $params , $is_network );
	}
}

/**
 * Authenticate to Google by using an access code
 *
 * @throws Exception | Google_IO_Exception | Google_Service_Exception
 *
 * @return array
 */
function ga_plus_code_authenticate( $code ) {
	global $google_analytics_async_dashboard;

	$google_analytics_async_dashboard->google_client->authenticate( $code );
	$token_object = ga_plus_get_google_token();
	ga_plus_set_google_token( $token_object );

	$google_user_info = new GAPGoogle_Service_Oauth2($google_analytics_async_dashboard->google_client);
	$google_user_id = $google_user_info->userinfo->get();
	$google_user_id = $google_user_id->id;

	return compact( 'token_object', 'google_user_id' );
}

/**
 * Return the authentication token set in Google_client
 *
 * @return object
 */
function ga_plus_get_google_token() {
	global $google_analytics_async_dashboard;
	$token = $google_analytics_async_dashboard->google_client->getAccessToken();
	if ( is_string( $token ) ) {
		return json_decode($token);
	}
	elseif ( is_array( $token ) ) {
		return (object) $token;
	}


}

/**
 * Set the authentication token into Google_Client
 *
 * @param object|string $token
 */
function ga_plus_set_google_token( $token ) {
	global $google_analytics_async_dashboard;
	if ( is_object( $token ) ) {
		$token = json_encode( $token );
	}
	$google_analytics_async_dashboard->google_client->setAccessToken($token);
}

/**
 * Get the current settings for the login with code mode
 *
 * @param string $source current|network|site
 * @return array
 */
function ga_plus_get_google_login_settings( $source = 'current' ) {
	$settings = ga_plus_get_settings( $source );
	if ( isset( $settings['google_login'] ) ) {
		return $settings['google_login'];
	}

	return array();
}

/**
 * Get the current settings for the login with API Key
 *
 * @param string $source current|network|site
 * @return array
 */
function ga_plus_get_google_api_settings( $source = 'current' ) {
	$settings = ga_plus_get_settings( $source );
	if ( isset( $settings['google_api'] ) ) {
		return $settings['google_api'];
	}

	return array();
}

/**
 * Check if Analytics is logged in by using a code
 *
 * @return bool
 */
function ga_plus_is_google_login_logged_in( $source = 'current' ) {
	$settings = ga_plus_get_google_login_settings( $source );
	return isset( $settings['logged_in'] );
}

/**
 * Get the authentication token for a given Google User ID
 *
 * @param $google_user_id
 *
 * @return array|bool
 */
function ga_plus_get_saved_token( $google_user_id ) {
	global $wpdb;
	$db_token = $wpdb->get_row($wpdb->prepare("SELECT id, token FROM {$wpdb->base_prefix}gaplus_login WHERE user_id = %s", $google_user_id), ARRAY_A);
	if ( $db_token ) {
		return $db_token;
	}
	return false;
}

/**
 * Get the authentication token data for a given Token ID
 *
 * @param $token_id
 *
 * @return array|bool|mixed|object
 */
function ga_plus_get_saved_token_by_token_id( $token_id ) {
	global $wpdb;
	$db_token = $wpdb->get_var($wpdb->prepare("SELECT token FROM {$wpdb->base_prefix}gaplus_login WHERE id = %d", $token_id));
	if ( $db_token ) {
		return json_decode( $db_token );
	}
	return false;
}

/**
 * Update the authentication token for a given Google User ID
 *
 * @param $google_user_id
 * @param $token
 *
 * @return int|mixed
 */
function ga_plus_update_saved_token( $google_user_id, $token ) {
	global $wpdb;
	if ( is_object( $token ) ) {
		$token = json_encode( $token );
	}

	$db_token = ga_plus_get_saved_token( $google_user_id );
	if ( $db_token['id'] ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->base_prefix}gaplus_login SET token = %s WHERE id = %d", $token, $db_token['id'] ) );
	} else {
		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->base_prefix}gaplus_login SET user_id = %s, token = %s", $google_user_id, $token ) );
		$db_token['id'] = $wpdb->insert_id;
	}
	return $db_token['id'];
}

/**
 * Update the authentication token for a given Token ID
 *
 * @param $google_user_id
 * @param $token
 *
 * @return int|mixed
 */
function ga_plus_update_saved_token_by_token_id( $token_id, $token ) {
	global $wpdb;
	if ( is_object( $token ) ) {
		$token = json_encode( $token );
	}

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->base_prefix}gaplus_login SET token = %s WHERE id = %d", $token, $token_id ) );
}


/**
 * Update the Google Login settings for a given Token ID (the main column in gaplus_login table)
 *
 * @param $token_id
 * @param string $type netowrk|empty string
 */
function ga_plus_refresh_google_login_settings( $token_id, $type = 'network' ) {
	$token = ga_plus_get_saved_token_by_token_id( $token_id );
	if ( ! $token ) {
		return;
	}
	$token_stringified = json_encode( $token );
	ga_plus_update_setting( 'google_login',
		array(
			'token_id'      => $token_id,
			'token'         => $token_stringified,
			'orginal_token' => $token_stringified,
			'expire'        => time() + $token->expires_in,
			'token_secret'  => 1,
			'logged_in'     => 2
		)
		, $type
	);
}

/**
 * @throws Google_IO_Exception|Exception
 */
function ga_plus_refresh_google_login_token( $google_login_settings, $source = 'site' ) {
	global $google_analytics_async_dashboard;

	$orginal_token = $google_login_settings['orginal_token'];
	if ( is_string( $orginal_token ) ) {
		$orginal_token = json_decode( $orginal_token );
	}

	if(isset($orginal_token->refresh_token)) {
		$google_analytics_async_dashboard->google_client->refreshToken($orginal_token->refresh_token);
		$token_object = ga_plus_get_google_token();

		$google_login_settings['token'] = json_encode( $token_object );
		$google_login_settings['expire'] = time() + $token_object->expires_in;

		//lets store the refresh token
		if ( ! empty( $google_login_settings['token_id'] ) ) {
			//lets keep refresh token
			$db_token_object = $token_object;
			$db_token_object->refresh_token = $orginal_token->refresh_token;
			$db_token_object->token_type = $orginal_token->token_type;
			ga_plus_update_saved_token_by_token_id( $google_login_settings['token_id'], $db_token_object );
		}

		ga_plus_update_setting( 'google_login', $google_login_settings, $source );
		ga_plus_set_google_token( $token_object );
	}
}

/**
 * @param string $source network|empty string
 */
function ga_plus_reset_google_login_settings( $source = 'network' ) {
	global $google_analytics_async;
	$google_analytics_async->save_options(array('google_login' => array()), $source);
	$google_analytics_async->save_options(array('google_api' => array()), $source);
}

/**
 * Return the tracking settings
 *
 * @param string $source current|network|site
 *
 * @return array
 */
function ga_plus_get_track_settings( $source = 'current' ) {
	$settings = ga_plus_get_settings( $source );
	if ( ! isset( $settings['track_settings'] ) ) {
		return array();
	}
	return $settings['track_settings'];
}