<?php
/*
Plugin Name: Snapshot Pro
Version: 3.1.9
Description: This plugin allows you to take quick on-demand backup snapshots of your working WordPress database. You can select from the default WordPress tables as well as custom plugin tables within the database structure. All snapshots are logged, and you can restore the snapshot as needed.
Author: WPMU DEV
Author URI: https://premium.wpmudev.org/
Plugin URI: https://premium.wpmudev.org/project/snapshot/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: snapshot
Domain Path: languages
Network: true
WDP ID: 257
 */

/**
 * @copyright Incsub (http://incsub.com/)
 *
 * Authors: WPMU DEV
 * Contributors: Rheinard Korf (Incsub), Cvetan Cvetanov (Incsub), Paul Menard, Vladislav Bailovic, Aaron Edwards
 *
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */

define('SNAPSHOT_VERSION', '3.1.9');

if ( ! defined( 'SNAPSHOT_I18N_DOMAIN' ) ) {
	define( 'SNAPSHOT_I18N_DOMAIN', 'snapshot' );
}

if ( ! defined( 'SNAPSHOT_TD' ) ) {
	define( 'SNAPSHOT_TD', 'snapshot' );
}

/* Load important file functions (and everything that goes with it). */
require_once ABSPATH . 'wp-admin/includes/admin.php';
/* Load shared-ui */
require_once 'assets/shared-ui/plugin-ui.php';
require_once 'new-ui-tester.php';

