<?php
/**
 * Plugin Name: Defender Pro
 * Plugin URI: https://premium.wpmudev.org/project/wp-defender/
 * Version:     2.3.2
 * Description: Get regular security scans, vulnerability reports, safety recommendations and customized hardening for your site in just a few clicks. Defender is the analyst and enforcer who never sleeps.
 * Author:      WPMU DEV
 * Author URI:  https://premium.wpmudev.org/
 * WDP ID:      1081723
 * License:     GNU General Public License (Version 2 - GPLv2)
 * Text Domain: wpdef
 * Network: true
 */


class WP_Defender {

	/**
	 * Store the WP_Defender object for singleton implement
	 *
	 * @var WP_Defender
	 */
	private static $_instance;
	/**
	 * @var string
	 */
	private $plugin_path;

	/**
	 * @return string
	 */
	public function getPluginPath() {
		return $this->plugin_path;
	}

	/**
	 * @return string
	 */
	public function getPluginUrl() {
		return $this->plugin_url;
	}

	/**
	 * @var string
	 */
	private $plugin_url;
	/**
	 * @var string
	 */
	public $domain = 'wpdef';

	/**
	 * @var string
	 */
	public $version = "2.3.2";

	/**
	 * @var string
	 */
	public $isFree = 0;
	/**
	 * @var bool
	 */
	public $is_membership = true;
	/**
	 * @var array
	 */
	public $global = array();
	/**
	 * @var string
	 */
	public $plugin_slug = 'wp-defender/wp-defender.php';

	public $db_version = "2.3.2";

	/**
	 * @return WP_Defender
	 */
	public static function instance() {
		if ( ! is_object( self::$_instance ) ) {
			self::$_instance = new WP_Defender();
		}

		return self::$_instance;
	}

	/**
	 * WP_Defender constructor.
	 */
	private function __construct() {
		$this->initVars();
		$this->includeVendors();
		$this->autoload();
		if ( class_exists( 'WP_ClI' ) ) {
			$this->initCliCommand();
		}
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_styles' ) );
		add_action( 'plugins_loaded', array( &$this, 'loadTextDomain' ) );
		include_once $this->getPluginPath() . 'main-activator.php';
		$this->global['bootstrap'] = new WD_Main_Activator( $this );
		//for the new SUI
		add_filter( 'admin_body_class', array( $this, 'adminBodyClasses' ), 11 );
	}

	public function initCliCommand() {
		WP_CLI::add_command( 'defender', '\WP_Defender\Component\Cli' );
	}

	public function loadTextDomain() {
		load_plugin_textdomain( $this->domain, false, basename( __DIR__ ) . '/languages' );
	}

