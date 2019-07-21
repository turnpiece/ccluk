<?php

if ( ! function_exists( 'onesocial_cache_key' ) ){
	function onesocial_cache_key( $name, $args = array(), $timestamps = array() ){
		$timestamp = 0;
	    foreach ( $timestamps as $key ){
			$timestamp .= get_option( $key );
        }
        return md5( $name . serialize( $args ) . $timestamp );
	}
}

if ( ! function_exists( 'onesocial_cache_update' ) ){
    function onesocial_cache_update( $key ){
	    update_option( $key, current_time('timestamp') );
    }
}

if ( ! function_exists( 'onesocial_cache_on_user_profile_update' ) ){
	function onesocial_cache_on_user_profile_update( ){
		onesocial_cache_update( '_user_profile_updated');
		onesocial_cache_update( '_shop_settings_updated');
	}
	add_action( 'edit_user_profile_update', 'onesocial_cache_on_user_profile_update' );
}
