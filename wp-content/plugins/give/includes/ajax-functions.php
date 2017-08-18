<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     Give
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if AJAX works as expected
 *
 * @since  1.0
 *
 * @return bool True if AJAX works, false otherwise
 */
function give_test_ajax_works() {

	// Check if the Airplane Mode plugin is installed.
	if ( class_exists( 'Airplane_Mode_Core' ) ) {

		$airplane = Airplane_Mode_Core::getInstance();

		if ( method_exists( $airplane, 'enabled' ) ) {

			if ( $airplane->enabled() ) {
				return true;
			}
		} else {

			if ( 'on' === $airplane->check_status()  ) {
				return true;
			}
		}
	}

	add_filter( 'block_local_requests', '__return_false' );

	if ( Give_Cache::get( '_give_ajax_works', true ) ) {
		return true;
	}

	$params = array(
		'sslverify' => false,
		'timeout'   => 30,
		'body'      => array(
			'action' => 'give_test_ajax',
		),
	);

	$ajax = wp_remote_post( give_get_ajax_url(), $params );

	$works = true;

	if ( is_wp_error( $ajax ) ) {

		$works = false;

	} else {

		if ( empty( $ajax['response'] ) ) {
			$works = false;
		}

		if ( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
			$works = false;
		}

		if ( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
			$works = false;
		}

		if ( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
			$works = false;
		}
	}

	if ( $works ) {
		Give_Cache::set( '_give_ajax_works', '1', DAY_IN_SECONDS, true );
	}

	return $works;
}


/**
 * Get AJAX URL
 *
 * @since  1.0
 *
 * @return string
 */
function give_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = give_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'give_ajax_url', $ajax_url );
}

/**
 * Loads Checkout Login Fields via AJAX
 *
 * @since  1.0
 *
 * @return void
 */
