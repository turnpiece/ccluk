<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_AJAX
 *
 * @since 1.0
 */
class Forminator_Admin_AJAX {

	/**
	 * Forminator_Admin_AJAX constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Handle close welcome box
		add_action( "wp_ajax_forminator_dismiss_welcome", array( $this, "dismiss_welcome" ) );
		add_action( "wp_ajax_nopriv_forminator_dismiss_welcome", array( $this, "dismiss_welcome" ) );

		// Handle load google fonts
		add_action( "wp_ajax_forminator_load_google_fonts", array( $this, "load_google_fonts" ) );

		// Handle save settings
		add_action( "wp_ajax_forminator_save_builder_fields", array( $this, "save_custom_form" ) );
		add_action( "wp_ajax_forminator_save_builder_settings", array( $this, "save_custom_form" ) );
		add_action( "wp_ajax_forminator_save_poll", array( $this, "save_poll_form" ) );
		add_action( "wp_ajax_forminator_save_quiz_nowrong", array( $this, "save_quiz" ) );
		add_action( "wp_ajax_forminator_save_quiz_knowledge", array( $this, "save_quiz" ) );
		add_action( "wp_ajax_forminator_save_login", array( $this, "save_login" ) );
		add_action( "wp_ajax_forminator_save_register", array( $this, "save_register" ) );

		// Handle settings popups
		add_action( "wp_ajax_forminator_load_paypal_popup", array( $this, "load_paypal" ) );
		add_action( "wp_ajax_forminator_save_paypal_popup", array( $this, "save_paypal" ) );

		add_action( "wp_ajax_forminator_load_captcha_popup", array( $this, "load_captcha" ) );
		add_action( "wp_ajax_forminator_save_captcha_popup", array( $this, "save_captcha" ) );

		add_action( "wp_ajax_forminator_load_currency_popup", array( $this, "load_currency" ) );
		add_action( "wp_ajax_forminator_save_currency_popup", array( $this, "save_currency" ) );

		add_action( "wp_ajax_forminator_load_pagination_entries_popup", array( $this, "load_pagination_entries" ) );
		add_action( "wp_ajax_forminator_save_pagination_entries_popup", array( $this, "save_pagination_entries" ) );

		add_action( "wp_ajax_forminator_load_pagination_listings_popup", array( $this, "load_pagination_listings" ) );
		add_action( "wp_ajax_forminator_save_pagination_listings_popup", array( $this, "save_pagination_listings" ) );

		add_action( "wp_ajax_forminator_load_email_settings_popup", array( $this, "load_email_form" ) );
		add_action( "wp_ajax_forminator_save_email_settings_popup", array( $this, "save_email_form" ) );

		add_action( "wp_ajax_forminator_load_uninstall_settings_popup", array( $this, "load_uninstall_form" ) );
		add_action( "wp_ajax_forminator_save_uninstall_settings_popup", array( $this, "save_uninstall_form" ) );

		add_action( "wp_ajax_forminator_load_preview_cforms_popup", array( $this, "preview_custom_forms" ) );
		add_action( "wp_ajax_forminator_load_preview_polls_popup", array( $this, "preview_polls" ) );
		add_action( "wp_ajax_forminator_load_preview_quizzes_popup", array( $this, "preview_quizzes" ) );

		// Handle exports popup
		add_action( "wp_ajax_forminator_load_exports_popup", array( $this, "load_exports" ) );
		add_action( "wp_ajax_forminator_clear_exports_popup", array( $this, "clear_exports" ) );

		// Handle search user email
		add_action( "wp_ajax_forminator_builder_search_emails", array( $this, "search_emails" ) );

		add_action( "wp_ajax_forminator_load_privacy_settings_popup", array( $this, "load_privacy_settings" ) );
		add_action( "wp_ajax_forminator_save_privacy_settings_popup", array( $this, "save_privacy_settings" ) );
	}

	/**
	 * Save quizzes
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function save_quiz() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$submitted_data = $this->get_post_data();

		// TODO: Add nonce verify here, and on js admin too
		$id    = isset( $submitted_data['formID'] ) ? $submitted_data['formID'] : null;
		$id    = intval( $id );
		$title = isset($submitted_data['quiz_title']) ? sanitize_text_field( $submitted_data['quiz_title'] ) : sanitize_text_field( $submitted_data['formName'] );

		if ( is_null($id) || $id <= 0 ) {
			$form_model = new Forminator_Quiz_Form_Model();
		} else {
			$form_model = Forminator_Quiz_Form_Model::model()->load( $id );
			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Quiz model doesn't exist", Forminator::DOMAIN ) );
			}
			//we need to empty fields cause we will send new data
			$form_model->clearFields();
		}

		$data 		= $submitted_data['data'];
		$action     = isset($submitted_data['action']) ? $submitted_data['action'] : '';
		$msg_count 	= $data['msg_count']; //Backup, we allow html here
		// Sanitize post data
		$data 		= forminator_sanitize_field( $data );

		$data['msg_count'] = $msg_count;

		// Detect action
		$form_model->quiz_type = 'knowledge';
		if ('forminator_save_quiz_nowrong' === $action) {
			$form_model->quiz_type = 'nowrong';
		}

		// Check if results exist
		if ( isset( $data['results'] ) && is_array( $data['results'] ) ) {
			$form_model->results = $data['results'];
		}

		// Check if questions exist
		if ( isset( $data['questions'] ) ) {
			foreach ( $data['questions'] as &$question ) {
				$question['type'] = $form_model->quiz_type;
				$question['slug'] = uniqid();
			}
		}

		$form_model->setVarInArray( 'name', 'formName', $submitted_data );

		// Handle quiz questions
		$form_model->questions = array();
		if( isset( $data['questions'] ) ) {
			$form_model->questions = $data['questions'];
		}

		// Unset data
		unset( $data['results'] );
		unset( $data['questions'] );
		$data['formName']     = $title;
		$form_model->settings = $data;

		// Save data
		$id = $form_model->save();

		wp_send_json_success( $id );
	}

	/**
	 * Save poll
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function save_poll_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$submitted_data = $this->get_post_data();
		$answers        = array();
		$id             = isset( $submitted_data['formID'] ) ? $submitted_data['formID'] : null;
		$id             = intval( $id );

		if ( is_null($id) || $id <= 0 ) {
			$form_model = new Forminator_Poll_Form_Model();
		} else {
			$form_model = Forminator_Poll_Form_Model::model()->load( $id );
			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Poll model doesn't exist", Forminator::DOMAIN ) );
			}
			//we need to empty fields cause we will send new data
			$form_model->clearFields();
		}

		$form_model->setVarInArray( 'name', 'formName', $submitted_data );

		// Check if answers exist
		if( isset( $submitted_data['data']['answers'] ) ) {
			$answers = forminator_sanitize_field( $submitted_data['data']['answers'] );
		}

		// Sanitize settings
		$settings = forminator_sanitize_field( $submitted_data['data'] );
		unset( $settings['answers'] );
		$form_model->settings = $settings;

		foreach ( $answers as $answer ) {
			$field_model  = new Forminator_Form_Field_Model();
			$answer['id'] = $answer['element_id'];
			$field_model->import( $answer );
			$field_model->slug = $answer['element_id'];
			$form_model->addField( $field_model );
		}

		// Save data
		$id = $form_model->save();

		// add privacy settings to global option
		$override_privacy = false;
		if ( isset( $settings['enable-ip-address-retention'] ) ) {
			$override_privacy = filter_var( $settings['enable-ip-address-retention'], FILTER_VALIDATE_BOOLEAN );
		}
		$retention_number = null;
		$retention_unit   = null;
		if ( $override_privacy ) {
			$retention_number = 0;
			$retention_unit   = 'days';
			if ( isset( $settings['ip-address-retention-number'] ) ) {
				$retention_number = (int) $settings['ip-address-retention-number'];
			}
			if ( isset( $settings['ip-address-retention-unit'] ) ) {
				$retention_unit = $settings['ip-address-retention-unit'];
			}
		}

		forminator_update_poll_ip_address_retention( $id, $retention_number, $retention_unit );

		wp_send_json_success( $id );
	}

	/**
	 * Save custom form fields & settings
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function save_custom_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// TODO: Add nonce verify here, and on js admin too
		$submitted_data = $this->get_post_data();
		$fields = array();
		$id     = isset( $submitted_data['formID'] ) ? $submitted_data['formID'] : null;
		$id     = intval( $id );

		if ( is_null($id) || $id <= 0 ) {
			$form_model = new Forminator_Custom_Form_Model();
		} else {
			$form_model = Forminator_Custom_Form_Model::model()->load( $id );
			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Form model doesn't exist", Forminator::DOMAIN ) );
			}
			//we need to empty fields cause we will send new data
			$form_model->clearFields();
		}
		$form_model->setVarInArray( 'name', 'formName', $submitted_data );

		// Build the fields
		if( isset( $submitted_data['data']['wrappers'] ) ) {
			$fields = $submitted_data['data']['wrappers'];
			unset( $submitted_data['data']['wrappers'] );
		}

		// Sanitize settings
		$settings = forminator_sanitize_field( $submitted_data['data'] );

		// Sanitize textarea fields

		// Sanitize custom css
		if( isset( $submitted_data['data']['custom_css'] ) ) {
			$settings['custom_css'] = sanitize_textarea_field( $submitted_data['data']['custom_css'] );
		}

		// Sanitize thank you message
		if( isset( $submitted_data['data']['thankyou-message'] ) ) {
			$settings['thankyou-message'] = $submitted_data['data']['thankyou-message'];
		}

		// Sanitize user email message
		if( isset( $submitted_data['data']['user-email-editor'] ) ) {
			$settings['user-email-editor'] = $submitted_data['data']['user-email-editor'];
		}

		// Sanitize admin email message
		if( isset( $submitted_data['data']['admin-email-editor'] ) ) {
			$settings['admin-email-editor'] = $submitted_data['data']['admin-email-editor'];
		}

		$form_model->settings = $settings;

		foreach ( $fields as $row ) {
			foreach ( $row['fields'] as $f ) {
				$field         = new Forminator_Form_Field_Model();
				$field->formID = $row['wrapper_id'];
				$field->slug   = $f['element_id'];
				unset( $f['element_id'] );
				$field->import( $f );
				$form_model->addField( $field );
			}
		}

		// Save data
		$id = $form_model->save();

		// add privacy settings to global option
		$override_privacy = false;
		if ( isset( $settings['enable-submissions-retention'] ) ) {
			$override_privacy = filter_var( $settings['enable-submissions-retention'], FILTER_VALIDATE_BOOLEAN );
		}
		$retention_number = null;
		$retention_unit   = null;
		if ( $override_privacy ) {
			$retention_number = 0;
			$retention_unit   = 'days';
			if ( isset( $settings['submissions-retention-number'] ) ) {
				$retention_number = (int) $settings['submissions-retention-number'];
			}
			if ( isset( $settings['submissions-retention-unit'] ) ) {
				$retention_unit = $settings['submissions-retention-unit'];
			}
		}

		forminator_update_form_submissions_retention( $id, $retention_number, $retention_unit );

		wp_send_json_success( $id );
	}

	/**
	 * Load existing custom field keys
	 *
	 * @since 1.0
	 * @return string JSON
	 */
	public function load_existing_cfields() {
		$keys = forminator_get_existing_cfields();
		$html = '';

		foreach ( $keys as $key ) {
			$html .= "<option value='$key'>$key</option>";
		}

		wp_send_json_success( $html );
	}

