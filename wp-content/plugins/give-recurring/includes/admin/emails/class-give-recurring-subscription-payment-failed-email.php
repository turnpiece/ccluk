<?php
/**
 * Donor Subscription Payment Failed Email Notification.
 *
 * @package    Give-Recurring
 * @subpackage Classes/Emails
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.9.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for class Give_Recurring_Subscription_Payment_Failed_Email exists?
 *
 * @since 1.9.0
 */
if ( ! class_exists( 'Give_Recurring_Subscription_Payment_Failed_Email' ) ) :
	/**
	 * Class Give_Recurring_Subscription_Payment_Failed_Email
	 *
	 * @since 1.9.0
	 */
	class Give_Recurring_Subscription_Payment_Failed_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @access public
		 * @since  1.9.0
		 */
		public function init() {

			$this->load(
				array(
					'id'                           => 'donor-subscription-payment-failed',
					'label'                        => __( 'Subscription Payment Failed Email', 'give-recurring' ),
					'description'                  => __( 'Sent to designated recipient(s) when the renewal payments of the donor fails due to card declined or expired', 'give-recurring' ),
					'recipient_group_name'         => __( 'Donor', 'give-recurring' ),
					'form_metabox_setting'         => false,
					'has_recipient_field'          => false,
					'has_preview_header'           => true,
					'notification_status'          => 'enabled',
					'notification_status_editable' => false,
					'email_tag_context'            => array( 'donor', 'general' ),
					'default_email_subject'        => sprintf(
						/* translators: %s: site name */
						esc_attr__( '[%s] Subscription Payment Failed', 'give-recurring' ),
						get_bloginfo( 'name' )
					),
					'default_email_message'        => $this->get_default_email_message(),
					'default_email_header'         => __( 'Subscription Payment Failed', 'give-recurring' ),
				)
			);

			// Setup action hook.
			add_action(
				"give_{$this->config['id']}_email_notification",
				array( $this, 'setup_email_notification' ),
				10,
				2
			);

			add_filter(
				'give_email_preview_header',
				array( $this, 'email_preview_header' ),
				10,
				2
			);
		}

		/**
		 * Get setting fields
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @param int $form_id Donation Form ID.
		 *
		 * @return array
		 */
		public function get_setting_fields( $form_id = null ) {

			$settings_field       = Give_Email_Setting_Field::get_setting_fields( $this, $form_id );
			$settings_field_end[] = array_pop( $settings_field );
			$supported_gateways   = wp_list_pluck( give_get_enabled_payment_gateways(), 'admin_label' );
			$default_gateways     = array();

			// Ensure that the enabled Stripe gateways are supported excluding Stripe iDEAL.
			foreach ( $supported_gateways as $gateway_slug => $gateway_name ) {
				if ( 'stripe' !== substr( $gateway_slug, 0, 6 ) || 'stripe_ideal' === $gateway_slug ) {
					unset( $supported_gateways[ $gateway_slug ] );
				} else {
					$default_gateways[] = $gateway_slug;
				}
			}

			$supported_gateways_field[] = array(
				'title'      => __( 'Supported Gateways', 'give-recurring' ),
				'desc'       => __( 'This email will be sent to the recurring donations processed via the supported gateways.', 'give-recurring' ),
				'id'         => "{$this->config['id']}_supported_gateways",
				'class'      => 'give-select-chosen give-chosen-settings',
				'type'       => 'multiselect',
				'options'    => $supported_gateways,
				'default'    => $default_gateways,
				'attributes' => array(
					'data-placeholder' => __( 'Select payment gateways', 'give-recurring' ),
				),
			);

			array_splice( $settings_field, '-1', count( $settings_field ), $supported_gateways_field );

			$settings_field = array_merge( $settings_field, $settings_field_end );

			return $settings_field;
		}

		/**
		 * Get default email message.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @return string
		 */
		public function get_default_email_message() {

			$message = esc_attr__( 'Dear {name},', 'give-recurring' ) . "\r\n\r\n";
			$message .= esc_attr__( 'We want to inform you that your recurring payment to {sitename} was unsuccessful. Your ongoing support is important to us. To continue your recurring donation, please update your payment method by clicking on this link:', 'give-recurring' ) . "\r\n\r\n";
			$message .= "{update_payment_method_link}\r\n\r\n";
			$message .= esc_attr__( 'If no action is taken, your subscription will automatically cancel after a few more attempts. If possible, please renew your payment method before your next scheduled donation on {next_payment_attempt}.', 'give-recurring' ) . "\r\n\r\n";
			$message .= esc_attr__( 'Sincerely,', 'give-recurring' ) . "\r\n\r\n";
			$message .= "{sitename} \r\n";

			/**
			 * Filter the default email message
			 *
			 * @since 1.9.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_default_email_message",
				$message,
				$this
			);
		}

		/**
		 * Send Subscription Payment Failed Email Notification.
		 *
		 * @param object $subscription Subscription Object.
		 * @param object $invoice      Invoice Object from Stripe.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @return void
		 */
		public function setup_email_notification( $subscription, $invoice ) {

			$supported_gateways = give_get_option( "$this->config['id']_supported_gateways", array() );

			if ( in_array( $subscription->gateway, $supported_gateways, true ) ) {
				$this->setup_email_data();

				$this->send_email_notification(
					array(
						'payment_id'           => $subscription->parent_payment_id,
						'subscription_id'      => $subscription->id,
						'donor_id'             => $subscription->donor_id,
						'next_payment_attempt' => $invoice->next_payment_attempt,
					)
				);
			}
		}

		/**
		 * Email Preview Header
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @param string $email_preview_header Email Preview Header.
		 * @param string $email                Email.
		 *
		 * @return string
		 */
		public function email_preview_header( $email_preview_header, $email ) {

			if ( $this->config['id'] === $email->config['id'] ) {
				$email_preview_header = '';
			}

			return $email_preview_header;
		}
	}

endif; // End class_exists check.

return Give_Recurring_Subscription_Payment_Failed_Email::get_instance();
