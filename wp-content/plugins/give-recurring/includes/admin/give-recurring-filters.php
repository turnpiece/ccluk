<?php
/**
 * Add recurring donation report tab page
 *
 * @since  1.2.2
 *
 * @param  array $settings
 *
 * @return array
 */
function give_recurring_report_page( $settings ) {
	$settings[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/class-recurring-reports.php';

	// Output.
	return $settings;
}

add_filter( 'give-reports_get_settings_pages', 'give_recurring_report_page' );

/**
 * Adds "Renewals" to the report views
 *
 * @since  1.2.2
 *
 * @param $views
 *
 * @return mixed
 */
function give_recurring_log_tabs( $views ) {
	$views['recurring_email_notices'] = __( 'Recurring Emails', 'give-recurring' );
	$views['recurring_sync_logs']     = __( 'Synchronizer Logs', 'give-recurring' );

	return $views;
}

add_filter( 'give_log_views', 'give_recurring_log_tabs' );


/**
 * Show Recurring Email Notices Table.
 *
 * @since 1.2.2
 */
function give_recurring_show_email_notices_table() {

	include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-recurring-email-log.php';

	$logs_table = new Give_Recurring_Email_Log();
	$logs_table->prepare_items();
	?>
	<div class="wrap">
		<?php do_action( 'give_logs_recurring_email_notices_top' ); ?>
		<form id="give-logs-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-reports&tab=logs' ); ?>">
			<?php
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-reports"/>
			<input type="hidden" name="tab" value="logs"/>
		</form>
		<?php do_action( 'give_logs_recurring_email_notices_bottom' ); ?>
	</div>
	<?php
}

add_action( 'give_logs_view_recurring_email_notices', 'give_recurring_show_email_notices_table' );


/**
 * Display log details.
 *
 * @param $log_id
 *
 * @return bool
 */
function give_recurring_get_single_sync_log( $log_id ) {

	$args = array(
		'post_type'   => 'give_recur_sync_log',
		'post_status' => array( 'publish', 'future' ),
		'include'     => $log_id,
	);

	$log = get_posts( $args );

	// Need log WP_Post object to continue.
	if ( isset( $log[0] ) ) {
		$log = $log[0];
	} else {
		echo '<h2>' . sprintf( __( 'Sorry, no log found for ID %s', 'give-recurring' ), $log_id ) . '</h2>';

		return false;
	}

	?>

	<h2><?php echo $log->post_title; ?></h2>
	<div class="give-recurring-sync-log-content">
		<?php echo $log->post_content; ?>
	</div>
	<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-tools&section=recurring_sync_logs&tab=logs' ); ?>" class="button button-small">
		&laquo; <?php esc_html_e( 'Return to Sync Log List', 'give-recurring' ); ?>
	</a>

	<?php
}

/**
 * Show Sync Log Table.
 *
 * @since 1.2.2
 */
function give_recurring_show_sync_logs_table() {

	if ( isset( $_GET['log-id'] ) ) {

		give_recurring_get_single_sync_log( $_GET['log-id'] );

		return;
	}

	require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/synchronizer/class-recurring-sync-log.php';

	$logs_table = new Give_Recurring_Sync_Log();
	$logs_table->prepare_items();
	?>
	<div class="wrap">
		<?php do_action( 'give_logs_recurring_sync_logs_top' ); ?>
		<form id="give-logs-filter" method="get"
		      action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-tools&tab=logs' ); ?>">
			<?php
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-tools"/>
			<input type="hidden" name="tab" value="logs"/>
		</form>
		<?php do_action( 'give_logs_recurring_sync_logs_bottom' ); ?>
	</div>
	<?php

}

add_action( 'give_logs_view_recurring_sync_logs', 'give_recurring_show_sync_logs_table' );

/**
 * Add a page display state for special Give Recurring pages in the page list table.
 *
 * @since 1.5.1
 *
 * @param array $post_states An array of post display states.
 * @param WP_Post $post The current post object.
 *
 * @return array $post_states.
 */
function give_recurring_add_display_page_states( $post_states, $post ) {

	// Checks if it's a Success Page.
	if ( $post->ID === absint( give_recurring_subscriptions_page_id() ) ) {
		$post_states['give_subscriptions_page'] = __( 'Donation Recurring Page', 'give-recurring' );
	}

	return $post_states;
}

// Add a post display state for special Give pages.
add_filter( 'display_post_states', 'give_recurring_add_display_page_states', 10, 2 );

/**
 * Change in Form Title for Renewal Donations.
 *
 * @param mixed $form_title_html Form Title HTML.
 * @param int   $donation_id        Donation ID.
 *
 * @since 1.5.1
 *
 * @return mixed
 */
function give_recurring_modify_donation_form_title( $form_title_html, $donation_id ) {
	if ( 'give_subscription' === get_post_status( $donation_id ) ) {
		remove_filter( 'give_get_donation_form_title', 'give_recurring_modify_donation_form_title', 10 );

		$form_title_html =  give_get_donation_form_title(
			wp_get_post_parent_id( $donation_id ),
			array(
				'only_level' => true,
			)
		);

		add_filter( 'give_get_donation_form_title', 'give_recurring_modify_donation_form_title', 10, 2 );
	}

	return $form_title_html;
}

add_filter( 'give_get_donation_form_title', 'give_recurring_modify_donation_form_title', 10, 3 );


/**
 * Get the Subcription count from the Donation Table.
 *
 * @since 1.5.1
 *
 * @return int $count
 */
function give_recurring_get_subscription_count_from_donation_table() {

	global $wpdb;

	$count = 0;
	$args  = array();

	if ( isset( $_GET['user'] ) ) {
		$args['user'] = urldecode( $_GET['user'] );
	} elseif ( isset( $_GET['donor'] ) ) {
		$args['donor'] = absint( $_GET['donor'] );
	} elseif ( isset( $_GET['s'] ) ) {
		$is_user = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;
		if ( $is_user ) {
			$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
			unset( $args['s'] );
		} else {
			$args['s'] = sanitize_text_field( $_GET['s'] );
		}
	}

	if ( ! empty( $_GET['start-date'] ) ) {
		$args['start-date'] = urldecode( $_GET['start-date'] );
	}

	if ( ! empty( $_GET['end-date'] ) ) {
		$args['end-date'] = urldecode( $_GET['end-date'] );
	}

	if ( ! empty( $_GET['form_id'] ) ) {
		$args['give_forms'] = urldecode( $_GET['form_id'] );
	}

	if ( ! empty( $_GET['gateway'] ) ) {
		$args['gateway'] = urldecode( $_GET['gateway'] );
	}

	// Extract all donations
	$args['number']   = - 1;
	$args['group_by'] = 'post_status';
	$args['count']    = true;
	$payments         = give_get_payments( $args );

	if ( $payments ) {
	    $payment_ids = implode( "','", wp_list_pluck( $payments, 'ID' ) );
		$table_name  = $wpdb->prefix . 'give_subscriptions';
		$sql         = "SELECT COUNT(id) FROM {$table_name} WHERE `parent_payment_id` IN ( '{$payment_ids}' )";
		$count       = $wpdb->get_var( $sql );
	}

	return (int) $count;
}

/**
 * Payments View.
 *
 * Displays the cancelled payments filter link.
 *
 * @since  1.0
 * @access public
 *
 * @param array $views
 * @param Give_Payment_History_Table $donation_table
 *
 * @return array
 */
function give_recurring_payments_table_views( $views, $donation_table ) {
	$current = isset( $_GET['donation_type'] ) ? $_GET['donation_type'] : '';
	$status  = isset( $_GET['status'] ) ? $_GET['status'] : false;
	$form_id = isset( $_GET['form_id'] ) ? true : false;
	if ( empty( $form_id ) ) {
		$recurring          = new Give_Subscriptions_DB();
		$subscription_count = (int) $recurring->count();
	} else {
		$subscription_count = (int) give_recurring_get_subscription_count_from_donation_table();
	}
	if ( ! empty( $subscription_count ) ) {
		$staus_url               = add_query_arg( array( 'donation_type' => '_give_subscription_payment' ), remove_query_arg( array(
			'status',
			'paged',
			'_wpnonce',
			'_wp_http_referer'
		) ) );
		$views['give_recurring'] = sprintf(
			'<a href="%s"%s>%s&nbsp;<span class="count">(%s)</span></a>',
			esc_url( $staus_url ),
			$current === '_give_subscription_payment' && empty( $status ) ? ' class="current"' : '',
			__( 'Subscriptions', 'give-recurring' ),
			$subscription_count
		);
		$staus_url               = remove_query_arg( array(
			'donation_type',
			'status',
			'paged',
			'_wpnonce',
			'_wp_http_referer'
		) );
		$views['all']            = sprintf(
			'<a href="%s"%s>%s&nbsp;<span class="count">(%s)</span></a>',
			esc_url( $staus_url ),
			empty( $current ) && empty( $status ) ? ' class="current"' : '',
			__( 'All', 'give-recurring' ),
			$donation_table->total_count
		);
	}
	$donations_count = $donation_table->get_payment_counts();
	if ( property_exists( $donations_count, 'give_subscription' ) && $donations_count->give_subscription ) {
		$current                    = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$renewal_count              = '&nbsp;<span class="count">(' . $donations_count->give_subscription . ')</span>';
		$staus_url                  = add_query_arg( array( 'status' => 'give_subscription' ), remove_query_arg( array(
			'paged',
			'_wpnonce',
			'_wp_http_referer',
			'donation_type',
		) ) );
		$views['give_subscription'] = sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $staus_url ),
			$current === 'give_subscription' ? ' class="current"' : '',
			__( 'Renewals', 'give-recurring' ) . $renewal_count
		);
	}

	return $views;
}