	/**
	 * Init values
	 */
	private function initVars() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
	}

	public function adminBodyClasses( $classes ) {
		$pages = [
			'wp-defender',
			'wdf-hardener',
			'wdf-scan',
			'wdf-logging',
			'wdf-ip-lockout',
			'wdf-advanced-tools',
			'wdf-setting',
			'wdf-debug',
			'wdf-2fa',
			'wdf-waf',
			'wdf-tutorial'
		];
		$page  = isset( $_GET['page'] ) ? $_GET['page'] : null;
		if ( ! in_array( $page, $pages, true ) ) {
			return $classes;
		}
		$classes .= ' sui-2-9-6 ';

		return $classes;
	}

	/**
	 * Including vendors
	 */
	private function includeVendors() {
		$phpVersion = phpversion();
		if ( version_compare( $phpVersion, '5.3', '>=' ) && ! function_exists( 'initCacheEngine' ) ) {
			//if current theme is Avanda, turn off wp defender object cache as avanda agressive flush cache when any cpt save
			if ( function_exists( 'get_option' ) ) {
				$template = get_option( 'template' );
				$template = strtolower( $template );

				if ( $template == 'avada' ) {
					define( 'WD_NO_OBJECT_CACHE', 1 );
				}
			}
			include_once $this->plugin_path . 'vendor' . DIRECTORY_SEPARATOR . 'hammer' . DIRECTORY_SEPARATOR . 'bootstrap.php';
		}
		//load gettext helper
		include_once $this->plugin_path . 'vendor' . DIRECTORY_SEPARATOR . 'gettext' . DIRECTORY_SEPARATOR . 'gettext' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
		include_once $this->plugin_path . 'vendor' . DIRECTORY_SEPARATOR . 'gettext' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
		//load dashboard notice
		global $wpmudev_notices;
		$wpmudev_notices[] = array(
			'id'      => 1081723,
			'name'    => 'Defender',
			'screens' => array(
				'toplevel_page_wp-defender',
				'toplevel_page_wp-defender-network',
				'defender_page_wdf-settings',
				'defender_page_wdf-settings-network',
				'defender_page_wdf-backup',
				'defender_page_wdf-backup-network',
				'defender_page_wdf-logging',
				'defender_page_wdf-logging-network',
				'defender_page_wdf-hardener',
				'defender_page_wdf-hardener-network',
				'defender_page_wdf-debug',
				'defender_page_wdf-debug-network',
				'defender_page_wdf-scan',
				'defender_page_wdf-scan-network',
				'defender_page_wdf-ip-lockout',
				'defender_page_wdf-ip-lockout-network'
			)
		);
		/** @noinspection PhpIncludeInspection */
		include_once( $this->plugin_path . 'dash-notice/wpmudev-dash-notification.php' );
	}

	/**
	 * Register the autoload
	 */
	private function autoload() {
		spl_autoload_register( array( &$this, '_autoload' ) );
	}

	/**
	 * Register globally css, js will be load on each module
	 */
	public function register_styles() {
		wp_enqueue_style( 'defender-menu', $this->getPluginUrl() . 'assets/css/defender-icon.css' );

		$css_files = array(
			'defender' => $this->plugin_url . 'assets/css/styles.css'
		);

		foreach ( $css_files as $slug => $file ) {
			wp_register_style( $slug, $file, array(), $this->version );
		}
		$is_min   = defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) == true ? '' : '.min';
		$js_files = array(
			'wpmudev-sui' => $this->plugin_url . 'assets/js/shared-ui.js',
			'defender'    => $this->plugin_url . 'assets/js/scripts.js',
			'def-vue'     => $this->plugin_url . 'assets/js/vendor/vue.runtime' . $is_min . '.js',
		);

		foreach ( $js_files as $slug => $file ) {
			wp_register_script( $slug, $file, array( 'jquery', 'clipboard' ), $this->version, true );
		}

		wp_localize_script( 'def-vue', 'defender', array(
			'whitelabel'    => \WP_Defender\Behavior\WPMUDEV::instance()->whiteLabelStatus(),
			'misc'          => [
				'high_contrast' => \WP_Defender\Behavior\WPMUDEV::instance()->maybeHighContrast(),
			],
			'site_url'      => network_site_url(),
			'admin_url'     => network_admin_url(),
			'defender_url'  => $this->getPluginUrl(),
			'is_free'       => $this->isFree,
			'is_membership' => $this->is_membership,
			'days_of_week'  => \WP_Defender\Behavior\Utils::instance()->getDaysOfWeek(),
			'times_of_day'  => \WP_Defender\Behavior\Utils::instance()->getTimes()
		) );
		do_action( 'defender_enqueue_assets' );
	}

	/**
	 * @param $class
	 */
	public function _autoload( $class ) {
		$base_path = __DIR__ . DIRECTORY_SEPARATOR;
		$pools     = explode( '\\', $class );

		if ( $pools[0] != 'WP_Defender' ) {
			return;
		}
		if ( $pools[1] == 'Vendor' ) {
			unset( $pools[0] );
		} else {
			$pools[0] = 'App';
		}

		//build the path
		$path = implode( DIRECTORY_SEPARATOR, $pools );
		$path = $base_path . strtolower( str_replace( '_', '-', $path ) ) . '.php';
		if ( file_exists( $path ) ) {
			include_once $path;
		}
	}
}

//if we found defender free, then deactivate it
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( is_plugin_active( 'defender-security/wp-defender.php' ) ) {
	deactivate_plugins( array( 'defender-security/wp-defender.php' ) );
	update_site_option( 'defenderJustUpgrade', 1 );
}

if ( ! function_exists( 'wp_defender' ) ) {

	/**
	 * Shorthand to get the instance
	 * @return WP_Defender
	 */
	function wp_defender() {
		return WP_Defender::instance();
	}

	//init
	wp_defender();

	function wp_defender_deactivate() {
		//we disable any cron running
		wp_clear_scheduled_hook( 'processScanCron' );
		wp_clear_scheduled_hook( 'lockoutReportCron' );
		wp_clear_scheduled_hook( 'auditReportCron' );
		wp_clear_scheduled_hook( 'cleanUpOldLog' );
		wp_clear_scheduled_hook( 'scanReportCron' );
		wp_clear_scheduled_hook( 'tweaksSendNotification' );
		wp_clear_scheduled_hook( 'auditSyncWithCloud' );
		//flush events to cloud
		\WP_Defender\Module\Audit\Model\Events::instance()->sendToApi();
	}

	function wp_defender_activate() {
		if ( wp_defender()->isFree ) {
			return;
		}

		$phpVersion = phpversion();
		if ( version_compare( $phpVersion, '5.3', '>=' ) ) {
			wp_defender()->global['bootstrap']->activationHook();
		}
		$hs            = \WP_Defender\Module\Hardener\Model\Settings::instance();
		$hs->last_seen = time();
		$hs->save();
	}

	register_deactivation_hook( __FILE__, 'wp_defender_deactivate' );
	register_activation_hook( __FILE__, 'wp_defender_activate' );
}