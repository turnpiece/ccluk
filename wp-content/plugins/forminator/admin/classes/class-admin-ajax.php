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
		add_action( "wp_ajax_forminator_load_google_fonts", array( $this, "load_fonts" ) );
		add_action( "wp_ajax_nopriv_forminator_load_google_fonts", array( $this, "load_fonts" ) );

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

		add_action( "wp_ajax_forminator_load_preview_cforms_popup", array( $this, "preview_custom_forms" ) );
		add_action( "wp_ajax_forminator_load_preview_polls_popup", array( $this, "preview_polls" ) );
		add_action( "wp_ajax_forminator_load_preview_quizzes_popup", array( $this, "preview_quizzes" ) );

		// Handle exports popup
		add_action( "wp_ajax_forminator_load_exports_popup", array( $this, "load_exports" ) );
		add_action( "wp_ajax_forminator_load_exports_popup", array( $this, "load_exports" ) );
		add_action( "wp_ajax_forminator_clear_exports_popup", array( $this, "clear_exports" ) );
		add_action( "wp_ajax_forminator_clear_exports_popup", array( $this, "clear_exports" ) );
	}

	/**
	 * Save quizzes
	 *
	 * @since 1.0
	 */
	function save_quiz() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$id = isset( $_POST['formID'] ) ? $_POST['formID'] : null;
		$id = intval( $id );
		if ( $id == null || $id <= 0 ) {
			$formModel = new Forminator_Quiz_Form_Model();
		} else {
			$formModel = Forminator_Quiz_Form_Model::model()->load( $id );
			if ( ! is_object( $formModel ) ) {
				wp_send_json_error( __( "Quiz model doesn't exist", Forminator::DOMAIN ) );
			}
			//we need to empty fields cause we will send new data
			$formModel->clearFields();
		}
		$formModel->quiz_type = ( $_POST['action'] == 'forminator_save_quiz_nowrong' ) ? 'nowrong' : 'knowledge';
		$data                 = $_POST['data'];
		if ( isset( $data['results'] ) && is_array( $data['results'] ) ) {
			$formModel->results = $data['results'];
		}
		if ( isset( $data['questions'] ) ) {
			foreach ( $data['questions'] as &$question ) {
				$question['type'] = $formModel->quiz_type;
				$question['slug'] = uniqid();
			}
		}
		$formModel->setVarInArray( 'name', 'formName', $_POST );
		$formModel->questions = $data['questions'];
		unset( $data['results'] );
		unset( $data['questions'] );
		$data['formName']    = $data['quiz_title'];
		$formModel->settings = $data;
		$id                  = $formModel->save();
		wp_send_json_success( $id );
	}

	/**
	 * Save poll
	 *
	 * @since 1.0
	 */
	public function save_poll_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id = isset( $_POST['formID'] ) ? $_POST['formID'] : null;
		$id = intval( $id );
		if ( $id == null || $id <= 0 ) {
			$formModel = new Forminator_Poll_Form_Model();
		} else {
			$formModel = Forminator_Poll_Form_Model::model()->load( $id );
			if ( ! is_object( $formModel ) ) {
				wp_send_json_error( __( "Poll model doesn't exist", Forminator::DOMAIN ) );
			}
			//we need to empty fields cause we will send new data
			$formModel->clearFields();
		}
		$formModel->setVarInArray( 'name', 'formName', $_POST );
		$answers  = $_POST['data']['answers'];
		$settings = $_POST['data'];
		unset( $settings['answers'] );
		$formModel->settings = $settings;
		foreach ( $answers as $answer ) {
			$fieldModel = new Forminator_Form_Field_Model();
			$fieldModel->import( $answer );
			$formModel->addField( $fieldModel );
		}
		$id = $formModel->save();
		wp_send_json_success( $id );
	}

	/**
	 * Save custom form fields & settings
	 *
	 * @since 1.0
	 */
	function save_custom_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id = isset( $_POST['formID'] ) ? $_POST['formID'] : null;
		$id = intval( $id );
		if ( $id == null || $id <= 0 ) {
			$formModel = new Forminator_Custom_Form_Model();
		} else {
			$formModel = Forminator_Custom_Form_Model::model()->load( $id );
			if ( ! is_object( $formModel ) ) {
				wp_send_json_error( __( "Form model doesn't exist", Forminator::DOMAIN ) );
			}
			//we need to empty fields cause we will send new data
			$formModel->clearFields();
		}
		$formModel->setVarInArray( 'name', 'formName', $_POST );
		//build the field
		//build the settings
		$fields = $_POST['data']['wrappers'];
		unset( $_POST['data']['wrappers'] );
		$settings            = $_POST['data'];
		$formModel->settings = $settings;
		foreach ( $fields as $row ) {
			foreach ( $row['fields'] as $f ) {
				$field         = new Forminator_Form_Field_Model();
				$field->formID = $row['wrapper_id'];
				$field->slug   = $f['element_id'];
				unset( $f['element_id'] );
				$field->import( $f );
				$formModel->addField( $field );
			}
		}
		$id = $formModel->save();
		wp_send_json_success( $id );
	}

	/**
	 * Save Login styles
	 *
	 * @since 1.0
	 */
	public function save_login() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$option = "forminator_login_styles";
		$data   = $_POST['data'];

		if ( get_option( $option ) ) {
			update_option( $option, $data );
		} else {
			add_option( $option, $data );
		}

		wp_send_json_success();
	}

	/**
	 * Save Register styles
	 *
	 * @since 1.0
	 */
	public function save_register() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$option = "forminator_register_styles";
		$data   = $_POST['data'];

		if ( get_option( $option ) ) {
			update_option( $option, $data );
		} else {
			add_option( $option, $data );
		}

		wp_send_json_success();
	}

	/**
	 * Load existing custom field keys
	 *
	 * @since 1.0
	 * @return string JSON
	 */
	function load_existing_cfields() {
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
	function dismiss_welcome() {
		forminator_validate_ajax( "forminator_dismiss_welcome" );
		update_option( "forminator_welcome_dismissed", true );
		wp_send_json_success();
	}

	/**
	 * Load Google Fonts
	 *
	 * @since 1.0
	 */
	function load_fonts() {
		$active = $html = "";
		$fonts  = forminator_get_font_families();

		if ( isset( $_POST['active'] ) ) {
			$active = sanitize_text_field( $_POST['active'] );
		}

		foreach ( $fonts as $font ) {
			if ( $active == $font ) {
				$html .= "<option value='$font' selected='selected'>$font</option>";
			} else {
				$html .= "<option value='$font'>$font</option>";
			}
		}
		wp_send_json_success( $html );
	}

	/**
	 * Load paypal settings
	 *
	 * @since 1.0
	 */
	function load_paypal() {
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
	function save_paypal() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_paypal" );

		update_option( "forminator_paypal_api_mode", sanitize_text_field( $_POST['api_mode'] ) );
		update_option( "forminator_paypal_client_id", sanitize_text_field( $_POST['client_id'] ) );
		update_option( "forminator_paypal_secret", sanitize_text_field( $_POST['secret'] ) );

		wp_send_json_success();
	}

	/**
	 * Load reCaptcha settings
	 *
	 * @since 1.0
	 */
	function load_captcha() {
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
	function save_captcha() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_captcha" );

		update_option( "forminator_captcha_key", sanitize_text_field( $_POST['captcha_key'] ) );
		update_option( "forminator_captcha_secret", sanitize_text_field( $_POST['captcha_secret'] ) );
		update_option( "forminator_captcha_language", sanitize_text_field( $_POST['captcha_language'] ) );
		update_option( "forminator_captcha_theme", sanitize_text_field( $_POST['captcha_theme'] ) );
		wp_send_json_success();
	}

	/**
	 * Load currency modal
	 *
	 * @since 1.0
	 */
	function load_currency() {
		forminator_validate_ajax( "forminator_popup_currency" );

		$html = forminator_template( 'settings/popup/edit-currency-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save reCaptcha popup data
	 *
	 * @since 1.0
	 */
	function save_currency() {
		// Validate nonce
		forminator_validate_ajax( "forminator_save_popup_currency" );

		update_option( "forminator_currency", sanitize_text_field( $_POST['currency'] ) );
		wp_send_json_success();
	}

	/**
	 * Preview custom forms
	 *
	 * @since 1.0
	 */
	function preview_custom_forms() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_preview_cforms" );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );
		}

		// Validate ID
		if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$preview_data = forminator_data_to_model_form( $_POST['data'] );
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
	function preview_polls() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_preview_polls" );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$preview_data = forminator_data_to_model_poll( $_POST['data'] );
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
	function preview_quizzes() {
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_preview_quizzes" );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$preview_data = forminator_data_to_model_quiz( $_POST['data'] );
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
	function load_exports() {
		// Validate nonce
		forminator_validate_ajax( "forminator_load_exports" );

		$form_id = isset( $_POST['id'] ) && $_POST['id'] >= 0 ? $_POST['id'] : false;
		if( $form_id ){
			$html = forminator_template( 'settings/popup/exports-content', $args = array(
				'form_id' => $form_id
			) );
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
	function clear_exports() {
		// Validate nonce
		forminator_validate_ajax( "forminator_clear_exports" );

		$form_id = isset( $_POST['id'] ) && $_POST['id'] >= 0 ? $_POST['id'] : false;
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
}