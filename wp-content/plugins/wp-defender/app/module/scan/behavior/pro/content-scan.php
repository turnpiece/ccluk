<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro;

use Hammer\Base\Behavior;
use Hammer\Helper\Log_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Scan;
use WP_Defender\Module\Scan\Component\Scan_Api;

class Content_Scan extends Behavior {
	const CONTENT_CHECKSUM = 'cleanchecksum', FILES_TRIED = 'filestried';
	/**
	 * @var Scan\Model\Scan
	 */
	protected $model;
	protected $oldChecksum = null;
	protected $tries = null;
	protected $tokens = array();
	protected $patterns = array();
	protected $skipTo = null;
	protected $content = null;

	public function processItemInternal( $args, $current ) {
		set_time_limit( - 1 );
		$start          = microtime( true );
		$this->model    = $args['model'];
		$this->patterns = $args['patterns'];
		$this->populateChecksums();
		$this->populateTries();
		if ( ( $oid = Scan_Api::isIgnored( $current ) ) !== false ) {
			//if this is ignored, we just need to update the parent ID
			$item           = Scan\Model\Result_Item::findByID( $oid );
			$item->parentId = $this->model->id;
			$item->save();

			return true;
		}
		//Log_Helper::logger( 'process file ' . $current );
		try {
			//echo 'start ' . \WP_Defender\Behavior\Utils::instance()->makeReadable( memory_get_peak_usage( true ) ) . PHP_EOL;
			$ret = $this->scanAFile( $current );
			//echo 'end ' . \WP_Defender\Behavior\Utils::instance()->makeReadable( memory_get_peak_usage( true ) ) . PHP_EOL;
		} catch ( \Exception $e ) {
			$ret = false;
		}
		$end  = microtime( true );
		$time = round( $end - $start, 2 );

		//Log_Helper::logger( $current . '-' . $time );

		return $ret;
	}

	/**
	 * Check if this file has scanned before and return a good result
	 *
	 * @param $file
	 * @param $checksum
	 *
	 * @return bool
	 */
	private function checksumCheck( $file, &$checksum ) {
		$checksum = md5_file( $file );
		if ( isset( $this->oldChecksum[ $checksum ] ) && $this->oldChecksum[ $checksum ] == $file ) {
			return true;
		}

		return false;
	}

	public function scanAFile( $file ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}

		//need to check if this file is have a version in the md5-scan folder
		$pluginsPath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins';
		$themesPath  = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes';
		if ( ( $isPlugin = strpos( $file, $pluginsPath ) === 0 )
		     || ( $isTheme = strpos( $file, $themesPath ) === 0 )
		) {
			if ( isset( $isPlugin ) && $isPlugin ) {
				$tmp = str_replace( $pluginsPath, '', $file );
			} elseif ( isset( $isTheme ) && $isTheme ) {
				$tmp = str_replace( $themesPath, '', $file );
			}
			if ( isset( $tmp ) ) {
				$ds          = DIRECTORY_SEPARATOR;
				$compareFile = Utils::instance()->getDefUploadDir() . $ds . 'md5-scan' . $tmp;
				if ( file_exists( $compareFile ) ) {
					$checksum            = md5_file( $file );
					$compareFileChecksum = md5_file( $compareFile );
					if ( $compareFileChecksum === $checksum ) {
						$checksum = null;
						unset( $checksum );
						$compareFileChecksum = null;
						unset( $compareFileChecksum );

						return true;
					} else {
						//todo need to check more here as it weird
					}
				}
			}
		}

