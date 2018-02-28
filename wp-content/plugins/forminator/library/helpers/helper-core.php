<?php
/**
 * Return needed cap for admin pages
 *
 * @since 1.0
 * @return string
 */
function forminator_get_admin_cap() {
	$cap = 'manage_options';

	if ( is_multisite() && is_network_admin() ) {
		$cap = 'manage_network';
	}

	return apply_filters( 'forminator_admin_cap', $cap );
}

/**
 * Checks if user is allowed to perform the ajax actions
 *
 * @since 1.0
 * @return bool
 */
function forminator_is_user_allowed() {
	return current_user_can( 'manage_options' );
}

/**
 * Check if array value exists
 *
 * @since 1.0
 *
 * @param array  $array
 * @param string $key - the string key
 *
 * @return bool
 */
function forminator_array_value_exists( $array, $key ) {
	return ( isset( $array[ $key ] ) && ! empty( $array[ $key ] ) );
}

/**
 * Convert object to array
 *
 * @since 1.0
 *
 * @param $object
 *
 * @return array
 */
function forminator_object_to_array( $object ) {
	$array = array();

	if ( empty( $object ) ) {
		return $array;
	}

	foreach ( $object as $key => $value ) {
		$array[ $key ] = $value;
	}

	return $array;
}

/**
 * Return AJAX url
 *
 * @since 1.0
 * @return mixed
 */
function forminator_ajax_url() {
	return admin_url( "admin-ajax.php", is_ssl() ? 'https' : 'http' );
}

/**
 * Checks if the AJAX call is valid
 *
 * @since 1.0
 *
 * @param $action
 */
function forminator_validate_ajax( $action ) {
	if ( ! forminator_is_user_allowed() || ! check_ajax_referer( $action ) ) {
		wp_send_json_error( __( "Invalid request, you are not allowed to do that action.", Forminator::DOMAIN ) );
	}
}

/**
 * Enqueue admin fonts
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_admin_enqueue_fonts() {
	wp_enqueue_style( 'forminator-roboto', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:300,300i,400,400i,500,500i,700,700i', false );
	wp_enqueue_style( 'forminator-opensans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i', false );
	wp_enqueue_style( 'forminator-source', 'https://fonts.googleapis.com/css?family=Source+Code+Pro', false );
}

/**
 * Enqueue admin styles
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_admin_enqueue_styles( $version ) {
	wp_enqueue_style( 'select2-forminator-css', forminator_plugin_url() . 'assets/css/select2.min.css', array(), "4.0.3" ); // Select2
	wp_enqueue_style( 'forminator-admin', forminator_plugin_url() . 'assets/css/admin.css', array(), $version );
	wp_enqueue_style( 'forminator-form-styles', forminator_plugin_url() . 'assets/css/front.css', array(), $version );
}

/**
 * Enqueue jQuery UI scripts on admin
 *
 * @since 1.0
 */
