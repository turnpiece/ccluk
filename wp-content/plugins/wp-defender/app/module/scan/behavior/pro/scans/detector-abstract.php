<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;

use WP_Defender\Module\Scan\Component\Token_Utils;

abstract class Detector_Abstract {
	protected $token;
	protected $index;

	public function __construct( $index, $token ) {
		$this->index = $index;
		$this->token = $token;
	}

	/**
	 * Get code of a token
	 * @return string
	 */
	public function getCode() {
		//a statement will be end with a comma
		$next = Token_Utils::findNext( T_SEMICOLON, $this->index + 1 );

		return Token_Utils::getTokensAsStringByIndex( $this->index, $next );
	}

	/**
	 * @return bool|int
	 */
	public function getCodeOffset() {
		$lines = array();

		for ( $i = 0; $i <= $this->index; $i ++ ) {
			$curr = Token_Utils::$tokens[ $i ];
			if ( ! isset( $lines[ $curr['line'] ] ) ) {
				$lines[ $curr['line'] ] = 0;
			}
			if ( $curr['column'] > $lines[ $curr['line'] ] ) {
				$lines[ $curr['line'] ] = $curr['column'];
			}

			if ( isset( Token_Utils::$tokens[ $i + 1 ] ) && Token_Utils::$tokens[ $i + 1 ]['line'] > $curr['line'] ) {
				//this one is end of line
				$lines[ $curr['line'] ] += $curr['length'];
			}
		}
		//if the code is first of this line, column will be 1
		return array_sum( $lines ) - 1;
	}

	abstract function run();

	/**
	 * @return array
	 */
	protected function getPredefinedVariables() {
		return array(
			'$_GET',
			'$_POST',
			//'$_FILES',
			'$_REQUEST',
			'$_HTTP_POST_VARS',
			'$_SERVER',
			'$_COOKIE'
		);
	}
}