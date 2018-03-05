<?php
/**
 * Front ajax for custom forms
 *
 * @since 1.0
 */
class Forminator_CForm_Front_Mail extends Forminator_Mail {

	protected $message_vars;

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
		$embed_url		= '';
		//$embed_url 	 	= get_permalink( $embed_id ); - throws error since the $wp_rewrite->get_extra_permastruct function is initialized late

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
		$message_vars = forminator_set_message_vars( $embed_id, $embed_title, $embed_url, $user_name, $user_email, $user_login );

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
	 * @param $custom_form
	 * @param $data
	 */
	public function process_mail( $custom_form, $data ) {
		$setting 	  = $custom_form->settings;

		/**
		 * Action called before mail is sent
		 *
		 * @param Forminator_CForm_Front_Mail - the current form
		 * @param Forminator_Custom_Form_Model - the current form
		 * @param array $data - current data
		 */
		do_action( 'forminator_custom_form_mail_before_send_mail', $this, $custom_form, $data );

		//Process Email
		if ( $this->send_admin_mail( $setting ) || $this->send_user_mail( $setting ) ) {
			$this->init( $_POST );
			//Process admin mail
			if ( $this->send_admin_mail( $setting ) ) {
				$subject = $setting['admin-email-title'];
				$message = forminator_replace_form_data( $setting['admin-email-editor'], $data );
				$message = forminator_replace_variables( $message );

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
				$subject = apply_filters( 'forminator_custom_form_mail_admin_subject', $subject, $custom_form );

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
				$message = apply_filters( 'forminator_custom_form_mail_admin_message', $message, $custom_form );

				$admin_email 	= get_option( 'admin_email' );

				$this->set_subject( $subject );
				$this->set_recipient( $admin_email );
				$this->set_message_with_vars( $this->message_vars, $message );
				$this->send();
			}

			//Process user mail
			if ( $this->send_user_mail( $setting ) && !empty( $this->message_vars['user_email'] ) && $this->message_vars['user_email'] ) {
				$subject = $setting['user-email-title'];
				$message = forminator_replace_form_data( $setting['user-email-editor'], $data );
				$message = forminator_replace_variables( $message );

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
				$subject = apply_filters( 'forminator_custom_form_mail_user_subject', $subject, $custom_form );

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
				$message = apply_filters( 'forminator_custom_form_mail_user_message', $message, $custom_form );

				$this->set_subject( $subject );
				$this->set_recipient( $this->message_vars['user_email'] );
				$this->set_message_with_vars( $this->message_vars, $message );
				$this->send();
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