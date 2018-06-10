<?php
/**
 * Convertkit Email Integration
 *
 * @class Opt_In_ConvertKit
 * @version 2.0.3
 **/
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Opt_In_ConvertKit' ) ) :

	include_once 'opt-in-convertkit-api.php';

	class Opt_In_ConvertKit extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {

		const ID = "convertkit";
		const NAME = "ConvertKit";

		/**
		* @var $api ConvertKit
		*/
		protected  static $api;
		protected  static $errors;

		static function instance() {
			return new self;
		}

		/**
		* @param $api_key
		* @return Opt_In_ConvertKit_Api
		*/
		protected static function api( $api_key, $api_secret = '' ){

			if( empty( self::$api ) ){
				try {
					self::$api = new Opt_In_ConvertKit_Api( $api_key, $api_secret );
					self::$errors = array();
				} catch (Exception $e) {
					self::$errors = array("api_error" => $e) ;
				}

			}
			return self::$api;
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

		function get_options( $module_id ) {
			$forms = self::api( $this->api_key )->get_forms();
			if( is_wp_error( $forms ) ) {
				wp_send_json_error(  __("No active form is found for the API. Please set up a form in ConvertKit or check your API.", Opt_In::TEXT_DOMAIN)  );
			}

			$lists = array();
			foreach(  ( array) $forms as $form ){
				$lists[ $form->id ]['value'] = $form->id;
				$lists[ $form->id ]['label'] = $form->name;
			}

			$first = count( $lists ) > 0 ? reset( $lists ) : "";
			if( !empty( $first ) ) $first = $first['value'];

			return  array(
				"label" => array(
					"id" => "optin_email_list_label",
					"for" => "optin_email_list",
					"value" => __("Choose a form:", Opt_In::TEXT_DOMAIN),
					"type" => "label",
				),
				"choose_email_list" => array(
					"type" 			=> 'select',
					'name' 			=> "optin_email_list",
					'id' 			=> "wph-email-provider-lists",
					"default" 		=> "",
					'options' 		=> $lists,
					'value' 		=> $first,
					'selected' 		=> $first,
					"attributes" 	=> array(
						"data-nonce" 	=> wp_create_nonce("convert_kit_choose_form"),
						'class' 		=> "wpmudev-select convert_kit_choose_form"
					)
				)
			);
		}

		function get_account_options( $module_id ) {
			$link = '<a href="https://app.convertkit.com/account/edit" target="_blank">ConvertKit</a>';
			$instruction = sprintf( __( 'Log in to your %s account to get your API Key.', Opt_In::TEXT_DOMAIN ), $link );

			$module 	= Hustle_Module_Model::instance()->get( $module_id );
			$api_key 	= $this->_get_api_key( $module );
			$api_secret = $this->_get_api_secret( $module );

			$options = array(
				'optin_api_secret_label' => array(
					'id' 	=> 'optin-api-secret-label',
					'for' 	=> 'optin_api_secret',
					'value' => __("Enter your API Secret:", Opt_In::TEXT_DOMAIN),
					'type' 	=> 'label',
				),
				'optin_api_secret_wrapper' => array(
					'id' 	=> 'wpoi-api-secret-wrapper',
					'class' => 'wpmudev-provider-group',
					'type' 	=> 'wrapper',
					'elements' => array(
						'api_secret' => array(
							'id' 			=> 'optin_api_secret',
							'name' 			=> 'optin_api_secret',
							'type' 			=> 'text',
							'default' 		=> '',
							'value' 		=> $api_secret,
							'placeholder' 	=> '',
							"class"         => "wpmudev-input_text",
						),
					)
				),
				'label' => array(
					'id' 	=> 'optin_api_key_label',
					'for' 	=> 'optin_api_key',
					'value' => __("Enter your API Key:", Opt_In::TEXT_DOMAIN),
					'type' 	=> 'label',
				),
				'wrapper' => array(
					'id' 	=> 'wpoi-get-lists',
					'class' => 'wpmudev-provider-group',
					'type' 	=> 'wrapper',
					'elements' => array(
						'api_key' => array(
							'id' 			=> 'optin_api_key',
							'name' 			=> 'optin_api_key',
							'type' 			=> 'text',
							'default' 		=> '',
							'value' 		=> $api_key,
							'placeholder' 	=> '',
							"class"         => "wpmudev-input_text",
						),
						'refresh' => array(
							'id' 	=> 'refresh_get_response_lists',
							'name' 	=> 'refresh_get_response_lists',
							'type' 	=> 'ajax_button',
							"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Forms", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
							'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
						),
					)
				),
				'instruction' => array(
					'id' 	=> 'optin_convertkit_instruction',
					'type' 	=> 'small',
					'value' => $instruction,
					'for' 	=> '',
				),
			);

			return $options;
		}

		function is_authorized() {
			return true;
		}

		function exclude_args_fields() {
			return array( 'api_key', 'api_secret' );
		}

		/**
		 * Prevents default selected list from showing
		 *
		 * @param $val
		 * @param $module Hustle_Module_Model
		 * @return bool
		 */
		public static function show_selected_list(  $val, $module  ){
			if( $module->content->active_email_service !== Opt_In_ConvertKit::ID ) return true;
			return false;
		}

		/**
		 * Renders selected list row
		 *
		 * @param $module Hustle_Module_Model
		 */
		public static function render_selected_form( $module ){
			$list_id 	= self::_get_email_list( $module );
			if( $module->content->active_email_service !== Opt_In_ConvertKit::ID || !$list_id ) return;
			$property = maybe_unserialize(self::instance()->get_option('lists', false));
			if ( $property && isset($property['choose_email_list']) ) {
				$options = ( isset($property['choose_email_list']['options']) )
					? $property['choose_email_list']['options']
					: false;
				$list_id = ( $options && isset($options[$list_id]) )
					? $options[$list_id]['label']
					: $list_id;
			}
			printf( __("Selected form: <strong>%s</strong> (Press the GET FORMS button to update value)", Opt_In::TEXT_DOMAIN), $list_id );
		}

		/**
		* Adds subscribers to the form
		*
		* @param Hustle_Module_Model $module
		* @param array $data
		* @return array|mixed|object|WP_Error
		*/
		public function subscribe( Hustle_Module_Model $module, array $data ) {

			$api_secret = self::_get_api_secret( $module );
			$api_key 	= self::_get_api_key( $module );
			$list_id 	= self::_get_email_list( $module );

			if ( !isset($data['email']) ) return false;

			// deal with custom fields first
			$custom_fields = array(
				'ip_address' => array(
					'label' => 'IP Address'
				)
			);
			$additional_fields = $module->get_design()->__get( 'module_fields' );
			$subscribe_data_fields = array();

			if ( $additional_fields && is_array($additional_fields) && count($additional_fields) > 0 ) {
				foreach( $additional_fields as $field ) {
					// skip defaults
					if ( $field['name'] == 'first_name' || $field['name'] == 'email' ) {
						continue;
					}
					$meta_key 	= 'cv_field_' . $field['name'];
					$meta_value = $module->get_meta( $meta_key );
					$field_name = $field['name'];

					if ( ! $meta_value || $meta_value != $field['label'] ) {
						$custom_fields[$field_name] = array(
							'label' => $field['label']
						);
					}

					if ( isset($data[$field_name]) ) {
						$subscribe_data_fields[$field_name] = $data[$field_name];
					}
				}
			}

			$err = new WP_Error();

			if ( ! $this->maybe_create_custom_fields( $module, $custom_fields ) ) {
				$data['error'] = __( 'Unable to add custom field.', Opt_In::TEXT_DOMAIN );
				$module->log_error( $data );
				$err->add( 'server_error', __( 'Something went wrong. Please try again.', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}

			// subscription
			$geo = new Opt_In_Geo();
			$subscribe_data = array(
				"api_key" 	=> $api_key,
				"name" 		=> ( isset( $data['first_name'] ) ) ? $data['first_name'] : '',
				"email" 	=> $data['email'],
				"fields" 	=> array(
					"ip_address" => $geo->get_user_ip()
				)
			);
			$subscribe_data['fields'] = wp_parse_args( $subscribe_data_fields, $subscribe_data['fields'] );

			if ( $this->email_exist( $data['email'], $api_key, $api_secret ) ) {
				$err->add( 'email_exist', __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}

			$res = self::api( $api_key )->subscribe( $list_id, $subscribe_data );

			if ( is_wp_error( $res ) ) {
				$error_code = $res->get_error_code();
				$data['error'] = $res->get_error_message( $error_code );
				$module->log_error( $data );
			}

			return $res;
		}

		function email_exist( $email, $api_key, $api_secret ) {
			$api = self::api( $api_key, $api_secret );
			$subscriber = $api->is_subscriber( $email );
			return $subscriber;
		}

		/**
		* Creates necessary custom fields for the form
		*
		* @param Hustle_Module_Model $module
		* @return array|mixed|object|WP_Error
		*/
		public function maybe_create_custom_fields( Hustle_Module_Model $module, array $fields ) {
			$api_secret = self::_get_api_secret( $module );
			$api_key 	= self::_get_api_key( $module );

			// check if already existing
			$custom_fields = self::api( $api_key, $api_secret )->get_form_custom_fields();
			$proceed = true;
			foreach( $custom_fields as $custom_field ) {
				if ( isset( $fields[$custom_field->key] ) ) {
					unset($fields[$custom_field->key]);
				}
			}
			// create necessary fields
			// Note: we don't delete fields here, let the user do it on ConvertKit app.convertkit.com
			$api = self::api( $api_key );
			foreach( $fields as $key => $field ) {
				$add_custom_field = $api->create_custom_fields( array(
					'api_secret' => $api_secret,
					'label' => $field['label'],
				) );
				if ( is_wp_error($add_custom_field) ) {
					$proceed = false;
					break;
				}
			}

			return $proceed;
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

		private static function _get_api_secret( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'api_secret' );
		}

		/**
		 * Add Custom Fields
		 *
		 * @param Array - fields
		 * @param Integer - module id
		 */
		static function add_custom_field( $fields, $module_id ) {

			$module 	= Hustle_Module_Model::instance()->get( $module_id );
			$api_secret = self::_get_api_secret( $module );
			$api_key 	= self::_get_api_key( $module );

			$api = self::api( $api_key );
			$custom_fields = self::api( $api_key, $api_secret )->get_form_custom_fields();

			foreach ( $fields as $field ) {
				$exist = false;

				if ( ! empty( $custom_fields ) ) {
					foreach ( $custom_fields as $custom_field ) {
						if ( $field['name'] == $custom_field->key ) {
							$exist = true;
						}
						// Save the key in meta
						$module->add_meta( 'cv_field_' . $custom_field->key, $custom_field->label );
					}
				}

				if ( false === $exist ) {
					$add = $api->create_custom_fields( array(
						'api_secret' => $api_secret,
						'label' => $field['label'],
					) );

					if ( ! is_wp_error( $add ) ) {
						$exist = true;
						$module->add_meta( 'cv_field_' . $field['name'], $field['label'] );
					}
				}
			}

			if ( $exist ) {
				return array( 'success' => true, 'field' => $fields );
			}

			return array( 'error' => true, 'code' => 'cannot_create_custom_field' );
		}
	}

	add_filter("wpoi_optin_convertkit_show_selected_list",  array( "Opt_In_ConvertKit", "show_selected_list" ), 10, 2 );
	add_action("wph_optin_show_selected_list_after",  array( "Opt_In_ConvertKit", "render_selected_form" ) );

endif;