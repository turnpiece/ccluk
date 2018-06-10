<?php
if ( ! class_exists( 'Opt_In_IContact' ) ) :

	class Opt_In_IContact extends Opt_In_Provider_Abstract  implements  Opt_In_Provider_Interface {

		const ID = "icontact";
		const NAME = "IContact";

		static function instance() {
			return new self;
		}

		/**
         * API Set up
         *
         * @param String $_app_id - the application id
         * @param String $_api_password - the api password
         * @param String $_api_username - the api username
         *
         * @return WP_Error|Object
         */
        static function api( $app_id, $api_password, $api_username ) {
            if ( ! class_exists( 'Opt_In_IContact_Api' ) )
                require_once 'opt-in-icontact-api.php';

            try {
                $api = new Opt_In_IContact_Api( $app_id, $api_password, $api_username );
                return $api;
            } catch( Exception $e ) {
                return new WP_Error( 'something_wrong', $e->getMessage() );
            }
		}

		function is_authorized() {
			return true;
		}

		function update_option($option_key, $option_value){
			return update_site_option( self::ID . "_" . $option_key, $option_value);
		}

		function get_option($option_key, $default){
			return get_site_option( self::ID . "_" . $option_key, $default );
		}

		function subscribe( Hustle_Module_Model $module, array $data ) {
			$app_id     = self::_get_app_id( $module );
			$username   = self::_get_username( $module );
			$password   = self::_get_password( $module );
			$list_id    = self::_get_list_id( $module );
			$api 		= self::api( $app_id, $password, $username );
			$err 		= new WP_Error();
            $err->add( 'something_wrong', __( 'Something went wrong. Please try again', Opt_In::TEXT_DOMAIN ) );
			if ( !is_wp_error( $api ) ) {
				$email = $data['email'];
				$merge_vals = array();

				if ( isset( $data['first_name'] ) ) {
					$merge_vals['firstName'] = $data['first_name'];
				}
				elseif ( isset( $data['f_name'] ) ) {
					$merge_vals['firstName'] = $data['f_name']; // Legacy
				}
				if ( isset( $data['last_name'] ) ) {
					$merge_vals['lastName'] = $data['last_name'];
				}
				elseif ( isset( $data['l_name'] ) ) {
					$merge_vals['lastName'] = $data['l_name']; // Legacy
				}

				// Add extra fields
				$merge_data = array_diff_key( $data, array(
					'email' => '',
					'first_name' => '',
					'last_name' => '',
					'f_name' => '',
					'l_name' => '',
				) );
				$merge_data = array_filter( $merge_data );

				if ( ! empty( $merge_data ) ) {
					$merge_vals = array_merge( $merge_vals, $merge_data );
				}

				if ( $this->_is_subscribed( $api, $list_id, $email ) ) {
					$err = new WP_Error();
					$err->add( 'email_exist', __( 'This email address has already subscribed', Opt_In::TEXT_DOMAIN ) );
					return $err;
				}
				$subscribe_data = array(
					'email'     => $email,
					'status'    => 'normal'
				);
				$subscribe_data = array_merge( $subscribe_data, $merge_vals );

				$response = $api->add_subscriber( $list_id, $subscribe_data );
				if ( !is_wp_error( $response ) ) {
					return true;
				} else {
					$data['error'] = $response->get_error_message();
					$optin->log_error( $data );
				}
			} else {
				$data['error'] 	= $api->get_error_message();
				$module->log_error( $data );
			}
			return $err;
		}


		/**
         * Check if email is already subcribed to list
         */
        private function _is_subscribed( $api, $list_id, $email ) {
            $contacts = $api->get_contacts( $list_id );
            if ( !is_wp_error( $contacts ) ) {
                if ( is_array( $contacts ) && isset( $contacts['contacts'] ) && is_array( $contacts['contacts'] ) ) {
                    foreach ( $contacts['contacts'] as $contact ) {
                        if ( $contact['email'] == $email ){
                            return true;
                        }
                    }
                }
            }
            return false;
        }

		function get_options( $module_id ){
			$api 	= self::api( $this->app_id, $this->password, $this->username );
			$lists 	= array();
			$value 	= '';
			$list 	= array();

			if ( !is_wp_error( $api ) ) {
				$_lists = $api->get_lists();
                if ( !is_wp_error( $_lists ) ) {
                    if( count( $_lists ) && isset( $_lists['lists'] ) ) {
                        foreach( $_lists['lists'] as $list ) {
                            $list = (array) $list;
                            $lists[ $list['listId'] ]['value'] = $list['listId'];
                            $lists[ $list['listId'] ]['label'] = $list['name'];
						}

						$total_lists = count( $lists );
						if ( !empty( $first ) ) {
							$value = $first['value'];
						}
                    }
                }
			}

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
					'value'         => $value,
					'selected'      => $value,
					"attributes"    => array(
						"data-nonce"    => wp_create_nonce("icontact_choose_campaign"),
						'class'         => "wpmudev-select icontact_choose_campaign"
					)
				)
			);
		}

		function get_account_options( $module_id ) {
			$app_id = '';
			$username = '';
			$password = '';

			if ( $module_id  ) {

				$module 	= Hustle_Module_Model::instance()->get( $module_id );
				$app_id     = self::_get_app_id( $module );
				$username   = self::_get_username( $module );
				$password   = self::_get_password( $module );
			}

			$options = array(
				'api_id_label' => array(
					'id' 	=> 'api_id_label',
					'for' 	=> '',
					'type' 	=> 'label',
					'value' => __( 'Enter your API APP-ID', Opt_In::TEXT_DOMAIN ),
				),
				'app_id' => array(
					'id' 			=> 'optin_app_id',
					'name' 			=> 'optin_app_id',
					'value' 		=> $app_id,
					'placeholder' 	=> '',
					'type' 			=> 'text',
					"class"         => "wpmudev-input_text",
				),
				array(
					'id' 	=> 'opt-username-label',
					'for' 	=> 'optin_username',
					'type' 	=> 'label',
					'value' => __( 'Enter your API Username', Opt_In::TEXT_DOMAIN ),
				),
				array(
					'id' 		=> 'optin_username',
					'name' 		=> 'optin_username',
					'type' 		=> 'text',
					'value' 	=> $username,
					"class" 	=> "wpmudev-input_text"
				),
				array(
					'id' 	=> 'opt-pass-label',
					'for' 	=> 'optin_password',
					'type' 	=> 'label',
					'value' => __( 'Enter your Password', Opt_In::TEXT_DOMAIN ),
				),
				'wrapper2' => array(
					'id' 	=> 'wpoi-get-lists',
					'type' 	=> 'wrapper',
					'class' => 'wpmudev-provider-group',
					'elements' => array(
						array(
							'id' 	=> 'optin_password',
							'type' 	=> 'text',
							'name' 	=> 'optin_password',
							'value' => $password,
							"class" => "wpmudev-input_text"
						),
						'refresh' => array(
							"id" 	=> "refresh_icontact_lists",
							"name" 	=> "refresh_icontact_lists",
							"type" 	=> "ajax_button",
							"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
							'class' => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
						),
					),
				),
				"instructions" => array(
					"id"    => "optin_api_instructions",
					"for"   => "",
					"value" => sprintf( __( "Set up a new application in your <a href='%s' target='_blank'>IContact account</a> to get your credentials. (2.0) Make sure the AppID is enabled in your account", Opt_In::TEXT_DOMAIN ), "https://app.icontact.com/icp/core/registerapp/" ),
					"type"  => "small",
				),
			);

			return $options;
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

		private static function _get_app_id( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'app_id' );
		}

		private static function _get_username( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'username' );
		}

		private static function _get_password( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'password' );
		}

		private static function _get_list_id( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'list_id' );
		}

		static function add_custom_field( $fields, $module_id ) {
			$module 	= Hustle_Module_Model::instance()->get( $module_id );
			$app_id     = self::_get_app_id( $module );
			$username   = self::_get_username( $module );
			$password   = self::_get_password( $module );

			$api 	= self::api( $app_id, $password, $username );

			foreach ( $fields as $field ) {
				$api->add_custom_field( array(
					'displayToUser'  => 1,
					'privateName'    => $field['name'],
					'fieldType'      => ( $field['type'] == 'email' ) ? 'text' : $field['type']
				) );
			}

			if ( $exist ) {
				return array( 'success' => true, 'field' => $fields );
			}

			return array( 'error' => true, 'code' => '' );
		}
	}

endif;
?>