// Show the Renewals and Subscription status links in Payment History.
add_filter( 'give_payments_table_views', 'give_recurring_payments_table_views', 10, 2 );
/**
 * Filter to Modify Pagination Total Count when Subscrition Tab is open.
 *
 * @since 1.5.1
 *
 * @param $total_items
 *
 * @return int
 */
function give_recurring_payment_table_pagination_total_count( $total_items ) {
	$current = isset( $_GET['donation_type'] ) ? $_GET['donation_type'] : false;
	$status  = isset( $_GET['status'] ) ? $_GET['status'] : false;
	if ( $current === '_give_subscription_payment' && empty( $status ) ) {
		$total_items = give_recurring_get_subscription_count_from_donation_table();
	}

	return $total_items;
}

add_filter( 'give_payment_table_pagination_total_count', 'give_recurring_payment_table_pagination_total_count' );
/**
 * Remove Variable from the Query in Donation Status.
 *
 * @since 1.5.1.
 *
 * @param (array) $args
 *
 * @return (array) $args
 */
function give_recurring_payments_table_status_remove_query_arg( $args ) {
	$search = ( isset( $_GET['form_id'] ) ? true : false );
	if ( empty( $search ) ) {
		$args[] = 'donation_type';
	}

	return $args;
}

add_filter( 'give_payments_table_status_remove_query_arg', 'give_recurring_payments_table_status_remove_query_arg' );
/**
 * Add Hidden fields is Donation type is get in the URL.
 *
 * @since 1.5.1
 */
