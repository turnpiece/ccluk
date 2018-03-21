<?php
/**
 * Front ajax for custom forms
 *
 * @since 1.0
 */
class Forminator_CForm_Front_Mail extends Forminator_Mail {

	protected $message_vars;

	/**
	 * Skipped custom form_data parsing
	 *
	 * @since 1.0.3
	 * @var array
	 */
	private $skip_custom_form_data
		= array(
			'admin' => array(),
			'user'  => array(
				'{all_fields}',
			),
		);

	/**
	 * Initialize the mail
	 *
	 * @since 1.0
	 * @param string $user_email - the user email
	 * @param array $post_vars - post variables
	 */
	public function init( $post_vars ) {
		$user_email 	= false;
		$user_name 		= '';
		$user_login 	= '';
		$embed_id 		= $post_vars['page_id'];
		$embed_title 	= get_the_title( $embed_id );
		$embed_url		= forminator_get_current_url();
		$site_url       = site_url();

		//Check if user is logged in
		if ( is_user_logged_in() ) {
			$current_user 	= wp_get_current_user();
			$user_email 	= $current_user->user_email;
			if ( !empty( $current_user->user_firstname ) ) {
				$user_name 	= $current_user->user_firstname . ' ' . $current_user->user_lastname;
			} else if ( !empty( $current_user->display_name ) ) {
				$user_name 	= $current_user->display_name;
			} else {
				$user_name 	= $current_user->display_name;
			}
			$user_login 	= $current_user->user_login;
		}

		//Set up mail variables
		$message_vars = forminator_set_message_vars( $embed_id, $embed_title, $embed_url, $user_name, $user_email, $user_login, $site_url );

		/**
		 * Message variables filter
		 *
		 * @since 1.0.2
		 *
		 * @param array $message_vars - the message variables
		 * @param int $embed_id - the current form id
		 * @param array $post_vars - the post params
		 *
		 * @return array $message_vars
		 */
		$this->message_vars = apply_filters( 'forminator_custom_form_message_vars', $message_vars, $embed_id, $post_vars );
	}

	/**
	 * Process mail
	 *
	 * @since 1.0
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $data
	 * @param Forminator_Form_Entry_Model $entry - saved entry @since 1.0.3
	 */
	public function process_mail( $custom_form, $data, Forminator_Form_Entry_Model $entry ) {
		$setting = $custom_form->settings;

		/**
		 * Message data filter
		 *
		 * @since 1.0.4
		 *
		 * @param array $data - the post data
		 * @param Forminator_Custom_Form_Model $custom_form - the form
		 * @param Forminator_Form_Entry_Model $entry - saved entry @since 1.0.3
		 *
		 *
		 * @return array $data
		 */
		$data = apply_filters( 'forminator_custom_form_mail_data', $data, $custom_form, $entry );

		/**
		 * Action called before mail is sent
		 *
		 * @param Forminator_CForm_Front_Mail - the current form
		 * @param Forminator_Custom_Form_Model - the current form
		 * @param array $data - current data
		 * @param Forminator_Form_Entry_Model $entry - saved entry @since 1.0.3
		 */
		do_action( 'forminator_custom_form_mail_before_send_mail', $this, $custom_form, $data, $entry );

		//Process Email
		if ( $this->send_admin_mail( $setting ) || $this->send_user_mail( $setting ) ) {
			$this->init( $_POST );
			//Process admin mail
			if ( $this->send_admin_mail( $setting ) ) {
				$recipents = $this->get_admin_email_recipents($setting);

				/**
				 * Custom form admin mail recipients filter
				 *
				 * @since 1.0.3
				 *
				 * @param array $recipents
				 * @param Forminator_Custom_Form_Model - the current form
				 *
				 * @return array $recipents
				 */
				$recipents = apply_filters( 'forminator_custom_form_mail_admin_recipients', $recipents, $custom_form, $data, $entry, $this );

				if ( $recipents ) {
					$subject = forminator_replace_form_data( $setting['admin-email-title'], $data );
					$subject = forminator_replace_custom_form_data( $subject, $custom_form, $data, $entry, $this->skip_custom_form_data['admin'] );

					$message = forminator_replace_form_data( $setting['admin-email-editor'], $data );
					$message = forminator_replace_variables( $message );
					$message = forminator_replace_custom_form_data( $message, $custom_form, $data, $entry, $this->skip_custom_form_data['admin'] );

					/**
					 * Custom form mail subject filter
					 *
					 * @since 1.0.2
					 *
					 * @param string $subject
					 * @param Forminator_Custom_Form_Model - the current form
					 *
					 * @return string $subject
					 */
					$subject = apply_filters( 'forminator_custom_form_mail_admin_subject', $subject, $custom_form, $data, $entry, $this );

					/**
					 * Custom form mail message filter
					 *
					 * @since 1.0.2
					 *
					 * @param string $message
					 * @param Forminator_Custom_Form_Model - the current form
					 *
					 * @return string $message
					 */
					$message = apply_filters( 'forminator_custom_form_mail_admin_message', $message, $custom_form, $data, $entry, $this );

					$this->set_subject( $subject );
					$this->set_recipients( $recipents );
					$this->set_message_with_vars( $this->message_vars, $message );
					$this->send_multiple();

					/**
					 * Action called after admin mail sent
					 *
					 * @param Forminator_CForm_Front_Mail - the current form
					 * @param Forminator_Custom_Form_Model - the current form
					 * @param array $data - current data
					 * @param Forminator_Form_Entry_Model $entry - saved entry @since 1.0.3
					 * @param array $recipents - array or recipents
					 */
					do_action( 'forminator_custom_form_mail_admin_sent', $this, $custom_form, $data, $entry, $recipents );
				}
			}

			$user_data_email = $this->get_user_email( $data, $custom_form );

			//Process user mail
			if ( $this->send_user_mail( $setting ) && $user_data_email && !empty( $user_data_email ) ) {
				$subject = forminator_replace_form_data(  $setting['user-email-title'], $data );
				$subject = forminator_replace_custom_form_data( $subject, $custom_form, $data, $entry, $this->skip_custom_form_data['user'] );

				$message = forminator_replace_form_data( $setting['user-email-editor'], $data );
				$message = forminator_replace_variables( $message );
				$message = forminator_replace_custom_form_data( $message, $custom_form, $data, $entry, $this->skip_custom_form_data['user'] );

				/**
				 * Custom form mail subject filter
				 *
				 * @since 1.0.2
				 *
				 * @param string $subject
				 * @param Forminator_Custom_Form_Model - the current form
				 *
				 * @return string $subject
				 */
				$subject = apply_filters( 'forminator_custom_form_mail_user_subject', $subject, $custom_form, $data, $entry, $this );

				/**
				 * Custom form mail filter
				 *
				 * @since 1.0.2
				 *
				 * @param string $message
				 * @param Forminator_Custom_Form_Model - the current form
				 *
				 * @return string $message
				 */
				$message = apply_filters( 'forminator_custom_form_mail_user_message', $message, $custom_form, $data, $entry, $this );

				$this->set_subject( $subject );
				$this->set_recipient( $user_data_email );
				$this->set_message_with_vars( $this->message_vars, $message );
				$this->send();

				/**
				 * Action called after admin mail sent
				 *
				 * @param Forminator_CForm_Front_Mail - the current form
				 * @param Forminator_Custom_Form_Model - the current form
				 * @param array $data - current data
				 * @param Forminator_Form_Entry_Model $entry - saved entry @since 1.0.3
				 * @param string $recipents - the recipent email address
				 */
			   do_action( 'forminator_custom_form_mail_user_sent', $this, $custom_form, $data, $entry, $user_data_email );
			}
		}


		/**
		 * Action called after mail is sent
		 *
		 * @param Forminator_CForm_Front_Mail - the current form
		 * @param Forminator_Custom_Form_Model - the current form
		 * @param array $data - current data
		 */
		do_action( 'forminator_custom_form_mail_after_send_mail', $this, $custom_form, $data );
	}

