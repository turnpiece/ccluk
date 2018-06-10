<?php
if( !class_exists("Opt_In_Aweber") ):

if( !class_exists( "AWeberAPI" ) )
	require_once Opt_In::$vendor_path . 'aweber/aweber/aweber_api/aweber_api.php';

class Opt_In_Aweber extends Opt_In_Provider_Abstract  implements  Opt_In_Provider_Interface {

	const ID = "aweber";
	const NAME = "AWeber";

	const APP_ID = 'b0cd0152';

	const AUTH_CODE = "aut_code";
	const CONSUMER_KEY = "consumer_key";
	const CONSUMER_SECRET = "consumer_secret";
	const ACCESS_TOKEN = "access_token";
	const ACCESS_SECRET = "access_secret";

	/**
	 * @var $api AWeberAPI
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
	function get_option($option_key, $default ){
		return get_site_option( self::ID . "_" . $option_key, $default );
	}

	/**
	 * Helper function get an option in static mode.
	 **/
	static function static_get_option( $option_key, $default ) {
		return get_site_option( self::ID . "_" . $option_key, $default );
	}

	/**
	 * @param $api_key
	 * @param $secret
	 * @return AWeberAPI
	 */
	protected static function api( $api_key, $secret ){

		if( empty( self::$api ) ){
			try {
				self::$api = new AWeberAPI( $api_key, $secret );
				self::$errors = array();
			} catch (AWeberException $e) {
				self::$errors = array("api_error" => $e) ;
			}

		}
		self::$api->adapter->debug = false;
		return self::$api;
	}

	function subscribe( Hustle_Module_Model $module, array $data  ){

		$consumerKey = $this->get_option( self::CONSUMER_KEY, false );
		$consumerSecret = $this->get_option( self::CONSUMER_SECRET, false );
		$accessToken = $this->get_option( self::ACCESS_TOKEN, false );
		$accessSecret = $this->get_option( self::ACCESS_SECRET, false );

		if( !$consumerKey ||  !$consumerSecret || !$accessToken || !$accessSecret)
			return false;

		$api = self::api( $consumerKey, $consumerSecret );

		$account =  $api->getAccount($accessToken, $accessSecret);
		$account_id =  isset( $account->data, $account->data['id'] ) ? $account->data['id'] : false;

		if( !$account_id )
			return false;

		$list_id   = $this->_get_list_id( $module );

		try {
			$URL = "/accounts/{$account_id}/lists/{$list_id}";
			$list = $account->loadFromUrl($URL);
			$subscribe_data = $data;
			$name = array();

			if ( ! empty( $data['first_name'] ) ) {// Check first_name field first
				$name['first_name'] = $data['first_name'];
				unset( $subscribe_data['first_name'] );
			}
			elseif ( ! empty( $data['f_name'] ) ) {// Legacy field name
				$name['first_name'] = $data['f_name'];
				unset( $subscribe_data['f_name'] );
			}
			if ( ! empty( $data['last_name'] ) ) { // Add last_name
				$name['last_name'] = $data['last_name'];
				unset( $subscribe_data['last_name'] );
			}
			elseif ( ! empty( $data['l_name'] ) ) {// Check legacy f_name
				$name['last_name'] = $data['l_name'];
				unset( $subscribe_data['l_name'] );
			}
			$subscribe_data['name'] = implode( ' ', $name );
			$custom_fields = array_diff_key( $data, array(
				'first_name' => '',
				'last_name' => '',
				'l_name' => '',
				'f_name' => '',
				'email' => '',
			) );

			if ( ! empty( $custom_fields ) ) {
				$subscribe_data['custom_fields'] = array();

				foreach ( $custom_fields as $key => $value ) {
					$field = $module->get_custom_field( 'name', $key );
					$label = $field['label'];
					$subscribe_data['custom_fields'][ $label ] = $value;
					unset( $subscribe_data[ $key ] );
				}
			}

			$err = new WP_Error();
			$findByEmail = $list->subscribers->find( array( 'email' => $subscribe_data['email'] ) );

			if ( ! empty( $findByEmail ) && ! empty( $findByEmail->data ) && ! empty( $findByEmail->data['entries'] ) ) {
				$err->add( 'email_exist', __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}

			$subscriber = $list->subscribers->create($subscribe_data);

			if ( empty( $subscriber ) ) {
				$data['error'] = __( 'Something went wrong. Unable to add subscriber', Opt_In::TEXT_DOMAIN );
				$module->log_error( $data );
			} else if( ! empty( $subscriber->data ) && ! empty( $subscribe_data['custom_fields'] ) ) {
				// Let's double check if all custom fields are successfully added
				$found_missing_field = 0;

				foreach ( array_filter( $subscribe_data['custom_fields'] ) as $label => $field ) {
					if ( ! isset( $subscriber->data['custom_fields'][ $label ] ) || empty( $subscriber->data['custom_fields'][ $label ] ) ) {
						$found_missing_field++;
					}
				}

				if ( $found_missing_field > 0 ) {
					$data['error'] = __( 'Some fields are not successfully added.', Opt_In::TEXT_DOMAIN );
					$module->log_error( $data );
				}
			}

			return $subscriber;

		} catch(Exception $e) {
			return self::$errors['subcription'] =  $e;
		}
	}

	function get_options( $module_id ){

		if( $this->get_option( self::AUTH_CODE, '' ) !== $this->api_key ){

			list($consumerKey, $consumerSecret, $accessToken, $accessSecret) = AWeberAPI::getDataFromAweberID( $this->api_key );
			$this->update_option( self::CONSUMER_KEY, $consumerKey );
			$this->update_option( self::CONSUMER_SECRET, $consumerSecret );
			$this->update_option( self::ACCESS_TOKEN, $accessToken );
			$this->update_option( self::ACCESS_SECRET, $accessSecret );

			$this->update_option( self::AUTH_CODE, $this->api_key );

		}else{
			$consumerKey = $this->get_option( self::CONSUMER_KEY, '' );
			$consumerSecret = $this->get_option( self::CONSUMER_SECRET, '' );
			$accessToken = $this->get_option( self::ACCESS_TOKEN, '' );
			$accessSecret = $this->get_option( self::ACCESS_SECRET, '' );
		}

		$account = $this->api( $consumerKey, $consumerSecret )->getAccount( $accessToken, $accessSecret );

		$data = (array) $account->lists->data;

		$lists = array();
		foreach( (array) $data['entries'] as $list ){
			$list = (array) $list;
			$lists[ $list['id'] ]['value'] = $list['id'];
			$lists[ $list['id'] ]['label'] = $list['name'];
		}


		$first = count( $lists ) > 0 ? reset( $lists ) : "";
		if( !empty( $first ) )
			$first = $first['value'];

		return array(
			"label" => array(
				"id"    => "optin_email_list_label",
				"for"   => "optin_email_list",
				"value" => __("Choose Email List:", Opt_In::TEXT_DOMAIN),
				"type"  => "label",
			),
			"choose_email_list" => array(
				"label"         => __("Choose Email List:", Opt_In::TEXT_DOMAIN),
				"type"          => 'select',
				'name'          => "optin_email_list",
				'id'            => "wph-email-provider-lists",
				"default"       => "",
				'options'       => $lists,
				'value'         => $first,
				'selected'      => $first,
				"attributes"    => array(
					'class'         => "wpmudev-select"
				)
			)
		);

	}



	function get_account_options( $module_id ){

		return array(
			'auth_code_label' => array(
				"id"    => "auth_code_label",
				"for"   => "aweber_authorization_url",
				"value" => sprintf(
					__('Please <a href="%s" target="_blank">click here</a> to connect to Aweber service then paste the authorization code below', Opt_In::TEXT_DOMAIN),
					"https://auth.aweber.com/1.0/oauth/authorize_app/" . self::APP_ID
				),
				"type"  => "label",
			),
			"wrapper" => array(
				"id"    => "wpoi-get-lists",
				"class" => "wpmudev-provider-group",
				"type"  => "wrapper",
				"elements" => array(
					"consumer_key" => array(
						"id"            => "optin_api_key",
						"name"          => "optin_api_key",
						"label"         => __("Customer key", Opt_In::TEXT_DOMAIN),
						"type"          => "text",
						"default"       => "",
						"value"         => "",
						"class"         => "wpmudev-input_text",
						"placeholder"   => __("Please enter authorization code", Opt_In::TEXT_DOMAIN)
					),
					'refresh' => array(
						"id"    => "refresh_aweber_lists",
						"name"  => "refresh_aweber_lists",
						"type"  => "ajax_button",
						"value" => __("Fetch Lists", Opt_In::TEXT_DOMAIN),
						'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
					),
				)
			)
		);
	}


	function is_authorized(){
		return true;
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


	private static function _get_api_key( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'api_key' );
	}

	private static function _get_list_id( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'list_id' );
	}

