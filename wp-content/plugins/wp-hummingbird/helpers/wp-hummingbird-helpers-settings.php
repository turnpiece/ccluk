<?php
/**
 * Settings helper file. Init defaults, get and update values.
 *
 * @package Hummingbird
 */

/**
 * Return the plugin settings.
 *
 * @return array Plugin Settings.
 */
function wphb_get_settings() {
	if ( ! is_multisite() ) {
		$options = get_option( 'wphb_settings', array() );
	} else {
		$blog_options = get_option( 'wphb_settings', array() );
		$network_options = get_site_option( 'wphb_settings', array() );
		$options = array_merge( $blog_options, $network_options );
	}

	return wp_parse_args( $options, wphb_get_default_settings() );
}

/**
 * Get settings.
 *
 * @param  string $option_name Return a single WP Hummingbird setting.
 * @return mixed
 */
function wphb_get_setting( $option_name ) {
	$settings = wphb_get_settings();
	if ( ! isset( $settings[ $option_name ] ) ) {
		return '';
	}

	return $settings[ $option_name ];
}

/**
 * Return the plugin default settings.
 *
 * @return array Default Plugin Settings.
 */
function wphb_get_default_settings() {
	$defaults = array(
		'minify'         => false,
		'uptime'         => false,
		'use_cdn'        => false,
		'gravatar_cache' => false,
		'page_cache'     => false,

		// Only for multisites. Toggles minification in a subsite
		// By default is true as if 'minify' is set to false, this option has no meaning.
		'minify-blog' => true,
		// Only for multisite.
		'minify-cdn'  => false,

		'block'       => array(
			'scripts' => array(),
			'styles'  => array(),
		),
		'dont_minify' => array(
			'scripts' => array(),
			'styles'  => array(),
		),
		'combine'     => array(
			'scripts' => array(),
			'styles'  => array(),
		),
		'position'    => array(
			'scripts' => array(),
			'styles'  => array(),
		),
		'defer'       => array(
			'scripts' => array(),
			'styles'  => array(),
		),
		'inline'      => array(
			'scripts' => array(),
			'styles'  => array(),
		),
		'caching_expiry_css'        => '8d/A691200',
		'caching_expiry_javascript' => '8d/A691200',
		'caching_expiry_media'      => '8d/A691200',
		'caching_expiry_images'     => '8d/A691200',

		'cloudflare-email'          => '',
		'cloudflare-api-key'        => '',
		'cloudflare-zone'           => '',
		'cloudflare-zone-name'      => '',
		'cloudflare-connected'      => false,
		'cloudflare-plan'           => false,
		'cloudflare-page-rules'     => array(),
		'cloudflare-caching-expiry' => 691200,
	);

	/**
	 * Filter the default settings.
	 * Useful when adding new settings to the plugin
	 */
	return apply_filters( 'wp_hummingbird_default_options', $defaults );
}

/**
 * Array of settings per site.
 *
 * @return array
 */
function wphb_get_blog_option_names() {
	return array( 'block', 'minify-blog', 'minify-cdn', 'dont_minify', 'defer', 'inline', 'combine', 'position', 'max_files_in_group', 'last_change' );
}

/**
 * Get setting type. Either blog or network.
 *
 * @param string $option_name  Option.
 *
 * @return string
 */
function wphb_get_setting_type( $option_name ) {
	// Settings per site.
	$blog_options = wphb_get_blog_option_names();

	// Rest of the options are network options.
	if ( in_array( $option_name, $blog_options, true ) ) {
		return 'blog';
	}

	return 'network';
}

/**
 * Update the plugin settings.
 *
 * @param array $new_settings New settings.
 */
function wphb_update_settings( $new_settings ) {
	if ( ! is_multisite() ) {
		update_option( 'wphb_settings', $new_settings );
	} else {
		$network_options = array_diff_key( $new_settings, array_fill_keys( wphb_get_blog_option_names(), wphb_get_blog_option_names() ) );
		$blog_options = array_intersect_key( $new_settings, array_fill_keys( wphb_get_blog_option_names(), wphb_get_blog_option_names() ) );

		update_site_option( 'wphb_settings', $network_options );
		update_option( 'wphb_settings', $blog_options );
	}
}

/**
 * Update selected plugin setting.
 *
 * @param string $setting Setting name.
 * @param mixed  $value   Setting value.
 */
function wphb_update_setting( $setting, $value ) {
	$settings = wphb_get_settings();
	$settings[ $setting ] = $value;
	wphb_update_settings( $settings );
}

/**
 * Toggle minification.
 *
 * @param bool $value   Value for minification. Accepts boolean value: true or false.
 * @param bool $network Value for network. Default: false.
 */
function wphb_toggle_minification( $value, $network = false ) {
	$settings = wphb_get_settings();
	if ( is_multisite() ) {
		if ( $network ) {
			// Updating for the whole network.
			$settings['minify'] = $value;
			// If deactivated for whole network, also deactivate CDN.
			if ( false === $value ) {
				$settings['use_cdn'] = false;
			}
		} else {
			// Updating on subsite.
			if ( ! $settings['minify'] ) {
				// Minification is turned down for the whole network, do not activate it per site.
				$settings['minify-blog'] = false;
			} else {
				$settings['minify-blog'] = $value;
			}
		}
	} else {
		$settings['minify'] = $value;
	}

	wphb_update_settings( $settings );
}

/**
 * Toggle CDN helper function.
 *
 * @param bool $value    CDN status to set.
 */
function wphb_toggle_cdn( $value ) {
	$settings = wphb_get_settings();

	if ( is_multisite() ) {
		// This is for the whole multisite.
		$settings['use_cdn'] = $value;
	} else {
		$settings['minify-cdn'] = $value;
	}

	wphb_update_settings( $settings );
}

/**
 * Get CDN status.
 *
 * @return bool
 * @since 1.5.2
 */
function wphb_get_cdn_status() {
	$options = wphb_get_settings();

	if ( is_multisite() ) {
		$current = $options['use_cdn'];
	} else {
		$current = $options['minify-cdn'];
	}

	return $current;
}