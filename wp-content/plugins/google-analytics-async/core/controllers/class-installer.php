<?php
/**
 * The installer class of the plugin.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Controllers
 */

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\Cache;
use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Installer
 *
 * @package Beehive\Core\Controllers
 */
class Installer extends Base {

	/**
	 * Run plugin activation scripts.
	 *
	 * If plugin is activated for the first time, setup the
	 * version details, and other flags.
	 * If the Pro version is being activated, check if free version is
	 * active and then deactivate it.
	 *
	 * @since 3.2.0
	 */
	public function activate() {
		// Current plugin version.
		if ( $this->is_network() ) {
			$version = get_site_option( 'beehive_version', '1.0.0' );
		} else {
			$version = get_option( 'beehive_version', '1.0.0' );
		}

		// Set plugin owner.
		$this->set_plugin_owner();
		// Assign capabilities.
		$this->assign_caps();

		/**
		 * Action hook to execute after activation.
		 *
		 * @param int Old version.
		 * @param int New version.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_activate', $version, BEEHIVE_VERSION );
	}

	/**
	 * Upgrade if we are updating from old version.
	 *
	 * This method will only update the version number if the
	 * installation is new.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function upgrade() {
		// Current plugin version.
		if ( $this->is_network() ) {
			$version = get_site_option( 'beehive_version', '1.0.0' );
		} else {
			$version = get_option( 'beehive_version', '1.0.0' );
		}

		/**
		 * This is specific to 3.2.0 upgrade.
		 *
		 * We can not use default upgrade process by checking version
		 * because we need to upgrade each subsites if the plugin setting
		 * is found there.
		 * We will delete the old settings once we upgrade, so we don't need
		 * to upgrade in future.
		 *
		 * @since 3.2.0
		 */
		// Upgrade to 3.2.4.
		if ( version_compare( $version, '3.2.0', '<' ) ) {
			$this->upgrade_3_2_0();
		}

		// Upgrade to 3.2.4.
		if ( version_compare( $version, '3.2.4', '<' ) ) {
			$this->upgrade_3_2_4();
		}

		// Upgrade to 3.3.0.
		if ( version_compare( $version, '3.3.0', '<' ) ) {
			$this->upgrade_3_3();
		}

		// If new installation or older versions.
		if ( BEEHIVE_VERSION !== $version ) {
			// Mark the plugin version.
			if ( $this->is_network() ) {
				update_site_option( 'beehive_version', BEEHIVE_VERSION );
			} else {
				update_option( 'beehive_version', BEEHIVE_VERSION );
			}

			// Assign capabilities.
			$this->assign_caps();

			/**
			 * Action hook to execute after upgrade.
			 *
			 * @param int Old version.
			 * @param int New version.
			 *
			 * @since 3.2.0
			 */
			do_action( 'beehive_after_upgrade', $version, BEEHIVE_VERSION );
		}
	}

