<?php

/**
 * Give Renewal Recipient Admin Email
 *
 * @package     Give_Recurring
 * @subpackage  Includes/Admin/Emails
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.3
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Renewal_Recipient_Admin_Email' ) ) :

	/**
	 * Class Give_Renewal_Recipient_Admin_Email
	 */
	class Give_Renewal_Recipient_Admin_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 */
		public function init() {

			$this->load( array(
				'id'                    => 'renewal-receipt-admin',
				'label'                 => __( 'Renewal Receipt Email', 'give-recurring' ),
				'description'           => __( 'Check this option if you would like admin to receive an email when a renewal donation payment has been received. Note: some payment gateways like Stripe and Authorize.net may also send out an email depending on your gateway settings.', 'give-recurring' ),
				'has_recipient_field'   => true,
				'notification_status'   => 'disabled',
				'form_metabox_setting'  => false,
				'default_email_subject' => __( 'Subscription Donation Receipt', 'give-recurring' ),
				'default_email_message' => __( 'Hi there,', 'give-recurring' ) . "\n\n" . "{name} " . __( 'has succesfully completed renewal donation process. Here are donation details:', 'give-recurring' ) . "\n\n<strong>Donar Name:</strong> {fullname}\n<strong>Donation:</strong> {donation} - {amount}\n<strong>Payment ID:</strong> {payment_id} \n<strong>Payment Method:</strong> {payment_method}\n<strong>Date:</strong> {date}\n\nSincerely,\n{sitename}",
				'default_email_header'  => __( 'Donation Receipt', 'give-recurring' ),
			) );

			add_action( 'give_recurring_add_subscription_payment', array( $this, 'setup_email_notification' ), 10, 2 );
		}

		/**
		 * Plugin settings.
		 *
		 * @param int $form_id
		 *
		 * @return array
		 */
		public function get_setting_fields( $form_id = 0 ) {

			$settings[] = Give_Email_Setting_Field::get_section_start( $this, $form_id );
			$settings[] = Give_Email_Setting_Field::get_notification_status_field( $this, $form_id );

			$settings = array_merge(
				$settings,
				array(
					array(
						'name'    => __( 'Renewal Receipt Subject', 'give-recurring' ),
						'id'      => 'subscription_notification_admin_subject',
						'desc'    => __( 'Enter the subject line for the renewal donation receipt email.', 'give-recurring' ),
						'type'    => 'text',
						'default' => $this->config['default_email_subject'],
					),
					array(
						'name'    => __( 'Renewal Receipt Header', 'give-recurring' ),
						'id'      => 'subscription_notification_admin_header',
						'desc'    => __( 'Enter the header for the renewal donation receipt email.', 'give-recurring' ),
						'type'    => 'text',
						'default' => $this->config['default_email_header'],
					),
					array(
						'name'    => __( 'Renewal Donation Receipt', 'give-recurring' ),
						'id'      => 'subscription_receipt_admin_message',
						'desc'    => __( 'Enter the email message that is sent to users after upon Give receiving a successful renewal donation. HTML is accepted. Available template tags: ', 'give-recurring' ) . $this->get_allowed_email_tags( true ),
						'type'    => 'wysiwyg',
						'default' => $this->config['default_email_message'],
					),
				)
			);

			// Recipient field.
			$settings[] = Give_Email_Setting_Field::get_recipient_setting_field( $this, $form_id, Give_Email_Notification_Util::has_recipient_field( $this ) );

			$settings[] = Give_Email_Setting_Field::get_preview_setting_field( $this, $form_id );
			$settings   = Give_Email_Setting_Field::add_section_end( $this, $settings );


			return $settings;
		}

		/**
		 * Get email message.
		 *
		 * @since  1.8.3
		 *
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = 0 ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				'subscription_receipt_admin_message',
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the subject.
			 *
			 * @since 1.8.3
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_message",
				$message,
				$this
			);
		}

		/**
		 * Get email subject.
		 *
		 * @since  1.8.3
		 *
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_subject( $form_id = 0 ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				'subscription_notification_admin_subject',
				$form_id,
				$this->config['default_email_subject']
			);

			/**
			 * Filter the message.
			 *
			 * @since 1.8.3
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_subject",
				$message,
				$this
			);
		}

		/**
		 * Get email header.
		 *
		 * @since 1.8.3
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_header( $form_id = 0 ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				'subscription_notification_admin_header',
				$form_id,
				$this->config['default_email_header']
			);

			/**
			 * Filter the message.
			 *
			 * @since 1.8.3
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_header",
				$message,
				$this
			);
		}


		/**
		 * Setup email notification.
		 *
		 * @since  1.8.3
		 *
		 * @access public
		 *
		 * @param Give_Payment      $payment      instance of Give_Payment.
		 * @param Give_Subscription $subscription instance of Give_Subscription.
		 *
		 * @return bool
		 */
		public function setup_email_notification( $payment, $subscription ) {

			$sent = false;

			/**
			 * Send subscription received email if payment completion emails are not prevented.
			 *
			 * @since 1.8.3
			 */
			if ( apply_filters( 'give_recurring_should_send_subscription_received_email', true ) ) {

				$sent = $this->send_email_notification( array(
					'subscription_id' => $subscription->id,
					'payment_id'      => $payment->ID,
					'user_id'         => $subscription->donor->user_id,
					'form_id'         => give_get_payment_form_id( $subscription->parent_payment_id ),
				) );
			}

			if ( $sent ) {
				Give_Recurring_Emails::log_recurring_email( 'payment-admin-email', $subscription, $this->get_email_subject() );
			}

			return $sent;
		}
	}

endif; // End class_exists check

return Give_Renewal_Recipient_Admin_Email::get_instance();