function forminator_admin_jquery_ui() {
	wp_enqueue_script( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', false, '1.12.1' );
}

/**
 * Load admin scripts
 *
 * @since 1.0
 */
function forminator_admin_jquery_ui_init() {
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-widget' );
	wp_enqueue_script( 'jquery-ui-mouse' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-resize' );
	wp_enqueue_style( 'wp-color-picker' );
}

/**
 * Enqueue admin scripts
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_admin_enqueue_scripts( $version, $data = array(), $l10n = array() ) {
	wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'build/library/select2.full.min.js', array( 'jquery' ), $version );
	wp_enqueue_script( 'ace-editor', forminator_plugin_url() . 'build/library/ace/ace.js', array( 'jquery' ), $version );
	wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'forminator-admin-layout', forminator_plugin_url() . 'build/admin/layout.js', array( 'jquery' ), $version );
	wp_register_script( 'forminator-admin',
	                    forminator_plugin_url() . 'build/main.js',
	                    array(
		                    'backbone',
		                    'underscore',
		                    'jquery',
		                    'wp-color-picker',
	                    ),
	                    $version );
	wp_localize_script( 'forminator-admin', 'forminator_data', $data );
	wp_localize_script( 'forminator-admin', 'forminator_l10n', $l10n );
	wp_enqueue_script( 'forminator-admin' );
}

/**
 * Enqueue front-end styles
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_print_front_styles( $version = '1.0' ) {
	wp_enqueue_style( 'forminator-form-styles', forminator_plugin_url() . 'assets/css/front.css', array(), $version );
	wp_enqueue_style( 'select2-forminator-css', forminator_plugin_url() . 'assets/css/select2.min.css', array(), "4.0.3" ); // Select2
}

/**
 * Enqueue front-end styles
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_print_front_scripts( $version = '1.0' ) {
	if ( ! is_admin() ) {
		wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'build/library/select2.full.min.js', array( 'jquery' ), $version );
		wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version );
		wp_enqueue_script( 'forminator-jquery-validate', forminator_plugin_url() . 'build/library/jquery.validate.min.js', array( 'jquery' ), FORMINATOR_VERSION );
		wp_enqueue_script( 'forminator-front-scripts', forminator_plugin_url() . 'build/front/front.multi.min.js', array( 'jquery', 'select2-forminator', 'forminator-jquery-validate' ), $version );

		wp_localize_script( 'forminator-front-scripts', 'ForminatorFront', forminator_localize_data() );
	}



	if ( is_admin() ) {
		wp_localize_script( 'forminator-front-scripts', 'ForminatorConditions', array() );
	}
}

/**
 * Return front-end localization data
 *
 * @since 1.0
 */
function forminator_localize_data() {
	return array(
		'ajaxUrl' => forminator_ajax_url(),
		'cform'   => array(
			'processing'      => __( 'Submitting form, please wait', Forminator::DOMAIN ),
			'error'           => __( 'An error occured processing the form. Please try again', Forminator::DOMAIN ),
			'pagination_prev' => __( 'Back', Forminator::DOMAIN ),
			'pagination_next' => __( 'Next', Forminator::DOMAIN ),
			'pagination_go'   => __( 'Submit', Forminator::DOMAIN ),
			'gateway'         => array(
				'processing' => __( 'Processing payment, please wait', Forminator::DOMAIN ),
				'paid'       => __( 'Success! Payment confirmed. Submitting form, please wait', Forminator::DOMAIN ),
				'error'      => __( 'Error! Something went wrong when verifying the payment', Forminator::DOMAIN ),
			),
			'captcha_error'   => __( 'Invalid Captcha', Forminator::DOMAIN ),
		),
		'poll'    => array(
			'processing' => __( 'Submitting vote, please wait', Forminator::DOMAIN ),
			'error'      => __( 'An error occured saving the vote. Please try again', Forminator::DOMAIN ),
		),
	);
}

/**
 * Return existing templates
 *
 * @since 1.0
 *
 * @param $path
 * @param $args
 *
 * @return mixed
 */

function forminator_template( $path, $args = array() ) {
	$file    = forminator_plugin_dir() . "admin/views/$path.php";
	$content = '';

	if ( is_file( $file ) ) {
		ob_start();

		if ( isset( $args['id'] ) ) {
			$args['template_class'] = $args['class'];
			$args['template_id']    = $args['id'];
		}

		extract( $args );

		include( $file );

		$content = ob_get_clean();
	}

	return $content;
}

/**
 * Return if template exist
 *
 * @since 1.0
 *
 * @param $path
 *
 * @return bool
 */
function forminator_template_exist( $path ) {
	$file = forminator_plugin_dir() . "admin/views/$path.php";

	return is_file( $file );
}

/**
 * Return if paypal settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_paypal_settings() {
	$client_id = get_option( "forminator_paypal_client_id", false );
	$secret    = get_option( "forminator_paypal_secret", false );

	if ( empty( $client_id ) || empty( $secret ) ) {
		return false;
	}

	return true;
}

/**
 * Return if captcha settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_captcha_settings() {
	$key    = get_option( "forminator_captcha_key", false );
	$secret = get_option( "forminator_captcha_secret", false );

	if ( empty( $key ) || empty( $secret ) ) {
		return false;
	}

	return true;
}

/**
 * Return form ID
 *
 * @since 1.0
 * @return int
 */
function forminator_get_form_id_helper() {
	$screen = get_current_screen();
	$ids    = array(
		'forminator_page_forminator-quiz-view',
		'forminator_page_forminator-cform-view',
		'forminator_page_forminator-poll-view',
	);
	if ( ! in_array( $screen->id, $ids ) ) {
		return 0;
	}

	return isset( $_GET['form_id'] ) ? intval( $_GET['form_id'] ) : 0;
}

/**
 * Return form type
 *
 * @since 1.0
 * @return int|null|string
 */
function forminator_get_form_type_helper() {
	$screen = get_current_screen();
	$ids    = array(
		'forminator_page_forminator-quiz-view',
		'forminator_page_forminator-cform-view',
		'forminator_page_forminator-poll-view',
	);
	if ( ! in_array( $screen->id, $ids ) ) {
		return 0;
	}

	$form_type = "";
	$page      = isset( $_GET['page'] ) ? $_GET['page'] : null;
	if ( $page == null ) {
		return null;
	}

	switch ( $page ) {
		case 'forminator-quiz-view':
			$form_type = "quiz";
			break;
		case 'forminator-poll-view':
			$form_type = "poll";
			break;
		case 'forminator-cform-view':
			$form_type = "cform";
			break;
	}

	return $form_type;
}

/**
 * @since 1.0
 *
 * @param $info
 * @param $key
 *
 * @return mixed
 */
function forminator_get_exporter_info( $info, $key ) {
	$data = get_option( 'forminator_entries_export_schedule', array() );

	return isset( $data[ $key ][ $info ] ) ? $data[ $key ][ $info ] : null;
}

/**
 * Return current logged in username
 *
 * @since 1.0
 * @return string
 */
function forminator_get_current_username() {
	$current_user = wp_get_current_user();
	if ( ! ( $current_user instanceof WP_User ) || empty( $current_user->user_login ) ) {
		return '';
	}

	return $current_user->user_login;
}

/**
 * @since 1.0
 *
 * @param $form_id
 *
 * @return bool
 */
function delete_export_logs( $form_id ) {
	if ( ! $form_id ) {
		return false;
	}

	$data = get_option( 'forminator_exporter_log', array() );
	$delete = false;

	if ( isset( $data[ $form_id ] ) ) {
		unset($data[$form_id]);
		$delete = update_option( 'forminator_exporter_log', $data );
	}

	return $delete;
}

/**
 * @since 1.0
 *
 * @param $form_id
 *
 * @return array
 */
function forminator_get_export_logs( $form_id ) {
	if ( ! $form_id ) {
		return array();
	}

	$data = get_option( 'forminator_exporter_log', array() );
	$row  = isset( $data[ $form_id ] ) ? $data[ $form_id ] : array();

	foreach ( $row as &$item ) {
		$item['time'] = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['time'] );
	}

	return $row;
}


/**
 * Return week day from number
 *
 * @since 1.0
 *
 * @param $day
 *
 * @return string
 */
function forminator_get_day_translated( $day ) {
	$days = array(
		"mon" => __( "Monday", Forminator::DOMAIN ),
		"tue" => __( "Tuesday", Forminator::DOMAIN ),
		"wed" => __( "Wednesday", Forminator::DOMAIN ),
		"thu" => __( "Thursday", Forminator::DOMAIN ),
		"fri" => __( "Friday", Forminator::DOMAIN ),
		"sat" => __( "Saturday", Forminator::DOMAIN ),
		"sun" => __( "Sunday", Forminator::DOMAIN ),
	);

	return isset( $days[ $day ] ) ? $days[ $day ] : $day;
}