	/**
	 * Upgrade the old GA network settings to new structure.
	 *
	 * We need this new structure to simplify the settings
	 * process. Prior to 3.2.0, we had all custom settings
	 * stored in `track_settings` group.
	 * This method is specific for multisite admin. For subsites
	 * and single installations we use upgrade_3_2_0_single().
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function upgrade_3_2_0() {
		// Check whether old settings still exist.
		if ( $this->is_network() ) {
			$old_options = get_site_option( 'ga2_settings', array() );
		} else {
			$old_options = get_option( 'ga2_settings', array() );
		}

		// Upgrade is not required because old settings does not exist.
		if ( empty( $old_options ) ) {
			return;
		}

		// New structure mapping.
		$options = array(
			// General settings.
			'general'      => array(
				'track_admin'              => $this->option_3_2( 'track_admin' ),
				'anonymize'                => $this->option_3_2( 'anonymize_ip' ),
				'force_anonymize'          => $this->option_3_2( 'anonymize_ip_force' ),
				'advertising'              => $this->option_3_2( 'display_advertising' ),
				// Upgrade Pro Sites settings to array.
				'prosites_settings_level'  => array( $this->option_3_2( 'supporter_only', 'track_settings' ) ),
				'prosites_analytics_level' => array( $this->option_3_2( 'supporter_only_reports', 'track_settings' ) ),

			),
			// Tracking settings.
			'tracking'     => array(
				'code' => $this->option_3_2( 'tracking_code' ),
			),
			// Analytics permissions.
			'permissions'  => array(
				'roles'         => $this->upgrade_role_3_2(), // Upgrade role.
				'custom_cap'    => $this->option_3_2( 'minimum_capability_reports' ),
				'overwrite_cap' => $this->option_3_2( 'capability_reports_overwrite' ),
			),
			// Reports settings.
			'reports'      => array(),
			// Google settings.
			'google'       => array(
				'account_id'    => $this->upgrade_account_id_3_2(),
				'client_id'     => $this->option_3_2( 'client_id', 'google_api' ),
				'client_secret' => $this->option_3_2( 'client_secret', 'google_api' ),
				'api_key'       => $this->option_3_2( 'api_key', 'google_api' ),
				'verified'      => $this->option_3_2( 'verified', 'google_api' ),
			),
			// Google login data.
			'google_login' => array(
				'access_token' => $this->option_3_2( 'token', 'google_login' ),
				'logged_in'    => $this->option_3_2( 'logged_in', 'google_login' ),
				'method'       => $this->upgrade_login_method_3_2(),
			),
			'misc'         => array(
				'onboarding_done' => 1,
			),
		);

		// Few items are not required in single/sub sites.
		if ( ! $this->is_network() ) {
			unset( $options['general']['track_admin'] );
			unset( $options['general']['anonymize_ip_force'] );
			unset( $options['general']['prosites_settings_level'] );
			unset( $options['general']['prosites_analytics_level'] );
			unset( $options['permissions']['overwrite_cap'] );
		}

		// Update to new options.
		beehive_analytics()->settings->update_options( $options, $this->is_network() );

		// Delete old settings.
		$this->delete_option_3_2();
	}

	/**
	 * Upgrade to 3.2.4 settings structure.
	 *
	 * Reports settings changed in 3.2.4.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	private function upgrade_3_2_4() {
		// Get the settings.
		if ( $this->is_network() ) {
			$settings = get_site_option( 'beehive_settings', array() );
		} else {
			$settings = get_option( 'beehive_settings', array() );
		}

		// Upgrade is not required because old settings does not exist.
		if ( empty( $settings ) ) {
			return;
		}

		// Upgrade reports.
		$settings = $this->upgrade_reports_3_2_4( $settings );

		// Update to new structure.
		beehive_analytics()->settings->update_options( $settings, $this->is_network() );

		// Clear stats cache.
		Cache::refresh_cache();
		Cache::refresh_transient( $this->is_network() );
	}

	/**
	 * Upgrade to 3.3.0 version.
	 *
	 * Added new option to show welcome modal.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function upgrade_3_3() {
		// Get the settings.
		if ( $this->is_network() ) {
			$settings = get_site_option( 'beehive_settings', array() );
		} else {
			$settings = get_option( 'beehive_settings', array() );
		}

		// Upgrade is not required because old settings does not exist.
		if ( empty( $settings ) ) {
			return;
		}

		// Set welcome modal.
		beehive_analytics()->settings->update( 'show_welcome', 1, 'misc', General::is_networkwide() );
	}

	/**
	 * Delete old options prior to 3.2.0.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function delete_option_3_2() {
		// Old option names.
		$options = array( 'ga2_settings', 'gaplus_ver' );

		// Delete all options.
		foreach ( $options as $option ) {
			$this->is_network() ? delete_site_option( $option ) : delete_option( $option );
		}

		global $wpdb;

		// Delete old login tables.
		// phpcs:ignore
		$wpdb->get_var( "DROP TABLE IF EXISTS {$wpdb->base_prefix}gaplus_login" );
	}

	/**
	 * Get old setting value for a field.
	 *
	 * This works only upto 3.1
	 *
	 * @param string $key     Setting key.
	 * @param string $group   Setting group.
	 * @param mixed  $default Default value.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function option_3_2( $key, $group = 'track_settings', $default = '' ) {
		static $options         = array();
		static $network_options = array();

		// Get old options.
		if ( $this->is_network() && empty( $network_options ) ) {
			$network_options = get_site_option( 'ga2_settings', array() );
		} elseif ( ! $this->is_network() && empty( $options ) ) {
			$options = get_option( 'ga2_settings', array() );
		}

		$current_options = $this->is_network() ? $network_options : $options;

		return isset( $current_options[ $group ][ $key ] ) ? $current_options[ $group ][ $key ] : $default;
	}

	/**
	 * Upgrade role settings to new structure.
	 *
	 * We were using static list of roles with capability as
	 * as key in old version. From 3.2.0, we will be using WP core
	 * function to get the roles dynamically. So upgrade the old
	 * structure to new one.
	 *
	 * @since 3.2.0
	 *
	 * @return array $value
	 */
	private function upgrade_role_3_2() {
		$value = array();

		// Old settings.
		$role = $this->option_3_2( 'minimum_role_capability_reports' );

		// Old roles structure.
		$roles = array(
			'manage_network_options' => 'super_admin', // Custom role name.
			'manage_options'         => 'administrator',
			'publish_pages'          => 'editor',
			'publish_posts'          => 'author',
			'edit_posts'             => 'contributor',
			'read'                   => 'subscriber',
		);

		// Upgrade if mapping found.
		if ( ! empty( $role ) && array_key_exists( $role, $roles ) ) {
			// Get the position of the role.
			$position = array_search( $role, array_keys( $roles ), true );

			// Get the roles array.
			$role_names = array_values( $roles );

			// Add all higher roles to the roles array.
			for ( $i = 0; $i <= $position; $i ++ ) {
				$value[] = $role_names[ $i ];
			}
		}

		return $value;
	}

