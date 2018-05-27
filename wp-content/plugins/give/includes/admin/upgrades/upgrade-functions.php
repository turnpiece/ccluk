<?php
/**
 * Upgrade Functions
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
 * /includes/install.php @ Appox Line 156
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perform automatic database upgrades when necessary.
 *
 * @since  1.6
 * @return void
 */
function give_do_automatic_upgrades() {
	$did_upgrade  = false;
	$give_version = preg_replace( '/[^0-9.].*/', '', get_option( 'give_version' ) );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
	}

	switch ( true ) {

		case version_compare( $give_version, '1.6', '<' ) :
			give_v16_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.7', '<' ) :
			give_v17_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8', '<' ) :
			give_v18_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.7', '<' ) :
			give_v187_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.8', '<' ) :
			give_v188_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.9', '<' ) :
			give_v189_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.12', '<' ) :
			give_v1812_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.13', '<' ) :
			give_v1813_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.17', '<' ) :
			give_v1817_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.18', '<' ) :
			give_v1818_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '2.0', '<' ) :
			give_v20_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '2.0.1', '<' ) :
			// Do nothing on fresh install.
			if ( ! doing_action( 'give_upgrades' ) ) {
				give_v201_create_tables();
				Give_Updates::get_instance()->__health_background_update( Give_Updates::get_instance() );
				Give_Updates::$background_updater->dispatch();
			}

			$did_upgrade = true;

		case version_compare( $give_version, '2.0.2', '<' ) :
			// Remove 2.0.1 update to rerun on 2.0.2
			$completed_upgrades = give_get_completed_upgrades();
			$v201_updates       = array(
				'v201_upgrades_payment_metadata',
				'v201_add_missing_donors',
				'v201_move_metadata_into_new_table',
				'v201_logs_upgrades',
			);

			foreach ( $v201_updates as $v201_update ) {
				if ( in_array( $v201_update, $completed_upgrades ) ) {
					unset( $completed_upgrades[ array_search( $v201_update, $completed_upgrades ) ] );
				}
			}

			update_option( 'give_completed_upgrades', $completed_upgrades );

			// Do nothing on fresh install.
			if ( ! doing_action( 'give_upgrades' ) ) {
				give_v201_create_tables();
				Give_Updates::get_instance()->__health_background_update( Give_Updates::get_instance() );
				Give_Updates::$background_updater->dispatch();
			}

			$did_upgrade = true;

		case version_compare( $give_version, '2.0.3', '<' ) :
			give_v203_upgrades();
			$did_upgrade = true;
	}

	if ( $did_upgrade ) {
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	}
}

add_action( 'admin_init', 'give_do_automatic_upgrades' );
add_action( 'give_upgrades', 'give_do_automatic_upgrades' );

/**
 * Display Upgrade Notices.
 *
 * IMPORTANT: ALSO UPDATE INSTALL.PHP WITH THE ID OF THE UPGRADE ROUTINE SO IT DOES NOT AFFECT NEW INSTALLS.
 *
 * @since 1.0
 * @since 1.8.12 Update new update process code.
 *
 * @param Give_Updates $give_updates
 *
 * @return void
 */
function give_show_upgrade_notices( $give_updates ) {
	// v1.3.2 Upgrades
	$give_updates->register(
		array(
			'id'       => 'upgrade_give_payment_customer_id',
			'version'  => '1.3.2',
			'callback' => 'give_v132_upgrade_give_payment_customer_id',
		)
	);

	// v1.3.4 Upgrades ensure the user has gone through 1.3.4.
	$give_updates->register(
		array(
			'id'       => 'upgrade_give_offline_status',
			'depend'   => 'upgrade_give_payment_customer_id',
			'version'  => '1.3.4',
			'callback' => 'give_v134_upgrade_give_offline_status',
		)
	);

	// v1.8 form metadata upgrades.
	$give_updates->register(
		array(
			'id'       => 'v18_upgrades_form_metadata',
			'version'  => '1.8',
			'callback' => 'give_v18_upgrades_form_metadata',
		)
	);

	// v1.8.9 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v189_upgrades_levels_post_meta',
			'version'  => '1.8.9',
			'callback' => 'give_v189_upgrades_levels_post_meta_callback',
		)
	);

	// v1.8.12 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v1812_update_amount_values',
			'version'  => '1.8.12',
			'callback' => 'give_v1812_update_amount_values_callback',
		)
	);

	// v1.8.12 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v1812_update_donor_purchase_values',
			'version'  => '1.8.12',
			'callback' => 'give_v1812_update_donor_purchase_value_callback',
		)
	);

	// v1.8.13 Upgrades for donor
	$give_updates->register(
		array(
			'id'       => 'v1813_update_donor_user_roles',
			'version'  => '1.8.13',
			'callback' => 'give_v1813_update_donor_user_roles_callback',
		)
	);

	// v1.8.17 Upgrades for donations.
	$give_updates->register( array(
		'id'       => 'v1817_update_donation_iranian_currency_code',
		'version'  => '1.8.17',
		'callback' => 'give_v1817_update_donation_iranian_currency_code',
	) );

	// v1.8.17 Upgrades for cleanup of user roles.
	$give_updates->register( array(
		'id'       => 'v1817_cleanup_user_roles',
		'version'  => '1.8.17',
		'callback' => 'give_v1817_cleanup_user_roles',
	) );

	// v1.8.18 Upgrades for assigning custom amount to existing set donations.
	$give_updates->register( array(
		'id'       => 'v1818_assign_custom_amount_set_donation',
		'version'  => '1.8.18',
		'callback' => 'give_v1818_assign_custom_amount_set_donation',
	) );

	// v1.8.18 Cleanup the Give Worker Role Caps.
	$give_updates->register( array(
		'id'       => 'v1818_give_worker_role_cleanup',
		'version'  => '1.8.18',
		'callback' => 'give_v1818_give_worker_role_cleanup',
	) );

	// v2.0.0 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_upgrades_form_metadata',
			'version'  => '2.0.0',
			'callback' => 'give_v20_upgrades_form_metadata_callback',
		)
	);

	// v2.0.0 User Address Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_upgrades_user_address',
			'version'  => '2.0.0',
			'callback' => 'give_v20_upgrades_user_address',
		)
	);

	// v2.0.0 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_upgrades_payment_metadata',
			'version'  => '2.0.0',
			'callback' => 'give_v20_upgrades_payment_metadata_callback',
		)
	);

	// v2.0.0 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_logs_upgrades',
			'version'  => '2.0.0',
			'callback' => 'give_v20_logs_upgrades_callback',

		)
	);

	// v2.0.0 Donor Name Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_upgrades_donor_name',
			'version'  => '2.0.0',
			'callback' => 'give_v20_upgrades_donor_name',
		)
	);

	// v2.0.0 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_move_metadata_into_new_table',
			'version'  => '2.0.0',
			'callback' => 'give_v20_move_metadata_into_new_table_callback',
			'depend'   => array( 'v20_upgrades_payment_metadata', 'v20_upgrades_form_metadata' ),
		)
	);

	// v2.0.0 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v20_rename_donor_tables',
			'version'  => '2.0.0',
			'callback' => 'give_v20_rename_donor_tables_callback',
			'depend'   => array(
				'v20_move_metadata_into_new_table',
				'v20_logs_upgrades',
				'v20_upgrades_form_metadata',
				'v20_upgrades_payment_metadata',
				'v20_upgrades_user_address',
				'v20_upgrades_donor_name',
			),
		)
	);


	// v2.0.1 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v201_upgrades_payment_metadata',
			'version'  => '2.0.1',
			'callback' => 'give_v201_upgrades_payment_metadata_callback',
		)
	);

	// v2.0.1 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v201_add_missing_donors',
			'version'  => '2.0.1',
			'callback' => 'give_v201_add_missing_donors_callback',
		)
	);

	// Run v2.0.0 Upgrades again in 2.0.1
	$give_updates->register(
		array(
			'id'       => 'v201_move_metadata_into_new_table',
			'version'  => '2.0.1',
			'callback' => 'give_v201_move_metadata_into_new_table_callback',
			'depend'   => array( 'v201_upgrades_payment_metadata', 'v201_add_missing_donors' ),
		)
	);

	// Run v2.0.0 Upgrades again in 2.0.1
	$give_updates->register(
		array(
			'id'       => 'v201_logs_upgrades',
			'version'  => '2.0.1',
			'callback' => 'give_v201_logs_upgrades_callback',
		)
	);

	// v2.1 Verify Form Status Upgrade.
	$give_updates->register(
		array(
			'id'       => 'v210_verify_form_status_upgrades',
			'version'  => '2.1.0',
			'callback' => 'give_v210_verify_form_status_upgrades_callback',
		)
	);

	// v2.1.3 Delete non attached donation meta.
	$give_updates->register(
		array(
			'id'       => 'v213_delete_donation_meta',
			'version'  => '2.1.3',
			'callback' => 'give_v213_delete_donation_meta_callback',
			'depends'  => array( 'v201_move_metadata_into_new_table' )
		)
	);

}

