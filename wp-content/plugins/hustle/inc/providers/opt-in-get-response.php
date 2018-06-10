<?php

if( !class_exists("Opt_In_Get_Response") ):

include_once 'opt-in-get-response-api.php';

/**
 * Defines and adds neeed methods for GetResponse email service provider
 *
 * Class Opt_In_Get_Response
 */
class Opt_In_Get_Response extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {

	const ID = "getresponse";
	const NAME = "GetResponse";


	/**
	 * @var $api GetResponse
	 */
	protected  static $api;

	protected  static $errors;


	static function instance(){
		return new self;
	}

	/**
	 * Updates api option
	 *
	 * @param $option_key
	 * @param $option_value
	 * @return bool
	 */
	function update_option($option_key, $option_value){
		return update_site_option( self::ID . "_" . $option_key, $option_value);
	}

	/**
	 * Retrieves api option from db
	 *
	 * @param $option_key
	 * @param $default
	 * @return mixed
	 */
	function get_option($option_key, $default){
		return get_site_option( self::ID . "_" . $option_key, $default );
	}

	/**
	 * @param $api_key
	 * @return Opt_In_Get_Response_Api
	 */
	protected static function api( $api_key ){

		if( empty( self::$api ) ){
			try {
				self::$api = new Opt_In_Get_Response_Api( $api_key, array("debug" => true) );
				self::$errors = array();
			} catch (Exception $e) {
				self::$errors = array("api_error" => $e) ;
			}

		}

		return self::$api;
	}

