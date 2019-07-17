<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;

use WP_Defender\Module\Scan\Component\Token_Utils;

class Code_Injection_Detector extends Detector_Abstract {
	public function __construct( $index, $token ) {
		parent::__construct( $index, $token );
	}

	public function run() {
		$funcs   = array_merge(
			Token_Utils::getFilesystemFunctions(),
			Token_Utils::getCreateFuncs(),
			Token_Utils::getCallbackFunctions(),
			array( 'extract' )
		);
		$results = array();
		if ( $this->token['code'] == T_STRING && in_array( $this->token['content'], $funcs ) ) {
			$next  = Token_Utils::$tokens[ $this->index + 1 ];
			$prev  = isset( Token_Utils::$tokens[ $this->index - 1 ] ) ? Token_Utils::$tokens[ $this->index - 1 ] : null;
			$prevv = isset( Token_Utils::$tokens[ $this->index - 2 ] ) ? Token_Utils::$tokens[ $this->index - 2 ] : null;
			if ( ! is_null( $prev ) && ! is_null( $prevv ) && $prev['content'] == '->' && $prevv['content'] == '$wpdb' ) {
				//whitelist for now
				return false;
			}
			if ( $next['code'] == T_OPEN_PARENTHESIS ) {
				$params = Token_Utils::findParams( $next['parenthesis_opener'] + 1, $next['parenthesis_closer'] );
				//loop to find the userinput
				foreach ( $params as $i => $param ) {
					if ( $param['code'] == T_VARIABLE && in_array( $param['content'], $this->getPredefinedVariables() ) ) {
						//gotcha
						//we will move back a bit to see what's wrapper this one
						$caller = Token_Utils::findFirstPrevious( $i - 1, 0, array(
							T_WHITESPACE,
							T_OPEN_PARENTHESIS
						) );
						$caller = Token_Utils::$tokens[ $caller ];
						if ( ( $caller['code'] == T_STRING || $caller['code'] == T_ISSET )
						     && in_array( $caller['content'], array_merge( Token_Utils::getSanitizeFunctions(), array( 'isset' ) ) ) ) {
							//looks like this one safe, moveing on
							continue;
						} elseif ( $caller['content'] == 'array_walk' || $caller['content'] == 'array_walk_recursive' ) {
							//this mean the $param+1 is a function name, need to check it
							$callable = Token_Utils::findFirstNext( $i + 1, $next['parenthesis_closer'], array(
								T_WHITESPACE,
								T_COMMA
							) );
							$callable = Token_Utils::$tokens[ $callable ];
							$string   = trim( $callable['content'], '"\'' );

							if ( in_array( $string, Token_Utils::getSanitizeFunctions() ) ) {
								//look safe
								continue;
							}
						}
						$results = array(
							'type'   => 'code_injection',
							'text'   => 'The function ' . $this->token['content'] . ' line ' . $this->token['line'] . ' column ' . $this->token['column'] . ' execute using unsanitize user inputs',
							'offset' => $this->getCodeOffset(),
							'length' => strlen( $this->getCode() ),
							'line'   => $this->token['line'],
							'column' => $this->token['column']
						);
					}
				}
			}
		}

		return count( $results ) ? $results : false;
	}
}