add_action( 'give_register_updates', 'give_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.0
 * @return void
 */
function give_trigger_upgrades() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array(
			'response' => 403,
		) );
	}

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
		add_option( 'give_version', $give_version );
	}

	update_option( 'give_version', GIVE_VERSION );
	delete_option( 'give_doing_upgrade' );

	if ( DOING_AJAX ) {
		die( 'complete' );
	} // End if().
}

add_action( 'wp_ajax_give_trigger_upgrades', 'give_trigger_upgrades' );


/**
 * Upgrades the
 *
 * Standardizes the discrepancies between two metakeys `_give_payment_customer_id` and `_give_payment_donor_id`
 *
 * @since      1.3.2
 */
function give_v132_upgrade_give_payment_customer_id() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// UPDATE DB METAKEYS.
	$sql   = "UPDATE $wpdb->postmeta SET meta_key = '_give_payment_customer_id' WHERE meta_key = '_give_payment_donor_id'";
	$query = $wpdb->query( $sql );

	$give_updates->percentage = 100;
	give_set_upgrade_complete( 'upgrade_give_payment_customer_id' );
}


/**
 * Upgrades the Offline Status
 *
 * Reverses the issue where offline donations in "pending" status where inappropriately marked as abandoned
 *
 * @since      1.3.4
 */
function give_v134_upgrade_give_offline_status() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// Get abandoned offline payments.
	$select = "SELECT ID FROM $wpdb->posts p ";
	$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
	$where  = "WHERE p.post_type = 'give_payment' ";
	$where  .= "AND ( p.post_status = 'abandoned' )";
	$where  .= "AND ( m.meta_key = '_give_payment_gateway' AND m.meta_value = 'offline' )";

	$sql            = $select . $join . $where;
	$found_payments = $wpdb->get_col( $sql );

	foreach ( $found_payments as $payment ) {

		// Only change ones marked abandoned since our release last week because the admin may have marked some abandoned themselves.
		$modified_time = get_post_modified_time( 'U', false, $payment );

		// 1450124863 =  12/10/2015 20:42:25.
		if ( $modified_time >= 1450124863 ) {

			give_update_payment_status( $payment, 'pending' );

		}
	}

	$give_updates->percentage = 100;
	give_set_upgrade_complete( 'upgrade_give_offline_status' );
}


/**
 * Cleanup User Roles
 *
 * This upgrade routine removes unused roles and roles with typos
 *
 * @since      1.5.2
 */
function give_v152_cleanup_users() {

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
	}

	$give_version = preg_replace( '/[^0-9.].*/', '', $give_version );

	// v1.5.2 Upgrades
	if ( version_compare( $give_version, '1.5.2', '<' ) || ! give_has_upgrade_completed( 'upgrade_give_user_caps_cleanup' ) ) {

		// Delete all caps with "ss".
		// Also delete all unused "campaign" roles.
		$delete_caps = array(
			'delete_give_formss',
			'delete_others_give_formss',
			'delete_private_give_formss',
			'delete_published_give_formss',
			'read_private_forms',
			'edit_give_formss',
			'edit_others_give_formss',
			'edit_private_give_formss',
			'edit_published_give_formss',
			'publish_give_formss',
			'read_private_give_formss',
			'assign_give_campaigns_terms',
			'delete_give_campaigns',
			'delete_give_campaigns_terms',
			'delete_give_campaignss',
			'delete_others_give_campaignss',
			'delete_private_give_campaignss',
			'delete_published_give_campaignss',
			'edit_give_campaigns',
			'edit_give_campaigns_terms',
			'edit_give_campaignss',
			'edit_others_give_campaignss',
			'edit_private_give_campaignss',
			'edit_published_give_campaignss',
			'manage_give_campaigns_terms',
			'publish_give_campaignss',
			'read_give_campaigns',
			'read_private_give_campaignss',
			'view_give_campaigns_stats',
			'delete_give_paymentss',
			'delete_others_give_paymentss',
			'delete_private_give_paymentss',
			'delete_published_give_paymentss',
			'edit_give_paymentss',
			'edit_others_give_paymentss',
			'edit_private_give_paymentss',
			'edit_published_give_paymentss',
			'publish_give_paymentss',
			'read_private_give_paymentss',
		);

		global $wp_roles;
		foreach ( $delete_caps as $cap ) {
			foreach ( array_keys( $wp_roles->roles ) as $role ) {
				$wp_roles->remove_cap( $role, $cap );
			}
		}

		// Create Give plugin roles.
		$roles = new Give_Roles();
		$roles->add_roles();
		$roles->add_caps();

		// The Update Ran.
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
		give_set_upgrade_complete( 'upgrade_give_user_caps_cleanup' );
		delete_option( 'give_doing_upgrade' );

	}// End if().

}

add_action( 'admin_init', 'give_v152_cleanup_users' );

/**
 * 1.6 Upgrade routine to create the customer meta table.
 *
 * @since  1.6
 * @return void
 */
function give_v16_upgrades() {
	// Create the donor databases.
	$donors_db = new Give_DB_Donors();
	$donors_db->create_table();
	$donor_meta = new Give_DB_Donor_Meta();
	$donor_meta->create_table();
}

/**
 * 1.7 Upgrades.
 *
 * a. Update license api data for plugin addons.
 * b. Cleanup user roles.
 *
 * @since  1.7
 * @return void
 */
function give_v17_upgrades() {
	// Upgrade license data.
	give_v17_upgrade_addon_license_data();
	give_v17_cleanup_roles();
}

/**
 * Upgrade license data
 *
 * @since 1.7
 */
