<?php
/**
 * The capability class of the plugin.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Controllers
 */

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_User;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Capability
 *
 * @package Beehive\Core\Controllers
 */
class Capability extends Base {

	/**
	 * Custom capability for settings.
	 *
	 * @since 3.2.0
	 *
	 * @var string $settings_cap
	 */
	const SETTINGS_CAP = 'beehive_manage_settings';

	/**
	 * Custom capability for analytics.
	 *
	 * @since 3.2.0
	 *
	 * @var string $analytics_cap
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
		add_action( 'beehive_settings_update', array( $this, 'set_analytics_capability' ), 10, 3 );
		add_action( 'beehive_settings_update', array( $this, 'set_settings_capability' ), 10, 3 );

		// Filter settings capabilities.
		add_filter( 'user_has_cap', array( $this, 'filter_settings_role_cap' ), 10, 3 );
		add_filter( 'user_has_cap', array( $this, 'filter_settings_user_cap' ), 11, 3 );

		// Filter analytics capabilities.
		add_filter( 'user_has_cap', array( $this, 'filter_analytics_cap' ), 10, 3 );
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
	 * @global      $wp_roles
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_analytics_capability( $options, $values, $network ) {
		// Chill. It's network admin.
		if ( $network && is_multisite() ) {
			return;
		}

		// Can subsites overwrite?.
		$can_overwrite = Permission::can_overwrite( 'analytics' );

		// Get enabled roles.
		$enabled_roles = (array) beehive_analytics()->settings->get( 'roles', 'permissions', ! $can_overwrite );

		// Custom capability.
		$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions' );

		global $wp_roles;

		// Make sure admin user has the capability in single installations.
		if ( ! in_array( 'administrator', $enabled_roles, true ) ) {
			$enabled_roles = array_merge( array( 'administrator' ), $enabled_roles );
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
	 * Update the role capabilities based on the settings.
	 *
	 * When settings are updated, we need to re-assign the settings
	 * capability to the selected roles and users in settings.
	 *
	 * @param array $options Old values.
	 * @param array $values  New values.
	 * @param bool  $network Network flag.
	 *
	 * @global      $wp_roles
	 *
	 * @since 3.2.5
	 *
	 * @return void
	 */
	public function set_settings_capability( $options, $values, $network ) {
		// Chill. It's network admin.
		if ( $network && is_multisite() ) {
			return;
		}

		// Can subsites overwrite?.
		$can_overwrite = Permission::can_overwrite( 'settings' );

		// Get enabled roles.
		$enabled_roles = (array) beehive_analytics()->settings->get( 'settings_roles', 'permissions', ! $can_overwrite, array() );

		global $wp_roles;

		// Make sure admin user has the capability in single installations.
		if ( ! in_array( 'administrator', $enabled_roles, true ) ) {
			$enabled_roles = array_merge( array( 'administrator' ), $enabled_roles );
		}

		// Loop through each roles.
		foreach ( $wp_roles->get_names() as $role => $label ) {
			// Get the role object.
			$role_object = $wp_roles->get_role( $role );

			// Role not found.
			if ( empty( $role_object ) ) {
				continue;
			}

			if ( in_array( $role, $enabled_roles, true ) ) {
				// Role is enabled in settings, so add capability.
				$role_object->add_cap( self::SETTINGS_CAP );
			} else {
				// Remove the capability if not enabled.
				$role_object->remove_cap( self::SETTINGS_CAP );
			}
		}

		// Now process users.
		$this->set_settings_capability_user();
	}

