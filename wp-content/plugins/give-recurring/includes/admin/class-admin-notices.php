<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Admin_Notices
 */
class Give_Recurring_Admin_Notices {

	/**
	 * Give_Recurring_Admin_Notices constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'admin_notices', array( $this, 'notices_on_subscriptions_page' ) );
	}

	/**
	 * Add notices on subcription page.
	 *
	 * @since 1.5.1
	 */
	public function notices_on_subscriptions_page() {
		if ( ! give_is_admin_page( 'give-subscriptions' ) ) {
			return;
		}

		// Bulk action notices.
		if (
			isset( $_GET['action'] ) &&
			! empty( $_GET['action'] )
		) {

			// Add payment bulk notice.
			if (
				current_user_can( 'edit_give_payments' ) &&
				isset( $_GET['subscription'] ) &&
				! empty( $_GET['subscription'] )
			) {
				$subscription_count = isset( $_GET['subscription'] ) ? count( $_GET['subscription'] ) : 0;

				switch ( $_GET['action'] ) {
					case 'delete':
						Give()->notices->register_notice( array(
							'id'          => 'bulk_action_delete',
							'type'        => 'updated',
							'description' =>
								sprintf(
									_n( 'Successfully deleted one subscription.',
										'Successfully deleted %d subscriptions.',
										$subscription_count,
										'give-recurring'
									),
									$subscription_count
								),
							'show'        => true,
						) );
						break;

					case 'set-status-pending':
					case 'set-status-active':
					case 'set-status-cancelled':
					case 'set-status-expired':
					case 'set-status-completed':
						Give()->notices->register_notice( array(
							'id'          => 'bulk_action_status_change',
							'type'        => 'updated',
							'description' => _n(
								'Subscription status updated successfully.',
								'Subscription statuses updated successfully.',
								$subscription_count,
								'give-recurring'
							),
							'show'        => true,
						) );
						break;
				}
			}
		}
	}

	/**
	 * Notices.
	 */
	public function notices() {


		if ( ! give_is_admin_page( 'give-subscriptions' ) ) {
			return;
		}

		if ( empty( $_GET['give-message'] ) ) {
			return;
		}

		$id          = false;
		$type        = false;
		$description = false;

		switch ( strtolower( $_GET['give-message'] ) ) {
			case 'updated' :
				$id          = 'give-subscription-updated';
				$type        = 'updated';
				$description = __( 'Subscription successfully updated.', 'give-recurring' );
				break;
			case 'deleted' :
				$id          = 'give-subscription-deleted';
				$type        = 'updated';
				$description = __( 'Subscription successfully deleted.', 'give-recurring' );
				break;
			case 'cancelled' :
				$id          = 'give-subscription-cancelled';
				$type        = 'updated';
				$description = __( 'Subscription successfully cancelled.', 'give-recurring' );
				break;
			case 'cancelled-admin' :
				$id          = 'give-subscription-cancelled-admin';
				$type        = 'updated';
				$description = __( 'Subscription successfully cancelled.', 'give-recurring' );
				break;
			case 'renewal-added' :
				$id          = 'give-subscription-renewal';
				$type        = 'updated';
				$description = __( 'Renewal donation successfully recorded.', 'give-recurring' );
				break;
			case 'renewal-not-added' :
				$id          = 'give-subscription-not-renewal';
				$type        = 'error';
				$description = __( 'Renewal donation could not be recorded.', 'give-recurring' );
				break;
		}

		if ( ! empty( $type ) ) {
			if ( class_exists( 'Give_Notices' ) ) {
				Give()->notices->register_notice( array(
					'id'          => $id,
					'type'        => $type,
					'description' => $description,
					'show'        => true,
				) );
			} else {
				printf( '<div class="%1$s"><p>%2$s</p></div>', $type, $description );
			}
		}
	}
}
$give_recurring_admin_notices = new Give_Recurring_Admin_Notices();