function give_v17_upgrade_addon_license_data() {
	$give_options = give_get_settings();

	$api_url = 'https://givewp.com/give-sl-api/';

	// Get addons license key.
	$addons = array();
	foreach ( $give_options as $key => $value ) {
		if ( false !== strpos( $key, '_license_key' ) ) {
			$addons[ $key ] = $value;
		}
	}

	// Bailout: We do not have any addon license data to upgrade.
	if ( empty( $addons ) ) {
		return false;
	}

	foreach ( $addons as $key => $addon_license ) {

		// Get addon shortname.
		$shortname = str_replace( '_license_key', '', $key );

		// Addon license option name.
		$addon_license_option_name = $shortname . '_license_active';

		// bailout if license is empty.
		if ( empty( $addon_license ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Get addon name.
		$addon_name       = array();
		$addon_name_parts = explode( '_', str_replace( 'give_', '', $shortname ) );
		foreach ( $addon_name_parts as $name_part ) {

			// Fix addon name
			switch ( $name_part ) {
				case 'authorizenet' :
					$name_part = 'authorize.net';
					break;
			}

			$addon_name[] = ucfirst( $name_part );
		}

		$addon_name = implode( ' ', $addon_name );

		// Data to send to the API.
		$api_params = array(
			'edd_action' => 'activate_license', // never change from "edd_" to "give_"!
			'license'    => $addon_license,
			'item_name'  => urlencode( $addon_name ),
			'url'        => home_url(),
		);

		// Call the API.
		$response = wp_remote_post(
			$api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure there are no errors.
		if ( is_wp_error( $response ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Tell WordPress to look for updates.
		set_site_transient( 'update_plugins', null );

		// Decode license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( $addon_license_option_name, $license_data );
	}// End foreach().
}


/**
 * Cleanup User Roles.
 *
 * This upgrade routine removes unused roles and roles with typos.
 *
 * @since      1.7
 */
function give_v17_cleanup_roles() {

	// Delete all caps with "_give_forms_" and "_give_payments_".
	// These roles have no usage; the proper is singular.
	$delete_caps = array(
		'view_give_forms_stats',
		'delete_give_forms_terms',
		'assign_give_forms_terms',
		'edit_give_forms_terms',
		'manage_give_forms_terms',
		'view_give_payments_stats',
		'manage_give_payments_terms',
		'edit_give_payments_terms',
		'assign_give_payments_terms',
		'delete_give_payments_terms',
	);

	global $wp_roles;
	foreach ( $delete_caps as $cap ) {
		foreach ( array_keys( $wp_roles->roles ) as $role ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

	// Set roles again.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();

}

/**
 * 1.8 Upgrades.
 *
 * a. Upgrade checkbox settings to radio button settings.
 * a. Update form meta for new metabox settings.
 *
 * @since  1.8
 * @return void
 */
function give_v18_upgrades() {
	// Upgrade checkbox settings to radio button settings.
	give_v18_upgrades_core_setting();
}

/**
 * Upgrade core settings.
 *
 * @since  1.8
 * @return void
 */
function give_v18_upgrades_core_setting() {
	// Core settings which changes from checkbox to radio.
	$core_setting_names = array_merge(
		array_keys( give_v18_renamed_core_settings() ),
		array(
			'uninstall_on_delete',
			'scripts_footer',
			'test_mode',
			'email_access',
			'terms',
			'give_offline_donation_enable_billing_fields',
		)
	);

	// Bailout: If not any setting define.
	if ( $give_settings = get_option( 'give_settings' ) ) {

		$setting_changed = false;

		// Loop: check each setting field.
		foreach ( $core_setting_names as $setting_name ) {
			// New setting name.
			$new_setting_name = preg_replace( '/^(enable_|disable_)/', '', $setting_name );

			// Continue: If setting already set.
			if (
				array_key_exists( $new_setting_name, $give_settings )
				&& in_array( $give_settings[ $new_setting_name ], array( 'enabled', 'disabled' ) )
			) {
				continue;
			}

			// Set checkbox value to radio value.
			$give_settings[ $setting_name ] = ( ! empty( $give_settings[ $setting_name ] ) && 'on' === $give_settings[ $setting_name ] ? 'enabled' : 'disabled' );

			// @see https://github.com/WordImpress/Give/issues/1063.
			if ( false !== strpos( $setting_name, 'disable_' ) ) {

				$give_settings[ $new_setting_name ] = ( give_is_setting_enabled( $give_settings[ $setting_name ] ) ? 'disabled' : 'enabled' );
			} elseif ( false !== strpos( $setting_name, 'enable_' ) ) {

				$give_settings[ $new_setting_name ] = ( give_is_setting_enabled( $give_settings[ $setting_name ] ) ? 'enabled' : 'disabled' );
			}

			// Tell bot to update core setting to db.
			if ( ! $setting_changed ) {
				$setting_changed = true;
			}
		}

		// Update setting only if they changed.
		if ( $setting_changed ) {
			update_option( 'give_settings', $give_settings );
		}
	}// End if().

	give_set_upgrade_complete( 'v18_upgrades_core_setting' );
}

/**
 * Upgrade form metadata for new metabox settings.
 *
 * @since  1.8
 * @return void
 */
function give_v18_upgrades_form_metadata() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query
	$forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $forms->have_posts() ) {
		$give_updates->set_percentage( $forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();

			// Form content.
			// Note in version 1.8 display content setting split into display content and content placement setting.
			// You can delete _give_content_option in future.
			$show_content = give_get_meta( get_the_ID(), '_give_content_option', true );
			if ( $show_content && ! give_get_meta( get_the_ID(), '_give_display_content', true ) ) {
				$field_value = ( 'none' !== $show_content ? 'enabled' : 'disabled' );
				give_update_meta( get_the_ID(), '_give_display_content', $field_value );

				$field_value = ( 'none' !== $show_content ? $show_content : 'give_pre_form' );
				give_update_meta( get_the_ID(), '_give_content_placement', $field_value );
			}

			// "Disable" Guest Donation. Checkbox.
			// See: https://github.com/WordImpress/Give/issues/1470.
			$guest_donation        = give_get_meta( get_the_ID(), '_give_logged_in_only', true );
			$guest_donation_newval = ( in_array( $guest_donation, array( 'yes', 'on' ) ) ? 'disabled' : 'enabled' );
			give_update_meta( get_the_ID(), '_give_logged_in_only', $guest_donation_newval );

			// Offline Donations.
			// See: https://github.com/WordImpress/Give/issues/1579.
			$offline_donation = give_get_meta( get_the_ID(), '_give_customize_offline_donations', true );
			if ( 'no' === $offline_donation ) {
				$offline_donation_newval = 'global';
			} elseif ( 'yes' === $offline_donation ) {
				$offline_donation_newval = 'enabled';
			} else {
				$offline_donation_newval = 'disabled';
			}
			give_update_meta( get_the_ID(), '_give_customize_offline_donations', $offline_donation_newval );

			// Convert yes/no setting field to enabled/disabled.
			$form_radio_settings = array(
				// Custom Amount.
				'_give_custom_amount',

				// Donation Gaol.
				'_give_goal_option',

				// Close Form.
				'_give_close_form_when_goal_achieved',

				// Term & conditions.
				'_give_terms_option',

				// Billing fields.
				'_give_offline_donation_enable_billing_fields_single',
			);

			foreach ( $form_radio_settings as $meta_key ) {
				// Get value.
				$field_value = give_get_meta( get_the_ID(), $meta_key, true );

				// Convert meta value only if it is in yes/no/none.
				if ( in_array( $field_value, array( 'yes', 'on', 'no', 'none' ) ) ) {

					$field_value = ( in_array( $field_value, array( 'yes', 'on' ) ) ? 'enabled' : 'disabled' );
					give_update_meta( get_the_ID(), $meta_key, $field_value );
				}
			}
		}// End while().

		wp_reset_postdata();

	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'v18_upgrades_form_metadata' );
	}
}


/**
 * Get list of core setting renamed in version 1.8.
 *
 * @since  1.8
 * @return array
 */
function give_v18_renamed_core_settings() {
	return array(
		'disable_paypal_verification' => 'paypal_verification',
		'disable_css'                 => 'css',
		'disable_welcome'             => 'welcome',
		'disable_forms_singular'      => 'forms_singular',
		'disable_forms_archives'      => 'forms_archives',
		'disable_forms_excerpt'       => 'forms_excerpt',
		'disable_form_featured_img'   => 'form_featured_img',
		'disable_form_sidebar'        => 'form_sidebar',
		'disable_admin_notices'       => 'admin_notices',
		'disable_the_content_filter'  => 'the_content_filter',
		'enable_floatlabels'          => 'floatlabels',
		'enable_categories'           => 'categories',
		'enable_tags'                 => 'tags',
	);
}


/**
 * Upgrade core settings.
 *
 * @since  1.8.7
 * @return void
 */
function give_v187_upgrades() {
	global $wpdb;

	/**
	 * Upgrade 1: Remove stat and cache transients.
	 */
	$cached_options = $wpdb->get_col(
		$wpdb->prepare(
			"
					SELECT *
					FROM {$wpdb->options}
					WHERE (
					option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					)
					",
			array(
				'%_transient_give_stats_%',
				'give_cache%',
				'%_transient_give_add_ons_feed%',
				'%_transient__give_ajax_works' .
				'%_transient_give_total_api_keys%',
				'%_transient_give_i18n_give_promo_hide%',
				'%_transient_give_contributors%',
				'%_transient_give_estimated_monthly_stats%',
				'%_transient_give_earnings_total%',
				'%_transient_give_i18n_give_%',
				'%_transient__give_installed%',
				'%_transient__give_activation_redirect%',
				'%_transient__give_hide_license_notices_shortly_%',
				'%give_income_total%',
			)
		),
		1
	);

	// User related transients.
	$user_apikey_options = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id, meta_key
			FROM $wpdb->usermeta
			WHERE meta_value=%s",
			'give_user_public_key'
		),
		ARRAY_A
	);

	if ( ! empty( $user_apikey_options ) ) {
		foreach ( $user_apikey_options as $user ) {
			$cached_options[] = '_transient_' . md5( 'give_api_user_' . $user['meta_key'] );
			$cached_options[] = '_transient_' . md5( 'give_api_user_public_key' . $user['user_id'] );
			$cached_options[] = '_transient_' . md5( 'give_api_user_secret_key' . $user['user_id'] );
		}
	}

	if ( ! empty( $cached_options ) ) {
		foreach ( $cached_options as $option ) {
			switch ( true ) {
				case ( false !== strpos( $option, 'transient' ) ):
					$option = str_replace( '_transient_', '', $option );
					delete_transient( $option );
					break;

				default:
					delete_option( $option );
			}
		}
	}
}

/**
 * Update Capabilities for Give_Worker User Role.
 *
 * This upgrade routine will update access rights for Give_Worker User Role.
 *
 * @since      1.8.8
 */
function give_v188_upgrades() {

	global $wp_roles;

	// Get the role object.
	$give_worker = get_role( 'give_worker' );

	// A list of capabilities to add for give workers.
	$caps_to_add = array(
		'edit_posts',
		'edit_pages',
	);

	foreach ( $caps_to_add as $cap ) {
		// Add the capability.
		$give_worker->add_cap( $cap );
	}

}

/**
 * Update Post meta for minimum and maximum amount for multi level donation forms
 *
 * This upgrade routine adds post meta for give_forms CPT for multi level donation form.
 *
 * @since      1.8.9
 */
function give_v189_upgrades_levels_post_meta_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query.
	$donation_forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $donation_forms->have_posts() ) {
		$give_updates->set_percentage( $donation_forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			$form_id = get_the_ID();

			// Remove formatting from _give_set_price.
			update_post_meta(
				$form_id,
				'_give_set_price',
				give_sanitize_amount( get_post_meta( $form_id, '_give_set_price', true ) )
			);

			// Remove formatting from _give_custom_amount_minimum.
			update_post_meta(
				$form_id,
				'_give_custom_amount_minimum',
				give_sanitize_amount( get_post_meta( $form_id, '_give_custom_amount_minimum', true ) )
			);

			// Bailout.
			if ( 'set' === get_post_meta( $form_id, '_give_price_option', true ) ) {
				continue;
			}

			$donation_levels = get_post_meta( $form_id, '_give_donation_levels', true );

			if ( ! empty( $donation_levels ) ) {

				foreach ( $donation_levels as $index => $donation_level ) {
					if ( isset( $donation_level['_give_amount'] ) ) {
						$donation_levels[ $index ]['_give_amount'] = give_sanitize_amount( $donation_level['_give_amount'] );
					}
				}

				update_post_meta( $form_id, '_give_donation_levels', $donation_levels );

				$donation_levels_amounts = wp_list_pluck( $donation_levels, '_give_amount' );

				$min_amount = min( $donation_levels_amounts );
				$max_amount = max( $donation_levels_amounts );

				// Set Minimum and Maximum amount for Multi Level Donation Forms
				give_update_meta( $form_id, '_give_levels_minimum_amount', $min_amount ? give_sanitize_amount( $min_amount ) : 0 );
				give_update_meta( $form_id, '_give_levels_maximum_amount', $max_amount ? give_sanitize_amount( $max_amount ) : 0 );
			}

		}

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v189_upgrades_levels_post_meta' );
	}

}


