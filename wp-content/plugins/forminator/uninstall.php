<?php
/**
 * Forminator Uninstall methods
 * Called when plugin is deleted
 *
 * @since 1.0.2
 */

// if uninstall.php is not called by WordPress, die
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/**
 * Drop custom tables
 *
 * @since 1.0.2
 */
function forminator_drop_custom_tables() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}frmt_form_entry" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}frmt_form_entry_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}frmt_form_views" );
}

/**
 * Clear custom posts
 *
 * @since 1.0.2
 */
function forminator_delete_custom_posts() {
	global $wpdb;
	//Now we delete the custom posts
	$forms_sql 			= "SELECT GROUP_CONCAT(`ID`) FROM {$wpdb->posts} WHERE `post_type` = %s";
	$delete_forms_sql 	= "DELETE FROM {$wpdb->posts} WHERE `post_type` = %s";
	$form_types 		= array(
		'forminator_forms' , 'forminator_polls', 'forminator_quizzes'
	);
	foreach ( $form_types as $type ) {
		$ids = $wpdb->get_var( $wpdb->prepare( $forms_sql, $type ) );
		if ( $ids ) {
			$delete_form_meta_sql 	= "DELETE FROM {$wpdb->postmeta} WHERE `post_id` in($ids)";
			$wpdb->query( $delete_form_meta_sql );
		}
		$wpdb->query( $wpdb->prepare( $delete_forms_sql, $type ) );
	}
}


/**
 * Delete custom options
 *
 * @since 1.0.2
 */
function forminator_delete_custom_options() {
	delete_site_option( "forminator_pagination_listings" );
	delete_site_option( "forminator_pagination_entries" );
	delete_site_option( "forminator_captcha_key" );
	delete_site_option( "forminator_captcha_secret" );
	delete_site_option( "forminator_captcha_language" );
	delete_site_option( "forminator_captcha_theme" );
	delete_site_option( "forminator_welcome_dismissed" );
	delete_site_option( "forminator_version" );
}

$forminator_uninstall = get_option( "forminator_uninstall_clear_data", false );
if ( $forminator_uninstall ) {
	forminator_drop_custom_tables();
	forminator_delete_custom_posts();
	forminator_delete_custom_options();
}
?>