<?php
/**
 * Helper functions for the admin - help tabs for supported StudioPress child themes - only if active.
 *
 * @package    Genesis Layout Extras
 * @subpackage Admin Help
 * @author     David Decker - DECKERWEB
 * @copyright  Copyright (c) 2011-2013, David Decker - DECKERWEB
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://genesisthemes.de/en/wp-plugins/genesis-layout-extras/
 * @link       http://deckerweb.de/twitter
 *
 * @since      1.6.0
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
 * Add optional help tab content for supported child themes by StudioPress.
 *
 * @since 1.6.0
 *
 * @uses  ddw_gle_help_content_sub_head()
 * @uses  post_type_exists()
 */
function ddw_gle_admin_help_studiopress() {

	ddw_gle_help_content_sub_head( __( 'Custom Post Types by Child Themes', 'genesis-layout-extras' ) . ' &mdash; ' . __( 'by StudioPress', 'genesis-layout-extras' ) );
	
		/** Child Themes by StudioPress: Minimum 2.0 / Executive 2.0 */
		if ( post_type_exists( 'portfolio' ) ) {

			if ( function_exists( 'minimum_portfolio_post_type' ) ) {
				$gle_sp_theme_check = 'Minimum 2.0';
			} elseif ( function_exists( 'executive_portfolio_post_type' ) ) {
				$gle_sp_theme_check = 'Executive 2.0';
			}

			echo '<p>' . sprintf(
					__( 'Child Theme: %s by StudioPress', 'genesis-layout-extras' ),
					$gle_sp_theme_check
				) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Portfolio Post Type Layout (archive)', 'genesis-layout-extras' ) . '</li>' .
				'</ul>';

		}  // end-if StudioPress check

}  // end of function ddw_gle_admin_help_studiopress