<?php
if ( ! class_exists( 'Opt_In_MailerLite' ) ) :

	include_once 'opt-in-mailerlite-api.php';

	class Opt_In_MailerLite extends Opt_In_Provider_Abstract  implements  Opt_In_Provider_Interface {

		const ID = "mailerlite";
		const NAME = "MailerLite";

		static function instance() {
			return new self;
		}

		static function api( $api_key ) {
            $api = new Opt_In_MailerLite_Api( $api_key );

            return $api;
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

			$api_key 	= self::_get_api_key( $module );
			$list_id 	= self::_get_list_id( $module );

			$err 		= new WP_Error();
			$api 		= self::api( $api_key );

			$email 		= $data['email'];
            $merge_vals = array();

            if ( isset( $data['first_name'] ) ) {
                $merge_vals['name'] = $data['first_name'];
            } elseif ( isset( $data['f_name'] ) ) {
                $merge_vals['name'] = $data['f_name']; // Legacy
			}

            if ( isset( $data['last_name'] ) ) {
                $merge_vals['last_name'] = $data['last_name'];
            } elseif ( isset( $data['l_name'] ) ) {
                $merge_vals['last_name'] = $data['l_name']; // Legacy
			}

			// Add extra fields
            $merge_data = array_diff_key( $data, array(
                'email' => '',
                'firstname' => '',
                'lastname' => '',
                'f_name' => '',
                'l_name' => '',
            ) );

            $merge_data = array_filter( $merge_data );

            if ( ! empty( $merge_data ) ) {
                $merge_vals = array_merge( $merge_vals, $merge_data );
			}

			$existing_member = $this->_email_exists( $list_id, $email, $api );
			if ( $existing_member ) {
				$err->add( 'email_exist', __( 'This email address has already subscribed.', Opt_In::TEXT_DOMAIN ) );
				return $err;
			}

			$subscriber_data = array(
				'email' => $email,
				'type'  => 'active'
			);
			if ( ! empty( $merge_vals ) ) {
				$subscriber_data['fields'] = $merge_vals;
			}

			$res = $api->add_subscriber( $list_id, $subscriber_data, 1 );
			if ( !is_wp_error( $res ) && isset( $res['id'] ) ) {
				return true;
			} else {
				$data['error'] 	= $res->get_error_message();
				$module->log_error( $data );
			}

			return $err;
		}


		/**
         * Check if an email exists
         *
         * @param $group_id - the group id
         * @param $email - the email
         * @param $api - the API class
         *
         * @return bool
         */
        private function _email_exists( $group_id, $email, $api ){
            $member_groups = $api->get_subscriber( $email );
            if ( is_wp_error( $member_groups ) ) {
                return false;
            } else {
                if ( !isset( $member_groups['error'] ) ) {
					foreach( $member_groups as $member_group => $group ){
						if ( $group['id'] == $group_id ) {
							return true;
						}
					}
				} else {
					return false;
                }
            }
            return false;
        }

		function get_options( $module_id ){
			$api 	= self::api( $this->api_key );
			$lists 	= array();
			$value 	= '';
			$list 	= array();

			if ( $api ) {
				$lists_api = $api->list_groups();
                if( !is_wp_error( $lists_api ) && !isset( $lists_api['error'] ) ) {
                    foreach ( $lists_api as $list ) {
						$lists[ $list['id'] ]['value'] = $list['id'];
						$lists[ $list['id'] ]['label'] = $list['name'];
                    }

                    $total_lists = count( $lists );
					if ( !empty( $first ) ) {
						$value = $first['value'];
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
						"data-nonce"    => wp_create_nonce("mailerlite_choose_campaign"),
						'class'         => "wpmudev-select mailerlite_choose_campaign"
					)
				)
			);
		}

		function get_account_options( $module_id ) {
			$api_key = '';

			if ( $module_id  ) {

				$module 	= Hustle_Module_Model::instance()->get( $module_id );
				$api_key    = self::_get_api_key( $module );
			}

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
							"id"    => "refresh_mailerlite_lists",
							"name"  => "refresh_mailerlite_lists",
							"type"  => "ajax_button",
							"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span><span class='wpmudev-loading'></span>",
							"class" => "wpmudev-button wpmudev-button-sm optin_refresh_provider_details"
						),
					)
				),
				"instructions" => array(
					"id"    => "optin_api_instructions",
					"for"   => "",
					"value" => sprintf( __("Log in to your <a href='%s' target='_blank'>MailerLite Integrations page</a> to get your API Key.", Opt_In::TEXT_DOMAIN), 'https://app.mailerlite.com/integrations/api/' ),
					"type"  => "small",
				)
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

		private static function _get_api_key( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'api_key' );
		}

		private static function _get_list_id( Hustle_Module_Model $module ) {
			return self::_get_provider_details( $module, 'list_id' );
		}

		static function add_custom_field( $fields, $module_id ) {
			$module 	= Hustle_Module_Model::instance()->get( $module_id );
			$api_key 	= self::_get_api_key( $module );

			$api = self::api( $api_key );

			foreach ( $fields as $field ) {
				$api->add_custom_field( array(
                    "title" => strtoupper( $field['label'] ),
                    "type"  => strtoupper( $field['type'] )
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