if ( ! class_exists( 'WPMUDEVSnapshot' ) ) {

	class WPMUDEVSnapshot {

		/**
		 * Singleton instance of the plugin.
		 *
		 * @since 2.5
		 *
		 * @access private
		 * @var WPMUDEVSnapshot
		 */
		private static $instance = null;

		public $DEBUG = false;
		private $_pagehooks = array(); // A list of our various nav items. Used when hooking into the page load actions.
		private $_messages = array(); // Message set during the form processing steps for add, edit, udate, delete, restore actions
		private $_settings = array(); // These are global dynamic settings NOT stores as part of the config options
		private $_admin_header_error; // Set during processing will contain processing errors to display back to the user
		private $snapshot_logger;
		private $_session;

		public $_snapshot_admin_metaboxes;
		public $_new_ui_tester;

		private $plugin_path;
		private $plugins_dir;
		private $plugins_folder;
		private $content_folder;
		private $plugin_url;
		private $plugin_file;

		public $form_errors;
		public $config_data;

		protected function __construct() {

			// Creates the class autoloader.
			spl_autoload_register( array( $this, 'class_loader' ) );

			$this->plugin_file = __FILE__;
			$this->plugin_path = plugin_dir_path( __FILE__ );
			$this->plugin_url = plugin_dir_url( __FILE__ );

			$this->DEBUG = false;
			$this->_settings['SNAPSHOT_VERSION'] = SNAPSHOT_VERSION;

			if ( is_multisite() ) {
				$this->_settings['SNAPSHOT_MENU_URL'] = network_admin_url() . 'admin.php?page=';
			} else {
				$this->_settings['SNAPSHOT_MENU_URL'] = get_admin_url() . 'admin.php?page=';
			}

			if ( defined( 'WP_PLUGIN_DIR' ) && WP_PLUGIN_DIR ) {
				$this->plugins_dir = WP_PLUGIN_DIR;
				$plugin_folder = str_replace( WP_CONTENT_DIR, '', WP_PLUGIN_DIR );
				$this->plugins_folder = str_replace( ABSPATH, '', WP_PLUGIN_DIR );
				$this->content_folder = str_replace( $plugin_folder, '', $this->plugins_folder );
			} else {
				$this->plugins_dir = trailingslashit( WP_CONTENT_DIR ) . 'plugins/';
				$this->plugins_folder = 'wp-content/plugins';
				$this->content_folder = 'wp-content/';
			}

			$this->_settings['SNAPSHOT_PLUGIN_URL'] = trailingslashit( WP_PLUGIN_URL ) . basename( dirname( __FILE__ ) );
			$this->_settings['SNAPSHOT_PLUGIN_BASE_DIR'] = dirname( __FILE__ );
			$this->_settings['admin_menu_label'] = __( "Snapshot", SNAPSHOT_I18N_DOMAIN ); // Used as the 'option_name' for wp_options table

			$this->_settings['options_key'] = "wpmudev_snapshot";

			$this->_settings['recover_table_prefix'] = "_snapshot_recover_";

			$this->_settings['backupBaseFolderFull'] = ""; // Will be set during page load in $this->set_backup_folder();
			$this->_settings['backupBackupFolderFull'] = ""; // Will be set during page load in $this->set_backup_folder();
			$this->_settings['backupRestoreFolderFull'] = ""; // Will be set during page load in $this->set_backup_folder();
			$this->_settings['destinationClasses'] = array(); // Will be set during page load

			$this->_settings['backupLogFolderFull'] = ""; // Will be set during page load in $this->set_backup_folder();
			$this->_settings['backupSessionFolderFull'] = ""; // Will be set during page load in $this->set_backup_folder();
			$this->_settings['backupLockFolderFull'] = ""; // Will be set during page load in $this->set_backup_folder();

			$this->_settings['backupURLFull'] = ""; // Will be set during page load in $this->set_backup_folder();
			$this->_settings['backupLogURLFull'] = ""; // Will be set during page load in $this->set_backup_folder();

			$this->_settings['backup_cron_hook'] = "snapshot_backup_cron"; // Used to identify WP Cron items
			$this->_settings['remote_file_cron_hook'] = "snapshot_remote_file_cron"; // Used to identify WP Cron items
			//$this->_settings['remote_file_cron_interval']	= "snapshot-15minutes";
			$this->_settings['remote_file_cron_interval'] = "snapshot-5minutes";
			$this->_admin_header_error = "";

			// Add support for new WPMUDEV Dashboard Notices
			global $wpmudev_notices;
			$wpmudev_notices[] = array(
				'id' => 257,
				'name' => 'Snapshot',
				'screens' => array(
					'toplevel_page_snapshot_pro_dashboard',
					'snapshot_page_snapshot_pro_snapshots',
					'snapshot_page_snapshot_pro_destinations',
					'snapshot_page_snapshot_pro_managed_backups',
					'snapshot_page_snapshot_pro_import',
					'snapshot_page_snapshot_pro_settings',
				),
			);

			if ( Snapshot_Helper_Utility::is_pro() ) {
				include_once dirname( __FILE__ ) . '/lib/WPMUDEV/Dashboard/wpmudev-dash-notification.php';
			}

			add_action( 'admin_head', array( $this, 'enqueue_shared_ui' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_icon_admin_style' ) );

			/* Setup the tetdomain for i18n language handling see http://codex.wordpress.org/Function_Reference/load_plugin_textdomain */
			load_plugin_textdomain( SNAPSHOT_I18N_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			/* Standard activation hook for all WordPress plugins see http://codex.wordpress.org/Function_Reference/register_activation_hook */
			register_activation_hook( __FILE__, array( $this, 'snapshot_plugin_activation_proc' ) );
			register_deactivation_hook( __FILE__, array( $this, 'snapshot_plugin_deactivation_proc' ) );
			//add_action('plugins_loaded', array( $this, 'snapshot_plugin_activation_proc' ) );

			/* Register admin actions */
			add_action( 'init', array( $this, 'snapshot_init_proc' ) );
			add_action( 'admin_init', array( $this, 'snapshot_admin_init_proc' ) );

			add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'snapshot_admin_menu_proc' ) );
			add_action( 'admin_init', array( $this, 'redirect_old_admin_menus' ) );

			/* Hook into the WordPress AJAX systems. */
			add_action( 'wp_ajax_snapshot_backup_ajax', array( $this, 'snapshot_ajax_backup_proc' ) );
			add_action( 'wp_ajax_snapshot_show_blog_tables', array( $this, 'snapshot_ajax_show_blog_tables' ) );
			add_action( 'wp_ajax_snapshot_get_blog_restore_info', array( $this, 'snapshot_get_blog_restore_info' ) );
			add_action( 'wp_ajax_snapshot_restore_ajax', array( $this, 'snapshot_ajax_restore_proc' ) );

			add_action( 'wp_ajax_snapshot_view_log_ajax', array( $this, 'snapshot_ajax_view_log_proc' ) );
			add_action( 'wp_ajax_snapshot_item_abort_ajax', array( $this, 'snapshot_ajax_item_abort_proc' ) );
			add_action( 'wp_ajax_snapshot_disable_notif_ajax', array( $this, 'snapshot_ajax_disable_notif_proc' ) );

			add_action( 'wp_ajax_snapshot_save_key', array( $this, 'snapshot_save_key_proc' ) );

			/* Cron related functions */
			add_filter( 'cron_schedules', array( 'Snapshot_Helper_Utility', 'add_cron_schedules' ), 99 );
			add_action( $this->_settings['backup_cron_hook'], array( $this, 'snapshot_backup_cron_proc' ) );
			add_action( $this->_settings['remote_file_cron_hook'], array( $this, 'snapshot_remote_file_cron_proc' ) );

			/* Snapshot Destination AJAX */
			add_action( 'snapshot_register_destination', array( $this, 'destination_register_proc' ) );

			add_action( 'activated_plugin', array( $this, 'snapshot_activated' ), 10, 2 );

			$this->_new_ui_tester = new WPMUDEVSnapshot_New_Ui_Tester();

			// Fix home path when integrating with Domain Mapping
			add_filter( 'snapshot_home_path', array( $this, 'snapshot_check_home_path' ) );

			// Fix DOMAIN_CURRENT_SITE if not configured
			add_filter( 'snapshot_current_domain', array( $this, 'snapshot_check_current_domain' ) );

			// Fix PATH_CURRENT_SITE if not configured
			add_filter( 'snapshot_current_path', array( $this, 'snapshot_check_current_path' ) );

			// Run the compat layer for Cron jobs
			Snapshot_Controller_Full_Cron::get()->run_compat();

			// Run the Hub integration controller
			Snapshot_Controller_Full_Hub::get()->run();
			// Run Hub reporter controller
			Snapshot_Controller_Full_Reporter::get()->run();

			add_filter( 'admin_body_class', array( $this, 'snapshot_maybe_add_body_classes' ) );

			require_once dirname( __FILE__ ) . '/lib/Snapshot/Helper/Privacy.php';
			Snapshot_Gdpr::serve();

		}

		public function snapshot_check_home_path( $path ) {
			if ( '/' === $path || 2 > strlen( $path ) ) {
				$path = ABSPATH;
			}

			return $path;
		}

		public function snapshot_check_current_domain( $path ) {
			if ( empty( $path ) ) {
				$path = preg_replace( '/(http|https):\/\/|\/$/', '', network_home_url() );
			}

			return $path;
		}

		public function snapshot_check_current_path( $path ) {
			if ( ! defined( $path ) ) {
				$blog_details = get_blog_details();
				$path = $blog_details->path;
			}

			return $path;
		}

		public function snapshot_init_proc() {

			if ( ! is_multisite() ) {
				$role = get_role( 'administrator' );

				if ( $role ) {
					$role->add_cap( 'manage_snapshots_items' );
					$role->add_cap( 'manage_snapshots_destinations' );
					$role->add_cap( 'manage_snapshots_settings' );
					$role->add_cap( 'manage_snapshots_import' );
				}

				$this->load_config();
				$this->set_backup_folder();
				$this->set_log_folders();

				if ( Snapshot_Helper_Utility::is_pro() ) {
					Snapshot_Model_Destination::load_destinations();
				}

			} else {
				global $current_site, $current_blog;
				if ( $current_site->blog_id === $current_blog->blog_id ) {

					$this->load_config();
					$this->set_backup_folder();
					$this->set_log_folders();

					if ( Snapshot_Helper_Utility::is_pro() ) {
						Snapshot_Model_Destination::load_destinations();
					}

				}
			}
			Snapshot_Controller_Full::get()->run();
		}

		/**
		 * Called from WordPress when the admin page init process is invoked.
		 * Sets up other action and filter needed within the admin area for
		 * our page display.
		 * @since 1.0.0
		 **
		 * @return void
		 */
		public function snapshot_admin_init_proc() {

			if ( is_multisite() || ! current_user_can( 'manage_snapshots_items' ) ) {
				return;
			}

			/* Hook into the Plugin listing display logic. This will call the function which adds the 'Settings' link on the row for our plugin. */
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'snapshot_plugin_settings_link_proc' ) );
		}

		/**
		 * Called when when our plugin is activated. Sets up the initial settings
		 * and creates the initial Snapshot instance.
		 *
		 * @since 1.0.0
		 * @uses $this->config_data Our class-level config data
		 * @see  $this->__construct() when the action is setup to reference this function
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_plugin_activation_proc() {
			if ( ! is_main_site() ) {
				return;
			}

			$this->load_config();
			$this->set_backup_folder();
			$this->set_log_folders();

			$this->snapshot_scheduler();
		}

		public function snapshot_plugin_deactivation_proc() {

			$this->load_config();
			$this->set_backup_folder();
			$this->set_log_folders();

			$crons = _get_cron_array();
			if ( $crons ) {
				foreach ( $crons as $cron_time => $cron_set ) {
					foreach ( $cron_set as $cron_callback_function => $cron_item ) {
						if ( "snapshot_backup_cron" === $cron_callback_function ) {
							foreach ( $cron_item as $cron_key => $cron_details ) {
								if ( isset( $cron_details['args'][0] ) ) {
									$item_key = intval( $cron_details['args'][0] );
									$timestamp = wp_next_scheduled( $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
									wp_unschedule_event( $timestamp, $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
								}
							}
						} else if ( $this->_settings['remote_file_cron_hook'] === $cron_callback_function ) {
							$timestamp = wp_next_scheduled( $this->_settings['remote_file_cron_hook'] );
							wp_unschedule_event( $timestamp, $this->_settings['remote_file_cron_hook'] );
						}
					}
				}
			}

			Snapshot_Controller_Full::get()->deactivate();
		}

		/**
		 * Display our message on the Snapshot page(s) header for actions taken
		 *
		 * @since 1.0.0
		 * @uses $this->_messages Set in form processing functions
		 *
		 * @param string $local_type
		 * @param string $local_message
		 *
		 * @return void
		 */
		public function snapshot_admin_notices_proc( $local_type = '', $local_message = '' ) {
			$message_types = array( 'success', 'warning', 'error' );

			$message_type = 'success';
			$message_text = '';

			// phpcs:ignore
			if ( isset( $_REQUEST['message'], $this->_messages[ $_REQUEST['message'] ] ) ) {
				$message_type = 'success';
				// phpcs:ignore
				$message_text = $this->_messages[ $_REQUEST['message'] ];

			} elseif ( ! empty( $this->_admin_header_error ) ) {
				$message_type = 'error';
				$message_text = $this->_admin_header_error;

			} elseif ( ! empty( $local_message ) && in_array( $local_type, $message_types, true ) ) {
				$message_type = $local_type;
				$message_text = $local_message;
			}

			$message_format = sprintf(
				'<div class="%%1$s snapshot-three wps-message wps-%%2$s-message" title="%s">
					<div class="wps-%%2$s-message-wrap"><p>%%3$s</p></div>
				</div>',
				esc_attr__( 'Click to dismiss', SNAPSHOT_I18N_DOMAIN )
			);

			if ( $message_text && $message_type ) {
				echo wp_kses_post( sprintf( $message_format, 'show', esc_attr( $message_type ), $message_text ) );
			}

			foreach ( $message_types as $message_type ) {
				echo wp_kses_post( sprintf( $message_format, 'hide', esc_attr( $message_type ), '' ) );
			}
		}

		/**
		 * Adds a 'settings' link on the plugin row
		 *
		 * @since 1.0.0
		 * @see $this->admin_init_proc where this function is referenced
		 *
		 * @param array links The default links for this plugin.
		 *
		 * @return array the same links array as was passed into function but with possible changes.
		 */
		public function snapshot_plugin_settings_link_proc( $links ) {
			$settings_link = sprintf(
                 '<a href="%s">%s</a>',
				esc_url( $this->snapshot_get_pagehook_url( 'snapshots-newui-new-snapshot' ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ) ),
				esc_html__( 'Settings', SNAPSHOT_I18N_DOMAIN )
			);
			array_unshift( $links, $settings_link );

			return $links;
		}

		/**
		 * Add the new Menu to the Tools section in the WordPress main nav
		 *
		 * @since 1.0.0
		 * @uses $this->_pagehooks
		 * @see  $this->__construct where this function is referenced
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_admin_menu_proc() {

			add_menu_page(
				_x( 'Snapshot Pro', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Snapshot', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_dashboard',
				array( $this->_new_ui_tester, 'dashboard' ),
				'div'
			);
			$this->_pagehooks['snapshots-newui-dashboard'] = add_submenu_page(
				'snapshot_pro_dashboard',
				_x( 'Dashboard', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Dashboard', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_dashboard',
				array( $this->_new_ui_tester, 'dashboard' )
			);
			$this->_pagehooks['snapshots-newui-snapshots'] = add_submenu_page(
				'snapshot_pro_dashboard',
				_x( 'Snapshots', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Snapshots', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_snapshots',
				array( $this->_new_ui_tester, 'snapshots' )
			);
			$this->_pagehooks['snapshots-newui-destinations'] = add_submenu_page(
				'snapshot_pro_dashboard',
				_x( 'Destinations', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Destinations', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_destinations',
				array( $this->_new_ui_tester, 'destinations' )
			);
			$this->_pagehooks['snapshots-newui-managed-backups'] = add_submenu_page(
				'snapshot_pro_dashboard',
				_x( 'Managed Backups', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Managed Backups', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_managed_backups',
				array( $this->_new_ui_tester, 'managed_backups' )
			);
			$this->_pagehooks['snapshots-newui-import'] = add_submenu_page(
				'snapshot_pro_dashboard',
				_x( 'Import', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Import', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_import',
				array( $this->_new_ui_tester, 'import' )
			);
			$this->_pagehooks['snapshots-newui-settings'] = add_submenu_page(
				'snapshot_pro_dashboard',
				_x( 'Settings', 'page label', SNAPSHOT_I18N_DOMAIN ),
				_x( 'Settings', 'menu label', SNAPSHOT_I18N_DOMAIN ),
				'manage_options',
				'snapshot_pro_settings',
				array( $this->_new_ui_tester, 'settings' )
			);

			// Hook into the WordPress load page action for our new nav items. This is better then checking page query_str values.
			$panels = array( 'dashboard', 'snapshots', 'destinations', 'managed-backups', 'import', 'settings' );
			$extra_actions = array(
				'destinations' => 'on_load_destination_panels',
				'managed-backups' => 'on_load_managed_backups_panels'
			);

			foreach ( $panels as $panel ) {
				add_action( 'load-' . $this->_pagehooks[ 'snapshots-newui-' . $panel ], array( $this, 'snapshot_on_load_panels' ) );

				if ( isset( $extra_actions[ $panel ] ) ) {
					add_action( 'load-' . $this->_pagehooks[ 'snapshots-newui-' . $panel ], array( $this, $extra_actions[ $panel ] ) );
				}
			}
		}

		/**
		 * Redirect old menu slugs to the new menus
		 */
		public function redirect_old_admin_menus() {

			if ( ! isset( $GLOBALS['pagenow'], $_GET['page'] ) || 'admin.php' !== $GLOBALS['pagenow'] ) {
				return;
			}

			/* old menu slug mapped to new menu slug */
			$page_map = array(
				'snapshots_edit_panel' => 'snapshots-newui-dashboard',
				'snapshots_new_panel' => 'snapshots-newui-new-snapshot',
				'snapshots_destinations_panel' => 'snapshots-newui-destinations',
				'snapshots_import_panel' => 'snapshots-newui-import',
				'snapshots_settings_panel' => 'snapshots-newui-settings',
				'snapshots_full_backup_panel' => 'snapshots-newui-managed-backups',
			);

			if ( isset( $page_map[ $_GET['page'] ] ) ) {
				if ( ! isset( $_REQUEST['snapshot-noonce-field']  ) ) {
					return;
				}
				if ( ! wp_verify_nonce( $_REQUEST['snapshot-noonce-field'], 'snapshot-nonce' ) ) {
					return;
				}
				wp_redirect( esc_url_raw( $this->snapshot_get_pagehook_url( $page_map[ $_GET['page'] ] ) ) );
			}
		}

		/**
		 * Set up the common items used on all Snapshot pages.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function snapshot_on_load_panels() {

			/* These messages are displayed as part of the admin header message see 'admin_notices' WordPress action */
			$this->_messages['success-update'] = __( 'The Snapshot has been updated.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-add'] = __( 'The Snapshot has been created.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-delete'] = __( 'Snapshot successfully deleted.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-delete-bulk'] = __( 'Selected Snapshots successfully deleted.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-restore'] = __( 'The Snapshot has been restored.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-settings'] = __( 'Settings have been updated.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-runonce'] = __( 'Item scheduled to run.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-snapshot-key'] = __( 'Your Snapshot Key has been successfully added.', SNAPSHOT_I18N_DOMAIN );

			$this->snapshot_scheduler();
			$this->snapshot_process_actions();

			add_thickbox();

			add_action( 'admin_notices', array( $this, 'snapshot_admin_notices_proc' ) );
			add_action( 'network_admin_notices', array( $this, 'snapshot_admin_notices_proc' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_footer', array( $this, 'snapshot_admin_panels_footer' ) );
		}

		/**
		 * Set up for the Managed Backups pages
		 */
		public function on_load_managed_backups_panels() {

			$this->_messages['success-update'] = __( 'The Backup has been updated.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-add'] = __( 'The Backup has been created.', SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-delete'] = __( "Backup successfully deleted.", SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-delete-bulk'] = __( "Selected Backups successfully deleted.", SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-restore'] = __( "The Backup has been restored.", SNAPSHOT_I18N_DOMAIN );
		}

		/**
		 * Set up the page with needed items for the Destinations metaboxes.
		 *
		 * @since 1.0.7
		 * @uses none
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function on_load_destination_panels() {

			// These messages are displayed as part of the admin header message see 'admin_notices' WordPress action
			$this->_messages['success-update'] = __( "The Destination has been updated.", SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-add'] = __( "The Destination has been added.", SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-delete'] = __( "The Destination has been deleted.", SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-restore'] = __( "The Destination has been restored.", SNAPSHOT_I18N_DOMAIN );
			$this->_messages['success-settings'] = __( "Settings have been updated.", SNAPSHOT_I18N_DOMAIN );

			$this->process_snapshot_destination_actions();

			add_action( 'admin_notices', array( $this, 'snapshot_admin_notices_proc' ) );
			add_action( 'network_admin_notices', array( $this, 'snapshot_admin_notices_proc' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_footer', array( $this, 'snapshot_admin_panels_footer' ) );
		}

		/**
		 * Enqueue the CSS for the menu icon
		 */
		public function enqueue_icon_admin_style() {

			wp_enqueue_style(
				'snapshot-menu-icon',
				plugins_url( 'assets/css/menu-icon.css', __FILE__ ),
				false, $this->_settings['SNAPSHOT_VERSION']
			);
		}

		/**
		 * Enqueue scripts and styles for the plugin admin
		 */
		public function enqueue_admin_scripts() {

			wp_enqueue_style(
                 'snapshots-admin-stylesheet', plugins_url( '/css/snapshots-admin-styles.css', __FILE__ ),
				false, $this->_settings['SNAPSHOT_VERSION']
                );

			wp_enqueue_script(
                 'snapshot-admin', plugins_url( '/js/snapshot-admin.js', __FILE__ ),
				array( 'jquery' ), $this->_settings['SNAPSHOT_VERSION']
                );

			wp_localize_script(
                 'snapshot-admin', 'snapshot_admin_messages', array(
				'log_viewer_title' => esc_html__( 'Snapshot Log Viewer', SNAPSHOT_I18N_DOMAIN ),
				'select_all' => esc_html__( 'Select all', SNAPSHOT_I18N_DOMAIN ),
				'unselect_all' => esc_html__( 'Unselect all', SNAPSHOT_I18N_DOMAIN ),
				'loading' => esc_html__( 'Loading...', SNAPSHOT_I18N_DOMAIN ),
				'snapshot_initializing' => esc_html__( 'Snapshot initializing', SNAPSHOT_I18N_DOMAIN ),
				'destination_type_dropbox' => esc_html__( 'Dropbox', SNAPSHOT_I18N_DOMAIN ),
				'memory' => esc_html__( 'Memory: ', SNAPSHOT_I18N_DOMAIN ),
				'memory_limit' => esc_html__( 'Limit', SNAPSHOT_I18N_DOMAIN ),
				'memory_usage' => esc_html__( 'Usage:', SNAPSHOT_I18N_DOMAIN ),
				'memory_peak' => esc_html__( 'Peak:', SNAPSHOT_I18N_DOMAIN ),
				'abort' => esc_html__( 'Abort', SNAPSHOT_I18N_DOMAIN ),
				'database' => esc_html__( 'Database:', SNAPSHOT_I18N_DOMAIN ),
				'files_label' => esc_html__( 'Files: ', SNAPSHOT_I18N_DOMAIN ),
				'finishing_snapshot' => esc_html__( 'Snapshot Finishing', SNAPSHOT_I18N_DOMAIN ),
				'snapshot_failed' => esc_html__( 'An unknown response returned from Snapshot backup attempt. Aborting. Double check Snapshot settings.', SNAPSHOT_I18N_DOMAIN ),
				'finding_filestables' => esc_html__( 'Snapshot determining tables/files to restore', SNAPSHOT_I18N_DOMAIN ),
				'files' => esc_html__( 'Files', SNAPSHOT_I18N_DOMAIN ),
				'backup_aborted' => esc_html__( 'Snapshot backup aborted', SNAPSHOT_I18N_DOMAIN ),
				'no_tables_selected' => esc_html__( 'You must select at least one table', SNAPSHOT_I18N_DOMAIN ),
				'no_files_tables_selected' => esc_html__( 'You must select which Files and/or Tables to backup in this Snapshot', SNAPSHOT_I18N_DOMAIN ),
				'missing_snapshot_timekey' => esc_html__( 'ERROR: The Snapshot timekey is not set. Try reloading the page', SNAPSHOT_I18N_DOMAIN ),
			)
                );

			/* new_ui styles and js */
			wp_enqueue_style(
                 'snapshot-pro-admin-stylesheet', plugins_url( '/assets/css/admin.css', __FILE__ ),
				array( 'wdev-plugin-ui' ), $this->_settings['SNAPSHOT_VERSION']
                );

			wp_enqueue_script(
                 'snapshot-pro-admin', plugins_url( '/assets/js/admin.min.js', __FILE__ ),
				array( 'jquery' ), $this->_settings['SNAPSHOT_VERSION']
                );

			wp_localize_script(
                 'snapshot-pro-admin', 'snapshot_messages', array(
				'snapshot_key' => esc_html__( 'Snapshot Key', SNAPSHOT_I18N_DOMAIN ),
				'snapshot_failed' => esc_html__( 'An unknown response returned from Snapshot backup attempt. Aborting. Double check Snapshot settings.', SNAPSHOT_I18N_DOMAIN ),
				'no_files_selected' => esc_html__( 'You must select at least one Files backup option.', SNAPSHOT_I18N_DOMAIN ),
				'no_tables_selected' => esc_html__( 'You must select at least one database table to include.', SNAPSHOT_I18N_DOMAIN ),
				'no_files_tables' => esc_html__( "You haven't included any files or database tables in this Snapshot. Please select what to include and try again.", SNAPSHOT_I18N_DOMAIN ),
				'loading' => esc_html__( 'Loading...', SNAPSHOT_I18N_DOMAIN ),
				'working' => esc_html__( 'Working...', SNAPSHOT_I18N_DOMAIN ),
				'snapshot_aborted' => esc_html__( 'Snapshot backup aborted', SNAPSHOT_I18N_DOMAIN ),
				'restore_aborted' => esc_html__( 'Snapshot restore aborted', SNAPSHOT_I18N_DOMAIN ),
				'missing_timekey' => esc_html__( 'ERROR: The Snapshot timekey is not set. Try reloading the page', SNAPSHOT_I18N_DOMAIN ),
				)
			);
		}

		/**
		 * Retrieve the ID of the current admin screen, sans the -network suffix
		 */
		public function get_current_screen_id() {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return '';
			}

			$screen_id = $screen->id;

			if ( '-network' === substr( $screen_id, -8, 8 ) ) {
				$screen_id = substr( $screen_id, 0, -8 );
			}

			return $screen_id;
		}

		/**
		 * Enqueue the shared-ui if within our plugin screens
		 */
		public function enqueue_shared_ui() {

			if ( ! in_array( $this->get_current_screen_id(), $this->_pagehooks, true ) ) {
				return;
			}

			WDEV_Plugin_Ui::load( plugin_dir_url( plugin_basename( __FILE__ ) ) . 'assets/shared-ui', 'wpmud' );
		}

		public function snapshot_admin_panels_footer() {
			?>
			<div style="display: none;" id="snapshot-log-view-container">
				<div id="snapshot-log-viewer"></div>
				<br /><br /></div>
                <?php
		}

		/**
		 * Plugin main action processing function. Will filter the action called then
		 * pass on to other sub-functions
		 *
		 * @since 1.0.0
		 * @uses $_REQUEST global PHP object
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_process_actions() {

			if ( is_multisite() ) {
				if ( ! is_super_admin() ) {
					return;
				}
			} else {
				if ( ! current_user_can( 'manage_snapshots_items' ) ) {
					return;
				}
			}

			$ACTION_FOUND = false;

			if ( isset( $_REQUEST['snapshot-action'] ) ) {
				$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );

				switch ( $snapshot_action ) {

					case 'add':
						if ( empty( $_POST ) || ( isset( $_POST['snapshot-noonce-field'] ) && ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-nonce' ) ) ) {
							return;
						} else {
							$this->snapshot_add_update_action_proc( $_POST );
						}

						$ACTION_FOUND = true;
						break;

					case 'delete-bulk':
						if ( empty( $_POST ) || ( isset( $_POST['snapshot-noonce-field'] ) && ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-delete' ) ) ) {
							return;
						} else if ( ( isset( $_POST['delete-bulk'] ) ) && ( count( $_POST['delete-bulk'] ) ) ) {
							$this->snapshot_delete_bulk_action_proc();
							$ACTION_FOUND = true;
						} else {
							return;
						}

						$return_url = wp_get_referer();
						if ( ! isset( $_GET['page'] ) ) {
							$_GET['page'] = 'snapshots_edit_panel';
						}
						if ( 'snapshots_edit_panel' === $_GET['page'] ) {
							$return_url = remove_query_arg( array( 'item' ), $return_url );
						}
						$return_url = remove_query_arg(
							array(
								'snapshot-action',
								'snapshot-noonce-field',
							), $return_url
						);

						$return_url = add_query_arg( 'page', sanitize_text_field( $_GET['page'] ), $return_url );
						$return_url = add_query_arg( 'message', 'success-delete-bulk', $return_url );
						$return_url = esc_url_raw( $return_url );
						if ( $return_url ) {
							wp_redirect( $return_url );
						}
						die();

					case 'delete-item':
						if ( empty( $_GET ) || ( isset( $_POST['snapshot-noonce-field'] ) && ! wp_verify_nonce( $_GET['snapshot-noonce-field'], 'snapshot-delete-item' ) ) ) {
							return;
						} else {
							$this->snapshot_delete_item_action_proc();
							$ACTION_FOUND = true;

							$return_url = wp_get_referer();
							if ( ! isset( $_GET['page'] ) ) {
								$_GET['page'] = 'snapshots_edit_panel';
							}
							if ( 'snapshots_edit_panel' === $_GET['page'] ) {
								$return_url = remove_query_arg( array( 'item' ), $return_url );
							}
							$return_url = remove_query_arg(
								array(
									'snapshot-action',
									'snapshot-noonce-field',
								), $return_url
							);

							$return_url = add_query_arg( 'page', sanitize_text_field( $_GET['page'] ), $return_url );
							$return_url = add_query_arg( 'message', 'success-delete', $return_url );
							$return_url = esc_url_raw( $return_url );
							if ( $return_url ) {
								wp_redirect( $return_url );
							}
							die();
						}

						break;

					case 'update':
						if ( empty( $_POST ) || ( isset( $_POST['snapshot-noonce-field'] ) && ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-nonce' ) ) ) {
							return;
						} else {
							$this->snapshot_add_update_action_proc( $_POST );
						}

						$ACTION_FOUND = true;
						break;

					case 'runonce':
						if ( empty( $_GET ) || ( isset( $_POST['snapshot-noonce-field'] ) && ! wp_verify_nonce( $_GET['snapshot-noonce-field'], 'snapshot-runonce' ) ) ) {
							return;
						} else {
							if ( ! isset( $_GET['item'] ) ) {
								return;
							}

							$this->snapshot_item_run_immediate( intval( $_GET['item'] ) );

							$return_url = wp_get_referer();
							if ( ! isset( $_GET['page'] ) ) {
								$_GET['page'] = 'snapshots_edit_panel';
							}
							if ( 'snapshots_edit_panel' === $_GET['page'] ) {
								$return_url = remove_query_arg( array( 'item' ), $return_url );
							}
							$return_url = remove_query_arg(
								array(
									'snapshot-action',
									'snapshot-noonce-field',
								), $return_url
							);

							$return_url = add_query_arg( 'page', sanitize_text_field( $_GET['page'] ), $return_url );
							$return_url = add_query_arg( 'message', 'success-runonce', $return_url );
							$return_url = esc_url_raw( $return_url );
							if ( $return_url ) {
								wp_redirect( $return_url );
							}
							die();
						}
						break;

					case 'settings-update':
						if ( empty( $_POST ) || ( isset( $_POST['snapshot-noonce-field'] ) && ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-settings' ) ) ) {
							return;
						} else {
							$this->snapshot_settings_config_update();
							$ACTION_FOUND = true;
						}

						break;

					case 'download-backup-archive':

						if ( ! isset( $_GET['backup-item'] ) ) {
							$ACTION_FOUND = false;
							break;
						}

						$timestamp = sanitize_text_field( $_GET['backup-item'] );

						$model = new Snapshot_Model_Full_Backup();
						$local_archive = $model->local()->get_backup( $timestamp );
						$remote_archive = $model->remote()->get_backup_link( $timestamp );

						/* Download the local archive if it exists */
						if ( file_exists( $local_archive ) ) {

							header( 'Content-Description: Snapshot Managed Backup Archive File' );
							header( 'Content-Type: application/zip' );
							header( 'Content-Disposition: attachment; filename=' . basename( $local_archive ) );
							header( 'Content-Transfer-Encoding: binary' );
							header( 'Expires: 0' );
							header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
							header( 'Pragma: public' );
							header( 'Content-Length: ' . filesize( $local_archive ) );

							Snapshot_Helper_Utility::file_output_stream_chunked( $local_archive );
							flush();
							die;

						} elseif ( $remote_archive ) {

							/* Otherwise, redirect to the remotely-hosted archive */
							wp_redirect( esc_url_raw( $remote_archive ) );
							die;
						}

						break;

					case 'download-archive':
					case 'download-log':

						if ( ( isset( $_GET['snapshot-item'] ) ) && ( isset( $_GET['snapshot-data-item'] ) ) ) {
							$item_key = intval( $_GET['snapshot-item'] );
							if ( isset( $this->config_data['items'][ $item_key ] ) ) {
								$item = $this->config_data['items'][ $item_key ];

								$data_item_key = intval( $_GET['snapshot-data-item'] );
								if ( isset( $item['data'][ $data_item_key ] ) ) {

									$data_item = $item['data'][ $data_item_key ];

									if ( 'download-archive' === $snapshot_action ) {

										if ( isset( $data_item['filename'] ) ) {

											if ( ( empty( $data_item['destination'] ) ) || ( "local" === $data_item['destination'] ) ) {

												$current_backupFolder = $this->snapshot_get_item_destination_path( $item, $data_item );

											} else {
												$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
											}
											if ( empty( $current_backupFolder ) ) {
												$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
											}

											$current_backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
											if ( file_exists( $current_backupFile ) ) {

												header( 'Content-Description: Snapshot Archive File' );
												header( 'Content-Type: application/zip' );
												header( 'Content-Disposition: attachment; filename=' . $data_item['filename'] );
												header( 'Content-Transfer-Encoding: binary' );
												header( 'Expires: 0' );
												header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
												header( 'Pragma: public' );
												header( 'Content-Length: ' . filesize( $current_backupFile ) );

												Snapshot_Helper_Utility::file_output_stream_chunked( $current_backupFile );
												flush();
												die();
											}
										}
									} else if ( 'download-log' === $snapshot_action ) {

										$backupLogFileFull = trailingslashit( $this->get_setting( 'backupLogFolderFull' ) )
										                     . $item['timestamp'] . "_" . $data_item['timestamp'] . ".log";

										if ( file_exists( $backupLogFileFull ) ) {

											header( 'Content-Description: Snapshot Log File' );
											header( 'Content-Type: application/text' );
											header( 'Content-Disposition: attachment; filename=' . basename( $backupLogFileFull ) );
											header( 'Content-Transfer-Encoding: text' );
											header( 'Expires: 0' );
											header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
											header( 'Pragma: public' );
											header( 'Content-Length: ' . filesize( $backupLogFileFull ) );

											Snapshot_Helper_Utility::file_output_stream_chunked( $backupLogFileFull );
											flush();
											die();
										}

									}
								}
							}
						}
						$ACTION_FOUND = false;

						break;

					case 'item-archives':

						$CONFIG_CHANGED = false;

						$item_key = intval( $_GET['item'] );
						if ( isset( $this->config_data['items'][ $item_key ] ) ) {
							$item = $this->config_data['items'][ $item_key ];

							$action = '';
							if ( ( isset( $_GET['action'] ) ) && ( "-1" !== $_GET['action'] ) ) {
								$action = sanitize_text_field( $_GET['action'] );
							} else if ( ( isset( $_GET['action2'] ) ) && ( "-1" !== $_GET['action2'] ) ) {
								$action = sanitize_text_field( $_GET['action2'] );
							}

							//echo "action=[". $action ."]<br />";
							switch ( $action ) {
								case 'resend':

									if ( "mirror" === $item['destination-sync'] ) {
										$snapshot_sync_files_option = 'wpmudev_snapshot_sync_files_' . $item['timestamp'];
										delete_option( $snapshot_sync_files_option );

									} else {
										$resend_data_items = intval( $_REQUEST['data-item'] );
										if ( ! is_array( $resend_data_items ) ) {
											$resend_data_items = array( $resend_data_items );
										}

										foreach ( $resend_data_items as $data_item_key ) {
											if ( ! isset( $item['data'][ $data_item_key ] ) ) {
												continue;
											}

											$data_item = $item['data'][ $data_item_key ];

											if ( isset( $data_item['filename'] ) ) {
												$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
												$current_backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];

												if ( ! file_exists( $current_backupFile ) ) {
													continue;
												}

												$data_item['destination-status'] = array();
											}
											$this->config_data['items'][ $item_key ]['data'][ $data_item_key ] = $data_item;
											$CONFIG_CHANGED = true;
										}
									}
									break;

								case 'delete':
									$delete_data_items = $_REQUEST['data-item'];
									if ( ! is_array( $delete_data_items ) ) {
										$delete_data_items = array( $delete_data_items );
									}

									foreach ( $delete_data_items as $data_item_key ) {
										$data_item_key = intval( $data_item_key );
										if ( ! isset( $item['data'][ $data_item_key ] ) ) {
											continue;
										}

										$data_item = $item['data'][ $data_item_key ];

										// Delete the archive file
										if ( isset( $data_item['filename'] ) ) {
											$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
											$current_backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
											if ( file_exists( $current_backupFile ) ) {
												unlink( $current_backupFile );
											}
										}

										// Delete the log file
										$backupLogFileFull = trailingslashit( $this->get_setting( 'backupLogFolderFull' ) )
										                     . $item['timestamp'] . "_" . $data_item['timestamp'] . ".log";
										if ( file_exists( $backupLogFileFull ) ) {
											unlink( $backupLogFileFull );
										}

										// Delete the data_item itself
										unset( $this->config_data['items'][ $item_key ]['data'][ $data_item_key ] );
										$CONFIG_CHANGED = true;
									}
									break;
							}
						}
						if ( true === $CONFIG_CHANGED ) {
							$this->save_config();
						}

						$per_page = 20;

						if ( ( isset( $_POST['wp_screen_options']['option'] ) )
						     && ( "toplevel_page_snapshots_edit_panel_network_per_page" === $_POST['wp_screen_options']['option'] )
						) {

							if ( isset( $_POST['wp_screen_options']['value'] ) ) {
								$per_page = intval( $_POST['wp_screen_options']['value'] );
								if ( ( ! $per_page ) || ( $per_page < 1 ) ) {
									$per_page = 20;
								}
								update_user_meta( get_current_user_id(), 'snapshot_data_items_per_page', $per_page );
							}
						}
						//$this->archives_data_items_table = new Snapshot_Archives_Data_Items_Table( $this );
						add_screen_option(
							'per_page', array(
								'label' => __( 'per Page', SNAPSHOT_I18N_DOMAIN ),
								'default' => $per_page,
							)
						);

						$ACTION_FOUND = true;

						break;

					/*
										case 'archives-import':
											if ( empty($_POST) || !wp_verify_nonce($_POST['snapshot-noonce-field'],'snapshot-settings') )
												die();
											else {
												$this->snapshot_archives_import_proc();
												$ACTION_FOUND = true;
											}

											break;
					*/
					default:
						break;
				}
			}

			if ( ! $ACTION_FOUND ) {
				$per_page = 20;

				if ( ( isset( $_POST['wp_screen_options']['option'] ) )
				     && ( "toplevel_page_snapshots_edit_panel_network_per_page" === $_POST['wp_screen_options']['option'] )
				) {

					if ( isset( $_POST['wp_screen_options']['value'] ) ) {
						$per_page = intval( $_POST['wp_screen_options']['value'] );
						if ( ( ! $per_page ) || ( $per_page < 1 ) ) {
							$per_page = 20;
						}
						update_user_meta( get_current_user_id(), 'snapshot_items_per_page', $per_page );
					}
				}

				add_screen_option(
					'per_page', array(
						'label' => __( 'per Page', SNAPSHOT_I18N_DOMAIN ),
						'default' => $per_page,
					)
				);

			}
		}

		/**
		 * Plugin main action processing function. Will filter the destination action called then
		 * pass on to other sub-functions
		 *
		 * @since 1.0.2
		 * @uses $_REQUEST global PHP object
		 *
		 * @param none
		 *
		 * @return void
		 */

		public function process_snapshot_destination_actions() {

			//if (!is_super_admin()) return;
			if ( is_multisite() ) {
				if ( ! is_super_admin() ) {
					return;
				}
			} else {
				if ( ! current_user_can( 'manage_snapshots_items' ) ) {
					return;
				}
			}

			if ( isset( $_REQUEST['snapshot-action'] ) ) {

				if ( ! isset( $_REQUEST['destination-noonce-field']  ) ) {
					return;
				}
				if ( ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ) {
					return;
				}

				switch ( sanitize_text_field( $_REQUEST['snapshot-action'] ) ) {

					case 'delete-bulk':
						if ( ( ! isset( $_POST['snapshot-destination-type'] ) ) || ( empty( $_POST['snapshot-destination-type'] ) ) ) {
							return;
						}

						$destination_type = sanitize_text_field( $_POST['snapshot-destination-type'] );
						if ( empty( $_POST[ 'snapshot-noonce-field-' . $destination_type ] ) ) {
							return;
						}

						if ( ! wp_verify_nonce( $_POST[ 'snapshot-noonce-field-' . $destination_type ], 'snapshot-delete-destination-bulk-' . $destination_type ) ) {
							return;
						} else {
							$this->snapshot_delete_bulk_destination_proc();
						}
						break;

					case 'delete':
						if ( empty( $_GET ) || ! wp_verify_nonce( $_GET['destination-noonce-field'], 'snapshot-destination' ) ) {
							return;
						} else {
							$this->snapshot_delete_destination_proc();
						}

						break;

					case 'edit':

						/* Ensure the required query variables are set */
						if ( ! isset( $_REQUEST['type'], $_REQUEST['item'], $_GET['state'], $_GET['code'] ) ) {
							break;
						}

						/* Ensure that Google are sending us an auth code and that this is a Google Drive destination */
						if ( 'token' !== $_GET['state'] || 'google-drive' !== $_REQUEST['type'] || ! $_GET['code'] ) {
							break;
						}

						/* Ensure the destination being edited exists */
						$item = $_REQUEST['item'];
						if ( ! isset( $this->config_data['destinations'][ $item ] ) ) {
							break;
						}

						/** @var SnapshotDestinationGoogleDrive $drive */
						$drive = $this->_settings['destinationClasses']['google-drive'];

						$auth_error = false;
						$drive->init();
						$drive->load_class_destination( $this->config_data['destinations'][ $item ] );
						$drive->login();

						if ( is_object( $drive->client ) ) {

							try {
								$drive->client->authenticate( $_GET['code'] );

							} catch ( Google_0814_Auth_Exception $e ) {
								$auth_error = true;
							}

							if ( ! $auth_error ) {
								$this->config_data['destinations'][ $item ]['access_token'] = $drive->client->getAccessToken();
								$this->save_config();
							}
						}

						break;

					case 'update':

						if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-update-destination' ) ) {
							return;
						} else {
							if ( isset( $_POST['snapshot-destination']['type'] ) && 'local' === $_POST['snapshot-destination']['type'] ) {
								$this->snapshot_settings_config_update();
							} else {
								$this->snapshot_update_destination_proc();
							}
						}

						break;

					case 'add':
						if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-add-destination' ) ) {
							return;
						} else {
							$this->snapshot_add_destination_proc();
						}

						break;

					default:
						break;
				}
			}
		}

		/**
		 * Processing 'delete' action from form post to delete a select Snapshot.
		 * Called from $this->snapshot_process_actions()
		 *
		 * @since 1.0.0
		 * @uses $_REQUEST['delete']
		 * @uses $this->config_data['items']
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_delete_bulk_action_proc() {
			// We have checked nonces coming into the function.
			// phpcs:ignore
			if ( ! isset( $_POST['action'] ) || ( isset( $_POST['action'] ) && 'delete' !== $_POST['action'] ) ) {
				return;
			}
			$ref = getenv( "HTTP_REFERER" );
			$parts = wp_parse_url( $ref );
			parse_str( $parts['query'], $query );
			// phpcs:ignore
			if ( ! isset( $_REQUEST['delete-bulk'] ) ) {
				wp_redirect( $ref );
				die();
			}

			$page = ( isset( $query['page'] ) && ! empty( $query['page'] ) ) ? $query['page'] : 'snapshots_edit_panel';

			$CONFIG_CHANGED = false;

			if ( 'snapshot_pro_managed_backups' === $page ) {
				$model = new Snapshot_Model_Full_Backup();

				if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
					die;
				}
				// Only some users can restore
				$status = false;

				// phpcs:ignore
				foreach ( $_REQUEST['delete-bulk'] as $snapshot_key ) {
					if ( $model->delete_backup( $snapshot_key ) ) {
						$CONFIG_CHANGED = true;
					}
				}

				if ( $CONFIG_CHANGED ) {
					// Update all settings, new list included
					$model->update_remote_schedule();
				}

			} else {
				// phpcs:ignore
				foreach ( $_REQUEST['delete-bulk'] as $snapshot_key ) {
					if ( $this->snapshot_delete_item_action_proc( $snapshot_key, true ) ) {
						$CONFIG_CHANGED = true;
					}
				}
			}

			if ( $CONFIG_CHANGED ) {
				$this->save_config();

				$location = esc_url_raw( add_query_arg( 'message', 'success-delete-bulk', $this->_settings['SNAPSHOT_MENU_URL'] . $page ) );
				if ( $location ) {
					wp_redirect( $location );
					die();
				}
			}

			wp_redirect( $this->_settings['SNAPSHOT_MENU_URL'] . $page );
			die();
		}

		/**
		 * Processing 'delete-item' action from form post to delete a select Snapshot.
		 * Called from $this->snapshot_process_actions()
		 *
		 * @since 1.0.0
		 * @uses $_REQUEST['delete']
		 * @uses $this->config_data['items']
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_delete_item_action_proc( $snapshot_item_key = 0, $DEFER_LOG_UPDATE = false ) {

			$CONFIG_CHANGED = false;
			$ref = getenv( "HTTP_REFERER" );
			$parts = wp_parse_url( $ref );
			parse_str( $parts['query'], $query );
			if ( ! $snapshot_item_key ) {
				// We have checked nonces coming into the function.
				// phpcs:ignore
				if ( isset( $_REQUEST['item'] ) ) {
					$snapshot_item_key = intval( $_REQUEST['item'] );
				}
			}

			if ( array_key_exists( $snapshot_item_key, $this->config_data['items'] ) ) {

				$item = $this->config_data['items'][ $snapshot_item_key ];
				if ( isset( $item['data'] ) ) {
					foreach ( $item['data'] as $item_data_key => $item_data ) {

						if ( isset( $item_data['filename'] ) ) {
							$backupFile = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . $item_data['filename'];

							if ( is_writable( $backupFile ) ) {
								unlink( $backupFile );
							}
						}

						if ( isset( $item_data['timestamp'] ) ) {
							$backupLogFileFull = trailingslashit( $this->_settings['backupLogFolderFull'] ) . $snapshot_item_key . "_" . $item_data['timestamp'] . ".log";
							if ( is_writable( $backupLogFileFull ) ) {
								unlink( $backupLogFileFull );
							}
						}
					}
				}

				$backupLogFileFull = trailingslashit( $this->_settings['backupLogFolderFull'] ) . $snapshot_item_key . "_backup.log";
				if ( is_writable( $backupLogFileFull ) ) {
					unlink( $backupLogFileFull );
				}

				$backupLogFileFull = trailingslashit( $this->_settings['backupLogFolderFull'] ) . $snapshot_item_key . "_restore.log";
				if ( is_writable( $backupLogFileFull ) ) {
					unlink( $backupLogFileFull );
				}

				$backupLockFileFull = trailingslashit( $this->_settings['backupLockFolderFull'] ) . $snapshot_item_key . ".lock";
				if ( is_writable( $backupLockFileFull ) ) {
					unlink( $backupLockFileFull );
				}

				// Note we don't check the interval because we shouldn't need to. Just unschdule the event.
				$timestamp = wp_next_scheduled( $this->_settings['backup_cron_hook'], array( intval( $snapshot_item_key ) ) );
				if ( $timestamp ) {
					wp_unschedule_event( $timestamp, $this->_settings['backup_cron_hook'], array( intval( $snapshot_item_key ) ) );
				}
				unset( $this->config_data['items'][ $snapshot_item_key ] );
				$CONFIG_CHANGED = true;
			}

			if ( ! $DEFER_LOG_UPDATE ) {

				$page = ( isset( $query['page'] ) && ! empty( $query['page'] ) ) ? $query['page'] : 'snapshots_edit_panel';

				if ( $CONFIG_CHANGED ) {
					$this->save_config();

					$location = esc_url_raw( add_query_arg( 'message', 'success-delete', $this->_settings['SNAPSHOT_MENU_URL'] . $page ) );
					if ( $location ) {
						wp_redirect( $location );
						die();
					}
				}

				wp_redirect( $this->_settings['SNAPSHOT_MENU_URL'] . $page );
				die();

			} else {

				return $CONFIG_CHANGED;
			}
		}

		public function snapshot_item_run_immediate( $item_key ) {
			wp_remote_post(
                 get_option( 'siteurl' ) . '/wp-cron.php',
				array(
					'timeout' => 3,
					'blocking' => false,
					'sslverify' => false,
					'body' => array(
						'nonce' => wp_create_nonce( 'WPMUDEVSnapshot' ),
						'type' => 'start',
					),
					'user-agent' => 'WPMUDEVSnapshot',
				)
			);
			wp_schedule_single_event( time(), $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
		}

		/**
		 * Processing 'add' action from form post to create a new Snapshot.
		 * Called from $this->snapshot_process_actions()
		 *
		 * @since 1.0.0
		 * @uses $_REQUEST['add']
		 *
		 * @return void
		 */
		public function snapshot_add_update_action_proc( $_post_array ) {
			$CONFIG_CHANGED = false;

			if ( 'add' === $_post_array['snapshot-action'] ) {
				$item = array();

				$item['timestamp'] = isset( $_post_array['snapshot-item'] ) ? intval( $_post_array['snapshot-item'] ) : time();
				$item['blog-id'] = isset( $_post_array['snapshot-blog-id'] ) ? intval( $_post_array['snapshot-blog-id'] ) : 0;

			} else if ( "update" === $_post_array['snapshot-action'] ) {
				$item_key = intval( $_post_array['snapshot-item'] );
				if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
					die;
				}

				$item = $this->config_data['items'][ $item_key ];

				if ( ! $item['blog-id'] && isset( $item['IMPORT'], $_post_array['snapshot-blog-id'] ) ) {
					$item['blog-id'] = intval( $_post_array['snapshot-blog-id'] );
				}
			}

			if ( isset( $_post_array['snapshot-name'] ) ) {
				$item['name'] = sanitize_text_field( $_post_array['snapshot-name'] );
			} else {
				$item['name'] = "";
			}

			if ( ! isset( $_post_array['snapshot-destination-directory'] ) ) {
				$_post_array['snapshot-destination-directory'] = '';
			}

			$item['notes'] = isset( $_post_array['snapshot-notes'] ) ? esc_textarea( $_post_array['snapshot-notes'] ) : '';
			$item['store-local'] = isset( $_post_array['snapshot-store-local'] ) ? sanitize_text_field( $_post_array['snapshot-store-local'] ) : '1';

			$current_user = wp_get_current_user();
			if ( isset( $current_user->ID ) && intval( $current_user->ID ) ) {
				$item['user'] = $current_user->ID;
			} else {
				$item['user'] = 0;
			}

			$item['tables-option'] = "none";
			$item['tables-sections'] = array();
			$item['tables-count'] = 0;

			if ( isset( $_post_array['snapshot-tables-option'] ) ) {

				$item['tables-option'] = $_post_array['snapshot-tables-option'];
				if ( "none" === $item['tables-option'] ) {
					assert(true); // Nothing to see here.
				} else if ( "all" === $item['tables-option'] ) {
					assert(true); // Nothing to see here.
				} else if ( "selected" === $item['tables-option'] ) {

					// The form submit when not immediate will be this form element.
					if ( isset( $_post_array['snapshot-tables'] ) ) {
						$snapshot_tables_array = array();
						foreach ( $_post_array['snapshot-tables'] as $table_section => $table_set ) {
							$snapshot_tables_array = array_merge( $snapshot_tables_array, $table_set );
						}
						$_post_array['snapshot-tables-array'] = $snapshot_tables_array;
					}

					// snapshot-tables-array will either be populated from above OR from the AJAX form processing
					if ( isset( $_post_array['snapshot-tables-array'] ) ) {

						$item['tables-sections'] = array();

						$tables_sections = Snapshot_Helper_Utility::get_database_tables( $item['blog-id'] );

						if ( $tables_sections ) {
							foreach ( $tables_sections as $section => $tables ) {
								if ( count( $tables ) ) {
									$item['tables-sections'][ $section ] = array_intersect( $tables, $_post_array['snapshot-tables-array'] );
								} else {
									$item['tables-sections'][ $section ] = array();
								}
							}
						}

					} else if ( isset( $_post_array['snapshot-tables-sections'] ) ) {

						$item['tables-sections'] = $_post_array['snapshot-tables-sections'];
					}
				}
			}

			$item['files-option'] = "none";
			$item['files-sections'] = array();
			$item['files-ignore'] = array();
			$item['files-count'] = 0;

			if ( isset( $_post_array['snapshot-files-option'] ) ) {

				$item['files-option'] = $_post_array['snapshot-files-option'];
				if ( 'none' === $item['files-option'] ) {
					assert(true); // Nothing to see here.
				} else if ( 'all' === $item['files-option'] ) {
					if ( is_main_site( $item['blog-id'] ) ) {
						$item['files-sections'] = array( 'themes', 'plugins', 'media' );
						if ( is_multisite() ) {
							$files_sections[] = 'mu-plugins';
						}
					} else {
						$item['files-sections'] = array( 'media' );
					}
				} else if ( 'selected' === $item['files-option'] ) {

					if ( is_main_site( $item['blog-id'] ) ) {
						if ( isset( $_post_array['snapshot-files-sections'] ) ) {
							$item['files-sections'] = $_post_array['snapshot-files-sections'];
						} else {
							$item['files-sections'] = array( 'themes', 'plugins', 'media' );
							if ( is_multisite() ) {
								$item['files-sections'][] = 'mu-plugins';
							}
						}
					} else {
						if ( isset( $_post_array['snapshot-files-sections'] ) ) {
							$item['files-sections'] = $_post_array['snapshot-files-sections'];
						} else {
							$item['files-sections'] = array( 'media' );
						}
					}
				}

				if ( ( isset( $_post_array['snapshot-files-ignore'] ) ) && ( strlen( $_post_array['snapshot-files-ignore'] ) ) ) {
					$files_ignore = explode( "\n", $_post_array['snapshot-files-ignore'] );
					if ( ( is_array( $files_ignore ) ) && ( count( $files_ignore ) ) ) {
						foreach ( $files_ignore as $file_ignore ) {
							$file_ignore = esc_attr( strip_tags( trim( $file_ignore ) ) );
							if ( ! empty( $file_ignore ) ) {
								$item['files-ignore'][] = $file_ignore;
							}
						}
					}
				}
			}

			$timestamp = wp_next_scheduled( $this->_settings['backup_cron_hook'], array( intval( $item['timestamp'] ) ) );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $this->_settings['backup_cron_hook'], array( intval( $item['timestamp'] ) ) );
			}

			$item['interval'] = '';
			$item['interval-offset'] = array();

			if ( isset( $_post_array['snapshot-interval'] ) ) {
				$item['interval'] = sanitize_text_field( $_post_array['snapshot-interval'] );

				switch ( $item['interval'] ) {

					case 'snapshot-5minutes':
						break;

					case 'snapshot-hourly':
						$item['interval-offset']['snapshot-hourly'] = $_post_array['snapshot-interval-offset']['snapshot-hourly'];
						break;

					case 'snapshot-daily':
					case 'snapshot-twicedaily':
						$item['interval-offset']['snapshot-daily'] = $_post_array['snapshot-interval-offset']['snapshot-daily'];
						break;

					case 'snapshot-weekly':
					case 'snapshot-twiceweekly':
						$item['interval-offset']['snapshot-weekly'] = $_post_array['snapshot-interval-offset']['snapshot-weekly'];
						break;

					case 'snapshot-monthly':
					case 'snapshot-twicemonthly':
						$item['interval-offset']['snapshot-monthly'] = $_post_array['snapshot-interval-offset']['snapshot-monthly'];
						break;

				}
			}

			if ( empty( $_post_array['snapshot-destination'] ) ) {
				$_post_array['snapshot-destination'] = 'local';
			} else {
				$_post_array['snapshot-destination'] = sanitize_text_field( $_post_array['snapshot-destination'] );
			}

			// If the form destination is empty then we are storing locally. So check the destination-directory
			// value and move the local file to that location
			if ( "local" === $_post_array['snapshot-destination'] ) {

				$item_tmp = array();
				$item_tmp['destination'] = sanitize_text_field( $_post_array['snapshot-destination'] );
				$item_tmp['blog-id'] = $item['blog-id'];
				$item_tmp['timestamp'] = intval( $_post_array['snapshot-item'] );
				$item_tmp['destination-directory'] = sanitize_text_field( trim( $_post_array['snapshot-destination-directory'] ) );
				//echo "item_tmp<pre>"; print_r($item_tmp); echo "</pre>";

				$new_backupFolder = $this->snapshot_get_item_destination_path( $item_tmp );
				if ( ! strlen( $new_backupFolder ) ) {
					$new_backupFolder = $this->_settings['backupBaseFolderFull'];
				}

				if ( ( isset( $item['data'] ) ) && ( count( $item['data'] ) ) ) {

					foreach ( $item['data'] as $data_item_idx => $data_item ) {

						if ( ( ! isset( $data_item['destination'] ) ) || ( $item_tmp['destination'] !== $data_item['destination'] ) ) {
							continue;
						}

						if ( ! isset( $data_item['destination-directory'] ) ) {
							$data_item['destination-directory'] = '';
						}

						if ( $data_item['destination-directory'] !== $item_tmp['destination-directory'] ) {
							$current_backupFolder = $this->snapshot_get_item_destination_path( $item_tmp, $data_item, false );
							if ( empty( $current_backupFolder ) ) {
								$current_backupFolder = $this->_settings['backupBaseFolderFull'];
							}

							// If destination is empty then this is a local file.
							if ( empty( $item['destination'] ) ) {
								$currentFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
								$newFile = trailingslashit( $new_backupFolder ) . $data_item['filename'];

								if ( ( file_exists( $currentFile ) ) && ( ! file_exists( $newFile ) ) ) {
									$rename_ret = rename( $currentFile, $newFile );
									if ( true === $rename_ret ) {
										$item['data'][ $data_item_idx ]['destination-directory'] = $item_tmp['destination-directory'];
									}
								}
							} else {
								// Else we just set the directoy of the remote destination item. It is up to the user
								// to update/move the remote files to the new path.
								$item['data'][ $data_item_idx ]['destination-directory'] = $item_tmp['destination-directory'];
							}
						}
					}
				}

				$item['destination-directory'] = sanitize_text_field( trim( $_post_array['snapshot-destination-directory'] ) );
				$item['destination'] = sanitize_text_field( $_post_array['snapshot-destination'] );
				$item['destination-sync'] = 'archive';

			} else {

				$item_tmp = array();
				$item_tmp['destination'] = sanitize_text_field( $_post_array['snapshot-destination'] );

				if ( isset( $_post_array['snapshot-blog-id'] ) ) {
					$item_tmp['blog-id'] = intval( $_post_array['snapshot-blog-id'] );
				}

				$item_tmp['timestamp'] = intval( $_post_array['snapshot-item'] );
				$item_tmp['destination-directory'] = "";

				$new_backupFolder = $this->_settings['backupBaseFolderFull'];

				if ( ( isset( $item['data'] ) ) && ( count( $item['data'] ) ) ) {

					foreach ( $item['data'] as $data_item_idx => $data_item ) {

						//if ($data_item['destination'] != $item_tmp['destination'])
						//	continue;

						if ( ! isset( $data_item['destination-directory'] ) ) {
							$data_item['destination-directory'] = '';
						}

						if ( $data_item['destination-directory'] !== $item_tmp['destination-directory'] ) {
							$current_backupFolder = $this->snapshot_get_item_destination_path( $item_tmp, $data_item, false );
							if ( empty( $current_backupFolder ) ) {
								$current_backupFolder = $this->_settings['backupBaseFolderFull'];
							}

							// If destination is empty then this is a local file.
							if ( ( $item['destination'] ) || ( "local" === $item['destination'] ) ) {
								$currentFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
								$newFile = trailingslashit( $new_backupFolder ) . $data_item['filename'];

								if ( ( file_exists( $currentFile ) ) && ( ! file_exists( $newFile ) ) ) {
									$rename_ret = rename( $currentFile, $newFile );
									if ( true === $rename_ret ) {
										$item['data'][ $data_item_idx ]['destination-directory'] = $item_tmp['destination-directory'];
									}
								}
							} else {
								// Else we just set the directoy of the remote destination item. It is up to the user
								// to update/move the remote files to the new path.
								$item['data'][ $data_item_idx ]['destination-directory'] = $item_tmp['destination-directory'];
							}
						}
					}
				}
				$item['destination-directory'] = sanitize_text_field( trim( $_post_array['snapshot-destination-directory'] ) );
				$item['destination'] = sanitize_text_field( $_post_array['snapshot-destination'] );

				$item['destination-sync'] = 'archive';
				$item['destination-sync'] = 'archive';
				if ( isset( $this->config_data['destinations'][ $item['destination'] ] ) ) {
					$destination = $this->config_data['destinations'][ $item['destination'] ];
					if ( ( isset( $destination['type'] ) ) && ( "dropbox" === $destination['type'] ) ) {
						$item['destination-sync'] = sanitize_text_field( $_post_array['snapshot-destination-sync'] );
					}
				}
			}

			// We have checked for nonces coming into the function.
			// phpcs:ignore
			if ( isset( $_POST['snapshot-archive-count'] ) ) {
				$item['archive-count'] = intval( $_post_array['snapshot-archive-count'] );
			} else {
				$item['archive-count'] = 0;
			}

			// phpcs:ignore
			if (!empty($_POST)) {
				$item['clean-remote'] = !empty($_post_array['snapshot-clean-remote']);
			} else {
				$item['clean-remote'] = !empty($item['clean-remote']);
			}

			$item['destination-directory'] = str_replace( '\\', '/', stripslashes( $item['destination-directory'] ) );

			// Saves the selected tables to our config. So next time the user goes to make a snapshot these will be pre-selected.
			// if (count($item['tables-sections']))
			//	$this->config_data['config']['tables_last'][$item['blog-id']] = $item['tables-sections'];

			//$this->config_data['items'][$item['timestamp']] = $item;
			//$this->save_config();
			$this->add_update_config_item( $item['timestamp'], $item );

			//if ($item['interval'] == "immediate") {
			//	$this->snapshot_item_run_immediate($item['timestamp']);
			//}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return $item['timestamp'];
			} else {
				$redirect_url = $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_snapshots';

				$redirect_url = add_query_arg(
					array(
						'snapshot-action' => 'view',
						'item' => $item['timestamp'],
						'message' => 'success-' . ( 'update' === $_post_array['snapshot-action'] ? 'update' : 'add' ),
						'snapshot-noonce-field' => wp_create_nonce( 'snapshot-nonce' ),
					), $redirect_url
				);

				wp_redirect( esc_url_raw( $redirect_url ) );
			}
		}

		/**
		 * Processing 'settings-update' action from form post to to update plugin global settings.
		 * Called from $this->snapshot_process_actions()
		 *
		 * @since 1.0.0
		 * @uses $_REQUEST['backupFolder']
		 * @uses $this->config_data['config']
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_settings_config_update() {
			$CONFIG_CHANGED = false;

			$model = new Snapshot_Model_Full_Backup();

			// We have checked nonces coming into the function.
			// phpcs:ignore
			if ( isset( $_REQUEST['files'] ) ) {

				$files = intval( $_REQUEST['files'] );
				if ( ( $files > 0 ) && ( ! isset( $this->config_data['config']['backupUseFolder'] ) || $files !== $this->config_data['config']['backupUseFolder'] ) ) {
					$this->config_data['config']['backupUseFolder'] = $files;
					$CONFIG_CHANGED = true;
				}
			}

			// phpcs:ignore
			if ( isset( $_REQUEST['secret-key'] ) ) {
				$key = sanitize_text_field( $_REQUEST['secret-key'] );

				if ( $key !== $this->config_data['config']['secret-key'] ) {
					$this->config_data['config']['secret-key'] = $key;
					$CONFIG_CHANGED = true;
				}


				$old_key = $model->get_config( 'secret-key', false );
				$model->set_config( 'secret-key', $key );
				if ( empty( $key ) || $key !== $old_key ) {
					$model->remote()->remove_token();
				}

				// Also stop cron when there's no secret key
				if ( empty( $key ) ) {
					$model->set_config( 'frequency', false );
					$model->set_config( 'schedule_time', false );
					$model->set_config( 'disable_cron', true );
					Snapshot_Controller_Full_Cron::get()->stop();
				}

			}

			//Testing secret-key
			$token = Snapshot_Model_Full_Remote_Api::get()->get_token();

			if ( false === $token ) {
				$this->config_data['config']['secret-key'] = '';
				$model->set_config( 'secret-key', '' );
				$model->remote()->remove_token();

				$model->set_config( 'frequency', false );
				$model->set_config( 'schedule_time', false );
				$model->set_config( 'disable_cron', true );
				Snapshot_Controller_Full_Cron::get()->stop();

			}

			// phpcs:ignore
			$backupFolderRequest = isset( $_REQUEST['backupFolder'] ) ? $_REQUEST['backupFolder'] : 'snapshot';
			// phpcs:ignore
			if ( isset( $_REQUEST['files'] ) && 1 === $files ) {
				$backupFolderRequest = 'snapshot';
			}
			if ( isset( $backupFolderRequest ) ) {

				$_oldbackupFolderFull = trailingslashit( sanitize_text_field( $this->_settings['backupBaseFolderFull'] ) );

				// Because this needs to be universal we convert Windows paths entered be the user into proper PHP forward slash '/'
				$backupFolderRequest = str_replace( '\\', '/', stripslashes( sanitize_text_field( $backupFolderRequest ) ) );

				if ( '/' === ( substr( $backupFolderRequest, 0, 1 ) )
				     || ':/' === ( substr( $backupFolderRequest, 1, 2 ) )
				) {
					// Setting Absolute path!

					$backupFolder = sanitize_text_field( $backupFolderRequest );
					$_newbackupFolderFull = $backupFolder;
				} else {
					$this->config_data['config']['absoluteFolder'] = false;

					$backupFolder = esc_attr( basename( untrailingslashit( $backupFolderRequest ) ) );

					$wp_upload_dir = wp_upload_dir();
					$wp_upload_dir['basedir'] = str_replace( '\\', '/', $wp_upload_dir['basedir'] );
					$_newbackupFolderFull = trailingslashit( $wp_upload_dir['basedir'] ) . $backupFolder;

					if ( file_exists( $_newbackupFolderFull ) && $backupFolderRequest !== $this->config_data['config']['backupFolder'] ) {
						/* If here we cannot create the folder. So report this via the admin header message and return */
						$this->_admin_header_error .= __( 'ERROR: The new Snapshot folder already exists. ', SNAPSHOT_I18N_DOMAIN );
						$this->_admin_header_error .= ' ' . $_newbackupFolderFull;

						return;
					}
				}

				if ( ( isset( $backupFolder ) ) && ( strlen( $backupFolder ) ) ) {
					if ( $_oldbackupFolderFull !== $_newbackupFolderFull ) {
						// Start with the assumption we failed moving dirs by default
						$rename_ret = false;
						if ( is_writable( $_oldbackupFolderFull ) ) {
							// If we can reach the old folder, we might still
							// be able to simply rename it
							$rename_ret = rename( $_oldbackupFolderFull, $_newbackupFolderFull );
						} else {
							// Okay, so no old backup folder. Let's just create
							// what we got and inform the user
							$this->_admin_header_error .= __( 'Warning: We were unable to find the old Snapshot folder.', SNAPSHOT_I18N_DOMAIN );
							$this->_admin_header_error .= ' ' . $_newbackupFolderFull;
							$rename_ret = true; // This will get picked up by the next condition...
							// ... and we will just create it via
							// the `$this->set_backup_folder()` call automatically
						}

						// Alright now... so, are we good to go?
						if ( true === $rename_ret ) {
							$CONFIG_CHANGED = true;

							// Now that the physical files have been changed update our settings.
							$this->config_data['config']['backupFolder'] = $backupFolder;
							$this->set_backup_folder();
							$this->set_log_folders();
						}
					}
				}
			}

			// phpcs:ignore
			if ( isset( $_REQUEST['segmentSize'] ) ) {

				$segmentSize = intval( $_REQUEST['segmentSize'] );
				if ( ( $segmentSize > 0 ) && ( $segmentSize !== $this->config_data['config']['segmentSize'] ) ) {
					$this->config_data['config']['segmentSize'] = $segmentSize;
					$CONFIG_CHANGED = true;
				}
			}

			// phpcs:ignore
			if ( ( isset( $_REQUEST['snapshot-sub-action'] ) ) && ( "memoryLimit" === $_REQUEST['snapshot-sub-action'] ) ) {

				// phpcs:ignore
				if ( isset( $_REQUEST['memoryLimit'] ) ) {

					$this->config_data['config']['memoryLimit'] = sanitize_text_field( $_REQUEST['memoryLimit'] );
					$CONFIG_CHANGED = true;
				}
			}

			// phpcs:ignore
			if ( isset( $_REQUEST['filesIgnore'] ) ) {

				// phpcs:ignore
				$files_ignore = explode( "\n", $_REQUEST['filesIgnore'] );
				if ( ( is_array( $files_ignore ) ) && ( count( $files_ignore ) ) ) {
					foreach ( $files_ignore as $idx => $file_ignore ) {
						$file_ignore = sanitize_text_field( trim( $file_ignore ) );
						if ( ! empty( $file_ignore ) ) {
							$files_ignore[ $idx ] = $file_ignore;
						}
					}

					$this->config_data['config']['filesIgnore'] = $files_ignore;
					$CONFIG_CHANGED = true;
				}
			}

			// phpcs:ignore
			if ( ( isset( $_REQUEST['errorReporting'] ) ) && ( count( $_REQUEST['errorReporting'] ) ) ) {
				// phpcs:ignore
				$this->config_data['config']['errorReporting'] = $_REQUEST['errorReporting'];
				$CONFIG_CHANGED = true;
			}

			// phpcs:ignore
			if ( ( isset( $_REQUEST['zipLibrary'] ) ) && ( "zipLibrary" === $_REQUEST['snapshot-sub-action'] ) ) {

				// phpcs:ignore
				if ( isset( $_REQUEST['zipLibrary'] ) ) {
					$this->config_data['config']['zipLibrary'] = sanitize_text_field( $_REQUEST['zipLibrary'] );
					$CONFIG_CHANGED = true;
				}
			}

			if ( $CONFIG_CHANGED ) {
				$this->save_config();
			}

			$location = esc_url_raw( add_query_arg( 'message', 'success-settings', $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_settings' ) );
			// phpcs:ignore
			if ( isset( $_POST['snapshot-destination']['type'] ) && 'local' === $_POST['snapshot-destination']['type'] ) {
				$message = 'success-update';
				if ( ! isset( $this->_admin_header_error ) || empty( $this->_admin_header_error ) ) {
					$location = esc_url_raw( add_query_arg( 'message', 'success-update', $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_destinations' ) );
				} else {
					$location = esc_url_raw(
						add_query_arg(
							array(
								'snapshot-action' => 'edit',
								'type' => rawurlencode( $_REQUEST['snapshot-destination']['type'] ), // phpcs:ignore
								'item' => rawurlencode( $_REQUEST['item'] ), // phpcs:ignore
								'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
							), self::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
						)
					);
				}

			}
			if ( $location ) {
				wp_redirect( $location );
				die();
			}
			die();
		}

		/**
		 * Utility function to read our config array from the WordPress options table. This
		 * function also will initialize needed instances of the array if needed.
		 *
		 * @since 1.0.0
		 * @uses $this->_settings
		 * @uses $this->config_data
		 *
		 * @return void
		 */
		public function load_config() {

			global $wpdb;

			if ( is_multisite() ) {
				//$this->config_data = get_blog_option($wpdb->blogid, $this->_settings['options_key']);
				$blog_prefix = $wpdb->get_blog_prefix( $wpdb->blogid );
				// We are using placeholder for non-dynamic data here.
				$row = $wpdb->get_col(
					$wpdb->prepare(
						// phpcs:ignore
						"SELECT option_value FROM {$blog_prefix}options
						WHERE option_name = %s", $this->_settings['options_key']
					)
				);
				if ( $row ) {
					$this->config_data = maybe_unserialize( $row[0] );
				}

			} else {
				//$this->config_data = get_option($this->_settings['options_key']);
				$row = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT option_value FROM $wpdb->options
						WHERE option_name = %s LIMIT 1", $this->_settings['options_key']
					)
				);
				if ( $row ) {
					$this->config_data = maybe_unserialize( $row[0] );
				}
			}

			if ( empty( $this->config_data ) ) {

				$snapshot_legacy_versions = array( '2.0.3', '2.0.2', '2.0.1', '2.0', '1.0.2' );
				foreach ( $snapshot_legacy_versions as $snapshot_legacy_version ) {
					$snapshot_options_key = "snapshot_" . $snapshot_legacy_version;

					if ( is_multisite() ) {
						$this->config_data = get_blog_option( $wpdb->blogid, $snapshot_options_key );
					} else {
						$this->config_data = get_option( $snapshot_options_key );
					}

					if ( ! empty( $this->config_data ) ) {
						$this->config_data['version'] = $snapshot_legacy_version;
						break;
					}
				}
			}

			if ( ! isset( $this->config_data['items'] ) ) {
				$this->config_data['items'] = array();
			} else {
				krsort( $this->config_data['items'] );
			} /* If we do have items sort them here instead of later. */

			if ( ! isset( $this->config_data['config'] ) ) {
				$this->config_data['config'] = array();
			}

			if ( ! isset( $this->config_data['config']['segmentSize'] ) ) {
				$this->config_data['config']['segmentSize'] = 1000;
			}

			if ( $this->config_data['config']['segmentSize'] < 1 ) {
				$this->config_data['config']['segmentSize'] = 1000;
			}

			//if ( ( ! isset( $this->config_data['config']['memoryLimit'] ) ) || ( empty( $this->config_data['config']['memoryLimit'] ) ) ) {

			$memory_limits = array();
			$memory_limit = ini_get( 'memory_limit' );
			$memory_limits[ $memory_limit ] = Snapshot_Helper_Utility::size_unformat( $memory_limit );

			$memory_limit = WP_MEMORY_LIMIT;
			$memory_limits[ $memory_limit ] = Snapshot_Helper_Utility::size_unformat( $memory_limit );

			$memory_limit = WP_MAX_MEMORY_LIMIT;
			$memory_limits[ $memory_limit ] = Snapshot_Helper_Utility::size_unformat( $memory_limit );

			arsort( $memory_limits );
			foreach ( $memory_limits as $memory_key => $memory_value ) {
				$this->config_data['config']['memoryLimit'] = $memory_key;
				break;
			}
			//}

			if ( ! isset( $this->config_data['config']['errorReporting'] ) ) {
				$this->config_data['config']['errorReporting'] = array();
				$this->config_data['config']['errorReporting'][ E_ERROR ] = array();
				$this->config_data['config']['errorReporting'][ E_ERROR ]['stop'] = true;
				$this->config_data['config']['errorReporting'][ E_ERROR ]['log'] = true;

				$this->config_data['config']['errorReporting'][ E_WARNING ] = array();
				$this->config_data['config']['errorReporting'][ E_WARNING ]['log'] = true;

				$this->config_data['config']['errorReporting'][ E_NOTICE ] = array();
				$this->config_data['config']['errorReporting'][ E_NOTICE ]['log'] = true;
			}

			if ( ! isset( $this->config_data['config']['zipLibrary'] ) ) {
				$this->config_data['config']['zipLibrary'] = 'ZipArchive';
			}
			// Let's see if we should attempt forcing the ZIP library
			if ( defined( 'SNAPSHOT_FORCE_ZIP_LIBRARY' ) && SNAPSHOT_FORCE_ZIP_LIBRARY ) {
				if ( 'pclzip' === SNAPSHOT_FORCE_ZIP_LIBRARY ) {
					$this->config_data['config']['zipLibrary'] = 'PclZip';
				} else {
					$this->config_data['config']['zipLibrary'] = 'ZipArchive';
				}
			}
			if ( ( 'ZipArchive' === $this->config_data['config']['zipLibrary'] ) && ( ! class_exists( 'ZipArchive' ) ) ) {
				$this->config_data['config']['zipLibrary'] = 'PclZip';
			}

			if ( ! isset( $this->config_data['config']['absoluteFolder'] ) ) {
				$this->config_data['config']['absoluteFolder'] = false;
			}

			if ( ( ! isset( $this->config_data['config']['backupFolder'] ) ) || ( ! strlen( $this->config_data['config']['backupFolder'] ) ) ) {
				$this->config_data['config']['backupFolder'] = "snapshots";
			}

			// Container for Destinations S3, FTP, etc.
			if ( ! isset( $this->config_data['destinations'] ) ) {
				$this->config_data['destinations'] = array();
			}

			if ( ! isset( $this->config_data['destinations']['local'] ) ) {
				$this->config_data['destinations']['local'] = array(
					'name' => __( 'Local Snapshot', SNAPSHOT_I18N_DOMAIN ),
					'type' => 'local',
				);
			}

			/* Set the default table to be part of the snapshot */
			if ( ! isset( $this->config_data['config']['tables_last'] ) ) {
				$this->config_data['config']['tables_last'] = array();
			}

			// Remove the activity section. No longer used.
			if ( isset( $this->config_data['activity'] ) ) {
				unset( $this->config_data['activity'] );
			}

			// The tables needs to be converted. In earlier versions of this plugin the table array was not aware of the blog_id.
			// We need to keep a set for each blog_id. So assume the current version is for the current blog.
			if ( isset( $this->config_data['config']['tables_last'][0] ) ) {
				$tables_last = $this->config_data['config']['tables_last'];
				unset( $this->config_data['config']['tables_last'] );
				$this->config_data['config']['tables_last'] = array();
				$this->config_data['config']['tables_last'][ $wpdb->blogid ] = $tables_last;
			}

			// If we don't have the 'version' config then assume it is the previous version.
			if ( ! isset( $this->config_data['version'] ) ) {
				$this->config_data['version'] = "1.0.2";
			}

			if ( version_compare( $this->config_data['version'], $this->_settings['SNAPSHOT_VERSION'], '<' ) ) {

				//echo "config version<pre>"; print_r($this->config_data['version']); echo "</pre>";
				//echo "plugin version<pre>"; print_r($this->_settings['SNAPSHOT_VERSION']); echo "</pre>";
				//die();

				$this->set_backup_folder();
				$this->set_log_folders();

				// During the conversion we needs to update the manifest.txt file within the archive. Tricky!
				$restoreFolder = trailingslashit( $this->_settings['backupRestoreFolderFull'] ) . "_imports";
				wp_mkdir_p( $restoreFolder );

				/*
					if ($this->config_data['version'] == "1.0.2") {
						foreach($this->config_data['items'] as $item_idx => $item) {

							// We change blog_id to blog-id
							if (!isset($item['blog-id'])) {
								if (isset($item['blog_id'])) {
									$item['blog-id'] = $item['blog_id'];
									unset($item['blog_id']);
								}
							}

							$all_tables_sections = snapshot_utility_get_database_tables($item['blog-id']);

							if (!isset($item['tables-option'])) {
								$item['tables-count'] = '';

								if ($all_tables_sections) {
									$all_tables_option = true;
									foreach($all_tables_sections as $section => $section_tables) {
										if (count($section_tables)) {
											$item['tables-sections'][$section] = array_intersect_key($section_tables, $item['tables']);
											$item['tables-count'] += count($item['tables-sections'][$section]);

											if (count($item['tables-sections'][$section]) != count($section_tables))
												$all_tables_option = false;
										}
										else
											$item['tables_sections'][$section] = array();
									}

									if ($all_tables_option == true) {
										$item['tables-option'] = 'all';
									} else {
										$item['tables-option'] = 'selected';
									}
								}
							}

							$item['files-option'] 	= "none";
							$item['files-sections'] = array();
							$item['files-count']	= 0;

							if (!isset($item['destination']))
								$item['destination'] = 'local';
							if (!isset($item['destination-directory']))
								$item['destination-directory'] = '';

							unset($item['tables']);

							if (!isset($item['interval']))
								$item['interval'] = '';

							if (isset($item['data'])) {
								foreach($item['data'] as $item_data_idx => $item_data) {

									if (!isset($item_data['blog-id'])) {
										if (isset($item_data['blog_id'])) {
											$item_data['blog-id'] = $item_data['blog_id'];
											unset($item_data['blog_id']);
										}
									}

									if (!isset($item_data['destination']))
										$item_data['destination'] = 'local';
									if (!isset($item_data['destination-directory']))
										$item_data['destination-directory'] = '';

									if (!isset($item_data['tables-option'])) {
										$item_data['tables-count'] = '';

										if ($all_tables_sections) {
											$all_tables_option = true;

											foreach($all_tables_sections as $section => $section_tables) {
												if (count($section_tables)) {
													$item_data['tables-sections'][$section] = array_intersect_key($section_tables, $item_data['tables']);
													$item_data['tables-count'] += count($item['tables-sections'][$section]);

													if (count($item_data['tables-sections'][$section]) != count($section_tables))
														$all_tables_option = false;
												}
												else
													$item_data['tables-sections'][$section] = array();
											}

											if ($all_tables_option == true) {
												$item_data['tables-option'] = 'all';
											} else {
												$item_data['tables-option'] = 'selected';
											}
										}
									}
									unset($item_data['tables']);

									$item_data['files-option'] 	= "none";
									$item_data['files-sections'] = array();
									$item_data['files-count']	= 0;

									if ((isset($item_data['filename'])) && (strlen($item_data['filename']))) {
										$backupZipFile = trailingslashit($this->_settings['backupBaseFolderFull']) . $item_data['filename'];
										if (file_exists($backupZipFile)) {

											// Get the file size
											$item_data['file_size'] = filesize($backupZipFile);

											// Now we do a hard task and extract the minifest.txt file then convert it to the new format. Tricky X 2!
											if (!defined('PCLZIP_TEMPORARY_DIR'))
												define('PCLZIP_TEMPORARY_DIR', trailingslashit($this->_settings['backupBackupFolderFull']) . $item_key."/");
											if (!class_exists('class PclZip'))
												require_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');

											$zipArchive = new PclZip($backupZipFile);
											$zip_contents = $zipArchive->listContent();
											if (($zip_contents) && (!empty($zip_contents))) {

												foreach($zip_contents as $zip_index => $zip_file_info) {
													if ($zip_file_info['stored_filename'] == "snapshot_manifest.txt") {

														Snapshot_Helper_Utility::recursive_rmdir($restoreFolder);
														$extract_files = $zipArchive->extractByIndex($zip_index, $restoreFolder);
														if ($extract_files) {

															$snapshot_manifest_file = trailingslashit($restoreFolder) . 'snapshot_manifest.txt';
															if (file_exists($snapshot_manifest_file)) {

																$manifest_data = snapshot_utility_consume_archive_manifest($snapshot_manifest_file);

																$manifest_data['SNAPSHOT_VERSION'] = $this->_settings['SNAPSHOT_VERSION'];

																$manifest_data['WP_UPLOAD_PATH'] = snapshot_utility_get_blog_upload_path(intval($item['blog-id']));

																$item_tmp = $item;
																unset($item_tmp['data']);
																$item_tmp['data'] = array();
																$item_tmp['data'][$item_data_idx] = $item_data;
																$manifest_data['ITEM'] = $item_tmp;

																$manifest_data['TABLES'] = $item_data['tables-sections'];
																//echo "manifest_data<pre>"; print_r($manifest_data); echo "</pre>";

																if (snapshot_utility_create_archive_manifest($manifest_data, $snapshot_manifest_file)) {
																	$zipArchive->deleteByIndex($zip_index);

																	$archiveFiles = array($snapshot_manifest_file);
																	$zipArchive->add($archiveFiles,
																		PCLZIP_OPT_REMOVE_PATH, $restoreFolder,
																		PCLZIP_OPT_TEMP_FILE_THRESHOLD, 10);

																	foreach($archiveFiles as $archiveFile) {
																		@unlink($archiveFile);
																	}
																}
															}
														}
														break;
													}
												}
											}
										}
									}

									$item['data'][$item_data_idx] = $item_data;
								}
								krsort($item['data']);
							}

							// Convert the logs...

							$backupLogFileFull = trailingslashit($this->_settings['backupLogFolderFull']) . $item['timestamp'] ."_backup.log";
							if (file_exists($backupLogFileFull)) {
								$log_entries = snapshot_utility_get_archive_log_entries($backupLogFileFull);
								if (($log_entries) && (count($log_entries))) {
									foreach($log_entries as $log_key => $log_data) {
										foreach($item['data'] as $item_data_idx => $item_data) {
											if ($log_key == $item_data['filename']) {
												$new_backupLogFileFull = trailingslashit($this->_settings['backupLogFolderFull']) .
													$item['timestamp'] ."_". $item_data_idx .".log";
												file_put_contents($new_backupLogFileFull, implode("\r\n", $log_data));
											}
										}
									}
								}
								@unlink($backupLogFileFull);
							}

							$this->config_data['items'][$item_idx] = $item;
						}

						// Now convert the Last Tables config section
						if (isset($this->config_data['config']['tables_last'])) {
							foreach ($this->config_data['config']['tables_last'] as $blog_id => $item_tables) {

								$all_tables_sections = snapshot_utility_get_database_tables($blog_id);
								if ($all_tables_sections) {
									$item_section_tables = array();
									foreach($all_tables_sections as $section => $section_tables) {
										if (count($section_tables))
											$item_section_tables[$section] = array_intersect_key($section_tables, $item_tables);
										else
											$item_section_tables[$section] = array();
									}
								}
								$this->config_data['config']['tables_last'][$blog_id] = $item_section_tables;
							}
						}
					}
				*/
				/*
								foreach($this->config_data['items'] as $item_idx => $item) {
									if (!isset($item['data'])) continue;

									foreach($item['data'] as $item_data_idx => $item_data) {
										if (!isset($item_data['destination-status'])) continue;

										foreach($item_data['destination-status'] as $destination_idx => $destination_status) {
											if (isset($destination_status['sendFileStatus'])) continue;

											if ( (isset($destination_status['responseArray'])) && (count($destination_status['responseArray']))
											  && (isset($destination_status['errorStatus'])) && ($destination_status['errorStatus'] != true) ) {

												// Assumed! Since we have responseArray items and the errorStatus is NOT set. Assume success
												$this->config_data['items'][$item_idx]['data'][$item_data_idx]['destination-status'][$destination_idx]['sendFileStatus'] = true;
											}
										}
									}
								}
				*/
				//echo "config_data<pre>"; print_r($this->config_data['items']); echo "</pre>";

				//die();

				$this->config_data['version'] = $this->_settings['SNAPSHOT_VERSION'];
				$this->save_config();
			}

			/**
			 * Fires when config data has been loaded
			 *
			 * Can be used to manipulate the loaded data on the fly.
			 *
			 * @since 3.0.2-beta-1
			 */
			do_action( 'snapshot_config_loaded' );
		}

		/**
		 * Utility function to save our config array to the WordPress options table.
		 *
		 * @since 1.0.0
		 * @uses $this->_settings
		 * @uses $this->config_data
		 *
		 * @param bool $force_save if set to true will first delete the option from the
		 * global options array then re-add it. This is needed after a restore action where
		 * the restored table my be the wp_options. In this case we need to re-add out own
		 * plugins config array. When we call update_option() WordPress will not see a change
		 * when it compares our config data to its own internal version so the INSERT will be skipped.
		 * If we first delete the option from the WordPress internal version this will force
		 * WordPress to re-insert our plugin option to the MySQL table.
		 *
		 * @return bool whether the config was saved successfully
		 */
		public function save_config( $force_save = false ) {
			global $wpdb;

			// Note below for multisite we hard code the blog id to '1'. This is because the plugin should only ever
			// save to the primary site.
			if ( $force_save ) {
				if ( is_multisite() ) {
					delete_blog_option( $wpdb->blogid, $this->_settings['options_key'] );
				} else {
					delete_option( $this->_settings['options_key'] );
				}
			}

			if ( is_multisite() ) {
				$result = update_blog_option( $wpdb->blogid, $this->_settings['options_key'], $this->config_data );
			} else {
				$result = update_option( $this->_settings['options_key'], $this->config_data );
			}

			return $result;
		}

		public function add_update_config_item( $item_key, $item ) {
			$this->load_config();
			$this->config_data['items'][ $item_key ] = $item;
			$this->save_config();
		}

		/**
		 * Utility function to pull the snapshot item from the config_data based on
		 * the $_REQUEST['item] value
		 *
		 * @since 1.0.0
		 * @uses $this->config_data
		 *
		 * @param array $item if found this array is the found snapshot item.
		 *
		 * @return void
		 */
		public function snapshot_get_edit_item( $item_key ) {
			//		if (!isset($_REQUEST['item']))
			//			return;

			// If the config_data[items] array has not yet been initialized or is empty return.
			if ( ( ! isset( $this->config_data['items'] ) ) || ( ! count( $this->config_data['items'] ) ) ) {
				return;
			}

			//$item_key = esc_attr($_REQUEST['item']);

			if ( isset( $this->config_data['items'][ $item_key ] ) ) {
				return $this->config_data['items'][ $item_key ];
			}
		}

		/**
		 * Utility function to setup our destination folder to store snapshot output
		 * files. The folder destination will be inside the site's /wp-content/uploads/
		 * folder tree. The default folder name will be 'snapshots'
		 *
		 * @since 1.0.0
		 * @see wp_upload_dir()
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function set_backup_folder( $is_moving = false ) {

			global $current_site;

			if ( is_multisite() ) {
				switch_to_blog( $current_site->blog_id );
			}

			$wp_upload_dir = wp_upload_dir();
			$wp_upload_dir['basedir'] = str_replace( '\\', '/', $wp_upload_dir['basedir'] );

			$this->config_data['config']['backupFolder'] = str_replace( '\\', '/', $this->config_data['config']['backupFolder'] );

			// Are we dealing with Abolute or relative path?
			if ( '/' === ( substr( $this->config_data['config']['backupFolder'], 0, 1 ) )
			     || ':/' === ( substr( $this->config_data['config']['backupFolder'], 1, 2 ) )
			) {

				// If absolute set a flag so we don't need to keep checking the substr();
				$this->config_data['config']['absoluteFolder'] = true;
				$_backupFolderFull = trailingslashit( $this->config_data['config']['backupFolder'] );

			} else {

				// If relative unset a flag so we don't need to keep checking the substr();
				$this->config_data['config']['absoluteFolder'] = false;

				// If relative then we store the files into the /uploads/ folder tree.
				$_backupFolderFull = trailingslashit( $wp_upload_dir['basedir'] ) . $this->config_data['config']['backupFolder'];
			}

			if ( ! file_exists( $_backupFolderFull ) ) {

				/* If the destination folder does not exist try and create it */
				if ( wp_mkdir_p( $_backupFolderFull, 0775 ) === false ) {

					/* If here we cannot create the folder. So report this via the admin header message and return */
					$this->_admin_header_error .= __( "ERROR: Cannot create snapshot folder. Check that the parent folder is writable", SNAPSHOT_I18N_DOMAIN )
					                              . " " . $_backupFolderFull;

					return;
				}
			}

			//echo "_backupFolderFull=[". $_backupFolderFull ."]<br />";
			/* If here the destination folder is present. But is it writeable by our process? */
			if ( ! is_writable( $_backupFolderFull ) ) {

				/* Try updating the folder perms */
				chmod( $_backupFolderFull, 0775 );
				if ( ! is_writable( $_backupFolderFull ) ) {

					/* Appears it is still not writeable then report this via the admin heder message and return */
					$this->_admin_header_error .= __( "ERROR: The Snapshot destination folder is not writable", SNAPSHOT_I18N_DOMAIN )
					                              . " " . $_backupFolderFull;
				}
			}

			$this->_settings['backupBaseFolderFull'] = $_backupFolderFull;
			if ( true !== $this->config_data['config']['absoluteFolder'] ) {

				$this->_settings['backupURLFull'] = trailingslashit( $wp_upload_dir['baseurl'] ) . $this->config_data['config']['backupFolder'];

			} else {
				$this->_settings['backupURLFull'] = '';
			}

			if ( is_multisite() ) {
				restore_current_blog();
			}
		}

		public function set_log_folders() {

			if ( empty( $this->_settings['backupBaseFolderFull'] ) ) {
				return;
			}
			Snapshot_Helper_Utility::secure_folder( $this->_settings['backupBaseFolderFull'] );

			$_backupBackupFolderFull = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . '_backup';
			if ( ! file_exists( $_backupBackupFolderFull ) ) {

				/* If the destination folder does not exist try and create it */
				if ( wp_mkdir_p( $_backupBackupFolderFull, 0775 ) === false ) {

					/* If here we cannot create the folder. So report this via the admin header message and return */
					$this->_admin_header_error .= __(
                         "ERROR: Cannot create snapshot Log folder. Check that the parent folder is writeable",
							SNAPSHOT_I18N_DOMAIN
                        ) . " " . $_backupBackupFolderFull;

					return;
				}
			}

			/* If here the destination folder is present. But is it writeable by our process? */
			if ( ! is_writable( $_backupBackupFolderFull ) ) {

				/* Try updating the folder perms */
				chmod( $_backupBackupFolderFull, 0775 );
				if ( ! is_writable( $_backupBackupFolderFull ) ) {

					/* Appears it is still not writeable then report this via the admin heder message and return */
					$this->_admin_header_error .= __( "ERROR: The Snapshot destination folder is not writable", SNAPSHOT_I18N_DOMAIN ) . " " . $_backupBackupFolderFull;
				}
			}
			Snapshot_Helper_Utility::secure_folder( $_backupBackupFolderFull );
			$this->_settings['backupBackupFolderFull'] = $_backupBackupFolderFull;

			$_backupRestoreFolderFull = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . '_restore';
			if ( ! file_exists( $_backupRestoreFolderFull ) ) {

				/* If the destination folder does not exist try and create it */
				if ( wp_mkdir_p( $_backupRestoreFolderFull, 0775 ) === false ) {

					/* If here we cannot create the folder. So report this via the admin header message and return */
					$this->_admin_header_error .= __( "ERROR: Cannot create snapshot Restore folder. Check that the parent folder is writeable", SNAPSHOT_I18N_DOMAIN ) . " " . $_backupRestoreFolderFull;

					return;
				}
			}

			/* If here the destination folder is present. But is it writeable by our process? */
			if ( ! is_writable( $_backupRestoreFolderFull ) ) {

				/* Try updating the folder perms */
				chmod( $_backupRestoreFolderFull, 0775 );
				if ( ! is_writable( $_backupRestoreFolderFull ) ) {

					/* Appears it is still not writeable then report this via the admin heder message and return */
					$this->_admin_header_error .= __( "ERROR: The Snapshot restore folder is not writable", SNAPSHOT_I18N_DOMAIN ) . " " . $_backupRestoreFolderFull;
				}
			}
			Snapshot_Helper_Utility::secure_folder( $_backupRestoreFolderFull );
			$this->_settings['backupRestoreFolderFull'] = $_backupRestoreFolderFull;

			$_backupLogFolderFull = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . '_logs';
			if ( ! file_exists( $_backupLogFolderFull ) ) {

				/* If the destination folder does not exist try and create it */
				if ( wp_mkdir_p( $_backupLogFolderFull, 0775 ) === false ) {

					/* If here we cannot create the folder. So report this via the admin header message and return */
					$this->_admin_header_error .= __( "ERROR: Cannot create snapshot Log folder. Check that the parent folder is writeable", SNAPSHOT_I18N_DOMAIN ) . " " . $_backupLogFolderFull;

					return;
				}
			}

			/* If here the destination folder is present. But is it writeable by our process? */
			if ( ! is_writable( $_backupLogFolderFull ) ) {

				/* Try updating the folder perms */
				chmod( $_backupLogFolderFull, 0775 );
				if ( ! is_writable( $_backupLogFolderFull ) ) {

					/* Appears it is still not writeable then report this via the admin heder message and return */
					$this->_admin_header_error .= __( "ERROR: The Snapshot destination folder is not writable", SNAPSHOT_I18N_DOMAIN ) . " " . $_backupLogFolderFull;
				}
			}
			Snapshot_Helper_Utility::secure_folder( $_backupLogFolderFull );
			$this->_settings['backupLogFolderFull'] = $_backupLogFolderFull;

			// Setup our own version of _SESSION save path. This is because some servers just don't have standard PHP _SESSIONS setup properly.
			$_backupSessionsFolderFull = trailingslashit( $this->_settings['backupLogFolderFull'] ) . '_sessions';
			if ( ! file_exists( $_backupSessionsFolderFull ) ) {

				/* If the destination folder does not exist try and create it */
				if ( wp_mkdir_p( $_backupSessionsFolderFull, 0775 ) === false ) {

					/* If here we cannot create the folder. So report this via the admin header message and return */
					$this->_admin_header_error .= __( "ERROR: Cannot create snapshot Log folder. Check that the parent folder is writeable", SNAPSHOT_I18N_DOMAIN );
					$this->_admin_header_error .= ' ' . $_backupSessionsFolderFull;

					return;
				}
			}

			/* If here the destination folder is present. But is it writeable by our process? */
			if ( ! is_writable( $_backupSessionsFolderFull ) ) {

				/* Try updating the folder perms */
				chmod( $_backupSessionsFolderFull, 0775 );
				if ( ! is_writable( $_backupSessionsFolderFull ) ) {

					/* Appears it is still not writeable then report this via the admin heder message and return */
					$this->_admin_header_error .= __( "ERROR: The Snapshot destination folder is not writable", SNAPSHOT_I18N_DOMAIN )
					                              . " " . $_backupSessionsFolderFull;
				}
			}
			Snapshot_Helper_Utility::secure_folder( $_backupSessionsFolderFull );
			$this->_settings['backupSessionFolderFull'] = $_backupSessionsFolderFull;

			if ( true !== $this->config_data['config']['absoluteFolder'] ) {

				//$relative_path = substr($_backupLogFolderFull, strlen(ABSPATH));
				//$this->_settings['backupLogURLFull']		= site_url($relative_path);
				$this->_settings['backupLogURLFull'] = trailingslashit( $this->_settings['backupURLFull'] ) . '_logs';
			} else {
				$this->_settings['backupLogURLFull'] = '';
			}

			/* Setup the _locks folder. Used by scheduled tasks */

			$_backupLockFolderFull = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . '_locks';
			if ( ! file_exists( $_backupLockFolderFull ) ) {

				/* If the destination folder does not exist try and create it */
				if ( wp_mkdir_p( $_backupLockFolderFull, 0775 ) === false ) {

					/* If here we cannot create the folder. So report this via the admin header message and return */
					$this->_admin_header_error .= __(
                         "ERROR: Cannot create snapshot Lock folder. Check that the parent folder is writeable",
							SNAPSHOT_I18N_DOMAIN
                        ) . " " . $_backupLockFolderFull;

					return;
				}
			}

			/* If here the destination folder is present. But is it writeable by our process? */
			if ( ! is_writable( $_backupLockFolderFull ) ) {

				/* Try updating the folder perms */
				chmod( $_backupLockFolderFull, 0775 );
				if ( ! is_writable( $_backupLockFolderFull ) ) {

					/* Appears it is still not writeable then report this via the admin heder message and return */
					$this->_admin_header_error .= __( "ERROR: The Snapshot locks folder is not writable", SNAPSHOT_I18N_DOMAIN )
					                              . " " . $_backupLockFolderFull;
				}
			}
			Snapshot_Helper_Utility::secure_folder( $_backupLockFolderFull );
			$this->_settings['backupLockFolderFull'] = $_backupLockFolderFull;
		}

		/**
		 * AJAX Gateway to adding a new snapshot. Seems the simple form post is too much given
		 * the number of tables possibly selected. So instead we intercept the form submit with
		 * jQuery and process each selected table as its own HTTP POST into this gateway.
		 *
		 * The process starts with the 'init' which sets up the session backup filename based on
		 * the session id. Next each 'table' is called. Last a 'finish' action is called to move
		 * the temp file into the final location and add a record about the backup to the activity log
		 *
		 * @since 1.0.0
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */

		public function snapshot_ajax_backup_proc() {
			check_ajax_referer( 'snapshot-ajax-nonce', 'security' );
			global $wpdb;

			// When zlib compression is turned on we get errors from this shutdown action setup by WordPress. So we disabled.
			$zlib_compression = ini_get( 'zlib.output_compression' );
			if ( $zlib_compression ) {
				remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
			}

			@ini_set( 'html_errors', 'Off' ); // phpcs:ignore
			@ini_set( 'zlib.output_compression', 'Off' ); // phpcs:ignore
			@set_time_limit( 0 ); // phpcs:ignore

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			$old_error_handler = set_error_handler( array( $this, 'snapshot_ErrorHandler' ) );

			if ( ( isset( $this->config_data['config']['memoryLimit'] ) ) && ( ! empty( $this->config_data['config']['memoryLimit'] ) ) ) {
				@ini_set( 'memory_limit', $this->config_data['config']['memoryLimit'] ); // phpcs:ignore
			}

			// Need the item_key and data_item_key before init of the Logger
			if ( isset( $_POST['snapshot-item'] ) ) {
				$item_key = intval( $_POST['snapshot-item'] );
			}
			if ( isset( $_POST['snapshot-data-item'] ) ) {
				$data_item_key = intval( $_POST['snapshot-data-item'] );
			}

			$this->snapshot_logger = new Snapshot_Helper_Logger( $this->_settings['backupLogFolderFull'], $item_key, $data_item_key );

			Snapshot_Helper_Debug::set_error_reporting( $this->config_data['config']['errorReporting'] );

			/* Needed to create the archvie zip file */
			if ( "PclZip" === $this->config_data['config']['zipLibrary'] ) {
				if ( ! defined( 'PCLZIP_TEMPORARY_DIR' ) ) {
					define( 'PCLZIP_TEMPORARY_DIR', trailingslashit( $this->_settings['backupBackupFolderFull'] ) . $item_key . "/" );
				}
				if ( ! class_exists( 'class PclZip' ) ) {
					require_once ABSPATH . '/wp-admin/includes/class-pclzip.php';
				}
			}

			switch ( $_REQUEST['snapshot-proc-action'] ) {
				case 'init':

					$this->snapshot_logger->log_message( 'Backup: init' );

					// Start/load our sessions file
					$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key, true );

					if ( isset( $_POST['snapshot-action'] ) ) {
						if ( "add" === $_POST['snapshot-action'] ) {
							$this->snapshot_logger->log_message( "adding new snapshot: " . $item_key );
						} else if ( "update" === $_POST['snapshot-action'] ) {
							$this->snapshot_logger->log_message( "updating snapshot: " . $item_key );
						}
						$this->snapshot_add_update_action_proc( $_POST );
					}

					if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
						die();
					}

					$item = $this->config_data['items'][ $item_key ];

					$destination_key = $item['destination'];
					$destmeta = ! empty( $this->config_data['destinations'][ $destination_key ] )
						? $this->config_data['destinations'][ $destination_key ]
						: array();

					if ( ! empty( $destmeta['protocol'] ) && ( 'sftp' === $destmeta['protocol'] ) && ( version_compare( phpversion(), "5.3.8", "<" ) ) ) {
						$this->snapshot_logger->log_message( "Error: For SFTP destinations, a PHP version greater or equal to 5.3.8 is required." );
						$error_array['errorStatus'] = true;

						echo wp_json_encode( $error_array );

						die();
					}

					$blog_id = 0;
					if ( is_multisite() ) {
						if ( isset( $item['blog-id'] ) ) {
							$blog_id = intval( $item['blog-id'] );
							if ( $blog_id !== $wpdb->blogid ) {
								$original_blog_id = $wpdb->blogid;
								switch_to_blog( $blog_id );
							}
						}
					}

					ob_start();
					$error_array = $this->snapshot_ajax_backup_init( $item, $_POST );
					$function_output = ob_get_contents();
					ob_end_clean();

					if ( ( is_multisite() ) && ( isset( $original_blog_id ) ) && ( $original_blog_id > 0 ) ) {
						switch_to_blog( $original_blog_id );
					}

					if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
						// We have a problem.

						// Not debug code. We use print_r() for logging purposes.
						$this->snapshot_logger->log_message( "init: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "init: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "init: _SESSION" . print_r( $this->_session->data, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "init: output:" . $function_output );

						$this->snapshot_logger->log_message(
                             "memory limit: " . ini_get( 'memory_limit' ) .
						                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
						                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                            );

						$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
						$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

						$error_array['MEMORY'] = array();
						$error_array['MEMORY']['memory_limit'] = ini_get( 'memory_limit' );
						$error_array['MEMORY']['memory_usage_current'] = Snapshot_Helper_Utility::size_format( memory_get_usage( true ) );
						$error_array['MEMORY']['memory_usage_peak'] = Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) );

						echo wp_json_encode( $error_array );

						die();
					}
					break;

				case 'table':

					if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
						die();
					}

					$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key );

					$item = $this->config_data['items'][ $item_key ];

					$blog_id = 0;
					if ( is_multisite() ) {
						if ( isset( $_POST['snapshot-blog-id'] ) ) {
							$blog_id = intval( $_POST['snapshot-blog-id'] );
							if ( $blog_id !== $wpdb->blogid ) {
								$original_blog_id = $wpdb->blogid;
								switch_to_blog( $blog_id );
							}
						}
					}

					ob_start();
					$error_array = $this->snapshot_ajax_backup_table( $item, $_POST );
					$function_output = ob_get_contents();
					ob_end_clean();

					if ( ( is_multisite() ) && ( isset( $original_blog_id ) ) && ( $original_blog_id > 0 ) ) {
						switch_to_blog( $original_blog_id );
					}

					if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
						// We have a problem.

						// Not debug code. We use print_r() for logging purposes.
						$this->snapshot_logger->log_message( "table: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "table: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "table: _SESSION" . print_r( $this->_session->data, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "table: output:" . $function_output );

						$this->snapshot_logger->log_message(
                             "memory limit: " . ini_get( 'memory_limit' ) .
						                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
						                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                            );

						$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
						$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

						$error_array['MEMORY'] = array();
						$error_array['MEMORY']['memory_limit'] = ini_get( 'memory_limit' );
						$error_array['MEMORY']['memory_usage_current'] = Snapshot_Helper_Utility::size_format( memory_get_usage( true ) );
						$error_array['MEMORY']['memory_usage_peak'] = Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) );

						echo wp_json_encode( $error_array );

						die();
					}

					break;

				case 'file':

					if ( isset( $_POST['snapshot-file-data-key'] ) ) {

						$file_data_key = esc_attr( $_POST['snapshot-file-data-key'] );

						$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key );

						if ( isset( $this->_session->data['files_data']['included'][ $file_data_key ] ) ) {

							if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
								die();
							}

							$item = $this->config_data['items'][ $item_key ];
							$this->snapshot_logger->log_message( "file: section: " . $file_data_key );

							ob_start();

							$error_array = $this->snapshot_ajax_backup_file( $item, $_POST );
							$function_output = ob_get_contents();
							ob_end_clean();

							if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
								// We have a problem.

								// Not debug code. We use print_r() for logging purposes.
								$this->snapshot_logger->log_message( "file: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: _SESSION" . print_r( $this->_session->data, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: output:" . $function_output );

								$this->snapshot_logger->log_message(
                                     "memory limit: " . ini_get( 'memory_limit' ) .
								                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
								                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                    );

								$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
								$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

								$error_array['MEMORY'] = array();
								$error_array['MEMORY']['memory_limit'] = ini_get( 'memory_limit' );
								$error_array['MEMORY']['memory_usage_current'] = Snapshot_Helper_Utility::size_format( memory_get_usage( true ) );
								$error_array['MEMORY']['memory_usage_peak'] = Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) );

								echo wp_json_encode( $error_array );

								die();
							}
						}
					}

					break;

				case 'finish':

					if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
						die();
					}

					$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key );
					//echo "_session<pre>"; print_r($this->_session); echo "</pre>";

					$item = $this->config_data['items'][ $item_key ];
					ob_start();
					$error_array = $this->snapshot_ajax_backup_finish( $item, $_POST );
					$function_output = ob_get_contents();
					ob_end_clean();

					if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
						// We have a problem.

						// Not debug code. We use print_r() for logging purposes.
						$this->snapshot_logger->log_message( "finish: error_array:" . print_r( $error_array, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "finish: _SESSION:" . print_r( $this->_session->data, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "finish: item:" . print_r( $item, true ) ); // phpcs:ignore
						$this->snapshot_logger->log_message( "finish: output:" . $function_output );

						$this->snapshot_logger->log_message(
                             "memory limit: " . ini_get( 'memory_limit' ) .
						                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
						                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                            );

						$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
						$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

						$error_array['MEMORY'] = array();
						$error_array['MEMORY']['memory_limit'] = ini_get( 'memory_limit' );
						$error_array['MEMORY']['memory_usage_current'] = Snapshot_Helper_Utility::size_format( memory_get_usage( true ) );
						$error_array['MEMORY']['memory_usage_peak'] = Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) );

						echo wp_json_encode( $error_array );

						die();
					} else {
						$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;

						//echo "_session<pre>"; print_r($this->_session); echo "</pre>";
					}

					$this->snapshot_logger->log_message( "finish: " . basename( $error_array['responseFile'] ) );
					$this->purge_archive_limit( $item_key );
					wp_remote_post(
						get_option( 'siteurl' ) . '/wp-cron.php',
							array(
								'timeout' => 3,
								'blocking' => false,
								'sslverify' => false,
								'body' => array(
									'nonce' => wp_create_nonce( 'WPMUDEVSnapshot' ),
									'type' => 'start',
								),
								'user-agent' => 'WPMUDEVSnapshot',
							)
					);

					break;

				default:
					break;
			}

			$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

