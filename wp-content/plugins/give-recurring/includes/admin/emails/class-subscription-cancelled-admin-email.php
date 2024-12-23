<?php

/**
 * Give Subscription Cancelled Admin Email
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

if ( ! class_exists( 'Give_Subscription_Cancelled_Admin_Email' ) ) :

	/**
	 * Class Give_Subscription_Cancelled_Admin_Email
	 */
	class Give_Subscription_Cancelled_Admin_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 */
		public function init() {

			$this->load( array(
				'id'                    => 'subscription-cancelled-admin',
				'label'                 => __( 'Subscription Cancelled Email', 'give-recurring' ),
				'description'           => __( 'Check this option if you would like admin to receive an email when a subscription has been cancelled. The email will send when either the donor or admin cancels the subscription', 'give-recurring' ),
				'has_recipient_field'   => true,
				'notification_status'   => 'disabled',
				'form_metabox_setting'  => false,
				'default_email_subject' => __( 'Subscription Donation Cancelled', 'give-recurring' ),
				'default_email_message' => __( 'Hi there,', 'give-recurring' ) . "\n\n" . __( "This email is to inform you that subscription for {donation} has been successfully cancelled. Here are the subscription details for your records:\n\n<strong>Donor Name:</strong> {fullname}\n<strong>Subscription:</strong> {donation} - {amount}\n<strong>Subscription Frequency:</strong> {subscription_frequency} \n<strong>Completed Donations:</strong> {subscriptions_completed} \n<strong>Payment Method:</strong> {payment_method}\n<strong>Cancellation Date:</strong> {cancellation_date}\n\nSincerely,\n{sitename}", 'give-recurring' ),
				'default_email_header'  => __( 'Subscription Donation Cancelled', 'give-recurring' ),
			) );

			add_action( 'give_subscription_cancelled', array( $this, 'setup_email_notification' ), 10, 2 );
		}

		/**
		 * Get email subject.
		 *
		 * @since 1.8.3
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_subject( $form_id = 0 ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				'subscription_cancelled_admin_subject',
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
				'subscription_cancelled_admin_header',
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
		 * Get email message.
		 *
		 * @since 1.8.3
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = 0 ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				'subscription_cancelled_admin_message',
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the message.
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
		 * Plugin settings.
		 *
		 * @since 1.8.3
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
						'name'    => __( 'Subscription Cancelled Subject', 'give-recurring' ),
						'id'      => 'subscription_cancelled_admin_subject',
						'desc'    => __( 'Enter the subject line of the email sent when a subscription is cancelled.', 'give-recurring' ),
						'type'    => 'text',
						'default' => $this->config['default_email_subject'],
					),
					array(
						'name'    => __( 'Subscription Cancelled Header', 'give-recurring' ),
						'id'      => 'subscription_cancelled_admin_header',
						'desc'    => __( 'Enter the header of the email sent when a subscription is cancelled.', 'give-recurring' ),
						'type'    => 'text',
						'default' => $this->config['default_email_header'],
					),
					array(
						'name'    => __( 'Subscription Cancelled Message', 'give-recurring' ),
						'id'      => 'subscription_cancelled_admin_message',
						'desc'    => __( 'Enter the email message that is sent to users when a subscription is cancelled. HTML is accepted. Available template tags: ', 'give-recurring' ) . $this->get_allowed_email_tags( true ),
						'type'    => 'wysiwyg',
						'default' => $this->config['default_email_message'],
					),
				)
			);

			// Recipient field.
			$settings[] = Give_Email_Setting_Field::get_recipient_setting_field( $this, $form_id, Give_Email_Notification_Util::has_recipient_field( $this ) );

			// $settings[] = Give_Email_Setting_Field::get_email_content_type_field( $this, $form_id );
			$settings[] = Give_Email_Setting_Field::get_preview_setting_field( $this, $form_id );
			$settings   = Give_Email_Setting_Field::add_section_end( $this, $settings );

			return $settings;
		}

		/**
		 * Send subscription cancelation email notifications to admin.
		 *
		 * @since  1.8.3
		 *
		 * @access public
		 *
		 * @param int               $subscription_id
		 * @param Give_Subscription $subscription
		 *
		 * @return bool
		 */
		public function setup_email_notification( $subscription_id, Give_Subscription $subscription ) {
			$this->setup_email_data();

			$sent = $this->send_email_notification( array(
				'subscription_id' => $subscription_id,
				'payment_id'      => $subscription->parent_payment_id,
				'user_id'         => $subscription->donor->user_id,
				'form_id'         => give_get_payment_form_id( $subscription->parent_payment_id ),
			) );

			if ( $sent ) {
				Give_Recurring_Emails::log_recurring_email( 'cancelled-admin', $subscription, $this->get_email_subject() );
			}

			return $sent;
		}
	}

endif; // End class_exists check

return Give_Subscription_Cancelled_Admin_Email::get_instance();