	/**
	 * Update the user capabilities based on the settings.
	 *
	 * Admins can specifically add or remove users from permission.
	 *
	 * @since 3.2.5
	 *
	 * @return void
	 */
	private function set_settings_capability_user() {
		// Can subsites overwrite?.
		$can_overwrite = Permission::can_overwrite( 'settings' );

		// Get enabled and disabled users.
		$included_users = (array) beehive_analytics()->settings->get( 'settings_include_users', 'permissions', ! $can_overwrite, array() );
		$excluded_users = (array) beehive_analytics()->settings->get( 'settings_exclude_users', 'permissions', ! $can_overwrite, array() );

		// Loop through all allowed users.
		foreach ( $included_users as $user_id ) {
			$user = get_userdata( $user_id );

			// Grant settings capability to the user.
			if ( $user instanceof WP_User ) {
				$user->add_cap( self::SETTINGS_CAP, true );
			}
		}

		// Loop through all denied users.
		foreach ( $excluded_users as $user_id ) {
			$user = get_userdata( $user_id );

			// Deny settings capability to the user.
			if ( $user instanceof WP_User ) {
				$user->add_cap( self::SETTINGS_CAP, false );
			}
		}
	}

	/**
	 * Filter a user's capabilities so they can be altered at runtime.
	 *
	 * This is used to grant  the 'beehive_manage_settings' capability
	 * to the user if they have the ability to manage options.
	 * This does not get called for Super Admins because super admin has all capabilities.
	 *
	 * @param bool[]   $user_caps     Array of key/value pairs where keys represent a capability name and boolean values
	 *                                represent whether the user has that capability.
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param array    $args          Arguments that accompany the requested capability check.
	 *
	 * @since 3.2.5
	 *
	 * @return bool[] Concerned user's capabilities.
	 */
	public function filter_settings_role_cap( $user_caps, $required_caps, $args ) {
		// Our custom settings capability is not being checked.
		if ( self::SETTINGS_CAP !== $args[0] ) {
			return $user_caps;
		}

		$override = Permission::can_overwrite( 'settings' );

		// Get enabled roles.
		$roles = (array) beehive_analytics()->settings->get( 'settings_roles', 'permissions', ! $override, array() );

		// Make sure admin user has the capability in single installations and subsites.
		$roles = array_merge( array( 'administrator' ), $roles );

		// Get user object.
		$user = get_userdata( $args[1] );

		// Make sure it exists.
		if ( empty( $user->roles ) ) {
			return $user_caps;
		}

		// Get user roles.
		$user_roles = get_userdata( $args[1] )->roles;

		// Get common roles.
		$common_roles = array_intersect( $roles, $user_roles );

		// Get allowed roles.
		if ( count( $common_roles ) > 0 ) {
			$user_caps[ self::SETTINGS_CAP ] = true;
		} else {
			$user_caps[ self::SETTINGS_CAP ] = false;
		}

		return $user_caps;
	}

	/**
	 * Filter a user's capabilities so they can be altered at runtime.
	 *
	 * Forcefully include granted individual users and deny excluded individual
	 * users on run-time.
	 *
	 * @param bool[]   $user_caps     Array of key/value pairs where keys represent a capability name and boolean values
	 *                                represent whether the user has that capability.
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param array    $args          Arguments that accompany the requested capability check.
	 *
	 * @since 3.2.5
	 *
	 * @return bool[] Concerned user's capabilities.
	 */
	public function filter_settings_user_cap( $user_caps, $required_caps, $args ) {
		// Our custom settings capability is not being checked.
		if ( self::SETTINGS_CAP !== $args[0] ) {
			return $user_caps;
		}

		// Can subsites overwrite?.
		$can_overwrite = Permission::can_overwrite( 'settings' );

		// Get enabled and disabled users.
		$included_users = (array) beehive_analytics()->settings->get( 'settings_include_users', 'permissions', ! $can_overwrite, array() );
		$excluded_users = (array) beehive_analytics()->settings->get( 'settings_exclude_users', 'permissions', ! $can_overwrite, array() );

		// Grant included user.
		// phpcs:ignore
		if ( in_array( $args[1], $included_users ) ) {
			$user_caps[ self::SETTINGS_CAP ] = true;
		}

		// Deny excluded user.
		// phpcs:ignore
		if ( in_array( $args[1], $excluded_users ) ) {
			$user_caps[ self::SETTINGS_CAP ] = false;
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
		} elseif ( is_multisite() && ! Permission::can_overwrite( 'analytics' ) ) {
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