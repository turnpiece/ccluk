<?php

if( !class_exists("Opt_In_Mailchimp") ):

	include_once 'opt-in-mailchimp-api.php';

	class Opt_In_Mailchimp extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {

		const ID = "mailchimp";
		const NAME = "MailChimp";


		/**
		 * @var $api Mailchimp
		 */
		protected  static $api;
		protected  static $errors;

		const GROUP_TRANSIENT = "hustle-mailchimp-group-transient";
		const LIST_PAGES = "hustle-mailchimp-list-pages";

		static function instance(){
			return new self;
		}

		public static function register_ajax_endpoints(){
			add_action( "wp_ajax_hustle_mailchimp_get_list_groups", array( __CLASS__ , "ajax_get_list_groups" ) );
			add_action( "wp_ajax_hustle_mailchimp_get_group_interests", array( __CLASS__ , "ajax_get_group_interests" ) );
			add_action( "wp_ajax_hustle_mailchimp_get_current_settings", array( __CLASS__ , "ajax_get_current_settings" ) );
		}

		/**
		 * Updates api option
		 *
		 * @param $option_key
		 * @param $option_value
		 * @return bool
		 */
		function update_option( $option_key, $option_value ){
			return update_site_option( self::ID . "_" . $option_key, $option_value);
		}

		/**
		 * Retrieves api option from db
		 *
		 * @param $option_key
		 * @param $default
		 * @return mixed
		 */
		function get_option( $option_key, $default ){
			return get_site_option( self::ID . "_" . $option_key, $default );
		}

		/**
		 * @param string $api_key
		 * @return Mailchimp
		 */
		protected static function api( $api_key ){

			if( empty( self::$api ) ){
				try {
					$exploded = explode( '-', $api_key );
					$data_center = end( $exploded );
					self::$api = new Opt_In_Mailchimp_Api( $api_key, $data_center );
					self::$errors = array();
				} catch (Exception $e) {
					self::$errors = array("api_error" => $e) ;
				}

			}
			return self::$api;
		}

		public function subscribe( Hustle_Module_Model $module, array $data ){
			$api        = self::api( self::_get_api_key( $module ) );
			$list_id    = self::_get_list_id( $module );
			$sub_status = self::_get_auto_optin( $module );

			if ( empty( $api ) ) {
				$err = new WP_Error();
				$err->add( 'server_failed', __( 'API Key is not defined!', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}

			$email =  $data['email'];
			$merge_vals = array();
			$interests = array();

			if ( isset( $data['first_name'] ) ) {
				$merge_vals['MERGE1'] = $data['first_name'];
				$merge_vals['FNAME'] = $data['first_name'];
			}
			elseif ( isset( $data['f_name'] ) ) {
				$merge_vals['MERGE1'] = $data['f_name']; // Legacy
				$merge_vals['FNAME'] = $data['f_name']; // Legacy
			}
			if ( isset( $data['last_name'] ) ) {
				$merge_vals['MERGE2'] = $data['last_name'];
				$merge_vals['LNAME'] = $data['last_name'];
			}
			elseif ( isset( $data['l_name'] ) ) {
				$merge_vals['MERGE2'] = $data['l_name']; // Legacy
				$merge_vals['LNAME'] = $data['l_name']; // Legacy
			}
			// Add extra fields
			$merge_data = array_diff_key( $data, array(
				'email' => '',
				'first_name' => '',
				'last_name' => '',
				'f_name' => '',
				'l_name' => '',
				'mailchimp_group_id' => '',
				'mailchimp_group_interest' => '',
			) );
			$merge_data = array_filter( $merge_data );

			if ( ! empty( $merge_data ) ) {
				$merge_vals = array_merge( $merge_vals, $merge_data );
			}
			$merge_vals = array_change_key_case($merge_vals, CASE_UPPER);

			/**
			 * Add args for interest groups
			 */
			if( !empty( $data['mailchimp_group_id'] ) && !empty( $data['mailchimp_group_interest'] ) ){
				$data_interest = (array) $data['mailchimp_group_interest'];
				foreach( $data_interest as $interest ) {
					$interests[$interest] = true;
				}
			}

			try {
				$subscribe_data = array(
					'email_address' => $email,
					'status'        => $sub_status
				);
				if ( !empty($merge_vals) ) {
					$subscribe_data['merge_fields'] = $merge_vals;
				}
				if ( !empty($interests) ) {
					$subscribe_data['interests'] = $interests;
				}
				$existing_member = $this->get_member( $email, $module, $data );
				if ( $existing_member ) {
					$member_interests = isset($existing_member->interests) ? (array) $existing_member->interests : array();
					$can_subscribe = false;
					if ( isset( $subscribe_data['interests'] ) ){
						$local_interest_keys = array_keys( $subscribe_data['interests'] );
						if ( !empty( $member_interests ) ) {
							foreach( $member_interests as $member_interest => $subscribed ){
								if( !$subscribed && in_array( $member_interest, $local_interest_keys ) ){
									$can_subscribe = true;
								}
							}
						} else {
							$can_subscribe = true;
						}
					}
					if ( isset( $subscribe_data['interests'] ) && $can_subscribe ) {
						unset( $subscribe_data['email_address'] );
						unset( $subscribe_data['merge_fields'] );
						unset( $subscribe_data['status'] );
						$response = $api->update_subscription( $list_id, $email, $subscribe_data );
						return array( 'message' => $response, 'existing' => true);
					} else {
						$err = new WP_Error();
						$err->add( 'email_exist', __( 'This email address has already subscribed', Opt_In::TEXT_DOMAIN ) );
						return $err;
					}
				} else {
					$result = $api->subscribe( $list_id, $subscribe_data );
					return $result;
				}
			} catch( Exception $e ) {
				$data['error'] = $e->getMessage();
				$module->log_error( $data );

				$err = new WP_Error();
				$err->add( 'server_failed', __( 'Something went wrong. Please try again.', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}
		}

		/**
		 * @param string $email
		 * @param Hustle_Module_Model $module
		 * @param array $data
		 *
		 * @return Object Returns the member if the email address already exists otherwise false.
		 */
		function get_member( $email, Hustle_Module_Model $module, $data ) {
			$api = self::api( self::_get_api_key( $module ) );

			try {
				$member_info = $api->check_email( self::_get_list_id( $module ), $email);
				// Mailchimp returns WP error if can't find member on a list
				if ( is_wp_error( $member_info ) && $member_info->get_error_code() == 404 ) {
					return false;
				}
				return $member_info;
			} catch( Exception $e ) {
				$data['error'] = $e->getMessage();
				$module->log_error($data);

				return false;
			}
		}

		function get_options( $module_id ) {

			//Load more function
			$load_more = filter_input( INPUT_POST, 'load_more' );

			$lists = array();

			if ( $load_more ) {
				$response = $this->lists_pagination( $this->api_key );
				list( $lists, $total ) =  $response;
			} else {
				$response = self::api( $this->api_key )->get_lists();
				$_lists   = $response->lists;
				$total    = $response->total_items;
				if( is_array( $_lists ) ) {
					foreach( $_lists as $list ) {
						$list = (array) $list;
						$lists[ $list['id'] ]['value'] = $list['id'];
						$lists[ $list['id'] ]['label'] = $list['name'];
					}
					delete_site_transient( self::LIST_PAGES );
				}
			}

			$total_lists = count( $lists );

			$first = $total_lists > 0 ? reset( $lists ) : "";
			if( !empty( $first ) )
				$first = $first['value'];


			$default_options =  array(
				"label" => array(
					"id"    => "optin_email_list_label",
					"for"   => "optin_email_list",
					"value" => __("Choose email list:", Opt_In::TEXT_DOMAIN),
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
						"data-nonce"    => wp_create_nonce("mailchimp_choose_email_list"),
						'class'         => "wpmudev-select mailchimp_optin_email_list"
					)
				),
				'loadmore' => array(
					"id"    => "loadmore_mailchimp_lists",
					"name"  => "loadmore_mailchimp_lists",
					"type"  => "button",
					"value" => __("Load More Lists", Opt_In::TEXT_DOMAIN),
					'class' => "wpmudev-button wph-button--spaced wph-button wph-button--filled wph-button--gray mailchimp_optin_load_more_lists"
				)
			);

			if ( $total_lists <= 0 ) {
				//If we have no items, no need to show the button
				unset( $default_options['loadmore'] );
			} else if ( $total <= $total_lists ) {
				//If we have reached the end, remove the button
				unset( $default_options['loadmore'] );
			}

			$list_group_options = self::_get_list_group_options( $this->api_key, $first );

			return array_merge( $default_options,  array(
				"wph-optin-list-groups-wrapper" => array(
					"id"        => "wph-optin-list-groups",
					"class"     => "wph-optin-list-groups",
					"type"      => "wrapper",
					"elements"  =>  is_a( $list_group_options, "Mailchimp_Error" ) ? array() : $list_group_options
				),
				"wph-optin-list-group-interests-wrapper" => array(
					"id"        => "wph-optin-list-group-interests-wrap",
					"class"     => "wph-optin-list-group-interests-wrap",
					"type"      => "wrapper",
					"elements"  =>  array()
				)
			));

		}

		/**
		 * Lists pagination
		 *
		 * @return array
		 */
		function lists_pagination( $api_key ) {

			$lists      = array();
			$list_pages = get_site_transient( self::LIST_PAGES );

			$offset     = 2; //Default limit to first page
			$total      = 0; //Default we have 0

			if ( $list_pages ) {
				$total  = isset( $list_pages['total'] ) ? $list_pages['total'] : 0;
				$offset = isset( $list_pages['offset'] ) ? $list_pages['offset'] : 2;
			} else {
				$list_pages = array();
			}

			if ( $offset > 0 ) {
				$response = self::api( $api_key )->get_lists( $offset );
				$_lists   = $response->lists;
				$total    = $response->total_items;

				if ( is_array( $_lists ) ) {
					foreach( $_lists as $list ){
						$list = (array) $list;
						$lists[ $list['id'] ]['value'] = $list['id'];
						$lists[ $list['id'] ]['label'] = $list['name'];
					}
					if ( count( $_lists ) >= $total ) {
						$offset = 0; //We have reached the end. No more pagination
					} else {
						$offset = $offset + 1;
					}

					$list_pages['offset'] = $offset;
					$list_pages['total']  = $total;
					set_site_transient( self::LIST_PAGES , $list_pages );
				} else {
					delete_site_transient( self::LIST_PAGES );
				}
			} else {
				delete_site_transient( self::LIST_PAGES );
			}

			return array( $lists, $total );
		}

		function get_account_options( $module_id ){
			$module = Hustle_Module_Model::instance()->get( $module_id );

			$api_key    = self::_get_api_key( $module );
			$checked    = self::_get_auto_optin( $module );

			return array(
				"label" => array(
					"id"    => "optin_api_key_label",
					"for"   => "optin_api_key",
					"value" => __("Choose your API key:", Opt_In::TEXT_DOMAIN),
					"type"  => "label",
				),
				"wrapper" => array(
					"id"    => "",
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
							"id"    => "refresh_mailchimp_lists",
							"name"  => "refresh_mailchimp_lists",
							"type"  => "ajax_button",
							"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
							"class" => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
						),
					)
				),
				"instructions" => array(
					"id"    => "optin_api_instructions",
					"for"   => "",
					"value" => sprintf( __("Log in to your <a href='%s' target='_blank'>MailChimp account</a> to get your API Key.", Opt_In::TEXT_DOMAIN), 'https://admin.mailchimp.com/account/api/' ),
					"type"  => "small",
				),
				"subscription_setup" => array(
					"id"    => "",
					"class" => "wpmudev-switch-labeled",
					"type"  => "wrapper",
					"elements" => array(
						"subscription_mode" => array(
							"id"    => "",
							"class" => "wpmudev-switch",
							"type"  => "wrapper",
							"elements" => array(
								"opt_in" => array(
									"type"          => 'checkbox',
									'name'          => "optin_auto_optin",
									'id'            => "optin_auto_optin",
									"default"       => "",
									'value'         => "pending",
									"attributes"    => array(
										'class'   => "toggle-checkbox",
										'checked' => ( $checked != 'pending') ? 'checked' : ''
									)
								),
								"label" => array(
									"id"            => "optin_auto_optin_label",
									"for"           => "optin_auto_optin",
									"value"         => "",
									"type"          => "label",
									"attributes"    => array(
										'class'     => "wpmudev-switch-design"
									)
								)
							),
						),
						"switch_instructions" => array(
							"id"            => "optin_auto_optin_label",
							"for"           => "optin_auto_optin",
							"value"         => __("Automatically opt-in new users to the mailing list", Opt_In::TEXT_DOMAIN),
							"type"          => "label",
							"attributes"    => array(
								'class'     => "wpmudev-switch-label"
							)
						),
					)
				),
			);
		}

		function is_authorized(){
			return true;
		}

		function get_args( $data ) {
			if ( $data && isset( $data['email_services'] ) ) {
				$email_services = $data['email_services'];
				$list_id = ( isset( $email_services['mailchimp']['list_id'] ) )
					? $email_services['mailchimp']['list_id']
					: '';
				$group_id = ( isset( $email_services['mailchimp']['group'] ) )
					? $email_services['mailchimp']['group']
					: '';
				$groups = $this->_get_group_interests( $list_id, $group_id );

				if ( isset( $email_services['mailchimp']['group_interest'] ) ) {
					$groups['selected'] = $email_services['mailchimp']['group_interest'];
				}

				return $groups;
			}
		}

		/**
		 * Returns options for the given $list_id
		 *
		 * @param $api_key
		 * @param $list_id
		 * @return array|Exception
		 */
		private static function _get_list_group_options( $api_key, $list_id ){
			$group_options = array();
			$options = array(
				-1 => array(
					"value" 	=> -1,
					"label" 	=> __( "No group", Opt_In::TEXT_DOMAIN ),
					"interests" => __("First choose interest group", Opt_In::TEXT_DOMAIN)
				)
			);

			$api  = self::api( $api_key );
			try{

				$total_groups = $api->get_interest_categories( $list_id )->total_items;
				if ( $total_groups < 10 ) {
					$total_groups = 10;
				}
				$groups = (array) $api->get_interest_categories( $list_id, $total_groups )->categories;
			}catch (Exception $e){
				return $e;
			}

			if( !is_array( $groups ) ) return $group_options;

			foreach( $groups as $group_key => $group ){
				$group = (array) $group;

				// get interests for each group category
				$total_interests = $api->get_interests( $list_id, $group['id'] )->total_items;
				if ( $total_interests < 10 ) {
					$total_interests = 10;
				}
				$groups[$group_key]->interests = (array) $api->get_interests( $list_id, $group['id'], $total_interests )->interests;

				$options[ $group['id'] ]['value'] = $group['id'];
				$options[ $group['id'] ]['label'] = $group['title'] . " ( " . ucfirst( $group['type'] ) . " )";
			}

			set_site_transient( self::GROUP_TRANSIENT  . $list_id, $groups );

			$first = current( $options );
			return array(
				"mailchimp_groups_label" => array(
					"id"    => "mailchimp_groups_label",
					"for"   => "mailchimp_groups",
					"value" => __("Choose interest group:", Opt_In::TEXT_DOMAIN),
					"type"  => "label",
				),
				"mailchimp_groups" => array(
					"type"      => 'select',
					'name'      => "mailchimp_groups",
					'id'        => "mailchimp_groups",
					'class'     => "wpmudev-select",
					"default"   => "",
					'options'   => $options,
					'value'     => $first,
					'selected'  => $first,
					"attributes" => array(
						"data-nonce" => wp_create_nonce("mailchimp_groups")
					)
				),
				"mailchimp_groups_instructions" => array(
					"id"    => "mailchimp_groups_instructions",
					//"class" => "wpmudev-label--notice",
					"value" => "<label class='wpmudev-label--notice'><span>" . __( "Leave this option blank if you would like to opt-in users without adding them to a group first", Opt_In::TEXT_DOMAIN ) . "</span></label>",
					"type"  => "label",
				)
			);

		}

		/**
		 * Normalizes api response for groups interests
		 *
		 *
		 * @since 1.0.1
		 *
		 * @param $interest
		 * @return mixed
		 */
		static function normalize_group_interest( $interest ){
			$interest = (array) $interest;
			$interest_arr = array();
			$interest_arr["label"] = $interest['name'];
			$interest_arr["value"] = $interest['id'];

			return $interest_arr;
		}
		/**
		 * Returns interest for given $list_id, $group_id
		 *
		 * @since 1.0.1
		 *
		 * @param $list_id
		 * @param $group_id
		 * @return array
		 */
		private static function _get_group_interests( $list_id, $group_id ){

			$interests = array(
				-1 => array(
					"id" 	=> -1,
					"label" => __("No default choice", Opt_In::TEXT_DOMAIN)
				)
			);

			$groups = get_site_transient( self::GROUP_TRANSIENT  . $list_id );

			if( !$groups || !is_array( $groups ) ) return $interests;

			$the_group = array();

			foreach( $groups as $group ){
				$group = (array) $group;
				if( $group["id"] == $group_id )
					$the_group = $group;
			}

			if( $the_group === array() ) return $interests;

			if( in_array($the_group['type'], array("radio", "checkboxes", "hidden")) )
				$interests = array();

			$interests = array_merge( $interests,  array_map( array(__CLASS__, "normalize_group_interest" ),  $the_group['interests']) );

			if(  "hidden" === $the_group['type'] && isset( $the_group['interests'][0] ) ) {
				$interest = $the_group['interests'][0];
				if ( is_object( $interest ) ) {
					$the_group['selected'] = $interest->id;
				} else {
					$the_group['selected'] = $interest['id'];
				}
			}

			return array(
				'group'     => $the_group,
				"interests" => $interests,
				"type"      => $the_group['type']
			);
		}

		/**
		 * @used by array_map in _get_group_interest_args to map interests to their id/value
		 *
		 * @since 1.0.1
		 * @param $interest
		 * @return mixed
		 */
		private function _map_interests_to_ids( $interest ){
			return $interest['value'];
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

		private function _get_auto_optin( Hustle_Module_Model $module ) {
			$auto_optin = 'pending';
			$saved_auto_optin = self::_get_provider_details( $module, 'auto_optin' );
			if ( !empty( $saved_auto_optin ) && $saved_auto_optin !== 'pending' ) {
				$auto_optin = 'subscribed';
			}
			return $auto_optin;
		}

		/**
		 * Returns interest args for the given $group_id and $list_id
		 *
		 * @since 1.0.1
		 *
		 * @param $list_id
		 * @param $group_id
		 * @return array
		 */
		private static function _get_group_interest_args( $list_id, $group_id ){
			$interests_config = self::_get_group_interests( $list_id, $group_id );
			$interests = $interests_config['interests'];

			$_type = $interests_config['type'];

			$type = "radio" === $interests_config['type'] ? "radios" : $interests_config['type'];
			$type = "dropdown" === $type || "hidden" === $type ? "select" : $type;


			$class = ( $type === 'select' ) ? 'wpmudev-select' : '';

			$first = current( $interests );

			$interests_config['group']['interests'] = array_map( array(__CLASS__, "normalize_group_interest" ), $interests_config['group']['interests'] );

			$name = "mailchimp_groups_interests";

			if( $type === "checkboxes" )
				$name .= "[]";

			$choose_prompt = __("Choose default interest:", Opt_In::TEXT_DOMAIN);

			if( $_type === "checkboxes" )
				$choose_prompt = __("Choose default interest(s):", Opt_In::TEXT_DOMAIN);

			if( $_type === "hidden" )
				$choose_prompt = __("Set default interest:", Opt_In::TEXT_DOMAIN);

			if( $type === "radios" )
				$choose_prompt .= sprintf(" ( <a href='#' data-name='mailchimp_groups_interests' class='wpoi-leave-group-intrests-blank wpoi-leave-group-intrests-blank-radios' >%s</a> )", __("clear selection", Opt_In::TEXT_DOMAIN) );

			return array(
				'group'     => $interests_config['group'],
				"fields"    => array(
					"mailchimp_groups_interest_label" => array(
						"id"    => "mailchimp_groups_interest_label",
						"for"   => "mailchimp_groups_interests",
						"value" => $choose_prompt,
						"type"  => "label",
					),
					"mailchimp_groups_interests" => array(
						"type"      => $type,
						'name'      => $name,
						'id'        => "mailchimp_groups_interests",
						"default"   => "",
						'options'   => $interests,
						'value'     => $first,
						'selected'  => array(),
						'class'     => $class,
						"item_attributes" => array()
					),
					"mailchimp_groups_interest_instructions" => array(
						"id"    => "mailchimp_groups_interest_instructions",
						"for"   => "",
						"value" =>  __( "What you select here will appear pre-selected for users. If this is a hidden group, the interest will be set but not shown to users.", Opt_In::TEXT_DOMAIN ),
						"type"  => "label",
					)
				)
			);
		}

		/**
		 * Ajax endpoint to render html for group options based on given $list_id and $api_key
		 *
		 * @since 1.0.1
		 */
		static function ajax_get_list_groups(){
			Opt_In_Utils::validate_ajax_call( 'mailchimp_choose_email_list' );

			$list_id = filter_input( INPUT_GET, 'optin_email_list' );
			$api_key = filter_input( INPUT_GET, 'optin_api_key' );

			$options = self::_get_list_group_options( $api_key, $list_id );

			$html = "";
			if( is_array( $options ) && !is_a( $options, "Mailchimp_Error" )  ){
				foreach( $options as $option )
					$html .= Opt_In::static_render("general/option", $option , true);

				wp_send_json_success( $html );
			}

			wp_send_json_error( $options );
		}

		/**
		 * Ajax call endpoint to return interest options of give list id and group id
		 *
		 * @since 1.0.1
		 */
		static function ajax_get_group_interests(){
			Opt_In_Utils::validate_ajax_call( 'mailchimp_groups' );

			$list_id 	= filter_input( INPUT_GET, 'optin_email_list' );
			$group_id 	= filter_input( INPUT_GET, 'mailchimp_groups' );

			$groups_config = get_site_transient( self::GROUP_TRANSIENT  . $list_id );
			if( !$groups_config || !is_array( $groups_config ) )
				wp_send_json_error( __("Invalid list id: ", Opt_In::TEXT_DOMAIN) . $list_id );

			$args 	= self::_get_group_interest_args( $list_id, $group_id );
			$fields = $args['fields'];
			$html 	= "";

			if ( is_array( $fields ) ) {
				foreach( $fields as $field ) {
					$html .= Opt_In::static_render("general/option", $field , true);
				}
			}

			wp_send_json_success(  array(
				"html" => $html,
				"group" => $args['group']
			) );
		}

		static function ajax_get_current_settings() {
			Opt_In_Utils::validate_ajax_call( 'optin_provider_current_settings' );

			$list_id 		= filter_input( INPUT_GET, 'list_id' );
			$group 			= filter_input( INPUT_GET, 'group' );
			$groups_config 	= get_site_transient( self::GROUP_TRANSIENT  . $list_id );
			$selected 		= null;

			if ( $groups_config && is_array( $groups_config ) ) {
				foreach( $groups_config as $groups ){
					if ( $groups->id === $group ) {
						$selected = $groups;
					}
				}
			}

			wp_send_json_success(  array(
				"group" => $selected
			) );
		}

		static function add_custom_field( $fields, $module_id ) {
			$module     = Hustle_Module_Model::instance()->get( $module_id );
			$api_key    = self::_get_api_key( $module );
			$list_id    = self::_get_list_id( $module );

			try{
				// Mailchimp does not support "email" field type so let's use text
				// use text as well for name, address and phone
				// returns either the new MailChimp "merge_field" object or WP error (if already existing)
				$api = self::api( $api_key );

				foreach ( $fields as $field ) {
					$api->add_custom_field( $list_id, array(
						'tag'   => strtoupper( $field['name'] ),
						'name'  => $field['label'],
						'type'  => ( $field['type'] == 'email' || $field['type'] == 'name' || $field['type'] == 'address' || $field['type'] == 'phone' ) ? 'text' : $field['type']
					) );
				}

				// double check if already on our system
				/*$current_module_fields = $module->get_design()->__get( 'module_fields' );
				foreach( $current_module_fields as $m_field ) {
					if ( $m_field['name'] == $field['name'] ) {
						return array( 'error' => true, 'code' => 'custom', 'message' => __( 'Field already exists.', Opt_In::TEXT_DOMAIN ) );
					}
				}*/

			}catch (Exception $e){
				return array( 'error' => true, 'code' => 'custom', 'message' => $e->getMessage() );
			}
			return array( 'success' => true, 'fields' => $fields );
		}
	}

	Opt_In_Mailchimp::register_ajax_endpoints();
endif;