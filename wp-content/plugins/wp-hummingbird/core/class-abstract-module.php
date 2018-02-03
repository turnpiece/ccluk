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
	 * @return array List of options
	 */
	public function options() {
		return array();
	}

	/**
	 * Write notice or error to debug.log
	 *
	 * @since 1.7.0
	 * @param mixed  $message  Error/notice message.
	 * @param string $module   Module name.
	 */
	public static function log( $message, $module ) {
		// For now only available for page-caching and gravatar modules.
		$available_modules = array( 'page-caching', 'gravatar' );
		if ( ! in_array( $module, $available_modules, true ) ) {
			return;
		}

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// If wphb-cache dir does not exist and unable to create it - exit.
			if ( ! is_dir( WP_CONTENT_DIR . '/wphb-cache/' ) ) {
				if ( ! mkdir( WP_CONTENT_DIR . '/wphb-cache/' ) ) {
					return;
				}
			}

			// Check that page caching logging is enabled.
			if ( 'page-caching' === $module ) {
				$config_file = WP_CONTENT_DIR . '/wphb-cache/wphb-cache.php';
				if ( ! file_exists( $config_file ) ) {
					return;
				}
				$settings = json_decode( file_get_contents( $config_file ), true );

				if ( ! (bool) $settings['settings']['debug_log'] ) {
					return;
				}
			}

			if ( ! is_string( $message ) || is_array( $message ) || is_object( $message ) ) {
				$message = print_r( $message, true );
			}

			$message = '[' . date( 'H:i:s' ) . '] ' . $message . PHP_EOL;

			$file = WP_CONTENT_DIR . '/wphb-cache/' . $module . '.log';
			error_log( $message, 3, $file );
		}
	}

}