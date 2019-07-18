<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;

use WP_Defender\Module\Scan\Component\Token_Utils;

/**
 * Detecting any eval() or assert(), which should be avoid at all cost
 * Class Eval_Detect
 * @package WP_Defender\Module\Scan\Behavior\Pro\Scans
 */
class Eval_Detector extends Detector_Abstract {
	public function __construct( $index, $token ) {
		parent::__construct( $index, $token );
	}

	public function run() {
		if ( $this->token['code'] == T_EVAL ||
		     ( $this->token['code'] == T_STRING && $this->token['content'] == 'assert' )
		) {
			//sometime we hve codde like assert(true), whitelist them
			$next = Token_Utils::findFirstNext( $this->index + 1, null, array( T_WHITESPACE ) );
			$next = Token_Utils::$tokens[ $next ];
			if ( $next['code'] == T_OPEN_PARENTHESIS ) {
				$skip   = true;
				$params = Token_Utils::findParams( $next['parenthesis_opener'] + 1, $next['parenthesis_closer'] );
				//need to check the params a bit closer, as this so many plugins in wp repo still using this functiuon
				foreach ( $params as $param ) {
					if ( ! in_array( $param['code'], array( T_TRUE, T_FALSE ) ) ) {
						//need to see if the function is in the list
						$skip = false;
					}
				}
				if ( $skip == true ) {
					return false;
				}
			}

			return array(
				'type'   => 'eval',
				'text'   => 'The function eval called at line ' . $this->token['line'] . ' column ' . $this->token['column'] . ', which should be avoided whenever possible.',
				'offset' => $this->getCodeOffset(),
				'length' => strlen( $this->getCode() ),
				'line'   => $this->token['line'],
				'column' => $this->token['column']
			);
		}

		return false;
	}

	private function sigs() {
		return array( 'gzinflate', 'base64_decode' );
	}
}