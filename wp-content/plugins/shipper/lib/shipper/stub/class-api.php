<?php
/**
 * Shipper stubs: API abstraction
 *
 * All API stubs will inherit from this.
 *
 * @package shipper
 */

/**
 * Stub API abstraction class
 */
abstract class Shipper_Stub_Api extends Shipper_Controller {

	/**
	 * Gets AJAX URL for the API call
	 *
	 * @param string $path API path.
	 *
	 * @return string
	 */
	public function get_api( $path ) {
		return trailingslashit( 'wp_ajax_shipper_stub_api_' ) . $path;
	}

	/**
	 * Gets AJAX nopriv URL for the API call
	 *
	 * @param string $path API path.
	 *
	 * @return string
	 */
	public function get_api_nopriv( $path ) {
		return trailingslashit( 'wp_ajax_nopriv_shipper_stub_api_' ) . $path;	   	 	    	 		     	 
	}

}