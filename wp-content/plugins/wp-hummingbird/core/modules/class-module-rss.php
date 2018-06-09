<?php

/**
 * Class WP_Hummingbird_Module_Rss
 *
 * @since 1.8
 * @package Hummingbird
 */
class WP_Hummingbird_Module_Rss extends WP_Hummingbird_Module {
	/**
	 * Initialize module.
	 */
	public function init() {
		add_action( 'wp_feed_options', array( $this, 'rss_caching_status' ) );
	}

	/**
	 * Execute module actions.
	 */
	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 */
	public function clear_cache() {}

	/**
	 * Return true if the module is activated.
	 *
	 * @return bool
	 */
	public function is_active() {
		if ( ! WP_Hummingbird_Settings::get_setting( 'enabled', $this->slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Activate module.
	 *
	 * @since 1.9.0
	 */
	public function enable() {
		WP_Hummingbird_Settings::update_setting( 'enabled', true, $this->slug );
	}

	/**
	 * Deactivate module.
	 *
	 * @since 1.9.0
	 */
	public function disable() {
		WP_Hummingbird_Settings::update_setting( 'enabled', false, $this->slug );
	}

	/**
	 * Set caching status.
	 *
	 * @param object $feed  SimplePie feed object (passed by reference).
	 */
	public function rss_caching_status( $feed ) {
		$options = $this->get_options();

		$feed->enable_cache( $options['enabled'] );
		$feed->set_cache_duration( $options['duration'] );
	}

}