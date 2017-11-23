<?php

/**
 * Class WP_Hummingbird_Core
 */
class WP_Hummingbird_Core {

	/**
	 * Saves the modules object instances
	 *
	 * @var array
	 */
	public $modules = array();

	public function __construct() {
		$this->includes();

		// Init the API.
		/* @noinspection PhpIncludeInspection */
		include_once( wphb_plugin_dir() . 'core/api/class-api.php' );
		$this->api = new WP_Hummingbird_API();

		// Init Hub endpoints.
		/* @noinspection PhpIncludeInspection */
		include_once( wphb_plugin_dir() . 'core/class-hub-endpoints.php' );
		$this->hub_endpoints = new WP_Hummingbird_Hub_Endpoints();
		$this->hub_endpoints->init();

		$this->load_modules();

		if ( wphb_can_execute_php() ) {
			$minify = wphb_get_setting( 'minify' );
			if ( ( is_multisite() && ( ( 'super-admins' === $minify && is_super_admin() ) || ( true === $minify && current_user_can( wphb_get_admin_capability() ) ) ) )
				|| ( ! is_multisite() && current_user_can( wphb_get_admin_capability() ) ) ) {
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
			}
		}

	}

	private function includes() {
		/** @noinspection PhpIncludeInspection */
		include_once( wphb_plugin_dir() . 'core/settings-hooks.php' );
		/** @noinspection PhpIncludeInspection */
		include_once( wphb_plugin_dir() . 'core/modules-hooks.php' );
	}

	/**
	 * Load WP Hummingbird modules
	 */
	private function load_modules() {
		/**
		 * Filters the modules slugs list
		 */
		$modules = apply_filters( 'wp_hummingbird_modules', array(
			'minify'       => __( 'Minify', 'wphb' ),
			'gzip'         => __( 'Gzip', 'wphb' ),
			'caching'      => __( 'Caching', 'wphb' ),
			'performance'  => __( 'Performance', 'wphb' ),
			'uptime'       => __( 'Uptime', 'wphb' ),
			'smush'        => __( 'Smush', 'wphb' ),
			'cloudflare'   => __( 'Cloudflare', 'wphb' ),
			'gravatar'     => __( 'Gravatar Caching', 'wphb' ),
			'page-caching' => __( 'Page Caching', 'wphb' ),
		) );

		// Do not load minification for PHP less than 5.3.
		if ( ! wphb_can_execute_php() ) {
			unset( $modules['minify'] );
		}

		/* @noinspection PhpIncludeInspection */
		include_once wphb_plugin_dir() . 'core/class-abstract-module.php';
		include_once wphb_plugin_dir() . 'core/class-abstract-module-server.php';

		array_walk( $modules, array( $this, 'load_module' ) );
	}

	/**
	 * Load a single module
	 *
	 * @param string $name Module Name
	 * @param string $module Module slug
	 */
	public function load_module( $name, $module ) {
		$module_slug = explode( '-', $module );
		$module_slug = array_map( 'ucfirst', $module_slug );
		$module_slug = implode( '_', $module_slug );
		$class_name = 'WP_Hummingbird_Module_' . $module_slug;

		// Default modules files.
		$filename = wphb_plugin_dir() . 'core/modules/class-module-' . $module . '.php';
		if ( file_exists( $filename ) ) {
			include_once $filename;
		}

		if ( class_exists( $class_name ) ) {
			$module_obj = new $class_name( $module, $name );

			/** @var WP_Hummingbird_Module $module_obj */
			if ( $module_obj->is_active() ) {
				$module_obj->run();
			}

			$this->modules[ $module ] = $module_obj;
		}
	}

	/**
	 * Add a HB menu to the admin bar
	 *
	 * @param WP_Admin_Bar $admin_bar
	 */
	public function admin_bar_menu( $admin_bar ) {
		/* @var WP_Hummingbird_Module_Minify $minification_module */
		$minification_module = wphb_get_module( 'minify' );

		$menu_args = array(
			'id'    => 'wphb',
			'title' => 'Hummingbird',
			'href'  => admin_url( 'admin.php?page=wphb-minification' ),
		);

		if ( $minification_module->is_active() ) {
			if ( ! is_admin() && ! isset( $_GET['avoid-minify'] ) ) {
				$admin_bar->add_menu( $menu_args );
				$admin_bar->add_menu( array(
					'id'     => 'wphb-page-minify',
					'title'  => __( 'See this page unminified', 'wphb' ),
					'href'   => add_query_arg( 'avoid-minify', 'true' ),
					'parent' => 'wphb',
				));
			}
		} else {
			if ( ! is_admin() && isset( $_GET['avoid-minify'] ) ) {
				$admin_bar->add_menu( $menu_args );
				$admin_bar->add_menu( array(
					'id'     => 'wphb-page-minify',
					'title'  => __( 'See this page minified', 'wphb' ),
					'href'   => remove_query_arg( 'avoid-minify' ),
					'parent' => 'wphb',
				));
			}
		}
	}

}