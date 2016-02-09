<?php
/**
 * Helper functions for the admin - plugin links.
 *
 * @package    Genesis Layout Extras
 * @subpackage Admin
 * @author     David Decker - DECKERWEB
 * @copyright  Copyright (c) 2011-2013, David Decker - DECKERWEB
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://genesisthemes.de/en/wp-plugins/genesis-layout-extras/
 * @link       http://deckerweb.de/twitter
 *
 * @since      1.0.0
 */

/**
 * Prevent direct access to this file.
 *
 * @since 1.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Sorry, you are not allowed to access this file directly.' );
}


/**
 * Setting internal plugin helper links constants
 *
 * @since 1.4.0
 *
 * @uses  get_locale()
 */
define( 'GLE_URL_TRANSLATE',		'http://translate.wpautobahn.com/projects/genesis-plugins-deckerweb/genesis-layout-extras' );
define( 'GLE_URL_WPORG_PLUGIN', 	'http://wordpress.org/plugins/genesis-layout-extras/' );
define( 'GLE_URL_WPORG_FAQ',		'http://wordpress.org/plugins/genesis-layout-extras/faq/' );
define( 'GLE_URL_WPORG_FORUM',		'http://wordpress.org/support/plugin/genesis-layout-extras' );
define( 'GLE_URL_WPORG_DDW', 		'http://wordpress.org/plugins/tags/deckerweb' );
define( 'GLE_URL_WPORG_PROFILE',	'http://profiles.wordpress.org/daveshine/' );
define( 'GLE_URL_SUPPORT',         	esc_url( GLE_URL_WPORG_FORUM ) );
define( 'GLE_URL_SNIPPETS',			'https://gist.github.com/deckerweb/6151740' );
define( 'GLE_PLUGIN_LICENSE', 		'GPL-2.0+' );
if ( get_locale() == 'de_DE' || get_locale() == 'de_AT' || get_locale() == 'de_CH' || get_locale() == 'de_LU' ) {
	define( 'GLE_URL_DONATE', 		'http://genesisthemes.de/spenden/' );
	define( 'GLE_URL_PLUGIN', 		'http://genesisthemes.de/plugins/genesis-layout-extras/' );
} else {
	define( 'GLE_URL_DONATE', 		'http://genesisthemes.de/en/donate/' );
	define( 'GLE_URL_PLUGIN', 		'http://genesisthemes.de/en/wp-plugins/genesis-layout-extras/' );
}


/**
 * Add "Settings" link to plugin page
 *
 * @since  1.0.0
 *
 * @param  $gle_links
 * @param  $gle_settings_link
 *
 * @return strings Admin settings page link.
 */
function ddw_gle_settings_page_link( $gle_links ) {

	/** Plugin setting page link */
	$gle_settings_link = sprintf(
		'<a href="%s" title="%s">%s</a>',
		admin_url( 'admin.php?page=gle-layout-extras' ),
		__( 'Go to the settings page', 'genesis-layout-extras' ),
		__( 'Settings', 'genesis-layout-extras' )
	);
	
	/** Set the order of the links */
	array_unshift( $gle_links, $gle_settings_link );

	/** Display plugin settings links */
	return apply_filters( 'gle_filter_settings_page_link', $gle_links );

}  // end of function ddw_gle_settings_page_link


add_filter( 'plugin_row_meta', 'ddw_gle_plugin_links', 10, 2 );
/**
 * Add various support links to plugin page
 *
 * @since  1.1.0
 *
 * @param  $gle_links
 * @param  $gle_file
 *
 * @return strings plugin links
 */
function ddw_gle_plugin_links( $gle_links, $gle_file ) {

	/** Capability Check */
	if ( ! current_user_can( 'install_plugins' ) ) {

		return $gle_links;

	}  // end-if cap check

	/** Add additional plugin links */
	if ( $gle_file == GLE_PLUGIN_BASEDIR . '/genesis-layout-extras.php' ) {

		$gle_links[] = '<a href="' . esc_url( GLE_URL_WPORG_FAQ ) . '" target="_new" title="' . __( 'FAQ', 'genesis-layout-extras' ) . '">' . __( 'FAQ', 'genesis-layout-extras' ) . '</a>';

		$gle_links[] = '<a href="' . esc_url( GLE_URL_WPORG_FORUM ) . '" target="_new" title="' . __( 'Support', 'genesis-layout-extras' ) . '">' . __( 'Support', 'genesis-layout-extras' ) . '</a>';

		$gle_links[] = '<a href="' . esc_url( GLE_URL_SNIPPETS ) . '" target="_new" title="' . __( 'Code Snippets for Customization', 'genesis-layout-extras' ) . '">' . __( 'Code Snippets', 'genesis-layout-extras' ) . '</a>';

		$gle_links[] = '<a href="' . esc_url( GLE_URL_TRANSLATE ) . '" target="_new" title="' . __( 'Translations', 'genesis-layout-extras' ) . '">' . __( 'Translations', 'genesis-layout-extras' ) . '</a>';

		$gle_links[] = '<a href="' . esc_url( GLE_URL_DONATE ) . '" target="_new" title="' . __( 'Donate', 'genesis-layout-extras' ) . '">' . __( 'Donate', 'genesis-layout-extras' ) . '</a>';

	}  // end-if plugin links

	/** Output the links */
	return apply_filters( 'gle_filter_plugin_links', $gle_links );

}  // end of function ddw_gle_plugin_links