/**
 * Give version 1.8.9 upgrades
 *
 * @since      1.8.9
 */
function give_v189_upgrades() {
	/**
	 * 1. Remove user license related notice show blocked ( Give_Notice will handle )
	 */
	global $wpdb;

	// Delete permanent notice blocker.
	$wpdb->query(
		$wpdb->prepare(
			"
					DELETE FROM $wpdb->usermeta
					WHERE meta_key
					LIKE '%%%s%%'
					",
			'_give_hide_license_notices_permanently'
		)
	);

	// Delete short notice blocker.
	$wpdb->query(
		$wpdb->prepare(
			"
					DELETE FROM $wpdb->options
					WHERE option_name
					LIKE '%%%s%%'
					",
			'__give_hide_license_notices_shortly_'
		)
	);
}

/**
 * 2.0 Upgrades.
 *
 * @since  2.0
 * @return void
 */
function give_v20_upgrades() {
	// Update cache setting.
	give_update_option( 'cache', 'enabled' );

	// Upgrade email settings.
	give_v20_upgrades_email_setting();
}

/**
 * Move old email api settings to new email setting api for following emails:
 *    1. new offline donation         [This was hard coded]
 *    2. offline donation instruction
 *    3. new donation
 *    4. donation receipt
 *
 * @since 2.0
 */
function give_v20_upgrades_email_setting() {
	$all_setting = give_get_settings();

	// Bailout on fresh install.
	if ( empty( $all_setting ) ) {
		return;
	}

	$settings = array(
		'offline_donation_subject'      => 'offline-donation-instruction_email_subject',
		'global_offline_donation_email' => 'offline-donation-instruction_email_message',
		'donation_subject'              => 'donation-receipt_email_subject',
		'donation_receipt'              => 'donation-receipt_email_message',
		'donation_notification_subject' => 'new-donation_email_subject',
		'donation_notification'         => 'new-donation_email_message',
		'admin_notice_emails'           => array(
			'new-donation_recipient',
			'new-offline-donation_recipient',
			'new-donor-register_recipient',
		),
		'admin_notices'                 => 'new-donation_notification',
	);

	foreach ( $settings as $old_setting => $new_setting ) {
		// Do not update already modified
		if ( ! is_array( $new_setting ) ) {
			if ( array_key_exists( $new_setting, $all_setting ) || ! array_key_exists( $old_setting, $all_setting ) ) {
				continue;
			}
		}

		switch ( $old_setting ) {
			case 'admin_notices':
				$notification_status = give_get_option( $old_setting, 'enabled' );

				give_update_option( $new_setting, $notification_status );

				// @todo: Delete this option later ( version > 2.0 ), We need this for per form email addon.
				// give_delete_option( $old_setting );

				break;

			// @todo: Delete this option later ( version > 2.0 ) because we need this for backward compatibility give_get_admin_notice_emails.
			case 'admin_notice_emails':
				$recipients = give_get_admin_notice_emails();

				foreach ( $new_setting as $setting ) {
					// bailout if setting already exist.
					if ( array_key_exists( $setting, $all_setting ) ) {
						continue;
					}

					give_update_option( $setting, $recipients );
				}
				break;

			default:
				give_update_option( $new_setting, give_get_option( $old_setting ) );
				give_delete_option( $old_setting );
		}
	}
}

/**
 * Give version 1.8.9 upgrades
 *
 * @since 1.8.9
 */
function give_v1812_upgrades() {
	/**
	 * Validate number format settings.
	 */
	$give_settings        = give_get_settings();
	$give_setting_updated = false;

	if ( $give_settings['thousands_separator'] === $give_settings['decimal_separator'] ) {
		$give_settings['number_decimals']   = 0;
		$give_settings['decimal_separator'] = '';
		$give_setting_updated               = true;

	} elseif ( empty( $give_settings['decimal_separator'] ) ) {
		$give_settings['number_decimals'] = 0;
		$give_setting_updated             = true;

	} elseif ( 6 < absint( $give_settings['number_decimals'] ) ) {
		$give_settings['number_decimals'] = 5;
		$give_setting_updated             = true;
	}

	if ( $give_setting_updated ) {
		update_option( 'give_settings', $give_settings );
	}
}


/**
 * Give version 1.8.12 update
 *
 * Standardized amount values to six decimal
 *
 * @see        https://github.com/WordImpress/Give/issues/1849#issuecomment-315128602
 *
 * @since      1.8.12
 */
function give_v1812_update_amount_values_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query.
	$donation_forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'give_forms', 'give_payment' ),
			'posts_per_page' => 20,
		)
	);
	if ( $donation_forms->have_posts() ) {
		$give_updates->set_percentage( $donation_forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			global $post;

			$meta = get_post_meta( $post->ID );

			switch ( $post->post_type ) {
				case 'give_forms':
					// _give_set_price.
					if ( ! empty( $meta['_give_set_price'][0] ) ) {
						update_post_meta( $post->ID, '_give_set_price', give_sanitize_amount_for_db( $meta['_give_set_price'][0] ) );
					}

					// _give_custom_amount_minimum.
					if ( ! empty( $meta['_give_custom_amount_minimum'][0] ) ) {
						update_post_meta( $post->ID, '_give_custom_amount_minimum', give_sanitize_amount_for_db( $meta['_give_custom_amount_minimum'][0] ) );
					}

					// _give_levels_minimum_amount.
					if ( ! empty( $meta['_give_levels_minimum_amount'][0] ) ) {
						update_post_meta( $post->ID, '_give_levels_minimum_amount', give_sanitize_amount_for_db( $meta['_give_levels_minimum_amount'][0] ) );
					}

					// _give_levels_maximum_amount.
					if ( ! empty( $meta['_give_levels_maximum_amount'][0] ) ) {
						update_post_meta( $post->ID, '_give_levels_maximum_amount', give_sanitize_amount_for_db( $meta['_give_levels_maximum_amount'][0] ) );
					}

					// _give_set_goal.
					if ( ! empty( $meta['_give_set_goal'][0] ) ) {
						update_post_meta( $post->ID, '_give_set_goal', give_sanitize_amount_for_db( $meta['_give_set_goal'][0] ) );
					}

					// _give_form_earnings.
					if ( ! empty( $meta['_give_form_earnings'][0] ) ) {
						update_post_meta( $post->ID, '_give_form_earnings', give_sanitize_amount_for_db( $meta['_give_form_earnings'][0] ) );
					}

					// _give_custom_amount_minimum.
					if ( ! empty( $meta['_give_donation_levels'][0] ) ) {
						$donation_levels = unserialize( $meta['_give_donation_levels'][0] );

						foreach ( $donation_levels as $index => $level ) {
							if ( empty( $level['_give_amount'] ) ) {
								continue;
							}

							$donation_levels[ $index ]['_give_amount'] = give_sanitize_amount_for_db( $level['_give_amount'] );
						}

						$meta['_give_donation_levels'] = $donation_levels;
						update_post_meta( $post->ID, '_give_donation_levels', $meta['_give_donation_levels'] );
					}


					break;

				case 'give_payment':
					// _give_payment_total.
					if ( ! empty( $meta['_give_payment_total'][0] ) ) {
						update_post_meta( $post->ID, '_give_payment_total', give_sanitize_amount_for_db( $meta['_give_payment_total'][0] ) );
					}

					break;
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1812_update_amount_values' );
	}
}


/**
 * Give version 1.8.12 update
 *
 * Standardized amount values to six decimal for donor
 *
 * @see        https://github.com/WordImpress/Give/issues/1849#issuecomment-315128602
 *
 * @since      1.8.12
 */
function give_v1812_update_donor_purchase_value_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query.
	$donors = Give()->donors->get_donors( array(
			'number' => 20,
			'offset' => $give_updates->get_offset( 20 ),
		)
	);

	if ( ! empty( $donors ) ) {
		$give_updates->set_percentage( Give()->donors->count(), $give_updates->get_offset( 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {
			Give()->donors->update( $donor->id, array( 'purchase_value' => give_sanitize_amount_for_db( $donor->purchase_value ) ) );
		}
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1812_update_donor_purchase_values' );
	}
}

/**
 * Upgrade routine for updating user roles for existing donors.
 *
 * @since 1.8.13
 */
function give_v1813_update_donor_user_roles_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// Fetch all the existing donors.
	$donors = Give()->donors->get_donors( array(
			'number' => 20,
			'offset' => $give_updates->get_offset( 20 ),
		)
	);

	if ( ! empty( $donors ) ) {
		$give_updates->set_percentage( Give()->donors->count(), $give_updates->get_offset( 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {
			$user_id = $donor->user_id;

			// Proceed, if donor is attached with user.
			if ( $user_id ) {
				$user = get_userdata( $user_id );

				// Update user role, if user has subscriber role.
				if ( is_array( $user->roles ) && in_array( 'subscriber', $user->roles ) ) {
					wp_update_user(
						array(
							'ID'   => $user_id,
							'role' => 'give_donor',
						)
					);
				}
			}
		}
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1813_update_donor_user_roles' );
	}
}


/**
 * Version 1.8.13 automatic updates
 *
 * @since 1.8.13
 */
function give_v1813_upgrades() {
	// Update admin setting.
	give_update_option( 'donor_default_user_role', 'give_donor' );

	// Update Give roles.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();
}

/**
 * Correct currency code for "Iranian Currency" for all of the payments.
 *
 * @since 1.8.17
 */
function give_v1817_update_donation_iranian_currency_code() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query.
	$payments = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'give_payment' ),
			'posts_per_page' => 100,
		)
	);

	if ( $payments->have_posts() ) {
		$give_updates->set_percentage( $payments->found_posts, ( $give_updates->step * 100 ) );

		while ( $payments->have_posts() ) {
			$payments->the_post();

			$payment_meta = give_get_payment_meta( get_the_ID() );

			if ( 'RIAL' === $payment_meta['currency'] ) {
				$payment_meta['currency'] = 'IRR';
				give_update_meta( get_the_ID(), '_give_payment_meta', $payment_meta );
			}

		}

	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1817_update_donation_iranian_currency_code' );
	}
}

