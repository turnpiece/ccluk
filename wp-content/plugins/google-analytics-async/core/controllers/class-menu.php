<?php
/**
 * The admin class of the plugin.
 *
 * Defines admin-specific functionality of the plugin.
 *
 * @link    http://wpmudev.com
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
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Setup plugin menu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );

		// Setup menu items.
		add_filter( 'beehive_main_menu_items', array( $this, 'dashboard_menu' ), 1 );
		add_filter( 'beehive_main_menu_items', array( $this, 'accounts_menu' ), 4 );
		add_filter( 'beehive_main_menu_items', array( $this, 'settings_menu' ), 5 );
		add_filter( 'beehive_main_menu_items', array( $this, 'tutorials_menu' ), 6 );
	}

	/**
	 * Register admin menu for the settings.
	 *
	 * We will register a dummy menu and then overwrite it with
	 * another sub menu items.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Modified to add Dashboard menu.
	 * @since 3.3.7 Modified to support multiple items.
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Do not add network menu if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Get the list of registered sub menus.
		$submenus = apply_filters( 'beehive_main_menu_items', array() );

		// No need to continue if empty.
		if ( empty( $submenus ) ) {
			return;
		}

		// Get main menu item.
		$main_slug = array_keys( $submenus )[0];
		$main_item = array_values( $submenus )[0];

		// Remove main item.
		unset( $submenus[ $main_slug ] );

		// Add the main page.
		add_menu_page(
			$main_item['page_title'],
			$main_item['menu_title'],
			$main_item['cap'],
			$main_slug,
			$main_item['callback'],
			Admin_View::instance()->get_settings_icon()
		);

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
		 * Action hook to run after main menu setup.
		 *
		 * @since 3.2.7
		 */
		do_action( 'beehive_main_menu' );

		global $menu;

		foreach ( $menu as $position => $data ) {
			// Only when it's Beehive menu.
			if ( isset( $data[2] ) && 'beehive' === $data[2] ) {
				// Rename the plugin main menu title to Beehive.
				// phpcs:ignore
				$menu[ $position ][0] = General::plugin_name();
			}
		}
	}

	/**
	 * Register admin menu for Beehive dashboard.
	 *
	 * @param array $menu_items Menu items.
	 *
	 * @since 3.3.7
	 *
	 * @return array
	 */
	public function dashboard_menu( $menu_items ) {
		// Add the dashboard page.
		if ( current_user_can( Capability::SETTINGS_CAP ) ) {
			$menu_items['beehive'] = array(
				'page_title' => __( 'Beehive Dashboard', 'ga_trans' ),
				'menu_title' => __( 'Dashboard', 'ga_trans' ),
				'cap'        => Capability::SETTINGS_CAP,
				'callback'   => array( Admin_View::instance(), 'dashboard_page' ),
			);
		}

		return $menu_items;
	}

	/**
	 * Register admin menu for account settings.
	 *
	 * This is where we handle the authentications for all integrations
	 * such as Google, Facebook etc.
	 *
	 * @param array $menu_items Menu items.
	 *
	 * @since 3.3.7
	 *
	 * @return array
	 */
	public function accounts_menu( $menu_items ) {
		// Add accounts page.
		if ( current_user_can( Capability::SETTINGS_CAP ) ) {
			$menu_items['beehive-accounts'] = array(
				'page_title' => __( 'Beehive Accounts', 'ga_trans' ),
				'menu_title' => __( 'Accounts', 'ga_trans' ),
				'cap'        => Capability::SETTINGS_CAP,
				'callback'   => array( Admin_View::instance(), 'accounts_page' ),
			);
		}

		return $menu_items;
	}

	/**
	 * Register admin menu for general settings.
	 *
	 * Beehive's general settings are managed in this page.
	 *
	 * @param array $menu_items Menu items.
	 *
	 * @since 3.3.7
	 *
	 * @return array
	 */
	public function settings_menu( $menu_items ) {
		// Add settings page.
		if ( current_user_can( Capability::SETTINGS_CAP ) ) {
			$menu_items['beehive-settings'] = array(
				'page_title' => __( 'Beehive Settings', 'ga_trans' ),
				'menu_title' => __( 'Settings', 'ga_trans' ),
				'cap'        => Capability::SETTINGS_CAP,
				'callback'   => array( Admin_View::instance(), 'settings_page' ),
			);
		}

		return $menu_items;
	}

	/**
	 * Register admin menu for tutorials.
	 *
	 * @param array $menu_items Menu items.
	 *
	 * @since 3.3.7
	 *
	 * @return array
	 */
	public function tutorials_menu( $menu_items ) {
		// Add tutorials page.
		if ( current_user_can( Capability::SETTINGS_CAP ) ) {
			// Check if tutorials should be hidden.
			$hide = apply_filters( 'wpmudev_branding_hide_doc_link', false );

			if ( ! $hide ) {
				$menu_items['beehive-tutorials'] = array(
					'page_title' => __( 'Beehive Tutorials', 'ga_trans' ),
					'menu_title' => __( 'Tutorials', 'ga_trans' ),
					'cap'        => Capability::SETTINGS_CAP,
					'callback'   => array( Admin_View::instance(), 'tutorials_page' ),
				);
			}
		}

		return $menu_items;
	}
}