	/**
	 * Dismiss welcome message
	 *
	 * @since 1.0
	 */
	public function dismiss_welcome() {
		forminator_validate_ajax( "forminator_dismiss_welcome" );
		update_option( "forminator_welcome_dismissed", true );
		wp_send_json_success();
	}

	/**
	 * Load Google Fonts
	 *
	 * @since 1.0
	 */
	public function load_fonts() {
		_deprecated_function( 'load_fonts', '1.0.5', 'load_google_fonts' );
		wp_send_json_error(array());
	}


	/**
	 * Load google fonts
	 *
	 * @since 1.0.5
	 */
	public function load_google_fonts() {
		$fonts = forminator_get_font_families();
		wp_send_json_success( $fonts );
	}

	/**
	 * Load paypal settings
	 *
	 * @since 1.0
	 */
	public function load_paypal() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_paypal" );

		$html = forminator_template( 'settings/popup/edit-paypal-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save paypal popup data
	 *
	 * @since 1.0
	 */
	public function save_paypal() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_paypal" );

		// TODO: Add nonce verification here and js admin
		update_option( "forminator_paypal_api_mode", sanitize_text_field( $_POST['api_mode'] ) ); // WPCS: CSRF ok by forminator_validate_ajax.
		update_option( "forminator_paypal_client_id", sanitize_text_field( $_POST['client_id'] ) ); // WPCS: CSRF ok by forminator_validate_ajax.
		update_option( "forminator_paypal_secret", sanitize_text_field( $_POST['secret'] ) ); // WPCS: CSRF ok by forminator_validate_ajax.

		wp_send_json_success();
	}

