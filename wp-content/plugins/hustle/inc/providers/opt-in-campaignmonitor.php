<?php

if( !class_exists("Opt_In_Campaignmonitor") ):

if( !class_exists( "CS_REST_General" ) )
	require_once Opt_In::$vendor_path . 'campaignmonitor/createsend-php/csrest_general.php';

if( !class_exists( "CS_REST_Subscribers" ) )
	require_once Opt_In::$vendor_path . 'campaignmonitor/createsend-php/csrest_subscribers.php';

if( !class_exists( "CS_REST_Clients" ) )
	require_once Opt_In::$vendor_path . 'campaignmonitor/createsend-php/csrest_clients.php';

if( !class_exists( "CS_REST_Lists" ) )
	require_once Opt_In::$vendor_path . 'campaignmonitor/createsend-php/csrest_lists.php';

class Opt_In_Campaignmonitor extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {

	const ID = "campaignmonitor";
	const NAME = "Campaignmonitor";

	/**
	 * @var $api AWeberAPI
	 */
	protected  static $api;

	/**
	 * @var
	 */
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
	 * @return CS_REST_General
	 */
	protected static function api( $api_key ){
		if( empty( self::$api ) ){
			try {
				self::$api = new CS_REST_General( array('api_key' => $api_key) );
				self::$errors = array();
			} catch (Exception $e) {
				self::$errors = array("api_error" => $e) ;
			}

		}

		return self::$api;
	}

