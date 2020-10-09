<?php
/**
 * The settings helper class of the plugin.
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

use Beehive\Core\Views;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Settings
 *
 * @package Beehive\Core\Controllers
 */
class Settings extends Base {

	/**
	 * Settings key name.
	 *
	 * @var string
	 *
	 * @since 3.2.0
	 */
	private $setting_key = 'beehive_settings';

	/**
	 * Default network settings array.
	 *
	 * @var array
	 *
	 * @since 3.2.0
	 */
	private $default_settings = array(
		'general'      => array(
			'track_admin'              => false, // Flag to check if admin tracking is enabled.
			'anonymize'                => false, // Flag to check if IP Anonymization is enabled.
			'force_anonymize'          => false, // Force IP Anonymization in subsites.
			'advertising'              => false, // Flag to enable advertising.
			'prosites_settings_level'  => array(), // Pro Sites level for accessing settings.
			'prosites_analytics_level' => array(), // Pro Sites level to accessing analytics.
		),
		'tracking'     => array(
			'code' => '', // Tracking code.
		),
		'permissions'  => array(
			'roles'                  => array(), // Roles enabled for statistics.
			'custom_cap'             => '', // Custom capability enabled for statistics.
			'overwrite_cap'          => false, // Flag to check if subsites can override stats cap.
			'overwrite_settings_cap' => false, // Flag to check if subsites can override settings cap.
			'settings_roles'         => array(), // Enabled roles for settings permission.
			'settings_exclude_users' => array(), // Excluded users for settings permission.
			'settings_include_users' => array(), // Included users for settings permission.
		),
		'reports'      => array(), // Selected report permissions.
		'google'       => array(
			'client_id'     => '', // Google client id entered by user.
			'client_secret' => '', // Google client secret entered by user.
			'api_key'       => '', // Google API key (unused).
			'account_id'    => '', // Selected profile id.
			'auto_track'    => true, // Check if automatic tracking code detection is enabled.
		),
		'google_login' => array(
			'client_id'     => '', // The client id used to login.
			'client_secret' => '', // The client secret used to login.
			'access_token'  => '', // The access token for the auth.
			'logged_in'     => '', // Login flag.
			'method'        => 'connect', // connect or api.
			'name'          => '', // Logged in user's name.
			'email'         => '', // Logged in user's email.
			'photo'         => '', // Logged in user's photo.
		),
		'misc'         => array(
			'onboarding_done' => false, // Flag to check if onboarding is dismissed/done.
			'auto_track'      => false, // Auto tracking code collected from selected profile.
			'show_welcome'    => false, // To show welcome modal.
		),
		'integrations' => array(
			'active' => array(),
		),
	);

	/**
	 * Network settings array.
	 *
	 * @var array
	 *
	 * @since 3.2.0
	 */
	private $network_settings = array();

	/**
	 * Single site settings array.
	 *
	 * @var array
	 *
	 * @since 3.2.0
	 */
	private $settings = array();

	/**
	 * Settings constructor.
	 *
	 * Initialize the setttings values.
	 *
	 * @since 3.2.0
	 */
	protected function __construct() {
		// Initialize settings first.
		if ( $this->is_network() ) {
			$this->init( true );
		} else {
			$this->init();
			if ( is_multisite() ) {
				$this->init( true );
			}
		}
	}