	/**
	 * Load reCaptcha settings
	 *
	 * @since 1.0
	 */
	public function load_captcha() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_captcha" );

		$html = forminator_template( 'settings/popup/edit-captcha-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save reCaptcha popup data
	 *
	 * @since 1.0
	 */
	public function save_captcha() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_captcha" );

		update_option( "forminator_captcha_key", sanitize_text_field( $_POST['captcha_key'] ) );// WPCS: CSRF ok by forminator_validate_ajax.
		update_option( "forminator_captcha_secret", sanitize_text_field( $_POST['captcha_secret'] ) );// WPCS: CSRF ok by forminator_validate_ajax.
		update_option( "forminator_captcha_language", sanitize_text_field( $_POST['captcha_language'] ) );// WPCS: CSRF ok by forminator_validate_ajax.
		update_option( "forminator_captcha_theme", sanitize_text_field( $_POST['captcha_theme'] ) );// WPCS: CSRF ok by forminator_validate_ajax.
		wp_send_json_success();
	}

	/**
	 * Load currency modal
	 *
	 * @since 1.0
	 */
	public function load_currency() {
		forminator_validate_ajax( "forminator_popup_currency" );

		$html = forminator_template( 'settings/popup/edit-currency-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save reCaptcha popup data
	 *
	 * @since 1.0
	 */
	public function save_currency() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_currency" );

		update_option( "forminator_currency", sanitize_text_field( $_POST['currency'] ) );// WPCS: CSRF ok by forminator_validate_ajax.
		wp_send_json_success();
	}

	/**
	 * Load entries pagination modal
	 *
	 * @since 1.0.2
	 */
	public function load_pagination_entries() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_pagination_entries" );

		$html = forminator_template( 'settings/popup/edit-pagination-entries-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save entries pagination popup data
	 *
	 * @since 1.0.2
	 */
	 public function save_pagination_entries() {
 		// Validate nonce
 		forminator_validate_ajax( "forminator_save_popup_pagination_entries" );

 		$pagination = intval( sanitize_text_field( $_POST['pagination_entries'] ) ) ;// WPCS: CSRF ok by forminator_validate_ajax.

 		if ( 0 < $pagination ) {

 			update_option( "forminator_pagination_entries", $pagination );
 			wp_send_json_success();

 		} else {

 			wp_send_json_error( __( "Limit per page can not be less than one.", Forminator::DOMAIN ) );

 		}

 	}


	/**
	 * Load listings pagination modal
	 *
	 * @since 1.0.2
	 */
	public function load_pagination_listings() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_pagination_listings" );

		$html = forminator_template( 'settings/popup/edit-pagination-listings-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save listings pagination popup data
	 *
	 * @since 1.0.2
	 */
	public function save_pagination_listings() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_pagination_listings" );

		$pagination = intval( sanitize_text_field( $_POST['pagination_listings'] ) ) ; // WPCS: CSRF ok by forminator_validate_ajax.

 		if ( 0 < $pagination ) {

 			update_option( "forminator_pagination_listings", $pagination );
 			wp_send_json_success();

 		} else {

 			wp_send_json_error( __( "Limit per page can not be less than one.", Forminator::DOMAIN ) );

 		}

	}

	/**
	 * Load the email settings form
	 *
	 * @since 1.1
	 */
	public function load_email_form() {
		// Validate nonce
		forminator_validate_ajax( "forminator_load_popup_email_settings" );

		$html = forminator_template( 'settings/popup/edit-email-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save email settings data
	 *
	 * @since 1.1
	 */
	public function save_email_form() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_email_settings" );

		update_option( "forminator_sender_email_address", sanitize_text_field( $_POST['sender_email'] ) ); // WPCS: CSRF ok by forminator_validate_ajax.
		update_option( "forminator_sender_name", sanitize_text_field( $_POST['sender_name'] ) ); // WPCS: CSRF ok by forminator_validate_ajax.
		wp_send_json_success();
	}

	/**
	 * Load the uninstall form
	 *
	 * @since 1.0.2
	 */
	public function load_uninstall_form() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_uninstall_form" );

		$html = forminator_template( 'settings/popup/edit-uninstall-content' );

		wp_send_json_success( $html );
	}


	/**
	 * Save listings pagination popup data
	 *
	 * @since 1.0.2
	 */
	public function save_uninstall_form() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_uninstall_settings" );

		$delete_uninstall = $_POST['delete_uninstall'];// WPCS: CSRF ok by forminator_validate_ajax.
		$delete_uninstall = filter_var( $delete_uninstall, FILTER_VALIDATE_BOOLEAN );

		update_option( "forminator_uninstall_clear_data", $delete_uninstall );
		wp_send_json_success();
	}

	/**
	 * Preview custom forms
	 *
	 * @since 1.0
	 */
	public function preview_custom_forms() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_preview_cforms" );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) { // WPCS: CSRF ok by forminator_validate_ajax.
			$form_id = intval( $_POST['id'] );
		}

