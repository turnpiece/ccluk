<?php

if( !class_exists("Opt_In_Activecampaign") ):

	include_once 'opt-in-activecampaign-api.php';

	class Opt_In_Activecampaign extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {
		const ID = "activecampaign";
		const NAME = "ActiveCampaign";


		/**
		 * @var $api Activecampaign
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
		 * @param $username
		 * @param $api_key
		 * @return Opt_In_Activecampaign_Api
		 */
		protected static function api( $url, $api_key ){

			if( empty( self::$api ) ){
				try {
					self::$api = new Opt_In_Activecampaign_Api( $url, $api_key );
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
			$ac_url     = self::_get_api_url( $module );
			$list_id    = self::_get_api_list_id( $module );

			$api = self::api( $ac_url, $api_key );

			if ( isset( $data['f_name'] ) ) {
				$data['first_name'] = $data['f_name']; // Legacy
				unset( $data['f_name'] );
			}
			if( isset( $data['l_name'] ) ) {
				$data['last_name'] = $data['l_name']; // Legacy
				unset( $data['l_name'] );
			}
			$custom_fields = array_diff_key( $data, array( 'first_name' => '', 'last_name' => '', 'email' => '' ) );
			$origData = $data;

			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $key => $value ) {
					$key = 'field[%' . $key . '%,0]';
					$data[ $key ] = $value;
				}
			}

			return $api->subscribe( $list_id, $data, $module, $origData );
		}

		/**
		 * Retrieves initial options of the GetResponse account with the given api_key
		 *
		 * @param $module_id
		 * @return array
		 */
		function get_options( $module_id ){

			$_lists = self::api( $this->url, $this->api_key )->get_lists();

			if( is_wp_error( ( array) $_lists ) )
				return $_lists;

			if( empty( $_lists ) )
				return new WP_Error("no_audionces", __("No audience list defined for this account", Opt_In::TEXT_DOMAIN));

			if( !is_array( $_lists )  )
				$_lists = array( $_lists );

			$lists = array();
			foreach(  ( array) $_lists as $list ){
				$list = (object) (array) $list;

				$lists[ $list->id ] = array('value' => $list->id, 'label' => $list->name);

			}


			$first = count( $lists ) > 0 ? reset( $lists ) : "";
			if( !empty( $first ) )
				$first = $first['value'];

			return  array(
				"label" => array(
					"id"    => "optin_email_list_label",
					"for"   => "optin_email_list",
					"value" => __("Choose list:", Opt_In::TEXT_DOMAIN),
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
						"data-nonce"    => wp_create_nonce("activecampaign_choose_campaign"),
						'class'         => "wpmudev-select activecampaign_choose_campaign"
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
			$ac_url     = self::_get_api_url( $module );

			return array(
				"optin_url_label" => array(
					"id"    => "optin_url_label",
					"for"   => "optin_url",
					"value" => __("Enter your ActiveCampaign URL:", Opt_In::TEXT_DOMAIN),
					"type"  => "label",
				),
				"optin_url_field_wrapper" => array(
					"id"        => "optin_url_id",
					"class"     => "optin_url_id_wrapper",
					"type"      => "wrapper",
					"elements"  => array(
						"optin_url_field" => array(
							"id"            => "optin_url",
							"name"          => "optin_url",
							"type"          => "text",
							"default"       => "",
							"value"         => $ac_url,
							"placeholder"   => "",
							"class"         => "wpmudev-input_text",
						)
					)
				),
				"optin_api_key_label" => array(
					"id" => "optin_api_key_label",
					"for" => "optin_api_key",
					"value" => __("Enter your API key:", Opt_In::TEXT_DOMAIN),
					"type" => "label",
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
							"id"    => "refresh_activecampaign_lists",
							"name"  => "refresh_activecampaign_lists",
							"type"  => "ajax_button",
							"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
							'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
						),
					)
				),
				"instructions" => array(
					"id"    => "optin_api_instructions",
					"for"   => "",
					"value" => __("Log in to your <a href='http://www.activecampaign.com/login/' target='_blank'>ActiveCampaign account</a> to get your URL and API Key.", Opt_In::TEXT_DOMAIN),
					"type"  => "small",
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

		private static function _get_api_url( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'url' );
		}

		private static function _get_api_list_id( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'list_id' );
		}

		public static function add_values_to_previous_optins( $option, $module  ){
			if( $module->optin_provider !== "activecampaign" ) return $option;

			if( $option['id'] === "optin_username_id" && isset( $module->provider_args->username ) ){
				$option['elements']['optin_username_field']['value'] = $module->provider_args->username;
			}

			return $option;
		}

		/**
		 * Prevents default selected list from showing
		 *
		 * @param $val
		 * @param $module Hustle_Module_Model
		 * @return bool
		 */
		public static function show_selected_list(  $val, $module  ){
			if( $module->optin_provider !== Opt_In_Activecampaign::ID ) return true;
			return false;
		}

		/**
		 * Renders selected list row
		 *
		 * @param $module Hustle_Module_Model
		 */
		public static function render_selected_list( $module ){
			if( $module->optin_provider !== Opt_In_Activecampaign::ID || !$module->optin_mail_list ) return;
			printf( __("Selected audience list: %s (Press the GET LISTS button to update value)", Opt_In::TEXT_DOMAIN), $module->optin_mail_list );
		}

		static function add_custom_field( $fields, $module_id ) {
			$module     = Hustle_Module_Model::instance()->get( $module_id );
			$api_key    = self::_get_api_key( $module );
			$ac_url     = self::_get_api_url( $module );
			$list_id    = self::_get_api_list_id( $module );

			$api        = self::api( $ac_url, $api_key );

			$available_fields = array( 'first_name', 'last_name', 'email' );

			foreach ( $fields as $field ) {
				if ( ! in_array( $field['name'], $available_fields ) ) {
					$custom_field = array( $field['name'] => $field['label'] );
					$api->add_custom_fields( $custom_field, $list_id, $module );
				}
			}

			return array( 'success' => true, 'fields' => $fields );
		}
	}

	add_filter("wpoi_optin_filter_optin_options",  array( "Opt_In_Activecampaign", "add_values_to_previous_optins" ), 10, 2 );
	add_filter("wpoi_optin_activecampaign_show_selected_list",  array( "Opt_In_Activecampaign", "show_selected_list" ), 10, 2 );
	add_action("wph_optin_show_selected_list_after",  array( "Opt_In_Activecampaign", "render_selected_list" ) );
endif;