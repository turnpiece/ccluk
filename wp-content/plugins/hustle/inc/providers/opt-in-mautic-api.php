<?php
/**
 * Mautic API Helper
 **/
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Opt_In_Mautic_Api' ) ) :
	require_once Opt_In::$vendor_path . 'mautic/api-library/lib/MauticApi.php';

	class Opt_In_Mautic_Api {
		/**
		 * @var (string) Mautic installation URL
		 **/
		private $base_url;

		/**
		 * @var (string) The username use to login.
		 **/
		private $username;

		/**
		 * @var (string) The password use to authenticate.
		 **/
		private $password;

		/**
		 * @var (object) \MauticApi class instance
		 **/
		private $api;

		/**
		 * @var (object) \Mautic\Auth\ApiAuth class instance.
		 **/
		private $auth;

		function __construct( $base_url, $username, $password ) {
			$this->base_url = $base_url;
			$this->username = $username;
			$this->password = $password;

			if ( ! empty( $base_url ) && ! empty( $username ) && ! empty( $password ) ) {
				$params = array(
					'baseUrl' => $this->base_url,
					'userName' => $this->username,
					'password' => $this->password,
				);

				$initAuth = new Mautic\Auth\ApiAuth();
				$this->auth = $initAuth->newAuth( $params, 'BasicAuth' );
				$this->api = new Mautic\MauticApi( $this->auth, $this->base_url );
			}
		}

		/**
		 * Retrieve the list of segments from Mautic installation.
		 **/
		function get_segments() {
			$segmentApi = $this->api->newApi( 'segments', $this->auth, $this->base_url );

			try {
				$segments = $segmentApi->getList();

				if ( ! empty( $segments ) && ! empty( $segments['lists'] ) ) {
					return $segments['lists'];
				}
			} catch( Exception $e ) {
				return false;
			}
			return false;
		}

		/**
		 * Add contact to Mautic installation.
		 *
		 * @param (associative_array) $data			An array of contact details to add.
		 * @return Returns contact ID on success or WP_Error.
		 **/
		function add_contact( $data, Hustle_Module_Model $module ) {
			$contactApi = $this->api->newApi( 'contacts', $this->auth, $this->base_url );
			$err = new WP_Error();

			try {
				$res = $contactApi->create( $data );

				if ( $res && ! empty( $res['contact'] ) ) {
					$contact_id = $res['contact']['id'];

					// Double check custom fields
					if ( ! empty( $res['contact']['fields'] ) && ! empty( $res['contact']['fields']['core'] ) ) {
						$found_missing = 0;

						$contact_fields = array_keys( $res['contact']['fields']['core'] );
						$common_fields = array( 'firstname', 'lastname', 'email', 'ipAddress' );

						foreach ( $data as $key => $value ) {
							// Check only uncommon fields
							if ( ! in_array( $key, $common_fields ) && ! in_array( $key, $contact_fields ) ) {
								$found_missing++;
							}
						}

						if ( $found_missing > 0 ) {
							$data['error'] = __( 'Some fields are not added.', Opt_In::TEXT_DOMAIN );
							unset( $data['ipAddress'] );
							$module->log_error( $data );
						}
					}

					return $contact_id;
				} else {
					$err->add( 'susbscribe_error', __( 'Something went wrong. Please try again', Opt_In::TEXT_DOMAIN ) );
				}
			} catch( Exception $e ) {
				$error = $e->getMessage();
				$err->add( 'subscribe_error', $error );
			}

			return $err;
		}

		/**
		 * Check if an email is already used.
		 *
		 * @param (string) $email
		 * @return Returns true if the given email already in use otherwise false.
		 **/
		function email_exist( $email ) {
			$contactApi = $this->api->newApi( 'contacts', $this->auth, $this->base_url );
			$err = new WP_Error();

			try {
				$res = $contactApi->getList( $email, 0, 1000 );

				return ! empty( $res ) && ! empty( $res['total'] );
			} catch( Exception $e ) {
				$err->add( 'server_error', $e->getMessage() );
			}

			return $err;
		}

		/**
		 * Add contact to segment list.
		 *
		 * @param (int) $segment_id
		 * @param (int) $contact_id
		 **/
		function add_contact_to_segment( $segment_id, $contact_id ) {
			$segmentApi = $this->api->newApi( 'segments', $this->auth, $this->base_url );
			$err = new WP_Error();

			try {
				$add = $segmentApi->addContact( $segment_id, $contact_id );
				return $add;
			} catch( Exception $e ) {
				$err->add( 'subscribe_error', $e->getMessage() );
			}
			return $err;
		}

		/**
		 * Get the list of available contact custom fields.
		 **/
		function get_custom_fields() {
			$contactApi = $this->api->newApi( 'contacts', $this->auth, $this->base_url );
			$fields = $contactApi->getFieldList();

			return $fields;
		}

		/**
		 * Add custom contact field.
		 *
		 * @param (array) $field
		 **/
		function add_custom_field( $field ) {
			$fieldApi = $this->api->newApi( 'contactFields', $this->auth, $this->base_url );
			$res = $fieldApi->create( $field );

			return ! empty( $res ) && ! empty( $res['field'] );
		}
	}
endif;