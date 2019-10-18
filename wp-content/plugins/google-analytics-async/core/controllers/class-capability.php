<?php

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * The capability class of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Capability extends Base {

	/**
	 * Custom capability for settings.
	 *
	 * @var string $settings_cap
	 *
	 * @since 3.2.0
	 */
	const SETTINGS_CAP = 'beehive_manage_settings';

	/**
	 * Custom capability for analytics.
	 *
	 * @var string $analytics_cap
	 *
	 * @since 3.2.0
	 */
	const ANALYTICS_CAP = 'beehive_view_analytics';

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Set capability to roles after settings update.
		add_action( 'beehive_settings_update', [ $this, 'set_analytics_capability' ], 10, 3 );

		// Make sure admins always have access.
		add_filter( 'user_has_cap', [ $this, 'filter_settings_cap' ], 10, 3 );
		add_filter( 'user_has_cap', [ $this, 'filter_analytics_cap' ], 10, 3 );
	}

	/**
	 * Update the role capabilities based on the settings.
	 *
	 * When settings are updated, we need to re-assign the analytics
	 * capability to the selected roles in settings.
	 *
	 * @param array $options Old values.
	 * @param array $values  New values.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @global      $wp_roles
	 *
	 * @return void
	 */
	public function set_analytics_capability( $options, $values, $network ) {
		// Chill. It's network admin.
		if ( $network && is_multisite() ) {
			return;
		}

		// Can subsites overwrite?.
		$can_overwrite = Permission::can_overwrite();

		// Get enabled roles.
		$enabled_roles = (array) beehive_analytics()->settings->get( 'roles', 'permissions', ! $can_overwrite );

		// Custom capability.
		$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions' );

		global $wp_roles;

		// Make sure admin user has the capability in single installations.
		if ( ! is_multisite() ) {
			$enabled_roles = array_merge( [ 'administrator' ], $enabled_roles );
		}

		// Loop through each roles.
		foreach ( $wp_roles->get_names() as $role => $label ) {
			// Get the role object.
			$role_object = $wp_roles->get_role( $role );

			// Role not found.
			if ( empty( $role_object ) ) {
				continue;
			}

			// Custom capability is available in this role. So add cap.
			if ( $role_object->has_cap( $custom_cap ) ) {
				$role_object->add_cap( self::ANALYTICS_CAP );
			} elseif ( in_array( $role, $enabled_roles, true ) ) {
				// Role is enabled in settings, so add capability.
				$role_object->add_cap( self::ANALYTICS_CAP );
			} else {
				// Remove the capability if not enabled.
				$role_object->remove_cap( self::ANALYTICS_CAP );
			}
		}
	}

	/**
	 * Filter a user's capabilities so they can be altered at runtime.
	 *
	 * This is used to grant  the 'wpmudev_videos_manage_settings' capability
	 * to the user if they have the ability to manage options.
	 * This does not get called for Super Admins because super admin has all capabilities.
	 *
	 * @param bool[]   $user_caps     Array of key/value pairs where keys represent a capability name and boolean values
	 *                                represent whether the user has that capability.
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param array    $args          Arguments that accompany the requested capability check.
	 *
	 * @since 3.2.0
	 *
	 * @return bool[] Concerned user's capabilities.
	 */
	public function filter_settings_cap( $user_caps, $required_caps, $args ) {
		// Our custom settings capability is not being checked.
		if ( self::SETTINGS_CAP !== $args[0] ) {
			return $user_caps;
		}

		// User already has the capability.
		if ( array_key_exists( self::SETTINGS_CAP, $user_caps ) ) {
			return $user_caps;
		}

		// Admin should be capable.
		if ( user_can( $args[1], 'manage_options' ) ) {
			$user_caps[ self::SETTINGS_CAP ] = true;
		}

		return $user_caps;
	}

	/**
	 * Filter a user's capabilities so they can be altered at runtime.
	 *
	 * This is used to grant  the 'beehive_view_analytics' capability
	 * to the user if they have the ability to manage options.
	 * This does not get called for Super Admins because super admin has all capabilities.
	 *
	 * @param bool[]   $user_caps     Array of key/value pairs where keys represent a capability name and boolean values
	 *                                represent whether the user has that capability.
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param array    $args          Arguments that accompany the requested capability check.
	 *
	 * @since 3.2.0
	 *
	 * @return bool[] Concerned user's capabilities.
	 */
	public function filter_analytics_cap( $user_caps, $required_caps, $args ) {
		// Our custom settings capability is not being checked.
		if ( self::ANALYTICS_CAP !== $args[0] ) {
			return $user_caps;
		}

		// User already has the capability.
		if ( array_key_exists( self::ANALYTICS_CAP, $user_caps ) ) {
			return $user_caps;
		}

		// Single site admins or subsite admins should have access.
		if ( user_can( $args[1], 'manage_options' ) ) {
			$user_caps[ self::ANALYTICS_CAP ] = true;
		} elseif ( is_multisite() && ! Permission::can_overwrite() ) {
			// Custom capability.
			$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions', true );
			// If the custom capability is already there for the user.
			if ( ! empty( $custom_cap ) && ! empty( $user_caps[ $custom_cap ] ) ) {
				$user_caps[ self::ANALYTICS_CAP ] = true;
			} else {
				// Get enabled roles.
				$roles = (array) beehive_analytics()->settings->get( 'roles', 'permissions', true );

				// Get user object.
				$user = get_userdata( $args[1] );
				// Make sure it exist.
				if ( empty( $user->roles ) ) {
					return $user_caps;
				}
				// Get user roles.
				$user_roles = get_userdata( $args[1] )->roles;
				// Get common roles.
				$common_roles = array_intersect( $roles, $user_roles );
				// Get allowed roles.
				if ( count( $common_roles ) > 0 ) {
					$user_caps[ self::ANALYTICS_CAP ] = true;
				}
			}
		}

		return $user_caps;
	}
}