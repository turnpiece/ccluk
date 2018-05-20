<?php

/**
 * A class for modifying the default admin columns behavior.
 *
 * This class will add the the Social Shares column to the list
 * of posts and pages in the WordPress admin panel.
 *
 * @package   Social-Warfare\Functions\Admin
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 * @since     3.0.0 | 21 FEB 2018 | Refactored into a class-based system.
 *
 */
class SWP_Column {


	/**
	 * The magic __construct method used to instatiate our class. This method
	 * will queue up all the other methods by adding them to the necessary
	 * WordPress action and filter hooks.
	 *
	 * NOTE: These "duplicate" hooks/functions are to cover both posts and pages.
	 *
	 * @since  3.0.0
	 * @access public
	 * @param  None
	 * @return None
	 *
	 */
    public function __construct() {

		// Create the social shares column
        add_filter( 'manage_post_posts_columns', array($this, 'create_social_shares_column' ) );
        add_filter( 'manage_page_posts_columns', array($this, 'create_social_shares_column' ) );

		// Populate the social shares column with data
        add_action( 'manage_posts_custom_column', array( $this, 'populate_social_shares_column' ), 10, 2 );
    	add_action( 'manage_page_posts_custom_column', array( $this, 'populate_social_shares_column' ), 10, 2 );

		// Make the social shares column sortable
    	add_filter( 'manage_edit-post_sortable_columns', array($this, 'make_social_shares_sortable' ) );
    	add_filter( 'manage_edit-page_sortable_columns', array($this, 'make_social_shares_sortable' ) );

		// Sort the output of the posts according to the sortable option created above
        add_action( 'pre_get_posts', array( $this, 'swp_social_shares_orderby' ) );
    }


	/**
	 * Add a share counts column to the post listing admin pages; make it Sortable.
	 *
	 * @since  1.4.0
	 * @param  Array The default columns registered with WordPress.
	 * @return Array The array modified with our new column.
	 *
	 */
	public function create_social_shares_column( $defaults ) {
		$defaults['swSocialShares'] = 'Social Shares';
		return $defaults;
	}


	/**
	 * Populate the new column with the share count from the meta field
	 *
	 * @since  1.4.0
	 * @param  String The name of the column to be modified.
	 * @param  Int The Post ID
	 * @return None The number is echoed to the screen.
	 *
	 */
	public function populate_social_shares_column( $column_name, $post_ID ) {
	 	if ( $column_name == 'swSocialShares' ) {
	 		$answer = get_post_meta( $post_ID,'_total_shares',true );
	 		echo intval( $answer );
		}
	}


	/**
	 * Make the column sortable
	 *
	 * @since  1.4.0
	 * @param  Array The array of registered columns.
	 * @return Array The array modified columns.
	 *
	 */
    public function make_social_shares_sortable( $columns ) {
    	$columns['swSocialShares'] = 'Social Shares';
    	return $columns;
    }


    /**
    * Sort the column by share count.
    *
    * @since 1.4.0
    * @param Object $query The WordPress query object.
    */
	public function swp_social_shares_orderby( $query ) {
		if ( ! is_admin() ) {
	 		return;
	 	}
	 	$orderby = $query->get( 'orderby' );

		if ( 'Social Shares' === $orderby ) {
	 		$query->set( 'meta_key','_total_shares' );
	 		$query->set( 'orderby','meta_value_num' );
	 	}
	}
}
