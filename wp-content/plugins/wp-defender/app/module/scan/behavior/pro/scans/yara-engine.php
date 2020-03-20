<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro\Scans;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Scan\Component\Token_Utils;

/**
 * Detecting any eval() or assert(), which should be avoid at all cost
 * Class Eval_Detect
 * @package WP_Defender\Module\Scan\Behavior\Pro\Scans
 */
class Yara_Engine {
	protected $content;
	protected $patterns;

	public function __construct( $content, $patterns ) {
		$this->content  = $content;
		$this->patterns = $patterns;
	}

	public function run() {
		$scanErrors = [];
		foreach ( $this->patterns as $rule ) {
			$ret = $this->processPattern( $this->content, $rule );
			if ( ! empty( $ret ) ) {
				$scanErrors = array_merge( $scanErrors, $ret );
			}
		}

		return $scanErrors;
	}

	/**
	 * @param $content
	 * @param $rule
	 *
	 * @return mixed
	 */
	private function processPattern( $content, $rule ) {
		$catches     = [];
		$description = '';
		if ( isset( $rule['tags']['description'] ) ) {
			$description = $rule['tags']['description'];
		} else if ( isset( $rule['meta'] ) ) {
			foreach ( $rule['meta'] as $item ) {
				if ( isset( $item['key'] ) && $item['key'] == 'description' ) {
					$description = $item['val'];
					break;
				}
			}
		}
		if ( ! is_array( $rule['strings'] ) ) {
			$rule['strings'] = [];
		}
		foreach ( $rule['strings'] as $string ) {
			switch ( $string['type'] ) {
				case 0:
					$pos = strpos( $content, $string['text'] );
					if ( $pos !== false ) {
						$catches[ $string['id'] ][] = [
							'id'     => $string['id'],
							'offset' => $pos,
							'length' => strlen( $string['text'] ),
							'code'   => $string['text'],
							'text'   => $description,
							'type'   => $rule['identifier'],
							'line'   => $this->findLine( $content, $pos, strlen( $string['text'] ) )
						];
					}
					break;
				case 2:
					$pattern = "/{$string['text']}/";
					if ( preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
						foreach ( $matches[0] as $match ) {
							$catches[ $string['id'] ][] = [
								'id'     => $string['id'],
								'offset' => $match[1],
								'length' => strlen( $match[0] ),
								'code'   => $match[0],
								'text'   => $description,
								'type'   => $rule['identifier'],
								'line'   => $this->findLine( $content, $match[1], strlen( $match[0] ) )
							];
						}
					}
					break;
			}
		}

		if ( ! empty( $catches ) ) {
			//parse condition here
			if ( $this->validateCondition( $catches, $rule['condition'], count( $rule['strings'] ) ) ) {
				return call_user_func_array( 'array_merge', $catches );
			}
		}
	}

	private function validateCondition( $catches, $condition, $total_rules ) {
		$pattern = '/([a-z0-9]+) of them/';
		if ( preg_match( $pattern, $condition, $matches ) ) {
			$number = $matches[1];
			if ( $number == 'all' ) {
				$number = $total_rules;
			}

			if ( $number == 'any' ) {
				$number = 1;
			}

			if ( count( $catches ) == $number ) {
				return true;
			}
		} elseif ( $condition == '$php at 0 and all of ($s*) and filesize > 570 and filesize < 800' ) {
			//this case is so specific
			//remove it
			if ( $catches['$php'][0]['offset'] == 0 ) {
				unset( $catches['$php'] );

				return count( $catches ) == $total_rules - 1;
			}
		} elseif ( $condition == '( $magic at 0 ) and ( 1 of ($s*) )' ) {
			if ( isset( $catches['$magic'] ) && $catches['$magic'][0]['offset'] == 0 ) {
				unset( $catches['$magic'] );

				return count( $catches );
			}
		} elseif ( $condition == '2 of ($s*) and not $fn' ) {
			return ! isset( $catches['$fn'] ) && count( $catches ) > 2;
		}

		return false;
	}

	private function parseYaraCondition() {

	}

	private function findLine( $content, $offset, $length ) {
		$string = substr( $content, 0, $offset + $length );

		return count( explode( PHP_EOL, $string ) );
	}
}