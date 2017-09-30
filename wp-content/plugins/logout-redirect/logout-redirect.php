<?php
/*
Plugin Name: Logout Redirect
Plugin URI: http://premium.wpmudev.org/project/logout-redirect
Description: Redirects users to specified url after logging out - say goodbye to users logging out... and seeing the logout screen :)
Author: WPMUDEV
Version: 1.1.4
Text Domain: logout_redirect
Author URI: http://premium.wpmudev.org/
WDP ID: 42
*/

/*
Copyright 2007-2009 Incsub (http://incsub.com)

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

/**
 * Plugin main class
 **/
class Logout_Redirect {

	/**
	 * PHP 5 constructor
	 **/
	function __construct() {
		add_action('login_init', array($this, 'clean_redirect'));
		add_filter( 'wp_logout', array( &$this, 'redirect' ) );
		add_action( 'wpmu_options', array( &$this, 'network_option' ) );
		add_action( 'update_wpmu_options', array( &$this, 'update_network_option' ) );
		add_action( 'admin_init', array( &$this, 'add_settings_field' ) );

		// load text domain
		if ( defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/logout-redirect.php' ) ) {
			load_muplugin_textdomain( 'logout_redirect', 'logout-redirect-files/languages' );
		} else {
			load_plugin_textdomain( 'logout_redirect', false, dirname( plugin_basename( __FILE__ ) ) . '/logout-redirect-files/languages' );
		}
	}

	function clean_redirect () {
		if (defined('LOGOUT_REDIRECT_DEFAULT_WP_BEHAVIOR') && LOGOUT_REDIRECT_DEFAULT_WP_BEHAVIOR) return false;
		$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : false;
		if ('logout' != $action) return false;
		if (is_user_logged_in()) return true; // User is still logged in, let WP do its job.

		// We're still here, so we have a case of user already logged out, requesting logout.
		// Suppress standard error and just redirect.
		$this->redirect();
	}

	/**
	 * Redirect user on logout
	 **/
	function redirect() {
		$redirect_url = !empty($_REQUEST['redirect_to']) && !(defined('LOGOUT_REDIRECT_FORCED') && LOGOUT_REDIRECT_FORCED)
			? $_REQUEST['redirect_to']
			: $this->get_redirection_url()
		;
		wp_redirect($redirect_url);
		exit();
	}

	private function _get_raw_redirection_url () {
		return trim($this->is_plugin_active_for_network(plugin_basename(__FILE__))
			? get_site_option('logout_redirect_url')
			: get_option('logout_redirect_url')
		);
	}

	private function _get_macros () {
		return apply_filters('logout_redirect-defined_macros', array(
			'BP_ACTIVITY_SLUG',
			'BP_GROUPS_SLUG',
			'BP_MEMBERS_SLUG',
		));
	}

	private function _expand_macro ($macro) {
		$value = false;
		$user = wp_get_current_user();
		switch ($macro) {
			case 'BP_ACTIVITY_SLUG':
				if (function_exists('bp_get_activity_root_slug')) $value = bp_get_activity_root_slug();
				break;
			case 'BP_GROUPS_SLUG':
				if (function_exists('bp_get_groups_slug')) $value = bp_get_groups_slug();
				break;
			case 'BP_MEMBERS_SLUG':
				if (function_exists('bp_get_members_slug')) $value = bp_get_members_slug();
				break;
		}
		return apply_filters('logout_redirect-macro_value', $value, $macro);
	}

	function get_redirection_url () {
		$raw = $this->_get_raw_redirection_url();
		foreach ($this->_get_macros() as $macro) {
			$value = $this->_expand_macro($macro);
			if (!$value) continue;
			$raw = preg_replace('/' . preg_quote($macro, '/') . '/', $value, $raw);
		}
		if (!preg_match('/^https?:\/\//', $raw)) {
			$protocol = @$_SERVER["HTTPS"] == 'on' ? 'https' : 'http';
			$raw = site_url($raw, apply_filters('logout_redirect-url_protocol', $protocol));
		}
		return apply_filters('logout_redirect-redirection_url', $raw);
	}

	/**
	 * Network option
	 **/
	function network_option() {
		if( ! $this->is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			return;
		$url = $this->_get_raw_redirection_url();
		?>
		<h3><?php _e( 'Logout Redirect', 'logout_redirect' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="logout_redirect_url"><?php _e( 'Redirect to', 'logout_redirect' ) ?></label></th>
				<td>
					<input name="logout_redirect_url" type="text" id="logout_redirect_url" value="<?php echo esc_attr($url) ?>" size="40" />
					<br />
					<?php _e( 'The URL users will be redirected to after logout.', 'logout_redirect' ) ?>
					<?php
					if (defined('BP_VERSION')) {
						printf(__('You can use these macros for your redirection: %s', 'logout_redirect'), '<code>' . join('</code>, <code>', $this->_get_macros()) . '</code>');
					}
					?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save option in the option
	 **/
	function update_network_option() {
		update_site_option( 'logout_redirect_url', stripslashes( $_POST['logout_redirect_url'] ) );
	}

	/**
	 * Add setting field for singlesite
	 **/
	function add_settings_field() {
		if( $this->is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			return;

		add_settings_section( 'logout_redirect_setting_section', __( 'Logout Redirect', 'logout_redirect' ), '__return_false', 'general' );

		add_settings_field( 'logout_redirect_url', __( 'Redirect to', 'logout_redirect' ), array( &$this, 'site_option' ), 'general', 'logout_redirect_setting_section' );

		register_setting( 'general', 'logout_redirect_url' );
	}

	/**
	 * Setting field for singlesite
	 **/
	function site_option() {
		$url = $this->_get_raw_redirection_url();
		echo '<input name="logout_redirect_url" type="text" id="logout_redirect_url" value="' . esc_attr($url) . '" size="40" />';
		if (defined('BP_VERSION')) {
			printf(__('You can use these macros for your redirection: %s', 'logout_redirect'), '<code>' . join('</code>, <code>', $this->_get_macros()) . '</code>');
		}
	}

	/**
	 * Verify if plugin is network activated
	 **/
	function is_plugin_active_for_network( $plugin ) {
		if ( !is_multisite() )
			return false;

		$plugins = get_site_option( 'active_sitewide_plugins');
		if ( isset($plugins[$plugin]) )
			return true;

		return false;
	}

}

$logout_redirect = new Logout_Redirect();

/**
 * Show notification if WPMUDEV Update Notifications plugin is not installed
 *
 **/
if ( !function_exists( 'wdp_un_check' ) ) {
	add_action( 'admin_notices', 'wdp_un_check', 5 );
	add_action( 'network_admin_notices', 'wdp_un_check', 5 );

	function wdp_un_check() {
		if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
			echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
	}
}