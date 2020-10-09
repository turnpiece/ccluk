<?php
/**
 * The admin view class of the plugin.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Views
 */

namespace Beehive\Core\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers;
use Beehive\Core\Controllers\Assets;
use Beehive\Core\Utils\Abstracts\View;

/**
 * Class Admin
 *
 * @package Beehive\Core\Views
 */
class Admin extends View {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Localization.
		add_filter( 'beehive_assets_scripts_common_localize_vars', array( $this, 'common_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-settings', array( $this, 'settings_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-dashboard', array( $this, 'dashboard_vars' ) );
	}

	/**
	 * Render settings page content for the site.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function dashboard_page() {
		echo '<div id="beehive-dashboard-app"></div>';

		Assets::instance()->enqueue_style( 'beehive-dashboard' );
		Assets::instance()->enqueue_script( 'beehive-dashboard' );
	}

	/**
	 * Render accounts page content.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function accounts_page() {
		echo '<div id="beehive-accounts-app"></div>';

		Assets::instance()->enqueue_style( 'beehive-accounts' );
		Assets::instance()->enqueue_script( 'beehive-accounts' );
	}

	/**
	 * Render settings page content for the site.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function settings_page() {
		echo '<div id="beehive-settings-app"></div>';

		Assets::instance()->enqueue_style( 'beehive-settings' );
		Assets::instance()->enqueue_script( 'beehive-settings' );
	}

	/**
	 * Get Beehive menu icon data.
	 *
	 * Get svg image instead of an image url.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_settings_icon() {
		ob_start();
		?>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 292 337.2" fill="#fff">
			<path d="M125.4 153.4l51.7 47.9 5.9-6.3-38-73.3zM81 112.3l25.5 23.6L147 70.2l54.6 105.4 88.6-92.4L146 0 0 84.3v119.8zm211 58.1v-53.6l-78.1 82.4 10.7 20.9zm-76.2 87.8l-20.5-39.4-17.1 18.1-66.4-61.5L47.1 280l98.9 57.2 146-84.3v-51.4zM93 158l-10.2-9.3L0 242.2v10.7l25.5 14.7z"/>
		</svg>
		<?php
		$svg = ob_get_clean();

		// phpcs:ignore
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Get Beehive menu icon data.
	 *
	 * Get svg image instead of an image url.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_statistics_icon() {
		ob_start();
		?>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
			<path
				d="M0 14.1v-.5c0-.3.1-.6.4-.8l4.3-3.7c.9-.7 2-.9 3.1-.5l.8.4c1.2.5 2.5.2 3.3-.8l3.2-3.5c.2-.2.5-.2.7 0a.76.76 0 0 1 .2.4V13c0 1.1-.9 2-2 2H.9c-.5 0-.9-.4-.9-.9zm5-7L1.6 9.8c-.4.3-1.1.3-1.4-.2s-.3-1.1.2-1.4l4.1-3.3c.9-.7 2.2-.9 3.2-.3l1.2.6c.4.2.9.1 1.2-.2L14.2.4c.3-.4.9-.6 1.4-.2.4.3.6.9.2 1.4l-4.3 4.9c-.9 1-2.3 1.3-3.5.7l-.8-.4c-.7-.3-1.6-.2-2.2.3z"
				fill="#fff"/>
		</svg>
		<?php
		$svg = ob_get_clean();

		// phpcs:ignore
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Get the settings data for the script.
	 *
	 * We need to remove sensitive data and check for the
	 * permissions first.
	 *
	 * @param array $vars Vars.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function settings_vars( $vars ) {
		// We need to hide sensitive data from non-admin users.
		if ( Helpers\Permission::can_manage_settings() ) {
			$vars['ps_levels'] = $this->is_network() ? Helpers\Permission::get_ps_levels() : array();
			$vars['roles']     = Helpers\Permission::get_roles( false );
			// Get excluded users.
			$excluded = beehive_analytics()->settings->get( 'settings_exclude_users', 'permissions', $this->is_network(), array() );
			// Get included users.
			$included = beehive_analytics()->settings->get( 'settings_include_users', 'permissions', $this->is_network(), array() );

			// Users list.
			$vars['users']        = $this->get_users_data( array_merge( $excluded, $included ) );
			$vars['current_user'] = get_current_user_id();
		}

		// Report items.
		$vars['report_tree'] = $this->report_items();

		return $vars;
	}

	/**
	 * Get the settings data for the script.
	 *
	 * We need to remove sensitive data and check for the
	 * permissions first.
	 *
	 * @param array $vars Vars.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function dashboard_vars( $vars ) {
		// We need to hide sensitive data from non-admin users.
		if ( Helpers\Permission::can_manage_settings() ) {
			$vars['ps_levels'] = $this->is_network() ? Helpers\Permission::get_ps_levels() : array();
			$vars['roles']     = Helpers\Permission::get_roles( false );
		}

		return $vars;
	}

	/**
	 * Get the common vars specific to settings.
	 *
	 * This data will be available in all scripts.
	 *
	 * @param array $vars Vars.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function common_vars( $vars ) {
		/* translators: %s: heart icon */
		$footer_text  = sprintf( __( 'Made with %s by WPMU DEV', 'ga_trans' ), '<i class="sui-icon-heart"></i>' );
		$custom_image = apply_filters( 'wpmudev_branding_hero_image', '' );
		$whitelabled  = apply_filters( 'wpmudev_branding_hide_branding', false );

		// Rest data.
		$vars['rest'] = array(
			'base'  => rest_url( 'beehive/v1/' ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		);

		// Only for admin.
		if ( is_admin() ) {
			// Plugin data.
			$vars['plugin'] = array(
				'name' => Helpers\General::plugin_name(),
			);

			// Settings data.
			$vars['settings'] = array(
				'site'    => $this->get_settings(),
				'network' => $this->get_settings( true ),
			);

			// White labelling.
			$vars['whitelabel'] = array(
				'hide_branding' => apply_filters( 'wpmudev_branding_hide_branding', false ),
				'hide_doc_link' => apply_filters( 'wpmudev_branding_hide_doc_link', false ),
				'footer_text'   => apply_filters( 'wpmudev_branding_footer_text', $footer_text ),
				'custom_image'  => $custom_image,
				'is_unbranded'  => empty( $custom_image ) && $whitelabled,
				'is_rebranded'  => ! empty( $custom_image ) && $whitelabled,
			);

			// Urls.
			$vars['urls'] = array(
				'base'     => BEEHIVE_URL,
				'site_url' => $this->is_network() ? network_site_url() : site_url(),
				'plugins'  => is_multisite() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' ),
				'settings' => Helpers\Template::settings_url( 'permissions', $this->is_network() ),
				'accounts' => Helpers\Template::accounts_url( 'google', $this->is_network() ),
			);

			// Flags.
			$vars['flags'] = array(
				'network'     => $this->is_network() ? 1 : 0,
				'networkwide' => Helpers\General::is_networkwide() ? 1 : 0,
				'multisite'   => is_multisite() ? 1 : 0,
				'admin'       => Helpers\Permission::is_admin_user( $this->is_network() ) ? 1 : 0,
				'super_admin' => is_multisite() && current_user_can( 'manage_network' ) ? 1 : 0,
				'is_pro'      => beehive_analytics()->is_pro(),
			);

			$vars['dates'] = array(
				'periods'         => $this->periods(),
				'start_date'      => gmdate( 'Y-m-d', strtotime( 'last week monday' ) ),
				'end_date'        => gmdate( 'Y-m-d', strtotime( 'last sunday' ) ),
				'selected_label'  => __( 'Last week', 'ga_trans' ),
				'selected_period' => gmdate( 'Y-m-d', strtotime( 'last week monday' ) ),
			);
		}

		return $vars;
	}

	/**
	 * Get periods for the date range filter dropdown.
	 *
	 * Create an array of date data to show as dropdown in
	 * stats dashboard widget.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function periods() {
		$periods = array(
			__( 'Today', 'ga_trans' )      => array(
				gmdate( 'Y-m-d' ),
				gmdate( 'Y-m-d' ),
			),
			__( 'Yesterday', 'ga_trans' )  => array(
				gmdate( 'Y-m-d', strtotime( '-1 days' ) ),
				gmdate( 'Y-m-d', strtotime( '-1 days' ) ),
			),
			__( 'Last week', 'ga_trans' )  => array(
				gmdate( 'Y-m-d', strtotime( 'last week monday' ) ),
				gmdate( 'Y-m-d', strtotime( 'last sunday' ) ),
			),
			__( 'Last month', 'ga_trans' ) => array(
				gmdate( 'Y-m-d', strtotime( 'first day of previous month' ) ),
				gmdate( 'Y-m-d', strtotime( 'last day of previous month' ) ),
			),
			__( 'Last year', 'ga_trans' )  => array(
				gmdate( 'Y-m-d', strtotime( 'last year January 1st' ) ),
				gmdate( 'Y-m-d', strtotime( 'last year December 31st' ) ),
			),
		);

		/**
		 * Filter to add or remove periods from date filter.
		 *
		 * The key of the item should be the start date, and the value
		 * array should contain label and end date.
		 *
		 * @param array $dates Dates array.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_assets_vars_periods', $periods );
	}

	/**
	 * Get the settings data to be used all over the plugin pages.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.4
	 *
	 * @return array|mixed
	 */
	private function get_settings( $network = false ) {
		// Single sites doesn't have network settings.
		if ( $network && ! is_multisite() ) {
			return array();
		}

		// Get the settings.
		$settings = beehive_analytics()->settings->get_settings_with_default( $network );

		// We need to hide sensitive data from non-admin users.
		if ( ! Helpers\Permission::can_manage_settings() ) {
			unset( $settings['google']['client_id'] );
			unset( $settings['google']['account_id'] );
			unset( $settings['google']['client_secret'] );
			unset( $settings['google_login']['name'] );
			unset( $settings['google_login']['email'] );
			unset( $settings['google_login']['photo'] );
			unset( $settings['google_login']['access_token'] );
		}

		/**
		 * Filter to include or exclude settings item from global vars.
		 *
		 * @param array $settings Settings data.
		 * @param bool  $network  Network flag.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_assets_settings_data_vars', $settings, $network );
	}

	/**
	 * Get the report items tree.
	 *
	 * We only support upto 3rd level tree.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function report_items() {
		$items = array();

		/**
		 * Filter to add report items to the permission settings.
		 *
		 * Please note we support only 3 level tree.
		 *
		 * @param array $items Items.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_settings_report_tree', $items );
	}

	/**
	 * Get user data for the settings.
	 *
	 * @param array $ids User IDs.
	 *
	 * @since 3.2.5
	 *
	 * @return array
	 */
	private function get_users_data( $ids ) {
		if ( empty( $ids ) ) {
			return array();
		}

		$args = array(
			'fields' => array( 'ID', 'user_email', 'display_name' ),
		);

		// Only these user ids.
		$args['include'] = $ids;

		// Search all sites in network.
		if ( $this->is_network() ) {
			$args['blog_id'] = 0;
		}

		$result = get_users( $args );

		$users = array();

		foreach ( $result as $user ) {
			$users[ $user->ID ] = $user;
		}

		/**
		 * Filter hook to modify the list of users excluded/included.
		 *
		 * @param array $users Users list.
		 *
		 * @since  3.2.5
		 */
		return apply_filters( 'beehive_admin_vars_get_users_data', $users, $ids );
	}
}