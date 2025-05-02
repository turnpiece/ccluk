<?php

if (! function_exists('ccluk_cache_key')) {
	function ccluk_cache_key($name, $args = array(), $timestamps = array())
	{
		$timestamp = 0;
		foreach ($timestamps as $key) {
			$timestamp .= get_option($key);
		}
		return md5($name . serialize($args) . $timestamp);
	}
}

if (! function_exists('ccluk_cache_update')) {
	function ccluk_cache_update($key)
	{
		update_option($key, current_time('timestamp'));
	}
}

if (! function_exists('ccluk_cache_on_user_profile_update')) {
	function ccluk_cache_on_user_profile_update()
	{
		ccluk_cache_update('_user_profile_updated');
		ccluk_cache_update('_shop_settings_updated');
	}
	add_action('edit_user_profile_update', 'ccluk_cache_on_user_profile_update');
}