		// Validate ID
		if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {// WPCS: CSRF ok by forminator_validate_ajax.
			wp_send_json_error();
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {// WPCS: CSRF ok by forminator_validate_ajax.
			$preview_data = forminator_data_to_model_form( $_POST['data'] );// WPCS: CSRF ok by forminator_validate_ajax.
		}

		ob_start();

		forminator_form_preview( $form_id, true, $preview_data );

		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Preview polls
	 *
	 * @since 1.0
	 */
	public function preview_polls() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_preview_polls" );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) {// WPCS: CSRF ok by forminator_validate_ajax.
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {// WPCS: CSRF ok by forminator_validate_ajax.
			$preview_data = forminator_data_to_model_poll( $_POST['data'] );// WPCS: CSRF ok by forminator_validate_ajax.
		}

		ob_start();

		forminator_poll_preview( $form_id, true, $preview_data );

		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Preview quizzes
	 *
	 * @since 1.0
	 */
	public function preview_quizzes() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_preview_quizzes" );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) {// WPCS: CSRF ok by forminator_validate_ajax.
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {// WPCS: CSRF ok by forminator_validate_ajax.
			$preview_data = forminator_data_to_model_quiz( $_POST['data'] );// WPCS: CSRF ok by forminator_validate_ajax.
		}

		ob_start();

		forminator_quiz_preview( $form_id, true, $preview_data );

		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Load list of exports
	 *
	 * @since 1.0
	 */
	public function load_exports() {
		// Validate nonce
		forminator_validate_ajax( "forminator_load_exports" );

		$form_id = isset( $_POST['id'] ) && $_POST['id'] >= 0 ? $_POST['id'] : false;// WPCS: CSRF ok by forminator_validate_ajax.
		if ( $form_id ) {
			$args = array(
				'form_id' => $form_id,
			);
			$html = forminator_template( 'settings/popup/exports-content', $args );
			wp_send_json_success( $html );
		} else {
			wp_send_json_error( __( "Not valid module ID provided.", Forminator::DOMAIN ) );
		}
	}

	/**
	 * Clear list of exports
	 *
	 * @since 1.0
	 */
	public function clear_exports() {
		// Validate nonce
		forminator_validate_ajax( "forminator_clear_exports" );

		$form_id = isset( $_POST['id'] ) && $_POST['id'] >= 0 ? $_POST['id'] : false;// WPCS: CSRF ok by forminator_validate_ajax.

		if ( !$form_id ) {
			wp_send_json_error( __( "No ID was provided.", Forminator::DOMAIN ) );
		}

		$was_cleared = delete_export_logs( $form_id );

		if( $was_cleared ) {
			wp_send_json_success( __( "Exports cleared.", Forminator::DOMAIN ) );
		} else {
			wp_send_json_error( __( "Exports couldn't be cleared.", Forminator::DOMAIN ) );
		}
	}

	/**
	 * Search Emails
	 *
	 * @since 1.0.3
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function search_emails() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array() );
		}

		$submitted_data = $this->get_post_data();

		//TODO : add ajax validate here and js admin too
		$admin_email  = ( ( isset( $submitted_data['admin_email'] ) && $submitted_data['admin_email'] ) ? true : false );
		$search_email = ( ( isset( $submitted_data['q'] ) && $submitted_data['q'] ) ? sanitize_text_field( $submitted_data['q'] ) : false );

		// return admin_email when requested
		if ( $admin_email ) {
			wp_send_json_success( get_option( 'admin_email' ) );
		}

		if ( ! $search_email ) {
			wp_send_json_success( array() );
		}

		$args = array(
			'search'  => '*' . $search_email . '*',
			'number'  => 10,
			'orderby' => 'user_login',
			'order'   => 'ASC',
		);

		$users = get_users( $args );
		$data  = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$data[] = array(
					'id'   => $user->user_email,
					'text' => $user->user_email,
				);
			}
		}
		wp_send_json_success( $data );
	}

	/**
	 * Get $_POST data
	 *
	 * @since 1.1
	 *
	 * @param string $nonce_action action to validate
	 * @param array  $sanitize_callbacks {
	 *                                   custom sanitize options, its assoc array
	 *                                   'field_name_1' => 'function_to_call_1' function will called with `call_user_func_array`,
	 *                                   'field_name_2' => 'function_to_call_2',
	 *                                   }
	 *
	 * @return array
	 */
	protected function get_post_data( $nonce_action = '', $sanitize_callbacks = array() ) {
		// do nonce / caps check when requested
		if ( ! empty( $nonce_action ) ) {
			// it will wp_send_json_error
			forminator_validate_ajax( $nonce_action );
		}

		// TODO : mark this as phpcs comply after checking usages of this function
		$post_data = $_POST;

		// do some sanitize
		foreach ( $sanitize_callbacks as $field => $sanitize_func ) {
			if ( isset( $post_data[ $field ] ) ) {
				if ( is_callable( $sanitize_func ) ) {
					$post_data[ $field ] = call_user_func_array( array( $sanitize_func ), array( $post_data[ $field ] ) );
				}
			}
		}

		// do some validation

		return $post_data;
	}

	/*
	 * Load Privacy Settings
	 *
	 * @since 1.0.6
	 */
	public function load_privacy_settings() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_privacy_settings" );

		$html = forminator_template( 'settings/popup/edit-privacy-settings' );

		wp_send_json_success( $html );
	}

