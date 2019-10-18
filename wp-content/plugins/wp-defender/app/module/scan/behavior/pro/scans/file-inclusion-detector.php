<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;

use WP_Defender\Module\Scan\Component\Token_Utils;

class File_Inclusion_Detector extends Detector_Abstract {
	public function __construct( $index, $token ) {
		parent::__construct( $index, $token );
	}

	public function run() {
		if ( in_array( $this->token['content'], Token_Utils::getInclutions() ) ) {
			/**
			 * it can not be an URL, any param from POST, GET, SERVER ...
			 */
			//find the next semicolon
			$next = Token_Utils::findNext( T_SEMICOLON, $this->index + 1 );
			for ( $i = $this->index + 1; $i < $next; $i ++ ) {
				$curr = Token_Utils::$tokens[ $i ];
				if ( in_array( $curr['content'], $this->getPredefinedVariables() ) ) {
					//it include from a global variable, catch
					return array(
						'type'   => 'rfi',
						'text'   => sprintf( __( 'The function %s line %d include an unsanitize user input %s', wp_defender()->domain ), $this->token['content'], $this->token['line'], $curr['content'] ),
						'offset' => $this->getCodeOffset(),
						'length' => strlen( $this->getCode() ),
						'line'   => $this->token['line'],
						'column' => $this->token['column']
					);
				}
			}
		}

		return false;
	}
}