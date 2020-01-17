<?php

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Views\Settings as Settings_View;

/**
 * The admin class of the plugin.
 *
 * Defines admin-specific functionality of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Admin extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Add settings menu.
		add_filter( 'beehive_admin_menu_items', [ $this, 'settings_menu' ], 10, 2 );

		// Setup plugin menu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		// Setup network admin menu.
		add_action( 'network_admin_menu', [ $this, 'admin_menu' ] );

		// Process settings form.
		add_action( 'admin_init', [ Settings::instance(), 'process_settings' ] );

		// Add body class to admin pages.
		add_filter( 'admin_body_class', [ $this, 'admin_body_classes' ] );

		// Add plugin action links.
		add_filter( 'plugin_action_links_' . plugin_basename( BEEHIVE_PLUGIN_FILE ), [
			$this,
			'action_links',
		] );

		// Only if network wide.
		if ( General::is_networkwide() ) {
			// Network admin plugin action links.
			add_filter( 'network_admin_plugin_action_links_' . plugin_basename( BEEHIVE_PLUGIN_FILE ), [
				$this,
				'action_links',
			] );
		}

		// Add links next to network admin plugin details.
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
	}

	/**
	 * Add custom admin body class for SUI.
	 *
	 * @param string $classes Admin body class.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function admin_body_classes( $classes ) {
		// Do not continue if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return $classes;
		}

		// Set our custom body class.
		$classes .= ' sui-beehive-admin ';

		// Only within our admin page.
		if ( General::is_plugin_admin() ) {
			// Shared UI.
			$classes .= ' sui-' . str_replace( '.', '-', BEEHIVE_SUI_VERSION ) . ' ';
		}

		return $classes;
	}

	/**
	 * Register admin menu for the settings.
	 *
	 * We will register a dummy menu and then overwrite it with
	 * another sub menu items.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Do not add network menu if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Get plugin name.
		$name = General::plugin_name();

		// Add a dummy page.
		add_menu_page(
			$name,
			$name,
			Capability::SETTINGS_CAP,
			'beehive-settings',
			false,
			Settings_View::instance()->get_menu_icon()
		);

		// Add settings page.
		add_submenu_page(
			'beehive-settings',
			__( 'Beehive Settings', 'ga_trans' ),
			__( 'Settings', 'ga_trans' ),
			Capability::SETTINGS_CAP,
			'beehive-settings', // Should be same as parent.
			[ Settings_View::instance(), 'settings_page' ]
		);
	}

	/**
	 * Action links for plugins listing page.
	 *
	 * Add quick links to plugin settings page, docs page, upgrade page
	 * from the plugins listing page.
	 *
	 * @param array $links Links array.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function action_links( $links ) {
		// Added a fix for weird warning in multisite, "array_unshift() expects parameter 1 to be array, null given".
		$links = empty( $links ) ? [] : $links;

		// Common links.
		$custom = [
			'settings' => '<a href="' . Template::settings_page( 'general', $this->is_network() ) . '" aria-label="' . esc_attr( __( 'Settings', 'ga_trans' ) ) . '">' . __( 'Settings', 'ga_trans' ) . '</a>',
			'docs'     => '<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/beehive/?utm_source=beehive&utm_medium=plugin&utm_campaign=beehive_pluginlist_docs" aria-label="' . esc_attr( __( 'Documentation', 'ga_trans' ) ) . '" target="_blank">' . __( 'Docs', 'ga_trans' ) . '</a>',
		];

		// WPMUDEV membership status.
		$membership = General::membership_status();

		// Expired membership.
		if ( ! beehive_analytics()->is_pro() ) {
			$custom['upgrade'] = '<a href="https://premium.wpmudev.org/?utm_source=beehive&utm_medium=plugin&utm_campaign=beehive_pluginlist_upgrade" aria-label="' . esc_attr( __( 'Upgrade to Beehive Pro', 'ga_trans' ) ) . '" target="_blank" style="color: #8D00B1;">' . esc_html__( 'Upgrade', 'ga_trans' ) . '</a>';
		} elseif ( 'expired' === $membership || 'free' === $membership ) {
			$custom['renew'] = '<a href="https://premium.wpmudev.org/?utm_source=beehive&utm_medium=plugin&utm_campaign=beehive_pluginlist_renew" aria-label="' . esc_attr( __( 'Renew Your Membership', 'ga_trans' ) ) . '" target="_blank" style="color: #8D00B1;">' . esc_html__( 'Renew Membership', 'ga_trans' ) . '</a>';
		}

		// Merge custom links to first.
		$links = array_merge( $custom, $links );

		return $links;
	}

	/**
	 * Add custom links to support and roadmap next to plugin meta.
	 *
	 * @param array  $links Current links.
	 * @param string $file  Plugin base name.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		// Show network meta links only when activated network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return $links;
		}

		// Make sure the links are array.
		$links = empty( $links ) ? [] : $links;

		// Only for our plugin.
		if ( plugin_basename( BEEHIVE_PLUGIN_FILE ) === $file ) {
			if ( beehive_analytics()->is_pro() ) {
				$custom['support'] = '<a href="https://premium.wpmudev.org/get-support/" aria-label="' . esc_html__( 'Get Premium Support', 'ga_trans' ) . '" target="_blank">' . esc_html__( 'Premium Support', 'ga_trans' ) . '</a>';
			} else {
				$custom['rate']    = '<a href="https://wordpress.org/support/plugin/beehive-analytics/reviews/?rate=5#new-post" aria-label="' . esc_html__( 'Rate Beehive', 'ga_trans' ) . '" target="_blank">' . esc_html__( 'Rate Beehive', 'ga_trans' ) . '</a>';
				$custom['support'] = '<a href="https://wordpress.org/support/plugin/404-to-301/" aria-label="' . esc_html__( 'Get Support', 'ga_trans' ) . '" target="_blank">' . esc_html__( 'Support', 'ga_trans' ) . '</a>';
			}

			$custom['roadmap'] = '<a href="https://premium.wpmudev.org/roadmap/" aria-label="' . esc_html__( 'View our Public Roadmap', 'ga_trans' ) . '" target="_blank">' . esc_html__( 'Roadmap', 'ga_trans' ) . '</a>';

			// Add our custom links.
			$links = array_merge( $links, $custom );
		}

		return $links;
	}
}