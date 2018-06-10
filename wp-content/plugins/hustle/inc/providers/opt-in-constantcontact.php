<?php


if( !class_exists("Opt_In_ConstantContact") ):

class Opt_In_ConstantContact extends Opt_In_Provider_Abstract  implements  Opt_In_Provider_Interface {

	const ID = "constantcontact";
	const NAME = "ConstantContact";

	protected static $errors;


	static function instance() {
		return new self;
	}

	/**
	 * @return bool|Opt_In_HubSpot_Api
	 */
	function api() {
		return self::static_api();
	}

	static function static_api() {
		if ( ! class_exists( 'Opt_In_ConstantContact_Api' ) ){
			require_once 'opt-in-constantcontact-api.php';
		}


		if ( class_exists( 'Opt_In_ConstantContact_Api' ) ){
			$api = new Opt_In_ConstantContact_Api();
			return $api;
		} else {
			return new WP_Error( 'error', __( "API Class coul not be initialized", Opt_In::TEXT_DOMAIN )  );
		}


	}

	/**
	 * Updates api option
	 *
	 * @param $option_key
	 * @param $option_value
	 * @return bool
	 */
	function update_option( $option_key, $option_value ) {
		return update_site_option(self::ID . "_" . $option_key, $option_value);
	}

	/**
	 * Retrieves api option from db
	 *
	 * @param $option_key
	 * @param $default
	 * @return mixed
	 */
	function get_option( $option_key, $default ) {
		return get_site_option(self::ID . "_" . $option_key, $default);
	}