/**
 * Correct currency code for "Iranian Currency" in Give setting.
 * Version 1.8.17 automatic updates
 *
 * @since 1.8.17
 */
function give_v1817_upgrades() {
	$give_settings = give_get_settings();

	if ( 'RIAL' === $give_settings['currency'] ) {
		$give_settings['currency'] = 'IRR';
		update_option( 'give_settings', $give_settings );
	}
}

/**
 * Process Clean up of User Roles for more flexibility.
 *
 * @since 1.8.17
 */
function give_v1817_process_cleanup_user_roles() {

	global $wp_roles;

	if ( ! ( $wp_roles instanceof WP_Roles ) ) {
		return;
	}

	// Add Capabilities to user roles as required.
	$add_caps = array(
		'administrator' => array(
			'view_give_payments',
		),
	);

	// Remove Capabilities to user roles as required.
	$remove_caps = array(
		'give_manager' => array(
			'edit_others_pages',
			'edit_others_posts',
			'delete_others_pages',
			'delete_others_posts',
			'manage_categories',
			'import',
			'export',
		),
	);

	foreach ( $add_caps as $role => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->add_cap( $role, $cap );
		}
	}

	foreach ( $remove_caps as $role => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

}

/**
 * Upgrade Routine - Clean up of User Roles for more flexibility.
 *
 * @since 1.8.17
 */
function give_v1817_cleanup_user_roles() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	give_v1817_process_cleanup_user_roles();

	$give_updates->percentage = 100;

	// Create Give plugin roles.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();

	give_set_upgrade_complete( 'v1817_cleanup_user_roles' );
}

/**
 * Automatic Upgrade for release 1.8.18.
 *
 * @since 1.8.18
 */
function give_v1818_upgrades() {

	// Remove email_access_installed from give_settings.
	give_delete_option( 'email_access_installed' );
}

/**
 * Upgrade Routine - Assigns Custom Amount to existing donation of type set donation.
 *
 * @since 1.8.18
 */
function give_v1818_assign_custom_amount_set_donation() {

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	$donations = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'give_payment' ),
			'posts_per_page' => 100,
		)
	);

	if ( $donations->have_posts() ) {
		$give_updates->set_percentage( $donations->found_posts, $give_updates->step * 100 );

		while ( $donations->have_posts() ) {
			$donations->the_post();

			$form          = new Give_Donate_Form( give_get_meta( get_the_ID(), '_give_payment_form_id', true ) );
			$donation_meta = give_get_payment_meta( get_the_ID() );

			// Update Donation meta with price_id set as custom, only if it is:
			// 1. Donation Type = Set Donation.
			// 2. Donation Price Id is not set to custom.
			// 3. Form has not enabled custom price and donation amount assures that it is custom amount.
			if (
				$form->ID &&
				$form->is_set_type_donation_form() &&
				( 'custom' !== $donation_meta['price_id'] ) &&
				$form->is_custom_price( give_get_meta( get_the_ID(), '_give_payment_total', true ) )
			) {
				$donation_meta['price_id'] = 'custom';
				give_update_meta( get_the_ID(), '_give_payment_meta', $donation_meta );
				give_update_meta( get_the_ID(), '_give_payment_price_id', 'custom' );
			}
		}

		wp_reset_postdata();
	} else {
		// Update Ran Successfully.
		give_set_upgrade_complete( 'v1818_assign_custom_amount_set_donation' );
	}
}

/**
 * Upgrade Routine - Removed Give Worker caps.
 *
 * See: https://github.com/WordImpress/Give/issues/2476
 *
 * @since 1.8.18
 */
function give_v1818_give_worker_role_cleanup() {

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	global $wp_roles;

	if ( ! ( $wp_roles instanceof WP_Roles ) ) {
		return;
	}

	// Remove Capabilities to user roles as required.
	$remove_caps = array(
		'give_worker' => array(
			'delete_give_payments',
			'delete_others_give_payments',
			'delete_private_give_payments',
			'delete_published_give_payments',
			'edit_others_give_payments',
			'edit_private_give_payments',
			'edit_published_give_payments',
			'read_private_give_payments',
		),
	);

	foreach ( $remove_caps as $role => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

	$give_updates->percentage = 100;

	// Create Give plugin roles.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();

	give_set_upgrade_complete( 'v1818_give_worker_role_cleanup' );
}

/**
 *
 * Upgrade form metadata for new metabox settings.
 *
 * @since  2.0
 * @return void
 */
function give_v20_upgrades_form_metadata_callback() {
	$give_updates = Give_Updates::get_instance();

	// form query
	$forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 100,
		)
	);

	if ( $forms->have_posts() ) {
		$give_updates->set_percentage( $forms->found_posts, ( $give_updates->step * 100 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();
			global $post;

			// Update offline instruction email notification status.
			$offline_instruction_notification_status = get_post_meta( get_the_ID(), '_give_customize_offline_donations', true );
			$offline_instruction_notification_status = give_is_setting_enabled( $offline_instruction_notification_status, array(
				'enabled',
				'global',
			) )
				? $offline_instruction_notification_status
				: 'global';
			update_post_meta( get_the_ID(), '_give_offline-donation-instruction_notification', $offline_instruction_notification_status );

			// Update offline instruction email message.
			update_post_meta(
				get_the_ID(),
				'_give_offline-donation-instruction_email_message',
				get_post_meta(
					get_the_ID(),
					// @todo: Delete this option later ( version > 2.0 ).
					'_give_offline_donation_email',
					true
				)
			);

			// Update offline instruction email subject.
			update_post_meta(
				get_the_ID(),
				'_give_offline-donation-instruction_email_subject',
				get_post_meta(
					get_the_ID(),
					// @todo: Delete this option later ( version > 2.0 ).
					'_give_offline_donation_subject',
					true
				)
			);


		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'v20_upgrades_form_metadata' );
	}
}


/**
 * Upgrade payment metadata for new metabox settings.
 *
 * @since  2.0
 * @global wpdb $wpdb
 * @return void
 */
