<?php
/*
Plugin Name: WPMU DEV Dashboard
Plugin URI: https://premium.wpmudev.org/project/wpmu-dev-dashboard/
Description: Brings the power of WPMU DEV direct to you, it'll revolutionize how you use WordPress, activate now!
Author: WPMU DEV
Version: 3.5.3
Author URI: http://premium.wpmudev.org/
Text Domain: wpmudev
Domain Path: /includes/languages/
Network: true
WDP ID: 119
 */

/*
Copyright 2007-2015 Incsub (http://incsub.com)
Author - Aaron Edwards
Contributors - Victor Ivanov, Vladislav Bailovic, Jeffri H, Marko Miljus

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class WPMUDEV_Dashboard {

	//------------------------------------------------------------------------//
	//---Config---------------------------------------------------------------//
	//------------------------------------------------------------------------//

	/**
	 * @var string
	 */
	var $version = '3.5.3';
	/**
	 * @var int
	 */
	var $theme_pack = 128;
	/**
	 * @var int $upfront The PID of the Upfront root theme
	 */
	var $upfront = 938297;
	/**
	 * @var int $legacy_themes IDs <= this number are legacy themes with minimal support
	 */
	var $legacy_themes = 237;
	/**
	 * @var string
	 */
	var $server_url;
	/**
	 * @var string
	 */
	var $server_root;
	/**
	 * @var
	 */
	var $plugin_dir;
	/**
	 * @var
	 */
	var $plugin_url;
	/**
	 * @var
	 */
	var $settings_url;
	/**
	 * @var bool
	 */
	var $dashboard_page = false;

	/**
	 *
	 */
	function __construct() {
		global $wp_version;

		$this->server_root = defined( 'WPMUDEV_CUSTOM_API_SERVER' ) ? WPMUDEV_CUSTOM_API_SERVER : 'https://premium.wpmudev.org/';
		$this->server_url  = trailingslashit( $this->server_root ) . 'wdp-un.php';

		//------------------------------------------------------------------------//
		//---Hook-----------------------------------------------------------------//
		//------------------------------------------------------------------------//

		add_action( 'admin_print_scripts-update-core.php', array( &$this, 'thickbox_script' ) );
		add_action( 'admin_print_styles-update-core.php', array( &$this, 'thickbox_style' ) );

		//refresh local projects where needed
		add_action( 'admin_init', array( &$this, 'schedule_refresh_local_projects' ) );
		add_action( 'update-core.php', array( &$this, 'refresh_local_projects' ) );
		add_action( 'load-plugins.php', array( &$this, 'refresh_local_projects' ) );
		add_action( 'load-update.php', array( &$this, 'refresh_local_projects' ) );
		add_action( 'load-update-core.php', array( &$this, 'refresh_local_projects' ) );
		add_action( 'load-themes.php', array( &$this, 'refresh_local_projects' ) );
		add_action( 'wp_update_plugins', array( &$this, 'refresh_local_projects' ) );
		add_action( 'wp_update_themes', array( &$this, 'refresh_local_projects' ) );
		add_action( 'delete_site_transient_update_plugins', array(
				&$this,
				'_refresh_local_projects'
			) ); //refresh after upgrade/install
		add_action( 'delete_site_transient_update_themes', array(
				&$this,
				'_refresh_local_projects'
			) ); //refresh after upgrade/install


		add_action( 'site_transient_update_plugins', array( &$this, 'filter_plugin_count' ) );
		add_action( 'site_transient_update_themes', array( &$this, 'filter_theme_count' ) );
		add_filter( 'plugins_api', array(
				&$this,
				'filter_plugin_info'
			), 101, 3 ); //run later to work with bad autoupdate plugins
		add_filter( 'themes_api', array(
				&$this,
				'filter_plugin_info'
			), 101, 3 ); //run later to work with bad autoupdate plugins

		add_filter( 'wp_prepare_themes_for_js', array(&$this, 'hide_upfront_theme'), 100);

		add_action( 'admin_init', array( &$this, 'first_redirect' ) );

		//localize the plugin
		add_action( 'plugins_loaded', array( &$this, 'localization' ) );

		// AJAX handlers
		add_action( 'wp_ajax_wpmudev_get_project_details', array( $this, 'ajax_get_project_details' ) );
		add_action( 'wp_ajax_wpmudev_support_post', array( $this, 'ajax_support_post' ) );
		add_action( 'wp_ajax_wpmudev_hide_install_message', array( $this, 'ajax_hide_install_message' ) );
		add_action( 'wp_ajax_nopriv_wpmudev_refresh_updates', array( $this, 'ajax_refresh_updates' ) );
		add_action( 'wp_ajax_wdpunauth', array( $this, 'authenticate_remote_access' ) );
		add_action( 'wp_ajax_nopriv_wdpunauth', array( $this, 'authenticate_remote_access' ) );

		// Form validation handlers
		add_action( 'wp_ajax_wpmudev_validate_username', array( $this, 'json_is_valid_username' ) );
		add_action( 'wp_ajax_wpmudev_validate_email', array( $this, 'json_is_valid_email' ) );
		add_action( 'wp_ajax_wpmudev_validate_password', array( $this, 'json_is_valid_password' ) );

		//moving all public hooks into a sep method so we can check for an allowed user
		add_action( 'init', array( &$this, 'load_branded_hooks' ) );

		$this->plugin_url = plugins_url( '', __FILE__ );

		//load special code if included in package
		if ( file_exists( dirname( __FILE__ ) . '/includes/custom-module.php' ) ) {
			include_once( dirname( __FILE__ ) . '/includes/custom-module.php' );
		}

		// Schedule update cron on main site only
		if ( is_main_site() ) {
			if ( ! wp_next_scheduled( 'wpmudev_scheduled_jobs' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'wpmudev_scheduled_jobs' );
			}

			add_action( 'wpmudev_scheduled_jobs', array( $this, 'refresh_updates' ) );
		} else if ( wp_next_scheduled( 'wpmudev_scheduled_jobs' ) ) {
			wp_clear_scheduled_hook( 'wpmudev_scheduled_jobs' ); //temporary code to clean out unneeded crons on subsites from previous versions
		}

		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}

	//------------------------------------------------------------------------//
	//---Functions------------------------------------------------------------//
	//------------------------------------------------------------------------//

	/**
	 *
	 */
	function install() {
		global $current_user;
		//reset allowed user on fresh activation
		if ( $current_user->ID != get_site_option( 'wdp_un_limit_to_user' ) ) {
			update_site_option( 'wdp_un_limit_to_user', $current_user->ID );
		}

		$this->refresh_updates();
	}

	/**
	 * Uses usermeta cache to store gravatar validity flag,
	 * in order to tighten up outgoing requests.
	 */
	function current_user_has_dev_gravatar() {
		global $current_user;
		if ( (int) get_user_meta( $current_user->ID, '_wdp_un_has_gravatar', true ) ) {
			return true;
		} // Already cached.

		$profile      = $this->get_profile();
		$has_gravatar = $this->_check_user_gravatar( $profile['profile']['gravatar'] );
		if ( $has_gravatar ) {
			update_user_meta( $current_user->ID, '_wdp_un_has_gravatar', 1 );
		}

		return $has_gravatar;
	}

	/**
	 * @param $gravatar
	 *
	 * @return bool
	 */
	private function _check_user_gravatar( $gravatar ) {
		// Extract clean gravatar request, short out if we fail
		if ( ! preg_match_all( '/src=[\'"](https?:\/\/.+\.gravatar.com\/avatar\/.+?\b)/', $gravatar, $parts ) ) {
			return false;
		}
		$link = isset( $parts[1][0] ) ? $parts[1][0] : false;
		if ( ! $link ) {
			return false;
		}

		// Construct a special, 404-fallback URL format,
		// see https://en.gravatar.com/site/implement/images/
		$link .= '?d=404';
		$response = wp_remote_get( $link, array(
			'timeout'    => 5,
			'user-agent' => 'UN Client/' . $this->version
		) );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $what
	 * @param $value
	 *
	 * @return bool
	 */
	private function _validation_request( $what, $value ) {
		$url      = $this->server_url . '?action=' . $what . '_exists&' . $what . '=' . $value;
		$response = wp_remote_get( $url, array(
			'timeout'    => 15,
			'sslverify'  => false,
			'user-agent' => 'UN Client/' . $this->version
		) );
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			return false;
		}

		return ! (bool) $response['body'];
	}

	/**
	 *
	 */
	function json_is_valid_username() {
		$valid = validate_username( $_POST['value'] );
		if ( ! $valid ) {
			die( json_encode( array(
				'status'  => 1,
				'message' => __( 'This is not a valid username', 'wpmudev' ),
			) ) );
		}
		$valid = $this->_validation_request( 'username', $_POST['value'] );
		if ( ! $valid ) {
			die( json_encode( array(
				'status'  => 1,
				'message' => __( 'This username is already taken, please try a different one', 'wpmudev' ),
			) ) );
		}

		die( json_encode( array( 'status' => 0 ) ) );
	}

	/**
	 *
	 */
	function json_is_valid_email() {
		$valid = is_email( $_POST['value'] );
		if ( ! $valid ) {
			die( json_encode( array(
				'status'  => 1,
				'message' => __( 'This is not a valid email', 'wpmudev' ),
			) ) );
		}
		$valid = $this->_validation_request( 'email', $_POST['value'] );
		if ( ! $valid ) {
			die( json_encode( array(
				'status'  => 1,
				'message' => __( 'This email is already registered', 'wpmudev' ),
			) ) );
		}
		die( json_encode( array( 'status' => 0 ) ) );
	}

	/**
	 *
	 */
	function json_is_valid_password() {
		$valid = strlen( $_POST['value'] ) > 4;
		if ( ! $valid ) {
			die( json_encode( array(
				'status'  => 1,
				'message' => __( 'Your password is too short', 'wpmudev' ),
			) ) );
		}
		die( json_encode( array( 'status' => 0 ) ) );
	}

	/**
	 *
	 */
	function load_branded_hooks() {
		if ( ! is_admin() ) {
			return false;
		}

		//if branding is on and not limited to
		if ( $this->allowed_user() ) {

			//add branded links to install/update process
			add_filter( 'install_plugin_complete_actions', array( &$this, 'install_plugin_complete_actions' ), 10, 3 );
			add_filter( 'install_theme_complete_actions', array( &$this, 'install_theme_complete_actions' ), 10, 4 );
			add_filter( 'update_plugin_complete_actions', array( &$this, 'update_plugin_complete_actions' ), 10, 2 );
			add_filter( 'update_theme_complete_actions', array( &$this, 'update_theme_complete_actions' ), 10, 2 );

			//get admin page location
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', array( &$this, 'plug_pages' ) );
				$this->settings_url  = network_admin_url( 'admin.php?page=wpmudev-settings' );
				$this->dashboard_url = network_admin_url( 'admin.php?page=wpmudev' );
				$this->updates_url   = network_admin_url( 'admin.php?page=wpmudev-updates' );
				$this->plugins_url   = network_admin_url( 'admin.php?page=wpmudev-plugins' );
				$this->themes_url    = network_admin_url( 'admin.php?page=wpmudev-themes' );
				$this->community_url = network_admin_url( 'admin.php?page=wpmudev-community' );
				$this->support_url   = network_admin_url( 'admin.php?page=wpmudev-support' );
				add_action( 'wp_network_dashboard_setup', array( &$this, 'register_dashboard_widget' ) );
			} else {
				add_action( 'admin_menu', array( &$this, 'plug_pages' ) );
				$this->settings_url  = admin_url( 'admin.php?page=wpmudev-settings' );
				$this->dashboard_url = admin_url( 'admin.php?page=wpmudev' );
				$this->updates_url   = admin_url( 'admin.php?page=wpmudev-updates' );
				$this->plugins_url   = admin_url( 'admin.php?page=wpmudev-plugins' );
				$this->themes_url    = admin_url( 'admin.php?page=wpmudev-themes' );
				$this->community_url = admin_url( 'admin.php?page=wpmudev-community' );
				$this->support_url   = admin_url( 'admin.php?page=wpmudev-support' );
				add_action( 'wp_dashboard_setup', array( &$this, 'register_dashboard_widget' ) );
			}

			require_once( dirname( __FILE__ ) . '/includes/notifications.php' );

			add_action( 'admin_init', array( &$this, 'filter_plugin_rows' ), 15 ); //make sure it runs after WP's
			//adds our stuff to update-core.php
			add_action( 'core_upgrade_preamble', array( &$this, 'list_updates' ) );

			//always load notification css
			add_action( 'admin_print_styles', array( &$this, 'notification_styles' ) );
		} else if ( ! defined( 'WPMUDEV_HIDE_BRANDING' ) ) { //if not allowed user and you want to completely hide branding, define WPMUDEV_HIDE_BRANDING
			add_action( 'admin_init', array( &$this, 'filter_plugin_rows' ), 15 ); //make sure it runs after WP's
			//adds our stuff to update-core.php
			add_action( 'core_upgrade_preamble', array( &$this, 'list_updates' ) );
			//always load notification css
			add_action( 'admin_print_styles', array( &$this, 'notification_styles' ) );
		}
	}

	/**
	 *
	 */
	function localization() {
		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's "languages" folder and name it "wpmudev-[value in wp-config].mo"
		load_plugin_textdomain( 'wpmudev', false, dirname( plugin_basename( __FILE__ ) ) . '/includes/languages/' );
	}

	/**
	 * Handles checking if plugin was just activated, and redirects to login page
	 */
	function first_redirect() {
		$redirected = get_site_option( 'wdp_un_redirected' );
		if ( is_admin() && ! defined( 'DOING_AJAX' ) && current_user_can( 'install_plugins' ) && ! $this->get_apikey() && ! $redirected && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'wpmudev' ) ) {
			update_site_option( 'wdp_un_redirected', 1 );
			wp_redirect( $this->dashboard_url );
			exit;
		}
	}

	/**
	 * Can plugins be automatically installed? Checks filesystem permissions and WP configuration to determine.
	 *
	 * @return bool can autoinstall
	 */
	function _can_auto_download_project( $type ) {
		$root                = $writable = false;
		$is_direct_access_fs = ( 'direct' == get_filesystem_method() ) // Are we dealing with direct access FS?
			? true : false;
		if ( 'plugin' == $type ) {
			$root = WP_PLUGIN_DIR;
			if ( empty( $root ) ) {
				$root = ABSPATH . 'wp-content/plugins';
			}
		} else {
			$root = WP_CONTENT_DIR . '/themes';
			if ( empty( $root ) ) {
				$root = ABSPATH . 'wp-content/themes';
			}
		}
		if ( $is_direct_access_fs ) {
			$writable = $root ? is_writable( $root ) : false;
		}

		// If we don't have write permissions, do we have FTP settings?
		$writable = $writable ? $writable : defined( 'FTP_USER' ) && defined( 'FTP_PASS' ) && defined( 'FTP_HOST' );

		// Lastly, if no other option worked, do we have SSH settings?
		$writable = $writable ? $writable : defined( 'FTP_USER' ) && defined( 'FTP_PUBKEY' ) && defined( 'FTP_PRIKEY' );

		return $writable;
	}

	/**
	 * Should the install message be shown to this user?
	 * @return bool
	 */
	function _install_message_is_hidden() {
		global $current_user;

		return get_user_meta( $current_user->ID, '_wpmudev_install_message', true );
	}

	/* Wrapper for backwards compatibility with 3.0
     * @return string url for the current admin location (single or network)
     */
	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	function self_admin_url( $path ) {
		if ( function_exists( 'self_admin_url' ) ) {
			return self_admin_url( $path );
		} else {
			return admin_url( $path );
		}
	}

	/**
	 * Get our special WDP ID header line from the file.
	 * @uses get_file_data()
	 * @return array of name, id, version
	 */
	function get_id_plugin( $plugin_file ) {
		return get_file_data( $plugin_file, array( 'name' => 'Plugin Name', 'id' => 'WDP ID', 'version' => 'Version' ) );
	}

	/**
	 * Checks if the current user is in the list of allowed users of the Dashboard.
	 * Allows for multiple users allowed in define, e.g. in this format:
	 * <code>
	 *  define("WPMUDEV_LIMIT_TO_USER", "1, 10, 15");
	 * </code>
	 *
	 * @return bool
	 */
	function allowed_user() {

		//balk if this is called too early
		if ( ! did_action( 'set_current_user' ) ) {
			return false;
		}

		//TODO calling this too soon bugs out in some wp installs http://premium.wpmudev.org/forums/topic/urgenti-lost-permission-after-upgrading#post-227543
		$user_id = get_current_user_id();
		if ( defined( 'WPMUDEV_LIMIT_TO_USER' ) && is_array( WPMUDEV_LIMIT_TO_USER ) ) {
			$allowed = array_map( "intval", WPMUDEV_LIMIT_TO_USER );
		} else if ( defined( 'WPMUDEV_LIMIT_TO_USER' ) ) {
			$allowed = array_map( "trim", explode( ',', WPMUDEV_LIMIT_TO_USER ) );
		} else if ( $allowed = get_site_option( 'wdp_un_limit_to_user' ) ) {
			$allowed = array( $allowed );
		} else {
			return true;
		}

		return in_array( $user_id, $allowed );
	}

	/**
	 * Get a human readable list of users with allowed permissions for the Dashboard
	 * @return string|bool
	 */
	function get_allowed_users() {
		global $current_user;

		if ( defined( 'WPMUDEV_LIMIT_TO_USER' ) && is_array( WPMUDEV_LIMIT_TO_USER ) ) {
			$allowed = array_map( "intval", WPMUDEV_LIMIT_TO_USER );
		} else if ( defined( 'WPMUDEV_LIMIT_TO_USER' ) ) {
			$allowed = array_map( "trim", explode( ',', WPMUDEV_LIMIT_TO_USER ) );
		} else if ( $allowed = get_site_option( 'wdp_un_limit_to_user' ) ) {
			$allowed = array( $allowed );
		} else {
			update_site_option( 'wdp_un_limit_to_user', $current_user->ID ); //not set, set to current user
			$allowed = array( $current_user->ID );
		}

		$usernames = array();
		foreach ( $allowed as $user_id ) {
			if ( $user_info = get_userdata( $user_id ) ) {
				$usernames[] = $user_info->display_name;
			}
		}

		if ( count( $usernames ) > 1 ) {
			return sprintf( __( 'Only the admin users "%s" have access to the WPMU DEV Dashboard plugin and features on this site.', 'wpmudev' ), implode( '", "', $usernames ) );
		} else if ( count( $usernames ) ) {
			return sprintf( __( 'Only the admin user "%s" has access to the WPMU DEV Dashboard plugin and features on this site.', 'wpmudev' ), implode( '", "', $usernames ) );
		} else {
			return false;
		}
	}

	/**
	 * Our own excerpt creater for listings
	 *
	 * @param string $text Text to be shrunk down
	 * @param int $charcount how many characters to limit to
	 *
	 * @return string excerpted text ending with ...
	 */
	function short_text( $text, $charcount ) {
		$text_count = strlen( $text );
		if ( $text_count <= $charcount ) {
			$text = $text;
		} else {
			$text = $text . " ";
			$text = wp_html_excerpt( $text, $charcount );
			$text = substr( $text, 0, strrpos( $text, ' ' ) );
			$text = $text . "...";
		}

		return $text;
	}

	/**
	 * Scans all folder locations and compiles a list of WPMU DEV plugins and themes and header data.
	 * Also saves 133 theme pack themes into option for later use.
	 * @return array local projects
	 */
	function get_projects() {
		$projects = array();

		//----------------------------------------------------------------------------------//
		//plugins directory
		//----------------------------------------------------------------------------------//
		$plugins_root = WP_PLUGIN_DIR;
		if ( empty( $plugins_root ) ) {
			$plugins_root = ABSPATH . 'wp-content/plugins';
		}

		$plugins_dir  = @opendir( $plugins_root );
		$plugin_files = array();
		if ( $plugins_dir ) {
			while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
				if ( substr( $file, 0, 1 ) == '.' ) {
					continue;
				}
				if ( is_dir( $plugins_root . '/' . $file ) ) {
					$plugins_subdir = @ opendir( $plugins_root . '/' . $file );
					if ( $plugins_subdir ) {
						while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
							if ( substr( $subfile, 0, 1 ) == '.' ) {
								continue;
							}
							if ( substr( $subfile, - 4 ) == '.php' ) {
								$plugin_files[] = "$file/$subfile";
							}
						}
					}
				} else {
					if ( substr( $file, - 4 ) == '.php' ) {
						$plugin_files[] = $file;
					}
				}
			}
		}
		@closedir( $plugins_dir );
		@closedir( $plugins_subdir );

		if ( $plugins_dir && ! empty( $plugin_files ) ) {
			foreach ( $plugin_files as $plugin_file ) {
				if ( is_readable( "$plugins_root/$plugin_file" ) ) {

					unset( $data );
					$data = $this->get_id_plugin( "$plugins_root/$plugin_file" );

					if ( isset( $data['id'] ) && ! empty( $data['id'] ) ) {
						$projects[ $data['id'] ]['type']     = 'plugin';
						$projects[ $data['id'] ]['version']  = $data['version'];
						$projects[ $data['id'] ]['filename'] = $plugin_file;
					}
				}
			}
		}

		//----------------------------------------------------------------------------------//
		// mu-plugins directory
		//----------------------------------------------------------------------------------//
		$mu_plugins_root = WPMU_PLUGIN_DIR;
		if ( empty( $mu_plugins_root ) ) {
			$mu_plugins_root = ABSPATH . 'wp-content/mu-plugins';
		}

		if ( is_dir( $mu_plugins_root ) && $mu_plugins_dir = @opendir( $mu_plugins_root ) ) {
			while ( ( $file = readdir( $mu_plugins_dir ) ) !== false ) {
				if ( substr( $file, - 4 ) == '.php' ) {
					if ( is_readable( "$mu_plugins_root/$file" ) ) {

						unset( $data );
						$data = $this->get_id_plugin( "$mu_plugins_root/$file" );

						if ( isset( $data['id'] ) && ! empty( $data['id'] ) ) {
							$projects[ $data['id'] ]['type']     = 'mu-plugin';
							$projects[ $data['id'] ]['version']  = $data['version'];
							$projects[ $data['id'] ]['filename'] = $file;
						}
					}
				}
			}
			@closedir( $mu_plugins_dir );
		}

		//----------------------------------------------------------------------------------//
		// wp-content directory
		//----------------------------------------------------------------------------------//
		$content_plugins_root = WP_CONTENT_DIR;
		if ( empty( $content_plugins_root ) ) {
			$content_plugins_root = ABSPATH . 'wp-content';
		}

		$content_plugins_dir  = @opendir( $content_plugins_root );
		$content_plugin_files = array();
		if ( $content_plugins_dir ) {
			while ( ( $file = readdir( $content_plugins_dir ) ) !== false ) {
				if ( substr( $file, 0, 1 ) == '.' ) {
					continue;
				}
				if ( ! is_dir( $content_plugins_root . '/' . $file ) ) {
					if ( substr( $file, - 4 ) == '.php' ) {
						$content_plugin_files[] = $file;
					}
				}
			}
		}
		@closedir( $content_plugins_dir );

		if ( $content_plugins_dir && ! empty( $content_plugin_files ) ) {
			foreach ( $content_plugin_files as $content_plugin_file ) {
				if ( is_readable( "$content_plugins_root/$content_plugin_file" ) ) {
					unset( $data );
					$data = $this->get_id_plugin( "$content_plugins_root/$content_plugin_file" );

					if ( isset( $data['id'] ) && ! empty( $data['id'] ) ) {
						$projects[ $data['id'] ]['type']     = 'drop-in';
						$projects[ $data['id'] ]['version']  = $data['version'];
						$projects[ $data['id'] ]['filename'] = $content_plugin_file;
					}
				}
			}
		}

		//----------------------------------------------------------------------------------//
		//themes directory
		//----------------------------------------------------------------------------------//
		$themes_root = WP_CONTENT_DIR . '/themes';
		if ( empty( $themes_root ) ) {
			$themes_root = ABSPATH . 'wp-content/themes';
		}

		$themes_dir   = @opendir( $themes_root );
		$themes_files = array();
		$local_themes = array();
		if ( $themes_dir ) {
			while ( ( $file = readdir( $themes_dir ) ) !== false ) {
				if ( substr( $file, 0, 1 ) == '.' ) {
					continue;
				}
				if ( is_dir( $themes_root . '/' . $file ) ) {
					$themes_subdir = @ opendir( $themes_root . '/' . $file );
					if ( $themes_subdir ) {
						while ( ( $subfile = readdir( $themes_subdir ) ) !== false ) {
							if ( substr( $subfile, 0, 1 ) == '.' ) {
								continue;
							}
							if ( substr( $subfile, - 4 ) == '.css' ) {
								$themes_files[] = "$file/$subfile";
							}
						}
					}
				} else {
					if ( substr( $file, - 4 ) == '.css' ) {
						$themes_files[] = $file;
					}
				}
			}
		}
		@closedir( $themes_dir );
		@closedir( $themes_subdir );

		if ( $themes_dir && ! empty( $themes_files ) ) {
			foreach ( $themes_files as $themes_file ) {

				//skip child themes
				if ( strpos( $themes_file, '-child' ) !== false ) {
					continue;
				}

				if ( is_readable( "$themes_root/$themes_file" ) ) {

					unset( $data );
					$data = $this->get_id_plugin( "$themes_root/$themes_file" );

					if ( isset( $data['id'] ) && ! empty( $data['id'] ) ) {
						$projects[ $data['id'] ]['type']     = 'theme';
						$projects[ $data['id'] ]['filename'] = substr( $themes_file, 0, strpos( $themes_file, '/' ) );

						//keep record of all themes for 133 themepack
						if ( $data['id'] == $this->theme_pack ) {
							$local_themes[ $themes_file ]['id']       = $data['id'];
							$local_themes[ $themes_file ]['filename'] = substr( $themes_file, 0, strpos( $themes_file, '/' ) );
							$local_themes[ $themes_file ]['version']  = $data['version'];
							//increment 133 theme pack version to lowest in all of them
							if ( isset( $projects[ $data['id'] ]['version'] ) && version_compare( $data['version'], $projects[ $data['id'] ]['version'], '<' ) ) {
								$projects[ $data['id'] ]['version'] = $data['version'];
							} else if ( ! isset( $projects[ $data['id'] ]['version'] ) ) {
								$projects[ $data['id'] ]['version'] = $data['version'];
							}
						} else {
							$projects[ $data['id'] ]['version'] = $data['version'];
						}
					}
				}
			}
		}
		update_site_option( 'wdp_un_local_themes', $local_themes );

		//----------------------------------------------------------------------------------//

		return $projects;
	}

	/**
	 * @return mixed
	 */
	function get_local_themes() {
		return get_site_option( 'wdp_un_local_themes' );
	}

	/**
	 *
	 */
	function schedule_refresh_local_projects() {

		if ( defined( 'WP_INSTALLING' ) ) {
			return false;
		}

		if ( current_user_can( 'update_plugins' ) ) {
			$this->get_local_projects(); //trigger refresh when necessary
		}
	}

	/**
	 * Does a filesystem scan for local plugins/themes and caches it. If any changes found it will trigger remote api
	 * check and calculate upgrades as well.
	 *
	 * @param bool $cache_reset default false. Set to true to skip change recognition and remote api check
	 *
	 * @return array
	 */
	function refresh_local_projects( $cache_reset = false ) {

		$local_projects = $this->get_projects();
		if ( ! $cache_reset ) {
			$saved_local_projects = $this->get_local_projects();

			//check for changes
			$saved_local_projects_md5 = md5( serialize( $saved_local_projects ) );
			$local_projects_md5       = md5( serialize( $local_projects ) );
			if ( $saved_local_projects_md5 != $local_projects_md5 ) {
				//refresh data as installed plugins have changed
				$data = $this->refresh_updates( $local_projects );
			}

			//recalculate upgrades with current/updated data
			$this->calculate_upgrades( $local_projects );
		}

		//save to be able to check for changes later
		set_site_transient( 'wpmudev_local_projects', $local_projects, 60 * 5 );

		return $local_projects;
	}

	/**
	 * Way to call refresh local projects from a hook, stripping passed arguments.
	 *
	 * @param $null
	 */
	function _refresh_local_projects( $null ) {
		$this->refresh_local_projects();
	}

	/**
	 * Compares the list of local plugins/themes against api data to determine available updates. Save the details to wdp_un_updates_available site option
	 * for later use.
	 *
	 * @param $local_projects array list of local projects from the transient
	 *
	 * @return array|bool
	 */
	function calculate_upgrades( $local_projects ) {
		$data            = $this->get_updates();
		$remote_projects = isset( $data['projects'] ) ? $data['projects'] : array();
		$updates         = array();

		//check for updates
		if ( is_array( $remote_projects ) ) {
			foreach ( $remote_projects as $id => $remote_project ) {
				if ( isset( $local_projects[ $id ] ) && is_array( $local_projects[ $id ] ) ) {
					//match
					$local_version = $local_projects[ $id ]['version'];

					//handle wp autoupgrades
					if ( $remote_project['autoupdate'] == '2' ) {
						if ( $local_projects[ $id ]['type'] == 'plugin' ) {
							$update_plugins = get_site_transient( 'update_plugins' );
							$remote_version = isset( $update_plugins->response[ $local_projects[ $id ]['filename'] ] ) ? $update_plugins->response[ $local_projects[ $id ]['filename'] ]->new_version : '';
						} else if ( $local_projects[ $id ]['type'] == 'theme' ) {
							$update_themes  = get_site_transient( 'update_themes' );
							$remote_version = isset( $update_themes->response[ $local_projects[ $id ]['filename'] ]['new_version'] ) ? $update_themes->response[ $local_projects[ $id ]['filename'] ]['new_version'] : '';
						} else {
							$remote_version = $remote_project['version'];
						}
						/**
						 * Allows excluding certain projects from update notifications.
						 *
						 * Basically just check the ID, and return true if you want to silence updates.
						 *
						 * @api wpmudev_project_ignore_updates
						 *
						 * @param bool $flag Defaults to false, return true to silence.
						 * @param int $id The WDP ID of the plugin/theme
						 */
					} else if ( apply_filters( 'wpmudev_project_ignore_updates', false, $id ) ) {
						$remote_version = $local_version;
					} else {
						$remote_version = $remote_project['version'];
					}

					if ( version_compare( $remote_version, $local_version, '>' ) ) {
						//add to array
						$updates[ $id ]                     = $local_projects[ $id ];
						$updates[ $id ]['url']              = $remote_project['url'];
						$updates[ $id ]['instructions_url'] = $remote_project['instructions_url'];
						$updates[ $id ]['support_url']      = $remote_project['support_url'];
						$updates[ $id ]['name']             = $remote_project['name'];
						$updates[ $id ]['thumbnail']        = $remote_project['thumbnail'];
						$updates[ $id ]['version']          = $local_version;
						$updates[ $id ]['new_version']      = $remote_version;
						$updates[ $id ]['changelog']        = $remote_project['changelog'];
						$updates[ $id ]['autoupdate']       = ( ( $local_projects[ $id ]['type'] == 'plugin' || $local_projects[ $id ]['type'] == 'theme' ) && $this->get_apikey() && isset( $data['downloads'] ) && $data['downloads'] == 'enabled' ) ? $remote_project['autoupdate'] : 0; //only allow autoupdates if installed in plugins
					}
				}
			}

			//record results
			update_site_option( 'wdp_un_updates_available', $updates );
		} else {
			return false;
		}

		return $updates;
	}

	/**
	 * Contacts the API to get the latest API updates data
	 *
	 * @param bool|array $local_projects Optional array of local projects.
	 *
	 * @return bool
	 */
	function refresh_updates( $local_projects = false ) {
		global $wpdb, $current_site, $wp_version;

		if ( defined( 'WP_INSTALLING' ) ) {
			return false;
		}

		//reset flag if it's set
		update_site_option( 'wdp_un_refresh_updates_flag', 0 );

		if ( ! is_array( $local_projects ) ) {
			$local_projects = $this->get_projects();
		}

		set_site_transient( 'wpmudev_local_projects', $local_projects, 60 * 5 );

		$api_key = $this->get_apikey();

		$projects = '';
		foreach ( $local_projects as $pid => $project ) {
			$projects .= "&p[$pid]=" . $project['version'];
		}

		//get WP/BP version string to help with support
		$wp = is_multisite() ? "WordPress Multisite $wp_version" : "WordPress $wp_version";
		if ( defined( 'BP_VERSION' ) ) {
			$wp .= ', BuddyPress ' . BP_VERSION;
		}

		//add blog count if multisite
		$blog_count = is_multisite() ? get_blog_count() : 1;

		$url = $this->server_url . '?action=check&un-version=' . $this->version . '&wp=' . urlencode( $wp ) . '&bcount=' . $blog_count . '&domain=' . urlencode( network_site_url() ) . '&key=' . urlencode( $api_key ) . $projects;

		$options = array(
			'timeout'    => 15,
			'sslverify'  => false,
			'user-agent' => 'UN Client/' . $this->version
		);

		$response = wp_remote_get( $url, $options );
		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$data = $response['body'];
			if ( $data != 'error' ) {
				$data = unserialize( $data );

				if ( is_array( $data ) ) {

					update_site_option( 'wdp_un_updates_data', $data );
					update_site_option( 'wdp_un_last_run_updates', time() ); //record update time

					//reset hiding permissions in case of membership change
					if ( ! $data['membership'] || $data['membership'] == 'free' ) { //free member
						update_site_option( 'wdp_un_hide_upgrades', 0 );
						update_site_option( 'wdp_un_hide_notices', 0 );
						update_site_option( 'wdp_un_hide_releases', 0 );
					} else if ( is_numeric( $data['membership'] ) ) { //single
						update_site_option( 'wdp_un_hide_notices', 0 );
						update_site_option( 'wdp_un_hide_releases', 0 );
					}

					$this->calculate_upgrades( $local_projects );

					return $data;
				} else {
					$this->api_error = 'Error unserializing remote response';

					return false;
				}
			} else {
				$this->api_error = 'API returned error';

				return false;
			}
		} else {
			$this->api_error = is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_response_code( $response ) . ' ' . wp_remote_retrieve_response_message( $response );

			//for network errors, set last run to 1 hour in future so it doesn't retry every single pageload (in case of server connection issues)
			update_site_option( 'wdp_un_last_run_updates', ( time() + HOUR_IN_SECONDS ) );

			return false;
		}
	}

	/**
	 * @return bool
	 */
	function refresh_profile() {
		global $wpdb, $current_site;

		if ( defined( 'WP_INSTALLING' ) ) {
			return false;
		}

		$api_key = $this->get_apikey();

		$url = $this->server_url . '?action=get_user_data&un-version=' . $this->version . '&key=' . urlencode( $api_key );

		$options = array(
			'timeout'    => 15,
			'sslverify'  => false,
			'user-agent' => 'UN Client/' . $this->version
		);

		$response = wp_remote_get( $url, $options );
		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$data = $response['body'];
			if ( $data != 'error' ) {

				$data = maybe_unserialize( $data );
				if ( is_array( $data ) ) {

					// 3.1.2 - 2012-06-26 PaulM Convert image urls for ssl admin
					if ( ( is_ssl() ) && ( isset( $data['profile']['gravatar'] ) ) ) {
						$data['profile']['gravatar'] = str_replace( "http://", "https://", $data['profile']['gravatar'] );
					}

					update_site_option( 'wdp_un_profile_data', $data );
					update_site_option( 'wdp_un_last_run_profile', time() ); //record update time

					return $data;
				} else {
					$this->api_error = 'Error unserializing remote response';

					return false;
				}
			} else {
				$this->api_error = 'API returned error';
				update_site_option( 'wpmudev_apikey', '' ); //api key reset
				return false;
			}
		} else {
			$this->api_error = is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_response_code( $response ) . ' ' . wp_remote_retrieve_response_message( $response );

			//for network errors, set last run to 1 hour in future so it doesn't retry every single pageload (in case of server connection issues)
			update_site_option( 'wdp_un_last_run_profile', ( time() + HOUR_IN_SECONDS ) );

			return false;
		}
	}

	/**
	 * @return bool
	 */
	function get_updates() {
		$expire = time() - ( HOUR_IN_SECONDS * 12 );
		if ( get_site_option( 'wdp_un_refresh_updates_flag' ) || $expire > get_site_option( 'wdp_un_last_run_updates' ) ) {
			return $this->refresh_updates();
		} else {
			return get_site_option( 'wdp_un_updates_data' );
		}
	}

	/**
	 * The proper way to get the array of profile data from cache/api
	 *
	 * @return array
	 */
	function get_profile() {
		$expire = time() - ( MINUTE_IN_SECONDS * 10 );
		if ( $expire > get_site_option( 'wdp_un_last_run_profile' ) ) {
			return $this->refresh_profile();
		} else {
			return get_site_option( 'wdp_un_profile_data' );
		}
	}

	/**
	 * The proper way to get the array of locally installed products from cache
	 *
	 * @return array
	 */
	function get_local_projects() {
		if ( false === ( $projects = get_site_transient( 'wpmudev_local_projects' ) ) ) {
			return $this->refresh_local_projects( true ); //set to true to avoid infinite loop
		}

		return $projects;
	}

	/**
	 * Get the api key from wherever it's set
	 *
	 * @return string
	 */
	function get_apikey() {
		if ( defined( 'WPMUDEV_APIKEY' ) ) {
			return WPMUDEV_APIKEY;
		}

		return get_site_option( 'wpmudev_apikey' );
	}

	/**
	 * Hooks into the plugin update api to add our custom api data
	 *
	 * @param $res
	 * @param $action
	 * @param $args
	 *
	 * @return stdClass
	 */
	function filter_plugin_info( $res, $action, $args ) {
		global $wp_version;
		$cur_wp_version = preg_replace( '/-.*$/', '', $wp_version );

		if ( ( $action == 'plugin_information' || $action == 'theme_information' ) && strpos( $args->slug, 'wpmudev_install' ) !== false ) {
			$string  = explode( '-', $args->slug );
			$id      = intval( $string[1] );
			$api_key = $this->get_apikey();
			$data    = $this->get_updates();

			//if in details iframe on update core page short-curcuit it
			if ( did_action( 'install_plugins_pre_plugin-information' ) && is_array( $data ) && isset( $data['projects'][ $id ] ) ) {
				//echo $data['projects'][$id]['changelog'];
				echo '<iframe width="100%" height="100%" border="0" style="border:none;" src="' . $this->server_url . '?action=details&id=' . $id . '"></iframe>';
				exit;
			}

			if ( $api_key && is_array( $data ) && isset( $data['projects'][ $id ] ) && $data['projects'][ $id ]['autoupdate'] == 1 ) {
				$res                = new stdClass;
				$res->name          = $data['projects'][ $id ]['name'];
				$res->slug          = sanitize_title( $data['projects'][ $id ]['name'] );
				$res->version       = $data['projects'][ $id ]['version'];
				$res->rating        = 100;
				$res->homepage      = $data['projects'][ $id ]['url'];
				$res->download_link = $this->server_url . "?action=install&key=$api_key&pid=$id";
				$res->tested        = $cur_wp_version;

				return $res;
			}
		}

		return $res;
	}

	/**
	 * Removes Upfront from being activatable in the theme browser
	 *
	 * @param $prepared_themes array
	 *
	 * @return array
	 */
	function hide_upfront_theme( $prepared_themes ) {

		unset( $prepared_themes['upfront'] );

		return $prepared_themes;
	}

	/**
	 * These filter the return action links when upgrading or installing DEV stuff
	 *
	 * @param $install_actions
	 * @param $api
	 * @param $plugin_file
	 *
	 * @return mixed
	 */
	function install_plugin_complete_actions( $install_actions, $api, $plugin_file ) {
		if ( isset( $api->download_link ) && strpos( $api->download_link, $this->server_url ) !== false ) {
			$install_actions['plugins_page'] = '<a href="' . $this->plugins_url . '" title="' . esc_attr( __( 'Return to WPMU DEV Plugins', 'wpmudev' ) ) . '" target="_parent">' . __( 'Return to WPMU DEV Plugins', 'wpmudev' ) . '</a>';
		}

		return $install_actions;
	}

	/**
	 * These filter the return action links when upgrading or installing DEV stuff
	 *
	 * @param $update_actions
	 * @param $plugin
	 *
	 * @return mixed
	 */
	function update_plugin_complete_actions( $update_actions, $plugin ) {
		$updates = get_site_transient( 'update_plugins' );
		if ( isset( $updates->response[ $plugin ] ) && strpos( $updates->response[ $plugin ]->package, $this->server_url ) !== false ) {
			$update_actions['plugins_page'] = '<a href="' . $this->updates_url . '" title="' . esc_attr( __( 'Return to WPMU DEV Available Updates', 'wpmudev' ) ) . '" target="_parent">' . __( 'Return to WPMU DEV Available Updates', 'wpmudev' ) . '</a>';
		}

		return $update_actions;
	}

	/**
	 * These filter the return action links when upgrading or installing DEV stuff
	 *
	 * @param $install_actions
	 * @param $api
	 * @param $stylesheet
	 * @param $theme_info
	 *
	 * @return mixed
	 */
	function install_theme_complete_actions( $install_actions, $api, $stylesheet, $theme_info ) {

		//if just installed an Upfront child theme and Upfront not installed warn them with a link
		if ( $theme_info->template == 'upfront' && $stylesheet != 'upfront' && ! $this->is_upfront_installed() && $install_link = $this->auto_install_url( $this->upfront ) ) {
			return array( 'install_upfront' => '<a id="install_upfront" href="' . $install_link . '" title="' . esc_attr( __( 'You must install the Upfront parent theme for this theme to work.', 'wpmudev' ) ) . '" target="_parent"><strong>' . __( 'Install Upfront (Required)', 'wpmudev' ) . '</strong></a>' );
		}

		//if we just installed upfront don't show them action links that won't work for the parent theme
		if ( $stylesheet == 'upfront' ) {
			unset( $install_actions['network_enable'] );
			unset( $install_actions['activate'] );
			unset( $install_actions['preview'] );
		}

		if ( isset($api->download_link) && strpos( $api->download_link, $this->server_url ) !== false ) {
			$install_actions['themes_page'] = '<a href="' . $this->themes_url . '" title="' . esc_attr( __( 'Return to WPMU DEV Themes', 'wpmudev' ) ) . '" target="_parent">' . __( 'Return to WPMU DEV Themes', 'wpmudev' ) . '</a>';
		}

		return $install_actions;
	}

	/**
	 * These filter the return action links when upgrading or installing DEV stuff
	 *
	 * @param $update_actions
	 * @param $theme
	 *
	 * @return mixed
	 */
	function update_theme_complete_actions( $update_actions, $theme ) {
		$updates = get_site_transient( 'update_themes' );
		if ( isset( $updates->response[ $theme ] ) && strpos( $updates->response[ $theme ]['package'], $this->server_url ) !== false ) {
			$update_actions['themes_page'] = '<a href="' . $this->updates_url . '" title="' . esc_attr( __( 'Return to WPMU DEV Available Updates', 'wpmudev' ) ) . '" target="_parent">' . __( 'Return to WPMU DEV Available Updates', 'wpmudev' ) . '</a>';
		}

		return $update_actions;
	}

	/**
	 *
	 */
	function filter_plugin_rows() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$updates = get_site_option( 'wdp_un_updates_available' );
		if ( is_array( $updates ) && count( $updates ) ) {
			foreach ( $updates as $id => $plugin ) {
				if ( $plugin['autoupdate'] != '2' ) {
					if ( $plugin['type'] == 'theme' ) {
						remove_all_actions( 'after_theme_row_' . $plugin['filename'] );
						add_action( 'after_theme_row_' . $plugin['filename'], array( &$this, 'plugin_row' ), 9, 2 );
					} else {
						remove_all_actions( 'after_plugin_row_' . $plugin['filename'] );
						add_action( 'after_plugin_row_' . $plugin['filename'], array( &$this, 'plugin_row' ), 9, 2 );
					}
				}
			}
		}

		$local_themes = $this->get_local_themes();
		if ( is_array( $local_themes ) && count( $local_themes ) ) {
			foreach ( $local_themes as $id => $plugin ) {
				remove_all_actions( 'after_theme_row_' . $plugin['filename'] );
				//only add the notice if specific version is wrong
				if ( isset( $updates[ $this->theme_pack ] ) && version_compare( $plugin['version'], $updates[ $this->theme_pack ]['new_version'], '<' ) ) {
					add_action( 'after_theme_row_' . $plugin['filename'], array( &$this, 'themepack_row' ), 9, 2 );
				}
			}
		}
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	function filter_plugin_count( $value ) {

		//remove any conflicting slug local WPMU DEV plugins from WP update notifications
		$local_projects = $this->get_local_projects();
		foreach ( $local_projects as $id => $plugin ) {
			if ( isset( $value->response[ $plugin['filename'] ] ) ) {
				unset( $value->response[ $plugin['filename'] ] );
			}
			if ( isset( $value->no_update[ $plugin['filename'] ] ) ) {
				unset( $value->no_update[ $plugin['filename'] ] );
			}
		}

		$updates = get_site_option( 'wdp_un_updates_available' );
		if ( is_array( $updates ) && count( $updates ) ) {
			$api_key            = $this->get_apikey();
			$enable_autoupdates = get_site_option( 'wdp_un_enable_autoupdates' );
			foreach ( $updates as $id => $plugin ) {
				if ( $plugin['type'] != 'theme' && $plugin['autoupdate'] != '2' ) {

					//build plugin class
					$object                 = new stdClass;
					$object->url            = $plugin['url'];
					$object->slug           = "wpmudev_install-$id";
					$object->upgrade_notice = $plugin['changelog'];
					$object->new_version    = $plugin['new_version'];
					if ( $plugin['autoupdate'] == '1' && $api_key ) {
						$object->package = $this->server_url . "?action=download&key=$api_key&pid=$id";
						//enable autoupdates of this plugin if allowed by user
						if ( $enable_autoupdates ) {
							$object->autoupdate = true;
						}
					} else {
						$object->package = '';
					}

					//add to class
					$value->response[ $plugin['filename'] ] = $object;
				}
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	function filter_theme_count( $value ) {

		$updates = get_site_option( 'wdp_un_updates_available' );
		if ( is_array( $updates ) && count( $updates ) ) {
			$api_key            = $this->get_apikey();
			$enable_autoupdates = get_site_option( 'wdp_un_enable_autoupdates' );
			foreach ( $updates as $id => $theme ) {
				if ( $theme['type'] == 'theme' && $theme['autoupdate'] != '2' ) {
					//build theme listing
					$value->response[ $theme['filename'] ]['url']         = $this->server_url . '?action=details&id=' . $id;
					$value->response[ $theme['filename'] ]['new_version'] = $theme['new_version'];

					if ( $theme['autoupdate'] == '1' && $api_key ) {
						$value->response[ $theme['filename'] ]['package'] = $this->server_url . "?action=download&key=$api_key&pid=$id";
						//enable autoupdates of this theme if allowed by user
						if ( $enable_autoupdates ) {
							$value->response[ $theme['filename'] ]['autoupdate'] = true;
						}
					} else {
						$value->response[ $theme['filename'] ]['package'] = '';
					}
				}
			}
		}

		//filter 133 theme pack themes from the list unless update is available
		$local_themes = $this->get_local_themes();
		if ( is_array( $local_themes ) && count( $local_themes ) ) {
			foreach ( $local_themes as $id => $theme ) {
				//add to count only if new version exists, otherwise remove
				if ( isset( $updates[ $theme['id'] ] ) && isset( $updates[ $theme['id'] ]['new_version'] ) && version_compare( $theme['version'], $updates[ $theme['id'] ]['new_version'], '<' ) ) {
					$value->response[ $theme['filename'] ]['new_version'] = $updates[ $theme['id'] ]['new_version'];
					$value->response[ $theme['filename'] ]['package']     = '';
				} else if ( isset( $value ) && isset( $value->response ) && isset( $theme['filename'] ) && isset( $value->response[ $theme['filename'] ] ) ) {
					unset( $value->response[ $theme['filename'] ] );
				}
			}
		}

		return $value;
	}

	/**
	 * @param $file
	 * @param $plugin_data
	 */
	function plugin_row( $file, $plugin_data ) {

		//get new version and update url
		$updates = get_site_option( 'wdp_un_updates_available' );
		if ( is_array( $updates ) && count( $updates ) ) {
			foreach ( $updates as $id => $plugin ) {
				if ( $plugin['filename'] == $file ) {
					$project_id = $id;
					$version    = $plugin['new_version'];
					$plugin_url = $plugin['url'];
					$autoupdate = $plugin['autoupdate'];
					$filename   = $plugin['filename'];
					$type       = $plugin['type'];
					break;
				}
			}
		} else {
			return false;
		}

		$plugins_allowedtags = array(
			'a'       => array( 'href' => array(), 'title' => array() ),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'em'      => array(),
			'strong'  => array()
		);
		$plugin_name         = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

		$update_url = $this->server_url . '?action=details&id=' . $project_id . '&TB_iframe=true&width=640&height=800';

		if ( $type == 'plugin' ) {
			$autoupdate_url = wp_nonce_url( $this->self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $filename, 'upgrade-plugin_' . $filename );
		} else if ( $type == 'theme' ) {
			$autoupdate_url = wp_nonce_url( $this->self_admin_url( 'update.php?action=upgrade-theme&theme=' ) . $filename, 'upgrade-theme_' . $filename );
		}

		if ( current_user_can( 'update_plugins' ) ) {
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">';
			if ( $autoupdate && $this->user_can_install( $project_id ) ) {
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">automatically update</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version, esc_url( $autoupdate_url ) );
			} else if ( $this->user_can_install( $project_id ) ) { //can only be manually installed
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s" target="_blank" title="Download update from WPMU DEV">download update</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version, esc_url( $plugin_url ) );
			} else if ( $this->allowed_user() && ! $this->get_apikey() ) { //no api key yet and they are activating user
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s" target="_blank" title="Setup your WPMU DEV account to update">configure to update</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version, $this->dashboard_url );
			} else if ( $this->allowed_user() ) { //no permissions to update
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s" target="_blank" title="Upgrade your WPMU DEV membership">upgrade to update</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version, apply_filters( 'wpmudev_project_upgrade_url', esc_url( 'https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode( $plugin_url ) . '#signup' ), $project_id ) );
			} else {
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version );
			}
			echo '</div></td></tr>';
		}
	}

	/**
	 * @param $file
	 * @param $plugin_data
	 */
	function themepack_row( $file, $plugin_data ) {

		//get new version and update url
		$updates = get_site_option( 'wdp_un_updates_available' );
		if ( isset( $updates[ $this->theme_pack ] ) ) {
			$plugin     = $updates[ $this->theme_pack ];
			$project_id = $this->theme_pack;
			$version    = $plugin['new_version'];
			$plugin_url = $plugin['url'];
		} else {
			return false;
		}

		$plugins_allowedtags = array(
			'a'       => array( 'href' => array(), 'title' => array() ),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'em'      => array(),
			'strong'  => array()
		);
		$plugin_name         = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

		$update_url = $this->server_url . '?action=details&id=' . $project_id . '&TB_iframe=true&width=640&height=800';

		if ( current_user_can( 'update_themes' ) ) {
			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">';
			if ( $this->user_can_install( $project_id ) ) {
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s" target="_blank" title="Download update from WPMU DEV">download update</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version, esc_url( $plugin_url ) );
			} else { //no permissions to update
				printf( __( 'There is a new version of %1$s available on WPMU DEV. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s" target="_blank" title="Upgrade your WPMU DEV membership">upgrade to update</a>.', 'wpmudev' ), $plugin_name, esc_url( $update_url ), esc_attr( $plugin_name ), $version, apply_filters( 'wpmudev_project_upgrade_url', esc_url( 'https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode( $plugin_url ) . '#signup' ), $project_id ) );
			}
			echo '</div></td></tr>';
		}
	}

	/**
	 *
	 */
	function list_updates() {

		$updates = get_site_option( 'wdp_un_updates_available' );
		if ( ! is_array( $updates ) || ( is_array( $updates ) && ! count( $updates ) ) ) {
			echo '<h3>' . __( 'WPMU DEV Plugins/Themes', 'wpmudev' ) . '</h3>';
			echo '<p>' . __( 'Your plugins/themes from WPMU DEV are all up to date.', 'wpmudev' ) . '</p>';

			return;
		}
		?>
		<h3><?php _e( 'WPMU DEV Plugins/Themes', 'wpmudev' ); ?></h3>
		<p><?php _e( 'The following plugins/themes from WPMU DEV have new versions available.', 'wpmudev' ); ?></p>
		<table class="widefat" cellspacing="0" id="update-plugins-table">
			<thead>
			<tr>
				<th scope="col" class="manage-column"><label><?php _e( 'Name', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Links', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Installed Version', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Latest Version', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Actions', 'wpmudev' ); ?></label></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" class="manage-column"><label><?php _e( 'Name', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Links', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Installed Version', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Latest Version', 'wpmudev' ); ?></label></th>
				<th scope="col" class="manage-column"><label><?php _e( 'Actions', 'wpmudev' ); ?></label></th>
			</tr>
			</tfoot>
			<tbody class="plugins">
			<?php
			$jquery = '';
			foreach ( (array) $updates as $id => $plugin ) {
				$screenshot = $plugin['thumbnail'];

				if ( $this->user_can_install( $id ) && $plugin['autoupdate'] && $plugin['type'] == 'plugin' ) {
					$upgrade_button_code = "<a href='" . wp_nonce_url( $this->self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin['filename'], 'upgrade-plugin_' . $plugin['filename'] ) . "' class='button-secondary'><i class='wdvicon-upload-alt'></i> " . __( 'Auto Update', 'wpmudev' ) . "</a>";
				} else if ( $this->user_can_install( $id ) && $plugin['autoupdate'] && $plugin['type'] == 'theme' ) {
					$upgrade_button_code = "<a href='" . wp_nonce_url( $this->self_admin_url( 'update.php?action=upgrade-theme&theme=' ) . $plugin['filename'], 'upgrade-theme_' . $plugin['filename'] ) . "' class='button-secondary'><i class='wdvicon-upload-alt'></i> " . __( 'Auto Update', 'wpmudev' ) . "</a>";
				} else if ( $this->user_can_install( $id ) ) {
					$upgrade_button_code = "<a href='" . esc_url( $plugin['url'] ) . "' class='button-secondary' target='_blank'><i class='wdvicon-download-alt'></i> " . __( 'Download Update', 'wpmudev' ) . "</a>";
					$jquery .= "<script type='text/javascript'>jQuery(\"input:checkbox[value='" . esc_attr( $plugin['filename'] ) . "']\").remove();</script>\n";
				} else if ( $this->allowed_user() && ! $this->get_apikey() ) { //no api key yet
					$upgrade_button_code = "<a href='" . $this->dashboard_url . "' class='button-secondary' title='" . __( 'Setup your WPMU DEV account to update', 'wpmudev' ) . "' target='_blank'><i class='wdvicon-pencil'></i> " . __( 'Configure to Update', 'wpmudev' ) . "</a>";
					$jquery .= "<script type='text/javascript'>jQuery(\"input:checkbox[value='" . esc_attr( $plugin['filename'] ) . "']\").remove();</script>\n";
				} else if ( $this->allowed_user() ) {
					$upgrade_button_code = "<a href='" . apply_filters( 'wpmudev_project_upgrade_url', esc_url( 'https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode( $plugin['url'] ) . '#signup' ), $id ) . "' class='button-secondary' target='_blank'><i class='wdvicon-arrow-up'></i> " . __( 'Upgrade to Update', 'wpmudev' ) . "</a>";
					$jquery .= "<script type='text/javascript'>jQuery(\"input:checkbox[value='" . esc_attr( $plugin['filename'] ) . "']\").remove();</script>\n";
				} else {
					$upgrade_button_code = "";
					$jquery .= "<script type='text/javascript'>jQuery(\"input:checkbox[value='" . esc_attr( $plugin['filename'] ) . "']\").remove();</script>\n";
				}


				echo "
				<tr class='active'>
				<td class='plugin-title'><a href='{$this->server_url}?action=description&id={$id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __( '%s Details', 'wpmudev' ), $plugin['name'] ) . "'><img src='$screenshot' width='80' height='60' style='float:left; padding: 5px' /><strong>{$plugin['name']}</strong></a>" . sprintf( __( 'You have version %1$s installed. Update to %2$s.' ), $plugin['version'], $plugin['new_version'] ) . "</td>
				<td style='vertical-align:middle;width:200px;'><a href='{$this->server_url}?action=help&id={$id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __( '%s Installation & Use Instructions', 'wpmudev' ), $plugin['name'] ) . "'><i class='wdvicon-info-sign'></i> " . __( 'Installation & Use Instructions', 'wpmudev' ) . "</a><br /><a target='_blank' href='{$plugin['support_url']}'><i class='wdvicon-question-sign'></i> " . __( 'Get Support', 'wpmudev' ) . "</a></td>
				<td style='vertical-align:middle'><strong>{$plugin['version']}</strong></td>
				<td style='vertical-align:middle'><strong><a href='{$this->server_url}?action=details&id={$id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __( 'View version %s details', 'wpmudev' ), $plugin['new_version'] ) . "'>{$plugin['new_version']}</a></strong></td>
				<td style='vertical-align:middle'>$upgrade_button_code</td>
				</tr>";
			}
			?>
			</tbody>
		</table>
		<br/>
		<?php
		//disable checkboxes for 133 theme pack themes
		$local_themes = $this->get_local_themes();
		if ( is_array( $local_themes ) && count( $local_themes ) ) {
			foreach ( $local_themes as $id => $theme ) {
				$jquery .= "<script type='text/javascript'>jQuery(\"input:checkbox[value='" . esc_attr( $theme['filename'] ) . "']\").remove();</script>\n";
			}
		}
		echo $jquery;
	}

	/**
	 *
	 */
	function plug_pages() {

		$updates = get_site_option( 'wdp_un_updates_available' );
		$count   = ( is_array( $updates ) ) ? count( $updates ) : 0;
		if ( $count > 0 ) {
			$count_output = ' <span class="updates-menu"><span class="update-plugins"><span class="updates-count count-' . $count . '">' . $count . '</span></span></span>';
		} else {
			$count_output = ' <span class="updates-menu"></span>';
		}

		$data = $this->get_updates();

		//allow override of menu location
		if ( ! defined( 'WPMUDEV_MENU_LOCATION' ) ) {
			define( 'WPMUDEV_MENU_LOCATION', '3.012' );
		}

		//dashboard page
		$page = add_menu_page( __( 'WPMU DEV Dashboard', 'wpmudev' ), __( 'WPMU DEV', 'wpmudev' ) . $count_output, 'manage_options', 'wpmudev', array(
				&$this,
				'dashboard_output'
			), $this->plugin_url . '/includes/images/icon.png', WPMUDEV_MENU_LOCATION );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'dashboard_script' ) );

		add_submenu_page( 'wpmudev', __( 'WPMU DEV Dashboard', 'wpmudev' ), __( 'Dashboard', 'wpmudev' ), 'manage_options', 'wpmudev', array(
				&$this,
				'dashboard_output'
			) );

		//only show if not a connection error
		if ( is_array( $data ) ) {

			//plugins page
			$page = add_submenu_page( 'wpmudev', __( 'WPMU DEV Plugins', 'wpmudev' ), __( 'Plugins', 'wpmudev' ), 'install_plugins', 'wpmudev-plugins', array(
					&$this,
					'plugins_output'
				) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'listings_script' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'thickbox_script' ) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'thickbox_style' ) );

			//themes page
			$page = add_submenu_page( 'wpmudev', __( 'WPMU DEV Themes', 'wpmudev' ), __( 'Themes', 'wpmudev' ), 'install_themes', 'wpmudev-themes', array(
					&$this,
					'themes_output'
				) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'listings_script' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'thickbox_script' ) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'thickbox_style' ) );
		}

		//support page
		$page = add_submenu_page( 'wpmudev', __( 'WPMU DEV Support', 'wpmudev' ), __( 'Support', 'wpmudev' ), 'manage_options', 'wpmudev-support', array(
				&$this,
				'support_output'
			) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'support_script' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'thickbox_script' ) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'thickbox_style' ) );

		//only show if not a connection error
		if ( is_array( $data ) ) {

			//community page
			$page = add_submenu_page( 'wpmudev', __( 'WPMU DEV Community', 'wpmudev' ), __( 'Community', 'wpmudev' ), 'manage_options', 'wpmudev-community', array(
					&$this,
					'community_output'
				) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'community_script' ) );

			//updates available page
			$page = add_submenu_page( 'wpmudev', __( 'WPMU DEV Updates', 'wpmudev' ), __( 'Updates', 'wpmudev' ) . $count_output, 'update_plugins', 'wpmudev-updates', array(
					&$this,
					'updates_output'
				) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'thickbox_script' ) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'thickbox_style' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'updates_script' ) );
			add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
		}

		$page = add_submenu_page( 'wpmudev', __( 'WPMU DEV Settings', 'wpmudev' ), __( 'Manage', 'wpmudev' ), is_multisite() ? 'manage_network_options' : 'manage_options', 'wpmudev-settings', array(
				&$this,
				'settings_output'
			) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_styles' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'settings_script' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_icon' ) );
	}

	/**
	 *
	 */
	function thickbox_script() {
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'plugin-install' );
	}

	/**
	 *
	 */
	function thickbox_style() {
		wp_enqueue_style( 'thickbox' );
	}

	/**
	 *
	 */
	function admin_styles() {
		global $wp_version;

		wp_enqueue_style( 'wpmudev-admin-google_fonts', 'https://fonts.googleapis.com/css?family=Lato:300,400,700,400italic', false, $this->version );

		wp_enqueue_style( 'wpmudev-admin-css', plugins_url( 'includes/css/admin.css', __FILE__ ), array( 'wpmudev-admin-google_fonts' ), $this->version );

		//hide all admin notices from another source on these pages
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'network_admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		$this->dashboard_page = true;
	}

	/**
	 *
	 */
	function admin_icon() {
		global $wp_version;

		if ( $wp_version >= 3.8 ) {
			wp_register_style( 'wpmudev-admin-38', plugins_url( 'includes/css/admin-icon.css', __FILE__ ) );
			wp_enqueue_style( 'wpmudev-admin-38' );
		}
	}

	/**
	 *
	 */
	function dashboard_script() {
		wp_enqueue_script( 'doubleSuggest', plugins_url( 'includes/js/doubleSuggest.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_enqueue_script( 'wpmudev-dashboard', plugins_url( 'includes/js/dashboard.js', __FILE__ ), array(
				'jquery',
				'doubleSuggest'
			), $this->version );
		wp_localize_script( 'wpmudev-dashboard', 'suggestedProjects', $this->autosuggest_data( 'all' ) );
	}

	/**
	 *
	 */
	function listings_script() {
		$suggest = 'all';
		$screen  = get_current_screen();
		if ( is_object( $screen ) && strstr( $screen->base, 'themes' ) ) {
			$suggest = 'theme';
		} else if ( is_object( $screen ) && strstr( $screen->base, 'plugins' ) ) {
			$suggest = 'plugin';
		}

		$class = ( 'themes' == $suggest ) ? 'themes' : 'plugins';
		add_filter( 'admin_body_class', create_function( '$cls', 'return preg_match("/wpmu-dev_page_wpmudev-' . $class . '/", $cls) ? $cls : "{$cls} wpmu-dev_page_wpmudev-' . $class . '";' ) );
		wp_enqueue_script( 'doubleSuggest', plugins_url( 'includes/js/doubleSuggest.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_enqueue_script( 'tinysort', plugins_url( 'includes/js/jquery.tinysort.min.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_enqueue_script( 'wpmudev-listings', plugins_url( 'includes/js/listings.js', __FILE__ ), array(
				'jquery',
				'doubleSuggest',
				'tinysort'
			), $this->version );
		wp_localize_script( 'wpmudev-listings', 'suggestedProjects', $this->autosuggest_data( $suggest ) );
		wp_localize_script( 'wpmudev-listings', 'project_screenshots', $this->screenshots_data( $suggest ) );
		wp_localize_script( 'wpmudev-listings', 'project_tags', $this->tags_data( $suggest ) );
		wp_localize_script( 'wpmudev-listings', 'loading_spinner', plugins_url( 'includes/images/spinner-dark.gif', __FILE__ ) );
	}

	/**
	 *
	 */
	function support_script() {
		add_filter( 'admin_body_class', create_function( '$cls', 'return preg_match("/wpmu-dev_page_wpmudev-support/", $cls) ? $cls : "{$cls} wpmu-dev_page_wpmudev-support";' ) );
		wp_enqueue_script( 'doubleSuggest', plugins_url( 'includes/js/doubleSuggest.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_enqueue_script( 'wpmudev-support', plugins_url( 'includes/js/support.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_localize_script( 'wpmudev-support', 'suggestedProjects', $this->autosuggest_data( 'all' ) );
	}

	/**
	 *
	 */
	function community_script() {
		add_filter( 'admin_body_class', create_function( '$cls', 'return preg_match("/wpmu-dev_page_wpmudev-community/", $cls) ? $cls : "{$cls} wpmu-dev_page_wpmudev-community";' ) );
		wp_enqueue_script( 'wpmudev-community', plugins_url( 'includes/js/community.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 *
	 */
	function updates_script() {
		add_filter( 'admin_body_class', create_function( '$cls', 'return preg_match("/wpmu-dev_page_wpmudev-updates/", $cls) ? $cls : "{$cls} wpmu-dev_page_wpmudev-updates";' ) );
		wp_enqueue_script( 'wpmudev-updates', plugins_url( 'includes/js/updates.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Enqueue styles for the settings page
	 */
	function settings_script() {
		add_filter( 'admin_body_class', create_function( '$cls', 'return preg_match("/wpmu-dev_page_wpmudev-settings/", $cls) ? $cls : "{$cls} wpmu-dev_page_wpmudev-settings";' ) );
		wp_enqueue_script( 'wpmudev-settings', plugins_url( 'includes/js/settings.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Enqueue styles for notifications
	 */
	function notification_styles() {
		if ( ! $this->dashboard_page ) {
			wp_enqueue_style( 'wpmudev-notification-css', plugins_url( 'includes/css/notifications.css', __FILE__ ), false, $this->version );
		}
	}


	/**
	 * @param string $type
	 *
	 * @return array
	 */
	function autosuggest_data( $type = 'all' ) {
		$suggest = array();
		$data    = $this->get_updates();
		if ( isset( $data['projects'] ) && is_array( $data['projects'] ) ) {
			foreach ( $data['projects'] as $id => $project ) {
				if ( $type !== 'all' && $project['type'] != $type ) {
					continue;
				}
				if ( $project['type'] != 'plugin' && $project['type'] != 'theme' ) {
					continue;
				}
				//skip multisite only products if not compatible
				if ( $project['requires'] == 'ms' && ! is_multisite() ) {
					continue;
				}
				//skip buddypress only products if not active
				if ( $project['requires'] == 'bp' && ! defined( 'BP_VERSION' ) ) {
					continue;
				}
				//skip lite products if full member
				if ( isset( $data['membership'] ) && $data['membership'] == 'full' && $project['paid'] == 'lite' ) {
					continue;
				}
				//skip showing Upfront root, Protected Content, and old Membership
				if ( in_array( $id, array( $this->upfront, 140, 928907 ) ) ) continue;

				$suggest[] = array( 'id' => $id, 'name' => stripslashes( $project['name'] ), 'type' => $project['type'] );
			}
		}

		return $suggest;
	}

	/**
	 * Get the screenshots array for given project types, used for json generation
	 *
	 * @param $type 'plugin' or 'theme'
	 *
	 * @return array
	 */
	function screenshots_data( $type ) {
		$shots = array();
		$data  = $this->get_updates();
		if ( isset( $data['projects'] ) && is_array( $data['projects'] ) ) {
			foreach ( $data['projects'] as $id => $project ) {
				if ( $project['type'] != $type ) {
					continue;
				}

				$shots[ $id ] = isset( $project['screenshots'] ) ? $project['screenshots'] : array();
			}
		}

		return $shots;
	}

	/**
	 * Get's a list of tags for given project type. Used for search or dropdowns
	 * @param $type 'plugin' or 'theme'
	 *
	 * @return array
	 */
	function tags_data( $type ) {
		$data = $this->get_updates();
		if ( $type == 'plugin' ) {
			return isset( $data['plugin_tags'] ) ? $data['plugin_tags'] : array();
		} else if ( $type == 'theme' ) {
			return isset( $data['theme_tags'] ) ? $data['theme_tags'] : array();
		}
	}

	/**
	 * returns an array of project id's for given search string
	 *
	 * @param $string
	 * @param string $type
	 *
	 * @return array
	 */
	function search_projects( $string, $type = 'all' ) {
		$search  = trim( $string );
		$results = array();
		$data    = $this->get_updates();
		if ( is_array( $data['projects'] ) ) {
			foreach ( $data['projects'] as $id => $project ) {
				if ( $type !== 'all' && $project['type'] != $type ) {
					continue;
				}
				if ( $project['type'] != 'plugin' && $project['type'] != 'theme' ) {
					continue;
				}

				//check if it's in the name or description
				if ( stripos( stripslashes( $project['name'] ), $search ) !== false || stripos( stripslashes( $project['short_description'] ), $search ) !== false ) {
					$results[] = $id;
				}
			}
		}

		return $results;
	}

	/**
	 * Handle notice dismissals, usually via ajax or GET fallback
	 */
	function handle_dismiss() {
		if ( isset( $_REQUEST['dismiss'] ) ) {
			$dismiss = array( 'id' => intval( $_REQUEST['dismiss'] ), 'expire' => strtotime( "+1 month" ) );
			update_site_option( 'wdp_un_dismissed', $dismiss );
			?>
			<div class="updated fade"><p><?php _e( 'Notice dismissed.', 'wpmudev' ); ?></p></div><?php
		}

		if ( isset( $_REQUEST['dismiss-release'] ) ) {
			update_site_option( 'wdp_un_dismissed_release', intval( $_REQUEST['dismiss-release'] ) );
			?>
			<div class="updated fade"><p><?php _e( 'Notice dismissed.', 'wpmudev' ); ?></p></div><?php
		}

		if ( isset( $_REQUEST['upgrade-dismiss'] ) ) {
			update_site_option( 'wdp_un_dismissed_upgrade', time() + 86400 );
			?>
			<div class="updated fade"><p><?php _e( 'Notice dismissed.', 'wpmudev' ); ?></p></div><?php
		}
	}

	/**
	 * Get the nonced admin url for installing a given project
	 * @param $project_id
	 *
	 * @return string|bool generated admin url for installing the project
	 */
	function auto_install_url( $project_id ) {
		$data           = $this->get_updates();
		$local_projects = $this->get_local_projects();
		$api_key        = $this->get_apikey();
		if ( ! isset( $local_projects[ $project_id ] ) && isset( $data['projects'][ $project_id ]['autoupdate'] ) && $data['projects'][ $project_id ]['autoupdate'] == 1 && $api_key && $this->user_can_install( $project_id ) && $this->project_compatible( $project_id ) && ( isset( $data['downloads'] ) && $data['downloads'] == 'enabled' ) ) {
			if ( $data['projects'][ $project_id ]['type'] == 'plugin' ) {
				return wp_nonce_url( self_admin_url( "update.php?action=install-plugin&plugin=wpmudev_install-$project_id" ), "install-plugin_wpmudev_install-$project_id" );
			} else if ( $data['projects'][ $project_id ]['type'] == 'theme' ) {
				return wp_nonce_url( self_admin_url( "update.php?action=install-theme&theme=wpmudev_install-$project_id" ), "install-theme_wpmudev_install-$project_id" );
			}
		}

		return false;
	}

	/**
	 * Check user permissions to see if we can install this project
	 *
	 * @param $project_id
	 *
	 * @return bool
	 */
	function user_can_install( $project_id ) {
		$data = $this->get_updates();
		if ( isset( $data['membership'] ) &&
		     $this->allowed_user() &&
		     (
			     $data['membership'] == 'full' || //full member
			     $data['membership'] == $project_id || //single member with access
			     ( is_numeric( $data['membership'] ) && isset( $data['projects'][ $project_id ]['package'] ) && $data['projects'][ $project_id ]['package'] === $data['membership'] ) || //handles packaged projects
			     ( $this->upfront == $project_id && ( $data['membership'] == 'full' || is_numeric( $data['membership'] ) ) ) || //upfront
			     $data['projects'][ $project_id ]['paid'] == 'free' || //free project
			     $data['projects'][ $project_id ]['paid'] == 'lite' //lite project
		     )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if root Upfront project is installed
	 *
	 * @return bool
	 */
	function is_upfront_installed() {
		$local_projects = $this->get_local_projects();
		return isset( $local_projects[ $this->upfront ] );
	}

	/**
	 * Check if a child Upfront project is installed
	 *
	 * @return bool
	 */
	function upfront_theme_installed() {
		$local_projects = $this->get_local_projects();
		foreach ( $local_projects as $project_id => $project ) {
			//quit on first theme installed greater than legacy threshold
			if ( $project['type'] == 'theme' && $this->is_upfront_theme( $project_id ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a given theme project id is an Upfront theme
	 *
	 * @param $project_id int
	 *
	 * @return bool
	 */
	function is_upfront_theme( $project_id ) {
		return ( $project_id > $this->legacy_themes );
	}

	/**
	 * Check if a given theme project id is a legacy theme
	 *
	 * @param $project_id int
	 *
	 * @return bool
	 */
	function is_legacy_theme( $project_id ) {
		return ( $project_id <= $this->legacy_themes );
	}

	/**
	 * Check whether this project is compatible with the current install based on requirements from API
	 *
	 * @param int $project_id
	 *
	 * @return bool
	 */
	function project_compatible( $project_id ) {
		$data = $this->get_updates();
		if ( ! isset( $data['projects'][ $project_id ]['requires'] ) ) {
			return false;
		}

		//skip multisite only products if not compatible
		if ( $data['projects'][ $project_id ]['requires'] == 'ms' && ! is_multisite() )
			return false;

		//skip buddypress only products if not active
		if ( $data['projects'][ $project_id ]['requires'] == 'bp' && ! defined( 'BP_VERSION' ) )
			return false;

		return true;
	}

	/**
	 * Registers our various dashboard widgets
	 */
	function register_dashboard_widget() {
		//only admins see this
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		$screen = get_current_screen();
		add_meta_box( 'wpmudev_widget', __( 'WPMU DEV Dashboard', 'wpmudev' ), array(
				&$this,
				'dashboard_widget'
			), $screen->id, 'normal', 'core' );
		add_meta_box( 'wpmudev_news_widget', __( 'WPMU DEV News', 'wpmudev' ), array(
				&$this,
				'wpmudev_news_widget'
			), $screen->id, 'side', 'core' );

		/* reorder widgets
                  // Globalize the metaboxes array, this holds all the widgets for wp-admin
                  global $wp_meta_boxes;
                  var_dump($wp_meta_boxes);
                  // Get the regular dashboard widgets array
                  // (which has our new widget already but at the end)
                  $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

                  // Backup and delete our new dashbaord widget from the end of the array
                  $widget_backup = array('wpmudev_widget' => $normal_dashboard['wpmudev_widget']);
                  unset($normal_dashboard['wpmudev_widget']);

                  // Merge the two arrays together so our widget is at the beginning
                  $sorted_dashboard = array_merge($widget_backup, $normal_dashboard);

                  // Save the sorted array back into the original metaboxes
                  $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
                 */
	}

	/**
	 * Output for the main dashboard widget
	 */
	function dashboard_widget() {
		$data           = $this->get_updates();
		$local_projects = $this->get_local_projects();
		$updates        = get_site_option( 'wdp_un_updates_available' );
		$count          = ( is_array( $updates ) ) ? count( $updates ) : 0;
		if ( $count > 0 ) {
			$count_output = ' <span class="updates-menu"><span class="update-plugins"><span class="updates-count count-' . $count . '">' . $count . '</span></span></span>';
		}
		?>
		<div id="wpdv-dash-links">
			<h4><?php _e( 'Quick Links:', 'wpmudev' ); ?></h4>
			<a class="quick-link" id="ql-browse" href="<?php echo $this->dashboard_url; ?>"
			   title="<?php _e( 'View your WPMU DEV Dashboard', 'wpmudev' ); ?>"><?php _e( 'Dashboard', 'wpmudev' ); ?></a>
			<a class="quick-link" id="ql-browse" href="<?php echo $this->plugins_url; ?>"
			   title="<?php _e( 'Browse and install WPMU DEV Plugins', 'wpmudev' ); ?>"><?php _e( 'Plugins', 'wpmudev' ); ?></a>
			<a class="quick-link" id="ql-browse" href="<?php echo $this->themes_url; ?>"
			   title="<?php _e( 'Browse and install WPMU DEV Themes', 'wpmudev' ); ?>"><?php _e( 'Themes', 'wpmudev' ); ?></a>
			<?php if ( $count ) { ?>
				<a class="quick-link" id="ql-updates" href="<?php echo $this->updates_url; ?>"
				   title="<?php _e( 'Update your WPMU DEV Plugins and Themes', 'wpmudev' ); ?>"><?php _e( 'Updates', 'wpmudev' );
					echo $count_output;
					?></a>
			<?php } ?>
			<a class="quick-link" id="ql-support" href="<?php echo $this->community_url; ?>"
			   title="<?php _e( 'Participate in the WPMU DEV community', 'wpmudev' ); ?>"><?php _e( 'Community', 'wpmudev' ); ?></a>
			<a class="quick-link" id="ql-support" href="<?php echo $this->support_url; ?>"
			   title="<?php _e( 'Get support for WPMU DEV Plugins and Themes', 'wpmudev' ); ?>"><?php _e( 'Support', 'wpmudev' ); ?></a>

			<div class="clear"></div>
		</div>
		<?php
		//handle ad messages
		if ( $this->get_apikey() ) { //no api key yet
			if ( isset( $data['membership'] ) && $data['membership'] == 'full' ) { //full member
				$msg = $data['full_notice']['msg'];
				$id  = $data['full_notice']['id'];
				if ( isset( $data['full_notice']['url'] ) ) {
					$button = '<a id="wdv-upgrade" class="wpmu-button" target="_blank" href="' . esc_url( $data['full_notice']['url'] ) . '"><i class="wdvicon-share-alt wdvicon-large"></i> ' . __( 'Go Now', 'wpmudev' ) . '</a>';
					$class  = 'with-button';
				} else {
					$class  = '';
					$button = '';
				}
			} else if ( isset( $data['membership'] ) && is_numeric( $data['membership'] ) ) { //single member
				$msg    = $data['single_notice']['msg'];
				$id     = $data['single_notice']['id'];
				$class  = 'with-button';
				$button = '<a id="wdv-upgrade" class="wpmu-button" target="_blank" href="' . apply_filters( 'wpmudev_upgrade_url', 'https://premium.wpmudev.org/membership/' ) . '"><i class="wdvicon-arrow-up wdvicon-large"></i> ' . __( 'Upgrade Now', 'wpmudev' ) . '</a>';
			} else if ( isset( $data['free_notice'] ) ) { //free member
				$msg    = $data['free_notice']['msg'];
				$id     = $data['free_notice']['id'];
				$class  = 'with-button';
				$button = '<a id="wdv-upgrade" class="wpmu-button" target="_blank" href="' . apply_filters( 'wpmudev_join_url', 'https://premium.wpmudev.org/join/' ) . '"><i class="wdvicon-arrow-up wdvicon-large"></i> ' . __( 'Upgrade Now', 'wpmudev' ) . '</a>';
			}
		}

		if ( isset( $msg ) ) {
			?>
			<div id="wpdv-dash-msg">
				<blockquote
					class="<?php echo $class; ?>"><?php echo $button; ?><?php echo strip_tags( stripslashes( $msg ), '<a><strong>' ); ?></blockquote>
			</div>
		<?php
		}

		//if latest release is set, has data, and not installed show notice
		if ( current_user_can( 'install_plugins' ) && isset( $data['latest_release'] ) && isset( $data['projects'][ $data['latest_release'] ] ) && ! isset( $local_projects[ $data['latest_release'] ] ) ) {
			$project  = $data['projects'][ $data['latest_release'] ];
			$info_url = ( $project['type'] == 'theme' ) ? $this->themes_url . '#pid=' . $data['latest_release'] : $this->plugins_url . '#pid=' . $data['latest_release'];
			?>
			<div id="wpdv-dash-release">
				<h4><?php _e( 'Latest WPMU DEV Release:', 'wpmudev' ); ?></h4>

				<div>
					<a id="wdv-release-img" title="<?php _e( 'More Information &raquo;', 'wpmudev' ); ?>"
					   href="<?php echo $info_url; ?>">
						<img src="<?php echo $project['thumbnail']; ?>" width="186" height="105"/>
					</a>
					<h4><?php echo esc_html( $project['name'] ); ?></h4>

					<p><?php echo esc_html( $project['short_description'] ); ?></p>

					<div id="wdv-release-buttons">
						<?php if ( ! $this->get_apikey() ) { //no api key yet
							?><a id="wdv-release-install" href="<?php echo $this->dashboard_url; ?>"
							     class="wpmu-button button-disabled"
							     title="<?php _e( 'Setup your WPMU DEV account to install', 'wpmudev' ); ?>"><i
								class="wdvicon-download-alt wdvicon-large"></i> <?php _e( 'INSTALL', 'wpmudev' ); ?></a><?php
						} else if ( $url = $this->auto_install_url( $data['latest_release'] ) ) {
							?><a id="wdv-release-install" href="<?php echo $url; ?>" class="wpmu-button"><i
								class="wdvicon-download-alt wdvicon-large"></i> <?php _e( 'INSTALL', 'wpmudev' ); ?></a><?php
						} else if ( $this->user_can_install( $data['latest_release'] ) ) { //has permission, but it's not autoinstallable
							?><a id="wdv-release-install" href="<?php echo esc_url( $project['url'] ); ?>" target="_blank"
							     class="wpmu-button"><i class="wdvicon-download wdvicon-large"></i> <?php _e( 'DOWNLOAD', 'wpmudev' ); ?>
							</a><?php
						} else { //needs to upgrade
							?><a id="wdv-release-install"
							     href="<?php echo apply_filters( 'wpmudev_project_upgrade_url', esc_url( 'https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode( $project['url'] ) . '#signup' ), $data['latest_release'] ); ?>"
							     target="_blank" class="wpmu-button"><i
								class="wdvicon-arrow-up wdvicon-large"></i> <?php _e( 'Upgrade to Install', 'wpmudev' ); ?></a><?php
						}
						?>
						<a id="wdv-release-info"
						   href="<?php echo $info_url; ?>"><?php _e( 'More Information &raquo;', 'wpmudev' ); ?></a>
					</div>
				</div>
			</div>

		<?php
		}
		echo '<div class="clear"></div>';
	}

	/**
	 * Outputs RSS feed from our blog on dashboard widget
	 */
	function wpmudev_news_widget() {
		$rss = @fetch_feed( 'http://premium.wpmudev.org/blog/category/wpmudev/feed/' );

		if ( is_wp_error( $rss ) ) {
			if ( is_admin() || current_user_can( 'manage_options' ) ) {
				echo '<div class="rss-widget"><p>';
				printf( __( '<strong>RSS Error</strong>: %s' ), $rss->get_error_message() );
				echo '</p></div>';
			}
		} elseif ( ! $rss->get_item_quantity() ) {
			$rss->__destruct();
			unset( $rss );

			return false;
		} else {
			echo '<div class="rss-widget">';
			wp_widget_rss_output( $rss, array( 'items' => 5 ) );
			echo '</div>';
			$rss->__destruct();
			unset( $rss );
		}
	}

	//------------------------------------------------------------------------//
	//---Page Output Functions------------------------------------------------//
	//------------------------------------------------------------------------//

	/**
	 * Outputs the Main Dashboard admin page
	 */
	function dashboard_output() {
		global $wpdb, $current_site, $current_user;

		if ( ! current_user_can( 'manage_options' ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		if ( isset( $_GET['clear_key'] ) ) {
			update_site_option( 'wpmudev_apikey', '' );
			update_site_option( 'wdp_un_limit_to_user', 0 );
			$this->refresh_updates();
		}

		if ( isset( $_REQUEST['set_apikey'] ) ) {
			update_site_option( 'wpmudev_apikey', trim( $_REQUEST['set_apikey'] ) );
			$result = $this->refresh_updates();
			if ( empty( $result['membership'] ) ) {
				update_site_option( 'wpmudev_apikey', '' );
				$key_valid = false;
				if ( $result === false ) {
					$connection_error = true;
				}
			} else {
				$key_valid = true;
				update_site_option( 'wdp_un_limit_to_user', $current_user->ID ); //limit by default to admin user who enters api key
				$this->refresh_profile();
			}
		}

		$data    = $this->get_updates();
		$profile = $this->get_profile();

		echo '<div id="container" class="wrap">';

		if ( ! $this->get_apikey() ) {

			require_once( dirname( __FILE__ ) . '/includes/templates/dashboard-signup.php' );
		} else if ( ! $this->allowed_user() ) {

			echo '<section id="profile" class="grid_container" style="height: 0px;"></section>';
		} else if ( $data['membership'] == 'full' ) { //api key isset and full
			if ( isset( $_REQUEST['set_apikey'] ) && isset( $data['downloads'] ) && $data['downloads'] != 'enabled' ) {
				?>
				<div class="registered_error">
				<p><?php _e( 'This site is not enabled for automatic updates, one-click installations, and direct support through the WPMU DEV Dashboard plugin. You may <a href="http://premium.wpmudev.org/wp-admin/profile.php?page=wdpun">change which sites are enabled or upgrade to a higher membership level here &raquo;</a>', 'wpmudev' ); ?></p>
				</div><?php
			}
			require_once( dirname( __FILE__ ) . '/includes/templates/dashboard-full.php' );
		} else { // Free or single
			// Set up template data
			$free_projects    = array( 'plugins' => array(), 'themes' => array() );
			$premium_projects = array( 'plugins' => array(), 'themes' => array() );
			foreach ( $data['projects'] as $project ) {
				if ( 'free' == $project['paid'] || 'lite' == $project['paid'] ) {
					if ( 'theme' == $project['type'] && count( $free_projects['themes'] ) < 3 ) {
						$free_projects['themes'][] = $project;
					} else if ( 'plugin' == $project['type'] && count( $free_projects['plugins'] ) < 3 ) {
						$free_projects['plugins'][] = $project;
					}
				} else {
					if ( 'theme' == $project['type'] && count( $premium_projects['themes'] ) < 10 ) {
						$premium_projects['themes'][] = $project;
					} else if ( 'plugin' == $project['type'] && count( $premium_projects['plugins'] ) < 10 ) {
						$premium_projects['plugins'][] = $project;
					}
				}
				if (
					3 == count( $free_projects['plugins'] ) && 3 == count( $free_projects['themes'] ) &&
					10 == count( $premium_projects['plugins'] ) && 10 == count( $premium_projects['themes'] )
				) {
					break;
				}
			}
			if ( is_numeric( $data['membership'] ) ) {
				//api key isset and single
				if ( isset( $_REQUEST['set_apikey'] ) && isset( $data['downloads'] ) && $data['downloads'] != 'enabled' ) {
					?>
					<div class="registered_error">
					<p><?php _e( 'This site is not enabled for automatic updates, one-click installations, and direct support through the WPMU DEV Dashboard plugin. You may <a href="http://premium.wpmudev.org/wp-admin/profile.php?page=wdpun">change which sites are enabled or upgrade to a higher membership level here &raquo;</a>', 'wpmudev' ); ?></p>
					</div><?php
				}
				// Get paid project info
				$paid_project_id = $data['membership'];
				$paid_project    = array();
				$local_projects  = $this->get_local_projects();
				foreach ( $data['projects'] as $project ) {
					if ( $paid_project_id != $project['id'] ) {
						continue;
					}
					$paid_project = $project;
					break;
				}
				require_once( dirname( __FILE__ ) . '/includes/templates/dashboard-single.php' );
			} else {
				//api key isset and free
				require_once( dirname( __FILE__ ) . '/includes/templates/dashboard-free.php' );
			}
		}

		echo '</div><!-- end of #container -->';
	}

	/**
	 * Outputs the Community admin page
	 */
	function community_output() {
		global $wpdb, $current_site;

		if ( ! current_user_can( 'manage_options' ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		$data    = $this->get_updates();
		$profile = $this->get_profile();

		$disabled = false;
		if ( ! $this->get_apikey() || ! $this->allowed_user() || ! isset( $data['membership'] ) || ! $data['membership'] || $data['membership'] == 'free' ) {
			$disabled = true;
		}

		require_once( dirname( __FILE__ ) . '/includes/templates/community.php' );
	}

	/**
	 * Outputs the Plugins admin page
	 */
	function plugins_output() {
		global $wpdb, $current_site;

		if ( ! current_user_can( 'install_plugins' ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		$page_type      = 'plugin';
		$page_title     = __( 'Plugins', 'wpmudev' );
		$data           = $this->get_updates();
		$local_projects = $this->get_local_projects();
		$tags           = $this->tags_data( 'plugin' );

		/*
          // If not a full member, rearrange so the free items come first
          if (isset($data['projects']) && !(isset($data['membership']) && $data['membership'] == 'full')) {
          $free = $other = array();
          $free_status = array('lite', 'free');
          foreach ($data['projects'] as $project) {
          if (in_array($project['paid'], $free_status)) $free[] = $project;
          else $other[] = $project;
          }
          $data['projects'] = array_merge($free, $other);
          }
         */

		//sort listings by popularity by default
		function sortByPopularity( $a, $b ) {
			return $b["popularity"] - $a["popularity"];
		}

		uasort( $data['projects'], 'sortByPopularity' );

		//rearrange incompatible products to the bottom of the list
		if ( isset( $data['projects'] ) ) {
			$incompatible = $other = array();
			foreach ( $data['projects'] as $project ) {
				if ( ( $project['requires'] == 'ms' && ! is_multisite() ) || ( $project['requires'] == 'bp' && ! defined( 'BP_VERSION' ) ) ) {
					$incompatible[] = $project;
				} else {
					$other[] = $project;
				}
			}
			$data['projects'] = array_merge( $other, $incompatible );
		}

		require_once( dirname( __FILE__ ) . '/includes/templates/listings.php' );
	}

	/**
	 * Outputs the Themes admin page
	 */
	function themes_output() {
		global $wpdb, $current_site;

		if ( ! current_user_can( 'install_themes' ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		$page_type      = 'theme';
		$page_title     = __( 'Themes', 'wpmudev' );
		$data           = $this->get_updates();
		$local_projects = $this->get_local_projects();
		$tags           = $this->tags_data( 'theme' );
		$current_theme  = get_stylesheet();

		//manually remove Upfront from install listings (installed silently)
		unset( $data['projects'][ $this->upfront ] );

		/*
          // If not a full member, rearrange so the free items come first
          if (isset($data['projects']) && !(isset($data['membership']) && $data['membership'] == 'full')) {
          $free = $other = array();
          $free_status = array('lite', 'free');
          foreach ($data['projects'] as $project) {
          if (in_array($project['paid'], $free_status)) $free[] = $project;
          else $other[] = $project;
          }
          $data['projects'] = array_merge($free, $other);
          }
         */

		//sort listings by popularity by default
		function sortByPopularity( $a, $b ) {
			return $b["popularity"] - $a["popularity"];
		}

		uasort( $data['projects'], 'sortByPopularity' );

		//rearrange incompatible products to the bottom of the list
		if ( isset( $data['projects'] ) ) {
			$incompatible = $other = array();
			foreach ( $data['projects'] as $project ) {
				if ( ( $project['requires'] == 'ms' && ! is_multisite() ) || ( $project['requires'] == 'bp' && ! defined( 'BP_VERSION' ) ) ) {
					$incompatible[] = $project;
				} else {
					$other[] = $project;
				}
			}
			$data['projects'] = array_merge( $other, $incompatible );
		}

		//move legacy projects to the bottom of the list
		if ( isset( $data['projects'] ) ) {
			$legacy = $current = array();
			foreach ( $data['projects'] as $project ) {
				if ( $this->is_legacy_theme( $project['id'] ) ) {
					$legacy[] = $project;
				} else {
					$current[] = $project;
				}
			}
			$data['projects'] = array_merge( $current, $legacy );
		}

		require_once( dirname( __FILE__ ) . '/includes/templates/listings.php' );
	}

	/**
	 * Outputs the Updates admin page
	 */
	function updates_output() {
		global $wpdb, $current_site;

		if ( ! current_user_can( 'update_plugins' ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		require_once( dirname( __FILE__ ) . '/includes/templates/updates.php' );
	}

	/**
	 * Outputs the Support admin page
	 */
	function support_output() {
		global $wpdb, $current_site, $current_user;

		if ( ! current_user_can( ( is_multisite() ? 'manage_network_options' : 'manage_options' ) ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		$profile = $this->get_profile();
		$data    = $this->get_updates();
		$spinner = plugins_url( 'includes/images/spinner-dark.gif', __FILE__ );

		$disabled = '';
		if ( ! $this->get_apikey() || ! $this->allowed_user() || ! isset( $data['membership'] ) || ! $data['membership'] || $data['membership'] == 'free' || ! isset( $data['downloads'] ) || $data['downloads'] != 'enabled' ) {
			$disabled = ' disabled="disabled"';
		}

		$hide_tips = (bool) get_user_meta( $current_user->ID, '_wpmudev_hide_post_tips', true );

		include_once( dirname( __FILE__ ) . '/includes/debug.php' );

		require_once( dirname( __FILE__ ) . '/includes/templates/support.php' );
	}

	/**
	 * Outputs the Manage/Settings admin page
	 */
	function settings_output() {
		global $wpdb, $current_site, $current_user;

		if ( ! current_user_can( ( is_multisite() ? 'manage_network_options' : 'manage_options' ) ) ) {
			echo "<p>Nice Try...</p>"; //If accessed properly, this message doesn't appear.
			return;
		}

		require_once( dirname( __FILE__ ) . '/includes/templates/settings.php' );
	}

	/**
	 * Request project description from UN service
	 * and send out the response as HTML fragment.
	 */
	function ajax_get_project_details() {
		$wdp_id = (int) $_POST['wdp_id'];

		//check for cached value
		if ( false === ( $return = get_transient( "wdpun_project_details_$wdp_id" ) ) ) {
			$api_key = $this->get_apikey();
			$url     = $this->server_url . '?action=get_project_info&key=' . urlencode( $api_key ) . '&id=' . $wdp_id;

			$options = array(
				'timeout'    => 15,
				'sslverify'  => false,
				'user-agent' => 'UN Client/' . $this->version
			);

			$response = wp_remote_get( $url, $options );
			if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
				$data = $response['body'];
				if ( $data == 'error' ) {
					// Handle error
					$return = __( 'There has been an error contacting remote server', 'wpmudev' );
				} else {
					$return = $data;
					set_transient( "wdpun_project_details_$wdp_id", $return, 60 * 60 * 12 ); //cache for 12 hours locally
				}
			} else {
				$return = __( 'There has been an error contacting remote server', 'wpmudev' );
			}
		}

		die( $return );
	}

	/**
	 * Send support post creation
	 */
	function ajax_support_post() {
		global $current_user;

		if ( ! current_user_can( 'manage_options' ) ) {
			die( json_encode( array(
				'response' => 0,
				'data'     => __( 'You do not have permissions for this.', 'wpmudev' )
			) ) );
		}

		//build GET url
		$url  = 'http://premium.wpmudev.org/forums/wdpun-api.php?';
		$args = array();
		foreach ( $_POST as $key => $value ) {
			$args[] = $key . '=' . urlencode( stripslashes( $value ) );
		}
		$args[] = 'api_key=' . $this->get_apikey();
		$args[] = 'domain=' . network_site_url();
		$url .= implode( '&', $args );

		$options = array(
			'timeout'    => 15,
			'user-agent' => 'UN Client/' . $this->version
		);

		$response = wp_remote_get( $url, $options );
		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			update_user_meta( $current_user->ID, '_wpmudev_hide_post_tips', 1 ); //so that the tips box will be closed by default now
			die( $response['body'] );
		} else {
			die( json_encode( array(
					'response' => 0,
					'data'     => __( 'There has been a temporary error contacting the remote server.', 'wpmudev' )
				) ) );
		}
	}

	/**
	 * Handle install message hiding per-user.
	 */
	function ajax_hide_install_message() {
		global $current_user;
		update_user_meta( $current_user->ID, '_wpmudev_install_message', 1 );
		die;
	}

	/**
	 * Listener for WPMU DEV API server asking site to refresh data (like after checkout). Next admin page load will force a refresh
	 */
	function ajax_refresh_updates() {
		update_site_option( 'wdp_un_refresh_updates_flag', 1 );
		die;
	}

	/**
	 * Listener for WPMU DEV staff remote access login
	 */
	function authenticate_remote_access() {

		if ( defined( 'WPMUDEV_DISABLE_REMOTE_ACCESS' ) ) {
			wp_die( 'Remote access disabled in wp-config!' );
		}

		//check that we are coming from WPMU DEV (sanity check, not real security)
		//if ( !isset($_SERVER['HTTP_REFERER']) || 'premium.wpmudev.org' != parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) )
		//	wp_die('Invalid referrer!');

		$access = get_site_option( 'wdp_un_remote_access' );
		//if access is set, valid key provided, and not expired
		if ( $access && isset( $_POST['wdpunkey'] ) && $_POST['wdpunkey'] == $access['key'] ) {
			if ( $access['expire'] > time() ) {
				wp_clear_auth_cookie();
				add_filter( 'auth_cookie_expiration', create_function( '$a', 'return 3600;' ) ); //force 1 hour cookie timeout
				wp_set_auth_cookie( $access['userid'], false );
				wp_set_current_user( $access['userid'] );

				//record login info
				$access['logins'][ time() ] = $_POST['staff'];
				update_site_option( 'wdp_un_remote_access', $access );

				//send to dashboard
				wp_redirect( network_admin_url() );
				exit;
			} else {
				wp_die( 'Sorry, this access token has expired. Please ask the user to renew it.' );
			}
		}
		wp_die( 'Sorry, this is an invalid access token. You must ask the user to grant access.' );
	}

	/**
	 * Enable WPMU DEV staff remote access login
	 */
	function enable_remote_access() {
		global $current_user;

		if ( ! current_user_can( 'edit_users' ) ) {
			return false;
		}

		if ( defined( 'WPMUDEV_DISABLE_REMOTE_ACCESS' ) ) {
			return false;
		}

		//build API call
		$key        = wp_generate_password( 64, true );
		$expiration = strtotime( "+3 Days" );
		$api_key    = $this->get_apikey();

		//make API call
		$domain  = str_replace( 'http://', 'https://', $this->server_url ); //force ssl
		$url     = $domain . '?action=grant-access&un-version=' . $this->version . '&authkey=' . urlencode( $key ) . '&authexpire=' . $expiration . '&authurl=' . urlencode( admin_url( 'admin-ajax.php?action=wdpunauth' ) ) . '&domain=' . urlencode( network_site_url() ) . '&key=' . urlencode( $api_key );
		$options = array(
			'timeout'    => 15,
			'sslverify'  => false, /* alot of hosting doesn't have updated CA bundle */
			'user-agent' => 'UN Client/' . $this->version
		);

		$response = wp_remote_get( $url, $options );
		if ( wp_remote_retrieve_response_code( $response ) != 200 || $response['body'] != 'registered' ) {
			return false;
		}

		//save to option
		$access           = array();
		$access['userid'] = $current_user->ID;
		$access['key']    = $key;
		$access['expire'] = $expiration;
		update_site_option( 'wdp_un_remote_access', $access );

		return true;
	}

	/**
	 * Removes access ability for support staff
	 */
	function revoke_remote_access() {
		if ( ! current_user_can( 'edit_users' ) ) {
			return false;
		}

		delete_site_option( 'wdp_un_remote_access' ); //do this whether or not we can update the api
		//build API call
		$api_key = $this->get_apikey();

		//make API call
		$url     = $this->server_url . '?action=revoke-access&un-version=' . $this->version . '&domain=' . urlencode( network_site_url() ) . '&key=' . urlencode( $api_key );
		$options = array(
			'timeout'    => 15,
			'sslverify'  => false, /* alot of hosting doesn't have updated CA bundle */
			'user-agent' => 'UN Client/' . $this->version
		);

		$response = wp_remote_get( $url, $options );

		//if (wp_remote_retrieve_response_code($response) != 200 || $response['body'] != 'revoked') return false;

		return true;
	}

}

global $wpmudev_un;
$wpmudev_un = new WPMUDEV_Dashboard();

//this is for compatibiilty with older versions of the notice
if ( ! class_exists( 'WPMUDEV_Update_Notifications' ) ) {

	class WPMUDEV_Update_Notifications {

	}

}