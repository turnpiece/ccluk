<?php
/**
 * Helper functions for the admin - help tabs for supported Themedy child themes - only if active.
 *
 * @package    Genesis Layout Extras
 * @subpackage Admin Help
 * @author     David Decker - DECKERWEB
 * @copyright  Copyright (c) 2011-2013, David Decker - DECKERWEB
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://genesisthemes.de/en/wp-plugins/genesis-layout-extras/
 * @link       http://deckerweb.de/twitter
 *
 * @since      1.3.0
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
 * Add optional help tab content for supported child themes by Themedy.
 *
 * @since 1.3.0
 *
 * @uses  ddw_gle_plugin_get_data()
 * @uses  post_type_exists()
 */
function ddw_gle_admin_help_themedy() {

	echo '<h3>' . __( 'Plugin: Genesis Layout Extras', 'genesis-layout-extras' ) . ' <small>v' . esc_attr( ddw_gle_plugin_get_data( 'Version' ) ) . '</small></h3>';

		echo '<h4>' . __( 'Custom Post Types by Child Themes', 'genesis-layout-extras' ) . ' &mdash; ' . __( 'by Themedy Themes Brand', 'genesis-layout-extras' ) . '</h4>';

		/** Child Theme: Clip Cart - by Themedy */
		if ( post_type_exists( 'products' ) ) {

			echo '<p>' . __( 'Child Theme: Clip Cart by Themedy Themes', 'genesis-layout-extras' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Products Post Type Layout (archive)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Product Categories Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
				'</ul>';

		}  // end-if Clip Cart check

		/** Child Theme: Stage - by Themedy */
		if ( post_type_exists( 'photo' ) ) {

			echo '<p>' . __( 'Child Theme: Stage by Themedy Themes', 'genesis-layout-extras' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Photo Galleries Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
				'</ul>';

		}  // end-if Stage check

}  // end of function ddw_gle_admin_help_themedy