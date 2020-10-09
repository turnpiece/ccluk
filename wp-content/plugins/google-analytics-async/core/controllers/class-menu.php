<?php
/**
 * The admin class of the plugin.
 *
 * Defines admin-specific functionality of the plugin.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Controllers
 */

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Views\Admin as Admin_View;

/**
 * Class Admin
 *
 * @package Beehive\Core\Controllers
 */
class Menu extends Base {

	/**
	 * Main slug of the beehive menu page.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	const SLUG = 'beehive';

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Setup plugin menu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Setup network admin menu.
		add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Register admin menu for the settings.
	 *
	 * We will register a dummy menu and then overwrite it with
	 * another sub menu items.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Modified to add Dashboard menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Do not add network menu if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Setup dashboard menu.
		$this->dashboard_menu();

		/**
		 * Register additional menus for Beehive.
		 *
		 * @since 3.2.4
		 */
		do_action( 'beehive_admin_menu' );

		// Setup accounts menu.
		$this->accounts_menu();

		// Setup settings menu.
		$this->settings_menu();

		// Statistics menu.
		$this->statistics_menu();

		global $menu;

		foreach ( $menu as $position => $data ) {
			// Only when it's Beehive menu.
			if ( isset( $data[2] ) && self::SLUG === $data[2] ) {
				// Rename the plugin main menu title to Beehive.
				// phpcs:ignore
				$menu[ $position ][0] = General::plugin_name();
			}
		}
	}

	/**
	 * Register admin menu for the statistics reports.
	 *
	 * Modules can use `beehive_statistics_menu_items` filter to add
	 * new items to the menu. Menu will appear only if at least one
	 * submenu item is added to the hook.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	private function statistics_menu() {
		// Get the list of registered sub menus.
		$submenus = apply_filters( 'beehive_statistics_menu_items', array() );

		// No need to continue if empty.
		if ( empty( $submenus ) ) {
			return;
		}

		// The main menu slug.
		$main_slug = 'beehive-statistics';

		// Add the statistics main menu.
		add_menu_page(
			__( 'Statistics', 'ga_trans' ),
			__( 'Statistics', 'ga_trans' ),
			Capability::ANALYTICS_CAP,
			$main_slug,
			null,
			Admin_View::instance()->get_statistics_icon(),
			3
		);

		// Add a fake page and we will remove it later.
		add_submenu_page(
			$main_slug,
			'',
			'',
			Capability::ANALYTICS_CAP,
			$main_slug,
			null
		);

		// Remove the fake submenu.
		remove_submenu_page( 'beehive-statistics', 'beehive-statistics' );

		// Add settings page.
		foreach ( $submenus as $slug => $submenu ) {
			add_submenu_page(
				$main_slug,
				$submenu['page_title'],
				$submenu['menu_title'],
				$submenu['cap'],
				$slug,
				$submenu['callback']
			);
		}

		/**
		 * Action hook to run after statistics menu setup.
		 *
		 * @since 3.2.4
		 */
		do_action( 'beehive_statistics_menu' );
	}

	/**
	 * Register admin menu for integrations settings.
	 *
	 * Modules can use `beehive_integrations` filter to add integrations
	 * to the list. If the list is empty, the menu will not be added.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function dashboard_menu() {
		// Add the dashboard page.
		add_menu_page(
			__( 'Beehive Dashboard', 'ga_trans' ),
			__( 'Dashboard', 'ga_trans' ),
			Capability::SETTINGS_CAP,
			self::SLUG,
			array( Admin_View::instance(), 'dashboard_page' ),
			Admin_View::instance()->get_settings_icon()
		);
	}

	/**
	 * Register admin menu for account settings.
	 *
	 * This is where we handle the authentications for all integrations
	 * such as Google, Facebook etc.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function accounts_menu() {
		// Add accounts page.
		add_submenu_page(
			self::SLUG,
			__( 'Beehive Accounts', 'ga_trans' ),
			__( 'Accounts', 'ga_trans' ),
			Capability::SETTINGS_CAP,
			'beehive-accounts',
			array( Admin_View::instance(), 'accounts_page' )
		);
	}

	/**
	 * Register admin menu for general settings.
	 *
	 * Beehive's general settings are managed in this page.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function settings_menu() {
		// Hide menu if not allowed.
		if ( General::is_networkwide() && ! $this->is_network() ) {
			// Get permission overriding settings.
			$settings_cap  = beehive_analytics()->settings->get( 'overwrite_settings_cap', 'permissions', true );
			$analytics_cap = beehive_analytics()->settings->get( 'overwrite_cap', 'permissions', true );

			// If permissions can not be overridden.
			if ( empty( $settings_cap ) && empty( $analytics_cap ) ) {
				return;
			}
		}

		// Add settings page.
		add_submenu_page(
			self::SLUG,
			__( 'Beehive Settings', 'ga_trans' ),
			__( 'Settings', 'ga_trans' ),
			Capability::SETTINGS_CAP,
			'beehive-settings',
			array( Admin_View::instance(), 'settings_page' ),
			998
		);
	}
}