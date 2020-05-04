<?php

/**
 * Class Give_Renewal_Subscription_Email_Access add support for recurring email access.
 *
 * @since 1.7
 */
class Give_Renewal_Subscriptions_Email_Access extends Give_Email_Notification {
	/**
	 * Create a class instance.
	 *
	 * @access  public
	 */
	public function init() {

		$this->load( array(
			'id'                           => 'subscriptions-email-access',
			'label'                        => __( 'Subscriptions Email Access', 'give-recurring' ),
			'description'                  => __( 'Sent when donors request access to their recurring donations using only their email as verification. (See Settings > General > Access Control)', 'give-recurring' ),
			'notification_status'          => give_get_option( 'email_access', 'disabled' ),
			'form_metabox_setting'         => false,
			'notification_status_editable' => false,
			'email_tag_context'            => 'donor',
			'recipient_group_name'         => __( 'Donor', 'give-recurring' ),
			'default_email_header'         => __( 'Confirm Email', 'give-recurring' ),
			'default_email_subject'        => sprintf( __( 'Please confirm your email for %s', 'give-recurring' ), get_bloginfo( 'url' ) ),
			'default_email_message'        => $this->get_default_email_message(),
			'notices'                      => array(
				'non-notification-status-editable' => sprintf(
					'%1$s <a href="%2$s">%3$s &raquo;</a>',
					__( 'This notification is automatically toggled based on whether the email access is enabled or not.', 'give-recurring' ),
					esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control' ) ),
					__( 'Edit Setting', 'give-recurring' )
				),
			),
			'preview_email_tags_values'    => array(
				'email_access_link' => sprintf(
					'<a href="%1$s">%2$s</a>',
					add_query_arg(
						array(
							'give_nl' => uniqid(),
						),
						give_get_subscriptions_page_uri()
					),
					__( 'View your subscription donations &raquo;', 'give-recurring' )
				),
			)
		) );

		add_filter( 'give_email-access_get_email_subject', array(
			$this,
			'email_access_default_email_subject'
		), 10, 2 );

		add_filter( 'give_email-access_get_default_email_message', array(
			$this,
			'email_access_default_email_message'
		), 10, 2 );

		add_filter( 'give_email-access_get_email_attachments', array(
			$this,
			'email_access_default_email_attachments'
		), 10, 2 );

		add_filter( 'give_email-access_get_email_content_type', array(
			$this,
			'email_access_default_email_content_type'
		), 10, 2 );

		add_filter( 'give_email_tag_email_access_link', array( $this, 'email_access_tag_link' ), 10, 2 );

		add_filter( 'give_email_access_welcome_message', array( $this, 'email_access_welcome_message' ), 10, 1 );

		add_filter( 'give_email_access_mail_send_notice', array( $this, 'email_access_mail_send' ), 10, 1 );

		add_filter( 'give_email_access_requests_exceed_notice', array(
			$this,
			'email_access_requests_detected'
		), 10, 2 );
	}

	/**
	 * Modify email access donation request detected.
	 *
	 * @since 1.7
	 *
	 * @param string $message email access request detected message.
	 * @param int    $value   email access request number of times.
	 *
	 * @return string email access request detected message.
	 */
	public function email_access_requests_detected( $message, $value ) {

		if ( give_recurring_is_subscriptions_page() ) {
			$message = sprintf(
				__( 'Too many access email requests detected. Please wait %s before requesting a new subscription donations access link.', 'give-recurring' ),
				sprintf( _n( '%s minute', '%s minutes', $value, 'give-recurring' ), $value )
			);
		}

		return $message;
	}

	/**
	 * Modify access donation welcome message.
	 *
	 * @since 1.7
	 *
	 * @param string $message email access welcome message.
	 *
	 * @return string email access welcome message.
	 */
	public function email_access_welcome_message( $message ) {
		if ( give_recurring_is_subscriptions_page() ) {
			$message = __( 'Please verify your email to access your subscription donations.', 'give-recurring' );
		}

		return $message;
	}

	/**
	 * Change email access mail send notifications.
	 *
	 * @since 1.7
	 *
	 * @param string $message email access notifications message.
	 *
	 * @return string $message email access notifications message.
	 */
	public function email_access_mail_send( $message ) {
		if ( give_recurring_is_subscriptions_page() ) {
			$message = __( 'Please check your email and click on the link to access your complete subscription donations.', 'give-recurring' );
		}

		return $message;
	}

