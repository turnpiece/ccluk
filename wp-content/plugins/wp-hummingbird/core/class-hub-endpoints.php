<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Hub_Endpoints
 *
 * Manage WPMU DEV Hub API endpoints
 */
class WP_Hummingbird_Hub_Endpoints {

	/**
	 * Endpoints array.
	 *
	 * @var array
	 */
	private $endpoints = array( 'get', 'performance' );

	/**
	 * Hub Endpoints Initialize
	 */
	public function init() {
		spl_autoload_register( array( $this, 'autoload' ) );
		add_filter( 'wdp_register_hub_action', array( $this, 'add_endpoints' ) );
	}

	/**
	 * Hub Endpoints class autoloader
	 *
	 * @param string $classname  Class name.
	 */
	public function autoload( $classname ) {
		if ( 0 !== strpos( $classname, 'WP_Hummingbird_Hub_Endpoint' ) ) {
			return;
		}

		$filename = 'class-hub-endpoint';
		$name = str_replace( '_', '-', strtolower( str_replace( 'WP_Hummingbird_Hub_Endpoint', '', $classname ) ) );
		if ( $name ) {
			$filename .= $name . '.php';
		} else {
			$filename .= '.php';
		}

		if ( file_exists( WPHB_DIR_PATH . 'core/hub-endpoints/' . $filename ) ) {
			/* @noinspection PhpIncludeInspection */
			include_once 'hub-endpoints/' . $filename;
		}
	}

	/**
	 * Add Hub endpoints
	 *
	 * Every Hub Endpoint name is build following the structure: 'wphb-$endpoint-$action'
	 * Examples:
	 * wphb-browser-caching-get
	 * wphb-gzip-get
	 *
	 * @param array $actions  Endpoint action.
	 *
	 * @return array
	 */
	public function add_endpoints( $actions ) {
		foreach ( $this->endpoints as $endpoint ) {
			$actions[ "wphb-{$endpoint}" ] = array( $this, 'action_' . $endpoint );
		}
		return $actions;
	}

	/**
	 * Retrieve data for endpoint.
	 *
	 * @param array  $params  Parameters.
	 * @param string $action  Action.
	 * @return void|WP_Error
	 */
	public function action_get( $params, $action ) {
		$result = array();

		/**
		 * Gzip
		 */
		$status = WP_Hummingbird_Utils::get_status( 'gzip' );

		if ( ! is_array( $status ) ) {
			$result['gzip'] = new WP_Error( 'gzip-status-not-found', 'There is not Gzip data yet' );
		} else {
			$result['gzip'] = array();
			foreach ( $status as $status_name => $status_value ) {
				$result['gzip']['status'][ strtolower( $status_name ) ] = $status_value;
			}
		}

		/**
		 * Caching
		 */
		$status = WP_Hummingbird_Utils::get_status( 'caching' );

		if ( ! is_array( $status ) ) {
			$result['browser-caching'] = new WP_Error( 'browser-caching-status-not-found', 'There is not Browser Caching data yet' );
		} else {
			$result['browser-caching'] = array();
			foreach ( $status as $status_name => $status_value ) {
				$result['browser-caching']['status'][ strtolower( $status_name ) ] = $status_value;
			}
		}

		/**
		 * Gravatar
		 * @var WP_Hummingbird_Module_Gravatar $module
		 */
		$module = WP_Hummingbird_Utils::get_module( 'gravatar' );
		$result['gravatar']['is_active'] = $module->is_active();
		$result['gravatar']['error'] = is_wp_error( $module->error );

		/**
		 * Asset Optimization
		 *
		 * @var WP_Hummingbird_Module_Minify $module
		 */
		$module = WP_Hummingbird_Utils::get_module( 'minify' );
		$collection = $module->get_resources_collection();
		if ( empty( $collection ) ) {
			$result['minify'] = new WP_Error( 'minify-status-not-found', 'There is no Asset Optimization data yet' );
		} elseif ( ! $module->is_active() ) {
			$result['minify'] = new WP_Error( 'minify-disabled', 'Asset Optimization module not activated' );
		} else {
			$original_size_styles  = array_sum( wp_list_pluck( $collection['styles'], 'original_size' ) );
			$original_size_scripts = array_sum( wp_list_pluck( $collection['scripts'], 'original_size' ) );
			$original_size = $original_size_scripts + $original_size_styles;

			$compressed_size_styles  = array_sum( wp_list_pluck( $collection['styles'], 'compressed_size' ) );
			$compressed_size_scripts = array_sum( wp_list_pluck( $collection['scripts'], 'compressed_size' ) );
			$compressed_size = $compressed_size_scripts + $compressed_size_styles;

			if ( ( $original_size_scripts + $original_size_styles ) <= 0 ) {
				$percentage = 0;
			} else {
				$percentage = 100 - (int) $compressed_size * 100 / (int) $original_size;
			}

			$compressed_size_scripts = number_format( $original_size_scripts - $compressed_size_scripts, 0 );
			$compressed_size_styles  = number_format( $original_size_styles - $compressed_size_styles, 0 );

			$result['minify']['status']['files']      = count( $collection['scripts'] ) + count( $collection['styles'] );
			$result['minify']['status']['original']   = number_format( $original_size, 1 );
			$result['minify']['status']['compressed'] = number_format( $compressed_size, 1 );
			$result['minify']['status']['percent']    = number_format_i18n( $percentage, 1 );
			$result['minify']['status']['saved_js']   = $compressed_size_scripts;
			$result['minify']['status']['saved_css']  = $compressed_size_styles;
			$result['minify']['status']['cdn']        = $module->get_cdn_status();
		} // End if().

		/**
		 * Page caching
		 * @var WP_Hummingbird_Module_Page_Cache $module
		 */
		$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$result['page-caching']['status'] = $module->is_active();

		/**
		 * Reports
		 * @var WP_Hummingbird_Module_Performance $performance_module
		 */
		$performance_module = WP_Hummingbird_Utils::get_module( 'performance' );
		$performance_is_active = $performance_module->is_active();

		/* @var WP_Hummingbird_Module_Uptime $uptime_module */
		$uptime_module = WP_Hummingbird_Utils::get_module( 'uptime' );
		$uptime_is_active = $uptime_module->is_active();

		$frequency = '';
		if ( $performance_is_active ) {
			$options = $performance_module->get_options();
			$frequency = $options['frequency'];
			switch ( $frequency ) {
				case 1:
					$frequency = __( 'Daily', 'wphb' );
					break;
				case 7:
					$frequency = __( 'Weekly', 'wphb' );
					break;
				case 30:
					$frequency = __( 'Monthly', 'wphb' );
					break;
			}
		}

		$result['reports']['uptime']['performance_is_active'] = $performance_is_active;
		$result['reports']['uptime']['uptime_is_active'] = $uptime_is_active;
		$result['reports']['uptime']['frequency'] = $frequency;

		$result = (object) $result;
		wp_send_json_success( $result );
	}

	/**
	 * Update performance scan from the Hub.
	 *
	 * @since 1.6.1
	 * @param array  $params  Parameters.
	 * @param string $action  Action.
	 */
	public function action_performance( $params, $action ) {
		// Refresh report if run from the Hub.
		WP_Hummingbird_Module_Performance::refresh_report();
	}

}