		$this->tries[] = $file;
		$count         = array_count_values( $this->tries );
		$altCache      = WP_Helper::getArrayCache();
		if ( isset( $count[ $file ] ) && $count[ $file ] > 1 ) {
			//we fail this once, just ignore for now
			return true;
		} else {
			$this->tries[] = $file;
			$this->tries   = array_unique( $this->tries );
			$altCache->set( self::FILES_TRIED, $this->tries );
			//if the file larger than 400kb, we will save immediatly to prevent stuck
			if ( filesize( $file ) >= apply_filters( 'wdScanPreventStuckSize', 30000 ) ) {
				$cache = WP_Helper::getCache();
				$cache->set( Content_Scan::FILES_TRIED, $this->tries );
			}
		}
		if ( ! class_exists( '\WP_Defender\Vendor\PHP_CodeSniffer_Tokenizers_PHP' ) ) {
			$this->loadDependency();
		}
		if ( ! defined( 'PHP_CODESNIFFER_VERBOSITY' ) ) {
			define( 'PHP_CODESNIFFER_VERBOSITY', 0 );
		}
		$tokenizer = new \WP_Defender\Vendor\PHP_CodeSniffer_Tokenizers_PHP();
		$content   = file_get_contents( $file );
		//set position to 0
		$this->content                      = $content;
		$this->tokens                       = \PHP_CodeSniffer_File::tokenizeString( $content, $tokenizer, PHP_EOL, 0, 'iso-8859-1' );
		Scan\Component\Token_Utils::$tokens = $this->tokens;
		$scanError                          = array();
		foreach ( $this->tokens as $i => $token ) {
			if ( $this->skipTo != null && $i < $this->skipTo ) {
				continue;
			}
			$results = array(
				'asserts'        => $this->checkAssert( $i, $token ),
				'crypto'         => $this->checkCrypto( $i, $token ),
				'callback '      => $this->checkCallBackFuncs( $i, $token ),
				'createFunc'     => $this->checkCreateFuncs( $i, $token ),
				//'xss'            => $this->checkXSS( $i, $token ),
				'variableFunc'   => $this->checkVariableFunc( $i, $token ),
				'concatVariable' => $this->checkConcatVariable( $i, $token ),
			);
			/**
			 * todo
			 * we need a function to check variables is suspicous or not
			 * trace the source of variable function
			 */
			//array_push( $scanError, $asserts, $callback, $xss, $crypto, $variableFunc );
			foreach ( $results as $found ) {
				$scanError = array_merge( $scanError, $found );
			}
		}

		$scanError = array_filter( $scanError );
		if ( count( $scanError ) ) {
			$item           = new Scan\Model\Result_Item();
			$item->type     = 'content';
			$item->raw      = array(
				'file' => $file,
				'meta' => $scanError
			);
			$item->parentId = $this->model->id;
			$item->status   = Scan\Model\Result_Item::STATUS_ISSUE;
			$item->save();
		} else {
			//$altCache->set( self::CONTENT_CHECKSUM, $this->oldChecksum );
		}
		$content                            = null;
		$this->tokens                       = null;
		Scan\Component\Token_Utils::$tokens = null;
		$this->skipTo                       = null;
		$this->content                      = null;
		unset( $content );

		//unset( $this->tokens );

