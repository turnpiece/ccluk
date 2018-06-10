<?php

if( !class_exists("Opt_In_Mad_Mimi") ):

	include_once 'opt-in-mad-mimi-api.php';

class Opt_In_Mad_Mimi extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {
	const ID = "mad_mimi";
	const NAME = "Mad Mimi";


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
	 * @param $username
	 * @param $api_key
	 * @return Opt_In_Mad_Mimi_Api
	 */
	protected static function api( $username, $api_key ){

		if( empty( self::$api ) ){
			try {
				self::$api = new Opt_In_Mad_Mimi_Api( $username, $api_key );
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

		$d = array();
		$d['email'] =  $data['email'];

		$api_key 	= self::_get_api_key( $module );
		$username 	= self::_get_username( $module );
		$list_id 	= self::_get_email_list( $module );

		if ( $this->email_exist( $d['email'], $api_key, $username, $list_id ) ) {
			$err = new WP_Error();
			$err->add( 'email_exist', __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
			return $err;
		}

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

		if( count( $name ) )
			$d['name'] = implode(" ", $name);

		// Add extra fields
		$data = array_diff_key( $data, array(
			'email' => '',
			'first_name' => '',
			'last_name' => '',
			'f_name' => '',
			'l_name' => '',
		) );
		$data = array_filter( $data );

		if ( ! empty( $data ) ) {
			$d = array_merge( $d, $data );
		}

		$res = self::api( $username, $api_key )->subscribe( $list_id, $d );

		if ( is_wp_error( $res ) ) {
			$error_code = $res->get_error_code();
			$data['error'] = $res->get_error_message( $error_code );
			$module->log_error( $data );
		}

		return $res;
	}


	/**
	 * Validate if email already subscribe
	 *
	 * @param $email string - Current guest user email address.
	 * @param $module object - Hustle_Module_Model
	 *
	 * @return bool Returns true if the specified email already subscribe otherwise false.
	 */
	function email_exist( $email, $api_key, $username, $list_id ) {
		$api = self::api( $username, $api_key );
		$res = $api->search_by_email( $email );

		if ( is_object( $res ) && ! empty( $res->member ) && $email == $res->member->email ) {
			$_lists = $api->search_email_lists( $email );
			if( !is_wp_error( $_lists ) && !empty( $_lists ) && is_array( $_lists ) ) {
				foreach(  ( array) $_lists as $list ){
					$list = (object) (array) $list;
					$list = $list->{'@attributes'};
					if ( $list['id'] == $list_id ) {
						return true;
					}
				}
			}

		}
		return false;
	}

	/**
	 * Retrieves initial options of the GetResponse account with the given api_key
	 *
	 * @param $module_id
	 * @return array
	 */
	function get_options( $module_id ){

		$_lists = self::api( $this->username, $this->api_key )->get_lists();

		if( is_wp_error( ( array) $_lists ) )
			return $_lists;

		if( empty( $_lists ) )
			return new WP_Error("no_audionces", __("No audience list defined for this account", Opt_In::TEXT_DOMAIN));

		if( !is_array( $_lists )  )
			$_lists = array( $_lists );

		$lists = array();
		foreach(  ( array) $_lists as $list ){
			$list = (object) (array) $list;
			$list = $list->{'@attributes'};
			$lists[ $list['id']]['value'] = $list['id'];
			$lists[ $list['id']]['label'] = $list['name'];
		}


		$first = count( $lists ) > 0 ? reset( $lists ) : "";
		if( !empty( $first ) )
			$first = $first['value'];

		return  array(
			"label" => array(
				"id"    => "optin_email_list_label",
				"for"   => "optin_email_list",
				"value" => __("Choose email list:", Opt_In::TEXT_DOMAIN),
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
					"data-nonce" => wp_create_nonce("mad_mimi_choose_campaign"),
					'class'     => "wpmudev-select mad_mimi_choose_campaign"
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

		$module 	= Hustle_Module_Model::instance()->get( $module_id );
		$api_key 	= self::_get_api_key( $module );
		$username 	= self::_get_username( $module );


		return array(
			"optin_username_label" => array(
				"id"    => "optin_username_label",
				"for"   => "optin_username",
				"value" => __("Enter your username (email address):", Opt_In::TEXT_DOMAIN),
				"type"  => "label",
			),
			"optin_username_field_wrapper" => array(
				"id"    => "optin_username_id",
				"class" => "optin_username_id_wrapper",
				"type"  => "wrapper",
				"elements" => array(
					"optin_username_field" => array(
						"id"            => "optin_username",
						"name"          => "optin_username",
						"type"          => "text",
						"default"       => "",
						"value"         => $username,
						"placeholder"   => "",
						"class"         => "wpmudev-input_text"
					)
				)
			),
			"optin_api_key_label" => array(
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
						"id"    => "refresh_mad_mimi_lists",
						"name"  => "refresh_mad_mimi_lists",
						"type"  => "ajax_button",
						"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
						'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
					),
				)
			),
			"instructions" => array(
				"id"    => "optin_api_instructions",
				"for"   => "",
				"value" => sprintf(__("Log in to your <a href='%s' target='_blank'>Mad Mimi account</a> to get your API Key.", Opt_In::TEXT_DOMAIN), 'https://madmimi.com' ),
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


	private static function _get_email_list( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'list_id' );
	}

	private static function _get_api_key( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'api_key' );
	}

	private static function _get_username( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'username' );
	}


	public static function add_values_to_previous_optins( $option, $module  ){
		if( $module->content->active_email_service  !== self::ID ) return $option;

		$username = self::_get_username( $module );

		if( $option['id'] === "optin_username_id" && isset( $username ) ){
			$option['elements']['optin_username_field']['value'] = $username;
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
		return ( $module->content->active_email_service !== Opt_In_Mad_Mimi::ID );
	}

	/**
	 * Renders selected list row
	 *
	 * @param $module Hustle_Module_Model
	 */
	public static function render_selected_list( $module ){
		$list_id = self::_get_email_list( $module );
		if( $module->content->active_email_service !== Opt_In_Mad_Mimi::ID || !$list_id ) return;
		printf( __("Selected audience list: %s (Press the GET LISTS button to update value)", Opt_In::TEXT_DOMAIN), $list_id );
	}
}

	add_filter("wpoi_optin_filter_optin_options",  array( "Opt_In_Mad_Mimi", "add_values_to_previous_optins" ), 10, 2 );
	add_filter("wpoi_optin_mad_mimi_show_selected_list",  array( "Opt_In_Mad_Mimi", "show_selected_list" ), 10, 2 );
	add_action("wph_optin_show_selected_list_after",  array( "Opt_In_Mad_Mimi", "render_selected_list" ) );
endif;