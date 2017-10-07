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

	public function __construct( $slug, $name ) {
		$this->slug = $slug;
		$this->name = $name;
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
		 * @usedby wphb_caching_module_status()
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
	 * Return the options array for this module
	 *
	 * @return array List of options
	 */
	public function options() {
		return array();
	}

}