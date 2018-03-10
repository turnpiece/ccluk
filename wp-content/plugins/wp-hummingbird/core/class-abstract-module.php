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
	 * @var WP_Hummingbird_Logger instance.
	 *
	 * @since 1.7.2
	 */
	public $logger;

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
	 * Execute the module actions. It must be defined in subclasses.
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