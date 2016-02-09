<?php
/**
 * Helper functions for the admin - help tabs for supported plugins - only if active.
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
 * Add optional help tab content for supported plugins.
 *
 * @since 1.3.0
 *
 * @uses  ddw_gle_help_content_sub_head()
 * @uses  post_type_exists()
 */
function ddw_gle_admin_help_plugins() {

	ddw_gle_help_content_sub_head( __( 'Custom Post Types by Plugins', 'genesis-layout-extras' ) );

		/** Plugin: AgentPress Listings */
		if ( post_type_exists( 'listing' ) ) {

			echo '<h4>AgentPress Listings</h4>' .
				'<ul>' .
					'<li>' . __( 'Listing Post Type Layout (archive)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Listings Features Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
				'</ul>';

		}  // end-if APL check

		/** Plugin: Genesis Media Project */
		if ( post_type_exists( 'video' ) ) {

			echo '<h4>' . __( 'Genesis Media Project', 'genesis-layout-extras' ) . '</h4>' .
				'<ul>' .
					'<li>' . __( 'Video Post Type Layout (archive)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Video SlideShows Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Video Categories Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Video Tags Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
				'</ul>';

		}  // end-if GMP check

		/** Plugins: WooCommerce or Jigoshop */
		if ( post_type_exists( 'product' ) ) {

			echo '<h4>' . __( 'WooCommerce OR Jigoshop', 'genesis-layout-extras' ) . '</h4>' .
				'<ul>' .
					'<li>' . __( 'Product Post Type Layout - Product Categories (all)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Product Post Type Layout - Product Tags (all)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . sprintf( __( '%s Genesis Integration Plugin', 'genesis-layout-extras' ), '<em>WooCommerce</em>' ) . ': <a href="http://wordpress.org/plugins/genesis-connect-woocommerce/" target="_new">Genesis Connect for WooCommerce</a> <small><em>(' . __( 'required', 'genesis-layout-extras' ) . ')</em></small></li>' .
					'<li>' . sprintf( __( '%s Genesis Integration Plugin', 'genesis-layout-extras' ), '<em>Jigoshop</em>' ) . ': <a href="http://jigoshop.com/product/genesis-connect-for-jigoshop/" target="_new">Genesis Connect for Jigoshop</a> <small><em>(' . __( 'required', 'genesis-layout-extras' ) . ')</em></small></li>' .
				'</ul>';

		}  // end-if CPT "product" check

		/** Plugin: Easy Digital Downloads */
		if ( post_type_exists( 'download' ) ) {

			echo '<h4>' . __( 'Easy Digital Downloads', 'genesis-layout-extras' ) . '</h4>' .
				'<ul>' .
					'<li>' . __( 'Download Post Type Layout (archive)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Download Categories Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Download Tags Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . sprintf( __( '%s Genesis Integration Plugin', 'genesis-layout-extras' ), '<em>Easy Digital Downloads</em>' ) . ': <a href="http://wordpress.org/plugins/genesis-connect-woocommerce/" target="_new">Genesis Connect for Easy Digital Downloads</a> <small><em>(' . __( 'recommended', 'genesis-layout-extras' ) . ')</em></small></li>' .
				'</ul>';

		}  // end-if EDD check

		/** Plugin: Sugar Events Calendar */
		if ( post_type_exists( 'sc_event' ) ) {

			echo '<h4>' . __( 'Sugar Events Calendar', 'genesis-layout-extras' ) . '</h4>' .
				'<ul>' .
					'<li>' . __( 'Event Post Type Layout (archive)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Event Categories Taxonomy Layout', 'genesis-layout-extras' ) . '</li>' .
				'</ul>';

		}  // end-if Sugar Events check

		/** Plugin: bbPress 2.x Forum */
		if ( class_exists( 'bbPress' ) ) {

			echo '<h4>' . __( 'bbPress 2.x', 'genesis-layout-extras' ) . '</h4>' .
				'<ul>' .
					'<li>' . __( 'bbPress 2.x Forum Layout (all areas)', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . __( 'Including extra setting for singular topics view', 'genesis-layout-extras' ) . '</li>' .
					'<li>' . sprintf( __( '%s Genesis Integration Plugin', 'genesis-layout-extras' ), '<em>bbPress</em>' ) . ': <a href="http://wordpress.org/plugins/bbpress-genesis-extend/" target="_new">bbPress Genesis Extend</a> <small><em>(' . __( 'required', 'genesis-layout-extras' ) . ')</em></small></li>' .
				'</ul>';

		}  // end-if bbPress 2.x check

}  // end of function ddw_gle_admin_help_plugins