function give_load_checkout_login_fields() {
	/**
	 * Fire when render login fields via ajax.
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_login_fields' );

	give_die();
}

add_action( 'wp_ajax_nopriv_give_checkout_login', 'give_load_checkout_login_fields' );

/**
 * Load Checkout Fields
 *
 * @since  1.3.6
 *
 * @return void
 */
function give_load_checkout_fields() {
	$form_id = isset( $_POST['form_id'] ) ? $_POST['form_id'] : '';

	ob_start();

	/**
	 * Fire to render registration/login form.
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_register_login_fields', $form_id );

	$fields = ob_get_clean();

	wp_send_json( array(
		'fields' => wp_json_encode( $fields ),
		'submit' => wp_json_encode( give_get_donation_form_submit_button( $form_id ) ),
	) );
}

add_action( 'wp_ajax_nopriv_give_cancel_login', 'give_load_checkout_fields' );
add_action( 'wp_ajax_nopriv_give_checkout_register', 'give_load_checkout_fields' );

/**
 * Get Form Title via AJAX (used only in WordPress Admin)
 *
 * @since  1.0
 *
 * @return void
 */
function give_ajax_get_form_title() {
	if ( isset( $_POST['form_id'] ) ) {
		$title = get_the_title( $_POST['form_id'] );
		if ( $title ) {
			echo $title;
		} else {
			echo 'fail';
		}
	}
	give_die();
}

add_action( 'wp_ajax_give_get_form_title', 'give_ajax_get_form_title' );
add_action( 'wp_ajax_nopriv_give_get_form_title', 'give_ajax_get_form_title' );

/**
 * Retrieve a states drop down
 *
 * @since  1.0
 *
 * @return void
 */
function give_ajax_get_states_field() {
	$states_found = false;
	$show_field = true;
	$states_require = true;
	// Get the Country code from the $_POST.
	$country = sanitize_text_field( $_POST['country'] );

	// Get the field name from the $_POST.
	$field_name = sanitize_text_field( $_POST['field_name'] );

	$label = __( 'State', 'give' );
	$states_label = give_get_states_label();

	// Check if $country code exists in the array key for states label.
	if ( array_key_exists( $country, $states_label ) ) {
		$label = $states_label[ $country ];
	}

	if ( empty( $country ) ) {
		$country = give_get_country();
	}

	$states = give_get_states( $country );
	if ( ! empty( $states ) ) {
		$args = array(
			'name'             => $field_name,
			'id'               => $field_name,
			'class'            => $field_name . '  give-select',
			'options'          => $states,
			'show_option_all'  => false,
			'show_option_none' => false,
			'placeholder' => $label,
		);
		$data = Give()->html->select( $args );
		$states_found = true;
	} else {
		$data = 'nostates';

		// Get the country list that does not have any states init.
		$no_states_country = give_no_states_country_list();

		// Check if $country code exists in the array key.
		if ( array_key_exists( $country, $no_states_country ) ) {
			$show_field = false;
		}

		// Get the country list that does not require states.
		$states_not_required_country_list = give_states_not_required_country_list();

		// Check if $country code exists in the array key.
		if ( array_key_exists( $country, $states_not_required_country_list ) ) {
			$states_require = false;
		}
	}
	$response = array(
		'success' => true,
		'states_found' => $states_found,
		'show_field' => $show_field,
		'states_label' => $label,
		'states_require' => $states_require,
		'data' => $data,
	);
	wp_send_json( $response );
}
add_action( 'wp_ajax_give_get_states', 'give_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_give_get_states', 'give_ajax_get_states_field' );

/**
 * Retrieve donation forms via AJAX for chosen dropdown search field.
 *
 * @since  1.0
 *
 * @return void
 */
function give_ajax_form_search() {
	global $wpdb;

	$search   = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$excludes = ( isset( $_GET['current_id'] ) ? (array) $_GET['current_id'] : array() );

	$results = array();
	if ( current_user_can( 'edit_give_forms' ) ) {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` = 'give_forms' AND `post_title` LIKE '%$search%' LIMIT 50" );
	} else {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` = 'give_forms' AND `post_status` = 'publish' AND `post_title` LIKE '%$search%' LIMIT 50" );
	}

	if ( $items ) {

		foreach ( $items as $item ) {

			$results[] = array(
				'id'   => $item->ID,
				'name' => $item->post_title,
			);
		}
	} else {

		$items[] = array(
			'id'   => 0,
			'name' => __( 'No forms found.', 'give' ),
		);

	}

	echo json_encode( $results );

	give_die();
}

add_action( 'wp_ajax_give_form_search', 'give_ajax_form_search' );
add_action( 'wp_ajax_nopriv_give_form_search', 'give_ajax_form_search' );

/**
 * Search the donors database via Ajax
 *
 * @since  1.0
 *
 * @return void
 */
function give_ajax_donor_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	if ( ! current_user_can( 'view_give_reports' ) ) {
		$donors = array();
	} else {
		$donors = $wpdb->get_results( "SELECT id,name,email FROM {$wpdb->prefix}give_customers WHERE `name` LIKE '%$search%' OR `email` LIKE '%$search%' LIMIT 50" );
	}

	if ( $donors ) {

		foreach ( $donors as $donor ) {

			$results[] = array(
				'id'   => $donor->id,
				'name' => $donor->name . ' (' . $donor->email . ')',
			);
		}
	} else {

		$donors[] = array(
			'id'   => 0,
			'name' => __( 'No donors found.', 'give' ),
		);

	}

	echo json_encode( $results );

	give_die();
}

add_action( 'wp_ajax_give_donor_search', 'give_ajax_donor_search' );


/**
 * Searches for users via ajax and returns a list of results
 *
 * @since  1.0
 *
 * @return void
 */
function give_ajax_search_users() {

	if ( current_user_can( 'manage_give_settings' ) ) {

		$search   = esc_sql( sanitize_text_field( $_GET['s'] ) );

		$get_users_args = array(
			'number' => 9999,
			'search' => $search . '*',
		);

		$get_users_args = apply_filters( 'give_search_users_args', $get_users_args );

		$found_users = apply_filters( 'give_ajax_found_users', get_users( $get_users_args ), $search );
		$results     = array();

		if ( $found_users ) {

			foreach ( $found_users as $user ) {

				$results[] = array(
					'id'   => $user->ID,
					'name' => esc_html( $user->user_login . ' (' . $user->user_email . ')' ),
				);
			}
		} else {

			$results[] = array(
				'id'   => 0,
				'name' => __( 'No users found.', 'give' ),
			);

		}

		echo json_encode( $results );

	}// End if().

	give_die();

}

add_action( 'wp_ajax_give_user_search', 'give_ajax_search_users' );


/**
 * Check for Price Variations (Multi-level donation forms)
 *
 * @since  1.5
 *
 * @return void
 */
function give_check_for_form_price_variations() {

	if ( ! current_user_can( 'edit_give_forms', get_current_user_id() ) ) {
		die( '-1' );
	}

	$form_id = intval( $_POST['form_id'] );
	$form    = get_post( $form_id );

	if ( 'give_forms' != $form->post_type ) {
		die( '-2' );
	}

	if ( give_has_variable_prices( $form_id ) ) {
		$variable_prices = give_get_variable_prices( $form_id );

		if ( $variable_prices ) {
			$ajax_response = '<select class="give_price_options_select give-select give-select" name="give_price_option">';

			if ( isset( $_POST['all_prices'] ) ) {
				$ajax_response .= '<option value="all">' . esc_html__( 'All Levels', 'give' ) . '</option>';
			}

			foreach ( $variable_prices as $key => $price ) {

				$level_text = ! empty( $price['_give_text'] ) ? esc_html( $price['_give_text'] ) : give_currency_filter( give_format_amount( $price['_give_amount'], array( 'sanitize' => false ) ) );

				$ajax_response .= '<option value="' . esc_attr( $price['_give_id']['level_id'] ) . '">' . $level_text . '</option>';
			}
			$ajax_response .= '</select>';
			echo $ajax_response;
		}
	}

	give_die();
}

add_action( 'wp_ajax_give_check_for_form_price_variations', 'give_check_for_form_price_variations' );


/**
 * Check for Variation Prices HTML  (Multi-level donation forms)
 *
 * @since  1.6
 *
 * @return void
 */
function give_check_for_form_price_variations_html() {
	if ( ! current_user_can( 'edit_give_payments', get_current_user_id() ) ) {
		wp_die();
	}

	$form_id    = ! empty( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : 0;
	$payment_id = ! empty( $_POST['payment_id'] ) ? intval( $_POST['payment_id'] ) : 0;
	$form       = get_post( $form_id );

	if ( 'give_forms' != $form->post_type ) {
		wp_die();
	}

	if ( ! give_has_variable_prices( $form_id ) || ! $form_id ) {
		esc_html_e( 'n/a', 'give' );
	} else {
		$prices_atts = '';
		if ( $variable_prices = give_get_variable_prices( $form_id ) ) {
			foreach ( $variable_prices as $variable_price ) {
				$prices_atts[ $variable_price['_give_id']['level_id'] ] = give_format_amount( $variable_price['_give_amount'], array( 'sanitize' => false ) );
			}
		}

		// Variable price dropdown options.
		$variable_price_dropdown_option = array(
			'id'               => $form_id,
			'name'             => 'give-variable-price',
			'chosen'           => true,
			'show_option_all'  => '',
			'show_option_none' => '',
			'select_atts'      => 'data-prices=' . esc_attr( json_encode( $prices_atts ) ),
		);

		if ( $payment_id ) {
			// Payment object.
			$payment = new Give_Payment( $payment_id );

			// Payment meta.
			$payment_meta                               = $payment->get_meta();
			$variable_price_dropdown_option['selected'] = $payment_meta['price_id'];
		}

		// Render variable prices select tag html.
		give_get_form_variable_price_dropdown( $variable_price_dropdown_option, true );
	}

	give_die();
}

add_action( 'wp_ajax_give_check_for_form_price_variations_html', 'give_check_for_form_price_variations_html' );
