<?php
/**
 * Mad Mimi API implementation
 *
 * Class Opt_In_Mad_Mimi_Api
 */
class Opt_In_Mad_Mimi_Api
{

	private $_user_name;
	private $_api_key;

	private $_endpoint = "https://api.madmimi.com/";


	function __construct( $username, $api_key, $args = array() ){
		$this->_user_name = $username;
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

		$url = add_query_arg( array(
			'api_key' => $this->_api_key,
			'username' => $this->_user_name,
		), $url );

		$_args = array(
			"method" => $verb
		);

		if( array() !== $args ){
			if( "GET" === $verb ){
				$url = add_query_arg( $args, $url );
			}else{
				$_args['body'] = json_encode( $args['body'] );
			}
		}

		$res = wp_remote_request( $url, $_args );

		if ( !is_wp_error( $res ) && is_array( $res ) ) {

			$res_code = wp_remote_retrieve_response_code( $res );
			if( $res_code <= 204 ) {
				libxml_use_internal_errors( true );
				return simplexml_load_string( wp_remote_retrieve_body( $res ) );
			}

			$err = new WP_Error();
			$err->add( $res_code, $res['response']['message'] );
			return  $err;
		}
		return  $res;
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
	 * Retrieves lists as array of objects
	 *
	 * @return array|WP_Error
	 */
	public function get_lists(){
		$res = $this->_get( "audience_lists/lists.xml");

		if( is_wp_error( $res ) )
			return $res;

		$res = (object) (array) $res;
		return isset ( $res->list ) ? $res->list : array();
	}

	/**
	 * Add new contact
	 *
	 * @param $data
	 * @return array|mixed|object|WP_Error
	 */
	public function subscribe( $list, array $data ){
		$action = add_query_arg( $data, "audience_members" );

		if( !empty( $list ) ){
			$action = "audience_lists/" . $list ."/add?";
			$action = add_query_arg( $data, $action );
		}

		$res =  $this->_post( $action );

		return empty( $res ) ? __("Successful subscription", Opt_In::TEXT_DOMAIN) : $res;
	}

	/**
	 * Get lists per email
	 *
	 * @param string $email
	 *
	 * @return array|WP_Error
	 */
	function search_email_lists( $email ) {
		$res = $this->_get( "audience_members/$email/lists.xml");

		if( is_wp_error( $res ) )
			return $res;

		$res = (object) (array) $res;
		return isset ( $res->list ) ? $res->list : array();
	}

	function search_by_email( $email ) {
		$action = 'audience_members/search.xml?query=' . $email;
		$res = $this->_get( $action );

		return $res;
	}
}