function give_v20_upgrades_payment_metadata_callback() {
	global $wpdb;
	$give_updates = Give_Updates::get_instance();

	// form query
	$forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_payment',
			'posts_per_page' => 100,
		)
	);

	if ( $forms->have_posts() ) {
		$give_updates->set_percentage( $forms->found_posts, ( $give_updates->step * 100 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();
			global $post;

			// Split _give_payment_meta meta.
			// @todo Remove _give_payment_meta after releases 2.0
			$payment_meta = give_get_meta( $post->ID, '_give_payment_meta', true );

			if ( ! empty( $payment_meta ) ) {
				_give_20_bc_split_and_save_give_payment_meta( $post->ID, $payment_meta );
			}

			$deprecated_meta_keys = array(
				'_give_payment_customer_id' => '_give_payment_donor_id',
				'_give_payment_user_email'  => '_give_payment_donor_email',
				'_give_payment_user_ip'     => '_give_payment_donor_ip',
			);

			foreach ( $deprecated_meta_keys as $old_meta_key => $new_meta_key ) {
				// Do not add new meta key if already exist.
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s", $post->ID, $new_meta_key ) ) ) {
					continue;
				}

				$wpdb->insert(
					$wpdb->postmeta,
					array(
						'post_id'    => $post->ID,
						'meta_key'   => $new_meta_key,
						'meta_value' => give_get_meta( $post->ID, $old_meta_key, true ),
					)
				);
			}

			// Bailout
			if ( $donor_id = give_get_meta( $post->ID, '_give_payment_donor_id', true ) ) {
				/* @var Give_Donor $donor */
				$donor = new Give_Donor( $donor_id );

				$address['line1']   = give_get_meta( $post->ID, '_give_donor_billing_address1', true, '' );
				$address['line2']   = give_get_meta( $post->ID, '_give_donor_billing_address2', true, '' );
				$address['city']    = give_get_meta( $post->ID, '_give_donor_billing_city', true, '' );
				$address['state']   = give_get_meta( $post->ID, '_give_donor_billing_state', true, '' );
				$address['zip']     = give_get_meta( $post->ID, '_give_donor_billing_zip', true, '' );
				$address['country'] = give_get_meta( $post->ID, '_give_donor_billing_country', true, '' );

				// Save address.
				$donor->add_address( 'billing[]', $address );
			}

		}// End while().

		wp_reset_postdata();
	} else {
		// @todo Delete user id meta after releases 2.0
		// $wpdb->get_var( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_give_payment_user_id' ) );

		// No more forms found, finish up.
		give_set_upgrade_complete( 'v20_upgrades_payment_metadata' );
	}
}


/**
 * Upgrade logs data.
 *
 * @since  2.0
 * @return void
 */
function give_v20_logs_upgrades_callback() {
	global $wpdb;
	$give_updates = Give_Updates::get_instance();

	// form query
	$forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'order'          => 'DESC',
			'post_type'      => 'give_log',
			'post_status'    => 'any',
			'posts_per_page' => 100,
		)
	);

	if ( $forms->have_posts() ) {
		$give_updates->set_percentage( $forms->found_posts, $give_updates->step * 100 );

		while ( $forms->have_posts() ) {
			$forms->the_post();
			global $post;

			if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_logs WHERE ID=%d", $post->ID ) ) ) {
				continue;
			}

			$term      = get_the_terms( $post->ID, 'give_log_type' );
			$term      = ! is_wp_error( $term ) && ! empty( $term ) ? $term[0] : array();
			$term_name = ! empty( $term ) ? $term->slug : '';

			$log_data = array(
				'ID'           => $post->ID,
				'log_title'    => $post->post_title,
				'log_content'  => $post->post_content,
				'log_parent'   => 0,
				'log_type'     => $term_name,
				'log_date'     => $post->post_date,
				'log_date_gmt' => $post->post_date_gmt,
			);
			$log_meta = array();

			if ( $old_log_meta = get_post_meta( $post->ID ) ) {
				foreach ( $old_log_meta as $meta_key => $meta_value ) {
					switch ( $meta_key ) {
						case '_give_log_payment_id':
							$log_data['log_parent']        = current( $meta_value );
							$log_meta['_give_log_form_id'] = $post->post_parent;
							break;

						default:
							$log_meta[ $meta_key ] = current( $meta_value );
					}
				}
			}

			if ( 'api_request' === $term_name ) {
				$log_meta['_give_log_api_query'] = $post->post_excerpt;
			}

			$wpdb->insert( "{$wpdb->prefix}give_logs", $log_data );

			if ( ! empty( $log_meta ) ) {
				foreach ( $log_meta as $meta_key => $meta_value ) {
					Give()->logs->logmeta_db->update_meta( $post->ID, $meta_key, $meta_value );
				}
			}

			$logIDs[] = $post->ID;
		}// End while().

		wp_reset_postdata();
	} else {
		// @todo: Delete terms and taxonomy after releases 2.0.
		/*$terms = get_terms( 'give_log_type', array( 'fields' => 'ids', 'hide_empty' => false ) );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term, 'give_log_type' );
			}
		}*/

		// @todo: Delete logs after releases 2.0.
		/*$logIDs = get_posts( array(
				'order'          => 'DESC',
				'post_type'      => 'give_log',
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);*/

		/*if ( ! empty( $logIDs ) ) {
			foreach ( $logIDs as $log ) {
				// Delete term relationship and posts.
				wp_delete_object_term_relationships( $log, 'give_log_type' );
				wp_delete_post( $log, true );
			}
		}*/

		// @todo: Unregister taxonomy after releases 2.0.
		/*unregister_taxonomy( 'give_log_type' );*/

		// Delete log cache.
		Give()->logs->delete_cache();

		// No more forms found, finish up.
		give_set_upgrade_complete( 'v20_logs_upgrades' );
	}
}


/**
 * Move payment and form metadata to new table
 *
 * @since  2.0
 * @return void
 */
function give_v20_move_metadata_into_new_table_callback() {
	global $wpdb;
	$give_updates = Give_Updates::get_instance();

	// form query
	$payments = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'give_forms', 'give_payment' ),
			'posts_per_page' => 100,
		)
	);

	if ( $payments->have_posts() ) {
		$give_updates->set_percentage( $payments->found_posts, $give_updates->step * 100 );

		while ( $payments->have_posts() ) {
			$payments->the_post();
			global $post;

			$meta_data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->postmeta where post_id=%d",
					get_the_ID()
				),
				ARRAY_A
			);

			if ( ! empty( $meta_data ) ) {
				foreach ( $meta_data as $index => $data ) {
					// Check for duplicate meta values.
					if ( $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . ( 'give_forms' === $post->post_type ? $wpdb->formmeta : $wpdb->paymentmeta ) . " WHERE meta_id=%d", $data['meta_id'] ), ARRAY_A ) ) {
						continue;
					}

					switch ( $post->post_type ) {
						case 'give_forms':
							$data['form_id'] = $data['post_id'];
							unset( $data['post_id'] );

							Give()->form_meta->insert( $data );
							// @todo: delete form meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;

						case 'give_payment':
							$data['payment_id'] = $data['post_id'];
							unset( $data['post_id'] );

							Give()->payment_meta->insert( $data );

							// @todo: delete donation meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;
					}
				}
			}

		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'v20_move_metadata_into_new_table' );
	}

}

/**
 * Upgrade routine for splitting donor name into first name and last name.
 *
 * @since 2.0
 *
 * @return void
 */
function give_v20_upgrades_donor_name() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	$donors = Give()->donors->get_donors( array(
		'paged'  => $give_updates->step,
		'number' => 100,
	) );

	if ( $donors ) {
		$give_updates->set_percentage( count( $donors ), $give_updates->step * 100 );
		// Loop through Donors
		foreach ( $donors as $donor ) {

			$donor_name       = explode( ' ', $donor->name, 2 );
			$donor_first_name = Give()->donor_meta->get_meta( $donor->id, '_give_donor_first_name' );
			$donor_last_name  = Give()->donor_meta->get_meta( $donor->id, '_give_donor_last_name' );

			// If first name meta of donor is not created, then create it.
			if ( ! $donor_first_name && isset( $donor_name[0] ) ) {
				Give()->donor_meta->add_meta( $donor->id, '_give_donor_first_name', $donor_name[0] );
			}

			// If last name meta of donor is not created, then create it.
			if ( ! $donor_last_name && isset( $donor_name[1] ) ) {
				Give()->donor_meta->add_meta( $donor->id, '_give_donor_last_name', $donor_name[1] );
			}

			// If Donor is connected with WP User then update user meta.
			if ( $donor->user_id ) {
				if ( isset( $donor_name[0] ) ) {
					update_user_meta( $donor->user_id, 'first_name', $donor_name[0] );
				}
				if ( isset( $donor_name[1] ) ) {
					update_user_meta( $donor->user_id, 'last_name', $donor_name[1] );
				}
			}
		}

	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v20_upgrades_donor_name' );
	}

}

