<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Mail
 *
 * Handle mail sending
 *
 * @since 1.0
 */
abstract class Forminator_Mail {

	/**
	 * Mail recipient
	 * The email to receive the mail
	 *
	 * @var string
	 */
	protected $recipient = '';

	/**
	 * Mail message
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * Mail subject
	 *
	 * @var string
	 */
	protected $subject = '';

	/**
	 * Mail from email
	 *
	 * @var string
	 */
	protected $sender_email = '';

	/**
	 * Mail from name
	 *
	 * @var string
	 */
	protected $sender_name = '';

	/**
	 * Mail headers
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Main constructor
	 *
	 * @since 1.0
	 * @param string $recipient - mail recipient email
	 * @param string $message - mail message
	 * @param string $subject - mail subject
	 */
	public function __construct( $recipient = '', $message = '', $subject = '' ) {
		if ( !empty( $recipient ) && filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) {
			$this->recipient = $recipient;
		}
		if ( !empty( $message ) ) {
			$this->message = $message;
		}
		if ( !empty( $subject ) ) {
			$this->subject = $subject;
		}
		$this->sender_email = $this->get_no_reply_email();
		$this->sender_name 	= get_option( 'blogname' );
		$this->set_headers();
	}

	/**
	 * No reply email
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_no_reply_email() {
		$no_reply_email = "noreply@" . parse_url( get_site_url(), PHP_URL_HOST );
		return apply_filters( 'fominator_noreply_email', $no_reply_email );
	}

	/**
	 * Set recipeint
	 *
	 * @since 1.0
	 * @param string $recipient - recipient email
	 */
	public function set_recipient( $recipient ) {
		if ( filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) {
			$this->recipient = $recipient;
		}
	}

	/**
	 * Set message
	 *
	 * @since 1.0
	 * @param string $message - the mail message
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Set message with vars
	 *
	 * @since 1.0
	 * @param array $message_vars - the mail message array variables
	 * @param string $message - the mail message
	 */
	public function set_message_with_vars( $message_vars, $message ) {
		$this->message 	= str_replace(
			array_keys( $message_vars ),
			array_values( $message_vars ),
			stripslashes( $message )
		);
	}

	/**
	 * Set subject
	 *
	 * @since 1.0
	 * @param string $subject - the mail subject
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;
	}

	/**
	 * Set headers
	 *
	 * @since 1.0
	 * @param array $headers - the mail headers
	 */
	public function set_headers( $headers = array() ) {
		if ( !empty( $headers ) ) {
			$this->headers = $headers;
		} else {
			$this->headers = array(
				'From: ' . $this->sender_name . ' <' . $this->sender_email . '>',
				'Content-Type: text/html; charset=UTF-8'
			);
		}
	}

	/**
	 * Set sender details
	 *
	 * @since 1.0
	 * @param array $sender_details - the sender details
	 * 		( 'email' => 'email', 'name' => 'name' )
	 */
	public function set_sender( $sender_details = array() ) {
		if ( !empty( $sender_details ) ) {
			$this->sender_email = $sender_details['email'];
			$this->sender_name 	= $sender_details['name'];
		}
	}

	/**
	 * Clean mail variables
	 *
	 * @since 1.0
	 */
	private function clean() {
		$subject 		= stripslashes( $this->subject );
		$subject 		= strip_tags( $subject );
		$this->subject 	= $subject;

		$message 		= stripslashes( $this->message );
		$message 		= wpautop( $message );
		$message 		= make_clickable( $message );
		$this->message 	= $message;
	}

	/**
	 * Send mail
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function send() {
		$sent = false;
		if ( !empty( $this->recipient ) && !empty( $this->subject ) && !empty( $this->message )  ) {
			$this->clean();
			$sent = wp_mail( $this->recipient, $this->subject, $this->message, $this->headers );
		}
		return $sent;
	}
}