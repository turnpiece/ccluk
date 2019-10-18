<?php

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * The compatibility functionality class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Compatibility extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Members plugin.
		add_action( 'members_register_caps', [ $this, 'register_members_caps' ] );
		add_action( 'members_register_cap_groups', [ $this, 'register_members_groups' ] );

		// User role editor plugin.
		add_filter( 'ure_built_in_wp_caps', [ $this, 'filter_ure_caps' ] );
		add_filter( 'ure_capabilities_groups_tree', [ $this, 'filter_ure_groups' ] );
	}

	/**
	 * Registers the custom capability for the Members plugin.
	 *
	 * @link  https://wordpress.org/plugins/members/
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function register_members_caps() {
		// Settings.
		members_register_cap( Capability::SETTINGS_CAP, [
			'label' => __( 'Manage Settings', 'ga_trans' ),
			'group' => 'beehive',
		] );

		// Analytics.
		members_register_cap( Capability::ANALYTICS_CAP, [
			'label' => __( 'View Analytics', 'ga_trans' ),
			'group' => 'beehive',
		] );
	}

	/**
	 * Registers the custom capability group for the Members plugin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function register_members_groups() {
		members_register_cap_group( 'beehive', [
			'label' => General::plugin_name(),
			'caps'  => [
				Capability::SETTINGS_CAP,
				Capability::ANALYTICS_CAP,
			],
			'icon'  => 'dashicons-chart-area',
		] );
	}

	/**
	 * Registers the custom capability for the User Role Editor plugin.
	 *
	 * @link  https://wordpress.org/plugins/user-role-editor/
	 *
	 * @param array $caps Array of existing capabilities.
	 *
	 * @since 3.2.0
	 *
	 * @return array[] Updated array of capabilities.
	 */
	public function filter_ure_caps( $caps ) {
		// Settings.
		$caps[ Capability::SETTINGS_CAP ] = [
			'custom',
			'beehive',
		];

		// Analytics.
		$caps[ Capability::ANALYTICS_CAP ] = [
			'custom',
			'beehive',
		];

		return $caps;
	}

	/**
	 * Registers the custom capability group for the User Role Editor plugin.
	 *
	 * @param array $groups Array of existing groups.
	 *
	 * @since 3.2.0
	 *
	 * @return array[] Updated array of groups.
	 */
	public function filter_ure_groups( $groups ) {
		$groups['beehive'] = [
			'caption' => General::plugin_name(),
			'parent'  => 'custom',
			'level'   => 2,
		];

		return $groups;
	}
}