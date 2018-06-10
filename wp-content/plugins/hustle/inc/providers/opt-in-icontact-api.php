<?php
/**
 * IContact API Helper
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Opt_In_IContact_Api' ) ) :

    /**
     * IContact API
     */
    class Opt_In_IContact_Api {

        /**
         * App ID
         *
         * @var String
         */
        protected $app_id;

        /**
         * API Password
         *
         * @var String
         */
        protected $api_password;

        /**
         * API Username
         *
         * @var String
         */
        protected $api_username;

        /**
         * Account ID
         *
         * @var Integer
         */
        protected $account_id;

        /**
         * Client Folder ID
         *
         * @var Integer
         */
        protected $folder_id;


        /**
         * Error Object
         *
         * @var bool|WP_Error
         */
        protected $error;

        /**
         * End point
         *
         * @var String
         */
        private $_end_point = 'https://app.icontact.com/icp';


        const API_CACHE_KEY = 'HUSTLE_ICONTACT_API_CACHE';

        const API_VERSION = '2.1';


        /**
         * Plugin constructor
         *
         * @param String $_app_id - the application id
         * @param String $_api_password - the api password
         * @param String $_api_username - the api username
         *
         * @throws Exception
         */
        public function __construct( $_app_id, $_api_password, $_api_username ) {
            $this->app_id       = $_app_id;
            $this->api_password = $_api_password;
            $this->api_username = $_api_username;
            $this->error        = false;

            //Set up other API details
            try {
                $this->_get_account_id();
                $this->_get_client_folder_id();
            } catch( Exception $e ) {
                throw new Exception( $e->getMessage() );
            }
        }

        /**
         * Generate the request url
         *
         * @param bool $full_path . Defaults to false. Set to true to get the full api url
         *
         * @return String
         */
        private function _build_url( $full_path = false ) {
            $base_url = $this->_end_point;
            if ( $full_path != false ) {
                return $base_url . "/a/{$this->account_id}/c/{$this->folder_id}";
            }
            return $base_url;
        }

        /**
         * Get the account id
         *
         * @param $_account_id - the account id. If not set, it will be pulled from the api
         *
         * @throws Exception
         */
        private function _get_account_id( $_account_id = null ){
            if ( !empty( $_account_id ) ) {
                $this->account_id = (integer) $_account_id;
            } else {
                $account_cache = wp_cache_get( 'hustle_icontact_account_id', self::API_CACHE_KEY );
                if ( $account_cache ) {
                    $this->account_id = (integer) $account_cache;
                } else {
                    $account_data = $this->_do_request( '/a/' );
                    if ( !is_wp_error( $account_data ) ) {
                        if ( is_array( $account_data ) && count( $account_data ) > 0 ){
                            if ( isset( $account_data['errors'] ) &&  $account_data['errors'] ) {
                                throw new Exception( __( 'Please check your API Credentials.', Opt_In::TEXT_DOMAIN ) );
                            } else {
                                if ( isset ( $account_data['accounts'] ) && is_array( $account_data['accounts'] ) ) {
                                    $account = $account_data['accounts'][0];
                                    if ( intval( $account['enabled'] ) === 1 ) {
                                        $this->account_id = (integer) $account['accountId'];
                                        wp_cache_set( 'hustle_icontact_account_id', $this->account_id, self::API_CACHE_KEY );
                                    } else {
                                        throw new Exception( __( 'Your account has been disabled.', Opt_In::TEXT_DOMAIN ) );
                                    }
                                } else {
                                    throw new Exception( __( 'Your have no accounts. Please check your credentials', Opt_In::TEXT_DOMAIN ) );
                                }
                            }

                        } else {
                            throw new Exception( __( 'Your have no accounts. Please check your credentials', Opt_In::TEXT_DOMAIN ) );
                        }
                    } else {
                        throw new Exception( $account_data->get_error_message() );
                    }
                }
            }
        }

        /**
         * Get the folder id
         *
         * @param $_folder_id - the folder id. If not set, it will be pulled from the api
         *
         * @throws Exception
         */
        private function _get_client_folder_id( $_folder_id = null ) {
            if ( !empty( $_folder_id ) ) {
                $this->folder_id = (integer) $_folder_id;
            } else {
                $folder_cache = wp_cache_get( 'hustle_icontact_client_folder_id', self::API_CACHE_KEY );
                if ( $folder_cache ) {
                    $this->folder_id = (integer) $folder_cache;
                } else {
                    $resource = (string) "/a/{$this->account_id}/c";
                    $folder_data = $this->_do_request( $resource );
                    if ( !is_wp_error( $folder_data ) ) {
                        if ( is_array( $folder_data ) && count( $folder_data ) > 0 && isset( $folder_data['clientfolders'] ) && count( $folder_data['clientfolders']  ) > 0 ){
                            $folder = $folder_data['clientfolders'][0];
                            $this->folder_id = (integer) $folder['clientFolderId'];
                            wp_cache_set( 'hustle_icontact_client_folder_id', $this->folder_id, self::API_CACHE_KEY );
                        } else {
                            throw new Exception( __( 'No client folders were found for this account', Opt_In::TEXT_DOMAIN ) );
                        }
                    } else {
                        throw new Exception( $account_data->get_error_message() );
                    }
                }
            }
        }

        /**
         * Perform API Call
         *
         * @param String $path - relative path
         * @param String $method - Request method
         * @param Array $input - the data input
         *
         */
        private function _do_request( $path, $method = 'GET', $input = null ) {
            $request_url = $this->_build_url() . $path;

            $method = strtoupper( $method );

            $args = array(
                'method'       => $method,
                'headers'      => array(
                    'Except'       => '',
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'Api-Version'  => self::API_VERSION,
                    'Api-AppId'    => $this->app_id,
                    'Api-Username' => $this->api_username,
                    'Api-Password' => $this->api_password,

                ),
            );
            if ( !empty( $input ) ) {
                switch ( $method ) {
                    case 'PUT' :
                    case 'POST' :
                        $args['body'] = json_encode( $input );
                        break;
                    default :
                        $args['body'] = $input;
                        break;
                }
            }


            $response   = wp_remote_request( $request_url, $args );
            $data       = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $data ) ) {
                return $data;
            }

            return json_decode( $data, true );
        }

        /**
         * Get Lists
         *
         * @return Array|WP_Error
         */
        public function get_lists() {
            return $this->_do_request( "/a/{$this->account_id}/c/{$this->folder_id}/lists", 'GET', null );
        }

        /**
         * Add a subscriber
         * First we add the subscriber and get the subscirber id
         * Then we add the subscriber id to the list
         *
         * @param Integer $list_id - the list id
         * @param Array $contact_details - the contact details
         * @param String $status  - normal, pending, unsubscribed
         *
         * @throws Exception
         *
         * @return String
         */
        public function add_subscriber( $list_id, $contact_details , $status = 'normal' ) {
            $valid_statuses = array( 'normal', 'pending', 'unsubscribed' );

            // Validate status
            if (! empty( $status ) && !in_array( $status, $valid_statuses ) ) {
                $status = 'normal';
            }

            //Save contact
            $contact = $this->_save_contact( $contact_details );

            $error_message = __( 'Something went wrong, please compare your Opt-in fields with IContact fields and add any missing fields', Opt_In::TEXT_DOMAIN );

            if ( is_wp_error( $contact ) ) {
                $this->error = $contact;
                throw new Exception( $error_message );
            } else {
                if ( is_array( $contact ) && is_array( $contact['contacts'] ) ) {
                    $contact_id = $contact['contacts'][0]['contactId'];

                    $subscriptions = $this->_do_request( "/a/{$this->account_id}/c/{$this->folder_id}/subscriptions", 'POST', array(
                                        array(
                                            'contactId' => $contact_id,
                                            'listId'    => $list_id,
                                            'status'    => $status
                                        )
                                    ) );

                    if ( is_wp_error( $subscriptions ) ) {
                        return $subscriptions;
                    } else {
                        return __( "Successful subscription", Opt_In::TEXT_DOMAIN );
                    }
                } else {
                    $this->_set_error( 'contact_error', 'Something went wrong. Please try again' );
                    throw new Exception( __( 'Something went wrong, please try again', Opt_In::TEXT_DOMAIN ) );
                }
            }
        }

        /**
         * Add Custom Field
         *
         *  @param Array $field
         *          @options displayToUser {Intger} - 1 to display the field or 0 to hide the field
         *          @options privateName {String} - Indicates the name displayed to the iContact user. This name will not appear to the contact
         *          @options fieldType {String} - String, one of the following: checkbox, text, number, decimalOne, decimalTwo, decimalThree, decimalFour, date
         */
        public function add_custom_field( $field ) {
            return $this->_do_request( "/a/{$this->account_id}/c/{$this->folder_id}/customfields", 'POST', array( $field ) );
        }

        /**
         * Get Contacts In a list
         *
         * @param Integer $list_id - the list id
         */
        public function get_contacts( $list_id ) {
            $response = $this->_do_request( "/a/{$this->account_id}/c/{$this->folder_id}/contacts?status=total&listId={$list_id}", 'GET' );
            return $response;
        }

        /**
         * Subscribe an email
         *
         * @param Array $contact_details - array of contact details
         *          @options email {String} - the email
         *          @options prefix {String} - the name prefix
         *          @options firstName {String} - the First name
         *          @options lastName {String} - the Last Name
         *          @options status {String} - the name status ('normal', 'bounced', 'donotcontact', 'pending', 'invitable', 'deleted')
         *
         * @return WP_Error | Object
         */
        private function _save_contact( $contact_details ) {
            $response = $this->_do_request( "/a/{$this->account_id}/c/{$this->folder_id}/contacts", 'POST', array( $contact_details ) );
            return $response;
        }

        /**
         * Set Error
         *
         * @param String $tag - the error tag
         * @param String $message - the error message
         */
        private function _set_error( $tag, $message ) {
            if ( !$this->error )
                $this->error = new WP_Error();
            $this->error->add( $tag, $message );
        }

        /**
         * Get the Error
         *
         * @return bool|WP_Error
         */
        public function get_error() {
            return $this->error;
        }
    }

endif;
?>