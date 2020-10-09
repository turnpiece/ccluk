<?php
/**
 * The Google core class.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics
 */

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Data\Locale;
use Beehive\Core\Helpers\General;
use Beehive\Core\Controllers\Menu;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Controllers\Capability;

/**
 * Class Admin
 *
 * @package Beehive\Core\Modules\Google_Analytics
 */
class Admin extends Base {

	/**
	 * Register all the hooks related to module.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Dashboard stats widget.
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widget' ) );
		add_action( 'wp_network_dashboard_setup', array( $this, 'dashboard_widget' ) );

		// Register assets.
		add_filter( 'beehive_assets_get_scripts', array( $this, 'get_scripts' ), 10, 2 );
		add_filter( 'beehive_assets_get_styles', array( $this, 'get_styles' ), 10, 2 );

		// Admin admin class to our page.
		add_filter( 'beehive_admin_body_classes_is_plugin_admin', array( $this, 'admin_body_class' ) );

		// Add i18n strings for the locale.
		add_filter( 'beehive_i18n_get_locale_scripts', array( $this, 'setup_i18n' ), 10, 2 );

		// Stats menu is required only when logged in.
		if ( Helper::instance()->can_get_stats( $this->is_network() ) ) {
			// Stats metabox for Post/Page edit screen.
			add_action( 'add_meta_boxes', array( $this, 'post_widget' ) );
		}

		// Settings menu in dashboard.
		add_action( 'beehive_admin_menu', array( $this, 'settings_menu' ) );

		// Stats menu in dashboard.
		add_filter( 'beehive_statistics_menu_items', array( $this, 'statistics_menu' ) );

		// Include required google vars.
		add_filter(
			'beehive_assets_scripts_localize_vars_beehive-dashboard-widget',
			array(
				Google_Auth\Views\Admin::instance(),
				'google_vars',
			)
		);

		// Include required google vars.
		add_filter(
			'beehive_assets_scripts_localize_vars_beehive-statistics-page',
			array(
				Google_Auth\Views\Admin::instance(),
				'google_vars',
			)
		);

		// Add statistics report items to settings.
		add_filter(
			'beehive_settings_report_tree',
			array(
				Views\Stats::instance(),
				'report_items',
			)
		);
	}

	/**
	 * Add stats overview dashboard widget.
	 *
	 * Contents of this widget is loaded via Ajax.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		// Do not continue if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Make sure the user has capability.
		if ( Permission::user_can( 'analytics', $this->is_network() ) ) {
			// Register widget.
			wp_add_dashboard_widget(
				'beehive_dashboard',
				__( 'Visitors', 'ga_trans' ),
				array( Views\Stats::instance(), 'dashboard_widget' )
			);
		}
	}

	/**
	 * Stats metabox for the post edit screen.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function post_widget() {
		global $pagenow, $post;

		// Make sure the user has capability (Only for subsite/single site).
		$capable = Permission::user_can( 'analytics' );

		if ( Helper::instance()->can_get_stats() && $capable ) {
			// Allowed post type.
			$allowed_post = in_array( get_post_type(), Helper::instance()->post_types(), true );

			if ( 'post.php' === $pagenow && $allowed_post && $post->ID > 0 ) {
				// Register metabox.
				add_meta_box(
					'beehive_analytics_stats',
					__( 'Statistics for last 30 days', 'ga_trans' ),
					array( Views\Stats::instance(), 'post_widget' ),
					Helper::instance()->post_types(),
					'normal'
				);
			}
		}
	}

	/**
	 * Register admin submenu for the settings page.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function settings_menu() {
		// Add accounts page.
		add_submenu_page(
			Menu::SLUG,
			__( 'Google Analytics Settings', 'ga_trans' ),
			__( 'Google Analytics', 'ga_trans' ),
			Capability::SETTINGS_CAP,
			'beehive-ga-settings',
			array( Views\Stats::instance(), 'settings_page' )
		);
	}

	/**
	 * Register admin submenu for the stats page.
	 *
	 * @param array $submenus Submenus.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function statistics_menu( $submenus ) {
		// Do not continue if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return $submenus;
		}

		// Should be able to view analytics.
		if ( ! Permission::can_view_analytics( $this->is_network() ) ) {
			return $submenus;
		}

		// Admin users always have permission to view analytics.
		if ( ! current_user_can( 'manage_options' ) ) {
			$network = $this->is_network();

			// Check if network options should be considered.
			if ( General::is_networkwide() && ! $network ) {
				// Can sub sites override.
				$network = ! (bool) beehive_analytics()->settings->get( 'overwrite_cap', 'permissions', true );
			}

			// Get the custom capability.
			$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions', $network );

			// Make sure custom cap is not available.
			if ( empty( $custom_cap ) || ! current_user_can( $custom_cap ) ) {
				// Get custom caps.
				$caps = Permission::user_report_caps( 'statistics', $network );

				// If not items allowed or only the statistics parent is added.
				if ( empty( $caps ) || ( count( $caps ) === 1 && in_array( 'statistics', $caps, true ) ) ) {
					return $submenus;
				}
			}
		}

		// Add settings page.
		$submenus['beehive-google-analytics'] = array(
			'page_title' => __( 'Google Analytics', 'ga_trans' ),
			'menu_title' => __( 'Google Analytics', 'ga_trans' ),
			'cap'        => Capability::ANALYTICS_CAP,
			'callback'   => array( Views\Stats::instance(), 'stats_page' ),
		);

		return $submenus;
	}

	/**
	 * Get the styles list to register.
	 *
	 * @param array $styles Styles list.
	 * @param bool  $admin  Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function get_styles( $styles, $admin ) {
		if ( $admin ) {
			// Settings.
			$styles['beehive-ga-settings'] = array(
				'src' => 'ga-settings.min.css',
			);
			// Post statistics.
			$styles['beehive-post-statistics'] = array(
				'src' => 'ga-post-statistics.min.css',
			);
			// Dashboard widget.
			$styles['beehive-dashboard-widget'] = array(
				'src' => 'ga-dashboard-widget.min.css',
			);
			// All statistics page.
			$styles['beehive-statistics-page'] = array(
				'src' => 'ga-statistics-page.min.css',
			);
		}

		return $styles;
	}

	/**
	 * Get the scripts list to register.
	 *
	 * @param array $scripts Scripts list.
	 * @param bool  $admin   Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function get_scripts( $scripts, $admin ) {
		if ( $admin ) {
			// GA settings.
			$scripts['beehive-ga-settings'] = array(
				'src'  => 'ga-settings.min.js',
				'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common' ),
			);
			// Post statistics.
			$scripts['beehive-post-statistics'] = array(
				'src'  => 'ga-post-statistics.min.js',
				'deps' => array( 'beehive-vendors', 'beehive-common' ),
			);
			// Dashboard widget.
			$scripts['beehive-dashboard-widget'] = array(
				'src'  => 'ga-dashboard-widget.min.js',
				'deps' => array( 'beehive-sui-dashboard-widget', 'beehive-vendors', 'beehive-common' ),
			);
			// All statistics page.
			$scripts['beehive-statistics-page'] = array(
				'src'  => 'ga-statistics-page.min.js',
				'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common' ),
			);
			// Dashboard widget SUI.
			$scripts['beehive-sui-dashboard-widget'] = array(
				'src'  => 'sui-dashboard-widget.min.js',
				'deps' => array( 'jquery' ),
			);
		}

		return $scripts;
	}

	/**
	 * Add Beehive admin body class to plugin statistics page.
	 *
	 * @param bool $include Should add class.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public function admin_body_class( $include ) {
		// Enqueue stats widget assets.
		if ( Helper::is_plugin_stats() || Helper::is_ga_settings() ) {
			$include = true;
		}

		return $include;
	}

	/**
	 * Add localized strings that can be used in JavaScript.
	 *
	 * @param array  $strings Existing strings.
	 * @param string $script  Script name.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function setup_i18n( $strings, $script ) {
		switch ( $script ) {
			case 'beehive-ga-settings':
				// Add settings strings.
				$strings = array_merge_recursive(
					$strings,
					Locale::welcome(),
					Locale::auth_form(),
					Data\Locale::settings()
				);
				break;
			case 'beehive-post-statistics':
				// Add strings.
				$strings = array_merge_recursive(
					$strings,
					Data\Locale::post()
				);
				break;
			case 'beehive-dashboard-widget':
				// Add strings.
				$strings = array_merge_recursive(
					$strings,
					Data\Locale::dashboard_widget()
				);
				break;
			case 'beehive-statistics-page':
				// Add strings.
				$strings = array_merge_recursive(
					$strings,
					Data\Locale::statistics()
				);
				break;
		}

		return $strings;
	}
}