/**
 * Upgrade routine for user addresses.
 *
 * @since 2.0
 * @global wpdb $wpdb
 *
 * @return void
 */
function give_v20_upgrades_user_address() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	/* @var WP_User_Query $user_query */
	$user_query = new WP_User_Query(
		array(
			'number' => 100,
			'paged'  => $give_updates->step,
		)
	);

	$users = $user_query->get_results();

	if ( $users ) {
		$give_updates->set_percentage( $user_query->get_total(), $give_updates->step * 100 );

		// Loop through Donors
		foreach ( $users as $user ) {
			/* @var Give_Donor $donor */
			$donor = new Give_Donor( $user->ID, true );

			if ( ! $donor->id ) {
				continue;
			}

			$address = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT meta_value FROM {$wpdb->usermeta}
					WHERE user_id=%s
					AND meta_key=%s
					",
					$user->ID,
					'_give_user_address'
				)
			);

			if ( ! empty( $address ) ) {
				$address = maybe_unserialize( $address );
				$donor->add_address( 'personal', $address );
				$donor->add_address( 'billing[]', $address );


				// @todo: delete _give_user_address from user meta after releases 2.0.
				/*delete_user_meta( $user->ID, '_give_user_address' );*/
			}
		}

	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v20_upgrades_user_address' );
	}

}

/**
 * Upgrade logs data.
 *
 * @since  2.0
 * @global wpdb $wpdb
 * @return void
 */
function give_v20_rename_donor_tables_callback() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	$tables = array(
		"{$wpdb->prefix}give_customers"    => "{$wpdb->prefix}give_donors",
		"{$wpdb->prefix}give_customermeta" => "{$wpdb->prefix}give_donormeta",
	);

	// Alter customer table
	foreach ( $tables as $old_table => $new_table ) {
		if (
			$wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", $old_table ) ) &&
			! $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", $new_table ) )
		) {
			$wpdb->query( "ALTER TABLE {$old_table} RENAME TO {$new_table}" );

			if ( "{$wpdb->prefix}give_donormeta" === $new_table ) {
				$wpdb->query( "ALTER TABLE {$new_table} CHANGE COLUMN customer_id donor_id bigint(20)" );
			}
		}
	}

	$give_updates->percentage = 100;

	// No more forms found, finish up.
	give_set_upgrade_complete( 'v20_rename_donor_tables' );

	// Re initiate donor classes.
	Give()->donors     = new Give_DB_Donors();
	Give()->donor_meta = new Give_DB_Donor_Meta();
}


/**
 * Create missing meta tables.
 *
 * @since  2.0.1
 * @global wpdb $wpdb
 * @return void
 */
function give_v201_create_tables() {
	global $wpdb;

	if ( ! $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", "{$wpdb->prefix}give_paymentmeta" ) ) ) {
		Give()->payment_meta->create_table();
	}

	if ( ! $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", "{$wpdb->prefix}give_formmeta" ) ) ) {
		Give()->form_meta->create_table();
	}

	if ( ! $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", "{$wpdb->prefix}give_logs" ) ) ) {
		Give()->logs->log_db->create_table();
	}

	if ( ! $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", "{$wpdb->prefix}give_logmeta" ) ) ) {
		Give()->logs->logmeta_db->create_table();
	}
}

/**
 * Upgrade payment metadata for new metabox settings.
 *
 * @since  2.0.1
 * @global wpdb $wpdb
 * @return void
 */
function give_v201_upgrades_payment_metadata_callback() {
	global $wpdb, $post;
	$give_updates = Give_Updates::get_instance();
	give_v201_create_tables();

	$payments = $wpdb->get_col(
		"
			SELECT ID FROM $wpdb->posts
			WHERE 1=1
			AND ( 
  				$wpdb->posts.post_date >= '2018-01-08 00:00:00'
			)
			AND $wpdb->posts.post_type = 'give_payment'
			AND {$wpdb->posts}.post_status IN ('" . implode( "','", array_keys( give_get_payment_statuses() ) ) . "')
			ORDER BY $wpdb->posts.post_date ASC 
			LIMIT 100
			OFFSET " . $give_updates->get_offset( 100 )
	);

	if ( ! empty( $payments ) ) {
		$give_updates->set_percentage( give_get_total_post_type_count( 'give_payment' ), ( $give_updates->step * 100 ) );

		foreach ( $payments as $payment_id ) {
			$post = get_post( $payment_id );
			setup_postdata( $post );

			// Do not add new meta keys if already refactored.
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s", $post->ID, '_give_payment_donor_id' ) ) ) {
				continue;
			}


			// Split _give_payment_meta meta.
			// @todo Remove _give_payment_meta after releases 2.0
			$payment_meta = give_get_meta( $post->ID, '_give_payment_meta', true );

			if ( ! empty( $payment_meta ) ) {
				_give_20_bc_split_and_save_give_payment_meta( $post->ID, $payment_meta );
			}

			$deprecated_meta_keys = array(
				'_give_payment_customer_id' => '_give_payment_donor_id',
				'_give_payment_user_email'  => '_give_payment_donor_email',
				'_give_payment_user_ip'     => '_give_payment_donor_ip',
			);

			foreach ( $deprecated_meta_keys as $old_meta_key => $new_meta_key ) {
				// Do not add new meta key if already exist.
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s", $post->ID, $new_meta_key ) ) ) {
					continue;
				}

				$wpdb->insert(
					$wpdb->postmeta,
					array(
						'post_id'    => $post->ID,
						'meta_key'   => $new_meta_key,
						'meta_value' => give_get_meta( $post->ID, $old_meta_key, true ),
					)
				);
			}

			// Bailout
			if ( $donor_id = give_get_meta( $post->ID, '_give_payment_donor_id', true ) ) {
				/* @var Give_Donor $donor */
				$donor = new Give_Donor( $donor_id );

				$address['line1']   = give_get_meta( $post->ID, '_give_donor_billing_address1', true, '' );
				$address['line2']   = give_get_meta( $post->ID, '_give_donor_billing_address2', true, '' );
				$address['city']    = give_get_meta( $post->ID, '_give_donor_billing_city', true, '' );
				$address['state']   = give_get_meta( $post->ID, '_give_donor_billing_state', true, '' );
				$address['zip']     = give_get_meta( $post->ID, '_give_donor_billing_zip', true, '' );
				$address['country'] = give_get_meta( $post->ID, '_give_donor_billing_country', true, '' );

				// Save address.
				$donor->add_address( 'billing[]', $address );
			}

		}// End while().

		wp_reset_postdata();
	} else {
		// @todo Delete user id meta after releases 2.0
		// $wpdb->get_var( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_give_payment_user_id' ) );

		// No more forms found, finish up.
		give_set_upgrade_complete( 'v201_upgrades_payment_metadata' );
	}
}

/**
 * Move payment and form metadata to new table
 *
 * @since  2.0.1
 * @return void
 */
function give_v201_move_metadata_into_new_table_callback() {
	global $wpdb, $post;
	$give_updates = Give_Updates::get_instance();
	give_v201_create_tables();

	$payments = $wpdb->get_col(
		"
			SELECT ID FROM $wpdb->posts 
			WHERE 1=1
			AND ( $wpdb->posts.post_type = 'give_payment' OR $wpdb->posts.post_type = 'give_forms' )
			AND {$wpdb->posts}.post_status IN ('" . implode( "','", array_keys( give_get_payment_statuses() ) ) . "')
			ORDER BY $wpdb->posts.post_date ASC 
			LIMIT 100
			OFFSET " . $give_updates->get_offset( 100 )
	);

	if ( ! empty( $payments ) ) {
		$give_updates->set_percentage( give_get_total_post_type_count( array(
			'give_forms',
			'give_payment',
		) ), $give_updates->step * 100 );

		foreach ( $payments as $payment_id ) {
			$post = get_post( $payment_id );
			setup_postdata( $post );

			$meta_data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->postmeta where post_id=%d",
					get_the_ID()
				),
				ARRAY_A
			);

			if ( ! empty( $meta_data ) ) {
				foreach ( $meta_data as $index => $data ) {
					// Check for duplicate meta values.
					if ( $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . ( 'give_forms' === $post->post_type ? $wpdb->formmeta : $wpdb->paymentmeta ) . " WHERE meta_id=%d", $data['meta_id'] ), ARRAY_A ) ) {
						continue;
					}

					switch ( $post->post_type ) {
						case 'give_forms':
							$data['form_id'] = $data['post_id'];
							unset( $data['post_id'] );

							Give()->form_meta->insert( $data );
							// @todo: delete form meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;

						case 'give_payment':
							$data['payment_id'] = $data['post_id'];
							unset( $data['post_id'] );

							Give()->payment_meta->insert( $data );

							// @todo: delete donation meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;
					}
				}
			}

		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'v201_move_metadata_into_new_table' );
	}

}

