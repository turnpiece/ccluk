<?php

add_filter( 'wphb_block_resource', 'wphb_filter_resource_block', 10, 5 );
function wphb_filter_resource_block( $value, $handle, $type ) {
	$options = wphb_get_settings();
	$blocked = $options['block'][ $type ];
	if ( in_array( $handle, $blocked ) ) {
		return true;
	}

	return $value;
}

add_filter( 'wphb_minify_resource', 'wphb_filter_resource_minify', 10, 3 );
function wphb_filter_resource_minify( $value, $handle, $type ) {
	$options = wphb_get_settings();
	$dont_minify = $options['dont_minify'][ $type ];
	if ( in_array( $handle, $dont_minify ) ) {
		return false;
	}

	return $value;
}

add_filter( 'wphb_combine_resource', 'wphb_filter_resource_combine', 10, 3 );
function wphb_filter_resource_combine( $value, $handle, $type ) {
	$options = wphb_get_settings();
	$combine = $options['combine'][ $type ];
	if ( ! in_array( $handle, $combine ) ) {
		return $value;
	}

	return true;
}

add_filter( 'wphb_defer_resource', 'wphb_filter_resource_defer', 10, 3 );
function wphb_filter_resource_defer( $value, $handle, $type ) {
	$options = wphb_get_settings();
	$defer = $options['defer'][ $type ];
	if ( ! in_array( $handle, $defer ) ) {
		return $value;
	}

	return true;
}

add_filter( 'wphb_inline_resource', 'wphb_filter_resource_inline', 10, 3 );
function wphb_filter_resource_inline( $value, $handle, $type ) {
	$options = wphb_get_settings();
	$defer = $options['inline'][ $type ];
	if ( ! in_array( $handle, $defer ) ) {
		return $value;
	}

	return true;
}

add_filter( 'wphb_send_resource_to_footer', 'wphb_filter_resource_to_footer', 10, 3 );
function wphb_filter_resource_to_footer( $value, $handle, $type ) {
	$options = wphb_get_settings();
	$to_footer = $options['position'][ $type ];

	if ( array_key_exists( $handle, $to_footer ) && 'footer' === $to_footer[ $handle ] ) {
		return true;
	}

	return $value;
}

add_filter( 'wp_hummingbird_is_active_module_uptime', 'wphb_uptime_module_status' );
function wphb_uptime_module_status( $current ) {
	$options = wphb_get_settings();
	if ( ! $options['uptime'] ) {
		return false;
	}

	return $current;
}

add_filter( 'wp_hummingbird_is_active_module_minify', 'wphb_minify_module_status' );
function wphb_minify_module_status( $current ) {
	$options = wphb_get_settings();

	if ( false === $options['minify'] ) {
		return false;
	}

	if ( is_multisite() ) {
		$current = $options['minify-blog'];
	} else {
		$current = $options['minify'];
	}

	return $current;
}

add_filter( 'wp_hummingbird_is_active_module_gravatar', 'wphb_gravatar_module_status' );
function wphb_gravatar_module_status( $current ) {
	$options = wphb_get_settings();
	if ( ! $options['gravatar_cache'] ) {
		return false;
	}

	return $current;
}

add_filter( 'wp_hummingbird_is_active_module_page-caching', 'wphb_page_caching_module_status' );
function wphb_page_caching_module_status( $current ) {
	$options = wphb_get_settings();
	if ( ! $options['page_cache'] ) {
		return false;
	}

	return $current;
}

add_filter( 'wphb_get_server_type', 'wphb_set_user_server_type' );
function wphb_set_user_server_type( $type ) {
	$user_type = get_user_meta( get_current_user_id(), 'wphb-server-type', true );
	if ( $user_type ) {
		$type = $user_type;
	}
	return $type;
}

// Do not minify files that already are named with .min
add_filter( 'wphb_minify_resource', 'wphb_minify_min_files', 15, 4 );
function wphb_minify_min_files( $minify, $handle, $type, $url ) {
	if ( preg_match( '/\.min\.(css|js)/', basename( $url ) ) ) {
		return false;
	}
	return $minify;
}