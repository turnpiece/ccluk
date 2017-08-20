<?php

/**
 * @package WordPress
 * @subpackage BuddyPress User Blog
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'BuddyBoss_SAP_Plugin' ) ):

	/**
	 *
	 * BuddyPress User Blog Main Plugin Controller
	 * *************************************
	 *
	 *
	 */
	class BuddyBoss_SAP_Plugin {
		/* Includes
		 * ===================================================================
		 */

		/**
		 * Most WordPress/BuddyPress plugin have the includes in the function
		 * method that loads them, we like to keep them up here for easier
		 * access.
		 * @var array
		 */
		private $main_includes = array(
			'sap-class',
			'bookmarks',
			'recommend-posts'
		);

		/**
		 * Admin includes
		 * @var array
		 */
		private $admin_includes = array(
			'admin'
		);

		/* Plugin Options
		 * ===================================================================
		 */

		/**
		 * Default options for the plugin, the strings are
		 * run through localization functions during instantiation,
		 * and after the user saves options the first time they
		 * are loaded from the DB.
		 *
		 * @var array
		 */
		private $default_options = array(
			'enabled' => true,
		);

		/**
		 * This options array is setup during class instantiation, holds
		 * default and saved options for the plugin.
		 *
		 * @var array
		 */
		public $options = array();

		/**
		 * Whether the plugin is activated network wide.
		 *
		 * @var boolean
		 */
		public $network_activated = false;

		/**
		 * Is BuddyPress installed and activated?
		 * @var boolean
		 */
		public $bp_enabled = false;

		/* Version
		 * ===================================================================
		 */

		/**
		 * Plugin codebase version
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Plugin database version
		 * @var string
		 */
		public $db_version = '0.0.0';

		/* Paths
		 * ===================================================================
		 */
		public $file			 = '';
		public $basename		 = '';
		public $plugin_dir		 = '';
		public $plugin_url		 = '';
		public $includes_dir	 = '';
		public $includes_url	 = '';
		public $lang_dir		 = '';
		public $assets_dir		 = '';
		public $assets_url		 = '';
		public $bower_components = '';

		/* Component State
		 * ===================================================================
		 */
		public $current_type	 = '';
		public $current_item	 = '';
		public $current_action	 = '';
		public $is_single_item	 = false;

		/* Magic
		 * ===================================================================
		 */

		/**
		 * BuddyPress User Blog uses many variables, most of which can be filtered to
		 * customize the way that it works. To prevent unauthorized access,
		 * these variables are stored in a private array that is magically
		 * updated using PHP 5.2+ methods. This is to prevent third party
		 * plugins from tampering with essential information indirectly, which
		 * would cause issues later.
		 *
		 * @see BuddyBoss_SAP_Plugin::setup_globals()
		 * @var array
		 */
		private $data;

		/* Singleton
		 * ===================================================================
		 */

		/**
		 * Main BuddyPress User Blog Instance.
		 *
		 * BuddyPress User Blog is great
		 * Please load it only one time
		 * For this, we thank you
		 *
		 * Insures that only one instance of BuddyPress User Blog exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 *
		 * @static object $instance
		 * @uses BuddyBoss_SAP_Plugin::setup_globals() Setup the globals needed.
		 * @uses BuddyBoss_SAP_Plugin::setup_actions() Setup the hooks and actions.
		 * @uses BuddyBoss_SAP_Plugin::setup_textdomain() Setup the plugin's language file.
		 * @see buddyboss_sap()
		 *
		 * @return BuddyPress User Blog The one true BuddyBoss.
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if ( null === $instance ) {
				$instance = new BuddyBoss_SAP_Plugin();
				$instance->setup_globals();
				$instance->setup_actions();
				$instance->setup_textdomain();
			}

			// Always return the instance
			return $instance;
		}

		/* Magic Methods
		 * ===================================================================
		 */

		/**
		 * A dummy constructor to prevent BuddyPress User Blog from being loaded more than once.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 * @see BuddyBoss_SAP_Plugin::instance()
		 * @see buddypress()
		 */
		private function __construct() { /* nothing here */
		}

		/**
		 * A dummy magic method to prevent BuddyPress User Blog from being cloned.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bp-user-blog' ), '1.0.0' );
		}

		/**
		 * A dummy magic method to prevent BuddyPress User Blog from being unserialized.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bp-user-blog' ), '1.0.0' );
		}

		/**
		 * Magic method for checking the existence of a certain custom field.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __isset( $key ) {
			return isset( $this->data[ $key ] );
		}

		/**
		 * Magic method for getting BuddyPress User Blog varibles.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __get( $key ) {
			return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
		}

		/**
		 * Magic method for setting BuddyPress User Blog varibles.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __set( $key, $value ) {
			$this->data[ $key ] = $value;
		}

		/**
		 * Magic method for unsetting BuddyPress User Blog variables.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __unset( $key ) {
			if ( isset( $this->data[ $key ] ) )
				unset( $this->data[ $key ] );
		}

		/**
		 * Magic method to prevent notices and errors from invalid method calls.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function __call( $name = '', $args = array() ) {
			unset( $name, $args );
			return null;
		}

		/* Plugin Specific, Setup Globals, Actions, Includes
		 * ===================================================================
		 */

		/**
		 * Setup BuddyPress User Blog plugin global variables.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 * @access private
		 *
		 * @uses plugin_dir_path() To generate BuddyPress User Blog plugin path.
		 * @uses plugin_dir_url() To generate BuddyPress User Blog plugin url.
		 * @uses apply_filters() Calls various filters.
		 */
		private function setup_globals( $args = array() ) {
			$this->network_activated = $this->is_network_activated();

			global $BUDDYBOSS_SAP;

			$saved_options	 = $this->network_activated ? get_site_option( 'buddyboss_sap_plugin_options' ) : get_option( 'buddyboss_sap_plugin_options' );
			$saved_options	 = maybe_unserialize( $saved_options );

			if ( !empty( $saved_options ) ) {
				$this->options = wp_parse_args( $saved_options, $this->default_options );
			}

			// Normalize legacy uppercase keys
			foreach ( $this->options as $key => $option ) {
				// Delete old entry
				unset( $this->options[ $key ] );

				// Override w/ lowercase key
				$this->options[ strtolower( $key ) ] = $option;
			}

			/** Versions ************************************************* */
			$this->version		 = BUDDYBOSS_SAP_PLUGIN_VERSION;
			$this->db_version	 = BUDDYBOSS_SAP_PLUGIN_DB_VERSION;

			/** Paths ***************************************************** */
			// BuddyPress User Blog root directory
			$this->file			 = BUDDYBOSS_SAP_PLUGIN_FILE;
			$this->basename		 = plugin_basename( $this->file );
			$this->plugin_dir	 = BUDDYBOSS_SAP_PLUGIN_DIR;
			$this->plugin_url	 = BUDDYBOSS_SAP_PLUGIN_URL;

			// Languages
			$this->lang_dir = dirname( $this->basename ) . '/languages/';

			// Includes
			$this->includes_dir	 = $this->plugin_dir . 'includes';
			$this->includes_url	 = $this->plugin_url . 'includes';

			// Templates
			$this->templates_dir = $this->plugin_dir . 'templates';
			$this->templates_url = $this->plugin_url . 'templates';

			// Assets
			$this->assets_dir	 = $this->plugin_dir . 'assets';
			$this->assets_url	 = $this->plugin_url . 'assets';

			//Bower components
			$this->bower_components = $this->plugin_url . 'bower_components';
		}

		/**
		 * Check if the plugin is activated network wide(in multisite)
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @return boolean
		 */
		private function is_network_activated() {
			$network_activated = false;
			if ( is_multisite() ) {
				if ( !function_exists( 'is_plugin_active_for_network' ) )
					require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

				if ( is_plugin_active_for_network( basename( constant( 'BUDDYBOSS_SAP_PLUGIN_DIR' ) ) . '/bp-user-blog.php' ) ) {
					$network_activated = true;
				}
			}
			return $network_activated;
		}

		/**
		 * Setup BuddyPress User Blog main actions
		 *
		 * @since  BuddyPress User Blog 1.0
		 */
		private function setup_actions() {
                        $network_activated = '';
			// Admin
			add_action( 'init', array( $this, 'setup_admin_settings' ) );

			add_action( 'init', array( $this, 'load_init' ), 9 );
                        
                        if (is_multisite() ) {
                            if ( !function_exists( 'is_plugin_active_for_network' ) )
                                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
                            if ( is_plugin_active_for_network( basename( constant( 'BP_PLUGIN_DIR' ) ) . '/bp-loader.php' ) ) {
					$network_activated = true;
                            }
                        }
                        //additonal check fixes multisite issue
                        if ( $network_activated ) {
                            add_action( 'bp_init', array( $this, 'bp_loaded' ), 10 );  
                        } else {
                            add_action( 'bp_loaded', array( $this, 'bp_loaded' ), 10 );
                        }
		}

		public function setup_admin_settings() {
			if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
				$this->load_admin();
			}
		}

		/**
		 * Load plugin text domain
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 *
		 * @uses sprintf() Format .mo file
		 * @uses get_locale() Get language
		 * @uses file_exists() Check for language file(filename)
		 * @uses load_textdomain() Load language file
		 */
		public function setup_textdomain() {
			$domain	 = 'bp-user-blog';
			$locale	 = apply_filters( 'plugin_locale', get_locale(), $domain );

			//first try to load from wp-content/languages/plugins/ directory
			load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo' );

			//if not found, then load from buddboss-sap/languages/ directory
			load_plugin_textdomain( 'bp-user-blog', false, $this->lang_dir );
		}

		/**
		 * We require BuddyPress to run the main components, so we attach
		 * to the 'bp_loaded' action which BuddyPress calls after it's started
		 * up. This ensures any BuddyPress related code is only loaded
		 * when BuddyPress is active.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 * @access public
		 *
		 * @return void
		 */
		public function bp_loaded() {
			$this->bp_enabled = true;
			$this->load_main();
		}

		/* Load
		 * ===================================================================
		 */

		/**
		 * Include required admin files.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 * @access private
		 *
		 * @uses $this->do_includes() Loads array of files in the include folder
		 */
		public function load_admin() {
			$this->do_includes( $this->admin_includes );

			$this->admin = BuddyBoss_SAP_Admin::instance();
		}

		/**
		 * Include required files.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 * @access private
		 *
		 * @uses BuddyBoss_SAP_Plugin::do_includes() Loads array of files in the include folder
		 */
		private function load_main() {
			$this->do_includes( $this->main_includes );

			$this->component = new BuddyBoss_SAP_BP_Component();
                        buddypress()->sap = $this->component;
		}

		/**
		 * Include blog files to added on init.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 * @access private
		 *
		 * @uses BuddyBoss_SAP_Plugin::do_includes() Loads array of files in the include folder
		 */
		public function load_init() {
			$this->do_includes( array( 'sap-functions', 'sap-blog' ) );
		}

		/* Activate/Deactivation/Uninstall callbacks
		 * ===================================================================
		 */

		/**
		 * Fires when plugin is activated
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 *
		 * @uses current_user_can() Checks for user permissions
		 * @uses check_admin_referer() Verifies session
		 */
		public function activate() {
			if ( !current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : '';

			check_admin_referer( "activate-plugin_{$plugin}" );
		}

		/**
		 * Fires when plugin is de-activated
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 *
		 * @uses current_user_can() Checks for user permissions
		 * @uses check_admin_referer() Verifies session
		 */
		public function deactivate() {
			if ( !current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : '';

			check_admin_referer( "deactivate-plugin_{$plugin}" );
		}

		/**
		 * Fires when plugin is uninstalled
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 *
		 * @uses current_user_can() Checks for user permissions
		 * @uses check_admin_referer() Verifies session
		 */
		public function uninstall() {
			if ( !current_user_can( 'activate_plugins' ) ) {
				return;
			}

			check_admin_referer( 'bulk-plugins' );

			// Important: Check if the file is the one
			// that was registered during the uninstall hook.
			if ( $this->file != WP_UNINSTALL_PLUGIN ) {
				return;
			}
		}

		/* Utility functions
		 * ===================================================================
		 */

		/**
		 * Include required array of files in the includes directory
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 *
		 * @uses require_once() Loads include file
		 */
		public function do_includes( $includes = array() ) {
			foreach ( (array) $includes as $include ) {
				require_once( $this->includes_dir . '/' . $include . '.php' );
			}
		}

		/**
		 * Convenience function to access plugin options, returns false by default
		 *
		 * @since  BuddyPress User Blog (1.0.0)
		 *
		 * @param  string $key Option key

		 * @uses apply_filters() Filters option values with 'buddyboss_sap_option' &
		 *                       'buddyboss_sap_option_{$option_name}'
		 * @uses sprintf() Sanitizes option specific filter
		 *
		 * @return mixed Option value (false if none/default)
		 *
		 */
		public function option( $key ) {
			$key	 = strtolower( $key );
			$option	 = isset( $this->options[ $key ] ) ? $this->options[ $key ] : null;

			// Apply filters on options as they're called for maximum
			// flexibility. Options are are also run through a filter on
			// class instatiation/load.
			// ------------------------
			// This filter is run for every option
			$option = apply_filters( 'buddyboss_sap_option', $option );

			// Option specific filter name is converted to lowercase
			$filter_name = sprintf( 'buddyboss_sap_option_%s', strtolower( $key ) );
			$option		 = apply_filters( $filter_name, $option );

			return $option;
		}

	}







	 // End class BuddyBoss_SAP_Plugin

endif;