/**
 * Move data to new log table.
 *
 * @since  2.0.1
 * @return void
 */
function give_v201_logs_upgrades_callback() {
	global $wpdb, $post;
	$give_updates = Give_Updates::get_instance();
	give_v201_create_tables();

	$logs = $wpdb->get_col(
		"
			SELECT ID FROM $wpdb->posts 
			WHERE 1=1
			AND $wpdb->posts.post_type = 'give_log'
			AND {$wpdb->posts}.post_status IN ('" . implode( "','", array_keys( give_get_payment_statuses() ) ) . "')
			ORDER BY $wpdb->posts.post_date ASC 
			LIMIT 100
			OFFSET " . $give_updates->get_offset( 100 )
	);

	if ( ! empty( $logs ) ) {
		$give_updates->set_percentage( give_get_total_post_type_count( 'give_log' ), $give_updates->step * 100 );

		foreach ( $logs as $log_id ) {
			$post = get_post( $log_id );
			setup_postdata( $post );

			if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_logs WHERE ID=%d", $post->ID ) ) ) {
				continue;
			}

			$term      = get_the_terms( $post->ID, 'give_log_type' );
			$term      = ! is_wp_error( $term ) && ! empty( $term ) ? $term[0] : array();
			$term_name = ! empty( $term ) ? $term->slug : '';

			$log_data = array(
				'ID'           => $post->ID,
				'log_title'    => $post->post_title,
				'log_content'  => $post->post_content,
				'log_parent'   => 0,
				'log_type'     => $term_name,
				'log_date'     => $post->post_date,
				'log_date_gmt' => $post->post_date_gmt,
			);
			$log_meta = array();

			if ( $old_log_meta = get_post_meta( $post->ID ) ) {
				foreach ( $old_log_meta as $meta_key => $meta_value ) {
					switch ( $meta_key ) {
						case '_give_log_payment_id':
							$log_data['log_parent']        = current( $meta_value );
							$log_meta['_give_log_form_id'] = $post->post_parent;
							break;

						default:
							$log_meta[ $meta_key ] = current( $meta_value );
					}
				}
			}

			if ( 'api_request' === $term_name ) {
				$log_meta['_give_log_api_query'] = $post->post_excerpt;
			}

			$wpdb->insert( "{$wpdb->prefix}give_logs", $log_data );

			if ( ! empty( $log_meta ) ) {
				foreach ( $log_meta as $meta_key => $meta_value ) {
					Give()->logs->logmeta_db->update_meta( $post->ID, $meta_key, $meta_value );
				}
			}

			$logIDs[] = $post->ID;
		}// End while().

		wp_reset_postdata();
	} else {
		// Delete log cache.
		Give()->logs->delete_cache();

		// No more forms found, finish up.
		give_set_upgrade_complete( 'v201_logs_upgrades' );
	}
}


/**
 * Add missing donor.
 *
 * @since  2.0.1
 * @return void
 */
function give_v201_add_missing_donors_callback() {
	global $wpdb;
	give_v201_create_tables();

	if ( $wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", "{$wpdb->prefix}give_customers" ) ) ) {
		$customers  = wp_list_pluck( $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}give_customers" ), 'id' );
		$donors     = wp_list_pluck( $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}give_donors" ), 'id' );
		$donor_data = array();

		if ( $missing_donors = array_diff( $customers, $donors ) ) {
			foreach ( $missing_donors as $donor_id ) {
				$donor_data[] = array(
					'info' => $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_customers WHERE id=%d", $donor_id ) ),
					'meta' => $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_customermeta WHERE customer_id=%d", $donor_id ) ),

				);
			}
		}

		if ( ! empty( $donor_data ) ) {
			$donor_table_name      = Give()->donors->table_name;
			$donor_meta_table_name = Give()->donor_meta->table_name;

			Give()->donors->table_name     = "{$wpdb->prefix}give_donors";
			Give()->donor_meta->table_name = "{$wpdb->prefix}give_donormeta";

			foreach ( $donor_data as $donor ) {
				$donor['info'][0] = (array) $donor['info'][0];

				// Prevent duplicate meta id issue.
				if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_donors WHERE id=%d", $donor['info'][0]['id'] ) ) ) {
					continue;
				}

				$donor_id = Give()->donors->add( $donor['info'][0] );

				if ( ! empty( $donor['meta'] ) ) {
					foreach ( $donor['meta'] as $donor_meta ) {
						$donor_meta = (array) $donor_meta;

						// Prevent duplicate meta id issue.
						if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_donormeta WHERE meta_id=%d", $donor_meta['meta_id'] ) ) ) {
							unset( $donor_meta['meta_id'] );
						}

						$donor_meta['donor_id'] = $donor_meta['customer_id'];
						unset( $donor_meta['customer_id'] );

						Give()->donor_meta->insert( $donor_meta );
					}
				}

				/**
				 * Fix donor name and address
				 */
				$address = $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT meta_value FROM {$wpdb->usermeta}
					WHERE user_id=%s
					AND meta_key=%s
					",
						$donor['info'][0]['user_id'],
						'_give_user_address'
					)
				);

				$donor = new Give_Donor( $donor_id );

				if ( ! empty( $address ) ) {
					$address = maybe_unserialize( $address );
					$donor->add_address( 'personal', $address );
					$donor->add_address( 'billing[]', $address );
				}

				$donor_name       = explode( ' ', $donor->name, 2 );
				$donor_first_name = Give()->donor_meta->get_meta( $donor->id, '_give_donor_first_name' );
				$donor_last_name  = Give()->donor_meta->get_meta( $donor->id, '_give_donor_last_name' );

				// If first name meta of donor is not created, then create it.
				if ( ! $donor_first_name && isset( $donor_name[0] ) ) {
					Give()->donor_meta->add_meta( $donor->id, '_give_donor_first_name', $donor_name[0] );
				}

				// If last name meta of donor is not created, then create it.
				if ( ! $donor_last_name && isset( $donor_name[1] ) ) {
					Give()->donor_meta->add_meta( $donor->id, '_give_donor_last_name', $donor_name[1] );
				}

				// If Donor is connected with WP User then update user meta.
				if ( $donor->user_id ) {
					if ( isset( $donor_name[0] ) ) {
						update_user_meta( $donor->user_id, 'first_name', $donor_name[0] );
					}
					if ( isset( $donor_name[1] ) ) {
						update_user_meta( $donor->user_id, 'last_name', $donor_name[1] );
					}
				}
			}

			Give()->donors->table_name     = $donor_table_name;
			Give()->donor_meta->table_name = $donor_meta_table_name;
		}
	}

	Give_Updates::get_instance()->percentage = 100;
	give_set_upgrade_complete( 'v201_add_missing_donors' );
}


/**
 * Version 2.0.3 automatic updates
 *
 * @since 2.0.3
 */
function give_v203_upgrades() {
	global $wpdb;

	// Do not auto load option.
	$wpdb->update( $wpdb->options, array( 'autoload' => 'no' ), array( 'option_name' => 'give_completed_upgrades' ) );

	// Remove from cache.
	$all_options = wp_load_alloptions();

	if ( isset( $all_options['give_completed_upgrades'] ) ) {
		unset( $all_options['give_completed_upgrades'] );
		wp_cache_set( 'alloptions', $all_options, 'options' );
	}

}

/**
 * Upgrade routine for 2.1 to set form closed status for all the donation forms.
 *
 * @since 2.1
 */
function give_v210_verify_form_status_upgrades_callback() {

	$give_updates = Give_Updates::get_instance();

	// form query.
	$donation_forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $donation_forms->have_posts() ) {
		$give_updates->set_percentage( $donation_forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			$form_id = get_the_ID();

			$form_closed_status = give_get_meta( $form_id, '_give_form_status', true );
			if ( empty( $form_closed_status ) ) {
				give_set_form_closed_status( $form_id );
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();

	} else {

		// The Update Ran.
		give_set_upgrade_complete( 'v210_verify_form_status_upgrades' );
	}

}

/**
 * Upgrade routine for 2.1.3 to delete meta which is not attach to any donation.
 *
 * @since 2.1
 */
function give_v213_delete_donation_meta_callback() {
	global $wpdb;
	$give_updates        = Give_Updates::get_instance();
	$donation_meta_table = Give()->payment_meta->table_name;

	$donations = $wpdb->get_col(
		"
		SELECT DISTINCT payment_id
		FROM {$donation_meta_table}
		LIMIT 20
		OFFSET {$give_updates->get_offset( 20 )}
		"
	);

	if ( ! empty( $donations ) ) {
		foreach ( $donations as $donation ) {
			$donation_obj = get_post( $donation );

			if ( ! $donation_obj instanceof WP_Post ) {
				Give()->payment_meta->delete_all_meta( $donation );
			}
		}
	} else {

		// The Update Ran.
		give_set_upgrade_complete( 'v213_delete_donation_meta' );
	}

}