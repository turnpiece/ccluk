<?php
/**
 * Defines permission helper functionality of the plugin.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Helpers
 */

namespace Beehive\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Controllers\Capability;

/**
 * Class Permission
 *
 * @package Beehive\Core\Helpers
 */
class Permission {

	/**
	 * Check if current user is capable for the action.
	 *
	 * @param string $type    Action type.
	 * @param bool   $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function user_can( $type = 'base', $network = false ) {
		switch ( $type ) {
			// Permissions.
			case 'analytics':
				// Make sure Pro Sites capability is also checked.
				$capable = self::can_view_analytics( $network );
				break;

			// Settings capability.
			case 'settings':
				// Make sure Pro Sites capability is also checked.
				$capable = self::can_manage_settings( $network );
				break;

			// By default check minimum capability.
			default:
				$capable = self::is_admin_user( $network );
				break;
		}

		/**
		 * Filter hook to modify access permission.
		 *
		 * @param bool $capable Is user capable.
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_user_can', $capable, $network );
	}

	/**
	 * Check if current site is capable based on Pro Sites level.
	 *
	 * @param string $type Action type.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function has_ps_capability( $type = 'settings' ) {
		// CIf Pro Sites is not active, capable.
		if ( ! class_exists( 'ProSites' ) || ! function_exists( 'is_pro_site' ) || is_network_admin() || ! is_multisite() ) {
			return true;
		}

		// Check based on capability.
		switch ( $type ) {
			case 'analytics':
				$levels = beehive_analytics()->settings->get( 'prosites_analytics_level', 'general', true );
				break;
			case 'settings':
				$levels = beehive_analytics()->settings->get( 'prosites_settings_level', 'general', true );
				break;
			default:
				$levels = array();
		}

		// Return early if no level is selected.
		if ( empty( $levels ) ) {
			return true;
		}

		// Loop through each levels and check.
		foreach ( (array) $levels as $level ) {
			// Only if the current site has the level.
			if ( is_pro_site( get_current_blog_id(), $level ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if current user has the default admin capability.
	 *
	 * When network flag is true, we will check if the user is super admin.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function is_admin_user( $network = false ) {
		// Check if user has the general admin capability.
		if ( $network && is_multisite() ) {
			$is_admin = current_user_can( 'manage_network' );
		} else {
			$is_admin = current_user_can( 'manage_options' );
		}

		/**
		 * Filter hook to modify has capability check.
		 *
		 * @param bool $is_admin Is admin.
		 * @param bool $network  Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_is_admin_user', $is_admin, $network );
	}

	/**
	 * Check if current user has the ability to manage settings.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function can_manage_settings( $network = false ) {
		// Check if user has the custom settings capability.
		if ( $network && is_multisite() ) {
			// Network settings can be managed by super admin only.
			$capable = current_user_can( 'manage_network' );
		} else {
			$capable = current_user_can( Capability::SETTINGS_CAP );
		}

		// Check Pro Sites capability.
		$capable = $capable && self::has_ps_capability( 'settings' );

		/**
		 * Filter hook to modify has settings capability check.
		 *
		 * @param bool $capable Has capability.
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_can_manage_settings', $capable, $network );
	}

	/**
	 * Check if current user has the ability to view analytics.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return array|bool
	 */
	public static function can_view_analytics( $network = false ) {
		// For network only super admin should have access.
		if ( $network && is_multisite() ) {
			$capable = current_user_can( 'manage_network' );
		} elseif ( ! $network && current_user_can( 'manage_options' ) ) {
			// Subsite admins should have access to the subsite stats.
			$capable = true;
		} else {
			// If sub sites can't overwrite permissions get network permissions.
			$network = self::can_overwrite( 'analytics' ) ? $network : true;

			// Check for custom capability.
			$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions', $network );

			// If current user has the custom cap, good to go.
			if ( ! empty( $custom_cap ) && current_user_can( $custom_cap ) ) {
				$capable = true;
			} else {
				// Else they should have our default capability.
				$capable = current_user_can( Capability::ANALYTICS_CAP );
			}
		}

		// Make sure to check the Pro Sites capability.
		$capable = $capable && self::has_ps_capability( 'analytics' );

		/**
		 * Filter hook to modify has analytics capability check.
		 *
		 * @param bool $capable Has capability.
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_can_view_analytics', $capable, $network );
	}

	/**
	 * Check if current site has ability to overwrite permission set from super admin.
	 *
	 * This will work only on multisite.
	 *
	 * @param string $type Permission type (settings or analytics).
	 *
	 * @since 3.2.0
	 * @since 3.2.5 Added type param.
	 *
	 * @return bool
	 */
	public static function can_overwrite( $type = 'settings' ) {
		// No if it's not multisite or in network admin.
		if ( ! is_multisite() || is_network_admin() ) {
			return true;
		}

		// Get the flag.
		if ( 'analytics' === $type ) {
			$overwrite = beehive_analytics()->settings->get( 'overwrite_cap', 'permissions', true );
		} else {
			$overwrite = beehive_analytics()->settings->get( 'overwrite_settings_cap', 'permissions', true );
		}

		/**
		 * Filter hook to change the ability to overwrite permissions.
		 *
		 * @param bool $overwrite Should overwrite?.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_permissions_can_overwrite', $overwrite );
	}

	/**
	 * Get the available report capabilities of the current user role.
	 *
	 * Different user role has different capabilities according to settings.
	 *
	 * @param string $section Report section.
	 * @param string $sub     Report sub section.
	 * @param string $type    Report type (dashboard or statistics).
	 * @param bool   $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public static function has_report_cap( $section, $sub = '', $type = 'dashboard', $network = false ) {
		// Is he an admin?.
		$is_admin = self::is_admin_user( $network );

		// Admin has the power.
		if ( $is_admin ) {
			$has = true;
		} elseif ( $network && ! $is_admin ) {
			$has = false;
		} else {
			// If sub sites can't overwrite permissions get network custom capability.
			$network = self::can_overwrite( 'analytics' ) ? $network : true;

			// Check for custom capability.
			$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions', $network );

			// If current user has the custom cap, good to go.
			if ( ! empty( $custom_cap ) && current_user_can( $custom_cap ) ) {
				$has = true;
			} else {
				// Get capabilities of current user.
				$caps = self::user_report_caps( $type );

				// Has capability set.
				$has = empty( $sub ) ? ! empty( $caps[ $section ] ) : ! empty( $caps[ $section ][ $sub ] );
			}
		}

		/**
		 * Filter hook to modify report capability check.
		 *
		 * @param bool   $has     Has capability.
		 * @param string $section Report section
		 * @param string $roles   Roles of user.
		 * @param string $type    Report type (dashboard or statistics).
		 * @param bool   $network Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_permissions_has_report_cap', $has, $section, $sub, $type, $network );

	}

	/**
	 * Get the available report capabilities of the current user.
	 *
	 * Different user role has different capabilities according to settings.
	 * Note: Do not use this for network admin. It will be empty. Super admin
	 * will have access to everything.
	 *
	 * @param string $type    Report type.
	 * @param bool   $network Network flag.
	 * @param bool   $force   Should force check?.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public static function user_report_caps( $type = 'dashboard', $network = false, $force = false ) {
		static $caps = array();

		$user_caps = array();

		$user_id = get_current_user_id();

		// First get from cache.
		if ( ! $force ) {
			$user_caps = isset( $caps[ $user_id ][ $type ] ) ? $caps[ $user_id ][ $type ] : array();
		}

		// Get from cache if exist.
		if ( empty( $user_caps ) ) {
			// Roles of current user.
			$roles = self::get_current_roles( $network );
			// Selected roles in settings.
			$selected_roles = (array) beehive_analytics()->settings->get( 'roles', 'permissions', $network, array() );

			// Report capabilities of current user role.
			foreach ( $roles as $role ) {
				// Only if role is selected in settings.
				if ( in_array( $role, $selected_roles, true ) ) {
					// Get role's capabilities.
					$role_caps = (array) beehive_analytics()->settings->get( $role, 'reports', $network, array() );
					// Get caps for the role.
					$role_caps = isset( $role_caps[ $type ] ) ? $role_caps[ $type ] : array();
					// Add to caps array.
					if ( ! empty( $role_caps ) ) {
						$user_caps = array_unique( array_merge( $user_caps, $role_caps ) );
					}
				}
			}

			// Set to non-persistent cache.
			$caps[ $user_id ][ $type ] = $user_caps;
		}

		/**
		 * Filter hook to modify available report capabilities.
		 *
		 * @param array  $user_caps Capabilities.
		 * @param string $type      Report type.
		 * @param array  $user      User.
		 * @param bool   $network   Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_permissions_report_caps', $user_caps, $type, $network );
	}

	/**
	 * Get the roles for settings to restrict analytics.
	 *
	 * Roles are taken from wp_roles(). So any custom roles registered
	 * with WP will also included.
	 *
	 * @param bool $include_admin Should include admin.
	 *
	 * @since 3.2.0
	 *
	 * @return array $roles Roles array.
	 */
	public static function get_roles( $include_admin = true ) {
		// Get all available roles.
		$roles = wp_roles()->get_names();

		// Admins can manage the settings, so he should have all access.
		if ( ! $include_admin ) {
			unset( $roles['administrator'] );
		}

		/**
		 * Filter hook to add/remove roles to settings.
		 *
		 * @param array $roles Roles.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_permissions_roles', $roles );
	}

	/**
	 * Get the roles of currently logged in user.
	 *
	 * This is a wrapper function to include super admin as a
	 * role to the list.
	 *
	 * @param bool $network Is network level?.
	 *
	 * @since 3.2.0
	 *
	 * @return array $roles Roles array.
	 */
	public static function get_current_roles( $network = false ) {
		// Current user.
		$user = wp_get_current_user();

		// Roles of current user.
		$roles = (array) $user->roles;

		// Super admin is not a wp role, so add it if current user is one.
		if ( $network && current_user_can( 'manage_network' ) ) {
			$roles[] = 'super_admin';
		}

		/**
		 * Filter hook to add/remove roles to settings.
		 *
		 * @param array $roles   Roles.
		 * @param bool  $network Is network level?.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_permissions_current_roles', $roles, $network );
	}

	/**
	 * Get the roles for settings to restrict analytics.
	 *
	 * We have hardcoded the roles list because we need to
	 * make sure backward compatibility and role setting key
	 * is not changed.
	 * You can add new role using beehive_settings_roles filter.
	 *
	 * @since 3.2.0
	 *
	 * @return array|bool $roles Roles array or false.
	 */
	public static function get_ps_levels() {
		static $levels = null;

		// Check if Pro Sites exist.
		if ( ! class_exists( 'ProSites' ) ) {
			return false;
		}

		if ( is_null( $levels ) ) {
			// Get Pro Sites levels.
			$levels = get_site_option( 'psts_levels', array() );

			// Remove invisible items.
			foreach ( $levels as $level => $data ) {
				if ( empty( $data['is_visible'] ) ) {
					unset( $levels[ $level ] );
				}
			}
		}

		/**
		 * Filter hook to add/remove levels.
		 *
		 * @param array $levels Levels.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_prosites_levels', $levels );
	}
}