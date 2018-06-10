<?php
if( !class_exists("Opt_In_HubSpot_Api") ):
	/**
	* Class Opt_In_HubSpot_Api
	*/
	class Opt_In_HubSpot_Api extends Opt_In_WPMUDEV_API {
		const CLIENT_ID = '5253e533-2dd2-48fd-b102-b92b8f250d1b';
		const CLIENT_SECRET = '2ed54e79-6ceb-4fc6-96d9-58b4f98e6bca';
		const HAPIKEY = 'db9600bf-648c-476c-be42-6621d7a1f96a';
		const BASE_URL = 'https://app.hubspot.com/';
		const API_URL = 'https://api.hubapi.com/';

		const REFERER = 'hustle_hubspot_referer';
		const CURRENTPAGE = 'hustle_hubspot_current_page';

		/**
		* @var string
		*/
		private $option_name = 'hustle_opt-in-hubspot-token';

		/**
		* @var string
		*/
		private $action_event = 'hustle_hubspot_event';

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
		* Opt_In_HubSpot_Api constructor.
		*/
		public function __construct() {
			// Init request callback listener
			add_action( 'init', array( $this, 'process_callback_request' ) );

			// Listen to wp-cron scheduled event
			add_action( $this->action_event, array( $this, 'refresh_access_token' ) );
		}

		/**
		* Helper function to listen to request callback sent from WPMUDEV
		*/
		function process_callback_request() {
			if ( $this->validate_callback_request( 'hubspot' ) ) {
				$code 			= filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );
				// Get the referer page that sent the request
				$referer 		= is_multisite() ? get_site_option( self::REFERER ) : get_option( self::REFERER );
				$current_page 	= is_multisite() ? get_site_option( self::CURRENTPAGE ) : get_option( self::CURRENTPAGE );
				if ( $code ) {
					if ( $this->get_access_token( array( 'code' => $code ) ) ) {
						if ( ! empty( $referer ) ) {
							wp_safe_redirect( $referer );
							exit;
						}
					}
				}
				// Allow retry but don't log referrer
				$authorization_uri = $this->get_authorization_uri( false, false, $current_page );

				$this->wp_die( __( 'Hubspot integration failed!', Opt_In::TEXT_DOMAIN ), $authorization_uri, $referer );
			}
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
				'hubspot',
				'authorize',
				array( 'client_id' => self::CLIENT_ID )
			);
		}

		function refresh_access_token() {
			$args = array(
				'grant_type' => 'refresh_token',
				'refresh_token' => $this->get_token( 'refresh_token' ),
			);

			return $this->get_access_token( $args );
		}

		/**
		* Get or retrieve access token from Hubspot.
		*
		* @param array $args
		* @return bool
		*/
		function get_access_token( array $args ) {
			$args = wp_parse_args ( $args, array(
				'redirect_uri' => $this->get_redirect_uri(),
				'grant_type' => 'authorization_code',
			) );

			$response = $this->_request( 'oauth/v1/token', 'POST', $args, false, true );

			if ( ! is_wp_error( $response ) && ! empty( $response->refresh_token ) ) {
				$token_data = get_object_vars( $response );

				// Update auth token
				$this->update_auth_token( $token_data );

				// Remove previously set cron event
				wp_clear_scheduled_hook( $this->action_event );

				// Hubspot access token expires every 6 hrs so we'll trigger the refresh token retrieval
				// 5 minutes before it's expiration
				$time = time() + 21300;

				wp_schedule_single_event( $time, $this->action_event );

				return true;
			} else {
				// Removed access token data
				$this->update_auth_token(array());
			}

			return false;
		}

		/**
		* @param string $endpoint The endpoint the request will be sent to.
		* @param string $method
		* @param array $query_args Additional args to include in the request body.
		* @param string $access_token
		* @param boolean $x_www Whether the request is sent in application/x-www-form format.
		*
		* @return mixed
		*/
		function _request( $endpoint, $method = 'GET', $query_args = array(), $access_token = '', $x_www = false, $json = false ) {
			// Avoid multiple call at once
			if ( $this->sending )
				return false;

			$this->sending = true;
			$url = self::API_URL . $endpoint;

			$args = array(
				'client_id' => self::CLIENT_ID,
				'client_secret' => self::CLIENT_SECRET,
				'scope' => 'contacts',
			);
			$args = wp_parse_args( $args, $query_args );

			if ( ! $x_www && $json )
				$args = json_encode($args);

			$_args = array(
				'method' => $method,
				'headers' => array(
					'Authorization' => 'Bearer ' . ( ! empty( $access_token ) ? $access_token : self::HAPIKEY ),
					'Content-Type' => 'application/json;charset=utf-8',
				),
				'body' => $args,
			);

			if ( 'POST' == $method && $x_www )
				$_args['headers']['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

			$response = wp_remote_request( $url, $_args );

			$this->sending = false;

			if ( ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ) );

				if ( $response['response']['code'] <= 204
					|| isset( $body->status ) && 'error' == $body->status )
					return $body;
			}
			return $response;
		}

		/**
		* Helper function to send authenticated Post request.
		*
		* @param $end_point
		* @param array $query_args
		* @param bool $x_www
		*
		* @return mixed
		*/
		function send_authenticated_post( $end_point, $query_args = array(), $x_www = false, $json = false ) {
			$access_token = $this->get_token( 'access_token' );
			return $this->_request( $end_point, 'POST', $query_args, $access_token, $x_www, $json );
		}

		/**
		* Helper function to send authenticated GET request.
		*
		* @param $endpoint
		* @param array $query_args
		*
		* @return mixed
		*/
		function send_authenticated_get( $endpoint, $query_args = array() ) {
			$access_token = $this->get_token( 'access_token' );
			return $this->_request( $endpoint, 'GET', $query_args, $access_token );
		}

		/**
		* Get stored token data.
		*
		* @return array|null
		*/
		function get_auth_token() {
			return is_multisite() ? get_site_option( $this->option_name ) : get_option( $this->option_name );
		}

		/**
		* Update token data.
		*
		* @param array $token
		* @return void
		*/
		function update_auth_token( array $token ) {
			if ( is_multisite() )
				update_site_option( $this->option_name, $token );
			else
				update_option( $this->option_name, $token );
		}

		/**
		* @return bool
		*/
		function is_authorized() {
			$auth = $this->get_auth_token();

			if ( empty( $auth ) )
				return false;

			// Attempt to refresh token
			return $refresh = $this->refresh_access_token();
		}

		/**
		* Generates authorization URL
		*
		* @param int $module_id
		*
		* @return string
		*/
		function get_authorization_uri( $module_id = 0, $log_referrer = true, $page = 'hustle_embedded' ) {
			$args = array(
				'client_id' => self::CLIENT_ID,
				'scope' => 'contacts',
				'redirect_uri' => $this->get_redirect_uri(),
			);
			$args = http_build_query( $args );

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

			return self::BASE_URL . 'oauth/authorize?' . $args;
		}

		/**
		* Retrieve contact lists from Hubspot
		*
		* @return array
		*/
		function get_contact_list() {
			$listing = array();

			$args = array(
				'count' => 200,
				'offset' => 0,
			);
			$res = $this->send_authenticated_get( 'contacts/v1/lists/static', $args );

			if ( ! is_wp_error( $res ) && ! empty( $res->lists ) )
				foreach ( $res->lists as $list )
					$listing[ $list->listId ] = array( 'value' => $list->listId, 'label' => $list->name );

			return $listing;
		}

		/**
		* Check if the given email address is already a subscriber.
		*
		* @param string $email The email address to check.
		*
		* @return bool|mixed
		*/
		function email_exists( $email ) {
			$args = array( 'showListMemberships' => true );
			$endpoint = 'contacts/v1/contact/email/' . $email . '/profile';

			$res = $this->send_authenticated_get( $endpoint, $args );

			if ( ! is_wp_error( $res ) && ! empty( $res->vid ) )
				return $res;

			return false;
		}

		/**
		* Get the list of existing properties from Hubspot account.
		*
		* @return array
		*/
		function get_properties() {
			$properties = array();
			$res = $this->send_authenticated_get( 'properties/v1/contacts/properties' );
			if ( ! is_wp_error( $res ) && ! isset( $res->status ) )
				foreach ( $res as $prop )
					$properties[ $prop->name ] = $prop->label;

			return $properties;
		}

		/**
		* Add new field contact property to Hubspot.
		*
		* @param array $property
		*
		* @return bool
		*/
		function add_property( array $property ) {
			$res = $this->send_authenticated_post( 'properties/v1/contacts/properties', $property, false, true );

			return ! is_wp_error( $res ) && ! empty( $res->name );
		}

		/**
		* Add contact subscriber to Hubspot.
		*
		* @param array $data
		*
		* @return mixed
		*/
		function add_contact( $data ) {
			$props = array();

			foreach ( $data as $key => $value ) {
				if ( 'first_name' == $key || 'f_name' == $key )
					$key = 'firstname';
				if ( 'last_name' == $key || 'l_name' == $key )
					$key = 'lastname';

				$props[] = array( 'property' => $key, 'value' => $value );
			}

			$args = array( 'properties' => $props );

			$res = $this->send_authenticated_post( 'contacts/v1/contact', $args, false, true );

			if ( ! is_wp_error( $res ) && ! empty( $res->vid ) )
				return $res->vid;

			return $res;
		}

		/**
		* Add contact to contact list.
		*
		* @param $contact_id
		* @param $email
		* @param $email_list
		*
		* @return bool|mixed
		*/
		function add_to_contact_list( $contact_id, $email, $email_list ) {
			$args = array(
				'listId' => $email_list,
				'vid' => array( $contact_id ),
				'emails' => array( $email ),
			);
			$endpoint = 'contacts/v1/lists/' . $email_list . '/add';
			$res = $this->send_authenticated_post( $endpoint, $args, false, true );

			if ( ! is_wp_error( $res ) && ! empty( $res->updated ) )
				return true;

			return false;
		}
	}
endif;