	static function add_custom_field( $fields, $module_id ) {
		$consumerKey    = self::static_get_option( self::CONSUMER_KEY, false );
		$consumerSecret = self::static_get_option( self::CONSUMER_SECRET, false );
		$accessToken    = self::static_get_option( self::ACCESS_TOKEN, false );
		$accessSecret   = self::static_get_option( self::ACCESS_SECRET, false );

		$module         = Hustle_Module_Model::instance()->get( $module_id );
		$services       = $module->get_content()->email_services;
		$list_id        = "";

		if( !is_null( $services ) && isset( $services['aweber'] ) ) {
			if ( isset( $services['aweber']['list_id'] ) ) {
				$list_id = $services['aweber']['list_id'];
			}
		}

		if( $consumerKey && $consumerSecret && $accessToken && $accessSecret && $list_id ) {
			$api = self::api( $consumerKey, $consumerSecret );
			$account =  $api->getAccount($accessToken, $accessSecret);
			$account_id =  isset( $account->data, $account->data['id'] ) ? $account->data['id'] : false;

			if( $account_id ) {
				$URL = "/accounts/{$account_id}/lists/{$list_id}";
				$list = $account->loadFromUrl($URL);
				$custom_fields = $list->custom_fields;

				foreach ( $fields as $field ) {
					$exist = false;
					$label = $field['label'];

					if ( $custom_fields && ! empty( $custom_fields->data ) && ! empty( $custom_fields->data['entries'] ) ) {

						foreach ( $custom_fields->data['entries'] as $custom_field ) {
							if ( $custom_field['name'] == $label ) {
								$exist = true;
							}
						}
					}

					if ( false === $exist ) {
						// Create custom field
						$custom_field = array( 'name' => $label );
						$add = $list->custom_fields->create( $custom_field );
						$exist = true;
					}
				}
			}
		}

		if ( $exist ) {
			return array( 'success' => true, 'fields' => $fields );
		}

		return array( 'error' => true, 'code' => 'cannot_create_custom_field' );
	}
}
endif;