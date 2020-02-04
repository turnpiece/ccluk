<?php

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Analytics\Views\Settings as Analytics_Settings;

/**
 * The settings helper class of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
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
	private $default_settings = [
		'general'      => [
			'track_admin'              => false,
			'anonymize'                => false,
			'force_anonymize'          => false,
			'advertising'              => false,
			'prosites_settings_level'  => [],
			'prosites_analytics_level' => [],

		],
		'tracking'     => [
			'code' => '',
		],
		'permissions'  => [
			'roles'         => [],
			'custom_cap'    => '',
			'overwrite_cap' => false,
		],
		'reports'      => [
			'dashboard'  => [],
			'statistics' => [],
		],
		'google'       => [
			'client_id'     => '',
			'client_secret' => '',
			'api_key'       => '',
			'account_id'    => '',
			'auto_track'    => true,
		],
		'google_login' => [
			'access_token' => '',
			'scopes'       => [],
			'logged_in'    => '',
			'method'       => 'connect', // connect or api.
			'name'         => '',
			'email'        => '',
			'photo'        => '',
		],
		'misc'         => [
			'onboarding_done' => false,
			'auto_track'      => false, // Auto tracking code collected from selected profile.
		],
	];

	/**
	 * Network settings array.
	 *
	 * @var array
	 *
	 * @since 3.2.0
	 */
	private $network_settings = [];

	/**
	 * Single site settings array.
	 *
	 * @var array
	 *
	 * @since 3.2.0
	 */
	private $settings = [];

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

		// Initialize default settings array.
		$this->default_settings( $network );

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
			$options = $network ? get_site_option( $this->setting_key, [] ) : get_option( $this->setting_key, [] );
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
			[ $options, $network, $this->setting_key ],
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
			[ $options, $network, $this->setting_key ],
			'3.2.0'
		);

		// If group is not given, return all values.
		if ( empty( $group ) ) {
			return $options;
		}

		return isset( $options[ $group ] ) ? $options[ $group ] : [];
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

		// Return early if no changes.
		if ( $options === $values ) {
			return true;
		}

		// Update options.
		$network ? update_site_option( $this->setting_key, $values ) : update_option( $this->setting_key, $values );

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


	/********************** Settings Form Handling ***********************/

	/**
	 * Process admin settings form.
	 *
	 * If current request is our form submit, process
	 * the settings after verifying the nonce and capability.
	 *
	 * @note  : Only the available fields will be updated.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function process_settings() {
		// Continue only when our form is submitted.
		if ( ! isset( $_POST['beehive_settings_form'] ) ) {
			return;
		}

		// Form data.
		$form_data = $_POST;

		// Check capability.
		if ( ! Permission::user_can( 'settings', $this->is_network() ) ) {
			return;
		}

		// Verify nonce.
		check_admin_referer( 'beehive_settings_nonce' );

		// Default settings.
		$settings = $this->default_settings( $this->is_network() );

		// Get existing group options.
		$options = $this->get_options( false, $this->is_network(), true );

		// Remove unwanted items.
		unset( $settings['google_login'] );

		// Loop through each items and update.
		foreach ( $settings as $group => $fields ) {
			switch ( $group ) {
				case 'reports':
					$options = $this->process_reports_settings( $form_data, $options );
					break;
				case 'permissions':
					$options = $this->process_permissions_settings( $fields, $form_data, $options );
					break;
				case 'google':
					$options = $this->process_google_settings( $fields, $form_data, $options );
					break;
				default:
					$options = $this->process_other_settings( $group, $fields, $form_data, $options );
					break;
			}
		}

		// Update group settings.
		if ( $this->update_options( $options, $this->is_network() ) ) {
			/**
			 * Action hook to execute after settings form submit processed.
			 *
			 * @note  You will get the updated options if you get the settings on this hook.
			 *
			 * @param array $options Updated options.
			 *
			 * @since 3.2.0
			 */
			do_action( 'beehive_admin_settings_processed', $options );
		}
	}

	/**
	 * Process the reports settings form submit.
	 *
	 * @param array $form_data Form submit data.
	 * @param array $options   Existing values.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function process_reports_settings( $form_data, $options ) {
		// Process only within reports settings tab.
		if ( ! isset( $form_data['beehive_settings_group'] ) || 'reports' !== $form_data['beehive_settings_group'] ) {
			return $options;
		}

		// Get reports settings from form data.
		$form_data = isset( $form_data['reports'] ) ? $form_data['reports'] : [];

		// Get available roles.
		$roles = Permission::get_roles( false );

		// Loop through each items.
		foreach ( $roles as $role => $label ) {
			foreach ( [ 'dashboard', 'statistics' ] as $type ) {
				// Update only if role is valid.
				if ( isset( $form_data[ $type ][ $role ] ) ) {
					$options['reports'][ $type ][ $role ] = $form_data[ $type ][ $role ];
				} elseif ( isset( $options['reports'][ $type ][ $role ] ) ) {
					unset( $options['reports'][ $type ][ $role ] );
				}
			}
		}

		return $options;
	}

	/**
	 * Process the permissions settings form submit.
	 *
	 * @param array $fields    Fields in group.
	 * @param array $form_data Form submit data.
	 * @param array $options   Existing values.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function process_permissions_settings( $fields, $form_data, $options ) {
		// Process only within reports settings tab.
		if ( ! isset( $form_data['beehive_settings_group'] ) || 'permissions' !== $form_data['beehive_settings_group'] ) {
			return $options;
		}

		// Get reports settings from form data.
		$new_roles = isset( $form_data['permissions']['roles'] ) ? $form_data['permissions']['roles'] : [];
		$old_roles = isset( $options['permissions']['roles'] ) ? $options['permissions']['roles'] : [];

		// Stats item tree.
		$dashboard  = Analytics_Settings::instance()->dashboard_tree();
		$statistics = Analytics_Settings::instance()->statistics_tree();

		// Loop through all new roles.
		foreach ( $new_roles as $role ) {
			// No need to process if already there.
			if ( in_array( $role, $old_roles, true ) ) {
				continue;
			}

			// Add each items in dashboard.
			foreach ( $dashboard as $parent => $data ) {
				// Parent item needs to be checked.
				$options['reports']['dashboard'][ $role ][ $parent ] = [];

				if ( ! empty( $data['items'] ) ) {
					foreach ( $data['items'] as $item_key => $label ) {
						$options['reports']['dashboard'][ $role ][ $parent ][ $item_key ] = 1;
					}
				} else {
					// Parent item needs to be checked.
					$options['reports']['dashboard'][ $role ][ $parent ] = 1;
				}
			}

			// Add each items in statistics.
			foreach ( $statistics as $key => $label ) {
				$options['reports']['statistics'][ $role ][ $key ] = 1;
			}
		}

		// Check if all old roles are selected.
		foreach ( $old_roles as $role ) {
			// Remove if a role is unchecked.
			if ( ! in_array( $role, $new_roles, true ) ) {
				unset( $options['reports']['dashboard'][ $role ] );
				unset( $options['reports']['statistics'][ $role ] );
			}
		}

		// Process the settings in normal way.
		$options = $this->process_other_settings( 'permissions', $fields, $form_data, $options );

		return $options;
	}

	/**
	 * Process the google settings form submit.
	 *
	 * @param array $fields    Fields in group.
	 * @param array $form_data Form submit data.
	 * @param array $options   Existing values.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function process_google_settings( $fields, $form_data, $options ) {
		// Process only within reports settings tab.
		if ( isset( $form_data['google'] ) ) {
			// Temporarly set the form group.
			$form_data['beehive_settings_group'] = 'google';

			// Process the settings.
			$options = $this->process_other_settings( 'google', $fields, $form_data, $options, false );
		}

		return $options;
	}

	/**
	 * Process the capability settings form submit.
	 *
	 * @param string $group     Settings group name.
	 * @param array  $fields    Fields in group.
	 * @param array  $form_data Form submit data.
	 * @param array  $options   Existing values.
	 * @param bool   $default   Should assign default value if empty?.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function process_other_settings( $group, $fields, $form_data, $options, $default = true ) {
		// Process only within reports settings tab.
		if ( ! isset( $form_data['beehive_settings_group'] )
		     || $group !== $form_data['beehive_settings_group']
		) {
			return $options;
		}

		if ( is_array( $fields ) ) {
			// Loop through each items and validate.
			foreach ( $fields as $field => $value ) {
				if ( isset( $form_data[ $group ][ $field ] ) ) {
					$new_value = $form_data[ $group ][ $field ];
					// Sanitize the values.
					if ( is_array( $new_value ) ) {
						$options[ $group ][ $field ] = General::sanitize_array( $new_value );
					} else {
						$options[ $group ][ $field ] = sanitize_text_field( $new_value );
					}
				} elseif ( 'auto_track' === $field ) {
					// Auto tracking settings needs to be handled separately.
					$options[ $group ][ $field ] = false;
				} elseif ( $default && 'auto_track' !== $field ) {
					// For all other settings, set default value.
					$options[ $group ][ $field ] = $value;
				}
			}
		}

		return $options;
	}
}