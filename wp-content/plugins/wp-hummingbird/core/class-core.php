<?php

/**
 * Class WP_Hummingbird_Core
 */
class WP_Hummingbird_Core {

	/**
	 * API
	 *
	 * @var WP_Hummingbird_API
	 */
	public $api;

	/**
	 * Hub endpoints
	 *
	 * @var WP_Hummingbird_Hub_Endpoints
	 */
	public $hub_endpoints;

	/**
	 * Saves the modules object instances
	 *
	 * @var array
	 */
	public $modules = array();

	/**
	 * WP_Hummingbird_Core constructor.
	 */
	public function __construct() {
		$this->includes();

		$this->init();

		$this->load_modules();

		// Return is user has no proper permissions.
		if ( ! ( is_super_admin() || is_blog_admin() ) ) {
			return;
		}

		if ( WP_Hummingbird_Utils::can_execute_php() && current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			$minify    = WP_Hummingbird_Settings::get_setting( 'enabled', 'minify' );
			$pc_module = WP_Hummingbird_Settings::get_setting( 'enabled', 'page_cache' );

			// Do not stric compare $pc_module to true, because it can also be 'blog-admins'.
			if ( ! is_multisite() || ( is_multisite() && ( ( 'super-admin' === $minify && is_super_admin() ) || true === $minify || true == $pc_module ) ) ) {
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
			}
		}
	}

	/**
	 * Includes.
	 */
	private function includes() {
		/* @noinspection PhpIncludeInspection */
		include_once WPHB_DIR_PATH . 'core/settings-hooks.php';
		/* @noinspection PhpIncludeInspection */
		include_once WPHB_DIR_PATH . 'core/api/class-api.php';
		/* @noinspection PhpIncludeInspection */
		include_once WPHB_DIR_PATH . 'core/class-hub-endpoints.php';
		/* @noinspection PhpIncludeInspection */
		include_once WPHB_DIR_PATH . 'core/class-logger.php';
	}

	/**
	 * Initialize core modules.
	 *
	 * @since 1.7.2
	 */
	private function init() {
		// Init the API.
		$this->api = new WP_Hummingbird_API();

		// Init Hub endpoints.
		$this->hub_endpoints = new WP_Hummingbird_Hub_Endpoints();
		$this->hub_endpoints->init();
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
			'page_cache'   => __( 'Page Caching', 'wphb' ),
			'advanced'     => __( 'Advanced Tools', 'wphb' ),
			'rss'          => __( 'RSS Caching', 'wphb' ),
		) );

		// Do not load minification for PHP less than 5.3.
		if ( ! WP_Hummingbird_Utils::can_execute_php() ) {
			unset( $modules['minify'] );
		}

		/* @noinspection PhpIncludeInspection */
		include_once WPHB_DIR_PATH . 'core/class-abstract-module.php';
		/* @noinspection PhpIncludeInspection */
		include_once WPHB_DIR_PATH . 'core/class-abstract-module-server.php';

		array_walk( $modules, array( $this, 'load_module' ) );
	}

	/**
	 * Load a single module
	 *
	 * @param string $name   Module name.
	 * @param string $module Module slug.
	 */
	public function load_module( $name, $module ) {
		// Split complex slugs (name_subname or name-subname) to an array.
		$module_slug = preg_split( '/(_|-)/', $module );
		// Glue together to form name-subname (for filename).
		$file_part = implode( '-', $module_slug );
		// Capitalize each word in array (to be used in class name).
		$module_slug = array_map( 'ucfirst', $module_slug );
		// Glue together to form Name_Subname.
		$class_name = 'WP_Hummingbird_Module_' . implode( '_', $module_slug );

		// Default modules files.
		$filename = WPHB_DIR_PATH . 'core/modules/class-module-' . $file_part . '.php';
		if ( file_exists( $filename ) ) {
			/* @noinspection PhpIncludeInspection */
			include_once $filename;
		}

		if ( class_exists( $class_name ) ) {
			$module_obj = new $class_name( $module, $name );

			/* @var WP_Hummingbird_Module $module_obj */
			if ( $module_obj->is_active() ) {
				$module_obj->run();
			}

			$this->modules[ $module ] = $module_obj;
		}
	}

	/**
	 * Add a HB menu to the admin bar
	 *
	 * @param WP_Admin_Bar $admin_bar  Admin bar.
	 */
	public function admin_bar_menu( $admin_bar ) {
		/* @var WP_Hummingbird_Module_Minify $minification_module */
		$minification_module = WP_Hummingbird_Utils::get_module( 'minify' );

		$menu_args = array(
			'id'    => 'wphb',
			'title' => 'Hummingbird',
			'href'  => admin_url( 'admin.php?page=wphb' ),
		);

		if ( is_multisite() && is_main_site() ) {
			$menu_args['href'] = network_admin_url( 'admin.php?page=wphb' );
		} elseif ( is_multisite() && ! is_main_site() ) {
			unset( $menu_args['href'] );
		}

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

		/* @var WP_Hummingbird_Module_Page_Cache $pc_module */
		$pc_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$options = $pc_module->get_options();

		if ( $pc_module->is_active() && $options['control'] ) {
			$pc_link = admin_url( 'admin.php?page=wphb-caching&view=main' );

			if ( ! is_main_network() || ! is_main_site() ) {
				$purge_url = add_query_arg( array(
					'action'  => 'clear_cache',
					'module'  => 'page_cache',
				), $pc_link );
			} else {
				$purge_url = add_query_arg( array(
					'action'  => 'clear_cache',
					'module'  => 'page_cache',
				), $pc_link );
			}
			$purge_url = wp_nonce_url( $purge_url, 'wphb-caching-actions' );

			$admin_bar->add_menu( $menu_args );
			$admin_bar->add_menu( array(
				'id'     => 'wphb-clear-cache',
				'title'  => __( 'Clear page cache', 'wphb' ),
				'href'   => $purge_url,
				'parent' => 'wphb',
			));
		}
	}

}