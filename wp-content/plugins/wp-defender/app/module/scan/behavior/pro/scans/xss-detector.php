<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;

use WP_Defender\Module\Scan\Component\Token_Utils;

/**
 * Beta
 * Class Xss_Detector
 * @package WP_Defender\Module\Scan\Behavior\Pro\Scans
 */
class Xss_Detector extends Detector_Abstract {
	public function __construct( $index, $token ) {
		parent::__construct( $index, $token );
	}

	public function run() {
		$results = array();
		if ( $this->token['code'] == T_ECHO || $this->token['code'] == T_PRINT ) {
			//find to the right
			$semicolon = Token_Utils::findNext( T_SEMICOLON, $this->index + 1 );
			if ( $semicolon == false ) {
				return false;
			}

			$params = Token_Utils::findParams( $this->index + 1, $semicolon );
			foreach ( $params as $i => $param ) {
				if ( $param['code'] == T_VARIABLE && in_array( $param['content'], $this->getPredefinedVariables() ) ) {
					//we will move back a bit to see what's wrapper this one
					$caller = Token_Utils::findFirstPrevious( $i - 1, 0, array(
						T_WHITESPACE,
						T_OPEN_PARENTHESIS
					) );
					$caller = Token_Utils::$tokens[ $caller ];
					if ( ( $caller['code'] == T_STRING || $caller['code'] == T_ISSET )
					     && in_array( $caller['content'], Token_Utils::getEscapeFunction() ) ) {
						//looks like this one safe, moveing on
						continue;
					}
					$results = array(
						'type'   => 'xxs',
						'text'   => 'The function ' . $this->token['content'] . ' line ' . $this->token['line'] . ' column ' . $this->token['column'] . ' execute using unescape user inputs',
						'offset' => $this->getCodeOffset(),
						'length' => strlen( $this->getCode() ),
						'line'   => $this->token['line'],
						'column' => $this->token['column']
					);
				}
			}
		}

		return count( $results ) ? $results : false;
	}
}