	/**
	 * Initialize settings from DB.
	 *
	 * We need to initialize network settings and single
	 * site settings separately.
	 *
	 * @param bool $network Is network level settings?.
	 *
	 * @since 3.2.0
	 */
	public function init( $network = false ) {
		// Get network options.
		$options = $this->get_options( false, $network, true );

		// Set setting value.
		$network ? $this->network_settings = $options : $this->settings = $options;

		/**
		 * Action hook to execute after initializing settings.
		 *
		 * @param array $options Current options.
		 * @param bool  $network Network flag.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_settings_init', $options, $network );
	}

	/**
	 * Get default settings array.
	 *
	 * This will also initialize the default settings array
	 * using filters so that other plugins can add new item
	 * to the array.
	 *
	 * @param bool $network Is network level?.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function default_settings( $network = false ) {
		// Get default settings.
		$settings = $this->default_settings;

		if ( $network ) {
			/**
			 * Filter to modify default settings array.
			 *
			 * Use this filter to add new item to settings array.
			 *
			 * @since 3.2.0
			 */
			return apply_filters( 'beehive_default_network_settings', $settings );
		} else {
			// Few items are not required in sub sites.
			if ( is_multisite() ) {
				unset( $settings['general']['anonymize_ip_force'] );
				unset( $settings['general']['prosites_settings_level'] );
				unset( $settings['general']['prosites_analytics_level'] );
				unset( $settings['permissions']['overwrite_cap'] );
			}

			/**
			 * Filter to modify default settings array.
			 *
			 * Use this filter to add new item to settings array.
			 *
			 * @since 3.2.0
			 */
			return apply_filters( 'beehive_default_settings', $settings );
		}
	}

	/**
	 * Get the settings data with default settings replaced for empty ones.
	 *
	 * Use this function if you want to get the full structure of the currently
	 * available settings data. If some values are not set, we will use the default
	 * value instead.
	 * Also for the report section, we will use all available roles with empty items.
	 *
	 * @param bool $network Is network level?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function get_settings_with_default( $network = false ) {
		// Get the settings.
		$settings = $this->get_options( false, $network );

		// Default values.
		$default = $this->default_settings( $network );

		// Merge with default options if empty.
		foreach ( $default as $group => $keys ) {
			foreach ( $keys as $key => $value ) {
				if ( ! isset( $settings[ $group ] ) ) {
					$settings[ $group ] = array(
						$key => $value,
					);
				} elseif ( ! isset( $settings[ $group ][ $key ] ) ) {
					$settings[ $group ][ $key ] = $value;
				}
			}
		}

		// Get all roles.
		$roles = Permission::get_roles( false );

		// Report items.
		$items = Views\Admin::instance()->report_items();

		// Make sure each roles are available in reports.
		foreach ( $roles as $role => $name ) {
			if ( empty( $settings['reports'][ $role ] ) ) {
				$settings['reports'][ $role ] = array();
			}

			foreach ( $items as $report ) {
				// Add each parents if children found.
				if ( empty( $settings['reports'][ $role ][ $report['name'] ] ) ) {
					$settings['reports'][ $role ][ $report['name'] ] = array();
				}
			}
		}

		/**
		 * Filter to include or exclude settings item.
		 *
		 * @param array $settings Settings data.
		 * @param bool  $network  Network flag.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_get_settings_with_default', $settings, $network );
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key     Setting key.
	 * @param string $group   Setting group.
	 * @param bool   $network Should check network wide?.
	 * @param mixed  $default Default value.
	 * @param bool   $force   Should force from db?.
	 *
	 * @since  3.2.0
	 *
	 * @return mixed
	 */
	public function get( $key, $group, $network = false, $default = false, $force = false ) {
		// We need key and group.
		if ( empty( $key ) || empty( $group ) ) {
			return $default;
		}

		// Get group values.
		$options = $this->get_options( $group, $network, $force );

		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	/**
	 * Get a setting group values.
	 *
	 * @param bool $group   Setting group.
	 * @param bool $network Should check network wide?.
	 * @param bool $force   Should force from db?.
	 *
	 * @since  3.2.0
	 *
	 * @return mixed
	 */
	public function get_options( $group = false, $network = false, $force = false ) {
		if ( $force ) {
			// Get option from WP.
			$options = $network ? get_site_option( $this->setting_key, array() ) : get_option( $this->setting_key, array() );
		} else {
			// Get from cache.
			$options = $network ? $this->network_settings : $this->settings;
		}

		/**
		 * Filter hook to filter all settings.
		 *
		 * Keeping this for backward compatibility.
		 *
		 * @param array  $settings
		 * @param bool   $network
		 * @param string $setting
		 *
		 * @deprecated 3.2.0
		 */
		$options = apply_filters_deprecated(
			'ga_get_options',
			array( $options, $network, $this->setting_key ),
			'3.2.0',
			'beehive_get_options'
		);

		/**
		 * Filter to modify settings values before returning.
		 *
		 * @paran array $options Option values.
		 *
		 * @param bool $network Network flag.
		 *
		 * @since 1.0.0
		 */
		$options = apply_filters( 'beehive_get_options', $options, $network );

		/**
		 * Action hook to perform after settings are retrieved.
		 *
		 * Keeping this for backward compatibility.
		 *
		 * @param array  $settings
		 * @param bool   $network
		 * @param string $setting
		 *
		 * @deprecated 3.2.0
		 */
		do_action_deprecated(
			'ga_plus_before_return_options',
			array( $options, $network, $this->setting_key ),
			'3.2.0'
		);

		// If group is not given, return all values.
		if ( empty( $group ) ) {
			return $options;
		}

		return isset( $options[ $group ] ) ? $options[ $group ] : array();
	}

	/**
	 * Update a single setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $value   Setting value.
	 * @param string $group   Setting group.
	 * @param bool   $network Should check network wide?.
	 *
	 * @since  3.2.0
	 *
	 * @return bool False if value was not updated. True if value was updated.
	 */
	public function update( $key, $value, $group, $network = false ) {
		// We need all parameters.
		if ( ! isset( $key ) || empty( $group ) ) {
			return false;
		}

		// Get all values first.
		$options = $this->get_options( false, $network, true );

		/**
		 * Filter to modify settings values before updating.
		 *
		 * @paran mixed  $value Option value.
		 * @paran string $key Option key.
		 * @paran string $options Option group.
		 *
		 * @since 3.2.0
		 */
		$value = apply_filters( 'beehive_update_option', $value, $key, $group );

		$options[ $group ][ $key ] = $value;

		return $this->update_options( $options, $network );
	}

	/**
	 * Update a single setting value.
	 *
	 * @param mixed  $values  Setting values.
	 * @param string $group   Setting group.
	 * @param bool   $network Should check network wide?.
	 *
	 * @since  3.2.0
	 *
	 * @return bool False if value was not updated. True if value was updated.
	 */
	public function update_group( $values, $group, $network = false ) {
		// We need all parameters.
		if ( empty( $group ) ) {
			return false;
		}

		// Get all values first.
		$options = $this->get_options( false, $network, true );

		/**
		 * Filter to modify settings values before updating.
		 *
		 * @paran mixed  $value Option value.
		 * @paran string $key Option key.
		 * @paran string $options Option group.
		 *
		 * @since 1.0.0
		 */
		$values = apply_filters( 'beehive_update_option_group', $values, $group );

		$options[ $group ] = $values;

		return $this->update_options( $options, $network );
	}

	/**
	 * Update a setting group value.
	 *
	 * @param array $values  Setting values.
	 * @param bool  $network Should check network wide?.
	 *
	 * @since  3.2.0
	 *
	 * @return bool False if value was not updated. True if value was updated.
	 */
	public function update_options( $values, $network = false ) {
		// We need values.
		if ( empty( $values ) ) {
			return false;
		}

		/**
		 * Filter to modify settings values before updating.
		 *
		 * @paran array $values Option values.
		 *
		 * @since 1.0.0
		 */
		$values = apply_filters( 'beehive_update_options', $values );

		// Get old values.
		$options = $this->get_options( false, $network, true );

		// Format reports.
		$values = $this->format_reports( $values );

		// Return early if no changes.
		if ( $options === $values ) {
			return true;
		}

		// Update options.
		(bool) $network ? update_site_option( $this->setting_key, $values ) : update_option( $this->setting_key, $values );

		// Re-init.
		$this->init( $network );

		/**
		 * Action hook to execute after updating settings.
		 *
		 * @param array $options Old values.
		 * @param array $values  New values.
		 * @param bool  $network Network flag.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_settings_update', $options, $values, $network );

		return true;
	}

	/**
	 * Format the reports settings before saving.
	 *
	 * We need to include parent item if all the children are
	 * selected in settings.
	 *
	 * @param array $values Setting values.
	 *
	 * @since  3.2.4
	 *
	 * @return array
	 */
	public function format_reports( $values ) {
		// We need values.
		if ( empty( $values['reports'] ) ) {
			return $values;
		}

		// Get the report items.
		$report_items = Views\Admin::instance()->report_items();

		// Loop through each roles.
		foreach ( $values['reports'] as $role => $reports ) {
			// Report sections.
			foreach ( $report_items as $type => $data ) {
				// Only if the section is found.
				if ( ! isset( $reports[ $data['name'] ] ) ) {
					continue;
				}

				// Format each group.
				$values['reports'][ $role ][ $type ] = $this->format_report_group(
					$data['name'],
					$data['children'],
					$reports[ $data['name'] ]
				);

				// Only unique items.
				if ( isset( $values['reports'][ $role ][ $type ] ) ) {
					$values['reports'][ $role ][ $type ] = array_unique( $values['reports'][ $role ][ $type ] );
				}
			}
		}

		return $values;
	}

	/**
	 * Format a report group recursively.
	 *
	 * If there is another group of children found, do it
	 * recursively.
	 *
	 * @param string $parent   Parent item key.
	 * @param array  $children Children of the group.
	 * @param array  $items    Report items array.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function format_report_group( $parent, $children, $items ) {
		// Get total children count.
		$children_count = count( $children );

		// Selected items in settings.
		$selected_count = 0;

		foreach ( $children as $child ) {
			// Again children loop.
			if ( ! empty( $child['children'] ) ) {
				$items = $this->format_report_group( $child['name'], $child['children'], $items );
			}

			// Now process the parent.
			if ( isset( $child['name'] ) && in_array( $child['name'], $items, true ) ) {
				// Increase the selected children count.
				$selected_count++;
			}
		}

		// If all children are selected, add parent also.
		if ( $children_count === $selected_count && ! in_array( $parent, $items, true ) ) {
			$items[] = $parent;
		} elseif ( $children_count !== $selected_count && in_array( $parent, $items, true ) ) {
			// Find the index of the item.
			$index = array_search( $parent, $items, true );
			// Delete the parent item.
			if ( false !== $index ) {
				unset( $items[ $index ] );
			}
		}

		return $items;
	}
}