	public function subscribe( Hustle_Module_Model $module, array $data ){
		$api_key    = self::_get_api_key( $module );
		$list_id    = self::_get_api_list_id( $module );

		$email = $data['email'];
		$name = array();

		if ( isset( $data['first_name'] ) ) {
			$name['first_name'] = $data['first_name'];
		}
		elseif ( isset( $data['f_name'] ) ) {
			$name['first_name'] = $data['f_name'];
		}
		if ( isset( $data['last_name'] ) ) {
			$name['last_name'] = $data['last_name'];
		}
		elseif ( isset( $data['l_name'] ) ) {
			$name['last_name'] = $data['l_name'];
		}
		$name = implode( ' ', $name );

		// Remove unwanted fields
		$old_data = $data;
		$data = array_diff_key( $data, array(
			'first_name' => '',
			'last_name' => '',
			'f_name' => '',
			'l_name' => '',
			'email' => '',
		) );

		$custom_fields = array();
		if( ! empty( $data ) ){
			foreach( $data as $key => $d ){
				$custom_fields[] = array(
					'Key' => $key,
					'Value' => $d,
				);
			}
		}

		$failed_custom_fields = 0;

		if ( ! empty( $custom_fields ) ) {
			$api_cf 	= new CS_REST_Lists( $list_id, array('api_key' => $api_key ) );

			foreach ( $custom_fields as $custom_field ) {
				$key 			= $custom_field['Key'];
				$meta_key 		= 'cm_field_' . $key;
				$label 			= $custom_field['Value'];
				$cm_field_meta 	= $module->get_meta( $meta_key );
				if ( $cm_field_meta ) {
					// No need to add, already added
					continue;
				}

				$cm_field = array(
					'FieldName' => $label,
					'Key'       => $key,
					'DataType'  => CS_REST_CUSTOM_FIELD_TYPE_TEXT, // We only support text for now,
					'Options'   => '',
					'VisibleInPreferenceCenter' => true,
				);

				if ( $api_cf->create_custom_field( $cm_field ) ) {
					$module->add_meta( $meta_key, $label );
				} else {
					$failed_custom_fields++;
				}
			}
		}

		$api = new CS_REST_Subscribers( $list_id, array('api_key' => $api_key ));
		$is_subscribed = $api->get( $email );
		$err = new WP_Error();

		if ( $failed_custom_fields > 0 ) {
			$old_data['error'] = __( 'Some custom fields are not added', Opt_In::TEXT_DOMAIN );
			$module->log_error( $old_data );
		}

		if ( $is_subscribed->was_successful() ) {
			$err->add("already_subscribed", __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
		} else {
			$res = $api->add( array(
				'EmailAddress' => $email,
				'Name'         => $name,
				'Resubscribe'  => true,
				'CustomFields' => $custom_fields
			) );

			if( $res->was_successful() ) {
				return array( 'success' => 'success' );
			} else {
				$err->add( 'request_error', __( 'Unexpeced error occurred. Please try again.', Opt_In::TEXT_DOMAIN ) );
				$data['error'] = __( 'Unable to add to subscriber list.', Opt_In::TEXT_DOMAIN );
				$module->log_error( $data );
			}
		}

		return $err;
	}

	function get_options( $module_id ){
		$cids = array();
		$lists = array();
		$clients = self::api( $this->api_key )->get_clients();
		if( !$clients->was_successful() ) return false;

		foreach( $clients->response as $client => $details ) {
			$cids[] = $details->ClientID;
		}

		if ( ! empty( $cids ) ) {
			foreach( $cids as $id ) {
				$client = new CS_REST_Clients( $id,  array('api_key' => $this->api_key) );
				$_lists = $client->get_lists();

				foreach ( $_lists->response as $key => $list ) {
					$lists[ $list->ListID ]['value'] = $list->ListID;
					$lists[ $list->ListID ]['label'] = $list->Name;

				}
			}
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

		$module     = Hustle_Module_Model::instance()->get( $module_id );
		$api_key    = self::_get_api_key( $module );;

		$api_key_tooltip = '<span class="wpoi-tooltip tooltip-right" tooltip="' . __('Once logged in, click on your profile picture at the top-right corner to open te menu, then click on Account Settings and finally click on API keys.', Opt_In::TEXT_DOMAIN) . '"><span class="dashicons dashicons-warning wpoi-icon-info"></span></span>';
		return array(
			"label" => array(
				"id"    => "optin_api_key_label",
				"for"   => "optin_api_key",
				"value" => __("Enter Your API Key:", Opt_In::TEXT_DOMAIN),
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
						"class"         => "wpmudev-input_text",
					),
					'refresh' => array(
						"id"    => "refresh_campaignmonitor_lists",
						"name"  => "refresh_campaignmonitor_lists",
						"type"  => "ajax_button",
						"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
						'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
					),
				)
			),
			"instructions" => array(
				"id" => "optin_api_instructions",
				"for" => "",
				"value" => __("To get your API key, log in to your <a href='https://login.createsend.com/l/?ReturnUrl=%2Faccount%2Fapikeys' target='_blank'>Campaign Monitor account</a>, then click on your profile picture at the top-right corner to open a menu, then select <strong>Account Settings</strong> and finally click on <strong>API keys</strong>.", Opt_In::TEXT_DOMAIN),
				"type" => "small",
			),
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


	private static function _get_api_list_id( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'list_id' );
	}

	static function add_custom_field( $fields, $module_id ) {
		$module     = Hustle_Module_Model::instance()->get( $module_id );
		$api_key    = self::_get_api_key( $module );
		$list_id    = self::_get_api_list_id( $module );

		$api_cf         = new CS_REST_Lists( $list_id, array( 'api_key' => $api_key ) );
		$custom_fields  = $api_cf->get_custom_fields();

		foreach ( $fields as $field ) {
			$exist      = false;
			$key        = $field['name'];
		    $meta_key   = "cm_field_{$key}";
			if ( ! empty( $custom_fields ) && ! empty( $custom_fields->response ) ) {
				foreach ( $custom_fields->response as $custom_field ) {
					if ( $custom_field->FieldName == $field['label'] ) {
						$exist = true;
					}
					$module->add_meta( "cm_field_". $custom_field->Key, $custom_field->FieldName );
				}
			}

			if ( false === $exist ) {
				$cm_field = array(
					'FieldName' => $field['label'],
					'Key'       => $key,
					'DataType'  => CS_REST_CUSTOM_FIELD_TYPE_TEXT, // We only support text for now,
					'Options'   => '',
					'VisibleInPreferenceCenter' => true,
				);
				$api_cf->create_custom_field($cm_field);
				$module->add_meta( $meta_key, $field['label'] );
				$exist = true;
			}
		}

		if ( $exist ) {
			return array( 'success' => true, 'field' => $fields );
		} else {
			return array( 'error' => true, 'code' => 'cannot_create_custom_field' );
		}
	}
}
endif;