	/**
	 * Upgrade account ID to new format.
	 *
	 * GA Reporting API v4 required View ID without prefix.
	 * Remove the prefix from old values.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed|string
	 */
	private function upgrade_account_id_3_2() {
		// Get account id.
		$account_id = $this->option_3_2( 'google_analytics_account_id' );
		// Only when account id set.
		if ( ! empty( $account_id ) ) {
			// We need to remove ga prefix from value.
			$account_id = str_replace( 'ga:', '', $account_id );
		}

		return $account_id;
	}

	/**
	 * Upgrade to new login method value.
	 *
	 * This is a new option introduced in 3.2.0. So we need to
	 * check if it is possible to set existing installation value.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function upgrade_login_method_3_2() {
		// Default method.
		$method = 'connect';

		// Get API credentials.
		$client_id     = $this->option_3_2( 'client_id', 'google_api' );
		$client_secret = $this->option_3_2( 'client_secret', 'google_api' );

		// API credentials set, so we can (only) assume that they have logged in using API creds.
		if ( ! empty( $client_id ) && ! empty( $client_secret ) ) {
			$method = 'api';
		}

		return $method;
	}

	/**
	 * Upgrade to new login reports settings.
	 *
	 * New reports settings introduced in 3.2.4.
	 *
	 * @param array $settings Settings data.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function upgrade_reports_3_2_4( $settings ) {
		$report_settings = array();

		// Upgrade reports structure.
		if ( ! empty( $settings['reports'] ) ) {
			foreach ( $settings['reports'] as $type => $enabled_items ) {
				foreach ( $enabled_items as $role => $reports ) {
					// If role item is not found, add.
					if ( ! isset( $report_settings[ $role ] ) ) {
						$report_settings[ $role ] = array();
					}

					// Set report type empty.
					$report_settings[ $role ][ $type ] = array();

					foreach ( $reports as $parent => $children ) {
						// If children found.
						if ( is_array( $children ) ) {
							foreach ( $children as $child => $value ) {
								if ( 'top_pages' === $child || 'top_countries' === $child ) {
									continue;
								} elseif ( ! empty( $value ) ) {
									$report_settings[ $role ][ $type ][] = $child;
								}
							}
						} elseif ( ! empty( $children ) ) {
							if ( 'referrals' === $parent ) {
								// New items added in 3.2.4.
								$report_settings[ $role ][ $type ][] = 'mediums';
								$report_settings[ $role ][ $type ][] = 'social_networks';
								$report_settings[ $role ][ $type ][] = 'search_engines';
							} else {
								$report_settings[ $role ][ $type ][] = $parent;
							}
						}
					}
				}
			}
		}

		// Set reports.
		$settings['reports'] = $report_settings;

		return $settings;
	}

	/**
	 * Set a user meta field to identify who activated the plugin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function set_plugin_owner() {
		// Get current user.
		$user = get_current_user_id();

		if ( ! empty( $user ) ) {
			// If network activated in multisite.
			if ( $this->is_network() ) {
				update_site_option( 'beehive_owner_user', $user );
			} else {
				// Single site.
				update_option( 'beehive_owner_user', $user );
			}
		}
	}

	/**
	 * Set our custom capability to admin user by default.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function assign_caps() {
		// Not needed in network admin.
		if ( ! $this->is_network() ) {
			global $wp_roles;

			// Get the role object.
			$role_object = $wp_roles->get_role( 'administrator' );

			// Assign settings and analytics caps.
			if ( ! empty( $role_object ) ) {
				$role_object->add_cap( Capability::SETTINGS_CAP );
				$role_object->add_cap( Capability::ANALYTICS_CAP );
			}
		}
	}
}