	/**
	 * Filter the {email_access_link} email template tag output.
	 *
	 * @since 1.7
	 *
	 * @param string $email_access_link email access link.
	 * @param array  $tag_args          email tags.
	 *
	 * @return string $email_access_link email access link.
	 */
	public function email_access_tag_link( $email_access_link, $tag_args ) {

		if ( ! $this->is_recurring_email_access_page() ) {
			return $email_access_link;
		}

		// Set email access link if donor exist.
		if ( ! empty( $tag_args['donor_id'] ) && $tag_args['verify_key'] ) {
			$donor_id = absint( $tag_args['donor_id'] );

			$access_url = add_query_arg(
				array(
					'give_nl' => $tag_args['verify_key'],
				),
				give_get_subscriptions_page_uri()
			);

			// Add donation id to email access url, if it exists.
			if ( ! empty( $_GET['donation_id'] ) ) {
				$access_url = add_query_arg(
					array(
						'donation_id' => give_clean( $_GET['donation_id'] ),
					),
					$access_url
				);
			}

			$content_type = $this->get_email_content_type( null );
			if ( empty( $content_type ) || 'text/html' === $content_type ) {
				$email_access_link = sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( $access_url ),
					__( 'View your subscription donations &raquo;', 'give-recurring' )
				);

			} else {

				$email_access_link = sprintf(
					'%1$s: %2$s',
					__( 'View your subscription donations', 'give-recurring' ),
					esc_url( $access_url )
				);
			}
		}

		return $email_access_link;
	}

	/**
	 * Check is recurring email access page.
	 *
	 * @since 1.7
	 *
	 * @return bool $value True if is email access page else false.
	 */
	public function is_recurring_email_access_page() {
		$access_page = empty( $_POST['give_access_page'] ) ? false : absint( $_POST['give_access_page'] );
		$value       = false;

		if ( give_recurring_subscriptions_page_id() === $access_page && give_get_option( 'history_page' ) !== $access_page ) {
			$value = true;
		}

		/**
		 * Filter to modify is email recurring email access page.
		 *
		 * @since 1.7
		 *
		 * @param bool $value True if it's email access page.
		 *
		 * @return bool $value True if it's email access page.
		 */
		return (bool) apply_filters( 'give_recurring_is_email_access_page', $value );
	}

	/**
	 * Filter to alter email message body.
	 *
	 * @since 1.7
	 *
	 * @param string $message Email Access Message Body.
	 *
	 * @return string $message Email Access Message Body.
	 */
	public function email_access_default_email_message( $message ) {

		if ( $this->is_recurring_email_access_page() ) {
			return $this->get_email_message();
		}

		return $message;
	}

	/**
	 * Filter to alter email message attachments.
	 *
	 * @since 1.7
	 *
	 * @param string $attachments Email Access Message attachments.
	 *
	 * @return string $attachments Email Access Message attachments.
	 */
	public function email_access_default_email_attachments( $attachments ) {

		if ( $this->is_recurring_email_access_page() ) {
			return $this->get_email_attachments();
		}

		return $attachments;
	}

	/**
	 * Filter to alter email message subject.
	 *
	 * @since 1.7
	 *
	 * @param string $subject Email Access Message subject.
	 *
	 * @return string $subject Email Access Message subject.
	 */
	public function email_access_default_email_subject( $subject ) {

		if ( $this->is_recurring_email_access_page() ) {
			return $this->get_email_subject();
		}

		return $subject;
	}

	/**
	 * Filter to alter email message subject.
	 *
	 * @since 1.7
	 *
	 * @param string $content_type Email Access Message content type.
	 *
	 * @return string $content_type Email Email Access Message content type.
	 */
	public function email_access_default_email_content_type( $content_type ) {

		if ( $this->is_recurring_email_access_page() ) {
			return $this->get_email_content_type( null );
		}

		return $content_type;
	}

	/**
	 * Get default email message.
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @return string
	 */
	public function get_default_email_message() {
		$message = sprintf(
			           __( 'Please click the link to access your subscription donations on <a target="_blank" href="%1$s">%1$s</a>. If you did not request this email, please contact <a href="mailto:%2$s">%2$s</a>.', 'give-recurring' ),
			           get_bloginfo( 'url' ),
			           get_bloginfo( 'admin_email' )
		           ) . "\n\n";
		$message .= '{email_access_link}' . "\n\n";
		$message .= "\n\n";
		$message .= __( 'Sincerely,', 'give-recurring' ) . "\n";
		$message .= get_bloginfo( 'name' ) . "\n";

		/**
		 * Filter the new donation email message
		 *
		 * @since 1.7
		 *
		 * @param string $message
		 */
		return apply_filters( "give_{$this->config['id']}_get_default_email_message", $message, $this );
	}

	/**
	 * Set email data
	 *
	 * @since 1.7
	 *
	 * @param int|null $form_id
	 *
	 * @return string
	 */
	public function get_email_header( $form_id = null ) {

		/**
		 * Filter to modify header of email access.
		 *
		 * @since 1.7
		 *
		 * @param string $header Header text for email
		 */
		$header = apply_filters( "give_{$this->config['id']}_email_access_token_heading", parent::get_email_header( $form_id ) );

		return $header;
	}
}

return Give_Renewal_Subscriptions_Email_Access::get_instance();
