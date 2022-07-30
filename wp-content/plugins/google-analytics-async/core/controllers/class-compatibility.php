<?php
/**
 * The compatibility functionality class.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Controllers
 */

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Compatibility
 *
 * @package Beehive\Core\Controllers
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
		add_action( 'members_register_caps', array( $this, 'register_members_caps' ) );
		add_action( 'members_register_cap_groups', array( $this, 'register_members_groups' ) );

		// User role editor plugin.
		add_filter( 'ure_built_in_wp_caps', array( $this, 'filter_ure_caps' ) );
		add_filter( 'ure_capabilities_groups_tree', array( $this, 'filter_ure_groups' ) );

		// WPMU Domain Mapping support.
		add_filter( 'beehive_google_analytics_request_home_url', array( $this, 'filter_wpmu_dm_mapped_url' ) );
		add_filter(
			'beehive_google_analytics_popular_widget_process_url_replace',
			array(
				$this,
				'filter_wpmu_dm_mapped_url_replace',
			)
		);
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
		members_register_cap(
			Capability::SETTINGS_CAP,
			array(
				'label' => __( 'Manage Settings', 'ga_trans' ),
				'group' => 'beehive',
			)
		);

		// Analytics.
		members_register_cap(
			Capability::ANALYTICS_CAP,
			array(
				'label' => __( 'View Analytics', 'ga_trans' ),
				'group' => 'beehive',
			)
		);
	}

	/**
	 * Registers the custom capability group for the Members plugin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function register_members_groups() {
		members_register_cap_group(
			'beehive',
			array(
				'label' => General::plugin_name(),
				'caps'  => array(
					Capability::SETTINGS_CAP,
					Capability::ANALYTICS_CAP,
				),
				'icon'  => 'dashicons-chart-area',
			)
		);
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
		$caps[ Capability::SETTINGS_CAP ] = array(
			'custom',
			'beehive',
		);

		// Analytics.
		$caps[ Capability::ANALYTICS_CAP ] = array(
			'custom',
			'beehive',
		);

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
		$groups['beehive'] = array(
			'caption' => General::plugin_name(),
			'parent'  => 'custom',
			'level'   => 2,
		);

		return $groups;
	}

	/**
	 * Filter the url to return mapped url before setting GA filter.
	 *
	 * @param string $url Home URL.
	 *
	 * @link  https://wordpress.org/plugins/wordpress-mu-domain-mapping/
	 *
	 * @since 3.2.4
	 *
	 * @return string.
	 */
	public function filter_wpmu_dm_mapped_url( $url ) {
		// Only if domain mapping exist.
		if ( function_exists( 'domain_mapping_siteurl' ) ) {
			// WordPress MU Domain Mapping.
			$url = domain_mapping_siteurl( $url );
		}

		return $url;
	}

	/**
	 * Filter the post/page url to display the links in popular posts widget.
	 *
	 * @param string $url Post URL.
	 *
	 * @link  https://wordpress.org/plugins/wordpress-mu-domain-mapping/
	 *
	 * @since 3.2.4
	 *
	 * @return string.
	 */
	public function filter_wpmu_dm_mapped_url_replace( $url ) {
		// Only if domain mapping exist.
		if ( function_exists( 'domain_mapping_siteurl' ) ) {
			// Get mapped url without protocol.
			$mapped_url = str_replace(
				array(
					'http://',
					'https://',
				),
				'',
				domain_mapping_siteurl( home_url() )
			);

			// Get home url without protocol.
			$home_url = str_replace( array( 'http://', 'https://' ), '', home_url() );

			// Replace it with mapped url.
			$url = str_replace( $mapped_url, $home_url, $url );
		}

		return $url;
	}
}