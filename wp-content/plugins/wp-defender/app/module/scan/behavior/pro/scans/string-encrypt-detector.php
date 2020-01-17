<?php

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;
class String_Encrypt_Detector extends Detector_Abstract {
	public function __construct( $index, $token ) {
		parent::__construct( $index, $token );
	}

	function run() {
		$results = array();
		if ( $this->token['code'] == T_CONSTANT_ENCAPSED_STRING ) {
			$value               = $this->token['content'];
			$value_without_quote = str_replace( [ '"', "'" ], '', $value );
			$pattern             = "/\\\x[0-9a-zA-Z]{1,2}/";
			$matches             = [];
			if ( preg_match_all( $pattern, $value, $matches ) ) {
				if ( count( $matches[0] ) > 2 ) {
					//something wrong as we got an ascii mask
					$results = array(
						'type'   => 'encrypt_string',
						'text'   => sprintf( __( "The variable %s line %d column %d looks suspicious", wp_defender()->domain ), $this->token['content'], $this->token['line'], $this->token['column'] ),
						'offset' => $this->getCodeOffset(),
						'length' => strlen( $this->getCode() ),
						'line'   => $this->token['line'],
						'column' => $this->token['column'],
						'output' => false
					);
				}
			} elseif ( in_array( $value_without_quote, [ 'base64_encode', 'base64_decode' ] ) ) {
				//why this function need to be a variable? this should not be shout out but only with eval
				$results = array(
					'type'   => 'variable_function',
					'output' => false
				);
			}
		}

		return count( $results ) ? $results : false;
	}
}