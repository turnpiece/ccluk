<?php
/**
 * Give Recurring - Install Functions
 *
 * @since 1.4
 */

/**
 * Include a file when give_loaded action fire up will contain give recurring helpers functions.
 *
 * @since 1.4 - Added to give support of recurring email tag in donation mail.
 */
function give_recurring_give_loaded_callback() {

	require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-helpers.php';
}

add_action( 'give_loaded', 'give_recurring_give_loaded_callback' );


/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies, flushing rewrite rules to initiate the new
 * 'donations' slug and also creates the plugin and populates the settings fields for those plugin pages. After
 * successful install, the user is redirected to the Give Welcome screen.
 *
 * @since 1.8.2
 *
 * @param bool $network_wide
 *
 * @global     $wpdb
 * @return void
 */
function give_recurring_on_activate( $network_wide  = false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			give_recurring_install();
			restore_current_blog();

		}

	} else {
		give_recurring_install();
	}
}

register_activation_hook( GIVE_RECURRING_PLUGIN_FILE, 'give_recurring_on_activate' );

/**
 * Network Activated New Site Setup.
 *
 * When a new site is created when Give is network activated this function runs the appropriate install function to set
 * up the site for Give.
 *
 * @since      1.8.2
 *
 * @param  int $blog_id The Blog ID created.
 */
function give_recurring_on_create_blog( $blog_id ) {

	if ( is_plugin_active_for_network( GIVE_PLUGIN_BASENAME ) ) {

		switch_to_blog( $blog_id );
		give_recurring_on_activate();
		restore_current_blog();

	}

}

add_action( 'wpmu_new_blog', 'give_recurring_on_create_blog', 10 );

/**
 * Drop Give Recurring's custom tables when a mu site is deleted.
 *
 * @since  1.8.2
 *
 * @param  array $tables  The tables to drop.
 * @param  int   $blog_id The Blog ID being deleted.
 *
 * @return array          The tables to drop.
 */
function give_recurring_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$table_list = give_recurring_get_tables();

	/* @var  Give_DB $table */
	foreach ( $table_list as $table ) {
		if ( $table->installed() ) {
			$tables[] = $table->table_name;
		}
	}

	restore_current_blog();

	return $tables;

}

add_filter( 'wpmu_drop_tables', 'give_recurring_wpmu_drop_tables', 10, 2 );


/**
 * Recurring installation.
 *
 * @since 1.0
 */
function give_recurring_install() {

	// We need Give to continue.
	if ( ! give_recurring_check_environment() ) {
		return false;
	}

	Give_Recurring();
	$plugin_version = get_option('give_recurring_version');

	give_recurring_register_tables();
	give_recurring_install_pages();

	add_role( 'give_subscriber', __( 'Give Subscriber', 'give-recurring' ), array( 'read' => true ) );

	// Is fresh install?
	if( ! $plugin_version ) {
		// New install, no need to run these upgrades.
		$updates = array(
			'give_recurring_v12_upgraded',
			'give_recurring_v14_update_donor_count',
			'give_recurring_v153_update_donor_count',
			'give_recurring_v153_create_log_type_metadata',
			'give_recurring_v153_add_db_notes_column',
			'give_recurring_v160_add_db_frequency_column',
			'give_recurring_v170_sanitize_db_amount',
			'give_recurring_v172_renewal_payment_level',
			'give_recurring_v182_alter_amount_column_type'
		);

		foreach ( $updates as $update ){
			give_set_upgrade_complete( $update );
		}
	}

	/**
	 * Fire the action
	 */
	do_action( 'give_recurring_install_complete' );
}


/**
 * Install recurring pages. One at the moment.
 *
 * @since 1.4
 *
 * @return bool
 */
function give_recurring_install_pages() {

	// Bailout if pages already created.
	if ( get_option( 'give_recurring_pages_created' ) ) {
		return false;
	}

	$subscriptions_page_id = give_recurring_subscriptions_page_id();

	// Checks if the Subscription Page option exists AND that the page exists.
	if ( empty( $subscriptions_page_id ) || ! get_post( absint( $subscriptions_page_id ) ) ) {
		// Donation History Page
		$give_subscriptions = wp_insert_post(
			array(
				'post_title'     => __( 'Recurring Donations', 'give-recurring' ),
				'post_content'   => '[give_subscriptions]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);

		if ( ! empty( $give_subscriptions ) ){
			give_update_option( 'subscriptions_page', $give_subscriptions );
		}
	}

	add_option( 'give_recurring_pages_created', 1, '', 'no' );
}

/**
 * Licensing.
 *
 * @since 1.0
 */
function give_add_recurring_licensing() {
	if ( class_exists( 'Give_License' ) ) {
		new Give_License( GIVE_RECURRING_PLUGIN_FILE, 'Recurring Donations', GIVE_RECURRING_VERSION, 'WordImpress', 'recurring_license_key' );
	}
}

add_action( 'plugins_loaded', 'give_add_recurring_licensing' );

/**
 * Check the environment before starting up.
 *
 * @since 1.2.3
 *
 * @return bool
 */
function give_recurring_check_environment() {

	// Check for if give plugin activate or not.
	$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? true : false;

	// Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( current_user_can( 'activate_plugins' ) && ! $is_give_active ) {

		add_action( 'admin_notices', 'give_recurring_core_issue_msg' );

		add_action( 'admin_init', 'give_recurring_deactivate_self' );

		return false;

	}

	// Min. Give. plugin version.
	if ( defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, GIVE_RECURRING_MIN_GIVE_VERSION, '<' ) ) {

		add_action( 'admin_notices', 'give_recurring_core_version_issue_msg' );
		add_action( 'admin_init', 'give_recurring_deactivate_self' );

		return false;
	}

	// Checks pass.
	return true;

}

/**
 * Deactivate self. Must be hooked with admin_init.
 *
 * Currently hooked via give_recurring_check_environment()
 */
function give_recurring_deactivate_self() {
	deactivate_plugins( GIVE_RECURRING_PLUGIN_BASENAME );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

}

/**
 * Outputs an admin message if Give core is not activated.
 *
 * Hooked using admin_notice via give_recurring_check_environment()
 *
 * @since 1.3.1
 */
function give_recurring_core_issue_msg() {
	$class   = 'notice notice-error';
	$message = sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core plugin installed and activated for the Recurring Donations add-on to activate.', 'give-recurring' ), 'https://wordpress.org/plugins/give' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}


/**
 * Outputs an admin message if Give core is incompatible with this Recurring version.
 *
 * Hooked using admin_notice via give_recurring_check_environment()
 *
 * @since 1.3.1
 */
function give_recurring_core_version_issue_msg() {
	$message = sprintf( __( '<strong>Activation Error:</strong> You must have <a href="%1$s" target="_blank">Give</a> version %2$s+ for the Recurring Donations add-on to activate.', 'give-recurring' ), 'https://givewp.com', GIVE_RECURRING_MIN_GIVE_VERSION );
	if ( property_exists( 'Give', 'notices' ) ) {
		Give()->notices->register_notice( array(
			'id'          => 'give-activation-error',
			'type'        => 'error',
			'description' => $message,
			'show'        => true,
		) );
	} else {
		$class = 'notice notice-error';
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
}