	/**
	 * Save Privacy Settings
	 *
	 * @since 1.0.6
	 */
	public function save_privacy_settings() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_privacy_settings" );
		$post_data = $_POST;

		// Custom Forms
		if ( isset( $post_data['erase_form_submissions'] ) ) {
			$enable_erasure_request_erase_form_submissions = filter_var( $post_data['erase_form_submissions'], FILTER_VALIDATE_BOOLEAN );
			update_option( 'forminator_enable_erasure_request_erase_form_submissions', $enable_erasure_request_erase_form_submissions );
		}

		if ( isset( $post_data['submissions_retention_number'] ) ) {
			$submissions_retention_number = intval( $post_data['submissions_retention_number'] );
			if ( $submissions_retention_number < 0 ) {
				$submissions_retention_number = 0;
			}
			update_option( 'forminator_retain_submissions_interval_number', $submissions_retention_number );
		}
		update_option( 'forminator_retain_submissions_interval_unit', $post_data['submissions_retention_unit'] );

		// Polls
		if ( isset( $post_data['votes_retention_number'] ) ) {
			$votes_retention_number = intval( $post_data['votes_retention_number'] );
			if ( $votes_retention_number < 0 ) {
				$votes_retention_number = 0;
			}
			update_option( 'forminator_retain_votes_interval_number', $votes_retention_number );
		}
		update_option( 'forminator_retain_votes_interval_unit', $post_data['votes_retention_unit'] );

		wp_send_json_success();
	}
}