function give_recurring_payment_table_advanced_filters() {
	if ( ! empty( $_GET['donation_type'] ) ) {
		?>
		<input type="hidden" name="donation_type" value="<?php echo give_clean( $_GET['donation_type'] ); ?>">
		<?php
	}
}

add_action( 'give_payment_table_advanced_filters', 'give_recurring_payment_table_advanced_filters' );

/**
 * Filter to modify Payment table Meta Query
 *
 * @since 1.5.1
 */
function give_recurring_payment_table_payments_query( $args ) {
	if ( ! empty( $_GET['donation_type'] ) && '_give_subscription_payment' === give_clean( $_GET['donation_type'] ) ) {
		$args['meta_query'] = array(
			array(
				'key'     => '_give_subscription_payment',
				'compare' => 'EXISTS'
			)
		);
	}

	return $args;
}

add_filter( 'give_payment_table_payments_query', 'give_recurring_payment_table_payments_query' );


/**
 * Add blank slate for subscription list
 *
 * @param string $screen
 *
 * @since 2.5.11
 */
function give_recurring_blank_slate( $screen ) {
	$subscription_db = new Give_Subscriptions_DB();

	if ( 'give_forms_page_give-subscriptions' === $screen && ! $subscription_db->count() ) {
		$blank_slate          = Give_Blank_Slate::get_instance();
		$blank_slate->content = $blank_slate->get_default_content();

		if ( ! $blank_slate->post_exists( 'give_forms' ) ) {
			$blank_slate->content = wp_parse_args(
				array(
					'heading' => __( 'No subscriptions found.', 'give-recurring' ),
					'message' => __( 'Your subscription history will appear here, but first, you need a donation form!', 'give-recurring' ),
				),
				$blank_slate->content
			);
		} else {
			$blank_slate->content = wp_parse_args(
				array(
					'heading'  => __( 'No subscriptions found.', 'give-recurring' ),
					'message'  => __( 'When your first recurring donation occurs a record of the subscription will appear here. Have you set up a recurring donation form yet?', 'give-recurring' ),
					'cta_text' => __( 'View All Donation Forms', 'give-recurring' ),
					'cta_link' => admin_url( 'edit.php?post_type=give_forms' ),
					'help'     => sprintf(
					/* translators: 1: Opening anchor tag. 2: Closing anchor tag. */
						__( 'Need help? Learn more about %sSubscriptions%2$s.', 'give-recurring' ),
						'<a href="http://docs.givewp.com/addon-recurring">',
						'</a>'
					),
				),
				$blank_slate->content
			);
		}

		add_action( 'give_recurring_subscriptions_page_bottom', array( $blank_slate, 'render' ) );
		add_action( 'admin_head', array( $blank_slate, 'hide_ui' ) );
	}
}

add_action( 'give_blank_slate', 'give_recurring_blank_slate', 10, 2 );
