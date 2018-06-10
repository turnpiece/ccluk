<?php

if ( !class_exists ('Opt_In_E_Newsletter', false ) ) {
    class Opt_In_E_Newsletter extends Opt_In_Provider_Abstract implements  Opt_In_Provider_Interface {

        /**
         * @var $_email_newsletter Email_Newsletter
         */
        private $_email_newsletter;

        /**
         * @var $_email_builder Email_Newsletter_Builder
         */
        private $_email_builder;

        public function __construct(){

            global $email_newsletter, $email_builder;
            $this->_email_builder = $email_builder;
            $this->_email_newsletter = $email_newsletter;

        }

		const ID = "e_newsletter";
        const NAME = "e-Newsletter";


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
		function update_option( $option_key, $option_value ) {
			return update_site_option( self::ID . "_" . $option_key, $option_value );
		}

		/**
		 * Retrieves api option from db
		 *
		 * @param $option_key
		 * @param $default
		 * @return mixed
		 */
		function get_option( $option_key, $default ) {
			return get_site_option( self::ID . "_" . $option_key, $default );
		}

		function get_options( $module_id ) {
			return array();
		}

		function get_account_options( $module_id ){

            $module = Hustle_Module_Model::instance()->get( $module_id );

			//display a notice only if e-Newsletter plugin is not active
			if( !$this->is_plugin_active() ){

				$e_newsletter_url = "https://premium.wpmudev.org/project/e-newsletter/";

				return array(
					"label" =>  array(
						"class"	=> "wpmudev-label--notice",
						"type"	=> "notice",
						"value"	=>  sprintf( __( "Please, activate e-Newsletter plugin. If you don't have it installed, <a href='%s' target='_blank'>download it here.</a>", Opt_In::TEXT_DOMAIN ), $e_newsletter_url )
					)
				);
			}

			$synced = self::_get_provider_details( $module, 'synced' );
            $checked = self::_get_auto_optin( $module );
			$lists = array();

			$_lists = $this->get_groups();
			if( is_array( $_lists ) && !empty( $_lists ) ) {
				foreach( $_lists as $list ) {
					$list = (array) $list;
					$lists[ $list['group_id'] ]['value'] = $list['group_id'];
					$lists[ $list['group_id'] ]['label'] = $list['group_name'];
				}
			}

            return array(
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
				"lists_setup" => array (
					"id"    => "optin-provider-account-options",
                    "class" => "wpmudev-provider-block",
                    "type"  => "wrapper",
                    "elements" => array(
						"label" => array(
							"id"    => "optin_email_list_label",
							"for"   => "optin_email_list",
							"value" => empty($lists)? __("There are no email lists to choose from.", Opt_In::TEXT_DOMAIN) : __("Choose email list:", Opt_In::TEXT_DOMAIN),
							"type"  => "label",
						),
						"choose_email_list" => array(
							"id"            => "wph-email-provider-lists",
							"name"          => "optin_email_list",
							"type"          => "checkboxes",
							'selected'		=> self::_get_provider_details( $module, 'list_id' ),
							"default"       => "",
							"value"         => "",
							'options'       => $lists,
						)
					)
				),
				"sync_with_current_local_list" => array(
                    "id"    => "",
                    "class" => "",
                    "type"  => "hidden",
					'name'  => "synced",
					'id'    => "synced",
					'value' => $synced ? 1 : 0,
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

		private function _get_auto_optin( Hustle_Module_Model $module ) {
            $auto_optin = 'subscribed';
            $saved_auto_optin = self::_get_provider_details( $module, 'auto_optin' );
            if ( !empty( $saved_auto_optin ) && $saved_auto_optin !== 'subscribed' ) {
                $auto_optin = 'pending';
            }
			return $auto_optin;
		}
//here below are the original e-news methods

        /**
         * Subscribes to E-Newsletter
         *
         *
         * @param array $data
         * @param array $groups
		 * @param int $subscribe
         *
         * @since 1.1.1
         * @return array
         */
        public function subscribe( Hustle_Module_Model $module, array $data ){

			$groups = self::_get_provider_details( $module, 'list_id' );
			$double_opt_in = self::_get_auto_optin( $module ) === 'pending' ? true : false ;
			$subscribe = $double_opt_in ? "" : 1;

			$_data = array();
			$_data['member_email'] = $data['email'];

			if( isset( $data['first_name'] ) )
				$_data['member_fname'] = $data['first_name'];

			if( isset( $data['last_name'] ) )
				$_data['member_lname'] = $data['last_name'];

            $_data['is_hustle'] = true;
            $e_newsletter = $this->_email_newsletter;

            if( !$this->is_member( $_data['member_email'] ) ){
                $insert_data = $e_newsletter->create_update_member_user( "",  $_data, $subscribe );

                if( isset( $insert_data['results'] ) && in_array( "member_inserted", (array) $insert_data['results'] )  ) {
                    $e_newsletter->add_members_to_groups( $insert_data['member_id'], $groups );

                    if( isset( $e_newsletter->settings['subscribe_newsletter'] ) && $e_newsletter->settings['subscribe_newsletter'] ) {
                        $send_details = $e_newsletter->add_send_email_info( $e_newsletter->settings['subscribe_newsletter'], $insert_data['member_id'], 0, 'waiting_send' );
                        $e_newsletter->send_email_to_member($send_details['send_id']);
                    }

					//$subscribe should only be false when double opt-in is enabled
					if ( !$subscribe ){
						$status = $e_newsletter->do_double_opt_in( $insert_data['member_id'] );
					}

					return true;
                }

				return new WP_Error("data_not_inserted", __("Something went wrong. Please try again later.", Opt_In::TEXT_DOMAIN), $data);
            }

            return new WP_Error("member_exists", __("Member exists", Opt_In::TEXT_DOMAIN), $data);
        }

        /**
         * Checks if E-Newsletter plugin is active
         *
         * @since 1.1.1
         * @return bool
         */
        function is_plugin_active(){
            return class_exists( 'Email_Newsletter' ) && isset( $this->_email_newsletter ) && isset( $this->_email_builder );
        }

        /**
         * Returns groups
         *
         * @since 1.1.1
         * @return array
         */
        function get_groups(){
            return (array) $this->_email_newsletter->get_groups();
        }

        /**
         * Checks if member with given email already exits
         *
         *
         * @since 1.1.1
         *
         * @param $email
         * @return bool
         */
        function is_member( $email ){
            $member = $this->_email_newsletter->get_member_by_email( $email );
            return !!$member;
        }

        /**
         * Subscribes $modules's subscribers to e-newsletter
         *
         * @since 1.1.2
         *
         * @param Hustle_Module_Model $module
         * @param array $groups
         */
        function sync_with_current_local_collection( Hustle_Module_Model $module, $groups = array() ){

            $groups = array() === $groups ?  $this->get_groups() : $groups;

            foreach( $module->get_local_subscriptions() as $subscription ){

                if( isset( $subscription->optin_type  ) && "e-newsletter"  === $subscription->optin_type  ) return;

                $data = array(
                    "is_hustle" => true,
                    "member_email" => $subscription->email,
                    "member_fname" => isset( $subscription->f_name ) ? $subscription->f_name : "",
                    "member_lname" => isset( $subscription->l_name ) ? $subscription->l_name : ""
                );
				if( !$this->is_member( $data['member_email'] ) ){
					$insert_data = $this->_email_newsletter->create_update_member_user( "",  $data, 1 );

					if( isset( $insert_data['results'] ) && in_array( "member_inserted", (array) $insert_data['results'] )  )
					$this->_email_newsletter->add_members_to_groups( $insert_data['member_id'],  $groups );
				}
            }

        }

    }
}