	/**
	 * Adds contact to the the campaign
	 *
	 * @param Hustle_Module_Model $module
	 * @param array $data
	 * @return array|mixed|object|WP_Error
	 */
	public function subscribe( Hustle_Module_Model $module, array $data ){

		$api_key    = self::_get_api_key( $module );
		$list_id    = self::_get_email_list( $module );

		$email =  $data['email'];

		$geo = new Opt_In_Geo();

		$name = array();

		if ( ! empty( $data['first_name'] ) ) {
			$name['first_name'] = $data['first_name'];
		}
		elseif ( ! empty( $data['f_name'] ) ) {
			$name['first_name'] = $data['f_name']; // Legacy
		}
		if ( ! empty( $data['last_name'] ) ) {
			$name['last_name'] = $data['last_name'];
		}
		elseif ( ! empty( $data['l_name'] ) ) {
			$name['last_name'] = $data['l_name']; // Legacy
		}

		$new_data = array(
			'email'         => $email,
			"dayOfCycle"    => apply_filters( "hustle_optin_get_response_cycle", "0" ),
			'campaign'      => array(
				"campaignId" => $list_id
			),
			"ipAddress"     => $geo->get_user_ip()
		);

		if( count( $name ) )
			$new_data['name'] = implode(" ", $name);

		// Extra fields
		$extra_data = array_diff_key( $data, array(
			'email' => '',
			'first_name' => '',
			'last_name' => '',
			'f_name' => '',
			'l_name' => '',
		) );
		$extra_data = array_filter( $extra_data );

		if ( ! empty( $extra_data ) ) {
			$new_data['customFieldValues'] = array();

			foreach ( $extra_data as $key => $value ) {
				$meta_key = 'gr_field_' . $key;
				$custom_field_id = $module->get_meta( $meta_key );
				$custom_field = array(
					'name' => $key,
					'type' => 'text', // We only support text for now
					'hidden' => false,
					'values' => array(),
				);

				if ( empty( $custom_field_id ) ) {
					$custom_field_id = self::api( $api_key )->add_custom_field( $custom_field );

					if ( ! empty( $custom_field_id ) ) {
						$module->add_meta( $meta_key, $custom_field_id );
					}
				}
				$new_data['customFieldValues'][] = array( 'customFieldId' => $custom_field_id, 'value' => array( $value ) );
			}
		}

		$res = self::api( $api_key )->subscribe( $new_data );

		if ( is_wp_error( $res ) ) {
			$error_code = $res->get_error_code();
			$error_message = $res->get_error_message( $error_code );

			if ( preg_match( '%Conflict%', $error_message ) ) {
				$res->add( $error_code, __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
			} else {
				$data['error'] = $error_message;
				$module->log_error( $data );
			}
		}

		return $res;
	}

	/**
	 * Retrieves initial options of the GetResponse account with the given api_key
	 *
	 * @param $module_id
	 * @return array
	 */
	function get_options( $module_id ){
		$campains = self::api( $this->api_key )->get_campains();

		if( is_wp_error( $campains ) )
			wp_send_json_error(  __("No active campaign is found for the API. Please set up a campaign in GetResponse or check your API.", Opt_In::TEXT_DOMAIN)  );

		$lists = array();
		foreach(  ( array) $campains as $campain ){
			$lists[ $campain->campaignId ]['value'] = $campain->campaignId;
			$lists[ $campain->campaignId ]['label'] = $campain->name;
		}

		$first = count( $lists ) > 0 ? reset( $lists ) : "";
		if( !empty( $first ) )
			$first = $first['value'];

		return  array(
			"label" => array(
				"id"    => "optin_email_list_label",
				"for"   => "optin_email_list",
				"value" => __("Choose campaign:", Opt_In::TEXT_DOMAIN),
				"type"  => "label",
			),
			"choose_email_list" => array(
				"type"      => 'select',
				'name'      => "optin_email_list",
				'id'        => "wph-email-provider-lists",
				"default"   => "",
				'options'   => $lists,
				'value'     => $first,
				'selected'  => $first,
				"attributes" => array(
					"data-nonce"    => wp_create_nonce("get_response_choose_campaign"),
					'class'         => "wpmudev-select get_response_choose_campaign"
				)
			)
		);

	}

	/**
	 * Returns initial account options
	 *
	 * @param $module_id
	 * @return array
	 */
	function get_account_options( $module_id ){

		$module     = Hustle_Module_Model::instance()->get( $module_id );
		$api_key    = self::_get_api_key( $module );

		return array(
			"label" => array(
				"id"    => "optin_api_key_label",
				"for"   => "optin_api_key",
				"value" => __("Enter your API key:", Opt_In::TEXT_DOMAIN),
				"type"  => "label",
			),
			"wrapper" => array(
				"id"    => "wpoi-get-lists",
				"class" => "wpmudev-provider-group",
				"type"  => "wrapper",
				"elements" => array(
					"api_key" => array(
						"id"            => "optin_api_key",
						"name"          => "optin_api_key",
						"type"          => "text",
						"default"       => "",
						"value"         => $api_key,
						"placeholder"   => "",
						"class"         => "wpmudev-input_text"
					),
					'refresh' => array(
						"id"    => "refresh_get_response_lists",
						"name"  => "refresh_get_response_lists",
						"type"  => "ajax_button",
						"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
						'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
					),
				)
			),
			"instructions" => array(
				"id"    => "optin_api_instructions",
				"for"   => "",
				"value" => __("Log in to your <a href='https://app.getresponse.com/manage_api.html' target='_blank'>GetResponse account</a> to get your API (version 3) Key.", Opt_In::TEXT_DOMAIN),
				"type"  => "small",
			),
		);
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

	private static function _get_api_key( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'api_key' );
	}

	function is_authorized(){
		return true;
	}

	static function add_custom_field( $fields, $module_id ) {

		$module     = Hustle_Module_Model::instance()->get( $module_id );
		$api_key    = self::_get_api_key( $module );

		$api = self::api( $api_key );
		$api_fields = $api->get_custom_fields();

		foreach ( $fields as $field ) {
			$type = ! in_array( $field['type'], array( 'text', 'number' ) ) ? 'text' : $field['type'];
			$key = $field['name'];
			$exist = false;

			// Check for existing custom fields
			if ( ! is_wp_error( $api_fields ) && is_array( $api_fields ) ) {
				foreach ( $api_fields as $custom_field ) {
					$name = $custom_field->name;
					$custom_field_id = $custom_field->customFieldId;
					$meta_key = "gr_field_{$name}";

					// Update meta
					$module->add_meta( $meta_key, $custom_field_id );

					if ( $name == $key ) {
						$exist = true;
					}
				}
			}

			// Add custom field if it doesn't exist
			if ( false === $exist ) {
				$custom_field = array(
					'name' => $key,
					'type' => $type,
					'hidden' => false,
					'values' => array(),
				);
				$custom_field_id = $api->add_custom_field( $custom_field );
				$module->add_meta( "gr_field_{$key}", $custom_field_id );
			}
		}

		return array( 'success' => true, 'field' => $field );
	}
}

endif;