	/**
	 * Get user email from data
	 *
	 * @since 1.0.3
	 *
	 * @param $data
	 * @param $custom_form
	 *
	 * @return bool|string
	 */
	private function get_user_email_data( $data, $custom_form ) {
		// Get form fields
		$fields = $custom_form->getFields();
		if ( ! is_null( $fields ) ) {
			foreach ( $fields as $field ) {
				$field_array = $field->toFormattedArray();
				$field_type  = $field_array["type"];

				// Check if field is email
				if( $field_type == "email" ) {
					$field_id = $field_array['element_id'];
					if( isset( $data[$field_id] ) && !empty( $data[$field_id] ) ) {
						return $data[$field_id];
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get user email
	 *
	 * @since 1.0.3
	 *
	 * @param $data
	 * @param $custom_form
	 *
	 * @return bool
	 */
	private function get_user_email( $data, $custom_form ) {
		$data_email = $this->get_user_email_data( $data, $custom_form );

		if( $data_email && !empty( $data_email ) ) {
			// We have data email, use it
			return $data_email;
		} else {
			// Check if user logged in
			if( is_user_logged_in() ) {
				return $this->message_vars['user_email'];
			}
		}

		return false;
	}

	/**
	 * Check if all conditions are met to send admin email
	 *
	 * @since 1.0
	 * @param array $setting - the form settings
	 *
	 * @return bool
	 */
	private function send_admin_mail( $setting ) {
		if ( isset( $setting['use-admin-email'] ) && !empty( $setting['use-admin-email'] ) ) {
			if ( filter_var( $setting['use-admin-email'] , FILTER_VALIDATE_BOOLEAN ) ) {
				if ( isset( $setting['admin-email-title'] ) &&  isset( $setting['admin-email-editor'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get Recipients of admin emails
	 *
	 * @since 1.0.3
	 *
	 * @param $setting
	 *
	 * @return array
	 */
	private function get_admin_email_recipents( $setting ) {
		// backward compatibility for version < 1.0.3
		// when `admin-email-recipients` not exist use admin email
		if ( ! isset( $setting['admin-email-recipients'] ) ) {
			return array( get_option( 'admin_email' ) );
		}

		if ( isset( $setting['admin-email-recipients'] ) && ! empty( $setting['admin-email-recipients'] ) ) {
			if ( is_array( $setting['admin-email-recipients'] ) ) {
				return $setting['admin-email-recipients'];
			}
		}

		return array();
	}

	/**
	 * Check if all conditions are met to send user email
	 *
	 * @since 1.0
	 * @param array $setting - the form settings
	 *
	 * @return bool
	 */
	private function send_user_mail( $setting ) {
		if ( isset( $setting['use-user-email'] ) && !empty( $setting['use-user-email'] ) ) {
			if ( filter_var( $setting['use-user-email'] , FILTER_VALIDATE_BOOLEAN ) ) {
				if ( isset( $setting['user-email-title'] ) &&  isset( $setting['user-email-editor'] ) ) {
					return true;
				}
			}
		}

		return false;
	}
}