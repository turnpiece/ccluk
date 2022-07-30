<?php
/**
 * The Google Tag Manager class.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */

namespace Beehive\Core\Modules\Google_Tag_Manager;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Tag_Manager
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */
class Tag_Manager extends Base {

	/**
	 * Register all the hooks related to module.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function init() {
		// Add Google Tag Manager settings.
		add_filter( 'beehive_default_settings', array( $this, 'setup_settings' ) );
		add_filter( 'beehive_default_network_settings', array( $this, 'setup_settings' ) );

		// Format GTM settings.
		add_filter( 'beehive_get_settings_with_default', array( $this, 'format_settings_get' ), 10, 2 );
		add_filter( 'beehive_update_options', array( $this, 'format_settings_save' ) );

		// Setup classes.
		Admin::instance()->init();
		Frontend::instance()->init();

		// View classes.
		Views\Vars::instance()->init();
		Views\Scripts::instance()->init();

		// Integrations.
		Integrations\Hustle::instance()->init();
		Integrations\Forminator::instance()->init();
	}

	/**
	 * Register Google Tage Manager to settings field list.
	 *
	 * These are the default settings for the GTM.
	 *
	 * @param array $settings Available settings.
	 *
	 * @since 3.3.0
	 *
	 * @return array $settings
	 */
	public function setup_settings( $settings ) {
		// Add Google Tag Manager settings.
		$settings['gtm'] = array(
			'active'    => false,
			'container' => '',
			'enabled'   => array(),
			'variables' => array(
				'post_id'          => 'post_id',
				'post_title'       => 'post_title',
				'post_type'        => 'post_type',
				'post_date'        => 'post_date',
				'post_author'      => 'post_author',
				'post_author_name' => 'post_author_name',
				'post_categories'  => 'post_categories',
				'post_tags'        => 'post_tags',
			),
			'visitors'  => array(
				'login_status'       => 'logged_in_status',
				'user_role'          => 'logged_in_user_role',
				'user_id'            => 'logged_in_user_id',
				'user_name'          => 'logged_in_user_name',
				'user_email'         => 'logged_in_user_email',
				'user_creation_date' => 'logged_in_user_creation_date',
			),
			'custom'    => array(),
		);

		return $settings;
	}

	/**
	 * Format the GTM settings values after getting from db.
	 *
	 * By default settings formatting may exclude sub arrays from the
	 * data. We need to make sure the GTM settings are safe.
	 *
	 * @param array $settings Available settings.
	 * @param bool  $network  Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array $settings
	 */
	public function format_settings_get( $settings, $network ) {
		// Default values.
		$default = beehive_analytics()->settings->default_settings( $network );

		// Set gtm settings.
		$options = beehive_analytics()->settings->get_options( 'gtm', $network );

		// Merge with default options if empty.
		foreach ( $default['gtm'] as $key => $value ) {
			// If the item is an array.
			if ( is_array( $value ) ) {
				if ( 'custom' === $key || 'enabled' === $key ) {
					// Custom doesn't require any formatting.
					$options[ $key ] = empty( $options[ $key ] ) ? array() : $options[ $key ];
				} elseif ( empty( $value ) ) {
					// Include empty arrays too.
					$options[ $key ] = $value;
				} else {
					// Loop and include all sub items.
					foreach ( $value as $sub_key => $sub_value ) {
						// Replace only if the original one is not set.
						if ( ! isset( $options[ $key ][ $sub_key ] ) ) {
							$options[ $key ][ $sub_key ] = $sub_value;
						}
					}
				}
			} elseif ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		// Replace GTM settings.
		$settings['gtm'] = $options;

		return $settings;
	}

	/**
	 * Format the GTM settings before save.
	 *
	 * Remove any empty custom variable from the list.
	 *
	 * @param array $values Available settings.
	 *
	 * @since 3.3.0
	 *
	 * @return array $values
	 */
	public function format_settings_save( $values ) {
		// We need values.
		if ( empty( $values['gtm']['custom'] ) ) {
			return $values;
		}

		// Loop through each roles.
		foreach ( $values['gtm']['custom'] as $index => $item ) {
			// Both name and value should not be empty.
			if ( empty( $item['name'] ) || empty( $item['value'] ) ) {
				unset( $values['gtm']['custom'][ $index ] );
			} else {
				// Make sure custom variable name should not have space.
				$values['gtm']['custom'][ $index ]['name'] = str_replace( ' ', '', $item['name'] );
			}
		}

		return $values;
	}
}