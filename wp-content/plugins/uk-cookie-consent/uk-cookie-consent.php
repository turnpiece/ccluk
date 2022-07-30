<?php
/**
 * Plugin Name: Termly | GDPR/CCPA Cookie Consent Banner
 * Plugin URI: https://termly.io/products/
 * Description: Our easy to use cookie consent plugin can assist in your GDPR and ePrivacy Directive compliance efforts.
 * Version: 3.0.3
 * Author: Termly
 * Author URI: https://termly.io/
 * License: GPL2
 * Text Domain: uk-cookie-consent
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package WordPress
 */

// Only proceed if this file is being loaded through WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'This file must not be called directly.' );
}

// Check for minimum PHP Version.
if ( ! function_exists( 'is_php_version_compatible' ) || ! is_php_version_compatible( '5.3.0' ) ) {

	add_action(
		'admin_notices',
		function() {

			$class   = 'notice notice-error';
			$message = __( 'Your site does not meet the minimum PHP version to run this plugin.', 'uk-cookie-consent' );
			echo sprintf(
				'<div class="%s"><p>%s</p></div>',
				esc_attr( $class ),
				esc_html( $message )
			);

		}
	);
	return false;

}

// Check for minimum WordPress Version.
if ( ! function_exists( 'is_wp_version_compatible' ) || ! is_wp_version_compatible( '5.2.0' ) ) {

	add_action(
		'admin_notices',
		function() {

			$class   = 'notice notice-error';
			$message = __( 'Your site does not meet the minimum WordPress version to run this plugin.', 'uk-cookie-consent' );
			echo sprintf(
				'<div class="%s"><p>%s</p></div>',
				esc_attr( $class ),
				esc_html( $message )
			);

		}
	);
	return false;

}

// Constants.
define( 'TERMLY_FILE', __FILE__ );
define( 'TERMLY_BASENAME', plugin_basename( __FILE__ ) );
define( 'TERMLY_API_BASE', 'https://app.termly.io/api' );
define( 'TERMLY_VERSION', '3.0.3' );
define( 'TERMLY_URL', plugin_dir_url( __FILE__ ) );
define( 'TERMLY_PATH', plugin_dir_path( __FILE__ ) );
define( 'TERMLY_LANG', TERMLY_PATH . 'lang/' );
define( 'TERMLY_INC', TERMLY_PATH . 'includes/' );
define( 'TERMLY_MODELS', TERMLY_INC . 'models/' );
define( 'TERMLY_VIEWS', TERMLY_INC . 'views/' );
define( 'TERMLY_CONTROLLERS', TERMLY_INC . 'controllers/' );
define( 'TERMLY_HELPERS', TERMLY_INC . 'helpers/' );
define( 'TERMLY_DIST', TERMLY_URL . 'dist/' );

// Common Files.
require_once TERMLY_HELPERS . 'class-url-helpers.php';
require_once TERMLY_INC . 'class-internationalization.php';
require_once TERMLY_MODELS . 'class-general-settings-model.php';
require_once TERMLY_CONTROLLERS . 'class-menu-controller.php';
require_once TERMLY_CONTROLLERS . 'class-robots-txt.php';
require_once TERMLY_MODELS . 'class-termly-api-model.php';
require_once TERMLY_CONTROLLERS . 'class-termly-api-controller.php';

/**
 * Checks the readme file on the .org repository for an update message.
 *
 * @param array  $args An array of plugin metadata.
 * @param object $response An array of metadata about the available plugin update.
 * @return void
 */
function ctcc_plugin_update_message( $args, $response ) {

	if ( isset( $args['update'] ) && $args['update'] ) {

		$transient_name = 'ctcc_upgrade_notice_' . $response->new_version;
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {

			$readme = wp_safe_remote_get(
				'https://plugins.svn.wordpress.org/uk-cookie-consent/trunk/readme.txt'
			);

			if ( ! is_wp_error( $readme ) && ! empty( $readme['body'] ) ) {

				$version_parts     = explode( '.', $response->new_version );
				$check_for_notices = array(
					$version_parts[0] . '.0', // Major.
					$version_parts[0] . '.0.0', // Major.
					$version_parts[0] . '.' . $version_parts[1], // Minor.
					$version_parts[0] . '.' . $version_parts[1] . '.' . $version_parts[2], // Patch.
				);
				$notice_regexp     = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $response->new_version ) . '\s*=|$)~Uis';
				$upgrade_notice    = '';

				foreach ( $check_for_notices as $check_version ) {
					if ( version_compare( $args['Version'], $check_version, '>' ) ) {
						continue;
					}

					$matches = null;
					if ( preg_match( $notice_regexp, $readme['body'], $matches ) ) {
						$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

						if ( version_compare( trim( $matches[1] ), $check_version, '=' ) ) {
							$upgrade_notice .= '<p class="ctcc_plugin_upgrade_notice">';

							$upgrade_notice .= sprintf(
								'<strong>Version %s</strong>:<br />',
								esc_html( trim( $matches[1] ) )
							);

							foreach ( $notices as $index => $line ) {
								$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
							}

							$upgrade_notice .= '</p>';
						}
						break;
					}
				}

				set_transient( $transient_name, wp_kses_post( $upgrade_notice ), DAY_IN_SECONDS );

			}

		}

		echo wp_kses_post( rtrim( $upgrade_notice, '</p>' ) );
	}
}
add_action( 'in_plugin_update_message-uk-cookie-consent/uk-cookie-consent.php', 'ctcc_plugin_update_message', 10, 2 );


$termly_api_key = get_option( 'termly_api_key', false );
if ( false === $termly_api_key || empty( $termly_api_key ) ) {

	// Sign Up Page.
	require_once TERMLY_CONTROLLERS . 'class-sign-up-controller.php';

} else {

	// Frontend.
	require_once TERMLY_CONTROLLERS . 'class-frontend.php';

	// Internal API.
	require_once TERMLY_CONTROLLERS . 'class-account-api-controller.php';

	// Menus.
	require_once TERMLY_CONTROLLERS . 'class-app-controller.php';
	require_once TERMLY_MODELS . 'class-site-scan-model.php';
	require_once TERMLY_CONTROLLERS . 'class-site-scan-controller.php';
	require_once TERMLY_CONTROLLERS . 'class-cookie-management-controller.php';
	require_once TERMLY_CONTROLLERS . 'class-edit-cookie.php';
	require_once TERMLY_CONTROLLERS . 'class-banner-settings-controller.php';
	require_once TERMLY_CONTROLLERS . 'class-policies-controller.php';

}
