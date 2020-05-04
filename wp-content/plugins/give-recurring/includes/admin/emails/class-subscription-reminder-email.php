<?php

/**
 * Class Give_Subscription_Reminder_Email
 */
class Give_Subscription_Reminder_Email extends Give_Email_Notification {


	/**
	 * Holds all the settings fields
	 *
	 * @since 1.7
	 *
	 * @var array
	 */
	private $settings;


	/**
	 * Array of reminder periods for both
	 * Renewal and renewal reminders.
	 *
	 * @since 1.7
	 *
	 * @var array
	 */
	private $renewal_reminder_periods;

	/**
	 * Array of reminder periods for both
	 * Renewal and Expiration reminders.
	 *
	 * @since 1.7
	 *
	 * @var array
	 */
	private $expiration_reminder_periods;


	/**
	 * Stores the value of the query param
	 * 'notice_type'.
	 *
	 * Notice types can be 'renewal|expiration'.
	 *
	 * @since 1.7
	 *
	 * @var string|boolean
	 */
	private $notice_type;


	/**
	 * Stores the value of the query param
	 * 'notice_action'.
	 *
	 * Notice actions can be 'add|edit|delete'.
	 *
	 * @since 1.7
	 *
	 * @var string|boolean
	 */
	private $notice_action;


	/**
	 * Stores the value of the query param
	 * 'notice_id'.
	 *
	 * @since 1.7
	 *
	 * @var string|boolean
	 */
	private $notice_id;


	/**
	 * Stores the number of notices.
	 *
	 * @since 1.7
	 *
	 * @var integer
	 */
	private $notice_count;


	/**
	 * Constructor function.
	 */
	public function __construct() {

		// Get all the reminder renewal periods.
		$grr = Give_Recurring_Renewal_Reminders::get_instance();
		$gre = Give_Recurring_Expiration_Reminders::get_instance();

		$this->notice_type                 = $this->get_notice_type();
		$this->notice_action               = $this->get_notice_action();
		$this->notice_id                   = $this->get_notice_id();
		$this->renewal_reminder_periods    = $grr->get_renewal_notice_periods();
		$this->expiration_reminder_periods = $gre->get_expiration_notice_periods();
		$this->notice_count                = count( get_option( 'give_recurring_reminder_notices', array() ) );
		$this->custom_save_logic();
	}

