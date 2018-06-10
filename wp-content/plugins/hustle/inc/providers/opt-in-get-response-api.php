<?php
/**
 * GetResponse API implementation
 *
 * Class Opt_In_Get_Response_Api
 */
class Opt_In_Get_Response_Api
{

	private $_api_key;

	private $_endpoint = "https://api.getresponse.com/v3/";

	/**
	 * Constructs class with required data
	 *
	 * Opt_In_Get_Response_Api constructor.
	 * @param $api_key
	 * @param array $args
	 */
	function __construct( $api_key, $args = array() ){
		$this->_api_key = $api_key;

		if( isset( $args['endpoint'] ) )
			$this->_endpoint = $args['endpoint'];
	}


	/**
	 * Sends request to the endpoint url with the provided $action
	 *
	 * @param string $verb
	 * @param string $action rest action
	 * @param array $args
	 * @return object|WP_Error
	 */
	private function _request( $verb = "GET", $action, $args = array() ){
		$url = trailingslashit( $this->_endpoint )  . $action;

		$_args = array(
			"method" => $verb,
			"headers" =>  array('X-Auth-Token' => 'api-key '. $this->_api_key,
				'Content-Type' => 'application/json;charset=utf-8'
			)
		);

		if( "GET" === $verb ){
			$url .= ( "?" . http_build_query( $args ) );
		}else{
			$_args['body'] = json_encode( $args['body'] );
		}

		$res = wp_remote_request( $url, $_args );
		if( ! is_wp_error( $res ) && is_array( $res ) && $res['response']['code'] <= 204 ) {
			return json_decode(  wp_remote_retrieve_body( $res ) );
		}

		if ( is_wp_error( $res ) ) {
			return $res;
		}

		$err = new WP_Error();
		$err->add($res['response']['code'], $res['response']['message'] );
		return  $err;
	}

	/**
	 * Sends rest GET request
	 *
	 * @param $action
	 * @param array $args
	 * @return array|mixed|object|WP_Error
	 */
	private function _get( $action, $args = array() ){
		return $this->_request( "GET", $action, $args );
	}

	/**
	 * Sends rest POST request
	 *
	 * @param $action
	 * @param array $args
	 * @return array|mixed|object|WP_Error
	 */
	private function _post( $action, $args = array()  ){
		return $this->_request( "POST", $action, $args );
	}

	/**
	 * Retrieves campains as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_campains(){
		return $this->_get( "campaigns", array(
			'name' => array( 'CONTAINS' => '%' ),
			"perPage" => 1000
		) );
	}

	/**
	 * Add new contact
	 *
	 * @param $data
	 * @return array|mixed|object|WP_Error
	 */
	public function subscribe( $data ){
		$res =  $this->_post("contacts", array(
			"body" =>  $data
		));

		return empty( $res ) ? __("Successful subscription", Opt_In::TEXT_DOMAIN) : $res;
	}

	function get_custom_fields() {
		$args = array( 'fields' => array( 'name' ) );
		$res = $this->_get( 'custom-fields', array(
			'body' => $args,
		) );

		return $res;
	}

	/**
	 * Add custom field
	 *
	 * @param (array) $custom_field
	 **/
	function add_custom_field( $custom_field ) {
		$res = $this->_post( 'custom-fields', array(
			'body' => $custom_field,
		));

		if ( ! empty( $res ) && ! empty( $res->customFieldId ) ) {
			return $res->customFieldId;
		}

		return false;
	}
}