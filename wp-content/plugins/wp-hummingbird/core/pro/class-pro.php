<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Pro manages the premium side of Hummingbird
 *
 * @since 1.5.0
 */
class WP_Hummingbird_Pro {

    /**
     * Saves the modules object instances
     *
     * @var array
     * @since 1.5.0
     */
    public $modules = array();

	/**
	 * @var null|WP_Hummingbird_Pro_Admin
	 */
	public $admin;

    /**
     * Initialize the class
     *
     * @since 1.5.0
     */
	public function init() {

		//load dashboard notice
		global $wpmudev_notices;
		$wpmudev_notices[] = array(
			'id'      => 1081721,
			'name'    => 'Hummingbird',
			'screens' => array(
				'toplevel_page_wphb',
				'hummingbird_page_wphb-performance',
				'hummingbird_page_wphb-minification',
				'hummingbird_page_wphb-caching',
				'hummingbird_page_wphb-gzip',
				'hummingbird_page_wphb-uptime'
			)
		);
		/** @noinspection PhpIncludeInspection */
		include_once( wphb_plugin_dir() . 'core/pro/externals/dash-notice/wpmudev-dash-notification.php' );

		if ( is_admin() ) {
			include_once( 'admin/class-pro-admin.php' );
			$this->admin = new WP_Hummingbird_Pro_Admin();
			$this->admin->init();
		}

		$this->load_ajax();
        $this->load_modules();

        if ( is_admin() && ! get_site_option( 'wphb-pro' ) ) {
        	// Make this check only on admin to avoid extra queries
        	update_site_option( 'wphb-pro', true );
        }

        add_action( 'wphb_deactivate', array( $this, 'on_deactivate' ) );
        add_action( 'wphb_activate', array( $this, 'on_activate' ) );

	}

	public function on_deactivate() {
		delete_site_option( 'wphb-pro' );
	}

	public function on_activate() {
		update_site_option( 'wphb-pro', 'yes' );
	}

    /**
     * Load AJAX functionality
     *
     * @since 1.5.0
     */
	private function load_ajax() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            include_once( wphb_plugin_dir() . 'core/pro/class-pro-ajax.php' );
            new WP_Hummingbird_Pro_AJAX();
        }
    }

    /**
     * Load WP Hummingbird Pro modules
     *
     * @since 1.5.0
     */
    private function load_modules() {
        $modules = apply_filters( 'wp_hummingbird_modules', array(
            'reporting-cron' => __( 'Cron', 'wphb' ),
	        'reporting' => __( 'Reporting', 'wphb' )
        ) );

        array_walk( $modules, array( $this, 'load_module' ) );
    }

    /**
     * Load a single module
     *
     * @param string $name Module Name
     * @param string $module Module slug
     *
     * @since 1.5.0
     */
    public function load_module( $name, $module ) {
        $class_name = 'WP_Hummingbird_Module_' . ucfirst( $module );
        if ( 'reporting-cron' === $module ) {
            $class_name = 'WP_Hummingbird_Module_Reporting_Cron';
        }

        // Default modules files
        $filename = wphb_plugin_dir() . 'core/pro/modules/class-module-' . $module . '.php';;
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
}