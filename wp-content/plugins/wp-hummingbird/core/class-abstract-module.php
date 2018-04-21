<?php

/**
 * Class WP_Hummingbird_Module
 *
 * Abstract class that define every module in WP Hummingbird
 */
abstract class WP_Hummingbird_Module {

	/**
	 * Module slug name
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Logger
	 *
	 * @var WP_Hummingbird_Logger instance.
	 *
	 * @since 1.7.2
	 */
	public $logger;

	/**
	 * WP_Hummingbird_Module constructor.
	 *
	 * @param string $slug  Module slug.
	 * @param string $name  Module name.
	 */
	public function __construct( $slug, $name ) {
		$this->slug = $slug;
		$this->name = $name;
		$this->logger = new WP_Hummingbird_Logger( $this->slug );
		$this->init();
	}

	/**
	 * Return true if the module is activated
	 *
	 * @return Boolean
	 */
	public function is_active() {
		$slug = $this->get_slug();

		/**
		 * Filters the activation of a module
		 *
		 * @usedby wphb_uptime_module_status()
		 * @usedby wphb_minify_module_status()
		 *
		 * @param boolean $active if the module is active or not
		 */
		return apply_filters( "wp_hummingbird_is_active_module_$slug", true );
	}

	/**
	 * Checks if user is on the page of a specific module.
	 *
	 * @since 1.8.1
	 *
	 * @param bool $dashboard  If set, function will return true when user is either on module page
	 *                         or on dashboard page.
	 *
	 * @return bool
	 */
	public function is_on_page( $dashboard = false ) {
		$slug = $this->slug;
		$page = get_current_screen()->id;

		/**
		'toplevel_page_wphb',
		'hummingbird_page_wphb-performance',
		'hummingbird_page_wphb-minification',
		'hummingbird_page_wphb-caching',
		'hummingbird_page_wphb-gzip',
		'hummingbird_page_wphb-uptime',
		'hummingbird-pro_page_wphb-advanced',
		'toplevel_page_wphb-network',
		'hummingbird_page_wphb-performance-network',
		'hummingbird_page_wphb-minification-network',
		'hummingbird_page_wphb-caching-network',
		'hummingbird_page_wphb-gzip-network',
		'hummingbird_page_wphb-uptime-network',
		 */

		// Asset optimization module has a different slug rather than the page id.
		if ( 'minify' === $slug ) {
			$slug = 'minification';
		}

		// Check if on dashboard page.
		if ( $dashboard && preg_match( '/^(toplevel_page_wphb)/', $page ) ) {
			return true;
		}

		// Check if on module page.
		if ( preg_match( "/(wphb-{$slug})/", $page ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return the module slug name
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Return the module name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Initializes the module. Always executed even if the module is deactivated.
	 *
	 * Do not use __construct in subclasses, use init() instead
	 */
	public abstract function init();

	/**
	 * Execute the module actions. It must be defined in subclasses. Executed when module is active.
	 */
	public abstract function run();

	/**
	 * Clear the module cache.
	 *
	 * @since 1.7.1
	 * @return mixed
	 */
	public abstract function clear_cache();

	/**
	 * Return the options array for this module
	 *
	 * @since  1.8
	 *
	 * @return array List of options
	 */
	public function get_options() {
		return WP_Hummingbird_Settings::get_settings( $this->get_slug() );
	}

	/**
	 * Update the settings for the module.
	 *
	 * @since  1.8
	 *
	 * @param array $options List of settings.
	 */
	public function update_options( $options ) {
		WP_Hummingbird_Settings::update_settings( $options, $this->get_slug() );
	}

}