		return true;
	}

	private function checkConcatVariable( $i, $token ) {
		$res = array();
		if ( $token['code'] == T_OPEN_SQUARE_BRACKET ) {
			//get the closer
			$closer = $token['bracket_closer'];
			//usually inside only have 1 token, if it take more then we need to check
			if ( $closer - $i <= 3 ) {
				return $res;
			}
			//need to take a look
			$params   = Scan\Component\Token_Utils::findParams( $i, $closer );
			$pAlazyer = $this->analyzeParams( $params );
			if ( $pAlazyer['concat'] > 4 ) {
				$content = Scan\Component\Token_Utils::getTokensAsString( $i - 1, $closer - $i + 1 );
				$offset  = $this->getCodeOffset( $content );
				$res[]   = array(
					'type'   => 'concat',
					'text'   => __( "Suspicous concat", wp_defender()->domain ),
					//'content' => addslashes( $token['content'] . $content ),
					'offset' => $offset,
					'length' => strlen( $content ),
					'level'  => 'info'
				);
			}
			$pAnalyze = null;
			unset( $pAnalyze );
		}

		//prevent false positive
		if ( count( $res ) > 2 ) {
			return $res;
		}

		return array();
	}

	private function checkCreateFuncs( $i, $token ) {
		$res = array();
		if ( in_array( $token['content'], Scan\Component\Token_Utils::getCreateFuncs() ) ) {
			//we found a callback situation
			$opener  = Scan\Component\Token_Utils::findNext( T_OPEN_PARENTHESIS, $i, $i + 5 );
			$content = Scan\Component\Token_Utils::getTokensAsString( $opener, $this->tokens[ $opener ]['parenthesis_closer'] - $opener + 1 );
			$content = $token['content'] . $content;
			$offset  = $this->getCodeOffset( $content );
			$params  = Scan\Component\Token_Utils::findParams( $opener + 1, $this->tokens[ $opener ]['parenthesis_closer'] - 1 );
			//check if the params is suspicious
			$pAnalyze = $this->analyzeParams( $params );
			if ( $pAnalyze['longStrings'] && $pAnalyze['crypto'] ) {
				$res[] = array(
					'type'   => 'createFunc',
					'text'   => __( "Create function function " . $token['content'] . ' detected', wp_defender()->domain ),
					//'content' => addslashes( $token['content'] . $content ),
					'offset' => $offset,
					'length' => strlen( $content ),
					'level'  => 'info'
				);
			}
			$pAnalyze = null;
			unset( $pAnalyze );
		}

		return $res;
	}

	/**
	 * @param $i
	 * @param $token
	 *
	 * @return array
	 */
	private function checkVariableFunc( $i, $token ) {
		$res = array();
		if ( $token['code'] == T_VARIABLE ) {
			$next = isset( $this->tokens[ $i + 1 ] ) ? $this->tokens[ $i + 1 ] : null;
			if ( is_null( $next ) ) {
				return array();
			}

			if ( $next['code'] != T_OPEN_PARENTHESIS ) {
				return array();
			}
			//next of the variable is a (, we need to check what is the funciton of this variable
			for ( $index = $i - 1; $index > 0; $index -- ) {
				if ( $this->tokens[ $index ]['code'] == T_VARIABLE
				     && $this->tokens[ $index ]['content'] == '$a' ) {
					//first get the equal
					//todo skip the whitespace
					$equal = Scan\Component\Token_Utils::findNext( T_EQUAL, $index + 1, $index + 5 );
					if ( $equal ) {
						//found the equal, next find the function
						$function = Scan\Component\Token_Utils::findNext( T_STRING, $equal + 1, $equal + 6 );
						if ( $function && in_array( $this->tokens[ $function ]['content'],
								array_merge( Scan\Component\Token_Utils::getCryptoFunctions(), array(
									'eval',
									'assert',
									'strrev',
									'mb_strrev'
								) ) ) ) {
							$content = $token['content'] . Scan\Component\Token_Utils::getTokensAsString( $i + 1, $next['parenthesis_closer'] + 1 - $i );
							$offset  = $this->getCodeOffset( $content );
							$res[]   = array(
								'type'   => 'variable function',
								'text'   => __( "Suspicious variable function call", wp_defender()->domain ),
								//'content' => addslashes( $token['content'] . Scan\Component\Token_Utils::getTokensAsString( $i + 1, $closer + 1 - $i ) ),
								'offset' => $offset,
								'length' => strlen( $content ),
								'level'  => 'warning'
							);
						}
					}
				}
			}
		}

		return $res;
	}

	/**
	 * @param $i
	 * @param $token
	 *
	 * @return array|bool
	 */
	private function checkXSS( $i, $token ) {
		$res = array();
		if ( in_array( $token['code'], array( T_ECHO, T_PRINT, T_EXIT, T_OPEN_TAG_WITH_ECHO ) ) ) {
			//check the params inside those function
			//find the closer
			//find the next tag
			$next = $this->tokens[ $i + 1 ];

			if ( $token['code'] == T_OPEN_TAG_WITH_ECHO ) {
				//this start with <?=
				$closer = Scan\Component\Token_Utils::findNext( T_CLOSE_TAG, $i );
			} elseif ( $next['code'] == T_OPEN_PARENTHESIS ) {
				$closer = $next['parenthesis_closer'];
			} else {
				//just find next semicolon
				$closer = Scan\Component\Token_Utils::findNext( array(
					T_SEMICOLON,
					//paranoid mode
					T_CLOSE_CURLY_BRACKET,
					//
					T_CLOSE_TAG
				), $i + 1 );
			}

			if ( ! $closer ) {
				return false;
			}
			//next we need to find all the params inside
			$params = Scan\Component\Token_Utils::findParams( $i + 2, $closer );
			//todo we will need a function to check if those param having issue
			$isUserInput = false;
			foreach ( $params as $param ) {
				if ( Scan\Component\Token_Utils::isUserInput( $param ) ) {
					$isUserInput = true;
					break;
				}
			}
			$content = $token['content'] . Scan\Component\Token_Utils::getTokensAsString( $i + 1, $closer + 1 - $i );
			$offset  = $this->getCodeOffset( $content );
			if ( $isUserInput ) {
				$res[] = array(
					'type'   => 'xss',
					'text'   => __( "Possible XSS detected", wp_defender()->domain ),
					//'content' => addslashes( $token['content'] . Scan\Component\Token_Utils::getTokensAsString( $i + 1, $closer + 1 - $i ) ),
					'offset' => $offset,
					'length' => strlen( $content ),
					'level'  => 'warning'
				);
			}
		}

		return $res;
	}

	private function checkCrypto( $index, $token ) {
		$res = array();
		if ( $token['code'] == T_STRING && in_array( $token['content'], Scan\Component\Token_Utils::getCryptoFunctions() ) ) {
			$opener = Scan\Component\Token_Utils::findNext( T_OPEN_PARENTHESIS, $index, $index + 5 );
			if ( $opener == null ) {
				return array();
			}
			$content = Scan\Component\Token_Utils::getTokensAsString( $opener, $this->tokens[ $opener ]['parenthesis_closer'] - $opener + 1 );
			$params  = Scan\Component\Token_Utils::findParams( $opener + 1, $this->tokens[ $opener ]['parenthesis_closer'] - 1 );
			$content = $token['content'] . $content;
			$offset  = $this->getCodeOffset( $content );
			//check if the params is suspicious
			$pAnalyze = $this->analyzeParams( $params );
			if ( $pAnalyze['crypto'] && (
					$pAnalyze['longStrings'] >= 1 ||
					$pAnalyze['concat'] >= 7
				) ) {
				//skip to the closer
				$this->skipTo = $this->tokens[ $opener ]['parenthesis_closer'] + 1;

				$res[] = array(
					'type'   => 'crypto',
					'text'   => __( "Crypto function " . $token['content'] . ' detected, with some suspicious parameters', wp_defender()->domain ),
					//'content' => addslashes( $token['content'] . $content ),
					'offset' => $offset,
					'length' => strlen( $content ),
					'level'  => 'info'
				);
			}
			$pAnalyze = null;
			unset( $pAnalyze );
		}

		return $res;
	}

	/**
	 * Collect info when assert or eval using
	 *
	 * @param $index
	 * @param $token
	 *
	 * @return array|bool
	 */
	private function checkAssert( $index, $token ) {
		$res = array();
		if ( $token['content'] == 'assert' || $token['content'] == 'eval' ) {
			$opener  = Scan\Component\Token_Utils::findNext( T_OPEN_PARENTHESIS, $index, $index + 5 );
			$content = Scan\Component\Token_Utils::getTokensAsString( $opener, $this->tokens[ $opener ]['parenthesis_closer'] - $opener + 1 );
			$content = $token['content'] . $content;
			$offset  = $this->getCodeOffset( $content );
			$params  = Scan\Component\Token_Utils::findParams( $opener + 1, $this->tokens[ $opener ]['parenthesis_closer'] - 1 );
			//check if the params is suspicious
			$pAnalyze = $this->analyzeParams( $params );
			if (
				$pAnalyze['crypto'] > 1 ||
				$pAnalyze['longStrings'] > 1
				//$pAnalyze['concat'] > 5
			) {
				//skip to the closer
				$this->skipTo = $this->tokens[ $opener ]['parenthesis_closer'] + 1;
				$res[]        = array(
					'type'   => 'eval',
					'text'   => __( "Eval function found, with suspicious parameters.", wp_defender()->domain ),
					//'content' => addslashes( $token['content'] . $content ),
					'offset' => $offset,
					'length' => strlen( $content ),
					'level'  => 'info'
				);

			}
			$pAnalyze = null;
			unset( $pAnalyze );
		}

		return $res;
	}

	/**
	 * @param $index
	 * @param $token
	 *
	 * @return array|\bool
	 */
	public function checkCallBackFuncs( $index, $token ) {
		$res = array();
		if ( in_array( $token['content'], Scan\Component\Token_Utils::getCallbackFunctions() ) ) {
			//we found a callback situation
			$opener  = Scan\Component\Token_Utils::findNext( T_OPEN_PARENTHESIS, $index, $index + 5 );
			$content = Scan\Component\Token_Utils::getTokensAsString( $opener, $this->tokens[ $opener ]['parenthesis_closer'] - $opener + 1 );
			$content = $token['content'] . $content;
			$offset  = $this->getCodeOffset( $content );
			$params  = Scan\Component\Token_Utils::findParams( $opener + 1, $this->tokens[ $opener ]['parenthesis_closer'] - 1 );
			//check if the params is suspicious
			$pAnalyze = $this->analyzeParams( $params );
			if ( $pAnalyze['longStrings'] && $pAnalyze['crypto'] ) {
				$res[] = array(
					'type'   => 'callback',
					'text'   => __( "Callback function " . $token['content'] . ' detected', wp_defender()->domain ),
					//'content' => addslashes( $token['content'] . $content ),
					'offset' => $offset,
					'length' => strlen( $content ),
					'level'  => 'info'
				);
			}
			$pAnalyze = null;
			unset( $pAnalyze );
		}

		return $res;
	}

	/**
	 *
	 * @param $content
	 *
	 * @return bool|int
	 */
	private function getCodeOffset( $content ) {
		return strpos( $this->content, $content );
	}

	public function _scan_a_file( $file ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}
		if ( $this->checksumCheck( $file, $checksum ) ) {
			//this one is good and still same, no need to do
			return true;
		}

		//this file has changed, unset the old one
		unset( $this->oldChecksum[ $checksum ] );
		$this->tries[] = $file;
		$count         = array_count_values( $this->tries );
		$altCache      = WP_Helper::getArrayCache();
		if ( isset( $count[ $file ] ) && $count[ $file ] > 1 ) {
			//we fail this once, just ignore for now
			return true;
		} else {
			$this->tries[] = $file;
			$this->tries   = array_unique( $this->tries );
			$altCache->set( self::FILES_TRIED, $this->tries );
			//if the file larger than 400kb, we will save immediatly to prevent stuck
			if ( filesize( $file ) >= apply_filters( 'wdScanPreventStuckSize', 400000 ) ) {
				$cache = WP_Helper::getCache();
				$cache->set( Content_Scan::FILES_TRIED, $this->tries );
			}
		}

		if ( ! class_exists( '\WP_Defender\Vendor\PHP_CodeSniffer_Tokenizers_PHP' ) ) {
			$this->loadDependency();
		}
		if ( ! defined( 'PHP_CODESNIFFER_VERBOSITY' ) ) {
			define( 'PHP_CODESNIFFER_VERBOSITY', 0 );
		}
		$tokenizer         = new \WP_Defender\Vendor\PHP_CodeSniffer_Tokenizers_PHP();
		$content           = file_get_contents( $file );
		$tokens            = \PHP_CodeSniffer_File::tokenizeString( $content, $tokenizer, PHP_EOL, 0, 'iso-8859-1' );
		$this->tokens      = $tokens;
		$scanError         = array();
		$ignoreTo          = false;
		$badFuncPattern    = $this->getFunctionScanPattern();
		$base64textPattern = $this->getBase64ScanPattern();
		//fallback
		$error1    = array();
		$error2    = array();
		$ignoreTo1 = false;
		$ignoreTo2 = false;
		//Log_Helper::logger( var_export( $tokens, true ) );
		for ( $i = 0; $i < count( $tokens ) - 1; $i ++ ) {
			if ( $ignoreTo !== false && $i <= $ignoreTo ) {
				continue;
			}
			//do stuff here
			if ( ! empty( $badFuncPattern ) && ! empty( $base64textPattern ) ) {
				list( $error1, $ignoreTo1 ) = $this->detectBadFunc( $i, $tokens[ $i ], $badFuncPattern, $base64textPattern );
			}
			list( $error2, $ignoreTo2 ) = $this->detectComplexConcat( $i, $tokens[ $i ] );

			$scanError = array_merge( $scanError, $error1 );
			$scanError = array_merge( $scanError, $error2 );
			$ignoreTo  = max( $ignoreTo1, $ignoreTo2 );
		}

		$scanError = array_filter( $scanError );

		if ( count( $scanError ) ) {
			$item           = new Scan\Model\Result_Item();
			$item->type     = 'content';
			$item->raw      = array(
				'file' => $file,
				'meta' => array_merge( $scanError )
			);
			$item->parentId = $this->model->id;
			$item->status   = Scan\Model\Result_Item::STATUS_ISSUE;
			$item->save();
		} else {
			//store the checksum for later use
			$this->oldChecksum[ $checksum ] = $file;
			$altCache->set( self::CONTENT_CHECKSUM, $this->oldChecksum );
		}
		$content      = null;
		$this->tokens = null;
		unset( $tokens );
		unset( $content );

		return true;
	}

	/**
	 * This will get the parameters from function and check
	 *
	 * @param $tokens
	 * @param $scenario
	 *
	 * @return array
	 */
	private function analyzeParams( $tokens ) {
		$arr         = new \ArrayObject( $tokens );
		$it          = $arr->getIterator();
		$crypto      = 0;
		$longStrings = 0;
		$concat      = 0;
		while ( $it->valid() ) {
			$curr = $it->current();
			switch ( $curr['code'] ) {
				case T_STRING:
					$func = $curr['content'];
					if ( preg_match( $this->getFunctionScanPattern(), $func ) ) {
						$crypto ++;
					}
//					if ( in_array( $curr['content'], Scan\Component\Token_Utils::getsuspiciousFunctions() ) ) {
//						$crypto ++;
//					}
					break;
				case T_STRING_CONCAT:
					$concat ++;
					break;
				case T_CONSTANT_ENCAPSED_STRING:
					if ( strlen( $curr['content'] ) > 100 || ( isset( $tokens[ $it->key() + 1 ] ) && $tokens[ $it->key() + 1 ]['code'] == T_CONSTANT_ENCAPSED_STRING ) ) {
						//larger than 100 chars, just add
						$longStrings ++;
					}
					break;
			}
			$it->next();
		}

		return array(
			'longStrings' => $longStrings,
			'crypto'      => $crypto,
			'concat'      => $concat
		);
	}

	/**
	 * @param $index
	 * @param $token
	 * @param $badFuncPattern
	 * @param $base64textPattern
	 *
	 * @return array
	 */
	private function detectBadFunc( $index, $token, $badFuncPattern, $base64textPattern ) {
		$extendFuncs = array(
			'str_rot13'
		);
		$ignoreTo    = false;
		$errorFound  = array();

		if ( empty( $badFuncPattern ) || empty( $base64textPattern ) ) {
			//should never happen, just a fall back for safe
			return array();
		}
		if ( in_array( $token['code'], array( T_EVAL, T_STRING ) )
		     && ( preg_match( $badFuncPattern, $token['content'] ) || in_array( $token['content'], $extendFuncs ) )
		) {
			//let's find the open and close of this parent function, in next 5 tokens
			$opener = $this->findNext( T_OPEN_PARENTHESIS, $index, $index + 5 );
			if ( $opener !== false && isset( $this->tokens[ $opener ]['parenthesis_closer'] ) ) {
				$funcsFound = array(
					$token['content']
				);
				$textFound  = array();
				//found one, need to parse the content to analyze the behavior of this chain of func
				$closer = $this->tokens[ $opener ]['parenthesis_closer'];
				//loop through all the inner
				for ( $i = $opener + 1; $i <= $closer; $i ++ ) {
					$lToken = $this->tokens[ $i ];
					switch ( $lToken['code'] ) {
						case T_CONSTANT_ENCAPSED_STRING:
							//Log_Helper::logger( var_export( $lToken, true ) );
							if ( preg_match( $base64textPattern, $lToken['content'] ) ) {
								$textFound[] = $lToken['content'];
							} elseif ( strlen( $lToken['content'] ) > 200 ) {
								//text too long
								$textFound[] = $lToken['content'];
							} else {
								//this case when the string is very long and separate by new line, need to combind the string,
								//the string is inside a nested function
								$pre  = isset( $this->tokens[ $i - 1 ] ) ? $this->tokens[ $i - 1 ] : null;
								$next = isset( $this->tokens[ $i + 1 ] ) ? $this->tokens[ $i + 1 ] : null;
								if ( $pre != null && $pre['code'] == T_OPEN_PARENTHESIS &&
								     $next != null && $next['code'] == T_CONSTANT_ENCAPSED_STRING
								     && isset( $lToken['nested_parenthesis'][ $i - 1 ] )
								) {
									//gotcha
									$string = $this->getTokensAsString( $i, $lToken['nested_parenthesis'][ $i - 1 ] - $i );
									if ( strlen( $string ) > 500 ) {
										$textFound[] = $string;
										//put the i to the end
										$i = $lToken['nested_parenthesis'][ $i - 1 ];
									}
								}
							}
							break;
						case T_STRING:
						case T_EVAL:
							if ( preg_match( $badFuncPattern, $lToken['content'] ) || in_array( $lToken['content'], $extendFuncs ) ) {
								$funcsFound[] = $lToken['content'];
							}
							break;
					}
				}
				$ignoreTo = $closer;

				if ( count( $funcsFound ) > 1 && ( count( $textFound ) || in_array( 'eval', $funcsFound ) ) ) {
					$errorFound[] = array(
						'lineFrom'   => $this->tokens[ $index ]['line'],
						'lineTo'     => $this->tokens[ $closer ]['line'],
						'columnFrom' => $this->tokens[ $index ]['column'],
						'columnTo'   => $this->tokens[ $closer ]['column'],
						//'code'       => $this->getTokensAsString( $opener, $closer - $opener )
					);
				}
			}
		}

		return array( $errorFound, $ignoreTo );
	}

	private function detectComplexConcat( $index, $token ) {
		$ignoreTo   = false;
		$errorFound = array();
		if ( in_array( $token['code'], array(
			T_VARIABLE,
		) ) ) {
			$opener = $this->findNext( T_OPEN_SQUARE_BRACKET, $index + 1, $index + 5 );
			if ( $opener !== false && isset( $this->tokens[ $opener ]['bracket_closer'] ) ) {
				$hasConcat = 0;
				$found     = 0;
				$closer    = $this->tokens[ $opener ]['bracket_closer'];
				for ( $line = $opener + 1; $line < $closer - 1; $line ++ ) {
					if ( in_array( $this->tokens[ $line ]['code'], array(
						T_STRING_CONCAT,
						T_VARIABLE,
						T_OPEN_SQUARE_BRACKET,
					) ) ) {
						if ( $this->tokens[ $line ]['code'] == T_STRING_CONCAT ) {
							//nested string or variable concat inside a varable
							$hasConcat ++;
						} else {
							//nested variable inside variable
							$found ++;
						}
					}
				}
				if ( $found > 5 && $hasConcat > 5 ) {
					$errorFound[] = array(
						'lineFrom'   => $this->tokens[ $index ]['line'],
						'lineTo'     => $this->tokens[ $closer ]['line'],
						'columnFrom' => $this->tokens[ $index ]['column'],
						'columnTo'   => $this->tokens[ $closer ]['column'],
					);
				}
				$ignoreTo = $closer;
			} else {
				$ignoreTo = $index + 5;
			}
		}

		return array( $errorFound, $ignoreTo );
	}

	/**
	 * this is for record fail files, to prevent block
	 */
	private function populateTries() {
		if ( $this->tries === null ) {
			//this is null, look at runtime cache
			$altCache = WP_Helper::getArrayCache();
			$tries    = $altCache->get( self::FILES_TRIED, null );
			if ( $tries === null ) {
				//has not init yet, check in db
				$cache = WP_Helper::getCache();
				//array as default so this never here again
				$tries       = $cache->get( self::FILES_TRIED, array() );
				$this->tries = $tries;
				$altCache->set( self::FILES_TRIED, $tries );
			} else {
				$this->tries = $tries;
			}
		}
	}

	/**
	 * Populate old checksum from DB
	 */
	private function populateChecksums() {
		if ( $this->oldChecksum === null ) {
			//this is null, look at runtime cache
			$altCache    = WP_Helper::getArrayCache();
			$oldChecksum = $altCache->get( self::CONTENT_CHECKSUM, null );
			if ( $oldChecksum === null ) {
				//has not init yet, check in db
				$cache = WP_Helper::getCache();
				//array as default so this never here again
				$oldChecksum       = $cache->get( self::CONTENT_CHECKSUM, array() );
				$this->oldChecksum = $oldChecksum;
				$altCache->set( self::CONTENT_CHECKSUM, $oldChecksum );
			} else {
				$this->oldChecksum = $oldChecksum;
			}
		}
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	private function getPatterns( $key ) {
		$pattern = $this->patterns;

		return isset( $pattern[ $key ] ) ? $pattern[ $key ] : false;
	}

	private function getFunctionScanPattern() {
		$pattern = $this->getPatterns( 'suspicious_function_pattern' );

		return $pattern;
	}

	private function getBase64ScanPattern() {
		$pattern = $this->getPatterns( 'base64_encode_pattern' );

		return $pattern;
	}

	private function loadDependency() {
		$ds         = DIRECTORY_SEPARATOR;
		$vendorPath = wp_defender()->getPluginPath() . 'vendor' . $ds . 'php_codesniffer' . $ds . 'CodeSniffer';
		if ( ! class_exists( 'PHP_CodeSniffer_Exception' ) ) {
			require_once $vendorPath . $ds . 'Exception.php';
		}
		if ( ! class_exists( 'PHP_CodeSniffer_Tokens' ) ) {
			require_once $vendorPath . $ds . 'Tokens.php';
		}
		if ( ! class_exists( 'PHP_CodeSniffer_File' ) ) {
			require_once $vendorPath . $ds . 'File.php';
		}
		if ( ! class_exists( 'PHP_CodeSniffer_Tokenizers_Comment' ) ) {
			require_once $vendorPath . $ds . 'Tokenizers' . $ds . 'Comment.php';
		}
		if ( ! class_exists( 'WP_Defender\Vendor\PHP_CodeSniffer_Tokenizers_PHP' ) ) {
			require_once $vendorPath . $ds . 'Tokenizers' . $ds . 'PHP.php';
		}
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => 'WP_Defender\Behavior\Utils'
		);
	}
}