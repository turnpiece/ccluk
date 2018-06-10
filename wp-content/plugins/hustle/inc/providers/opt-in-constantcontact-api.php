<?php

if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {

	if ( !class_exists ( 'Ctct\CTCTOfficialSplClassLoader' ) ) {
		require_once( Opt_In::$vendor_path . 'Ctct/autoload.php' );
	}

	if( !class_exists("Opt_In_ConstantContact_Api") ):

		class Opt_In_ConstantContact_Api extends Opt_In_WPMUDEV_API {

			const API_URL = 'https://api.constantcontact.com/v2/';
			const AUTH_API_URL = 'https://oauth2.constantcontact.com/';

			const APIKEY = "wn8r98wcxnegkgy976xeuegt";
			const CONSUMER_SECRET = "QZytJQReSTM3K9bH4NG9Dd2A";

			//Random client ID we use to verify our calls
			const CLIENT_ID = '9253e5C3-28d6-48fd-c102-b92b8f250G1b';

			const REFERER = 'hustle_constantcontact_referer';
			const CURRENTPAGE = 'hustle_constantcontact_current_page';

			/**
			* Auth token
			* @var string
			*/
			private $option_token_name = 'hustle_opt-in-constant_contact-token';


			/**
			* @var string
			*/
			private $action_event = 'hustle_constantcontact_event';

			/**
			* @var bool
			*/
			var $is_error = false;

			/**
			* @var string
			*/
			var $error_message;

			/**
			* @var boolean
			*/
			var $sending = false;


			/**
			* Opt_In_ConstantContact_Api constructor.
			*/
			public function __construct() {
				// Init request callback listener
				add_action( 'init', array( $this, 'process_callback_request' ) );
			}

			/**
			* Helper function to listen to request callback sent from WPMUDEV
			*/
			function process_callback_request() {
				if ( $this->validate_callback_request( 'constantcontact' ) ) {
					$code 			= filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );
					// Get the referer page that sent the request
					$referer 		= is_multisite() ? get_site_option( self::REFERER ) : get_option( self::REFERER );
					$current_page 	= is_multisite() ? get_site_option( self::CURRENTPAGE ) : get_option( self::CURRENTPAGE );
					if ( $code ) {
						if ( $this->get_access_token( $code ) ) {
							if ( ! empty( $referer ) ) {
								wp_safe_redirect( $referer );
								exit;
							}
						}
					}
					// Allow retry but don't log referrer
					$authorization_uri = $this->get_authorization_uri( false, false, $current_page );

					$this->wp_die( __( 'Constant Contact integration failed!', Opt_In::TEXT_DOMAIN ), $authorization_uri, $referer );
				}
			}


			/**
			 * Generates authorization URL
			 *
			 * @param int $module_id
			 *
			 * @return string
			 */
			function get_authorization_uri( $module_id = 0, $log_referrer = true, $page = 'hustle_embedded' ) {

				$oauth = new Ctct\Auth\CtctOAuth2( self::APIKEY, self::CONSUMER_SECRET, $this->get_redirect_uri() );

				if ( $log_referrer ) {
					/**
					* Store $referer to use after retrieving the access token
					*/
					$referer       = add_query_arg( array( 'page' => $page,
			                                       'id'   => $module_id
			        ), admin_url( 'admin.php' ) );
					$update_option = is_multisite() ? 'update_site_option' : 'update_option';
					$update_option( self::REFERER, $referer );
					$update_option( self::CURRENTPAGE, $page );
				}

				return $oauth->getAuthorizationUrl();
			}



			/**
			* @param string $key
			*
			* @return bool|mixed
			*/
			function get_token( $key ) {
				$auth = $this->get_auth_token();

				if ( ! empty( $auth ) && ! empty( $auth[ $key ] ) )
					return $auth[$key];

				return false;
			}


			/**
			* Compose redirect_uri to use on request argument.
			* The redirect uri must be constant and should not be change per request.
			*
			* @return string
			*/
			function get_redirect_uri() {
				return $this->_get_redirect_uri(
					'constantcontact',
					'authorize',
					array( 'client_id' => self::CLIENT_ID )
				);
			}

			/**
			* Get Access token
			*
			* @param Array $args
			*/
			function get_access_token( $code ) {
				$oauth = new Ctct\Auth\CtctOAuth2( self::APIKEY, self::CONSUMER_SECRET, $this->get_redirect_uri() );
				$access_token = $oauth->getAccessToken( $code );

				$this->update_auth_token( $access_token );

				return true;
			}


			/**
			* Get stored token data.
			*
			* @return array|null
			*/
			function get_auth_token() {
				return is_multisite() ? get_site_option( $this->option_token_name ) : get_option( $this->option_token_name );
			}


			/**
			* Update token data.
			*
			* @param array $token
			* @return void
			*/
			function update_auth_token( array $token ) {
				if ( is_multisite() )
					update_site_option( $this->option_token_name, $token );
				else
					update_option( $this->option_token_name, $token );
			}

			/**
			* Retrieve contact lists from Hubspot
			*
			* @return array
			*/
			function get_contact_lists() {

				$cc_api = new Ctct\ConstantContact(self::APIKEY);

				$access_token = $this->get_token( 'access_token' );

				$lists_data = $cc_api->listService->getLists( $access_token );

				return ( !empty( $lists_data ) && is_array( $lists_data ) ) ? $lists_data : array();
			}

			/**
			* Check if email exists
			* If it exists just return the contact
			*
			* @return bool|Object
			*/
			function email_exist( $email, $list_id ) {
				$exists = false;
				$cc_api = new Ctct\ConstantContact(self::APIKEY);
				$access_token = $this->get_token( 'access_token' );
				$res = $cc_api->contactService->getContacts( $access_token, array( 'email' => $email ) );
				if ( is_object( $res ) && ! empty( $res->results ) ) {
					$contact = $res->results[0];
					if ( $contact instanceof Ctct\Components\Contacts\Contact ) {
						$lists = $contact->lists;
						$exists = $contact;
						foreach ( $lists as $list ) {
							$list = (array) $list;
							if ( $list_id == $list['id']  ) {
								$exists = true;
								break;
							}
						}
					}
				}
				return $exists;
			}



			/**
			* Subscribe contact
			*
			* @param String $email
			* @param String $list
			* @param Array $custom_fields
			*/
			function subscribe( $email, $first_name, $last_name, $list, $custom_fields = array() ) {
				$access_token = $this->get_token( 'access_token' );
				$cc_api = new Ctct\ConstantContact(self::APIKEY);
				$contact = new Ctct\Components\Contacts\Contact();
				$contact->addEmail( $email );
				if ( !empty ( $first_name ) ){
					$contact->first_name = $first_name;
				}
				if ( !empty ( $last_name ) ){
					$contact->last_name = $last_name;
				}
				$contact->addList( $list );

				if ( !empty( $custom_fields ) ) {
					$allowed = array(
						'prefix_name',
						'job_title',
						'company_name',
						'home_phone',
						'work_phone',
						'cell_phone',
						'fax',
					);

					// Add extra fields
					$x = 1;
					foreach ( $custom_fields as $key => $value ) {
						if ( in_array( $key, $allowed ) ) {
							$contact->$key = $value;
						} else {
							if ( ! empty( $value ) ) {
								$custom_field = array(
									'name' => 'CustomField' . $x,
									'value' => $value,
								);
								$contact->custom_fields[] = $custom_field;
								$x++;
							}
						}
					}
				}

				$response = $cc_api->contactService->addContact( $access_token, $contact );
				return $response;
			}

			/**
			* Update Subscription
			*
			*/
			function updateSubscription( $contact, $first_name, $last_name, $list, $custom_fields = array() ) {
				$access_token = $this->get_token( 'access_token' );
				$cc_api = new Ctct\ConstantContact(self::APIKEY);
				$contact->addList( $list );
				if ( !empty ( $first_name ) ){
					$contact->first_name = $first_name;
				}
				if ( !empty ( $last_name ) ){
					$contact->last_name = $last_name;
				}

				if ( !empty( $custom_fields ) ) {
					$allowed = array(
						'prefix_name',
						'job_title',
						'company_name',
						'home_phone',
						'work_phone',
						'cell_phone',
						'fax',
					);

					// Add extra fields
					$x = 1;
					foreach ( $custom_fields as $key => $value ) {
						if ( in_array( $key, $allowed ) ) {
							$contact->$key = $value;
						} else {
							if ( ! empty( $value ) ) {
								$custom_field = array(
									'name' => 'CustomField' . $x,
									'value' => $value,
								);
								$contact->custom_fields[] = $custom_field;
								$x++;
							}
						}
					}
				}

				$response = $cc_api->contactService->updateContact( $access_token, $contact );
				return $response;
			}
		}
	endif;
}