	function subscribe(Hustle_Module_Model $module, array $data) {
		$err = new WP_Error();


		try {
			$api = $this->api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			$email_list = self::_get_email_list( $module );
			$existing_contact = $api->email_exist( $data['email'], $email_list );
			if ( $existing_contact == true) {
				$err->add( 'email_exist', __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}

			$first_name = '';
			$last_name = '';

			if ( isset( $data['first_name'] ) ) {
				$first_name = $data['first_name'];
			}
			elseif ( isset( $data['f_name'] ) ) {
				$first_name = $data['f_name']; // Legacy call
			}
			if ( isset( $data['last_name'] ) ) {
				$last_name = $data['last_name'];
			}
			elseif ( isset( $data['l_name'] ) ) {
				$last_name = $data['l_name']; // Legacy call
			}

			$custom_fields = array_diff_key( $data, array(
				'email' => '',
				'first_name' => '',
				'last_name' => '',
				'f_name' => '',
				'l_name' => '',
			) );
			$custom_fields = array_filter( $custom_fields );

			if ( is_object( $existing_contact ) ) {
				$response = $api->updateSubscription( $existing_contact, $first_name, $last_name, $email_list, $custom_fields );
			} else {
				$response = $api->subscribe( $data['email'], $first_name, $last_name, $email_list, $custom_fields );
			}

			if ( isset( $response ) ) {
				self::$errors['success'] = 'success';
			    return true;
			}

		} catch ( Exception $e ) {
			$err->add( 'subscribe_error', __( 'Something went wrong. Please try again.', Opt_In::TEXT_DOMAIN ) );
			$error_message = json_decode( $e->getMessage() );

			if ( is_array( $error_message ) ) {
				$error_message = array_pop( $error_message );
				$error_message = $error_message->error_message;
			}

			$data['error'] = $error_message;

			$module->log_error( $data );
		}

		return $err;
	}

	function get_options( $module_id ) {
		return array();
	}

	function get_lists( $api ) {

	    $lists = array();

		try {
			$lists_data = $api->get_contact_lists();
			foreach( $lists_data as $list ){
				$list = (array) $list;
				$lists[ $list['id'] ]['value'] = $list['id'];
				$lists[ $list['id'] ]['label'] = $list['name'];
			}
		} catch (Exception $e) {

		}
		return $lists;
	}


	function get_account_options( $module_id ) {
		if (!$this->php_version_ok()) {
			return array(
				'auth_code_label' => array(
					"id" => "auth_code_label",
					"for" => "constant_contact_authorization_url",
					"value" => sprintf(
						__('Constant Contact integration requires PHP version 5.3 or higher installed.', Opt_In::TEXT_DOMAIN)
					),
					"type" => "label",
				)
			);
		}

		$api = $this->api();
		if ( is_wp_error( $api ) ) {
			return array(
				'auth_code_label' => array(
					"id"    => "auth_code_label",
					"for"   => "constant_contact_authorization_url",
					"value" => __('An error occured initializing Constant Contact', Opt_In::TEXT_DOMAIN),
					"type"  => "label",
				)
			);
		}

	    $access_token = $api->get_token( 'access_token' );

		if ( !$access_token ) {

	        $default_options = array(
		        'auth_code_label' => array(
			        "id"    => "auth_code_label",
			        "for"   => "constant_contact_authorization_url",
			        "value" => sprintf(
				        __('Please <a href="%s" class="constantcontact-authorize" data-optin="%s">click here</a> to connect to ConstantContact. You will be asked to give us access to your ConstantContact account and then be redirected back to this screen.', Opt_In::TEXT_DOMAIN),
				        $api->get_authorization_uri( $module_id, true, $this->current_page ), $module_id
			        ),
			        "type" => "label",
		        ),
				array(
					'type' => 'notice',
					'value' => __( 'ConstantContact requires your site to have SSL certificate.', Opt_In::TEXT_DOMAIN ),
					'class' => 'wph-label--notice wph-label--persist_notice'
				)
			);

			if ( is_ssl() ) {
				unset( $default_options['notice'] );
			}

			return $default_options;
		}

	    $email_list = '';
	    if ( $module_id ) {
			$module = Hustle_Module_Model::instance()->get( $module_id );
			$email_list = self::_get_email_list( $module );
	    }

		$list = $this->get_lists( $api );

		$default_options =  array(
			"auth_label" => array(
				"id" => "auth_code_label",
			    "for" => "constant_contact_authorization_url",
			    "value" => sprintf(
				    __('Please <a href="%s" class="constantcontact-authorize" data-optin="%s">click here</a> to reconnect to ConstantContact. You will be asked to give us access to your ConstantContact account and then be redirected back to this screen.', Opt_In::TEXT_DOMAIN),
				    $api->get_authorization_uri( $module_id, true, $this->current_page ), $module_id
			    ),
			    "type" => "label",
			),
			"notice" => array(
				'type' => 'notice',
				'value' => __( 'ConstantContact requires your site to have SSL certificate.', Opt_In::TEXT_DOMAIN ),
				'class' => 'wpmudev-label--notice wpmudev-label--persist_notice'
			),
			"label" => array(
				"id"    => "optin_email_list_label",
				"for"   => "optin_email_list",
				"value" => __( "Choose Email List:", Opt_In::TEXT_DOMAIN ),
				"type"  => "label",
			),
			"choose_email_list" => array(
				"type"      => 'select',
				'name'      => "optin_email_list",
				'id'        => "wph-email-provider-lists",
				"default"   => "",
				'options'   => $list,
				'selected'  => $email_list,
				"attributes" => array(
					'class' => "wpmudev-select constantContact_optin_email_list"
				)
			)
		);

		if ( is_ssl() ) {
			unset( $default_options['notice'] );
		}

	    return $default_options;
	}

	/**
	 * Get Provider Details
	 * General function to get provider details from database based on key
	 *
	 * @param Hustle_Module_Model $module
	 * @param String $field - the field name
	 *
	 * @return String
	 */
	private static function _get_provider_details( Hustle_Module_Model $module, $field ) {
		$details = '';
		$name = self::ID;
		if ( !is_null( $module->content->email_services )
			&& isset( $module->content->email_services[$name] )
			&& isset( $module->content->email_services[$name][$field] ) ) {

			$details = $module->content->email_services[$name][$field];
		}
		return $details;
	}

	private static function _get_email_list( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'list_id' );
	}

	function is_authorized() {
		return $this->php_version_ok();
	}

	private function php_version_ok() {
		return version_compare( PHP_VERSION, '5.3', '>=' );
	}

}
endif;