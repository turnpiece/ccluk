<?php

class WP_Hummingbird_Module_Smush extends WP_Hummingbird_Module {

	/**
	 * Variable used to distinguish between versions of Smush.
	 * Sets true if the Pro version is installed. False in all other cases.
	 *
	 * @var bool
	 */
	static public $is_smush_pro = false;

	public function init() {}
	public function run() {}

	public static function is_smush_installed() {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugins = get_plugins();
		if ( array_key_exists( 'wp-smush-pro/wp-smush.php', $plugins ) ) {
			self::$is_smush_pro = true;
		}
		return array_key_exists( 'wp-smush-pro/wp-smush.php', $plugins ) || array_key_exists( 'wp-smushit/wp-smush.php', $plugins );
	}

	public static function is_smush_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( 'wp-smush-pro/wp-smush.php' ) || is_plugin_active( 'wp-smushit/wp-smush.php' );
	}

	public static function get_smush_install_url() {
		$url = '';
		if ( wphb_is_member() ) {
			// Return the pro plugin URL
			$url = WPMUDEV_Dashboard::$ui->page_urls->plugins_url;
			$url = $url . '#pid=912164';
		}
		else {
			// Return the free URL
			$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-smushit' ), 'install-plugin_wp-smushit' );
		}

		return $url;
	}

	public static function get_smush_activate_url() {

	}
}