//			$this->snapshot_item_run_immediate($item_key);

			$error_array['MEMORY'] = array();
			$error_array['MEMORY']['memory_limit'] = ini_get( 'memory_limit' );
			$error_array['MEMORY']['memory_usage_current'] = Snapshot_Helper_Utility::size_format( memory_get_usage( true ) );
			$error_array['MEMORY']['memory_usage_peak'] = Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) );

			echo wp_json_encode( $error_array );
			die();
		}

		/**
		 * This 'init' process begins the user's backup via AJAX. Creates the session backup file.
		 *
		 * @since 1.0.0
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */

		public function snapshot_ajax_backup_init( $item, $_post_array ) {
			global $wpdb;

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";
			$error_status['table_data'] = array();
			$error_status['files_data'] = array();

			if ( isset( $this->_session->data ) ) {
				unset( $this->_session->data );
			}

			$sessionItemBackupFolder = trailingslashit( $this->_settings['backupBackupFolderFull'] );
			$sessionItemBackupFolder = trailingslashit( $sessionItemBackupFolder ) . intval( $item['timestamp'] );

			if ( ! file_exists( $sessionItemBackupFolder ) ) {
				wp_mkdir_p( $sessionItemBackupFolder );
			}

			if ( ! is_writable( $sessionItemBackupFolder ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" . __(
                     "ERROR: Snapshot backup aborted.<br />The Snapshot folder is not writeable. Check the settings.",
						SNAPSHOT_I18N_DOMAIN
                    ) . " " . $sessionItemBackupFolder . "</p>";

				return $error_status;
			}

			// Cleanup any files from a previous backup attempt
			$dh = opendir( $sessionItemBackupFolder );
			if ( $dh ) {
				$file = readdir( $dh );
				while ( false !== $file ) {
					if ( ( '.' === $file ) || ( '..' === $file ) ) {
						$file = readdir( $dh );
						continue;
					}

					if ( is_writable( trailingslashit( $sessionItemBackupFolder ) . $file ) )
						unlink( trailingslashit( $sessionItemBackupFolder ) . $file );

					$file = readdir( $dh );
				}
				closedir( $dh );
			}
			$this->_session->data['backupItemFolder'] = $sessionItemBackupFolder;

			if ( isset( $this->_session->data['table_data'] ) ) {
				unset( $this->_session->data['table_data'] );
			}

			if ( isset( $item['tables-option'] ) ) {

				if ( "none" === $item['tables-option'] ) {
					assert(true); // No-op.
				} else if ( "all" === $item['tables-option'] ) {
					$tables_sections = Snapshot_Helper_Utility::get_database_tables( $item['blog-id'] );
				} else if ( "selected" === $item['tables-option'] ) {
					// This should already be set from the Add/Update form post
					$tables_sections = $item['tables-sections'];
				}
			}
			//echo "tables_sections<pre>"; print_r($tables_sections); echo "</pre>";
			//die();

			if ( ( isset( $tables_sections ) ) && ( count( $tables_sections ) ) ) {

				foreach ( $tables_sections as $section => $tables_set ) {
					foreach ( $tables_set as $table_name ) {
						$_set = array();

						if ( "global" === $section ) {
							//echo "table_name[". $table_name ."]<br />";
							if ( ( $wpdb->base_prefix . "users" === $table_name ) || ( $wpdb->base_prefix . "usermeta" === $table_name ) ) {

								if ( ! isset( $this->_session->data['global_user_ids'] ) ) {
									$this->_session->data['global_user_ids'] = array();
									$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->base_prefix}usermeta WHERE meta_key='primary_blog' AND meta_value=%s", $item['blog-id'] ) );
									if ( $user_ids ) {
										$this->_session->data['global_user_ids'] = $user_ids;
									}
								}

								if ( ( isset( $this->_session->data['global_user_ids'] ) )
								     && ( is_array( $this->_session->data['global_user_ids'] ) )
								     && ( count( $this->_session->data['global_user_ids'] ) )
								) {
									if ( $wpdb->base_prefix . "users" === $table_name ) {

										$tables_segment = Snapshot_Helper_Utility::get_table_segments(
												$table_name,
												intval( $this->config_data['config']['segmentSize'] ),
												'WHERE ID IN (' . implode( ',', $this->_session->data['global_user_ids'] ) . ');'
											);

									} else if ( $wpdb->base_prefix . "usermeta" === $table_name ) {
										$tables_segment = Snapshot_Helper_Utility::get_table_segments(
												$table_name,
												intval( $this->config_data['config']['segmentSize'] ),
												'WHERE user_id IN (' . implode( ',', $this->_session->data['global_user_ids'] ) . ');'
											);
									}
									//echo "tables_segment<pre>"; print_r($tables_segment); echo "</pre>";
									if ( ( $tables_segment['segments'] ) && ( count( $tables_segment['segments'] ) ) ) {

										foreach ( $tables_segment['segments'] as $segment_idx => $_set ) {

											$_set['table_name'] = $tables_segment['table_name'];
											$_set['rows_total'] = $tables_segment['rows_total'];
											$_set['segment_idx'] = intval( $segment_idx ) + 1;
											$_set['segment_total'] = count( $tables_segment['segments'] );

											$error_status['table_data'][] = $_set;
										}
									}
								}
							}
						} else {
							$tables_segment = Snapshot_Helper_Utility::get_table_segments( $table_name, intval( $this->config_data['config']['segmentSize'] ) );
							if ( ( $tables_segment['segments'] ) && ( count( $tables_segment['segments'] ) ) ) {

								foreach ( $tables_segment['segments'] as $segment_idx => $_set ) {

									$_set['table_name'] = $tables_segment['table_name'];
									$_set['rows_total'] = $tables_segment['rows_total'];
									$_set['segment_idx'] = intval( $segment_idx ) + 1;
									$_set['segment_total'] = count( $tables_segment['segments'] );

									$error_status['table_data'][] = $_set;
								}
							} else {
								$_set['table_name'] = $tables_segment['table_name'];
								$_set['rows_total'] = $tables_segment['rows_total'];
								$_set['segment_idx'] = 1;
								$_set['segment_total'] = 1;
								$_set['rows_start'] = 0;
								$_set['rows_end'] = 0;

								$error_status['table_data'][] = $_set;
							}
						}
					}
				}

				if ( ( isset( $tables_sections ) ) && ( count( $tables_sections ) ) ) {
					$this->_session->data['tables_sections'] = $tables_sections;
				} else {
					$this->_session->data['tables_sections'] = array();
				}

				if ( isset( $error_status['table_data'] ) ) {
					$this->_session->data['table_data'] = $error_status['table_data'];
				}
			}
			//echo "table_data<pre>"; print_r($this->_session->data['table_data']); echo "</pre>";
			//die();

			if ( ! isset( $item['destination-sync'] ) ) {
				$item['destination-sync'] = "archive";
			}

			if ( "archive" === $item['destination-sync'] ) {
				//echo "_post_array<pre>"; print_r($_post_array); echo "</pre>";
				//echo "item<pre>"; print_r($item); echo "</pre>";

				$error_status['files_data'] = $this->snapshot_gather_item_files( $item );
				//echo "files_data<pre>"; print_r($error_status['files_data']); echo "</pre>";
				//die();

				if ( ( isset( $error_status['files_data']['included'] ) ) && ( count( $error_status['files_data']['included'] ) ) ) {
					$files_data = array();

					foreach ( $error_status['files_data']['included'] as $_section => $_files ) {

						if ( ! count( $_files ) ) {
							continue;
						}

						switch ( $_section ) {
							/*
							case 'home':
								$_path = $home_path;
								if (($_post_array['snapshot-action']) && ($_post_array['snapshot-action'] == "cron"))
									$_max_depth=0;
								else
									$_max_depth=2;
								break;
							*/
							case 'media':
								$_path = trailingslashit( $home_path ) . Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] ) . "/";
								//$_path = snapshot_utility_get_blog_upload_path($item['blog-id']) ."/";
								if ( ( $_post_array['snapshot-action'] ) && ( "cron" === $_post_array['snapshot-action'] ) ) {
									$_max_depth = 0;
								} else {
									$_max_depth = 2;
								}
								break;

							case 'plugins':
								/* case 'mu-plugins': */
								$_path = trailingslashit( $this->plugins_dir );
								//$_max_depth=0;
								if ( ( $_post_array['snapshot-action'] ) && ( "cron" === $_post_array['snapshot-action'] ) ) {
									$_max_depth = 0;
								} else {
									$_max_depth = 0;
								}
								break;

							case 'mu-plugins':
								$_path = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/';
								//$_max_depth=0;
								if ( ( $_post_array['snapshot-action'] ) && ( "cron" === $_post_array['snapshot-action'] ) ) {
									$_max_depth = 0;
								} else {
									$_max_depth = 0;
								}
								break;

							case 'themes':
								$_path = trailingslashit( WP_CONTENT_DIR ) . 'themes/';
								if ( ( $_post_array['snapshot-action'] ) && ( "cron" === $_post_array['snapshot-action'] ) ) {
									$_max_depth = 0;
								} else {
									$_max_depth = 0;
								}
								//$_max_depth=0;
								break;

							default:
								$_path = '';
								$_max_depth = 0;
								break;
						}

						if ( ( $_max_depth > 0 ) && ( ! empty( $_path ) ) ) {
							foreach ( $_files as $_idx => $_file ) {
								$_new_file = str_replace( $_path, '', $_file );
								$_slash_parts = explode( '/', $_new_file );
								if ( count( $_slash_parts ) > $_max_depth ) {

									// We first remove the file from this section...
									unset( $error_status['files_data']['included'][ $_section ][ $_idx ] );

									// ... then we add a new section for this group of files.
									$_new_section = '';
									foreach ( $_slash_parts as $_slash_idx => $slash_part ) {
										if ( $_slash_idx > ( $_max_depth - 1 ) ) {
											break;
										}

										if ( strlen( $_new_section ) ) {
											$_new_section .= "/";
										}
										$_new_section .= $slash_part;

										unset( $_slash_parts[ $_slash_idx ] );
									}
									$_new_file = implode( '/', array_values( $_slash_parts ) );

									if ( ! isset( $error_status['files_data']['included'][ $_section . "/" . $_new_section ] ) ) {
										$error_status['files_data']['included'][ $_section . "/" . $_new_section ] = array();
									}
									$error_status['files_data']['included'][ $_section . "/" . $_new_section ][] = $_file;
								}
							}

							if ( empty( $error_status['files_data']['included'][ $_section ] ) ) {
								unset( $error_status['files_data']['included'][ $_section ] );
							}
						}
					}
					ksort( $error_status['files_data']['included'] );

					if ( ( $_post_array['snapshot-action'] ) && ( "cron" === $_post_array['snapshot-action'] ) ) {
						$all_files = array(
							'all_files' => array(),
						);

						foreach ( $error_status['files_data']['included'] as $section => $files ) {
							$all_files['all_files'] = array_merge( $all_files['all_files'], $files );
						}
						$this->_session->data['files_data']['included'] = $all_files;
					} else {
						$this->_session->data['files_data']['included'] = $error_status['files_data']['included'];
					}
					$error_status['files_data'] = array_keys( $this->_session->data['files_data']['included'] );
				}
			} else {
				$this->_session->data['files_data'] = '';
				$error_status['files_data'] = '';
			}
			$this->_session->data['snapshot_time_start'] = time();

			$error_status['errorStatus'] = false;
			$error_status['responseText'] = "Init Start";
			//echo "DEBUG: In: ". __FUNCTION__ ."  Line:". __LINE__ ."<br />";
			//echo "error_status<pre>"; print_r($error_status); echo "</pre>";
			//echo "_session<pre>"; print_r($this->_session->data); echo "</pre>";
			//die();
			return $error_status;
		}

		/**
		 * This 'table' process is called from JS for each table selected. The contents of the SQL table
		 * are appended to the session backup file.
		 *
		 * @since 1.0.0
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */

		public function snapshot_ajax_backup_table( $item, $_post_array ) {
			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";

			if ( ! isset( $_post_array['snapshot-table-data-idx'] ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "table_data_idx not set";

				return $error_status;
			}

			$table_data_idx = intval( $_post_array['snapshot-table-data-idx'] );
			$table_data = array();

			if ( ( isset( $this->_session->data['table_data'] ) ) && ( isset( $this->_session->data['table_data'][ $table_data_idx ] ) ) ) {
				$table_data = $this->_session->data['table_data'][ $table_data_idx ];
			}

			if ( ! $table_data ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "table_data not set. This normally means that PHP on your server is not properly setup to handle _SESSION. Check with your hosting company.";

				return $error_status;
			}

			if ( ( ! isset( $table_data['rows_start'] ) ) || ( ! isset( $table_data['rows_end'] ) ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "table_data rows_start or rows_end not set";

				return $error_status;
			}

			$this->snapshot_logger->log_message(
					"table: " . $table_data['table_name'] .
					" segment: " . $table_data['segment_idx'] . "/" . $table_data['segment_total']
                );

			if ( isset( $this->_session->data['backupItemFolder'] ) ) {
				$backupTable = sanitize_text_field( $table_data['table_name'] );
				$backupFile = trailingslashit( $this->_session->data['backupItemFolder'] ) . $backupTable . ".sql";

				$fp = fopen( $backupFile, 'a' ); // phpcs:ignore
				if ( $fp ) {
					fseek( $fp, 0, SEEK_END );
					$table_data['ftell_before'] = ftell( $fp );
					$backup_db = new Snapshot_Model_Database_Backup();
					$backup_db->set_fp( $fp ); // Set our file point so the object can write to out output file.

					// $backup_db->set_file( $backupFile );

					if ( intval( $table_data['segment_idx'] ) === intval( $table_data['segment_total'] ) ) {
						if ( isset( $table_data['sql'] ) ) {
							$sql = $table_data['sql'];
						} else {
							$sql = '';
						}

						// If we are at the end ot the table's segments we now just pass a large number for the table end.
						// This will force MySQL to use the table_start as the offset then we read the rest of the rows in the table.
						$number_rows_segment = $backup_db->backup_table(
								$backupTable, $table_data['rows_start'],
								$table_data['rows_total'] * 3, $table_data['rows_total'] * 3, $sql
							);
					} else {
						// Else we just ready the table segment of rows.
						$number_rows_segment = $backup_db->backup_table(
								$backupTable, $table_data['rows_start'],
								$table_data['rows_end'], $table_data['rows_total']
							);
					}

					if ( count( $backup_db->errors ) ) {
						$error_status['errorStatus'] = true;
						$error_messages = implode( '</p><p>', $backup_db->errors );
						$error_status['errorText'] = "<p>" . __( 'ERROR: Snapshot backup aborted.', SNAPSHOT_I18N_DOMAIN ) . $error_messages . "</p>";

						return $error_status;
					}

					// $table_data['ftell_after'] = $backup_db->get_temp_ftell_after();
					unset( $backup_db );
					$table_data['ftell_after'] = ftell( $fp );
					fclose( $fp ); // phpcs:ignore

					$error_status['table_data'] = $table_data;
					$this->_session->data['table_data'][ $table_data_idx ] = $table_data;

					//if (($table_data['rows_start'] + $table_data['rows_end']) == $table_data['rows_total']) {
					if ( intval( $table_data['segment_idx'] ) === intval( $table_data['segment_total'] ) ) {

						$archiveFiles[] = $backupFile;

						$backupZipFile = trailingslashit( $this->_session->data['backupItemFolder'] ) . 'snapshot-backup.zip';

						if ( "PclZip" === $this->config_data['config']['zipLibrary'] ) {
							$zipArchive = new PclZip( $backupZipFile );
							try {
								$zip_add_ret = $zipArchive->add(
										$archiveFiles,
										PCLZIP_OPT_REMOVE_PATH, $this->_session->data['backupItemFolder'],
										PCLZIP_OPT_TEMP_FILE_THRESHOLD, 10,
										PCLZIP_OPT_ADD_TEMP_FILE_ON
									);
								if ( ! $zip_add_ret ) {
									$error_status['errorStatus'] = true;
									$error_status['errorText'] = "ERROR: PclZIP table: " . $table_data . ": add failed : " .
									                             $zipArchive->errorCode() . ": " . $zipArchive->errorInfo() . "]";

									return $error_status;
								}

							} catch ( Exception $e ) {
								$error_status['errorStatus'] = true;
								$error_status['errorText'] = "ERROR: PclZIP table:" . $table_data['table_name'] . " : add failed : " .
								                             $zipArchive->errorCode() . ": " . $zipArchive->errorInfo() . "]";

								$error_status['MEMORY']['memory_limit'] = ini_get( 'memory_limit' );
								$error_status['MEMORY']['memory_usage_current'] = Snapshot_Helper_Utility::size_format( memory_get_usage( true ) );
								$error_status['MEMORY']['memory_usage_peak'] = Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) );

								return $error_status;
							}
						} else if ( "ZipArchive" === $this->config_data['config']['zipLibrary'] ) {
							$zipArchive = new ZipArchive();
							if ( $zipArchive ) {
								if ( ! file_exists( $backupZipFile ) ) {
									$zip_flags = ZIPARCHIVE::CREATE;
								} else {
									$zip_flags = null;
								}
								$zip_hdl = $zipArchive->open( $backupZipFile, $zip_flags );
								if ( true !== $zip_hdl ) {
									$error_status['errorStatus'] = true;
									$error_status['errorText'] = "ERROR: ZipArchive table:" . $table_data['table_name'] . " : add failed: " .
									                             ZipArchiveStatusString( $zip_hdl );

									return $error_status;
								}

								foreach ( $archiveFiles as $file ) {
									$file = str_replace( '\\', '/', $file );
									$zipArchive->addFile( $file, str_replace( $this->_session->data['backupItemFolder'] . '/', '', $file ) );
								}
								$zipArchive->close();
							}
						}
						if ( isset( $zipArchive ) ) {
							unset( $zipArchive );
						}

						foreach ( $archiveFiles as $archiveFile ) {
							if ( is_writable( $archiveFile ) )
								unlink( $archiveFile );
						}
					}

					//$error_status['archiveFile'] = $archiveFile;
					return $error_status;
				}

			} else {

				if ( ! isset( $this->_session->data['backupItemFolder'] ) ) {

					$error_status['errorStatus'] = true;
					$error_status['errorText'] = "_SESSION backupFolder not set";

					return $error_status;
				}
				if ( ! isset( $_post_array['snapshot_table'] ) ) {

					$error_status['errorStatus'] = true;
					$error_status['errorText'] = "post_array snapshot_table not set";

					return $error_status;
				}
			}
		}

		/**
		 * This 'file' process
		 *
		 * @since 1.0.6
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_ajax_backup_file( $item, $_post_array ) {

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			if ( isset( $_post_array['snapshot-file-data-key'] ) ) {
				$file_data_key = sanitize_text_field( $_post_array['snapshot-file-data-key'] );
				if ( isset( $this->_session->data['files_data']['included'][ $file_data_key ] ) ) {

					@set_time_limit( 0 ); // phpcs:ignore

					$backupZipFile = trailingslashit( $this->_session->data['backupItemFolder'] ) . 'snapshot-backup.zip';

					if ( "PclZip" === $this->config_data['config']['zipLibrary'] ) {

						$zipArchive = new PclZip( $backupZipFile );
						try {
							$zip_add_ret = $zipArchive->add(
									$this->_session->data['files_data']['included'][ $file_data_key ],
									PCLZIP_OPT_REMOVE_PATH, $home_path,
									PCLZIP_OPT_ADD_PATH, 'www',
									PCLZIP_OPT_TEMP_FILE_THRESHOLD, 10,
									PCLZIP_OPT_ADD_TEMP_FILE_ON
								);
							if ( ! $zip_add_ret ) {
								$error_status['errorStatus'] = true;
								$error_status['errorText'] = "ERROR: PcLZIP file:" . $file_data_key . " add failed " .
								                             $zipArchive->errorCode() . ": " . $zipArchive->errorInfo();

								return $error_status;
							}
						} catch ( Exception $e ) {
							$error_status['errorStatus'] = true;
							$error_status['errorText'] = "ERROR: PclZIP file:" . $file_data_key . " : add failed : " .
							                             $zipArchive->errorCode() . ": " . $zipArchive->errorInfo();

							return $error_status;
						}

					} else if ( "ZipArchive" === $this->config_data['config']['zipLibrary'] ) {
						$zipArchive = new ZipArchive();
						if ( $zipArchive ) {
							if ( ! file_exists( $backupZipFile ) ) {
								$zip_flags = ZIPARCHIVE::CREATE;
							} else {
								$zip_flags = null;
							}
							$zip_hdl = $zipArchive->open( $backupZipFile, $zip_flags );
							if ( true !== $zip_hdl ) {
								$error_status['errorStatus'] = true;
								$error_status['errorText'] = "ERROR: ZipArchive file:" . $file_data_key . " : add failed: " .
								                             ZipArchiveStatusString( $zip_hdl );

								return $error_status;
							}
							$fileCount = 0;
							$limit_files_per_session = apply_filters( 'snapshot_limit_of_files_per_session', 200 );
							foreach ( $this->_session->data['files_data']['included'][ $file_data_key ] as $file ) {
								$file = str_replace( '\\', '/', $file );
								$zipArchive->addFile( $file, str_replace( $home_path, 'www/', $file ) );

								// Per some PHP documentation.
								/*
									When a file is set to be added to the archive, PHP will attempt to lock the file and it is
									only released once the ZIP operation is done. In short, it means you can first delete an
									added file after the archive is closed. Related to this there is a limit to the number of
									files that can be added at once. So we are setting a limit of 200 files per add session (by default).
									Then we close the archive and re-open.
								*/
								$fileCount++;
								if ( $limit_files_per_session > 0 && $fileCount >= $limit_files_per_session ) {
									$zipArchive->close();
									$zip_hdl = $zipArchive->open( $backupZipFile, $zip_flags );
									$fileCount = 0;
								}
							}
							$zipArchive->close();
						}
					}
					if ( isset( $zipArchive ) ) {
						unset( $zipArchive );
					}

					foreach ( $this->_session->data['files_data']['included'][ $file_data_key ] as $idx => $filename ) {
						$filename = str_replace( $home_path, '', $filename );
						$this->snapshot_logger->log_message( "file: " . $filename );
					}

				}
			}

			return $error_status;
		}

		/**
		 * This 'finish' process is called from JS when all selected tables have been archived. This process
		 * renames the session backup file to the final location and writes an activity log record.
		 *
		 * @since 1.0.0
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */

		public function snapshot_ajax_backup_finish( $item, $_post_array ) {

			global $wpdb;

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";

			$manifest_array = array();

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			if ( isset( $this->_session->data['backupItemFolder'] ) ) {

				$item_key = $item['timestamp'];
				if ( isset( $_post_array['snapshot-data-item'] ) ) {
					$data_item_key = intval( $_post_array['snapshot-data-item'] );
				} else {
					$data_item_key = time();
				}

				$data_item = array();
				$data_item['timestamp'] = $data_item_key;

				if ( isset( $item['tables-option'] ) ) {
					$data_item['tables-option'] = $item['tables-option'];
				}

				if ( isset( $this->_session->data['tables_sections'] ) ) {
					$data_item['tables-sections'] = $this->_session->data['tables_sections'];
					$item['tables-sections'] = $this->_session->data['tables_sections'];
				}

				if ( isset( $item['files-option'] ) ) {
					$data_item['files-option'] = $item['files-option'];
				}

				if ( "all" === $data_item['files-option'] ) {

					if ( is_main_site( $item['blog-id'] ) ) {
						$data_item['files-sections'] = array( 'themes', 'plugins', 'media' );
						if ( is_multisite() ) {
							$data_item['files-sections'][] = 'mu-plugins';
						}
					} else {
						$data_item['files-sections'] = array( 'media' );
					}

				} else if ( "selected" === $data_item['files-option'] ) {

					if ( is_main_site( $item['blog-id'] ) ) {
						if ( isset( $item['files-sections'] ) ) {
							$data_item['files-sections'] = $item['files-sections'];
						}
					} else {
						$data_item['files-sections'] = '';
					}
				}

				if ( isset( $this->_session->data['files_data']['included'] ) ) {

					$session_files_data = array();
					foreach ( $this->_session->data['files_data']['included'] as $files_section => $files_set ) {

						if ( "plugins" === $files_section ) {
							if ( ! function_exists( 'get_plugins' ) ) {
								require_once ABSPATH . 'wp-admin/includes/plugin.php';
							}

							$manifest_array['FILES-DATA-THEMES-PLUGINS'] = get_plugins();
						} else if ( "themes" === $files_section ) {
							$themes = wp_get_themes();
							$manifest_array['FILES-DATA-THEMES'] = get_plugins();
						}

						$session_files_data = array_merge( $session_files_data, $files_set );
					}

					$item['files-count'] = count( $session_files_data );
					$data_item['files-count'] = count( $session_files_data );
				} else {
					$item['files-count'] = 0;
					$data_item['files-count'] = 0;
				}

				// If the master item destination is not empty, means we are connected to some external system (FTP, S3, Dropbox)
				if ( ( empty( $item['destination'] ) ) || ( "local" === $item['destination'] ) ) {
					// Else if the master item destination is empty..
					$data_item['destination'] = 'local';

					// We assume the local archive folder for this item is set to something non-standard.
					if ( ( isset( $item['destination-directory'] ) ) && ( strlen( $item['destination-directory'] ) ) ) {
						$data_item['destination-directory'] = $item['destination-directory'];
					} else {
						$data_item['destination-directory'] = '';
					}
				} else {
					// In that case we don't want to set the destination and path until the file has been transmitted.
					$data_item['destination'] = '';
					$data_item['destination-directory'] = '';
				}

				if ( isset( $item['destination-sync'] ) ) {
					$data_item['destination-sync'] = $item['destination-sync'];
				}

				if ( isset( $this->_session->data['snapshot_time_start'] ) ) {
					$data_item['time-start'] = $this->_session->data['snapshot_time_start'];
					$data_item['time-end'] = time();

					unset( $this->_session->data['snapshot_time_start'] );
				}

				$manifest_array['SNAPSHOT_VERSION'] = $this->_settings['SNAPSHOT_VERSION'];

				if ( ( ( ! isset( $item['blog-id'] ) ) && ( empty( $item['blog-id'] ) ) ) && ( isset( $_post_array['snapshot-blog-id'] ) ) ) {
					$item['blog-id'] = intval( $_post_array['snapshot-blog-id'] );
				}

				$manifest_array['WP_BLOG_ID'] = $item['blog-id'];

				if ( is_multisite() ) {
					$manifest_array['WP_MULTISITE'] = 1;

					if ( is_main_site( intval( $item['blog-id'] ) ) ) {
						$manifest_array['WP_MULTISITE_MAIN_SITE'] = 1;
					}

					$manifest_array['WP_HOME'] = get_blog_option( intval( $item['blog-id'] ), 'home' );
					$manifest_array['WP_SITEURL'] = get_blog_option( intval( $item['blog-id'] ), 'siteurl' );

					$blog_details = get_blog_details( intval( $item['blog-id'] ) );
					if ( isset( $blog_details->blogname ) ) {
						$manifest_array['WP_BLOG_NAME'] = $blog_details->blogname;
					}

					if ( isset( $blog_details->domain ) ) {
						$manifest_array['WP_BLOG_DOMAIN'] = $blog_details->domain;
					}

					if ( isset( $blog_details->path ) ) {
						$manifest_array['WP_BLOG_PATH'] = $blog_details->path;
					}

					if ( defined( 'UPLOADBLOGSDIR' ) ) {
						$manifest_array['WP_UPLOADBLOGSDIR'] = UPLOADBLOGSDIR;
					}

					// We can't use the 'UPLOADS' defined because it is set via the live site and does ot changes when using switch blog
					//if ( defined( 'UPLOADS' ) ) {
					//	$manifest_array['WP_UPLOADS'] = UPLOADS;
					//}

				} else {
					$manifest_array['MULTISITE'] = 0;
					$manifest_array['WP_HOME'] = get_option( 'home' );
					$manifest_array['WP_BLOG_NAME'] = get_option( 'blogname' );

					$home_url_parts = wp_parse_url( $manifest_array['WP_HOME'] );
					if ( isset( $home_url_parts['host'] ) ) {
						$manifest_array['WP_BLOG_DOMAIN'] = $home_url_parts['host'];
					}
					if ( isset( $home_url_parts['path'] ) ) {
						$manifest_array['WP_BLOG_PATH'] = $home_url_parts['path'];
					}

					$manifest_array['WP_SITEURL'] = get_option( 'siteurl' );
				}
				global $wp_version, $wp_db_version;

				$manifest_array['WP_VERSION'] = $wp_version;
				$manifest_array['WP_DB_VERSION'] = $wp_db_version;

				$manifest_array['WP_DB_NAME'] = Snapshot_Helper_Utility::get_db_name();
				$manifest_array['WP_DB_BASE_PREFIX'] = $wpdb->base_prefix;
				$manifest_array['WP_DB_PREFIX'] = $wpdb->get_blog_prefix( intval( $item['blog-id'] ) );
				$manifest_array['WP_DB_CHARSET_COLLATE'] = $wpdb->get_charset_collate();
				$manifest_array['WP_UPLOAD_PATH'] = Snapshot_Helper_Utility::get_blog_upload_path( intval( $item['blog-id'] ), 'basedir' );

				$manifest_array['WP_UPLOAD_URLS'] = Snapshot_Helper_Utility::get_blog_upload_path( intval( $item['blog-id'] ), 'baseurl' );
				//if (is_multisite()) && (!is_main_site()) {
				//$manifest_array['WP_UPLOAD_URL_UNFILTERED'] = snapshot_utility_get_blog_upload_path(intval($item['blog-id']), 'baseurl', false);
				//}

				$manifest_array['SEGMENT_SIZE'] = intval( $this->config_data['config']['segmentSize'] );

				$item_tmp = $item;
				if ( isset( $item_tmp['data'] ) ) {
					unset( $item_tmp['data'] );
				}
				$item_tmp['data'] = array();

				$item_tmp['data'][ $data_item_key ] = $data_item;
				$manifest_array['ITEM'] = $item_tmp;

				if ( isset( $this->_session->data['tables_sections'] ) ) {
					//fwrite($fp, "TABLES:". serialize($this->_session->data['tables_sections']) ."\r\n");
					$manifest_array['TABLES'] = $this->_session->data['tables_sections'];
				}

				if ( isset( $this->_session->data['table_data'] ) ) {
					//fwrite($fp, "TABLES-DATA:". serialize($this->_session->data['table_data']) ."\r\n");
					$manifest_array['TABLES-DATA'] = $this->_session->data['table_data'];
				}

				if ( isset( $session_files_data ) ) {
					// We want to remove the ABSPATH from the stored file items.

					foreach ( $session_files_data as $file_item_idx => $file_item ) {
						$session_files_data[ $file_item_idx ] = str_replace( $home_path, '', $file_item );
					}
					//fwrite($fp, "FILES-DATA:". serialize($this->_session->data['files_data']) ."\r\n");
					//$manifest_array['FILES-DATA'] = $session_files_data;
				}

				// Let's actually create the zip file from the files_array. We strip off the leading path (3rd param)
				$backup_zip_file = trailingslashit( $this->_session->data['backupItemFolder'] ) . 'snapshot-backup.zip';
				//if (file_exists($backupZipFile)) {

				/* Create a zip manifest file */
				$manifestFile = trailingslashit( $this->_session->data['backupItemFolder'] ) . 'snapshot_manifest.txt';
				if ( Snapshot_Helper_Utility::create_archive_manifest( $manifest_array, $manifestFile ) ) {

					$archiveFiles = array();
					$archiveFiles[] = $manifestFile;

					// Let's actually create the zip file from the files_array. We strip off the leading path (3rd param)
					$backup_zip_file = trailingslashit( $this->_session->data['backupItemFolder'] ) . 'snapshot-backup.zip';

					if ( "PclZip" === $this->config_data['config']['zipLibrary'] ) {

						$zipArchive = new PclZip( $backup_zip_file );
						$zipArchive->add(
								$archiveFiles,
								PCLZIP_OPT_REMOVE_PATH, $this->_session->data['backupItemFolder'],
								PCLZIP_OPT_TEMP_FILE_THRESHOLD, 10,
								PCLZIP_OPT_ADD_TEMP_FILE_ON
							);
						unset( $zipArchive );

					} else if ( "ZipArchive" === $this->config_data['config']['zipLibrary'] ) {
						$zipArchive = new ZipArchive();
						if ( $zipArchive ) {
							if ( ! file_exists( $backup_zip_file ) ) {
								$zip_flags = ZIPARCHIVE::CREATE;
							} else {
								$zip_flags = null;
							}
							$zip_hdl = $zipArchive->open( $backup_zip_file, $zip_flags );
							if ( true !== $zip_hdl ) {
								$error_status['errorStatus'] = true;

								$error_status['errorText'] = "ERROR: ZipArchive file:" . baename( $manifestFile ) . " : add failed: " .
								                             ZipArchiveStatusString( $zip_hdl );

								return $error_status;
							}

							foreach ( $archiveFiles as $file ) {
								$file = str_replace( '\\', '/', $file );
								$zipArchive->addFile( $file, str_replace( $this->_session->data['backupItemFolder'] . '/', '', $file ) );
							}
							$zipArchive->close();
						}
					}

					foreach ( $archiveFiles as $archiveFile ) {
						if ( is_writable( $archiveFile ) )
							unlink( $archiveFile );
					}
				}

				$checksum = Snapshot_Helper_Utility::get_file_checksum( $backup_zip_file );

				$date_key = date( 'ymd-His', $data_item_key ); // This timestamp format is used for the filename on disk.

				$filename_prefix = sanitize_file_name( strtolower( $item['name'] ) );
				if ( ! $filename_prefix ) {
					$filename_prefix = 'snapshot';
				}
				$backup_zip_filename = sprintf( '%s-%s-%s-%s.zip', $filename_prefix, $item_key, $date_key, $checksum );

				$data_item['filename'] = $backup_zip_filename;
				$data_item['file_size'] = filesize( $backup_zip_file );

				//$backupZipFolder = $this->snapshot_get_item_destination_path($item, $data_item, true);

				if ( ( empty( $data_item['destination'] ) ) || ( "local" === $data_item['destination'] ) ) {
					$backup_zip_folder = $this->snapshot_get_item_destination_path( $item, $data_item, true );
					$this->snapshot_logger->log_message( 'backupZipFolder[' . $backup_zip_folder . ']' );

					if ( empty( $backup_zip_folder ) ) {
						$backup_zip_folder = $this->_settings['backupBaseFolderFull'];
					}
				} else {
					$backup_zip_folder = $this->_settings['backupBaseFolderFull'];
				}

				$backupZipFileFinal = trailingslashit( $backup_zip_folder ) . $backup_zip_filename;
				if ( is_writable( $backupZipFileFinal ) ) {
					unlink( $backupZipFileFinal );
				}

				$this->snapshot_logger->log_message( 'rename: backupZipFile[' . $backup_zip_file . '] backupZipFileFinal[' . $backupZipFileFinal . ']' );

				// Remove the destination file if it exists. If should not but just in case.
				if ( is_writable( $backupZipFileFinal ) ) {
					unlink( $backupZipFileFinal );
				}

				//$rename_ret = @rename($backupZipFile, $backupZipFileFinal);
				$rename_ret = rename( $backup_zip_file, $backupZipFileFinal );
				if ( false === $rename_ret ) {
					//$this->snapshot_logger->log_message('rename: failed: error:'. print_r(error_get_last(), true) .'');

					// IF for some reason the destination path is not our default snapshot backups folder AND we could not not rename to that
					// alternate path. We then try the default snapshot destination.
					if ( trailingslashit( $this->_settings['backupBaseFolderFull'] ) !== trailingslashit( dirname( $backupZipFileFinal ) ) ) {

						$backupZipFileTMP = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . basename( $backupZipFileFinal );
						$this->snapshot_logger->log_message( 'rename: backupZipFile[' . $backup_zip_file . '] backupZipFileFinal[' . $backupZipFileTMP . ']' );
						$rename_ret = rename( $backup_zip_file, $backupZipFileTMP );
						if ( false !== $rename_ret ) {
							$this->snapshot_logger->log_message( 'rename: success' );
							$error_status['responseFile'] = basename( $backupZipFileFinal );

							$data_item['destination-directory'] = '';
						}
					}
				} else {
					$error_status['responseFile'] = basename( $backupZipFileFinal );
				}

				$error_status['responseFile'] = basename( $backupZipFileFinal );

				// echo out the finished message so the user knows we are done.
				$snapshot_url = $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_snapshots';
				$error_status['responseText'] = __( "Your snapshot has been successfully created and stored!", SNAPSHOT_I18N_DOMAIN ) . "<br />" . '<a href="' . $snapshot_url . '&amp;snapshot-action=view&amp;item=' . $item_key . '&amp;snapshot-noonce-field=' . wp_create_nonce  ( 'snapshot-nonce' ) . '">' . __( "View Snapshot", SNAPSHOT_I18N_DOMAIN ) . '</a>';

				//}

				if ( ! isset( $item['data'] ) ) {
					$item['data'] = array();
				}

				// Add the file entry to the data section of out snapshot item
				$item['data'][ $data_item_key ] = $data_item;
				ksort( $item['data'] );

				$this->config_data['items'][ $item_key ] = $item;

				if ( ( isset( $this->_session->data['tables_sections'] ) )
				     && ( isset( $this->_session->data['table_data'] ) ) && ( count( $this->_session->data['table_data'] ) )
				) {
					if ( ! isset( $this->config_data['config']['tables_last'][ $item['blog-id'] ] ) ) {
						$this->config_data['config']['tables_last'][ $item['blog-id'] ] = array();
					}

					$this->config_data['config']['tables_last'][ $item['blog-id'] ] = $this->_session->data['tables_sections'];
				}

				//unset($this->_session->data);

				return $error_status;
			}
		}

		/**
		 * AJAX callback function from the snapshot add new form. Used to update the blog tables listing
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param $_POST ['blog_id'] designates the blog to show tables for.
		 *
		 * @return JSON formatted array of tables. This is a multi-dimensional array containing groups for 'wp' - WordPress core, 'other' - Non core tables
		 */

		public function snapshot_ajax_show_blog_tables() {
			check_ajax_referer( 'snapshot-ajax-nonce', 'security' );
			global $wpdb;

			//echo "POST<pre>"; print_r($_POST); echo "</pre>";
			$blog_id = 0;
			$json_data = array();

			if ( isset( $_POST['snapshot_blog_id_search'] ) ) {
				$snapshot_blog_id_search = esc_attr( $_POST['snapshot_blog_id_search'] );
				$PHP_URL_SCHEME = wp_parse_url( $snapshot_blog_id_search, PHP_URL_SCHEME );

				if ( ! empty( $PHP_URL_SCHEME ) ) {
					$snapshot_blog_id_search = str_replace( $PHP_URL_SCHEME . "://", '', $snapshot_blog_id_search );
				}

				if ( is_numeric( trim( $snapshot_blog_id_search ) ) ) {
					$blog_id = intval( $snapshot_blog_id_search );
				} else {

					global $wpdb;

					if ( defined( 'DOMAIN_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE ) {
						$current_domain = DOMAIN_CURRENT_SITE;
					} else {
						$current_domain = apply_filters( 'snapshot_current_domain', DOMAIN_CURRENT_SITE );
					}

					$current_path = apply_filters( 'snapshot_current_path', PATH_CURRENT_SITE );

					if ( is_subdomain_install() ) {
						if ( ! empty( $snapshot_blog_id_search ) ) {
							$full_domain = $snapshot_blog_id_search . "." . $current_domain;
							// $full_domain = $snapshot_blog_id_search .".". $current_domain.$current_path;
						} else {
							$full_domain = $current_domain;
							// $full_domain = $current_domain.current_path;
						}
						$sql_str = $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s LIMIT 1", $full_domain );
					} else {
						$snapshot_blog_id_search_path = trailingslashit( $snapshot_blog_id_search );
						if ( '/' === $snapshot_blog_id_search_path ) {
							$snapshot_blog_id_search_path = '';
						}

						$sql_str = $wpdb->prepare(
								"SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s LIMIT 1",
								$current_domain, $current_path . $snapshot_blog_id_search_path
							);
					}

					//echo "sql_str=[". $sql_str ."]<br />";
					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$blog = $wpdb->get_row( $sql_str );
					if ( ( isset( $blog->blog_id ) ) && ( intval( $blog->blog_id ) > 0 ) ) {
						$blog_id = intval( $blog->blog_id );
					} else if ( ! $blog ) {
						if ( ( function_exists( 'is_plugin_active' ) ) && ( is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) ) {
							$sql_str = $wpdb->prepare(
									"SELECT blog_id FROM " . $wpdb->prefix . "domain_mapping WHERE domain = %s LIMIT 1",
									$snapshot_blog_id_search
								);
							//echo "sql_str=[". $sql_str ."]<br />";
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$blog = $wpdb->get_row( $sql_str );
							if ( ( isset( $blog->blog_id ) ) && ( intval( $blog->blog_id ) > 0 ) ) {
								$blog_id = intval( $blog->blog_id );
							}
						}
					}
				}
			}

			if ( $blog_id > 0 ) {
				$json_data['blog'] = get_blog_details( $blog_id );

				if ( ( function_exists( 'is_plugin_active' ) ) && ( is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) ) {
					$sql_str = $wpdb->prepare(
							"SELECT domain FROM " . $wpdb->prefix . "domain_mapping WHERE blog_id = %d AND active=1 LIMIT 1",
							$blog_id
						);
					//echo "sql_str=[". $sql_str ."]<br />";

					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$mapped_domain = $wpdb->get_row( $sql_str );
					if ( ( isset( $mapped_domain->domain ) ) && ( ! empty( $mapped_domain->domain ) ) ) {
						$json_data['mapped_domain'] = $mapped_domain->domain;
					}
				}

				$tables = Snapshot_Helper_Utility::get_database_tables( $blog_id );
				if ( $tables ) {

					/* Grab the last set of tables for this blog_id */
					$last_tables = array();
					if ( isset( $this->config_data['config']['tables_last'][ $blog_id ] ) ) {
						$last_tables = $this->config_data['config']['tables_last'][ $blog_id ];
					}

					foreach ( $tables as $table_key => $table_set ) {

						foreach ( $table_set as $table_name => $table_val ) {

							/* If this table was in the last_tables for this blog set the value to on so it will be checked for the user */
							if ( array_search( $table_name, $last_tables, true ) !== false ) {
								$table_set[ $table_name ] = "checked";
							} else {
								$table_set[ $table_name ] = "";
							}
						}
						ksort( $table_set );
						$tables[ $table_key ] = $table_set;
					}
					$json_data['tables'] = $tables;

					$upload_path = Snapshot_Helper_Utility::get_blog_upload_path( $blog_id );
					$json_data['upload_path'] = $upload_path;

					if ( is_multisite() ) {
						if ( is_main_site( $blog_id ) ) {
							$json_data['is_main_site'] = "YES";
						} else {
							$json_data['is_main_site'] = "NO";
						}

					} else {
						$json_data['is_main_site'] = "YES";
					}
				}
			}
			echo wp_json_encode( $json_data );
			die();
		}

		public function snapshot_get_blog_restore_info() {
			check_ajax_referer( 'snapshot-ajax-nonce', 'security' );
			global $wpdb;

			$blog_id = 0;
			$json_data = array();

			if ( ! isset( $_POST['snapshot_blog_id_search'] ) ) {
				return;
			}

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );
			$snapshot_blog_id_search = sanitize_text_field( $_POST['snapshot_blog_id_search'] );
			$PHP_URL_SCHEME = wp_parse_url( $snapshot_blog_id_search, PHP_URL_SCHEME );

			if ( ! empty( $PHP_URL_SCHEME ) ) {
				$snapshot_blog_id_search = str_replace( $PHP_URL_SCHEME . "://", '', $snapshot_blog_id_search );
			}

			if ( is_numeric( $snapshot_blog_id_search ) ) {
				$blog_id = intval( $snapshot_blog_id_search );
			} else {

				$current_domain = apply_filters( 'snapshot_current_domain', DOMAIN_CURRENT_SITE );
				$current_path = apply_filters( 'snapshot_current_path', PATH_CURRENT_SITE );

				if ( is_subdomain_install() ) {
					if ( ! empty( $snapshot_blog_id_search ) ) {
						// $full_domain = $snapshot_blog_id_search . "." . $current_domain . $current_path;
						$full_domain = $snapshot_blog_id_search . "." . $current_domain;
					} else {
						// $full_domain = $current_domain . $current_path;
						$full_domain = $current_domain;
					}

					$sql_str = $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s LIMIT 1", $full_domain );
				} else {
					$snapshot_blog_id_search_path = trim( $snapshot_blog_id_search, '/\\' ) . '/';
					if ( '/' === $snapshot_blog_id_search_path ) {
						$snapshot_blog_id_search_path = '';
					}

					$sql_str = $wpdb->prepare(
							"SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s LIMIT 1",
							$current_domain, $current_path . $snapshot_blog_id_search_path
						);
				}
				// We are using placeholders and $wpdb->prepare() inside the variable.
				// phpcs:ignore
				$blog = $wpdb->get_row( $sql_str );

				if ( isset( $blog->blog_id ) && intval( $blog->blog_id ) > 0 ) {
					$blog_id = intval( $blog->blog_id );
				} else if ( ! $blog ) {
					if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) {
						$sql_str = $wpdb->prepare(
							"SELECT blog_id FROM {$wpdb->prefix}domain_mapping WHERE domain = %s LIMIT 1",
							$snapshot_blog_id_search
						);
						// We are using placeholders and $wpdb->prepare() inside the variable.
						// phpcs:ignore
						$blog = $wpdb->get_row( $sql_str );
						if ( isset( $blog->blog_id ) && intval( $blog->blog_id ) > 0 ) {
							$blog_id = intval( $blog->blog_id );
						}
					}
				}
			}

			if ( $blog_id <= 0 ) {
				return;
			}
			$json_data['blog'] = get_blog_details( $blog_id );

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'domain-mapping/domain-mapping.php' ) ) {
				$sql_str = $wpdb->prepare(
						"SELECT domain FROM {$wpdb->prefix}domain_mapping WHERE blog_id = %d AND active=1 LIMIT 1",
						$blog_id
					);
				// We are using placeholders and $wpdb->prepare() inside the variable.
				// phpcs:ignore
				$mapped_domain = $wpdb->get_row( $sql_str );
				if ( ! empty( $mapped_domain->domain ) ) {
					$json_data['mapped_domain'] = $mapped_domain->domain;
				}
			}

			switch_to_blog( intval( $blog_id ) );

			$json_data['WP_DB_BASE_PREFIX'] = $wpdb->base_prefix;
			$json_data['WP_DB_PREFIX'] = $wpdb->get_blog_prefix( $blog_id );
			$json_data['WP_DB_NAME'] = Snapshot_Helper_Utility::get_db_name();
			$json_data['WP_DB_CHARSET_COLLATE'] = $wpdb->get_charset_collate();

			$uploads = wp_upload_dir();

			if ( isset( $uploads['basedir'] ) ) {
				$uploads['basedir'] = str_replace( '\\', '/', $uploads['basedir'] );
				$json_data['WP_UPLOAD_PATH'] = str_replace( $home_path, '', $uploads['basedir'] );
			}

			restore_current_blog();

			echo wp_json_encode( $json_data );
			die();
		}

		/**
		 * AJAX callback function from snapshots page to disable notification.
		 *
		 * @since 3.1
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_ajax_disable_notif_proc() {
			check_ajax_referer( 'snapshot-disable-notif', 'security' );
			if ( current_user_can( "manage_options" ) ) {
				update_option( 'snapshot-disable_notif_snapshot_page', time() );
			}
			die();
		}

		/**
		 * AJAX callback function from snapshots page to disable notification.
		 *
		 * @since 3.1
		 * @see
		 *
		 * @param none
		 *
		 * @return
		 */
		public function snapshot_save_key_proc() {

			if ( is_multisite() ) {
				if ( ! is_super_admin() ) {
					return false;
				}
				if ( ! is_network_admin() ) {
					if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
						return false;
					}
				}
			}

			check_ajax_referer( 'snapshot-save-key', 'security' );
			$model = new Snapshot_Model_Full_Backup();
			$data = new Snapshot_Model_Post();

			if ( $data->is_empty() ) {
				wp_send_json_error();
			}

			if ( current_user_can( "manage_options" ) ) {

				// Do the secret key part first
				if ( $data->has( 'secret-key' ) ) {

					$key = sanitize_text_field( $_REQUEST['secret-key'] );

					if ( ! isset( $this->config_data['config']['secret-key'] ) || ( isset( $this->config_data['config']['secret-key'] ) && $key !== $this->config_data['config']['secret-key'] ) ) {
						$this->config_data['config']['secret-key'] = $key;
						$this->save_config();
					}

					$old_key = $model->get_config( 'secret-key', false );
					$model->set_config( 'secret-key', $key );
					if ( empty( $key ) || $key !== $old_key ) {
						$model->remote()->remove_token();
					}

					// Also stop cron when there's no secret key
					if ( empty( $key ) ) {
						$model->set_config( 'frequency', false );
						$model->set_config( 'schedule_time', false );
						$model->set_config( 'disable_cron', true );
						Snapshot_Controller_Full::get()->deactivate();
					}
				} else {
					wp_send_json_error();
				}

			} else {
				wp_send_json_error();
			}

			//Testing secret-key
			$token = Snapshot_Model_Full_Remote_Api::get()->get_token();

			if ( false === $token ) {
				$this->config_data['config']['secret-key'] = '';
				$this->save_config();
				$model->set_config( 'secret-key', '' );
				Snapshot_Controller_Full::get()->deactivate();
				// Send schedule update
				$model->update_remote_schedule();
				wp_send_json_error( 'wrong-key' );
			}

			// If we got this far, let's activate it too
			$model->set_config( 'active', true );

			// Send schedule update
			$model->update_remote_schedule();

			wp_send_json_success();

			die();
		}

		/**
		 * AJAX callback function from the snapshot restore form.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param none
		 *
		 * @return JSON formatted array status.
		 */

		public function snapshot_ajax_restore_proc() {
			check_ajax_referer( 'snapshot-ajax-nonce', 'security');
			// When zlib compression is turned on we get errors from this shutdown action setup by WordPress. So we disabled.
			$zlib_compression = ini_get( 'zlib.output_compression' );
			if ( $zlib_compression ) {
				remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
			}

			@ini_set( 'html_errors', 'Off' ); // phpcs:ignore
			@ini_set( 'zlib.output_compression', 'Off' ); // phpcs:ignore
			@set_time_limit( 0 ); // phpcs:ignore

			if ( isset( $_POST['item_key'] ) ) {
				$item_key = intval( $_POST['item_key'] );
			}

			if ( isset( $_POST['item_data'] ) ) {
				$data_item_key = intval( $_POST['item_data'] );
			}
			$this->snapshot_logger = new Snapshot_Helper_Logger( $this->_settings['backupLogFolderFull'], $item_key, $data_item_key );

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			$old_error_handler = set_error_handler( array( $this, 'snapshot_ErrorHandler' ) );

			Snapshot_Helper_Debug::set_error_reporting( $this->config_data['config']['errorReporting'] );

			if ( ( isset( $this->config_data['config']['memoryLimit'] ) ) && ( ! empty( $this->config_data['config']['memoryLimit'] ) ) ) {
				@ini_set( 'memory_limit', $this->config_data['config']['memoryLimit'] ); // phpcs:ignore
			}

			if ( ( isset( $item_key ) ) && ( isset( $data_item_key ) ) ) {

				if ( isset( $this->config_data['items'][ $item_key ] ) ) {
					$item = $this->config_data['items'][ $item_key ];

					/*
						$this->snapshot_logger->log_message("memory limit: ". ini_get('memory_limit') .
							": memory usage current: ". snapshot_utility_size_format(memory_get_usage(true)) .
							": memory usage peak: ". snapshot_utility_size_format(memory_get_peak_usage(true)) );
					*/
					switch ( sanitize_text_field( $_REQUEST['snapshot_action'] ) ) {
						case 'init':
							$this->snapshot_logger->log_message( 'restore: init' );

							// Start/load our sessions file
							$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key, true );

							ob_start();
							$error_array = $this->snapshot_ajax_restore_init( $item );
							$function_output = ob_get_contents();
							ob_end_clean();

							if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
								// We have a problem.

								// Not debug code. We use print_r() for logging purposes.
								$this->snapshot_logger->log_message( "init: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "init: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "init: _SESSION" . print_r( $this->_session->data, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "init: output:" . $function_output );

								$this->snapshot_logger->log_message(
                                     "memory limit: " . ini_get( 'memory_limit' ) .
								                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
								                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                    );

								echo wp_json_encode( $error_array );

								die();
							}

							break;

						case 'table':

							// Start/load our sessions file
							$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key );

							ob_start();
							$result = $this->snapshot_ajax_restore_table( $item );
							$error_array = $result;
							$function_output = ob_get_contents();
							ob_end_clean();
							if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
								// We have a problem.

								// Not debug code. We use print_r() for logging purposes.
								$this->snapshot_logger->log_message( "table: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "table: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "table: _SESSION" . print_r( $this->_session, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "table: output:" . $function_output );

								$this->snapshot_logger->log_message(
                                     "memory limit: " . ini_get( 'memory_limit' ) .
								                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
								                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                    );

								echo wp_json_encode( $error_array );

								die();
							}
							break;

						case 'file':

							// Start/load our sessions file
							$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key );

							ob_start();
							$error_array = $this->snapshot_ajax_restore_file( $item );
							$function_output = ob_get_contents();
							ob_end_clean();
							if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
								// We have a problem.

								// Not debug code. We use print_r() for logging purposes.
								$this->snapshot_logger->log_message( "file: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: _SESSION" . print_r( $this->_session, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: output:" . $function_output );

								$this->snapshot_logger->log_message(
                                     "memory limit: " . ini_get( 'memory_limit' ) .
								                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
								                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                    );

								echo wp_json_encode( $error_array );

								die();
							}
							break;

						case 'finish':

							$this->snapshot_logger->log_message( 'restore: finish:' );

							// Start/load our sessions file
							$this->_session = new Snapshot_Helper_Session( trailingslashit( $this->_settings['backupSessionFolderFull'] ), $item_key );

							ob_start();
							$error_array = $this->snapshot_ajax_restore_finish( $item );
							$function_output = ob_get_contents();
							ob_end_clean();
							if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
								// We have a problem.

								// Not debug code. We use print_r() for logging purposes.
								$this->snapshot_logger->log_message( "finish: _POST" . print_r( $_POST, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "finish: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "finish: _SESSION" . print_r( $this->_session, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "finish: output:" . $function_output );

								$this->snapshot_logger->log_message(
                                     "memory limit: " . ini_get( 'memory_limit' ) .
								                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
								                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                    );

								echo wp_json_encode( $error_array );

								die();
							}
							$this->snapshot_logger->log_message( "restore: memory_limit: " . ini_get( 'memory_limit' ) );

							break;

						default:
							break;
					}
					/*
						$this->snapshot_logger->log_message("memory limit: ". ini_get('memory_limit') .
							": memory usage current: ". snapshot_utility_size_format(memory_get_usage(true)) .
							": memory usage peak: ". snapshot_utility_size_format(memory_get_peak_usage(true)) );
					*/
				}
			}
			$this->save_config( true );

			if ( isset( $error_array ) ) {
				$blog_id = intval( $_POST['snapshot-blog-id'] );
				$error_array['restore_admin_url'] = esc_url( get_admin_url( $blog_id ) );
				$error_array['restore_site_url'] = esc_url( get_site_url( $blog_id ) );

				echo wp_json_encode( $error_array );
			}

			die();
		}

		/**
		 * AJAX callback function from the snapshot restore form. This is the first
		 * step of the restore. This step will unzip the archive and retrieve the
		 * the MANIFEST file content.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param none
		 *
		 * @return array JSON formatted array status.
		 */
		public function snapshot_ajax_restore_init( $item ) {
			global $wpdb, $current_blog;

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			// We have checked for nonces coming into the function.
			// phpcs:ignore
			if ( ! isset( $_POST['item_data'] ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" . __(
						"ERROR: The Snapshot missing 'item_data' key",
						SNAPSHOT_I18N_DOMAIN
					) . "</p>";

				return $error_status;
			}

			$item_data = intval( $_POST['item_data'] );

			if ( ! isset( $item['data'][ $item_data ] ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" .
					sprintf(
						__( "ERROR: The Snapshot incorrect 'item_data' [%d] key", SNAPSHOT_I18N_DOMAIN ),
						$item_data
					) .
				"</p>";

				return $error_status;
			}
			//$error_status['data'] = $item['data'];
			$data_item = $item['data'][ $item_data ];
			//echo "data_item<pre>"; print_r($data_item); echo "</pre>";

			$backupZipFolder = $this->snapshot_get_item_destination_path( $item, $data_item, false );
			//echo "backupZipFolder[". $backupZipFolder ."]<br />";
			//die();
			$restoreFile = trailingslashit( $backupZipFolder ) . $data_item['filename'];
			$error_status['restoreFile'] = $restoreFile;
			if ( ! file_exists( $restoreFile ) ) {
				$error_status_errorText = "<p>" . __(
						"ERROR: The Snapshot file not found:",
						SNAPSHOT_I18N_DOMAIN
					) . " " . $restoreFile . "</p>";

				$restoreFile = trailingslashit( $this->_settings['backupBaseFolderFull'] ) . $data_item['filename'];
				$error_status['restoreFile'] = $restoreFile;

				if ( ! file_exists( $restoreFile ) ) {
					$error_status['errorStatus'] = true;
					$error_status['errorText'] = $error_status_errorText . "<p>" . __(
							"ERROR: The Snapshot file not found:",
							SNAPSHOT_I18N_DOMAIN
						) . " " . $restoreFile . "</p>";

					return $error_status;
				}
			}

			// Create a unique folder for our restore processing. Will later need to remove it.
			$sessionRestoreFolder = trailingslashit( $this->_settings['backupRestoreFolderFull'] );
			wp_mkdir_p( $sessionRestoreFolder );
			if ( ! is_writable( $sessionRestoreFolder ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" . __(
						"ERROR: The Snapshot folder is not writeable. Check the settings",
						SNAPSHOT_I18N_DOMAIN
					) . " " . $sessionRestoreFolder . "</p>";

				return $error_status;
			}

			// Cleanup any files from a previous restore attempt
			$dh = opendir( $sessionRestoreFolder );
			if ( $dh ) {
				$file = readdir( $dh );
				while ( false !== $file ) {
					if ( ( '.' === $file ) || ( '..' === $file ) ) {
						$file = readdir( $dh );
						continue;
					}

					Snapshot_Helper_Utility::recursive_rmdir( $sessionRestoreFolder . $file );
					$file = readdir( $dh );
				}
				closedir( $dh );
			}

			if ( "PclZip" === $this->config_data['config']['zipLibrary'] ) {
				if ( ! defined( 'PCLZIP_TEMPORARY_DIR' ) ) {
					define( 'PCLZIP_TEMPORARY_DIR', trailingslashit( $this->_settings['backupBackupFolderFull'] ) . $item['timestamp'] . "/" );
				}
				if ( ! class_exists( 'class PclZip' ) ) {
					require_once  ABSPATH . '/wp-admin/includes/class-pclzip.php' ;
				}
				$zipArchive = new PclZip( $restoreFile );
				$zip_contents = $zipArchive->listContent();
				if ( $zip_contents ) {
					$extract_files = $zipArchive->extract( PCLZIP_OPT_PATH, $sessionRestoreFolder );
					if ( $extract_files ) {
						$this->_session->data['restoreFolder'] = $sessionRestoreFolder;
					}
				}

			} else {
				$zip = new ZipArchive();
				$res = $zip->open( $restoreFile );
				if ( true === $res ) {
					$extract_ret = $zip->extractTo( $sessionRestoreFolder );
					if ( false !== $extract_ret ) {
						$this->_session->data['restoreFolder'] = $sessionRestoreFolder;
					}
				}
			}

			$error_status['MANIFEST'] = array();
			$snapshot_manifest_file = trailingslashit( $sessionRestoreFolder ) . 'snapshot_manifest.txt';
			if ( file_exists( $snapshot_manifest_file ) ) {
				$error_status['MANIFEST'] = Snapshot_Helper_Utility::consume_archive_manifest( $snapshot_manifest_file );
				//unlink($snapshot_manifest_file);
			}

			//echo "item<pre>"; print_r($item); echo "</pre>";
			//echo "error_status<pre>"; print_r($error_status); echo "</pre>";
			//echo "_POST<pre>"; print_r($_POST); echo "</pre>";

			//switch_to_blog( $item['blog-id'] );
			$error_status['MANIFEST']['RESTORE']['SOURCE'] = array();
			$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_BLOG_ID'] = $error_status['MANIFEST']['WP_BLOG_ID'];
			$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'] = $error_status['MANIFEST']['WP_DB_PREFIX'];
			$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_BASE_PREFIX'] = $error_status['MANIFEST']['WP_DB_BASE_PREFIX'];
			$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_CHARSET_COLLATE'] = $error_status['MANIFEST']['WP_DB_CHARSET_COLLATE'];
			$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_SITEURL'] = $error_status['MANIFEST']['WP_SITEURL'];
			$error_status['MANIFEST']['RESTORE']['SOURCE']['UPLOAD_DIR'] = $error_status['MANIFEST']['WP_UPLOAD_PATH'];
			$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_UPLOAD_URLS'] = $error_status['MANIFEST']['WP_UPLOAD_URLS'];

			$error_status['MANIFEST']['RESTORE']['DEST'] = array();

			if ( is_multisite() ) {
				// phpcs:ignore
				switch_to_blog( $_POST['snapshot-blog-id'] );

				$error_status['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] = $_POST['snapshot-blog-id']; // phpcs:ignore
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_PREFIX'] = $wpdb->get_blog_prefix( $_POST['snapshot-blog-id'] ); // phpcs:ignore
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_BASE_PREFIX'] = $wpdb->base_prefix; // phpcs:ignore
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_CHARSET_COLLATE'] = $wpdb->get_charset_collate(); // phpcs:ignore
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] = get_site_url( $_POST['snapshot-blog-id'] ); // phpcs:ignore
				if ( empty( $error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] ) ) {
					if ( ! empty ( $_POST['snapshot_blog_search'] ) ) { // phpcs:ignore
						$error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] = network_site_url( '/' . untrailingslashit( $_POST['snapshot_blog_search'] ) . '/' ); // phpcs:ignore
					} else {
						$error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] = $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_SITEURL'];
					}
				}

			} else {
				$error_status['MANIFEST']['RESTORE']['DEST'] = array();
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] = $error_status['MANIFEST']['WP_BLOG_ID'];
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_PREFIX'] = $wpdb->prefix;
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_BASE_PREFIX'] = $wpdb->base_prefix;
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_CHARSET_COLLATE'] = $wpdb->get_charset_collate();
				$error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] = get_site_url( $error_status['MANIFEST']['WP_BLOG_ID'] );
				if ( empty( $error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] ) ) {
					$error_status['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] = $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_SITEURL'];
				}
			}

			$wp_upload_dir = wp_upload_dir();
			$error_status['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR'] = str_replace( '\\', '/', $wp_upload_dir['basedir'] );

			// phpcs:ignore
			if ( ! isset( $_POST['snapshot-tables-option'] ) ) {
				$_POST['snapshot-tables-option'] = "none";
			}

			// phpcs:ignore
			if ( "none" === $_POST['snapshot-tables-option'] ) {

				unset( $error_status['MANIFEST']['TABLES'] );
				$error_status['MANIFEST']['TABLES'] = array();

			} else if ( "selected" === $_POST['snapshot-tables-option'] ) { // phpcs:ignore

				// phpcs:ignore
				if ( isset( $_POST['snapshot-tables-array'] ) ) {
					// phpcs:ignore
					$error_status['MANIFEST']['TABLES'] = $_POST['snapshot-tables-array'];
				}

			} else if ( "all" === $_POST['snapshot-tables-option'] ) { // phpcs:ignore

				$manifest_tables = array();
				foreach ( $error_status['MANIFEST']['TABLES'] as $table_set_key => $table_set ) {

					// Per the instructions on the page. When selecting 'all' we do not include the global tables: users and usermeta
					if ( 'global' === $table_set_key ) {
						continue;
					}
					$manifest_tables = array_merge( $manifest_tables, array_values( $table_set ) );
				}
				//echo "manifest_tables<pre>"; print_r($manifest_tables); echo "</pre>";
				//die();

				$error_status['MANIFEST']['TABLES'] = $manifest_tables;
			}

			//echo "RESTORE<pre>"; print_r($error_status['MANIFEST']['RESTORE']); echo "</pre>";
			//echo "TABLES<pre>"; print_r($error_status['MANIFEST']['TABLES']); echo "</pre>";
			//echo "MANIFEST<pre>"; print_r($error_status['MANIFEST']); echo "</pre>";
			//echo "wpdb<pre>"; print_r($wpdb); echo "</pre>";
			//die();

			// upload_path wp-content/blogs.dir/7/files

			if ( ( isset( $error_status['MANIFEST']['TABLES'] ) ) && ( count( $error_status['MANIFEST']['TABLES'] ) ) ) {
				$tables_array = array();

				foreach ( $error_status['MANIFEST']['TABLES'] as $table_name ) {
					$table_info = array();
					$table_info['table_name'] = $table_name;

					if ( 0 === strncasecmp(
							$table_name, $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'],
							strlen( $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'] )
						)
					) {
						$table_info['table_name_base'] = str_replace( $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'], '', $table_name );

						$table_info['table_name_restore'] = $this->_settings['recover_table_prefix'] . str_replace(
								$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'],
								$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_PREFIX'],
								$table_name
                            );

						$table_name_dest = str_replace(
								$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'],
								$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_PREFIX'], $table_name
							);

					} else if ( 0 === strncasecmp(
							$table_name, $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_BASE_PREFIX'],
							strlen( $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_BASE_PREFIX'] )
						)
					) {
						$table_info['table_name_base'] = str_replace( $error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_BASE_PREFIX'], '', $table_name );

						$table_info['table_name_restore'] = $this->_settings['recover_table_prefix'] . str_replace(
								$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_BASE_PREFIX'],
								$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_BASE_PREFIX'],
								$table_name
                            );

						$table_name_dest = str_replace(
								$error_status['MANIFEST']['RESTORE']['SOURCE']['WP_DB_BASE_PREFIX'],
								$error_status['MANIFEST']['RESTORE']['DEST']['WP_DB_BASE_PREFIX'], $table_name
							);
					} else {
						// If the table name is not using the DB_PREFIX or DB_BASE_PREFIX then don't convert it.
						$table_info['table_name_base'] = $table_name;
						$table_info['table_name_restore'] = $table_name;
						$table_name_dest = $table_name;
					}

					$table_info['label'] = $table_name . " > " . $table_name_dest;
					$table_info['table_name_dest'] = $table_name_dest;

					$tables_array[ $table_name ] = $table_info;
				}
				$error_status['MANIFEST']['TABLES'] = $tables_array;
				//echo "MANIFEST<pre>"; print_r($error_status['MANIFEST']['TABLES']); echo "</pre>";
				//die();
			}

			if ( ( isset( $error_status['MANIFEST']['TABLES-DATA'] ) ) && ( count( $error_status['MANIFEST']['TABLES-DATA'] ) ) ) {
				$tables_data_sets = array();
				foreach ( $error_status['MANIFEST']['TABLES-DATA'] as $table_set ) {
					if ( ! isset( $table_set['table_name'] ) ) {
						continue;
					}
					//echo "table_set table_name[". $table_set['table_name'] ."]<br />";

					if ( array_key_exists( $table_set['table_name'], $error_status['MANIFEST']['TABLES'] ) !== false ) {
						$tables_data_sets[] = $table_set;
					}
				}
				$error_status['MANIFEST']['TABLES-DATA'] = $tables_data_sets;
			}
			//echo "MANIFEST<pre>"; print_r($error_status['MANIFEST']['TABLES']); echo "</pre>";
			//echo "MANIFEST<pre>"; print_r($error_status['MANIFEST']['TABLES-DATA']); echo "</pre>";
			//die();

			// phpcs:ignore
			if ( ! isset( $_POST['snapshot-files-option'] ) ) {
				$_POST['snapshot-files-option'] = "none";
			}

			// phpcs:ignore
			if ( "none" === $_POST['snapshot-files-option'] ) {

				unset( $error_status['MANIFEST']['FILES-DATA'] );
				$error_status['MANIFEST']['FILES-DATA'] = array();

			} else if ( "selected" === $_POST['snapshot-files-option'] ) { // phpcs:ignore
				// phpcs:ignore
				if ( isset( $_POST['snapshot-files-sections'] ) ) {
					// phpcs:ignore
					$error_status['MANIFEST']['FILES-DATA'] = $_POST['snapshot-files-sections'];
				}
			} else if ( "all" === $_POST['snapshot-files-option'] ) { // phpcs:ignore
				if ( isset( $error_status['MANIFEST']['ITEM']['data'] ) ) {
					$data_item = Snapshot_Helper_Utility::latest_data_item( $error_status['MANIFEST']['ITEM']['data'] );
					if ( isset( $data_item['files-sections'] ) ) {
						$error_status['MANIFEST']['FILES-DATA'] = array_values( $data_item['files-sections'] );

						$array_idx = array_search( 'config', $error_status['MANIFEST']['FILES-DATA'], true );
						if ( false !== $array_idx ) {
							unset( $error_status['MANIFEST']['FILES-DATA'][ $array_idx ] );
						}

						$array_idx = array_search( 'htaccess', $error_status['MANIFEST']['FILES-DATA'], true );
						if ( false !== $array_idx ) {
							unset( $error_status['MANIFEST']['FILES-DATA'][ $array_idx ] );
						}
					}
				}
			}

			//echo "_POST<pre>"; print_r($_POST); echo "</pre>";
			//echo "MANIFEST<pre>"; print_r($error_status['MANIFEST']); echo "</pre>";
			//echo "MANIFEST RESTORE<pre>"; print_r($error_status['MANIFEST']['RESTORE']); echo "</pre>";
			//die();

			$this->_session->data['MANIFEST'] = $error_status['MANIFEST'];

			return $error_status;
		}

		/**
		 * AJAX callback function from the snapshot restore form. This is the second
		 * step of the restore. This step will receives a table name to restore.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param array  $item from the snapshot history.
		 * @param string $_POST ['snapshot_table] send from AJAX for table name to restore.
		 *
		 * @return JSON formatted array status.
		 */

		public function snapshot_ajax_restore_table( $item ) {
			global $wpdb, $current_blog;

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";

			if ( ( is_multisite() ) && ( $current_blog->blog_id !== $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] ) ) {
				$wpdb->set_blog_id( $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] );
			}

			// We have checked for nonces coming into the function.
			// phpcs:ignore
			if ( ( isset( $_POST['snapshot_table'] ) ) && ( isset( $_POST['table_data'] ) ) ) {
				$this->snapshot_logger->log_message(
						'restore: table: ' . $_POST['snapshot_table'] . ' (' . $_POST['table_data']['segment_idx'] . '/' . // phpcs:ignore
						$_POST['table_data']['segment_total'] . ')' // phpcs:ignore
					);

				// phpcs:ignore
				$table_data = $_POST['table_data'];
				//echo "table_data<pre>"; print_r($table_data); echo "</pre>";
				$table_name = $table_data['table_name'];

				if ( isset( $this->_session->data['MANIFEST']['TABLES'][ $table_name ] ) ) {
					$table_set = $this->_session->data['MANIFEST']['TABLES'][ $table_name ];
				} else {
					echo wp_kses("table_set for [" . $table_name . "] not found<br />", array('br' => array()));
					echo "TABLES<pre>";
					// We use print_r() for easy support when restoration fails.
					print_r( $this->_session->data['MANIFEST']['TABLES'] ); //phpcs:ignore
					echo "</pre>";

					die();
					//return;
				}
				//echo "_POST<pre>"; print_r($_POST); echo "</pre>";
				//echo "MANIFEST<pre>"; print_r($this->_session->data['MANIFEST']); echo "</pre>";
				//die();

				// phpcs:ignore
				$restoreFile = trailingslashit( $this->_session->data['restoreFolder'] ) . esc_attr( $_POST['snapshot_table'] ) . ".sql";
				if ( file_exists( $restoreFile ) ) {
					global $wp_filesystem;

					if( Snapshot_Helper_Utility::connect_fs() ) {
						$backup_file_content_temp = $wp_filesystem->get_contents( $restoreFile );
						$backup_file_content = substr( $backup_file_content_temp, $_POST['table_data']['ftell_before'], $table_data['ftell_after'] - $table_data['ftell_before'] ); // phpcs:ignore

						// phpcs:ignore
						$source_table_name = $_POST['snapshot_table'];
						$dest_table_name = $table_set['table_name_restore'];

						if ( ( ! empty( $source_table_name ) ) && ( ! empty( $dest_table_name ) ) ) {
							$backup_file_content = str_replace( "`" . $source_table_name . "`", "`" . $dest_table_name . "`", $backup_file_content );
						}

						@set_time_limit( 300 ); // phpcs:ignore
						$backup_db = new Snapshot_Model_Database_Backup();
						$backup_db->restore_databases( $backup_file_content );

						// Check if there were any processing errors during the backup
						if ( count( $backup_db->errors ) ) {

							// If yes then append to our admin header error and return.
							foreach ( $backup_db->errors as $error ) {
								$this->_admin_header_error .= $error;
							}
						}
						unset( $backup_db );
					} else {
						$error_status['errorStatus'] = true;
						$error_status['errorText'] = "Cannot initialize filesystem";

						return $error_status;
					}

					//if ($table_data['rows_total'] == ($table_data['rows_start']+$table_data['rows_end'])) {
					//	$this->snapshot_ajax_restore_convert_db_content($table_data);
					//}

					if ( $table_data['segment_idx'] === $table_data['segment_total'] ) {
						//echo "table_data<pre>"; print_r($table_data); echo "</pre>";
						//die();

						$this->snapshot_ajax_restore_convert_db_content( $table_data );
					}

				} else {
					$error_status['errorStatus'] = true;
					$error_status['errorText'] = "<p>" . __(
							"ERROR: Unable to locate table restore file from archive: ",
							SNAPSHOT_I18N_DOMAIN
						) . " " . basename( $restoreFile ) . "</p>";

					return $error_status;
				}
			}
			// phpcs:ignore
			$error_status['table_data'] = $_POST['table_data'];

			return $error_status;
		}

		/**
		 * AJAX callback function from the snapshot restore form. This is the third
		 * step of the restore. This step will restore a single file to the original location.
		 *
		 * @since 1.0.7
		 * @see
		 *
		 * @param none
		 *
		 * @return JSON formatted array status.
		 */
		public function snapshot_ajax_restore_file( $item ) {

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "";

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			// We have checked for nonces coming into the function.
			// phpcs:ignore
			if ( ! isset( $_POST['file_data_idx'] ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" . __( "ERROR: The Snapshot missing 'file_data_idx' key", SNAPSHOT_I18N_DOMAIN ) . "</p>";

				return $error_status;

			} else {
				$file_data_idx = intval( $_POST['file_data_idx'] );
			}

			if ( ! isset( $this->_session->data['MANIFEST']['FILES-DATA'] ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" . __(
						"ERROR: The Snapshot missing session 'FILES-DATA' object.",
						SNAPSHOT_I18N_DOMAIN
					) . "</p>";

				return $error_status;
			}

			if ( ! isset( $this->_session->data['MANIFEST']['FILES-DATA'][ $file_data_idx ] ) ) {
				$error_status['errorStatus'] = true;
				$error_status['errorText'] = "<p>" .
					sprintf(
						__( "ERROR: The Snapshot missing restore file at idx [%d]", SNAPSHOT_I18N_DOMAIN ),
						$file_data_idx
					).
				"</p>";

				return $error_status;
			}

			$this->snapshot_logger->log_message( 'restore: file-section: ' . $this->_session->data['MANIFEST']['FILES-DATA'][ $file_data_idx ] );
			$restoreFilesBase = trailingslashit( $this->_session->data['restoreFolder'] ) . 'www/';
			$restoreFilesSet = array();

			$src_basedir = '';
			$dest_basedir = '';

			//echo "restoreFolder[". $this->_session->data['restoreFolder'] ."]<br />";
			//die();

			switch ( $this->_session->data['MANIFEST']['FILES-DATA'][ $file_data_idx ] ) {
				case 'themes':
					$restoreFilesPath = $restoreFilesBase . trailingslashit( $this->content_folder ) . "themes";
					if ( is_dir( $restoreFilesPath ) ) {
						$restoreFilesSet = Snapshot_Helper_Utility::scandir( $restoreFilesPath );
					}
					break;

				case 'plugins':
					$restoreFilesPath = $restoreFilesBase . $this->plugins_folder;

					if ( is_dir( $restoreFilesPath ) ) {

						// Make sure the Snapshot plugin is NOT restored.
						// We don't want to restore a different version which might break the restore processing. D'OH!
						$snapshot_plugin_dir = $restoreFilesPath . "/snapshot";
						Snapshot_Helper_Utility::recursive_rmdir( $snapshot_plugin_dir );

						$restoreFilesSet = Snapshot_Helper_Utility::scandir( $restoreFilesPath );
					}

					break;

				case 'mu-plugins':
					$restoreFilesPath = $restoreFilesBase . trailingslashit( $this->content_folder ) . "mu-plugins";

					if ( is_dir( $restoreFilesPath ) ) {

						// Make sure the Snapshot plugin is NOT restored.
						// We don't want to restore a different version which might break the restore processing. D'OH!
						$snapshot_plugin_dir = $restoreFilesPath . "/snapshot";
						Snapshot_Helper_Utility::recursive_rmdir( $snapshot_plugin_dir );

						$restoreFilesSet = Snapshot_Helper_Utility::scandir( $restoreFilesPath );
					}

					break;

				case 'media':
					$restoreFilesPath = $restoreFilesBase . $this->_session->data['MANIFEST']['WP_UPLOAD_PATH'];
					if ( is_dir( $restoreFilesPath ) ) {
						$restoreFilesSet = Snapshot_Helper_Utility::scandir( $restoreFilesPath );
					}
					break;

				case 'config':
					$wp_config_file = $restoreFilesBase . "wp-config.php";
					if ( file_exists( $wp_config_file ) ) {
						$restoreFilesSet[] = $wp_config_file;
					}
					break;

				case 'htaccess':
					$wp_htaccess_file = $restoreFilesBase . ".htaccess";
					if ( file_exists( $wp_htaccess_file ) ) {
						$restoreFilesSet[] = $wp_htaccess_file;
					}
					break;

				default:
					break;
			}

			if ( count( $restoreFilesSet ) ) {

				foreach ( $restoreFilesSet as $restoreFileFull ) {

					if ( 'media' === $this->_session->data['MANIFEST']['FILES-DATA'][ $file_data_idx ] ) {
						$uploads_source = $restoreFilesBase . $this->_session->data['MANIFEST']['RESTORE']['SOURCE']['UPLOAD_DIR'];
						$uploads_dest = $this->_session->data['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR'];
						$destinationFileFull = str_replace( $uploads_source, $uploads_dest, $restoreFileFull );
					} else {
						$file_relative = str_replace( $restoreFilesBase, '', $restoreFileFull );
						$destinationFileFull = trailingslashit( $home_path ) . $file_relative;
					}

					if ( file_exists( $destinationFileFull ) ) {
						unlink( $destinationFileFull );
						rename( $restoreFileFull, $destinationFileFull );
					} else {
						$currentFileDir = dirname( $destinationFileFull );
						if ( ! is_dir( $currentFileDir ) ) {
							if ( wp_mkdir_p( $currentFileDir ) === false ) {
								$error_status['errorStatus'] = true;
								$error_status['errorText'] =
									"<p>" . sprintf(
										__(
											'Unable to create directory %s. Make sure the parent folder is writeable.',
											SNAPSHOT_I18N_DOMAIN
										), $currentFileDir
									) . "</p>";

								return $error_status;
							}
						}
						rename( $restoreFileFull, $destinationFileFull );
					}
				}
			}
			$error_status['file_data'] = $this->_session->data['MANIFEST']['FILES-DATA'][ $file_data_idx ];

			return $error_status;
		}

		/**
		 * AJAX callback function from the snapshot restore form. This is the third
		 * step of the restore. This step will performs the cleanup of the unzipped
		 * archive and writes an entry to the activity log about the restore.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param none
		 *
		 * @return JSON formatted array status.
		 */

		public function snapshot_ajax_restore_finish( $item ) {
			$this->snapshot_ajax_restore_rename_restored_tables( $item );

			if ( is_multisite() ) {
				$this->snapshot_ajax_restore_convert_db_global_tables( $item );
			}

			// We have check for nonces coming into the function.
			// phpcs:ignore
			if ( isset( $_POST['snapshot_restore_theme'] ) ) {
				// phpcs:ignore
				$snapshot_restore_theme = esc_attr( $_REQUEST['snapshot_restore_theme'] );
				if ( $snapshot_restore_theme ) {
					$themes = Snapshot_Helper_Utility::get_blog_active_themes( $item['blog-id'] );
					if ( ( $themes ) && ( isset( $themes[ $snapshot_restore_theme ] ) ) ) {

						if ( is_multisite() ) {

							delete_blog_option( $item['blog-id'], 'current_theme' );
							add_blog_option( $item['blog-id'], 'current_theme', $themes[ $snapshot_restore_theme ] );

						} else {

							delete_option( 'current_theme' );
							add_option( 'current_theme', $themes[ $snapshot_restore_theme ] );
						}
					}
				}
			}

			// phpcs:ignore
			if ( ( isset( $_REQUEST['snapshot_restore_plugin'] ) ) && ( esc_attr( 'yes' === $_REQUEST['snapshot_restore_plugin'] )) ) {
				$_plugin_file = basename( dirname( __FILE__ ) ) . "/" . basename( __FILE__ );
				$_plugins = array( $_plugin_file );
				if ( is_multisite() ) {

					delete_blog_option( $item['blog-id'], 'active_plugins' );
					add_blog_option( $item['blog-id'], 'active_plugins', $_plugins );

				} else {

					delete_option( 'active_plugins' );
					add_option( 'active_plugins', $_plugins );
				}
			}

			// Cleanup any files from restore in case any files were left
			$dh = opendir( $this->_session->data['restoreFolder'] );
			if ( $dh ) {
				$file = readdir( $dh );
				while ( false !== $file ) {
					if ( ( '.' === $file ) || ( '..' === $file ) ) {
						$file = readdir( $dh );
						continue;
					}

					Snapshot_Helper_Utility::recursive_rmdir( $this->_session->data['restoreFolder'] . $file );
					$file = readdir( $dh );
				}
				closedir( $dh );
			}
			flush_rewrite_rules();

			$error_status = array();
			$error_status['errorStatus'] = false;
			$error_status['errorText'] = "";
			$error_status['responseText'] = "<p>" . __( "SUCCESS: Snapshot Restore complete! ", SNAPSHOT_I18N_DOMAIN ) . "</p>";

			return $error_status;
		}

		public function snapshot_ajax_restore_rename_restored_tables( $item ) {
			global $wpdb;

			$tables = array();
			$tables_sections = Snapshot_Helper_Utility::get_database_tables( $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] );
			$blog_prefix = $wpdb->get_blog_prefix( $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] );

			if ( $tables_sections ) {
				foreach ( $tables_sections as $_section => $_tables ) {
					if ( 'global' !== $_section ) {
						$tables = array_merge( $_tables, $tables );
					} else {
						// The 'global' tables will generally be set so the table name uses the base prefix. For restore we want to use
						// the blog prefix. Since we will need to process the rows and not replace the original file.
						if ( ! empty( $_tables ) ) {
							foreach ( $_tables as $_table ) {
								$table_dest = str_replace( $wpdb->base_prefix, $blog_prefix, $_table );
								if ( $table_dest !== $_table ) {
									if ( isset( $this->_session->data['MANIFEST']['TABLES'][ $_table ]['table_name_dest'] ) ) {
										$this->_session->data['MANIFEST']['TABLES'][ $_table ]['table_name_dest'] = $table_dest;
									}
								}
							}
						}
					}
				}
			}

			foreach ( $this->_session->data['MANIFEST']['TABLES'] as $table_set ) {

				if ( isset( $tables[ $table_set['table_name_restore'] ] ) ) {
					unset( $tables[ $table_set['table_name_restore'] ] );
				}

				if ( isset( $tables[ $table_set['table_name_dest'] ] ) ) {
					$sql_str = "DROP TABLE `" . $table_set['table_name_dest'] . "`;";
					$this->snapshot_logger->log_message( 'drop original table: ' . $sql_str );
					$wpdb->query( esc_sql( "DROP TABLE `{$table_set['table_name_dest']}`;" ) );
				}

				$sql_str = "ALTER TABLE `" . $table_set['table_name_restore'] . "` RENAME `" . $table_set['table_name_dest'] . "`;";
				$this->snapshot_logger->log_message( 'rename restored table: ' . $sql_str );
				$wpdb->query( esc_sql( "ALTER TABLE `{$table_set['table_name_restore']}` RENAME `{$table_set['table_name_dest']}`;" ) );
			}
		}

		public function snapshot_ajax_restore_convert_db_content( $table_data ) {
			global $wpdb;

			//echo "table_data<pre>"; print_r($table_data); echo "</pre>";
			if ( ! defined( 'SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE' ) ) {
				define( 'SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE', 500 );
			}

			if ( empty( $table_data ) ) {
				return;
			}

			if ( ! isset( $table_data['table_name'] ) ) {
				return;
			}
			$table_name = $table_data['table_name'];

			if ( isset( $this->_session->data['MANIFEST']['TABLES'][ $table_name ] ) ) {
				$table_set = $this->_session->data['MANIFEST']['TABLES'][ $table_name ];
			} else {
				return;
			}

			if ( ! isset( $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] ) ) {
				return;
			}
			if ( $this->_session->data['MANIFEST']['WP_HOME'] === $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_SITEURL']
			     && $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] === $this->_session->data['MANIFEST']['RESTORE']['SOURCE']['WP_BLOG_ID']
			) {
				return;
			}

			// We have checked for nonces coming into the function.
			// phpcs:ignore
			$blog_prefix = $wpdb->get_blog_prefix( $_POST['snapshot-blog-id'] );
			$_old_siteurl = str_replace( 'http://', '://', $this->_session->data['MANIFEST']['WP_HOME'] );
			$_old_siteurl = str_replace( 'https://', '://', $_old_siteurl );

			$_new_siteurl = str_replace( 'http://', '://', $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] );
			$_new_siteurl = str_replace( 'https://', '://', $_new_siteurl );

			//echo "_old_siteurl[". $_old_siteurl ."]<br />";
			//echo "_new_siteurl[". $_new_siteurl ."]<br />";
			//echo "MANIFEST<pre>"; print_r($this->_session->data['MANIFEST']); echo "</pre>";
			//die();

			$replacement_strs = array();

			// First we add the fill image path '://www.somesite.com/wp-content/uploads/sites/2/2012/10/image.gif
			$old_str = trailingslashit( $_old_siteurl ) . $this->_session->data['MANIFEST']['RESTORE']['SOURCE']['UPLOAD_DIR'];
			$new_str = trailingslashit( $_new_siteurl ) . $this->_session->data['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR'];
			//$old_str = $this->_session->data['MANIFEST']['RESTORE']['SOURCE']['UPLOAD_DIR'];
			//$new_str = $this->_session->data['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR'];
			$replacement_strs[ $old_str ] = $new_str;

			// If here we may have URLs in posts which are http://www.somesite.com/files/2012/10/image.gif instead of
			// http://www.somesite.com/wp-content/uploads/sites/2/2012/10/image.gif
			if ( ( ! defined( 'BLOGUPLOADDIR' ) )
			     && ( isset( $this->_session->data['MANIFEST']['WP_UPLOADBLOGSDIR'] ) ) && ( ! empty( $this->_session->data['MANIFEST']['WP_UPLOADBLOGSDIR'] ) )
			) {
				$old_str = trailingslashit( $_old_siteurl ) . 'files';
				$new_str = trailingslashit( $_new_siteurl ) . $this->_session->data['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR'];
				$replacement_strs[ $old_str ] = $new_str;
			}

			// Now add our base old/new domains as a final check.
			// $replacement_strs[$_old_siteurl] = $_new_siteurl;
			//echo "replacement_strs<pre>"; print_r($replacement_strs); echo "</pre>";
			//error_log(__FUNCTION__ .": replacement_strs<pre>". print_r($replacement_strs, true) ."</pre>");

			$this->snapshot_logger->log_message( 'restore: table: ' . $table_data['table_name'] . ' converting URLs from [' . $_old_siteurl . '] -> [' . $_new_siteurl . ']' );

			switch ( $table_set['table_name_base'] ) {

				case 'options':
					// Options table
					$limit_start = 0;
					$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

					$table_name_restore = $table_set['table_name_restore'];
					while ( true ) {

						$db_rows = $wpdb->get_results( $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore`" ) . " LIMIT %d, %d", $limit_start, $limit_end ) );
						if ( ! empty( $db_rows ) ) {

							foreach ( $db_rows as $row ) {
								//echo "row<pre>"; print_r($row); echo "</pre>";
								$new_value = $row->option_value;
								foreach ( $replacement_strs as $_old_str => $_new_str ) {
									$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
								}
								if ( $new_value !== $row->option_value ) {
									$sql_str = $wpdb->prepare(
											esc_sql( "UPDATE `$table_name_restore`" ) . " SET option_value=%s WHERE option_id=%d",
											$new_value, $row->option_id
										);
									//error_log(__FUNCTION__ .": sql[". $sql_str ."]");

									// We are using placeholders and $wpdb->prepare() inside the variable.
									// phpcs:ignore
									$wpdb->query( $sql_str );
								}
							}

							$limit_start = $limit_end;
							$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

						} else {
							break;
						}
					}

					// Options - user_roles
					$sql_str = $wpdb->prepare(
							esc_sql( "SELECT * FROM `$table_name_restore` " ) . " WHERE option_name=%s LIMIT 1",
							$this->_session->data['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'] . "user_roles"
						);
					//echo "sql_str=[". $sql_str ."]<br />";
					//error_log(__FUNCTION__ .": sql[". $sql_str ."]");

					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$db_row = $wpdb->get_row( $sql_str );
					//echo "db_row<pre>"; print_r($db_row); echo "</pre>";
					//error_log(__FUNCTION__ .": db_row<pre>". print_r($db_row, true). "</pre>");
					if ( ! empty( $db_row ) ) {
						$new_value = $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_DB_PREFIX'] . "user_roles";

						$sql_str = $wpdb->prepare(
								esc_sql( "UPDATE `$table_name_restore` " ) . " SET option_name=%s WHERE option_id=%d",
								$new_value, $db_row->option_id
							);
						//echo "sql_str=[". $sql_str ."]<br />";
						//error_log(__FUNCTION__ .": sql[". $sql_str ."]");

						// We are using placeholders and $wpdb->prepare() inside the variable.
						// phpcs:ignore
						$wpdb->query( $sql_str );
					}

					// Options - upload_path
					$sql_str = $wpdb->prepare(
						esc_sql( "SELECT * FROM `$table_name_restore` " ) . " WHERE option_name=%s LIMIT 1", 'upload_path'
					);
					//echo "sql_str=[". $sql_str ."]<br />";

					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$db_row = $wpdb->get_row( $sql_str );
					//echo "db_row<pre>"; print_r($db_row); echo "</pre>";
					if ( ! empty( $db_row ) ) {
						$new_value = Snapshot_Helper_Utility::replace_value(
								$db_row->option_value,
								$this->_session->data['MANIFEST']['RESTORE']['SOURCE']['UPLOAD_DIR'],
								$this->_session->data['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR']
							);

						if ( $new_value !== $db_row->option_value ) {
							$sql_str = $wpdb->prepare(
									esc_sql( "UPDATE `$table_name_restore` " ) . " SET option_value=%s WHERE option_id=%d",
									$new_value, $db_row->option_id
								);

							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_str );
						}
					}

					// Options - upload_url_path
					$sql_str = $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` " ) . " WHERE option_name=%s LIMIT 1", 'upload_url_path' );
					//echo "sql_str=[". $sql_str ."]<br />";

					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$db_row = $wpdb->get_row( $sql_str );
					//echo "db_row<pre>"; print_r($db_row); echo "</pre>";
					if ( ! empty( $db_row ) ) {
						$new_value = Snapshot_Helper_Utility::replace_value(
								$db_row->option_value,
								$this->_session->data['MANIFEST']['RESTORE']['SOURCE']['UPLOAD_DIR'],
								$this->_session->data['MANIFEST']['RESTORE']['DEST']['UPLOAD_DIR']
							);

						if ( $new_value !== $db_row->option_value ) {
							$sql_str = $wpdb->prepare(
									esc_sql( "UPDATE `$table_name_restore` " ) . " SET option_value=%s WHERE option_id=%d",
									$new_value, $db_row->option_id
								);
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_str );
						}
					}

					// Options - siteurl
					$sql_str = $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` " ) . " WHERE option_name=%s LIMIT 1", 'siteurl' );
					//echo "sql_str=[". $sql_str ."]<br />";

					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$db_row = $wpdb->get_row( $sql_str );
					//echo "db_row<pre>"; print_r($db_row); echo "</pre>";
					if ( ! empty( $db_row ) ) {
						$new_value = Snapshot_Helper_Utility::replace_value(
								$db_row->option_value,
								$this->_session->data['MANIFEST']['RESTORE']['SOURCE']['WP_SITEURL'],
								$this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_SITEURL']
							);

						if ( $new_value !== $db_row->option_value ) {
							$sql_str = $wpdb->prepare(
									esc_sql( "UPDATE `$table_name_restore` " ) . " SET option_value=%s WHERE option_id=%d",
									$new_value, $db_row->option_id
								);
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_str );
						}
					}

					// Options - home
					$sql_str = $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` " ) . " WHERE option_name=%s LIMIT 1", 'home' );
					//echo "sql_str=[". $sql_str ."]<br />";

					// We are using placeholders and $wpdb->prepare() inside the variable.
					// phpcs:ignore
					$db_row = $wpdb->get_row( $sql_str );
					//echo "db_row<pre>"; print_r($db_row); echo "</pre>";
					if ( ! empty( $db_row ) ) {

						if ( defined( 'SUBDOMAIN_INSTALL' ) ) {
							$home_url = untrailingslashit( $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_SITEURL'] );
						} else {
							$blog_id = ! empty( $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] ) ? $this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_BLOG_ID'] : false;

							if ( $blog_id && is_multisite() ) {
								switch_to_blog( $blog_id );
							}

							$home_url = home_url();

							if ( $blog_id && is_multisite() ) {
								restore_current_blog();
							}
						}

						$new_value = Snapshot_Helper_Utility::replace_value(
								$db_row->option_value,
								$this->_session->data['MANIFEST']['WP_HOME'],
								$home_url
							);

						if ( $new_value !== $db_row->option_value ) {
							$sql_str = $wpdb->prepare(
									esc_sql( "UPDATE `$table_name_restore` " ) . " SET option_value=%s WHERE option_id=%d",
									$new_value, $db_row->option_id
								);
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_str );
						}
					}

					break;

				case 'posts':
					// Posts table
					$limit_start = 0;
					$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

					$table_name_restore = $table_set['table_name_restore'];
					while ( true ) {

						$db_rows = $wpdb->get_results( $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore`" ) . " LIMIT %d, %d", $limit_start, $limit_end ) );
						if ( ! empty( $db_rows ) ) {

							//echo "dp_rows<pre>"; print_r($db_rows); echo "</pre>";
							foreach ( $db_rows as $row ) {

								// Update post_title
								if ( ! empty( $row->post_title ) ) {
									$new_value = $row->post_title;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->post_title ) {
										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore`" ) . " SET post_title=%s WHERE ID=%d",
												$new_value, $row->ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}

								// Update post_content
								if ( ! empty( $row->post_content ) ) {
									$new_value = $row->post_content;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->post_content ) {
										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore`" ) . " SET post_content=%s WHERE ID=%d",
												$new_value, $row->ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}

								// Update post_content_filtered
								if ( ! empty( $row->post_content_filtered ) ) {
									$new_value = $row->post_content_filtered;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->post_content_filtered ) {
										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore`" ) . " SET post_content_filtered=%s WHERE ID=%d",
												$new_value, $row->ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}

								// Update post_excerpt
								if ( ! empty( $row->post_excerpt ) ) {
									$new_value = $row->post_excerpt;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->post_excerpt ) {

										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore`" ) . " SET post_excerpt=%s WHERE ID=%d",
												$new_value, $row->ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}

								// Update guid
								if ( ! empty( $row->guid ) ) {
									$new_value = $row->guid;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->guid ) {

										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore`" ) . " SET guid=%s WHERE ID=%d",
												$new_value, $row->ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}
								// Update pinged
								if ( ! empty( $row->pinged ) ) {
									$new_value = $row->pinged;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->guid ) {

										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore`" ) . " SET pinged=%s WHERE ID=%d",
												$new_value, $row->ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}
							}

							$limit_start = $limit_end;
							$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

						} else {
							break;
						}
					}
					break;

				case 'postmeta':
					// Posts Meta table
					$limit_start = 0;
					$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

					$table_name_restore = $table_set['table_name_restore'];
					while ( true ) {

						$sql_str = $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` " ) . " LIMIT %d,%d", $limit_start, $limit_end );
						//echo "sql_str=[". $sql_str ."]<br />";

						// We are using placeholders and $wpdb->prepare() inside the variable.
						// phpcs:ignore
						$db_rows = $wpdb->get_results( $sql_str );
						if ( ! empty( $db_rows ) ) {
							//echo "dp_rows<pre>"; print_r($db_rows); echo "</pre>";
							foreach ( $db_rows as $row ) {
								$new_value = $row->meta_value;
								foreach ( $replacement_strs as $_old_str => $_new_str ) {
									$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
								}
								if ( $new_value !== $row->meta_value ) {
									//echo "postmeta [". $row->meta_name ."] [". $row->meta_value ."] [". $new_value ."]<br />";

									$sql_str = $wpdb->prepare(
                                         esc_sql( "UPDATE `$table_name_restore` " ) . " SET meta_value=%s WHERE meta_id=%d",
										$new_value, $row->meta_id
                                        );
									//echo "sql_str=[". $sql_str ."]<br />";

									// We are using placeholders and $wpdb->prepare() inside the variable.
									// phpcs:ignore
									$wpdb->query( $sql_str );
								}
							}

							$limit_start = $limit_end;
							$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

						} else {
							break;
						}
					}
					break;

				case 'comments':
					// Comments table
					$limit_start = 0;
					$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

					$table_name_restore = $table_set['table_name_restore'];
					while ( true ) {

						$sql_str = $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` " ) . " LIMIT %d, %d", $limit_start, $limit_end );
						//echo "sql_str=[". $sql_str ."]<br />";

						// We are using placeholders and $wpdb->prepare() inside the variable.
						// phpcs:ignore
						$db_rows = $wpdb->get_results( $sql_str );
						//echo "dp_rows<pre>"; print_r($db_rows); echo "</pre>";
						if ( ! empty( $db_rows ) ) {
							foreach ( $db_rows as $row ) {

								// Update comment_content
								if ( ! empty( $row->comment_content ) ) {
									$new_value = $row->comment_content;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->comment_content ) {
										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore` " ) . " SET comment_content=%s WHERE comment_ID=%d",
												$new_value, $row->comment_ID
											);

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}

								// Update comment_author_url
								if ( ! empty( $row->comment_author_url ) ) {
									$new_value = $row->comment_author_url;
									foreach ( $replacement_strs as $_old_str => $_new_str ) {
										$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
									}
									if ( $new_value !== $row->comment_author_url ) {
										$sql_str = $wpdb->prepare(
												esc_sql( "UPDATE `$table_name_restore` " ) . " SET comment_author_url=%s WHERE comment_ID=%d",
												$new_value, $row->comment_ID
											);
										//echo "sql_str=[". $sql_str ."]<br />";

										// We are using placeholders and $wpdb->prepare() inside the variable.
										// phpcs:ignore
										$wpdb->query( $sql_str );
									}
								}
							}

							$limit_start = $limit_end;
							$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

						} else {
							break;
						}
					}
					break;

				case 'commentmeta':
					// Comment Meta table
					$limit_start = 0;
					$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

					$table_name_restore = $table_set['table_name_restore'];
					while ( true ) {
						$sql_str = $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` " ) . " LIMIT %d,%d", $limit_start, $limit_end );
						//echo "sql_str=[". $sql_str ."]<br />";

						// We are using placeholders and $wpdb->prepare() inside the variable.
						// phpcs:ignore
						$db_rows = $wpdb->get_results( $sql_str );
						if ( ! empty( $db_rows ) ) {
							//echo "dp_rows<pre>"; print_r($db_rows); echo "</pre>";
							foreach ( $db_rows as $row ) {
								$new_value = $row->meta_value;
								foreach ( $replacement_strs as $_old_str => $_new_str ) {
									$new_value = Snapshot_Helper_Utility::replace_value( $new_value, $_old_str, $_new_str );
								}
								if ( $new_value !== $row->meta_value ) {
									$sql_str = $wpdb->prepare(
											esc_sql( "UPDATE `$table_name_restore` " ) . " SET meta_value=%s WHERE meta_id=%d",
											$new_value, $row->meta_id
										);
									//echo "sql_str=[". $sql_str ."]<br />";

									// We are using placeholders and $wpdb->prepare() inside the variable.
									// phpcs:ignore
									$wpdb->query( $sql_str );
								}
							}
							$limit_start = $limit_end;
							$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

						} else {
							break;
						}
					}
					break;

				case 'usermeta':
					$limit_start = 0;
					$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

					$table_name_restore = $table_set['table_name_restore'];
					while ( true ) {

						//$this->snapshot_logger->log_message('restore: table: '. $table_data['table_name'] .' sql_str ['. $sql_str .']');

						$db_rows = $wpdb->get_results( $wpdb->prepare( esc_sql( "SELECT * FROM `$table_name_restore` WHERE meta_key LIKE " ) .
														"%s LIMIT %d,%d" , $this->_session->data['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'] . '%', $limit_start, $limit_end  ) );
						if ( ! empty( $db_rows ) ) {
							//echo "dp_rows<pre>"; print_r($db_rows); echo "</pre>";
							//die();

							foreach ( $db_rows as $row ) {
								$new_value = str_replace(
										$this->_session->data['MANIFEST']['RESTORE']['SOURCE']['WP_DB_PREFIX'],
										$this->_session->data['MANIFEST']['RESTORE']['DEST']['WP_DB_PREFIX'], $row->meta_key
									);

								$sql_str = $wpdb->prepare(
										esc_sql( "UPDATE `$table_name_restore`") . " SET meta_key=%s WHERE umeta_id=%d",
										$new_value, $row->umeta_id
									);
								//$this->snapshot_logger->log_message('restore: table: '. $table_data['table_name'] .' sql_str ['. $sql_str .']');
								//echo "sql_str=[". $sql_str ."]<br />";
								//error_log(__FUNCTION__ .": sql[". $sql_str ."]");

								// We are using placeholders and $wpdb->prepare() inside the variable.
								// phpcs:ignore
								$wpdb->query( $sql_str );
							}

							$limit_start = $limit_end;
							$limit_end = $limit_start + SNAPSHOT_RESTORE_MIGRATION_LIMIT_SIZE;

						} else {
							break;
						}
					}
					break;

				case 'users':
				$table_name_restore = $table_set['table_name_restore'];
					if ( ! is_multisite() ) {

						// For non-Multisite we want to drop the extra columns from the users table. But we don't
						// know if the archive was from a regular or Miltisite.
						$db_rows = $wpdb->get_row( esc_sql( "SELECT * FROM `$table_name_restore` WHERE 1=1 LIMIT 1" ) );
						if ( $db_rows ) {
							$alter_tables = array();
							if ( isset( $db_rows->spam ) ) {
								$alter_tables[] = "DROP `spam`";
							}
							if ( isset( $db_rows->deleted ) ) {
								$alter_tables[] = "DROP `deleted`";
							}
							if ( count( $alter_tables ) ) {
								$wpdb->query( esc_sql( "ALTER TABLE `$table_name_restore` " . implode( ',', $alter_tables ) ) );
							}
						}
					}
					break;

				default:
					break;
			}
		}

		public function snapshot_ajax_restore_convert_db_global_tables( $item ) {
			global $wpdb, $current_blog, $current_user;

			// We have checked nonces coming into the function.
			// phpcs:ignore
			if ( ( is_multisite() ) && ( $current_blog->blog_id !== $_POST['snapshot-blog-id'] ) ) {
				// phpcs:ignore
				$wpdb->set_blog_id( $_POST['snapshot-blog-id'] );
			}

			//echo "MANIFEST<pre>"; print_r($this->_session->data['MANIFEST']); echo "</pre>";
			//echo "RESTORE<pre>"; print_r($this->_session->data['MANIFEST']['RESTORE']); echo "</pre>";
			//die();

			$table_prefix_org = $this->_session->data['MANIFEST']['WP_DB_PREFIX'];
			//echo "table_prefix_org[". $table_prefix_org ."]<br />";

			// phpcs:ignore
			$blog_prefix = $wpdb->get_blog_prefix( $_POST['snapshot-blog-id'] );
			//echo "blog_prefix[". $blog_prefix ."]<br />";

			$tables = array();
			$table_results = $wpdb->get_results( 'SHOW TABLES' );
			foreach ( $table_results as $table ) {
				$obj = 'Tables_in_' . DB_NAME;
				$tables[] = $table->$obj;
			}

			$users_restore = false;
			$users_table = $blog_prefix . 'users';

			// Avoid PHP Notice when prefix_[ID]_users don't exist.
			if ( in_array( $users_table, $tables, true ) ) {
				$sql_str = "SELECT * FROM " . $users_table;
				// We are using non-dynamic data here.
				// phpcs:ignore
				$users_restore = $wpdb->get_results( $sql_str );
			}

			if ( $users_restore ) {
				//echo "users_restore<pre>"; print_r($users_restore); echo "</pre>";
				foreach ( $users_restore as $user_restore ) {

					// We purposely skip the user running the restore. This is for security plus we don't want to accedentially create a password change!
					if ( $user_restore->user_login === $current_user->user_login ) {
						continue;
					}

					//echo "user_restore<pre>"; print_r($user_restore); echo "</pre>";
					//echo "user_restore [". $user_restore->ID ."] [". $user_restore->user_login ."] [". $user_restore->user_email ."]<br />";

					$user_local_id = username_exists( $user_restore->user_login );
					//echo "user_local_id=[". $user_local_id ."]<br />";
					if ( $user_local_id ) {
						$user_local = get_userdata( $user_local_id );
						//echo "user_local<pre>"; print_r($user_local); echo "</pre>";
					}

					if ( ! isset( $user_local ) ) {
						//echo "HERE: Need to create new user<br />";
						//die();
						if ( is_multisite() ) {
							if ( ! isset( $user_restore->spam ) ) {
								$user_restore->spam = 0;
							}
							if ( ! isset( $user_restore->deleted ) ) {
								$user_restore->deleted = 0;
							}
						}

						//echo "sql_insert_user[". $sql_insert_user ."]<br />";
						$wpdb->get_results( $wpdb->prepare( "INSERT INTO $wpdb->users VALUES (0, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )", $user_restore->user_login, $user_restore->user_pass, $user_restore->user_nicename, $user_restore->user_email, $user_restore->user_url, $user_restore->user_registered, $user_restore->user_activation_key,  $user_restore->user_status, $user_restore->display_name, $user_restore->spam, $user_restore->deleted ) );
						if ( ! $wpdb->insert_id ) {
							echo nl2br(
								esc_html(
									sprintf(
										"ERROR: Failed to insert user record for User ID[%d] WP Error[%s]\n",
										esc_html($user_restore->ID), esc_html($wpdb->last_error)
									)
								)
							);
						} else {
							$user_restore_new_id = $wpdb->insert_id;
							//echo "sql_usermeta_str=[". $sql_usermeta_str ."]<br />";

							// The interpolated variable is non-dynamic data.
							// phpcs:ignore
							$usermeta_restore = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$blog_prefix}usermeta WHERE user_id=%s", $user_restore->ID ) );

							if ( ( $usermeta_restore ) && ( count( $usermeta_restore ) ) ) {
								//$meta_sql_str = '';
								foreach ( $usermeta_restore as $meta ) {
									//echo "meta_sql_str=[". $meta_sql_str ."]<br />";
									$ret = $wpdb->query( $wpdb->prepare( "INSERT into $wpdb->usermeta VALUES(0, %s, %s, %s)", $user_restore_new_id, $meta->meta_key, $meta->meta_value ) );
									//echo "ret[". $ret ."] wpdb<pre>"; print_r($wpdb); echo "</pre>";
								}
								update_user_meta( $user_restore_new_id, $blog_prefix . 'old_user_id', $user_restore->ID );

								if ( is_multisite() ) {
									// phpcs:ignore
									add_user_meta( $user_restore_new_id, 'primary_blog', $_POST['snapshot-blog-id'] );
								}
							}

							// Update the Posts post_author field
							$sql_posts_str = $wpdb->prepare( "UPDATE $wpdb->posts SET post_author = %d WHERE post_author = %d", $user_restore_new_id, $user_restore->ID );
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_posts_str );

							// Update the Comments user_id field
							$sql_comments_str = $wpdb->prepare( "UPDATE $wpdb->comments SET user_id = %d WHERE user_id = %d", $user_restore_new_id, $user_restore->ID );
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_comments_str );
						}
						continue;

					} else {
						//echo "HERE User exists!<br />";
						//die();

						// If the user an exact match?
						if ( ( $user_local->ID === $user_restore->ID ) && ( $user_local->user_login === $user_restore->user_login ) ) {

							// Now we have the user, we need to add the user meta. We only add meta keys which do not already exist.
							//echo "sql_usermeta_str=[". $sql_usermeta_str ."]<br />";

							// We are using placeholders for dynamic data.
							// phpcs:ignore
							$usermeta_restore = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $blog_prefix . "usermeta WHERE user_id=%s", $user_local->ID ) );
							if ( ( $usermeta_restore ) && ( count( $usermeta_restore ) ) ) {
								foreach ( $usermeta_restore as $meta ) {
									if ( ! get_user_meta( $user_local->ID, $meta->meta_key ) ) {
										//echo "meta_sql_str=[". $meta_sql_str ."]<br />";
										$wpdb->query( $wpdb->prepare( "INSERT into $wpdb->usermeta VALUES(0, %s, %s, %s)", $user_restore->ID, $meta->meta_key, $meta->meta_value ) );
									}
								}
								if ( is_multisite() ) {
									if ( ! get_user_meta( $user_local->ID, 'primary_blog' ) ) {
										// phpcs:ignore
										add_user_meta( $user_local->ID, 'primary_blog', $_POST['snapshot-blog-id'] );
									}
								}
							}
							continue;

						} else {
							//echo "HERE: Need to copy restored user, usermeta, post, comments<br />";

							// IF here we need to copy the usermeta records to the new local_user ID. Copy the usermeta records over to the main table
							// We are using placeholders for dynamic data.
							// phpcs:ignore
							$usermeta_restore = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $blog_prefix . "usermeta WHERE user_id=%s", $user_restore->ID ) );
							if ( ( $usermeta_restore ) && ( count( $usermeta_restore ) ) ) {
								//echo "usermeta_restore<pre>"; print_r($usermeta_restore); echo "</pre>";
								foreach ( $usermeta_restore as $meta ) {
									if ( ! get_user_meta( $user_local->ID, $meta->meta_key ) ) {
										//echo "meta_sql_str=[". $meta_sql_str ."]<br />";
										$wpdb->query( $wpdb->prepare( "INSERT into $wpdb->usermeta VALUES(0, %s, %s, %s)", $user_local->ID, $meta->meta_key, $meta->meta_value ) );
									}
								}
								update_user_meta( $user_local->ID, $blog_prefix . 'old_user_id', $user_restore->ID );

								if ( is_multisite() ) {
									if ( ! get_user_meta( $user_local->ID, 'primary_blog' ) ) {
										// phpcs:ignore
										add_user_meta( $user_local->ID, 'primary_blog', $_POST['snapshot-blog-id'] );
									}
								}
							}

							// Update the Posts post_author field
							$sql_posts_str = $wpdb->prepare( "UPDATE $wpdb->posts SET post_author = %d WHERE post_author = %d", $user_local->ID, $user_restore->ID );
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_posts_str );

							// Update the Comments user_id field
							$sql_comments_str = $wpdb->prepare( "UPDATE $wpdb->comments SET user_id = %d WHERE user_id = %d", $user_local->ID, $user_restore->ID );
							// We are using placeholders and $wpdb->prepare() inside the variable.
							// phpcs:ignore
							$wpdb->query( $sql_comments_str );

						}
					}
				}
			}
		}

		/**
		 * Uninstall/Delete plugin action. Called from uninstall.php file. This function removes file and options setup by plugin.
		 *
		 * @since 1.0.0
		 * @see
		 *
		 * @param int UNIX timestamp from time()
		 *
		 * @return void
		 */

		public function uninstall_snapshot() {

			$this->load_config();
			$this->set_backup_folder();

			if ( ( isset( $this->_settings['backupBaseFolderFull'] ) ) && ( strlen( $this->_settings['backupBaseFolderFull'] ) ) ) {
				Snapshot_Helper_Utility::recursive_rmdir( $this->_settings['backupBaseFolderFull'] );
			}

			delete_option( $this->_settings['options_key'] );
		}

		/**
		 * Interface function provide access to the private _settings array to outside classes.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param string $settings key to $this->_settings array item
		 *
		 * @return string value of setting
		 */

		public function get_setting( $setting ) {
			return isset( $this->_settings[ $setting ] ) ? $this->_settings[ $setting ] : null;
		}

		/**
		 * Interface function provide access to the private _settings array to outside classes.
		 *
		 * @since 1.0.7
		 * @see
		 *
		 * @param string $settings key to $this->_settings array item
		 *
		 * @return string value of setting
		 */
		public function snapshot_update_setting( $setting, $_value ) {
			//echo "_settings<pre>"; print_r($this->_settings); echo "</pre>";
			if ( isset( $this->_settings[ $setting ] ) ) {
				$this->_settings[ $setting ] = $_value;

				return true;
			}
		}

		/**
		 * Interface function provide access to the private _pagehooks array to outside classes.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param string $settings key to $this->_pagehooks array item
		 *
		 * @return string value of pagehook
		 */

		public function snapshot_get_pagehook( $setting ) {
			if ( isset( $this->_pagehooks[ $setting ] ) ) {
				return $this->_pagehooks[ $setting ];
			}
		}

		/**
		 * provide admin page URl spesific to a _pagehooks array element.
		 *
		 * @since 2.0.0
		 * @see
		 *
		 * @param string $setting key to $this->_pagehooks array item
		 *
		 * @return string admin url of pagehook
		 */

		public function snapshot_get_pagehook_url( $setting ) {
			$page_url = '';
			$original_setting = $setting;

			if ( 'snapshots-newui-new-snapshot' === $setting ) {
				$setting = 'snapshots-newui-snapshots';
			}

			if ( isset( $this->_pagehooks[ $setting ] ) ) {
				$page_slug = str_replace( 'snapshot_page_', '', $this->_pagehooks[ $setting ] );
				$page_url = add_query_arg( 'page', $page_slug, network_admin_url( 'admin.php' ) );
			}

			if ( 'snapshots-newui-new-snapshot' === $original_setting ) {
				$page_url = add_query_arg( 'snapshot-action', 'new', $page_url );
			}

			return $page_url;
		}

		public function snapshot_add_destination_proc() {

			// We have checked for nonces coming into the function.
			// phpcs:ignore
			if ( ! isset( $_POST['snapshot-destination']['type'] ) ) {
				return;
			}

			// phpcs:ignore
			$destination_info = $_POST['snapshot-destination'];
			$destination_type = $destination_info['type'];

			// if the 'type' is not found in the list of loaded destinationClasses then abort.
			if ( ! isset( $this->_settings['destinationClasses'][ $destination_type ] ) ) {
				return;
			}

			$location_redirect_url = '';
			/** @var Snapshot_Model_Destination $destination_type_object */
			$destination_type_object = $this->_settings['destinationClasses'][ $destination_type ];
			$destination_info = $destination_type_object->validate_form_data( $destination_info );
			$_POST['snapshot-destination'] = $destination_info;

			if ( count( $destination_type_object->form_errors ) ) {
				$this->form_errors = $destination_type_object->form_errors;

				return;
			}


			if ( ! isset( $this->config_data['destinations'] ) ) {
				$this->config_data['destinations'] = array();
			}

			$original_destination_slug = sanitize_title( $destination_info['name'] );
			$destination_slug = $original_destination_slug;

			/* Ensure that the destination slug is unique */
			for ( $counter = 0; isset( $this->config_data['destinations'][ $destination_slug ] ) && $counter < 100; $counter ++ ) {
				$destination_slug = $original_destination_slug . '-' . $counter;
			}

			$this->config_data['destinations'][ $destination_slug ] = $destination_info;

			if ( ! empty( $destination_info['form-step-url'] ) ) {
				$location_redirect_url = add_query_arg(
					array(
						'item' => $destination_slug,
						'message', 'success-add',
					), $destination_info['form-step-url']
				);
			}

			$this->save_config();


			if ( empty( $location_redirect_url ) ) {
				$location_redirect_url = add_query_arg(
					array(
						'snapshot-action' => 'edit',
						'type' => $destination_type,
						'item' => $destination_slug,
					),
					$this->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
				);
			}

			$location = add_query_arg( 'message', 'success-add', $location_redirect_url );
			$location = add_query_arg( 'destination-noonce-field', wp_create_nonce( 'snapshot-destination' ), $location_redirect_url );

			if ( $location ) {
				wp_redirect( esc_url_raw( $location ) );
			}
		}

		public function snapshot_update_destination_proc() {

			// For the form post we need both elements to continue;
			// We have checked for nonce coming into the function.
			// phpcs:ignore
			if ( ! isset( $_POST['snapshot-destination']['type'], $_POST['item'] ) ) {
				return;
			}

			// phpcs:ignore
			$destination_key = $_POST['item'];

			// If not a valid destination key, them abort.
			if ( ! isset( $this->config_data['destinations'][ $destination_key ] ) ) {
				return;
			}

			// phpcs:ignore
			$destination_info = $_POST['snapshot-destination'];
			$destination_type = $destination_info['type'];

			// If the 'type' is not found in the list of loaded destinationClasses then abort.
			if ( ! isset( $this->_settings['destinationClasses'][ $destination_type ] ) ) {
				return;
			}

			/** @var Snapshot_Model_Destination $destination_type_object */
			$destination_type_object = $this->_settings['destinationClasses'][ $destination_type ];
			$destination_info = $destination_type_object->validate_form_data( $destination_info );
			$_POST['snapshot-destination'] = $destination_info;
			$location_redirect_url = '';

			if ( count( $destination_type_object->form_errors ) ) {
				$this->form_errors = $destination_type_object->form_errors;

				return;
			}

			$this->config_data['destinations'][ $destination_key ] = $destination_info;
			$this->save_config();

			if ( ! empty( $destination_info['form-step-url'] ) ) {
				$location_redirect_url = add_query_arg( 'item', $destination_key, $destination_info['form-step-url'] );
				$location_redirect_url = add_query_arg( 'message', 'success-update', $location_redirect_url );
				//$location_redirect_url = add_query_arg('snapshot-action', 'edit', $location_redirect_url);
				$location_redirect_url = esc_url_raw( $location_redirect_url );
			}

			if ( empty( $location_redirect_url ) ) {
				$location_redirect_url = add_query_arg(
					array(
						'snapshot-action' => 'edit',
						'type' => $destination_type,
						'item' => $destination_key,
						'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
					),
					$this->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
				);
			}

			$location = add_query_arg( 'message', 'success-update', $location_redirect_url );
			$location = add_query_arg( 'destination-noonce-field', wp_create_nonce( 'snapshot-destination' ), $location_redirect_url );

			if ( isset( $location ) ) {
				wp_redirect( esc_url_raw( $location ) );
			}
		}

		/**
		 * Processing 'delete' action from form post to delete a select Snapshot destination.
		 *
		 * @since 1.0.0
		 * @uses $_REQUEST['delete']
		 * @uses $this->config_data['destinations']
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_delete_bulk_destination_proc() {

			// We have checked nonces coming into the function.
			// phpcs:ignore
			if ( ! isset( $_REQUEST['delete-bulk-destination'] ) ) {
				wp_redirect( $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshots_destinations_panel' );
				die();
			}

			$CONFIG_CHANGED = false;
			// phpcs:ignore
			foreach ( $_REQUEST['delete-bulk-destination'] as $key => $val ) {
				if ( $this->snapshot_delete_destination_proc( $key, true ) ) {
					$CONFIG_CHANGED = true;
				}
			}

			if ( $CONFIG_CHANGED ) {

				$this->save_config();

				$location = esc_url_raw( add_query_arg( 'message', 'success-delete', $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshots_destinations_panel' ) );

				// phpcs:ignore
				if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'snapshot_pro_destinations' ) {
					$location = esc_url_raw( add_query_arg( 'message', 'success-delete', $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_destinations' ) );

				}

				if ( $location ) {
					wp_redirect( $location );
					die();
				}
			}

			wp_redirect( $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshots_destinations_panel' );
			die();
		}

		public function snapshot_delete_destination_proc( $item_key = 0, $DEFER_LOG_UPDATE = false ) {

			$CONFIG_CHANGED = false;

			if ( ! $item_key ) {
				// We have checked nonces coming into the function.
				// phpcs:ignore
				if ( isset( $_GET['item'] ) ) {
					// phpcs:ignore
					$item_key = $_GET['item'];
				}
			}

			if ( array_key_exists( $item_key, $this->config_data['destinations'] ) ) {

				unset( $this->config_data['destinations'][ $item_key ] );
				$CONFIG_CHANGED = true;
			}

			if ( ! $DEFER_LOG_UPDATE ) {
				if ( $CONFIG_CHANGED ) {
					$this->save_config();

					$location = esc_url_raw( add_query_arg( 'message', 'success-delete', $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshots_destinations_panel' ) );
					// phpcs:ignore
					if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'snapshot_pro_destinations' ) {
						$location = esc_url_raw( add_query_arg( 'message', 'success-delete', $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_destinations' ) );

					}

					if ( $location ) {
						wp_redirect( $location );
						die();
					}
				}

				// phpcs:ignore
				if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'snapshot_pro_destinations' ) {
					wp_redirect( $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshot_pro_destinations' );
					die();

				}

				wp_redirect( $this->_settings['SNAPSHOT_MENU_URL'] . 'snapshots_destinations_panel' );
				die();

			} else {
				return $CONFIG_CHANGED;
			}
		}

		/**
		 * Utility function loop through existing Snapshot items and make sure they are
		 * setup in the WP Cron facility. Also, in case there are some left over cron
		 * entries a secondary process will loop through the WP Cron entries to make
		 * the entries related to Snapshot are valid and current.
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */

		public function snapshot_scheduler() {

			$HAVE_SCHEDULED_EVENTS = false;
			// A two-step process.

			// 1. First any items needing to be schduled we make sure they are added.
			if ( ( isset( $this->config_data['items'] ) ) && ( count( $this->config_data['items'] ) ) ) {

				$scheds = (array) wp_get_schedules();

				foreach ( $this->config_data['items'] as $key_slug => $item ) {

					if ( ( isset( $item['interval'] ) ) && ( '' !== $item['interval'] ) ) {

						if ( isset( $scheds[ $item['interval'] ] ) ) {

							$next_timestamp = wp_next_scheduled( $this->_settings['backup_cron_hook'], array( intval( $key_slug ) ) );

							if ( ! $next_timestamp ) {
								//$interval_offset = $scheds[$item['interval']]['interval'];
								//$offset_timestamp = time() - $interval_offset;
								//wp_schedule_event($offset_timestamp, $item['interval'], $this->_settings['backup_cron_hook'], array(intval($key_slug)) );
								wp_schedule_event(
										time() + Snapshot_Helper_Utility::calculate_interval_offset_time( $item['interval'], $item['interval-offset'] ),
										$item['interval'], $this->_settings['backup_cron_hook'], array( intval( $key_slug ) )
									);
								$HAVE_SCHEDULED_EVENTS = true;
							}
						}
					}
				}
			}

			// 2. Go through the WP cron entries. Any snapshot items not matching to existing items or items without proper intervals unschedule.
			$crons = _get_cron_array();
			if ( $crons ) {
				foreach ( $crons as $cron_time => $cron_set ) {
					foreach ( $cron_set as $cron_callback_function => $cron_item ) {
						if ( "snapshot_backup_cron" === $cron_callback_function ) {
							foreach ( $cron_item as $cron_key => $cron_details ) {
								if ( isset( $cron_details['args'][0] ) ) {
									$item_key = intval( $cron_details['args'][0] );
									if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
										$timestamp = wp_next_scheduled( $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
										if ( $timestamp ) {
											wp_unschedule_event( $timestamp, $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
										} else {
											wp_unschedule_event( $cron_time, $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
										}
									}
								}
							}
						} else if ( $cron_callback_function === $this->_settings['remote_file_cron_hook'] ) {
							foreach ( $cron_item as $cron_key => $cron_details ) {
								if ( $cron_details['schedule'] !== $this->_settings['remote_file_cron_interval'] ) {
									$timestamp = wp_next_scheduled( $this->_settings['remote_file_cron_hook'] );
									wp_unschedule_event( $timestamp, $this->_settings['remote_file_cron_hook'] );
								}
							}
						}

					}
				}
			}

			// We only need the remote file cron if we have destinations defined
			if ( ! empty( $this->config_data['destinations'] ) ) {

				// Special-case local destination check
				// We shouldn't really do the remote file hook with only local destination set up
				// And it is always set up by default, @see $this->load_config()
				$destinations = $this->config_data['destinations'];
				if ( ! empty( $destinations['local'] ) && count( $destinations ) > 1 ) {
					// Ok, so we have destinations that are not local. We should schedule, really
					$timestamp = wp_next_scheduled( $this->_settings['remote_file_cron_hook'] );
					if ( ! $timestamp ) {
						wp_schedule_event( time(), $this->_settings['remote_file_cron_interval'], $this->_settings['remote_file_cron_hook'] );
						$HAVE_SCHEDULED_EVENTS = true;

					}
				}
			}

			if ( true === $HAVE_SCHEDULED_EVENTS ) {
				wp_remote_post(
					get_option( 'siteurl' ) . '/wp-cron.php',
						array(
							'timeout' => 3,
							'blocking' => false,
							'sslverify' => false,
							'body' => array(
								'nonce' => wp_create_nonce( 'WPMUDEVSnapshot' ),
								'type' => 'start',
							),
							'user-agent' => 'WPMUDEVSnapshot',
					)
				);
			}
		}

		/**
		 * Utility function called by WPCron scheduling dispatch. The parameter passed in is the
		 * config item key to an existing entry. If a match is found and verified it will be processed
		 *
		 * @since 1.0.2
		 * @see
		 *
		 * @param int $item_key - Match to an item in the $this->config_data['items'] array.
		 *
		 * @return void
		 */
		public function snapshot_backup_cron_proc( $item_key ) {

			global $wpdb;

			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			@set_time_limit( 0 ); // phpcs:ignore

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			$old_error_handler = set_error_handler( array( $this, 'snapshot_ErrorHandler' ) );

			if ( ( isset( $this->config_data['config']['memoryLimit'] ) ) && ( ! empty( $this->config_data['config']['memoryLimit'] ) ) ) {
				@ini_set( 'memory_limit', $this->config_data['config']['memoryLimit'] ); // phpcs:ignore
			}

			$item_key = intval( $item_key );

			// If we are somehow called for an item_key not in our list then remove any future cron calls then die
			if ( ( ! defined( 'SNAPSHOT_DOING_CRON' ) ) || ( true !== SNAPSHOT_DOING_CRON ) ) {
				if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
					$timestamp = wp_next_scheduled( $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
					if ( $timestamp ) {
						wp_unschedule_event( $timestamp, $this->_settings['backup_cron_hook'], array( intval( $item_key ) ) );
					}
					die();
				}
			}

			$item = $this->config_data['items'][ $item_key ];

			$data_item_key = time();

			if ( ! isset( $item['destination-sync'] ) ) {
				$item['destination-sync'] = "archive";
			}

			// If we are syncing/mirroring file and we don't have and database files. Then no need going through the
			// process of creating a new data_item entry.
			$_has_incomplete = false;
			if ( "mirror" === $item['destination-sync'] ) {
				if ( ( isset( $item['data'] ) ) && ( count( $item['data'] ) ) ) {
					$data_item = Snapshot_Helper_Utility::latest_data_item( $item['data'] );
					//echo "data_item<pre>"; print_r($data_item); echo "</pre>";
					if ( ( isset( $data_item['destination-status'] ) ) && ( count( $data_item['destination-status'] ) ) ) {
						$dest_item = Snapshot_Helper_Utility::latest_data_item( $data_item['destination-status'] );
						if ( ( ! isset( $dest_item['sendFileStatus'] ) ) || ( true !== $dest_item['sendFileStatus'] ) ) {
							$_has_incomplete = true;
						}
					}
				}
			}

			if ( ( 'mirror' !== $item['destination-sync'] )
			     || ( false === $_has_incomplete )
			     || ( "all" === $item['tables-option'] ) || ( "selected" === $item['tables-option'] )
			) {

				if ( ! isset( $this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'] ) ) {
					$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'] = array();
				}

				$snapshot_locker = new Snapshot_Helper_Locker( $this->_settings['backupLockFolderFull'], $item_key );

				// If we can't lock the locker then abort.
				if ( ! $snapshot_locker->is_locked() ) {
					return;
				}

				$locket_info = array(
					'doing' => __( 'Creating Archive', SNAPSHOT_I18N_DOMAIN ),
					'item_key' => $item_key,
					'data_item_key' => $data_item_key,
					'time_start' => time(),
				);
				$snapshot_locker->set_locker_info( $locket_info );

				$this->snapshot_logger = new Snapshot_Helper_Logger( $this->_settings['backupLogFolderFull'], $item_key, $data_item_key );

				Snapshot_Helper_Debug::set_error_reporting( $this->config_data['config']['errorReporting'] );

				/* Needed to create the archvie zip file */
				if ( "PclZip" === $this->config_data['config']['zipLibrary'] ) {
					if ( ! defined( 'PCLZIP_TEMPORARY_DIR' ) ) {
						define( 'PCLZIP_TEMPORARY_DIR', trailingslashit( $this->_settings['backupBackupFolderFull'] ) . $item_key . "/" );
					}

					//$this->snapshot_logger->log_message("init: Using PclZip PCLZIP_TEMPORARY_DIR". PCLZIP_TEMPORARY_DIR);

					if ( ! class_exists( 'class PclZip' ) ) {
						require_once ABSPATH . '/wp-admin/includes/class-pclzip.php';
					}
				}

				$this->snapshot_logger->log_message( 'init' );

				$_post_array['snapshot-proc-action'] = "init";
				$_post_array['snapshot-action'] = "cron";
				$_post_array['snapshot-blog-id'] = $item['blog-id'];
				$_post_array['snapshot-item'] = $item_key;
				$_post_array['snapshot-data-item'] = $data_item_key;
				$_post_array['snapshot-interval'] = $item['interval'];
				$_post_array['snapshot-tables-option'] = $item['tables-option'];
				$_post_array['snapshot-destination-sync'] = $item['destination-sync'];

				$_post_array['snapshot-tables-array'] = array();
				if ( "none" === $_post_array['snapshot-tables-option'] ) {
					assert(true); // Nothing to process here.

				} else if ( "all" === $_post_array['snapshot-tables-option'] ) {

					$tables_sections = Snapshot_Helper_Utility::get_database_tables( $item['blog-id'] );
					//$this->_session->data['tables_sections'] = $tables_sections;
					if ( $tables_sections ) {
						foreach ( $tables_sections as $section => $tables ) {
							$_post_array['snapshot-tables-array'] = array_merge( $_post_array['snapshot-tables-array'], $tables );
						}
					}
				} else if ( "selected" === $_post_array['snapshot-tables-option'] ) {

					if ( isset( $item['tables-sections'] ) ) {
						$this->_session->data['tables-sections'] = $item['tables-sections'];

						foreach ( $item['tables-sections'] as $section => $tables ) {
							$_post_array['snapshot-tables-array'] = array_merge( $_post_array['snapshot-tables-array'], $tables );
						}
					}
				}

				if ( "archive" === $item['destination-sync'] ) {
					$_post_array['snapshot-files-option'] = $item['files-option'];
					$_post_array['snapshot-files-sections'] = array();
					if ( "none" === $_post_array['snapshot-files-option'] ) {
						assert(true); // No-op.
					} else if ( "all" === $_post_array['snapshot-files-option'] ) {

						if ( is_main_site( $item['blog-id'] ) ) {
							$_post_array['snapshot-files-sections'] = array( 'themes', 'plugins', 'media' );
							if ( is_multisite() ) {
								$_post_array['snapshot-files-sections'][] = 'mu-plugins';
							}
						} else {
							$_post_array['snapshot-files-sections'] = array( 'media' );
						}

					} else if ( "selected" === $_post_array['snapshot-files-option'] ) {

						if ( isset( $item['files-sections'] ) ) {
							$_post_array['snapshot-files-sections'] = $item['files-sections'];
						}
					}
				} else {
					$_post_array['snapshot-files-option'] = "none";
				}

				ob_start();
				$error_array = $this->snapshot_ajax_backup_init( $item, $_post_array );
				$function_output = ob_get_contents();
				ob_end_clean();

				if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {

					// Not debug code. We use print_r() for logging purposes.
					$this->snapshot_logger->log_message( "init: error_array" . print_r( $error_array, true ) ); // phpcs:ignore
					$this->snapshot_logger->log_message( "init: item" . print_r( $item, true ) ); // phpcs:ignore
					$this->snapshot_logger->log_message( "init: output:" . $function_output );

					$this->snapshot_logger->log_message(
                         "memory limit: " . ini_get( 'memory_limit' ) .
					                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
					                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                        );

					$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
					$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

					unset( $snapshot_locker );
					die();
				}

				if ( ( isset( $error_array['table_data'] ) ) && ( count( $error_array['table_data'] ) ) ) {

					// Switch to the blog site we are attempting to backup. This will ensure it should work properly
					if ( is_multisite() ) {
						$current_blogid = $wpdb->blogid;
						switch_to_blog( intval( $item['blog-id'] ) );
					}

					foreach ( $error_array['table_data'] as $idx => $table_item ) {

						unset( $_post_array );
						$_post_array['snapshot-proc-action'] = "table";
						$_post_array['snapshot-action'] = "cron";
						$_post_array['snapshot-blog-id'] = $item['blog-id'];
						$_post_array['snapshot-item'] = $item_key;
						$_post_array['snapshot-data-item'] = $data_item_key;
						$_post_array['snapshot-table-data-idx'] = $idx;

						$this->snapshot_logger->log_message(
                             "table: " . $table_item['table_name'] .
						                                     " segment: " . $table_item['segment_idx'] . "/" . $table_item['segment_total']
                            );

						ob_start();
						$error_array_table = $this->snapshot_ajax_backup_table( $item, $_post_array );
						$function_output = ob_get_contents();
						ob_end_clean();

						if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
							// We have a problem.

							// Not debug code. We use print_r() for logging purposes.
							$this->snapshot_logger->log_message( "table: " . $table_item['table_name'] . ": error_array" . print_r( $error_array_table, true ) ); // phpcs:ignore
							$this->snapshot_logger->log_message( "table: " . $table_item['table_name'] . ": _SESSION" . print_r( $this->_session, true ) ); // phpcs:ignore
							$this->snapshot_logger->log_message( "table: " . $table_item['table_name'] . ": item" . print_r( $item, true ) ); // phpcs:ignore
							$this->snapshot_logger->log_message( "table: output:" . $function_output );

							$this->snapshot_logger->log_message(
                                 "memory limit: " . ini_get( 'memory_limit' ) .
							                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
							                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                );

							$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
							$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

							unset( $snapshot_locker );

							die();
						}
					}

					if ( is_multisite() ) {
						if ( isset( $current_blogid ) ) {
							switch_to_blog( intval( $current_blogid ) );
						}
					}
				} else {
					$this->snapshot_logger->log_message( "table: non selected" );
				}

				if ( "archive" === $item['destination-sync'] ) {

					if ( ( isset( $error_array['files_data'] ) ) && ( count( $error_array['files_data'] ) ) ) {

						foreach ( $error_array['files_data'] as $file_set_key ) {

							unset( $_post_array );
							$_post_array['snapshot-proc-action'] = "file";
							$_post_array['snapshot-action'] = "cron";
							$_post_array['snapshot-blog-id'] = $item['blog-id'];
							$_post_array['snapshot-item'] = $item_key;
							$_post_array['snapshot-file-data-key'] = $file_set_key;

							ob_start();
							$error_array_file = $this->snapshot_ajax_backup_file( $item, $_post_array );
							$function_output = ob_get_contents();
							ob_end_clean();

							if ( ( isset( $error_array_file['errorStatus'] ) ) && ( true === $error_array_file['errorStatus'] ) ) {
								// We have a problem.

								// Not debug code. We use print_r() for logging purposes.
								$this->snapshot_logger->log_message( "file: _post_array:" . print_r( $_post_array, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: error_array:" . print_r( $error_array_file, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: _SESSION:" . print_r( $this->_session, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: item:" . print_r( $item, true ) ); // phpcs:ignore
								$this->snapshot_logger->log_message( "file: output:" . $function_output );

								$this->snapshot_logger->log_message(
                                     "memory limit: " . ini_get( 'memory_limit' ) .
								                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
								                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                                    );

								$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
								$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

								unset( $snapshot_locker );

								die();
							}
						}

						if ( ( isset( $error_array['files_data']['excluded']['pattern'] ) ) && ( count( $error_array['files_data']['excluded']['pattern'] ) ) ) {

							$this->snapshot_logger->log_message(
                                __(
                                 "file: The following files are excluded due to match exclusion patterns.",
								SNAPSHOT_I18N_DOMAIN
                                )
                                );

							foreach ( $error_array['files_data']['excluded']['pattern'] as $idx => $filename ) {
								$filename = str_replace( $home_path, '', $filename );
								$this->snapshot_logger->log_message( "file: excluded:  " . $filename );
							}
						}

						if ( ( isset( $error_array['files_data']['excluded']['error'] ) ) && ( count( $error_array['files_data']['excluded']['error'] ) ) ) {

							$this->snapshot_logger->log_message( __( "file: The following files are excluded because snapshot cannot open them. Check file permissions or locks", SNAPSHOT_I18N_DOMAIN ) );

							foreach ( $error_array['files_data']['excluded']['error'] as $idx => $filename ) {
								$filename = str_replace( $home_path, '', $filename );
								$this->snapshot_logger->log_message( "file: error: " . $filename );
							}
						}

					} else {
						$this->snapshot_logger->log_message( "file: non selected" );
					}
				} else {
					$this->snapshot_logger->log_message( "file: mirroring enabled. Files are synced during send to destination." );
				}

				$_post_array['snapshot-proc-action'] = "finish";
				$_post_array['snapshot-action'] = "cron";
				$_post_array['snapsho-blog-id'] = $item['blog-id'];
				$_post_array['snapshot-item'] = $item_key;
				$_post_array['snapshot-data-item'] = $data_item_key;

				ob_start();
				$error_array = $this->snapshot_ajax_backup_finish( $item, $_post_array );
				$function_output = ob_get_contents();
				ob_end_clean();

				if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
					// We have a problem.

					// Not debug code. We use print_r() for logging purposes.
					$this->snapshot_logger->log_message( "finish: error_array:" . print_r( $error_array, true ) ); // phpcs:ignore
					$this->snapshot_logger->log_message( "finish: _SESSION:" . print_r( $this->_session, true ) ); // phpcs:ignore
					$this->snapshot_logger->log_message( "finish: item:" . print_r( $item, true ) ); // phpcs:ignore
					$this->snapshot_logger->log_message( "finish: output:" . $function_output );

					$this->snapshot_logger->log_message(
                         "memory limit: " . ini_get( 'memory_limit' ) .
					                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
					                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                        );

					$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
					$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

					unset( $snapshot_locker );

					die();
				}

				$this->snapshot_logger->log_message(
                     "memory limit: " . ini_get( 'memory_limit' ) .
				                                     ": memory usage current: " . Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ) .
				                                     ": memory usage peak: " . Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) )
                    );

				if ( isset( $error_array['responseFile'] ) ) {
					$this->snapshot_logger->log_message( "finish: " . basename( $error_array['responseFile'] ) );
				}

				$this->config_data['items'][ $item_key ]['data'][ $data_item_key ]['archive-status'][ time() ] = $error_array;
				$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );

				// Checking for Archive Account purge
				$this->purge_archive_limit( $item_key );

				unset( $snapshot_locker );
			}
			$this->process_item_remote_files( $item_key );
		}

		/**
		 * Cleans up remote destination stored files
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param array $item Snapshot item hash.
		 *
		 * @return bool
		 */
		public function purge_remote_destination_archive ($item) {
			if (empty($item['clean-remote'])) {
				// Item remotes should not be cleaned up.
				// Nothing to do, so bail out.
				return true;
			}

			$data = isset($item['data']) ? $item['data']: false;
			if (empty($item['data'])) return false;

			$archive_count = isset($item['archive-count']) ? intval( $item['archive-count'] ) : 0;
			if (empty($archive_count)) return false;

			$destination_object = Snapshot_Model_Destination_Factory::from_item($item);
			if (!is_object($destination_object)) return false;

			if (!is_callable(array($destination_object, 'purge_remote_items'))) return false;

			$filename_prefix = sanitize_file_name( strtolower( $item['name'] ) );
			$destination_object->purge_remote_items($filename_prefix, $archive_count);

			return true;
		}

		public function purge_archive_limit( $item_key ) {
			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
				return;
			}

			$item = $this->config_data['items'][ $item_key ];

			// Purge remote files first
			$this->purge_remote_destination_archive($item);

			if ( ( isset( $item['archive-count'] ) ) && ( intval( $item['archive-count'] ) ) ) {
				$archive_count = intval( $item['archive-count'] );
				if ( ( isset( $this->config_data['items'][ $item_key ]['data'] ) )
				     && ( count( $this->config_data['items'][ $item_key ]['data'] ) > $archive_count )
				) {

					$this->snapshot_logger->log_message(
							"archive cleanup: max archive:" . intval( $item['archive-count'] )
							. " number of archives: " . count( $this->config_data['items'][ $item_key ]['data'] )
						);

					$item_data = $this->config_data['items'][ $item_key ]['data'];
					ksort( $item_data );
					krsort( $item_data );
					$data_keep = array_slice( $item_data, 0, $archive_count, true );

					if ( $data_keep ) {
						ksort( $data_keep );
						$this->config_data['items'][ $item_key ]['data'] = $data_keep;

						$this->add_update_config_item( $item_key, $this->config_data['items'][ $item_key ] );
					}

					$data_purge = array_slice( $item_data, $archive_count );

					if ( $data_purge ) {
						foreach ( $data_purge as $data_item ) {
							//if ((empty($data_item['destination'])) || ($data_item['destination'] == "local")) {
							if ( isset( $data_item['filename'] ) ) {

								$current_backupFolder = $this->snapshot_get_item_destination_path( $item, $data_item );
								if ( empty( $current_backupFolder ) ) {
									$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
								}
								$this->snapshot_logger->log_message( "archive cleanup: DEBUG :" . $current_backupFolder );

								$backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];

								if ( ! file_exists( $backupFile ) ) {
									$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
									$backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
								}
								$this->snapshot_logger->log_message( "DEBUG: backupFile=[" . $backupFile . "]" );

								if ( is_writable( $backupFile ) ) {
									unlink( $backupFile );
									$this->snapshot_logger->log_message( "archive cleanup: filename: " . str_replace( $home_path, '', $backupFile ) . " removed" );
								} else {
									$this->snapshot_logger->log_message( "archive cleanup: filename: " . str_replace( $home_path, '', $backupFile ) . " not found or it is not writeable" );
								}
							}
							//}

							$backupLogFileFull = trailingslashit( $this->_settings['backupLogFolderFull'] )
							                     . $item['timestamp'] . "_" . $data_item['timestamp'] . ".log";

							if ( is_writable( $backupLogFileFull ) ) {
								unlink( $backupLogFileFull );
							}
						}
					}
				}
			}

		}

		public function process_item_remote_files( $item_key ) {

			$item_key = intval( $item_key );

			// reload the config.
			$this->load_config();

			// If we are somehow called for an item_key not in our list then remove any future cron calls then die
			if ( ! isset( $this->config_data['items'][ $item_key ] ) ) {
				return;
			}

			$item = $this->config_data['items'][ $item_key ];

			// If the item destination is not set or is empty then the file stay local.
			if ( empty( $item['destination'] ) || 'local' === $item['destination'] ) {
				return;
			}

			// If the destination is set but not found in the destinations array we can't process. Abort.
			if ( ! isset( $this->config_data['destinations'][ $item['destination'] ] ) ) {
				return;
			}

			// If the item doesn't have data. Abort.
			if ( empty( $item['data'] ) ) {
				return;
			}

			$snapshot_locker = new Snapshot_Helper_Locker( $this->_settings['backupLockFolderFull'], $item_key );

			if ( $snapshot_locker->is_locked() ) {
				ksort( $item['data'] ); // Earliest first. Since those need/should be processed first!
				foreach ( $item['data'] as $data_item_key => $data_item ) {

					$data_item_key = $data_item['timestamp'];

					if ( isset( $item['destination-sync'] ) && 'mirror' === $item['destination-sync'] ) {
						$doing_message = __( 'Syncing Files', SNAPSHOT_I18N_DOMAIN );
					} else {
						$doing_message = __( 'Sending Archive', SNAPSHOT_I18N_DOMAIN );
					}

					$locker_info = array(
						'doing' => $doing_message,
						'item_key' => $item_key,
						'data_item_key' => $data_item_key,
						'time_start' => time(),
					);
					$snapshot_locker->set_locker_info( $locker_info );

					$data_item_new = $this->process_item_send_archive( $item, $data_item, $snapshot_locker );

					if ( $data_item_new && is_array( $data_item_new ) ) {
						$item['data'][ $data_item_key ] = $data_item_new;
						$this->add_update_config_item( $item_key, $item );
					}
				}
				unset( $snapshot_locker );
			}
		}

		public function process_item_send_archive( $item, $data_item, $snapshot_locker ) {
			$item_key = $item['timestamp'];
			$data_item_key = $data_item['timestamp'];

			// Create a logged for each item/data_item combination because that is how the log files are setup
			if ( isset( $snapshot_logger ) ) {
				unset( $snapshot_logger );
			}
			$snapshot_logger = new Snapshot_Helper_Logger( $this->_settings['backupLogFolderFull'], $item_key, $data_item_key );

			// If the file has already been transmitted the move to the next one.
			if ( isset( $data_item['destination-status'] ) && count( $data_item['destination-status'] ) ) {
				$destination_status = Snapshot_Helper_Utility::latest_data_item( $data_item['destination-status'] );

				// If we have a positive 'sendFileStatus' continue on
				if ( isset( $destination_status['sendFileStatus'] ) && $destination_status['sendFileStatus'] ) {
					return false;
				}
			}

			// Get the archive folder
			$current_backupFolder = $this->snapshot_get_item_destination_path( $item, $data_item );
			if ( empty( $current_backupFolder ) ) {
				$current_backupFolder = $this->get_setting( 'backupBaseFolderFull' );
			}

			// If the data_item destination is not empty...
			if ( ( isset( $data_item['destination'] ) ) && ( ! empty( $data_item['destination'] ) ) ) {
				// We make sure to check it against the item master. If they don't match it means
				// the data_item archive was sent to the data_item destination. We probably don't
				// have the archive file to resent.
				assert(true); // @TODO ... or we don't?
			}

			$destination_key = $item['destination'];
			if ( ! isset( $this->config_data['destinations'][ $destination_key ] ) ) {
				return;
			}

			$destination = $this->config_data['destinations'][ $destination_key ];
			if ( ! isset( $destination['type'] ) ) {
				return;
			}

			if ( ! isset( $this->_settings['destinationClasses'][ $destination['type'] ] ) ) {
				return;
			}

			$destination_object = $this->_settings['destinationClasses'][ $destination['type'] ];

			$new_backupFolder = $this->snapshot_get_item_destination_path( $item );
			if ( ( $new_backupFolder ) && ( strlen( $new_backupFolder ) ) ) {
				$destination['directory'] = $new_backupFolder;
			}

			if ( ! isset( $data_item['destination-sync'] ) ) {
				$data_item['destination-sync'] = "archive";
			}

			$files_sync = array();
			if ( "archive" === $data_item['destination-sync'] ) {

				// If the data item is there but no final archive filename (probably stopped in an error). Abort
				if ( empty( $data_item['filename'] ) ) {
					return;
				}

				// See if we still have the archive file.
				// First check where we originally placed it.
				$backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
				if ( ! file_exists( $backupFile ) ) {

					// Then check is the detail Snapshot archive folder
					$current_backupFolder = $this->_settings['backupBaseFolderFull'];
					$backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
					if ( ! file_exists( $backupFile ) ) {
						return;
					}
				}

				$snapshot_logger->log_message( "Sending Archive: " . basename( $backupFile ) . " " . Snapshot_Helper_Utility::size_format( filesize( $backupFile ) ) );
				$snapshot_logger->log_message( "Destination: " . $destination['type'] . ": " . stripslashes( $destination['name'] ) );

				$locker_info = $snapshot_locker->get_locker_info();
				$locker_info['file_name'] = $backupFile;
				$locker_info['file_size'] = filesize( $backupFile );
				$snapshot_locker->set_locker_info( $locker_info );

				$destination_object->snapshot_logger = $snapshot_logger;
				$destination_object->snapshot_locker = $snapshot_locker;

				$error_array = $destination_object->sendfile_to_remote( $destination, $backupFile );
				//echo "error_array<pre>"; print_r($error_array); echo "</pre>";

				//$snapshot_logger->log_message("DEBUG: error_array<pre>". print_r($error_array, true)."</pre>");

				if ( ( isset( $error_array['responseArray'] ) ) && ( count( $error_array['responseArray'] ) ) ) {
					foreach ( $error_array['responseArray'] as $message ) {
						$snapshot_logger->log_message( $message );
					}
				}

				if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
					if ( ( isset( $error_array['errorArray'] ) ) && ( count( $error_array['errorArray'] ) ) ) {
						foreach ( $error_array['errorArray'] as $message ) {
							$snapshot_logger->log_message( "ERROR: " . $message );
						}
					}
				}

				if ( ! isset( $data_item['destination-status'] ) ) {
					$data_item['destination-status'] = array();
				}

				if ( isset( $item['store-local'] ) && ( 0 === $item['store-local'] ) ) {
					if ( ( isset( $error_array['sendFileStatus'] ) ) && ( true === $error_array['sendFileStatus'] ) ) {
						$snapshot_logger->log_message( "Local archive removed: " . basename( $backupFile ) );
						unlink( $backupFile );
					}
				}

				$data_item['destination-status'][ time() ] = $error_array;
				//echo "destination-status<pre>"; print_r($data_item['destination-status']); echo "</pre>";
				//die();

				krsort( $data_item['destination-status'] );
				if ( count( $data_item['destination-status'] ) > 5 ) {
					$data_item['destination-status'] = array_slice( $data_item['destination-status'], 0, 5, true );
				}
				$data_item['destination'] = $item['destination'];
				$data_item['destination-directory'] = $item['destination-directory'];

//				echo "data_item<pre>"; print_r($data_item); echo "</pre>";
				//				die();

			} else {

				// We create an option to store the list of files we are sending. This is better than adding to the config data
				// for snapshot. Less loading of the master array. The list of files is a reference we pass to the sender function
				// of the destination. As files are sent they are removed from the array and the option is updated. So if something
				// happens we don't start from the first of the list. Could probably use a local file...
				$snapshot_sync_files_option = 'wpmudev_snapshot_sync_files_' . $item_key;
				$snapshot_sync_files = get_option( $snapshot_sync_files_option );

				if ( ! $snapshot_sync_files ) {
					$snapshot_sync_files = array();
				}

				$last_sync_timestamp = time();

				//$snapshot_logger->log_message("DEBUG: going to snapshot_gather_item_files");
				//$snapshot_logger->log_message("DEBUG: data_item<pre>". print_r($data_item, true), "</pre>");
				if ( ! isset( $data_item['blog-id'] ) ) {
					$data_item['blog-id'] = $item['blog-id'];
				}
				$gather_files_sync = $this->snapshot_gather_item_files( $data_item );
				foreach ( $data_item['files-sections'] as $file_section ) {
					if ( ( "config" === $file_section ) || ( "config" === $file_section ) ) {
						$file_section = "files";
					}

					if ( isset( $gather_files_sync['included'][ $file_section ] ) ) {
						if ( ! isset( $snapshot_sync_files['last-sync'][ $file_section ] ) ) {
							$snapshot_sync_files['last-sync'][ $file_section ] = 0;
						}

						foreach ( $gather_files_sync['included'][ $file_section ] as $_file_idx => $_file ) {
							if ( filemtime( $_file ) < $snapshot_sync_files['last-sync'][ $file_section ] ) {
								unset( $gather_files_sync['included'][ $file_section ][ $_file_idx ] );
							}
						}

						if ( ! isset( $snapshot_sync_files['included'][ $file_section ] ) ) {
							$snapshot_sync_files['included'][ $file_section ] = array();
						}

						if ( count( $gather_files_sync['included'][ $file_section ] ) ) {
							$snapshot_sync_files['included'][ $file_section ] = array_merge(
								$snapshot_sync_files['included'][ $file_section ],
								$gather_files_sync['included'][ $file_section ]
							);

							$snapshot_sync_files['included'][ $file_section ] = array_unique( $snapshot_sync_files['included'][ $file_section ] );
							$snapshot_sync_files['included'][ $file_section ] = array_values( $snapshot_sync_files['included'][ $file_section ] );
						}
						$snapshot_sync_files['last-sync'][ $file_section ] = $last_sync_timestamp;
					}
				}

				$destination_object->snapshot_logger = $snapshot_logger;
				$destination_object->snapshot_locker = $snapshot_locker;

				update_option( $snapshot_sync_files_option, $snapshot_sync_files );
				$error_array = $destination_object->syncfiles_to_remote( $destination, $snapshot_sync_files, $snapshot_sync_files_option );

				if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
					if ( ( isset( $error_array['errorArray'] ) ) && ( count( $error_array['errorArray'] ) ) ) {
						foreach ( $error_array['errorArray'] as $message ) {
							$snapshot_logger->log_message( "ERROR: " . $message );
						}
					}
				}

				if ( ! isset( $data_item['destination-status'] ) ) {
					$data_item['destination-status'] = array();
				}

				$data_item['destination-status'][ time() ] = $error_array;
				krsort( $data_item['destination-status'] );
				if ( count( $data_item['destination-status'] ) > 5 ) {
					$data_item['destination-status'] = array_slice( $data_item['destination-status'], 0, 5 );
				}
				$data_item['destination'] = $item['destination'];
				$data_item['destination-directory'] = $item['destination-directory'];

				// See if we still have the archive file.
				// First check where we originally placed it.
				if ( strlen( $data_item['filename'] ) ) {
					$backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
					if ( ! file_exists( $backupFile ) ) {

						// Then check is the detail Snapshot archive folder
						$current_backupFolder = $this->_settings['backupBaseFolderFull'];
						$backupFile = trailingslashit( $current_backupFolder ) . $data_item['filename'];
						if ( ! file_exists( $backupFile ) ) {
							return $data_item;
						}
					}

					//echo "backupFile=[". $backupFile ."]<br />";

					$snapshot_logger->log_message( "Sending Archive: " . basename( $backupFile ) );
					$snapshot_logger->log_message( "Destination: " . $destination['type'] . ": " . stripslashes( $destination['name'] ) );

					$error_array = $destination_object->sendfile_to_remote( $destination, $backupFile );
					//$snapshot_logger->log_message("DEBUG: error_array<pre>". print_r($error_array, true)."</pre>");

					if ( ( isset( $error_array['responseArray'] ) ) && ( count( $error_array['responseArray'] ) ) ) {
						foreach ( $error_array['responseArray'] as $message ) {
							$snapshot_logger->log_message( $message );
						}
					}

					if ( ( isset( $error_array['errorStatus'] ) ) && ( true === $error_array['errorStatus'] ) ) {
						if ( ( isset( $error_array['errorArray'] ) ) && ( count( $error_array['errorArray'] ) ) ) {
							foreach ( $error_array['errorArray'] as $message ) {
								$snapshot_logger->log_message( "ERROR: " . $message );
							}
						}
					}

					if ( isset( $item['store-local'] ) && ( 0 === $item['store-local'] ) ) {
						if ( ( isset( $error_array['sendFileStatus'] ) ) && ( true === $error_array['sendFileStatus'] ) ) {
							$snapshot_logger->log_message( "Local archive removed: " . basename( $backupFile ) );
							unlink( $backupFile );
						}
					}

					if ( ! isset( $data_item['destination-status'] ) ) {
						$data_item['destination-status'] = array();
					}

					$data_item['destination-status'][ time() ] = $error_array;
					krsort( $data_item['destination-status'] );
					if ( count( $data_item['destination-status'] ) > 5 ) {
						$data_item['destination-status'] = array_slice( $data_item['destination-status'], 0, 5 );
					}
					$data_item['destination'] = $item['destination'];
					$data_item['destination-directory'] = $item['destination-directory'];
				}
			}

			return $data_item;
		}

		/**
		 * Utility function called by WPCron scheduling dispatch. This function handles forwarding of files
		 * to remote destination.
		 *
		 * @since 1.0.7
		 * @see
		 *
		 * @param none
		 *
		 * @return void
		 */
		public function snapshot_remote_file_cron_proc() {

			global $wpdb;

			@ini_set( 'html_errors', 'Off' ); // phpcs:ignore
			@ini_set( 'zlib.output_compression', 'Off' ); // phpcs:ignore
			@set_time_limit( 0 ); // phpcs:ignore

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			$old_error_handler = set_error_handler( array( $this, 'snapshot_ErrorHandler' ) );

			if ( ( isset( $this->config_data['config']['memoryLimit'] ) ) && ( ! empty( $this->config_data['config']['memoryLimit'] ) ) ) {
				@ini_set( 'memory_limit', $this->config_data['config']['memoryLimit'] ); // phpcs:ignore
			}

			// If we are somehow called for an item_key not in our list then remove any future cron calls then die
			if ( ( ! isset( $this->config_data['items'] ) ) || ( ! count( $this->config_data['items'] ) ) ) {
				return;
			}

			// If we don't have any remote destinations...why are we here.
			if ( ( ! isset( $this->config_data['destinations'] ) ) || ( ! count( $this->config_data['destinations'] ) ) ) {
				return;
			}

			foreach ( $this->config_data['items'] as $item_key => $item ) {
				$this->process_item_remote_files( $item_key );
			}
		}

		/**
		 * Custom Error handler to trap critical error and log them
		 *
		 * @since 1.0.4
		 * @see
		 *
		 * @param errno , errstr, errfile, errline all provided by PHP
		 *
		 * @return void
		 */

		public function snapshot_ErrorHandler( $errno, $errstr, $errfile, $errline ) {
			//echo "errno[". $errno ."]<br />";
			//echo "errstr[". $errstr ."]<br />";
			//echo "errfile[". $errfile ."]<br />";
			//echo "errline[". $errline ."]<br />";

			$errType = '';
			if ( ( defined( 'E_ERROR' ) ) && ( E_ERROR === $errno ) ) {
				$errType = "Error";
			} else if ( ( defined( 'E_WARNING' ) ) && ( E_WARNING === $errno ) ) {
				$errType = "Warning";
			} else if ( ( defined( 'E_PARSE' ) ) && ( E_PARSE === $errno ) ) {
				$errType = "Parse";
			} else if ( ( defined( 'E_NOTICE' ) ) && ( E_NOTICE === $errno ) ) {
				$errType = "Notice";
			} else if ( ( defined( 'E_CORE_ERROR' ) ) && ( E_CORE_ERROR === $errno ) ) {
				$errType = "Error (core)";
			} else if ( ( defined( 'E_CORE_WARNING' ) ) && ( E_CORE_WARNING === $errno ) ) {
				$errType = "Warning (core)";
			} else if ( ( defined( 'E_COMPILE_ERROR' ) ) && ( E_COMPILE_ERROR === $errno ) ) {
				$errType = "Error (compile)";
			} else if ( ( defined( 'E_COMPILE_WARNING' ) ) && ( E_COMPILE_WARNING === $errno ) ) {
				$errType = "Warning (compile)";
			} else if ( ( defined( 'E_USER_ERROR' ) ) && ( E_USER_ERROR === $errno ) ) {
				$errType = "Error (user)";
			} else if ( ( defined( 'E_USER_WARNING' ) ) && ( E_USER_WARNING === $errno ) ) {
				$errType = "Warning (user)";
			} else if ( ( defined( 'E_USER_NOTICE' ) ) && ( E_USER_NOTICE === $errno ) ) {
				$errType = "Notice (user)";
			} else if ( ( defined( 'E_STRICT' ) ) && ( E_STRICT === $errno ) ) {
				$errType = "Strict";
			} else if ( ( defined( 'E_RECOVERABLE_ERROR' ) ) && ( E_RECOVERABLE_ERROR === $errno ) ) {
				$errType = "Error (recoverable)";
			} else if ( ( defined( 'E_DEPRECATED' ) ) && ( E_DEPRECATED === $errno ) ) {
				$errType = "Deprecated";
			} else if ( ( defined( 'E_USER_DEPRECATED' ) ) && ( E_USER_DEPRECATED === $errno ) ) {
				$errType = "Deprecated (user)";
			} else {
				$errType = "Unknown";
			}

			$error_string = $errType . ": errno:" . $errno . " " . $errstr . " " . $errfile . " on line " . $errline;

			if ( isset( $this->config_data['config']['errorReporting'][ $errno ]['log'] ) ) {

				// We need to check the logger because there might be an error BEFORE it is ready.
				if ( is_object( $this->snapshot_logger ) ) {
					// Build the error reporting message
					$this->snapshot_logger->log_message( $error_string );
				}
			}

			//if (!isset($this->config_data['config']['errorReporting'][$errno]['stop'])) {
			//	return;
			//}

			// This error code is not included in error_reporting
			// phpcs:ignore
			if ( ! ( error_reporting() & $errno ) ) {
				return;
			}

			$error_array = array();
			$error_array['errorStatus'] = true;
			$error_array['errorText'] = "<p>" . $error_string . "</p>";
			$error_array['responseText'] = "";

			echo wp_json_encode( $error_array );
			die();
		}

		public function snapshot_get_item_destination_path( $item = array(), $data_item = array(), $create_folder = true ) {
			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			// If not destination in the data_item we can't process.
			if ( ! isset( $data_item['destination'] ) ) {
				if ( ! isset( $item['destination'] ) ) {
					return;
				} else {
					$data_item['destination'] = $item['destination'];
				}
			}

			if ( ! isset( $data_item['destination-directory'] ) ) {
				if ( isset( $item['destination-directory'] ) ) {
					$data_item['destination-directory'] = $item['destination-directory'];
				} else {
					$data_item['destination-directory'] = '';
				}
			}

			if ( empty( $data_item['destination-directory'] ) ) {
				return;
			}

			$backupFolder = trim( $data_item['destination-directory'] );

			if ( ( empty( $data_item['destination'] ) ) || ( "local" === $data_item['destination'] ) ) {
				$backupFolder = str_replace( '[DEST_PATH]', $this->_settings['backupBaseFolderFull'], $backupFolder );
			} else {
				$destination_key = $data_item['destination'];
				$destmeta = ! empty( $this->config_data['destinations'][ $destination_key ] )
					? $this->config_data['destinations'][ $destination_key ]
					: array();
				// Do not expand DEST_PATH for google drive, it won't work
				if ( ! empty( $destmeta['type'] ) && 'google-drive' !== $destmeta['type'] ) {
					if ( isset( $this->config_data['destinations'][ $destination_key ]['directory'] ) ) {
						$d_directory = $this->config_data['destinations'][ $destination_key ]['directory'];
						$backupFolder = str_replace( '[DEST_PATH]', $d_directory, $backupFolder );
					} else {
						$backupFolder = str_replace( '[DEST_PATH]', '', $backupFolder );
					}
				} else {
					// Google drive doesn't support DEST_PATH expansion like that.
					// It actually doesn't like dynamic folders *at all*.
					// So, let's drop the whole thing and use the default destination path instead.
					$backupFolder = ! empty( $this->config_data['destinations'][ $destination_key ]['directory'] )
						? $this->config_data['destinations'][ $destination_key ]['directory']
						: ''
					;
				}
			}

			if ( is_multisite() ) {
				$blog_info = get_blog_details( $item['blog-id'] );
				if ( $blog_info->domain ) {
					$domain = $blog_info->domain;
				}
			} else {
				$siteurl = get_option( 'siteurl' );
				if ( $siteurl ) {
					$domain = wp_parse_url( $siteurl, PHP_URL_HOST );
				}
			}

			if ( ! isset( $domain ) ) {
				$domain = '';
			}

			$backupFolder = str_replace( '[SITE_DOMAIN]', $domain, $backupFolder );
			$backupFolder = str_replace( '[SNAPSHOT_ID]', $item['timestamp'], $backupFolder );

			// Only for local destination. If the destination path does not start with a leading slash (for absolute paths), then prepend
			// the site root path.
			if ( ( ( empty( $data_item['destination'] ) ) || ( "local" === $data_item['destination'] ) ) && ( ! empty( $backupFolder ) ) ) {
				if ( '/' !== substr( $backupFolder, 0, 1 ) ) {
					$backupFolder = trailingslashit( $home_path ) . $backupFolder;
				}
				if ( $create_folder ) {
					if ( ! file_exists( $backupFolder ) ) {
						wp_mkdir_p( $backupFolder );
					}
				}
			}
			return $backupFolder;
		}

		public function snapshot_ajax_view_log_proc() {

			check_ajax_referer( 'snapshot-view-log', 'snapshot-noonce-field');
			if ( ( isset( $_REQUEST['snapshot-item'] ) ) && ( isset( $_REQUEST['snapshot-data-item'] ) ) ) {
				$item_key = intval( $_REQUEST['snapshot-item'] );
				if ( isset( $this->config_data['items'][ $item_key ] ) ) {
					$item = $this->config_data['items'][ $item_key ];

					$data_item_key = intval( $_REQUEST['snapshot-data-item'] );
					if ( isset( $this->config_data['items'][ $item_key ]['data'][ $data_item_key ] ) ) {
						$data_item = $this->config_data['items'][ $item_key ]['data'][ $data_item_key ];

						$backupLogFileFull = trailingslashit( $this->get_setting( 'backupLogFolderFull' ) )
						                     . $item['timestamp'] . "_" . $data_item['timestamp'] . ".log";

						if ( file_exists( $backupLogFileFull ) ) {

							if ( isset( $_POST['snapshot-log-position'] ) ) {
								$log_position = intval( $_POST['snapshot-log-position'] );
							} else {
								$log_position = 0;
							}

							$log_file_information = array();
							$log_file_information['payload'] = '';
							$log_file_information['position'] = $log_position;

							global $wp_filesystem;

							if ( Snapshot_Helper_Utility::connect_fs() ) {
								//while ( ( $buffer = fgets( $handle, 4096 ) ) !== false ) {
								//	$log_file_information['payload'] .= $buffer . "<br />";
								//}
								$log_file_information_temp = $wp_filesystem->get_contents( $backupLogFileFull );
								$log_file_filesize = strlen( $log_file_information_temp );
								if ( $log_position >= $log_file_filesize ) {
									$log_file_information['payload'] = array();
									$log_file_information['payload'][] = "Error: unexpected fgets() fail\n";
								}
								if ( $log_position < $log_file_filesize - 1) {
									$log_file_information['payload'] = substr( $log_file_information_temp, $log_position, 10000 );
									$log_file_information['payload'] = nl2br( $log_file_information['payload'] );
								}

								if ( ( $log_position + 10000 ) >= $log_file_filesize )
									$log_file_information['position'] = $log_file_filesize - 1;
								else
									$log_file_information['position'] = $log_position + 10000;

								$log_file_information['filesize'] = $log_file_filesize;
								echo wp_json_encode( $log_file_information );
								die();
							}
							echo "<br /><br />";

						}
					}
				}
			}

			die();
		}

		public function snapshot_gather_item_files( $item ) {
			global $wpdb, $site_id;

			$item_files = array();
			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			if ( ( ! isset( $item['files-option'] ) ) || ( ! count( $item['files-option'] ) ) ) {
				return $item_files;
			}

			if ( "none" === $item['files-option'] ) {
				if ( ( isset( $item['files-sections'] ) ) && ( count( $item['files-sections'] ) ) ) {
					unset( $item['files-sections'] );
					$item['files-sections'] = array();
				}
			} else if ( "all" === $item['files-option'] ) {
				if ( is_main_site( $item['blog-id'] ) ) {
					$files_sections = array( 'themes', 'plugins', 'media' );

					if ( is_multisite() ) {
						$files_sections[] = 'mu-plugins';
					}

				} else {
					$files_sections = array( 'media' );
				}
			} else if ( "selected" === $item['files-option'] ) {
				$files_sections = $item['files-sections'];
			}

			if ( ( ! isset( $files_sections ) ) || ( ! count( $files_sections ) ) ) {
				return $item_files;
			}

			//global $is_IIS;
			//echo "is_IIS[". $is_IIS ."]<br />";
			//echo "iis7_supports_permalinks[". iis7_supports_permalinks() ."]<br />";
			//echo "files_sections<pre>"; print_r($files_sections); echo "</pre>";

			foreach ( $files_sections as $file_section ) {

				switch ( $file_section ) {
					case 'media':

						$_path = $home_path . Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] );
						$_path = str_replace( '\\', '/', $_path );

						//echo "_path[". $_path ."]<br />";
						$item_files['media'] = Snapshot_Helper_Utility::scandir( $_path );
						//echo "media files<pre>"; print_r($item_files['media']); echo "</pre>";
						//die();

						break;

					case 'plugins':
						$_path = untrailingslashit( $this->plugins_dir );
						$_path = str_replace( '\\', '/', $_path );
						$item_files['plugins'] = Snapshot_Helper_Utility::scandir( $_path );
						break;

					case 'mu-plugins':
						$_path = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
						$_path = str_replace( '\\', '/', $_path );
						$item_files['mu-plugins'] = Snapshot_Helper_Utility::scandir( $_path );
						break;

					case 'themes':
						$_path = trailingslashit( WP_CONTENT_DIR ) . 'themes';
						$_path = str_replace( '\\', '/', $_path );
						$item_files['themes'] = Snapshot_Helper_Utility::scandir( $_path );
						break;

					case 'config':
						$wp_config_file = trailingslashit( $home_path ) . "wp-config.php";
						//$wp_config_file = str_replace('\\', '/', $wp_config_file);

						if ( file_exists( $wp_config_file ) ) {

							if ( ! isset( $item_files['files'] ) ) {
								$item_files['files'] = array();
							}

							$item_files['files'][] = $wp_config_file;
						}
						break;

					case 'htaccess':
						$wp_htaccess_file = trailingslashit( $home_path ) . ".htaccess";
						//$wp_htaccess_file = str_replace('\\', '/', $wp_htaccess_file);
						if ( file_exists( $wp_htaccess_file ) ) {

							if ( ! isset( $item_files['files'] ) ) {
								$item_files['files'] = array();
							}

							$item_files['files'][] = $wp_htaccess_file;
						}

						$web_config_file = trailingslashit( $home_path ) . "web.config";
						//$web_config_file = str_replace('\\', '/', $web_config_file);
						if ( file_exists( $web_config_file ) ) {

							if ( ! isset( $item_files['files'] ) ) {
								$item_files['files'] = array();
							}

							$item_files['files'][] = $web_config_file;
						}

						break;

					default:
						break;
				}
			}

			//echo "item_files<pre>"; print_r($item_files); echo "</pre>";
			//die();

			if ( ! count( $item_files ) ) {
				return $item_files;
			}

			// Exclude files.
			$item_ignore_files = array();

			// With WP 3.5 fresh installs we have a slight issue. In prior versions of WP the main site upload folder and
			// related sub-site were seperate. Main site was typically /wp-content/uploads/ while sub-sites were
			// /wp-content/blogs.dir/X/files/
			// But in 3.5 when doing a fresh install, not upgrade, the sub-site upload path is beneath the main site.
			// main site /wp-content/uploads/ and sub-site wp-content/uploads/sites/X
			// So we have this added fun to try and exclude the sub-site from the main site's media. ug.
			$blog_id = intval( $item['blog-id'] );
			if ( ( is_multisite() ) && ( is_main_site( $blog_id ) ) ) {

				$main_site_upload_path = Snapshot_Helper_Utility::get_blog_upload_path( $blog_id );
				$sql_str = $wpdb->prepare( "SELECT blog_id FROM " . $wpdb->base_prefix . "blogs WHERE blog_id != %d AND site_id=%d LIMIT 5", $blog_id, $site_id );
				// We are using placeholders and $wpdb->prepare() inside the variable.
				// phpcs:ignore
				$blog_ids = $wpdb->get_col( $sql_str );
				if ( ! empty( $blog_ids ) ) {
					foreach ( $blog_ids as $blog_id_tmp ) {
						$sub_site_upload_path = Snapshot_Helper_Utility::get_blog_upload_path( $blog_id_tmp );
						if ( ! empty( $sub_site_upload_path ) ) {
							if ( ( $sub_site_upload_path !== $main_site_upload_path )
							     && ( substr( $sub_site_upload_path, 0, strlen( $main_site_upload_path ) ) === $main_site_upload_path )
							) {
								$item_ignore_files[] = dirname( $sub_site_upload_path );
							}
							break;
						}
					}
				}
			}

			//We auto exclude the snapshot tree. Plus any entered exclude entries from the form.
			$item_ignore_files[] = trailingslashit( $this->_settings['backupBaseFolderFull'] );
			$item_ignore_files[] = trailingslashit( $this->_settings['SNAPSHOT_PLUGIN_BASE_DIR'] );

			// Then we add any global excludes
			if ( ( isset( $this->config_data['config']['filesIgnore'] ) ) && ( count( $this->config_data['config']['filesIgnore'] ) ) ) {
				$item_ignore_files = array_merge( $item_ignore_files, $this->config_data['config']['filesIgnore'] );
			}

			// Then item excludes
			if ( ( isset( $item['files-ignore'] ) ) && ( count( $item['files-ignore'] ) ) ) {
				$item_ignore_files = array_merge( $item_ignore_files, $item['files-ignore'] );
			}

			$item_section_files = array();
			// Need to exclude the user ignore patterns as well as our Snapshot base folder. No backup of the backups
			foreach ( $item_files as $item_set_key => $item_set_files ) {
				if ( ( ! is_array( $item_set_files ) ) || ( ! count( $item_set_files ) ) ) {
					continue;
				}

				foreach ( $item_set_files as $item_set_files_key => $item_set_files_file ) {

					// We spin through all the files. They will fall into one of three sections...

					// If the file is not readable we ignore
					if ( ! is_readable( $item_set_files_file ) ) {

						if ( ! isset( $item_section_files['error'][ $item_set_key ] ) ) {
							$item_section_files['error'][ $item_set_key ] = array();
						}

						$item_section_files['error'][ $item_set_key ][] = $item_set_files_file;

					} else {

						$EXCLUDE_THIS_FILE = false;
						foreach ( $item_ignore_files as $item_ignore_file ) {
							// Make sure we don't have any blank entries.
							$item_ignore_file = trim( $item_ignore_file );
							if ( empty( $item_ignore_file ) ) {
								continue;
							}

							//echo "item_set_files_file<pre>"; print_r($item_set_files_file); echo "</pre>";
							//echo "item_ignore_file[". $item_ignore_file ."]<br />";
							$stristr_ret = stristr( $item_set_files_file, $item_ignore_file );
							if ( false !== $stristr_ret ) {
								$EXCLUDE_THIS_FILE = true;
								break;
							}
						}

						if ( false === $EXCLUDE_THIS_FILE ) {
							// If file is valid we keep it
							if ( ! isset( $item_section_files['included'][ $item_set_key ] ) ) {
								$item_section_files['included'][ $item_set_key ] = array();
							}

							$item_section_files['included'][ $item_set_key ][] = $item_set_files_file;

						} else {
							if ( ! isset( $item_section_files['excluded']['pattern'] ) ) {
								$item_section_files['excluded']['pattern'] = array();
							}

							$item_section_files['excluded']['pattern'][] = $item_set_files_file;
						}
					}
				}
			}
			//echo "item_section_files<pre>"; print_r($item_section_files); echo "</pre>";
			//die();
			return $item_section_files;
		}

		public function destination_register_proc( $name_class ) {

			//		echo "name_class=[". $name_class ."]<br />";
			//		if (class_exists($name_class)) {

			$classObject = new $name_class();
			if ( isset( $classObject->name_slug ) ) {
				if ( ! isset( $this->_settings['destinationClasses'][ $classObject->name_slug ] ) ) {
					$this->_settings['destinationClasses'][ $classObject->name_slug ] = $classObject;
				}
			}
			//		}
		}

		// Add body class to admin page if needed
		public function snapshot_maybe_add_body_classes( $classes ) {

			if ( ! isset( $_REQUEST['snapshot-action'] ) ) {
				if ( ! isset( $_REQUEST['snapshot-noonce-field']  ) ) {
					return $classes;
				}
				if ( ! wp_verify_nonce( $_REQUEST['snapshot-noonce-field'], 'snapshot-nonce' ) ) {
					return $classes;
				}
				return $classes;
			}

			$screen_id = $this->get_current_screen_id();
			$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );

			if ( 'new' === $snapshot_action || 'backup' === $snapshot_action ) {
				if ( 'snapshot_page_snapshot_pro_managed_backups' === $screen_id ) {
					$classes .= ( ' ' === substr( $classes, -1 ) ) ? 'snapshot_page_snapshot_pro_managed_backups_create ' : ' snapshot_page_snapshot_pro_managed_backups_create ';

				} else if ( 'snapshot_page_snapshot_pro_snapshots' === $screen_id ) {
					$classes .= ( ' ' === substr( $classes, -1 ) ) ? 'snapshot_page_snapshot_pro_snapshot_create ' : ' snapshot_page_snapshot_pro_snapshot_create ';
				}
			}
			if ( 'restore' === $snapshot_action ) {
				if ( 'snapshot_page_snapshot_pro_managed_backups' === $screen_id ) {
					$classes .= ( ' ' === substr( $classes, -1 ) ) ? 'snapshot_page_snapshot_pro_managed_backups_restore ' : ' snapshot_page_snapshot_pro_managed_backups_restore ';

				} else if ( 'snapshot_page_snapshot_pro_snapshots' === $screen_id ) {
					$classes .= ( ' ' === substr( $classes, -1 ) ) ? 'snapshot_page_snapshot_pro_snapshots_restore ' : ' snapshot_page_snapshot_pro_snapshots_restore ';
				}
			}

			return $classes;
		}

		/**
		 * Handles redirection to dashboard once activated
		 *
		 * @param string $plugin Activated plugin path.
		 * @param bool $network_activation Whether the activation was network-wide.
		 */
		public function snapshot_activated( $plugin, $network_activation ) {
			global $pagenow;
			if ( 'plugins.php' !== $pagenow ) {
				// Do not redirect if not on plugins page.
				return false;
			}

			if ( ! $network_activation ) {
				if ( preg_match( '/' . preg_quote( basename( __FILE__ ), '/' ) . '$/', $plugin ) ) {
					$dashboard_url = 'admin.php?page=snapshot_pro_dashboard';
					wp_safe_redirect( $dashboard_url );
					exit;
				}
			}
		}

		/** ------------------------------ 2.5 ----------------------------------------------- */

		private function class_loader( $class ) {

			do_action( 'snapshot_class_loader_pre_processing', $this );

			$basedir = dirname( __FILE__ );
			$class = trim( $class );

			if ( preg_match( '/^Snapshot_/', $class ) ) {
				$filename = $basedir . '/lib/' . str_replace( '_', DIRECTORY_SEPARATOR, $class ) . '.php';
				$filename = apply_filters( 'snapshot_class_file_override', $filename );
				if ( is_readable( $filename ) ) {
					include_once $filename;

					return true;
				}
			}

			return false;
		}

		/**
		 * @return string
		 */
		public function get_plugin_url() {
			return $this->plugin_url;
		}

		/**
		 * @return string
		 */
		public function get_plugin_path() {
			return $this->plugin_path;
		}

		/**
		 * Returns singleton instance of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @static
		 * @access public
		 *
		 * @return WPMUDEVSnapshot
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

}

$wpmudev_snapshot = WPMUDEVSnapshot::instance();