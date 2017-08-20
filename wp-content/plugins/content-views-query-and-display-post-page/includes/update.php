<?php
/**
 * Check update, do update
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

// Compare stored version and current version
$stored_version = get_option( PT_CV_OPTION_VERSION );
if ( $stored_version ) {
	if ( version_compare( $stored_version, PT_CV_VERSION, '<' ) ) {
		update_option( PT_CV_OPTION_VERSION, PT_CV_VERSION );
	}

	// Delete transients
	if ( version_compare( $stored_version, '1.9.9', '<' ) ) {
		if ( !(defined( 'DOING_AJAX' ) && DOING_AJAX) ) {
			global $wpdb;
			$sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_pt-cv-%' OR option_name LIKE '_transient_pt-cv-%'";
			$wpdb->query( $sql );
		}
	}

	// Delete view_count post meta
	if ( version_compare( $stored_version, '1.8.8.0', '<=' ) ) {
		if ( !get_option( 'pt_cv_version_pro' ) ) {
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM $wpdb->postmeta WHERE meta_key = %s", '_' . PT_CV_PREFIX_ . 'view_count'
				)
			);
		}
	}
}