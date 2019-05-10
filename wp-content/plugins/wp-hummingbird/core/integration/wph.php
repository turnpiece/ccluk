<?php
/**
 * Compatibility with WP Hide & Security Enhancer.
 *
 * @since 1.9.4
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_WPH_Integration
 */
class WP_Hummingbird_WPH_Integration {

	/**
	 * WP_Hummingbird_WPH_Integration constructor.
	 *
	 * @since 1.9.4
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'enable_integration' ) );
	}

	/**
	 * Enable integration.
	 *
	 * @since 1.9.4
	 */
	public function enable_integration() {
		// If WP Hide & Security Enhancer is not enabled - return.
		if ( ! defined( 'WPH_PATH' ) || ! defined( 'WPH_CORE_VERSION' ) ) {
			return;
		}


		if ( ! WP_Hummingbird_Settings::get_setting( 'enabled', 'page_cache' ) ) {
			return;
		}

		add_filter( 'wphb_cache_content', array( $this, 'replace_links' ) );
	}

	/**
	 * Replace links when URLs are replaced in WP Hide & Security Enhancer.
	 *
	 * @since 1.9.4
	 *
	 * @param string $content  Page buffer.
	 *
	 * @return string
	 */
	public function replace_links( $content ) {
		global $wph;

		$content = $wph->ob_start_callback( $content );

		return $content;
	}

}

new WP_Hummingbird_WPH_Integration();
