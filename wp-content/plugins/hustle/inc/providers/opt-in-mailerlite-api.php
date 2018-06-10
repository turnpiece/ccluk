<?php
/**
 * MailerLite API Helper
 *
 */
 if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Opt_In_MailerLite_Api' ) ) :

    class Opt_In_MailerLite_Api {

        /**
         * Api Key
         *
         * @var String
         */
        protected $api_key;

        /**
         * End point
         *
         * @var String
         */
        private $_end_point = 'https://api.mailerlite.com/api/v2/';


        public function __construct( $_api_key ) {
            $this->api_key = $_api_key;
        }

        /**
         * Perform API remote request
         *
         * @param String $path - relative api path to the $_end_point
         * @param String $method - allowed methods are GET, POST, PUT, DELETE
         * @param String|Array $input - the post data
         *
         * @return WP_Error|Array
         */
        private function _do_request( $path, $method, $input ) {

            $called_url = $this->_end_point . $path;
            $ssl_verify = true;
            if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
                // Windows only over-ride
                $ssl_verify = false;
            }

            $args = array(
                'method'       => $method,
                'sslverify'    => apply_filters( 'opt_in_mailerlite_sslverify', $ssl_verify ),
                'headers'      => array(
                    'X-MailerLite-ApiKey'   => $this->api_key,
                    'Content-Type'          => 'application/json'
                ),
            );

            $args['body'] = $input;

            $response   = wp_remote_request( $called_url, $args );
            $data       = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $data ) ) {
                return $data;
            }

            return json_decode( $data, true );
        }

        /**
         * GET Request
         */
        private function _get( $path, $input = array() ) {
            return $this->_do_request( $path, "GET", $input );
        }

        /**
         * PUT Http request
         */
        private function _put( $path, $input = array() ) {
            return $this->_do_request( $path, "PUT", json_encode( $input ) );
        }

        /**
         * POST Http request
         */
        private function _post( $path, $input = array() ) {
            return $this->_do_request( $path, "POST", json_encode( $input ) );
        }

        /**
         * DELETE Http request
         */
        private function _delete( $path, $input = array() ) {
            return $this->_do_request( $path, "DELETE", $input );
        }

        /**
         * List Groups
         *
         * @return Array|WP_Error
         */
        public function list_groups() {
            return $this->_get( 'groups' );
        }

        /**
         * Add Subscriber
         *
         * @param Integer $group_id - the group id
         * @param Array $subscriber_data - An array containing the keys email and fields(name,value)
         */
        public function add_subscriber( $group_id,  $subscriber_data, $resubscribe = 0 ) {
            $subscriber_data['resubscribe'] = $resubscribe;
            return $this->_post( 'groups/' . $group_id . '/subscribers', $subscriber_data );
        }

        /**
         * Get Subscriber groups
         *
         * @param String $email
         */
        public function get_subscriber( $email ) {
            return $this->_get( 'subscribers/' . $email . '/groups' );
        }

        /**
         * Add custom field
         *
         * @param Array $field_data (title, type)
         */
        public function add_custom_field( $field_data ) {
            return $this->_post( 'fields', $field_data );
        }
    }
endif;
?>