	/**
	 * Create a class instance.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function init() {

		$this->load_notice_edit_screen();

		// Registering custom Give Admin Fields.
		add_action( 'give_admin_field_renewal_reminder_buttons', array( $this, 'render_renewal_reminder_buttons' ) );
		add_action( 'give_admin_field_expiration_reminder_buttons', array( $this, 'render_expiration_reminder_buttons' ) );
		add_action( 'give_admin_field_reminder_period_select', array( $this, 'render_reminder_period_select' ) );
		add_action( 'give_admin_field_save_reminder_options', array( $this, 'render_save_button' ) );

		// Custom function to get value of setting field.
		add_filter( 'give_admin_field_get_value', array( $this, '__get_options' ), 10, 4 );

		add_action( 'wp_ajax_give_toggle_reminder', array( $this, 'toggle_reminder' ) );

		add_filter( 'give_hide_save_button_on_email_admin_setting_page', array( $this, 'disable_settings_save_button' ) );
		add_filter( "give_email_list_render_{$this->config['id']}_email_content_type", array( $this, 'render_email_content_type_column' ) );

		// Notice Actions.
		$this->action_notice_delete();

	}

	/**
	 * Get notices setting field values
	 * Note: only for internal use
	 *
	 * @param mixed  $option_value
	 * @param string $option_name
	 * @param string $field_id
	 * @param mixed  $default_value
	 *
	 * @return mixed
	 */
	public function __get_options( $option_value, $option_name, $field_id, $default_value ) {
		if ( ! $this->is_renewal_settings() ) {
			return $option_value;
		}

		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$notice_id      = $this->get_notice_id();

		// Exit if not editing or add reminder.
		if ( ! $notice_id || ! array_key_exists( $notice_id, $stored_notices ) ) {
			return $option_value;
		}

		switch ( $field_id ) {
			case 'email-reminder-period':
				if ( array_key_exists( 'send_period', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['send_period'];
				}
				break;

			case 'subscription_renewal_reminder_header':
				if ( array_key_exists( 'header', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['header'];
				}
				break;

			case 'subscription_renewal_reminder_subject':
				if ( array_key_exists( 'subject', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['subject'];
				}
				break;

			case 'subscription_renewal_reminder_message':
				if ( array_key_exists( 'message', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['message'];
				}
				break;

			case 'excluded_gateways':
				if ( array_key_exists( 'gateway', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['gateway'];
				}
				break;

			case 'content_type_select':
				if ( array_key_exists( 'content_type', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['content_type'];
				}
				break;

			case 'email_notice_state':
				if ( array_key_exists( 'status', $stored_notices[ $notice_id ] ) ) {
					$option_value = $stored_notices[ $notice_id ]['status'];
				}
				break;
		}

		return $option_value;
	}

	/**
	 * Get email subject.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return string
	 */
	public function get_email_subject( $form_id = 0 ) {
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$subject        = $stored_notices[ $this->notice_id ]['subject'];

		$subject = ! empty( $subject ) ? $subject : $this->config['default_email_subject'];

		/**
		 * Filter the subject.
		 */
		return apply_filters(
			"give_{$this->config['id']}_get_email_subject",
			$subject,
			$this
		);
	}

	/**
	 * Get email header.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return string
	 */
	public function get_email_header( $form_id = 0 ) {
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$header         = $stored_notices[ $this->notice_id ]['header'];

		$header = ! empty( $header ) ? $header : $this->config['default_email_header'];

		/**
		 * Filter the header.
		 */
		return apply_filters(
			"give_{$this->config['id']}_get_email_header",
			$header,
			$this
		);
	}

	/**
	 * Get email content type.
	 *
	 * @access public
	 *
	 * @param $form_id
	 *
	 * @return string
	 */
	public function get_email_content_type( $form_id ) {
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$content_type   = $stored_notices[ $this->notice_id ]['content_type'];

		$content_type = ! empty( $content_type ) ? $content_type : $this->config['content_type'];

		/**
		 * Filter the email content type.
		 */
		return apply_filters(
			"give_{$this->config['id']}_get_email_content_type",
			$content_type,
			$this,
			$form_id
		);
	}


	/**
	 * Get email message.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return string
	 */
	public function get_email_message( $form_id = 0 ) {
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$message        = $stored_notices[ $this->notice_id ]['message'];

		$message = ! empty( $message ) ? $message : $this->config['default_email_message'];


		/**
		 * Filter the message.
		 */
		return apply_filters(
			"give_{$this->config['id']}_get_email_message",
			$message,
			$this
		);
	}


	/**
	 * Get notification status.
	 *
	 * @since  1.7
	 *
	 * @param int $form_id
	 *
	 * @return bool
	 */
	public function get_notification_status( $form_id = null ) {

		$renewal_reminders    = give_is_setting_enabled( give_get_option( 'recurring_send_renewal_reminders' ), 'enabled' );
		$expiration_reminders = give_is_setting_enabled( give_get_option( 'recurring_send_expiration_reminders' ), 'enabled' );

		if ( $renewal_reminders || $expiration_reminders ) {
			return 'enabled';
		}

		return 'disabled';
	}


	/**
	 * This populates the email fields depending on
	 * whether it is the 'edit' or the 'add' screen.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function load_notice_edit_screen() {

		$fields = array();

		// Verify nonce.
		if ( $this->is_notice_action('add' ) ) {
			$this->verify_action_nonce( 'add' );
		}

		if ( 'renewal' === $this->notice_type ) {
			$fields = array(
				'default_email_header'  => esc_html__( 'Subscription Reminder', 'give-recurring' ),
				'default_email_subject' => __( 'Subscription Renewal Reminder', 'give-recurring' ),
				'default_email_message' => __( 'Dear', 'give-recurring' ) . " {name},\n\n" . __( "Your subscription's renewal date is approaching. Here are the subscription details for your records:\n\n<strong>Subscription:</strong> {donation} - {amount}\n<strong>Subscription Frequency:</strong> {subscription_frequency} \n<strong>Completed Donations:</strong> {subscriptions_completed} \n<strong>Payment Method:</strong> {payment_method}\n\nSincerely,\n{sitename}", 'give-recurring' ),
			);
		} else if ( 'expiration' === $this->notice_type ) {
			$fields = array(
				'default_email_header'  => esc_html__( 'Subscription Reminder', 'give-recurring' ),
				'default_email_subject' => __( 'Subscription Expiration reminder', 'give-recurring' ),
				'default_email_message' => __( 'Dear', 'give-recurring' ) . " {name},\n\n" . __( "Your subscription's expiration date is approaching. Here are the subscription details for your records:\n\n<strong>Subscription:</strong> {donation} - {amount}\n<strong>Subscription Frequency:</strong> {subscription_frequency} \n<strong>Completed Donations:</strong> {subscriptions_completed} \n<strong>Payment Method:</strong> {payment_method}\n\nSincerely,\n{sitename}", 'give-recurring' ),
			);
		}


		// Load already saved setting if any.
		if ( $this->is_notice_action( 'edit' ) ) {
			$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
			$notice_id      = $this->get_notice_id();

			$fields = array(
				'default_email_header'  => $stored_notices[ $notice_id ]['header'],
				'default_email_subject' => $stored_notices[ $notice_id ]['subject'],
				'default_email_message' => $stored_notices[ $notice_id ]['message'],
			);
		}

		/**
		 * Set up the default values for various fields.
		 */
		$fields = wp_parse_args(
			$fields, array(
				'id'                           => 'subscription-reminder',
				'label'                        => __( 'Subscription Reminder Email', 'give-recurring' ),
				'description'                  => __( 'Check this option if you would like donors to receive an email when a subscription is approaching its renewal or expiration. The email will send a reminder about when the subscription is about to renew or expire.', 'give-recurring' ),
				'recipient_group_name'         => __( 'Donor', 'give-recurring' ),
				'form_metabox_setting'         => false,
				'has_recipient_field'          => false,
				'notification_status_editable' => false,
			)
		);

		$this->load( $fields );

		$this->config['has_preview_header'] = true;
		$this->config['has_preview']        = false;
	}

	/**
	 * Plugin settings.
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array
	 */
	public function get_setting_fields( $form_id = 0 ) {

		$this->settings   = array();
		$renewal_settings = $this->is_renewal_settings();

		// Start of the section.
		$this->settings[] = Give_Email_Setting_Field::get_section_start( $this, $form_id );

		if ( $renewal_settings ) {
			switch ( $this->notice_action ) {
				case 'add':
				case 'edit':
					$this->add_email_fields_for_renewal_reminder();
					break;

				default:
					$this->register_enable_renewal_reminder();
					$this->register_add_new_renewal_reminder_button();

					$this->register_enable_expiration_reminder();
					$this->register_add_new_expiration_reminder_button();
					break;
			}
		}

		// End of the section.
		$this->settings = Give_Email_Setting_Field::add_section_end( $this, $this->settings );

		return $this->settings;
	}


	/**
	 * Deletes a Renewal|Expiration reminder and updates
	 * the option. After deletion, it will redirect you
	 * to the main screen.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function action_notice_delete() {

		/**
		 * If notice ID is more than the number of existing
		 * notices, then return.
		 */
		$renewal_settings = $this->is_renewal_settings();

		if ( 'delete' !== $this->notice_action || ! $renewal_settings ) {
			return;
		}

		$this->verify_action_nonce( 'delete' );

		// Do not process wrong notice id.
		if ( $this->notice_id >= $this->notice_count ) {
			give_die();
		}

		// Get the stored email notices.
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );

		// Delete 1 email notice as per index.
		unset( $stored_notices[ intval( $this->notice_id ) ] );

		// Re-index the array.
		$stored_notices = array_values( $stored_notices );

		if ( empty( $stored_notices ) ) {

			// Delete the option.
			delete_option( 'give_recurring_reminder_notices' );
		} else {

			// Update the option.
			update_option( 'give_recurring_reminder_notices', $stored_notices );
		}

		// Redirect to the notice listing page.
		if ( wp_get_referer() ) {
			wp_safe_redirect( wp_get_referer() );
			exit;
		}
	}


	/**
	 * This function is a utility function. It checks if
	 * you're on the 'emails' tab and the 'subscription-reminder'
	 * section.
	 *
	 * @since 1.7
	 *
	 * @return boolean 'true' if the conditions are met, 'false' otherwise.
	 */
	public function is_renewal_settings() {
		return Give_Admin_Settings::is_setting_page( 'emails', 'subscription-reminder' );
	}


	/**
	 * Get value of the 'notice_action' query param.
	 *
	 * @since 1.7
	 *
	 * @return boolean|string 'false' if 'notice_action', value of 'notice_action' if true.
	 */
	public function get_notice_action() {

		if ( isset( $_GET['notice_action'] ) ) {
			return give_clean( $_GET['notice_action'] );
		}

		return false;
	}


	/**
	 * Get value of the 'notice_type' query param.
	 *
	 * @since 1.7
	 *
	 * @return boolean|string 'false' if 'notice_type', value of 'notice_type' if true.
	 */
	public function get_notice_type() {

		if ( isset( $_GET['notice_type'] ) ) {
			return give_clean( $_GET['notice_type'] );
		}

		return false;
	}


	/**
	 * Get value of the 'notice_id' query param.
	 *
	 * @since 1.7
	 *
	 * @return boolean|string 'false' if 'notice_id', value of 'notice_id' if true.
	 */
	public function get_notice_id() {

		if ( isset( $_GET['notice_id'] ) ) {
			return give_clean( $_GET['notice_id'] );
		}

		return false;
	}


	/**
	 * Adds radio buttons to enable/disable sending out
	 * Renewal Reminder Emails.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function register_enable_renewal_reminder() {

		$this->settings[] = array(
			'name'    => 'recurring_send_renewal_reminders',
			'desc'    => __( 'Globally Enable/Disable the Renewal Reminder feature.', 'give-recurring' ),
			'id'      => 'recurring_send_renewal_reminders',
			'title'   => __( 'Send Renewal Reminder', 'give-recurring' ),
			'type'    => 'radio_inline',
			'class'   => 'send-renewal-reminders',
			'options' => array(
				'enabled'  => __( 'Enable', 'give-recurring' ),
				'disabled' => __( 'Disable', 'give-recurring' ),
			),
			'default' => 'disabled',
		);
	}


	/**
	 * Adds the 'Add New Reminder' button which allows
	 * you to add a new Renewal Reminder.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function register_add_new_renewal_reminder_button() {

		$this->settings[] = array(
			'name'  => 'add_renewal_reminder_button',
			'id'    => 'add-renewal-reminder-button',
			'title' => __( 'Renewal Reminder Emails', 'give-recurring' ),
			'type'  => 'renewal_reminder_buttons',
			'class' => 'button-secondary',
		);
	}


	/**
	 * Adds radio buttons to enable/disable sending out
	 * Renewal Expiration Emails.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function register_enable_expiration_reminder() {

		$this->settings[] = array(
			'name'    => 'recurring_send_expiration_reminders',
			'desc'    => __( 'Globally Enable/Disable the Expiration Reminder feature.', 'give-recurring' ),
			'id'      => 'recurring_send_expiration_reminders',
			'title'   => __( 'Send Expiration Reminder', 'give-recurring' ),
			'type'    => 'radio_inline',
			'class'   => 'send-renewal-reminders',
			'options' => array(
				'enabled'  => __( 'Enable', 'give-recurring' ),
				'disabled' => __( 'Disable', 'give-recurring' ),
			),
			'default' => 'disabled',
		);
	}


	/**
	 * Adds the 'Add New Expiration' button which allows
	 * you to add a new Renewal Expiration.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function register_add_new_expiration_reminder_button() {

		$this->settings[] = array(
			'name'  => 'add_expiration_reminder_button_test',
			'id'    => 'add-expiration-reminder-button',
			'title' => __( 'Expiration Reminder Emails', 'give-recurring' ),
			'type'  => 'expiration_reminder_buttons',
			'class' => 'button-secondary',
		);
	}


	/**
	 * Renders the HTML required to a list of renewal
	 * notices and an 'Add New Renewal' button at the
	 * bottom of the list.
	 *
	 * @param array $field Array of custom field properties.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function render_renewal_reminder_buttons( $field ) {
		$remind_for = 'renewal';

		include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/view/add-reminder-buttons.php';
	}


	/**
	 * Renders the HTML required to a list of expiration
	 * notices and an 'Add New Expiration' button at the
	 * bottom of the list.
	 *
	 * @param array $field Array of custom field properties.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function render_expiration_reminder_buttons( $field ) {
		$remind_for = 'expiration';

		include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/view/add-reminder-buttons.php';
	}


	/**
	 * Registers all the fields required for the email to
	 * be sent out for Renewal|Expiration reminders.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function add_email_fields_for_renewal_reminder() {

		/**
		 * If notice id is more than the number of notices
		 * existing, then return.
		 */
		if ( 'edit' === $this->notice_action && $this->notice_id >= $this->notice_count ) {
			return;
		}

		/**
		 * Radio buttons to Enable/Disable the email notices.
		 * Disabled email notices will not be sent out.
		 */
		$this->settings[] = array(
			'title'   => __( 'Notification', 'give-recurring' ),
			'desc'    => __( 'Enable/Disable this email notice.', 'give-recurring' ),
			'id'      => 'email_notice_state',
			'type'    => 'radio_inline',
			'options' => array(
				'enabled'  => __( 'Enable', 'give-recurring' ),
				'disabled' => __( 'Disable', 'give-recurring' ),
			),
			'default' => 'enabled',
		);

		/**
		 * Adds a select field which has a list of time periods.
		 * These time period tells when to send out the email.
		 * The CRON job will use the date set here to send out
		 * the emails.
		 */
		$this->settings[] = array(
			'title' => __( 'Period', 'give-recurring' ),
			'type'  => 'reminder_period_select',
			'id'    => 'email-reminder-period',
			'name'  => 'email-reminder-period',
		);

		/**
		 * Adds a text field to the screen with some default
		 * value. Content in this field are sent as header
		 * in the email.
		 */
		$this->settings[] = array(
			'name'    => __( 'Subscription Renewal Header', 'give-recurring' ),
			'id'      => 'subscription_renewal_reminder_header',
			'desc'    => __( 'Enter the header line of the email sent when a subscription is completed.', 'give-recurring' ),
			'type'    => 'text',
			'default' => $this->config['default_email_header'],
		);

		/**
		 * Adds a text field to the screen with some default
		 * value. Content in this field are sent as subject
		 * in the email.
		 */
		$this->settings[] = array(
			'name'    => __( 'Subscription Renewal Subject', 'give-recurring' ),
			'id'      => 'subscription_renewal_reminder_subject',
			'desc'    => __( 'Enter the subject line of the email sent when a subscription is completed.', 'give-recurring' ),
			'type'    => 'text',
			'default' => $this->config['default_email_subject'],
		);

		/**
		 * Adds a wysiwyg editor to the screen with some
		 * default value. Contents in this field are sent
		 * as message body in the email.
		 */
		$this->settings[] = array(
			'name'    => __( 'Subscription Renewal Message', 'give-recurring' ),
			'id'      => 'subscription_renewal_reminder_message',
			'desc'    => __( 'Enter the email message that is sent to users when a subscription is completed. HTML is accepted. Available template tags: ', 'give-recurring' ) . $this->get_allowed_email_tags( true ),
			'type'    => 'wysiwyg',
			'default' => $this->config['default_email_message'],
		);

		/**
		 * Adds a dropdown with options whether to format
		 * the email as plaintext or leave it as HTML.
		 */
		$this->settings[] = array(
			'id'      => 'content_type_select',
			'name'    => esc_html__( 'Email Content Type', 'give-recurring' ),
			'desc'    => __( 'Choose email type.', 'give-recurring' ),
			'type'    => 'select',
			'options' => array(
				'text/html'  => Give_Email_Notification_Util::get_formatted_email_type( 'text/html' ),
				'text/plain' => Give_Email_Notification_Util::get_formatted_email_type( 'text/plain' ),
			),
			'default' => 'text/html',
		);

		/**
		 * Adds a checbox to use this email notice template
		 * only for payments made via the offline method.
		 */
		$this->settings[] = array(
			'title'      => __( 'Exclude Gateways', 'give-recurring' ),
			'desc'       => __( 'This email won\'t be sent if the donations are made using the selected payment methods.', 'give-recurring' ),
			'id'         => 'excluded_gateways',
			'type'       => 'multiselect',
			'options'    => $this->get_payment_gateways(),
			'default'    => '',
			'attributes' => array(
				'data-placeholder' => __( 'Select payment gateways', 'give-recurring' ),
			),
		);

		/**
		 * This adds a custom save button which has the text
		 * as 'Add|Update Renewal|Expiration Reminder' depending
		 * on the type of page you're on.
		 */
		$this->settings[] = array(
			'name' => 'save_reminder_options',
			'type' => 'save_reminder_options',
		);
	}


	/**
	 * Returns all the payment gateways that are active.
	 *
	 * @since 1.7
	 *
	 * @return array
	 */
	public function get_payment_gateways() {
		$registered_gateways = give_get_payment_gateways();
		$gateways            = array();

		foreach ( $registered_gateways as $key => $value ) {
			$gateways[ $key ] = $value['admin_label'];
		}

		return $gateways;
	}


	/**
	 * Returns with the list of payment gateways indicating which
	 * gateways are in the excluded list.
	 *
	 * @param integer $notice_id      ID of the email notice.
	 * @param array   $stored_notices Array of all the notices.
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public function get_gateway_info( $notice_id, $stored_notices ) {

		// Get all the gateways.
		$all_gateways = $this->get_payment_gateways();
		$all_count    = count( $all_gateways );

		/**
		 * Get all the gateways that are excluded.
		 *
		 * This will only return IDs of excluded
		 * gateways.
		 */
		$excluded_gateways = ! empty( $stored_notices[ $notice_id ]['gateway'] ) ? $stored_notices[ $notice_id ]['gateway'] : array();
		$excluded_count    = count( $excluded_gateways );

		$excluded_gateway_labels = array();

		// Get labels of excluded gateways.
		foreach ( $all_gateways as $key => $value ) {

			if ( in_array( $key, $excluded_gateways ) ) {
				$excluded_gateway_labels[ $key ] = $value;
			}
		}

		/**
		 * If no gateways are added to the exclude gateways list.
		 */
		if ( empty( $excluded_gateway_labels ) ) {
			return sprintf( esc_html__( 'All', 'give-recurring' ) );
		}

		/**
		 * Message if the excluded gateway count is less than or equal to
		 * the count of all gateways.
		 */
		if ( count( $excluded_gateways ) <= count( $all_gateways ) / 2 ) {
			return sprintf( __( 'All, except %s.', 'give-recurring' ), implode( ', ', array_values( $excluded_gateway_labels ) ) );
		}

		/**
		 * Message if the excluded gateway count more than the count of
		 * all gateways.
		 */
		if ( count( $excluded_gateways ) > count( $all_gateways ) / 2 ) {
			return sprintf( __( '%s only.', 'give-recurring' ), implode( ', ', array_diff_assoc( $all_gateways, $excluded_gateway_labels ) ) );
		}
	}


	/**
	 * Renders the HTML required to a display the list of
	 * periods at which reminder emails need to be sent out.
	 *
	 * @param array $field Array of custom field properties.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function render_reminder_period_select( $field ) {
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );

		include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/view/reminder-send-periods.php';
	}


	/**
	 * Renders the HTML to display a custom save button.
	 *
	 * @param array $field Array of custom field properties.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function render_save_button( $field ) {
		$action = ( 'add' === $this->notice_action ) ? __( 'Add', 'give-recurring' ) : __( 'Update', 'give-recurring' );
		$type   = ( 'renewal' === $this->notice_type ) ? __( 'Renewal Reminder', 'give-recurring' ) : __( 'Expiration Reminder', 'give-recurring' );
		?>
		<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . esc_attr( $field['wrapper_class'] ) . '"' : ''; ?>>
			<td scope="row" class="titledesc">
				<input type="submit" name="custom-save-button" class="button-primary subscription-options-save-button" value="<?php echo esc_attr( $action . ' ' . $type ); ?>" />
				<input type="submit" name="save-notice-state-button" class="button-primary subscription-options-save-button" value="<?php esc_html_e( 'Save Changes', 'give-recurring' ); ?>" />
			</td>
		</tr>
		<?php
	}


	/**
	 * When adding or editing a Renewal|Expiration reminder,
	 * the default save button is disabled since we use our
	 * own custom save button and custom save logic.
	 *
	 * @since 1.7
	 *
	 * @return boolean
	 */
	public function disable_settings_save_button() {
		if ( $this->is_renewal_settings() && isset( $_GET['notice_type'] ) ) {
			return true;
		}

		return false;
	}


	/**
	 * This AJAX callback is used to enable/disable
	 * email notices individually.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function toggle_reminder() {
		$status         = give_clean( $_POST['status'] );
		$notice_id      = absint( give_clean( $_POST['notice_id'] ) );
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$nonce          = give_clean( $_POST['nonce'] );
		$nonce_status   = give_validate_nonce( $nonce, 'email-notice-nonce' );

		if ( $nonce_status ) {
			switch ( $status ) {
				case 'enabled':
					if ( 'enabled' === $stored_notices[ $notice_id ]['status'] ) {
						$stored_notices[ $notice_id ]['status'] = 'disabled';

						update_option( 'give_recurring_reminder_notices', $stored_notices );
						wp_send_json_success( array( 'action' => 'disabled' ) );

					} else {
						wp_send_json_error();
					}

					break;

				case 'disabled':
					if ( 'disabled' === $stored_notices[ $notice_id ]['status'] ) {
						$stored_notices[ $notice_id ]['status'] = 'enabled';

						update_option( 'give_recurring_reminder_notices', $stored_notices );
						wp_send_json_success( array( 'action' => 'enabled' ) );

					} else {
						wp_send_json_error();
					}

					break;

				default:
					break;
			}
		}

		wp_die();
	}


	/**
	 * This logic runs when you hit the custom
	 * 'Add|Edit Renewal|Expiration Reminder' button.
	 *
	 * @since 1.7
	 *
	 * @return boolean Returns false if $_POST is empty.
	 */
	public function custom_save_logic() {

		if ( empty( $_POST ) ) {
			return false;
		}

		$action = $this->get_notice_action();

		if ( false !== $action && isset( $_POST['custom-save-button'] ) ) {

			$stored_notices = get_option( 'give_recurring_reminder_notices', array() );

			$email_notices = array(
				'send_period'  => give_clean( $_POST['email-reminder-period'] ),
				'header'       => give_clean( $_POST['subscription_renewal_reminder_header'] ),
				'subject'      => give_clean( $_POST['subscription_renewal_reminder_subject'] ),
				'message'      => wp_kses_post( trim( wp_unslash( $_POST['subscription_renewal_reminder_message'] ) ) ),
				'notice_type'  => give_clean( $this->notice_type ),
				'gateway'      => isset( $_POST['excluded_gateways'] ) ? give_clean( $_POST['excluded_gateways'] ) : '',
				'content_type' => give_clean( $_POST['content_type_select'] ),
				'status'       => give_clean( $_POST['email_notice_state'] ),
			);

			if ( empty( $stored_notices ) ) {
				add_option( 'give_recurring_reminder_notices', array( $email_notices ) );
			} else {

				if ( 'add' === $this->notice_action ) {
					$stored_notices[] = $email_notices;
				}

				if ( 'edit' === $this->notice_action ) {
					$stored_notices[ $this->notice_id ] = $email_notices;
				}

				update_option( 'give_recurring_reminder_notices', $stored_notices );
			}

			wp_redirect(
				admin_url( 'edit.php' ) . '?post_type=give_forms&page=give-settings&tab=emails&section=subscription-reminder'
			);
			exit;
		} elseif ( isset( $_POST['save-notice-state-button'] ) ) {

			$stored_notices = get_option( 'give_recurring_reminder_notices', array() );

			if ( 'edit' === $this->notice_action ) {
				$stored_notices[ $this->notice_id ]['status'] = give_clean( $_POST['email_notice_state'] );
			}

			update_option( 'give_recurring_reminder_notices', $stored_notices );

			wp_redirect(
				admin_url( 'edit.php' ) . '?post_type=give_forms&page=give-settings&tab=emails&section=subscription-reminder'
			);
			exit;
		}
	}


	/**
	 * Verify nonce
	 *
	 * @since  1.7
	 *
	 * @access private
	 *
	 * @param string $action The type of action: Edit|Delete.
	 *
	 * @return void
	 */
	private function verify_action_nonce( $action ) {
		$nonce_key   = '';
		$notice_type = $this->get_notice_type();
		$notice_id   = $this->get_notice_id();

		switch ( $action ) {
			case 'add':
				$nonce_key = "add_{$notice_type}_reminder";
				break;

			case 'delete':
				$nonce_key = "delete_{$notice_type}_reminder_{$notice_id}";
				break;

			case 'edit':
				$nonce_key = "edit_{$notice_type}_reminder_{$notice_id}";
				break;
		}

		if ( ! empty( $nonce_key ) ) {
			check_admin_referer( $nonce_key );
		}
	}

	/**
	 * Render email content type column label
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @return string
	 */
	public function render_email_content_type_column() {
		return '';
	}

	/**
	 * Flag to verify notice action
	 *
	 * @since 1.8.1
	 * @access private
	 *
	 * @param $action
	 *
	 * @return bool
	 */
	private function is_notice_action( $action ) {
		return $this->notice_action === $action;
	}
}

return Give_Subscription_Reminder_Email::get_instance();
