<?php

class WP_Hummingbird_API_Exception extends Exception {

	public function __construct( $message = '', $code = 0, Exception $previous = null ) {
		if ( ! is_numeric( $code ) ) {
			switch ( $code ) {
				default:
					$code = 500;
			}
		}

		parent::__construct( $message, $code );
	}
}
