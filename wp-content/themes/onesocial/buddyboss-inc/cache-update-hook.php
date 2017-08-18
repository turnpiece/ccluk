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

if ( ! function_exists( 'onesocial_cache_on_product_update' ) ){
	function onesocial_cache_on_product_update(){
		onesocial_cache_update( '_product_updated');
		//onesocial_cache_update( '_product_cat_updated');
	}
	add_action( 'save_post_product', 'onesocial_cache_on_product_update' );
}

if ( ! function_exists( 'onesocial_cache_on_product_cat_update' ) ){
	function onesocial_cache_on_product_cat_update( ){
		onesocial_cache_update( '_product_cat_updated');
	}
	add_action( 'edited_product_cat', 'onesocial_cache_on_product_cat_update' );
}

if ( ! function_exists( 'onesocial_cache_on_wc_settings_update' ) ){
	function onesocial_cache_on_wc_settings_update( ){
		onesocial_cache_update( '_wc_setting_updated');
	}
	add_action( 'woocommerce_settings_saved', 'onesocial_cache_on_wc_settings_update' );
}

if ( ! function_exists( 'onesocial_cache_on_shop_update' ) ){
	function onesocial_cache_on_shop_update( ){
		onesocial_cache_update( '_shop_settings_updated');
	}
	add_action( 'wcvendors_shop_settings_saved', 'onesocial_cache_on_shop_update' );
}

if ( ! function_exists( 'onesocial_cache_on_user_profile_update' ) ){
	function onesocial_cache_on_user_profile_update( ){
		onesocial_cache_update( '_user_profile_updated');
		onesocial_cache_update( '_shop_settings_updated');
	}
	add_action( 'edit_user_profile_update', 'onesocial_cache_on_user_profile_update' );
}

