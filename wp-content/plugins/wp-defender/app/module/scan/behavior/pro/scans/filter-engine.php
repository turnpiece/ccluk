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
class Filter_Engine {
	protected $scanErrors;
	protected $content;

	public function __construct( $content, $scanErrors ) {
		$this->scanErrors = $scanErrors;
		$this->content    = $content;
	}

	public function run() {
		$lookInto  = $this->getLookInto();
		$willCheck = false;
		foreach ( $this->scanErrors as $error ) {
			if ( isset( $lookInto[ $error['id'] ] ) ) {
				$willCheck                = true;
				$lookInto[ $error['id'] ] = true;
			}
		}
		if ( $willCheck == false ) {
			//nothing to do
			return $this->scanErrors;
		}

		$this->initTokens();

		/*
		 * We will filter roughly first to see if the error is in a php block, if not, just unset it
		 * For eval, we will have to check if the file contain suspicious stuff like string, base64_... etc
		 *
		 */
		$this->filterPHPBlock();
		if ( empty( $this->scanErrors ) ) {
			return $this->scanErrors;
		}

		foreach ( $lookInto as $id => $should ) {
			if ( ! $should ) {
				continue;
			}
			$type = str_replace( '$', '', $id );
			$func = 'filter' . $type;
			if ( method_exists( $this, $func ) ) {
				$this->$func();
			}
		}

		return $this->scanErrors;
	}

	/**
	 * Check if the line is actual php, if not, unset it
	 */
	protected function filterPHPBlock() {
		$lookInto = $this->getLookInto();
		foreach ( $this->scanErrors as $id => $error ) {
			if ( ! isset( $lookInto[ $error['id'] ] ) ) {
				continue;
			}
			foreach ( $this->getTokensByLine( $error['line'] ) as $token ) {
				$needle   = strlen( $token['content'] ) > strlen( $error['code'] ) ? $error['code'] : $token['content'];
				$haystack = strlen( $token['content'] ) > strlen( $error['code'] ) ? $token['content'] : $error['code'];
				if ( strpos( $haystack, $needle ) !== false ) {
					//have to check if the current is in php block, if not, remove the error
					if ( in_array( $token['code'], [ T_INLINE_HTML, T_COMMENT, T_DOC_COMMENT_STRING ] ) ) {
						unset( $this->scanErrors[ $id ] );
						break;
					}
				}
			}
		}
	}

	/**
	 * Some ids prone to get a lot positive, we will need to heuristic check
	 * @return array
	 */
	private function getLookInto() {
		$lookInto = [ '$eval' => false, '$var_as_func' => false, '$execution' => false ];

		return $lookInto;
	}

	/**
	 * Filter the execution
	 */
	protected function filterexecution() {
		//echo 'Looking filter execution' . PHP_EOL;
		/**
		 * Usually we need to check if the caller wrapped by sanitize function
		 */
		$engines   = [
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\Code_Injection_Detector',
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\Command_Injection_Detector'
		];
		$scanError = $this->doInternalScan( $engines );
		if ( empty( $scanError ) ) {
			//eval should come with at least 1
			foreach ( $this->scanErrors as $key => $val ) {
				if ( $val['id'] == '$execution' ) {
					unset( $this->scanErrors[ $key ] );
				}
			}
		}
	}

	/**
	 * Validate if the eval is right
	 */
	protected function filtereval() {
		//echo 'Looking filter eval' . PHP_EOL;
		$engines   = [
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\String_Encrypt_Detector'
		];
		$scanError = $this->doInternalScan( $engines );
		if ( empty( $scanError ) ) {
			//eval should come with at least 1
			foreach ( $this->scanErrors as $key => $val ) {
				if ( $val['id'] == '$eval' ) {
					unset( $this->scanErrors[ $key ] );
				}
			}
		}
	}

	protected function filtervar_as_func() {

	}

	private function doInternalScan( $engines ) {
		$scanError = [];
		foreach ( Token_Utils::$tokens as $stackPtr => $token ) {
			foreach ( $engines as $engine ) {
				$ret = ( new $engine( $stackPtr, $token ) )->run();
				if ( is_array( $ret ) ) {
					$scanError[] = $ret;
				}
			}
		}

		return $scanError;
	}

	/**
	 * @param $line
	 *
	 * @return \Generator
	 */
	public function getTokensByLine( $line ) {
		foreach ( Token_Utils::$tokens as $key => $token ) {
			if ( $token['line'] == $line ) {
				$token['position'] = $key;
				yield $token;
			}
		}
	}

	private function initTokens() {
		if ( ! class_exists( 'PHP_CodeSniffer\Tokenizers\PHP' ) ) {
			$this->loadDependency();
		}
		if ( ! defined( 'PHP_CODESNIFFER_VERBOSITY' ) ) {
			define( 'PHP_CODESNIFFER_VERBOSITY', 0 );
		}
		$config    = $this->makeConfig();
		$tokenizer = new \PHP_CodeSniffer\Tokenizers\PHP( $this->content, $config, PHP_EOL );

		Token_Utils::$tokens = $tokenizer->getTokens();
	}

	private function loadDependency() {
		$ds         = DIRECTORY_SEPARATOR;
		$vendorPath = wp_defender()->getPluginPath() . 'vendor' . $ds . 'php_codesniffer-3.4.0' . $ds . 'src';

		if ( ! class_exists( 'PHP_CodeSniffer\Tokenizers\Tokenizer' ) ) {
			require_once $vendorPath . $ds . 'Tokenizers' . $ds . 'Tokenizer.php';
		}
		if ( ! class_exists( 'PHP_CodeSniffer\Tokenizers\PHP' ) ) {
			require_once $vendorPath . $ds . 'Tokenizers' . $ds . 'PHP.php';
		}

		if ( ! class_exists( 'PHP_CodeSniffer\Tokenizers\Comment' ) ) {
			require_once $vendorPath . $ds . 'Tokenizers' . $ds . 'Comment.php';
		}

		if ( ! class_exists( 'PHP_CodeSniffer\Util\Tokens' ) ) {
			require_once $vendorPath . $ds . 'Util' . $ds . 'Tokens.php';
		}

		if ( ! class_exists( 'PHP_CodeSniffer\Util\Standards' ) ) {
			require_once $vendorPath . $ds . 'Util' . $ds . 'Standards.php';
		}

		if ( ! class_exists( 'PHP_CodeSniffer\Exceptions\RuntimeException' ) ) {
			require_once $vendorPath . $ds . 'Exceptions' . $ds . 'RuntimeException.php';
		}
	}

	/**
	 * @return \stdClass
	 */
	private function makeConfig() {
		$config                  = new \stdClass();
		$config->files           = [];
		$config->standards       = [ 'PEAR' ];
		$config->verbosity       = 0;
		$config->interactive     = false;
		$config->cache           = false;
		$config->cacheFile       = null;
		$config->colors          = false;
		$config->explain         = false;
		$config->local           = false;
		$config->showSources     = true;
		$config->showProgress    = false;
		$config->quiet           = false;
		$config->annotations     = true;
		$config->parallel        = 1;
		$config->tabWidth        = 0;
		$config->encoding        = 'utf-8';
		$config->extensions      = [
			'php' => 'PHP',
			'inc' => 'PHP',
			'js'  => 'JS',
			'css' => 'CSS',
		];
		$config->sniffs          = [];
		$config->exclude         = [];
		$config->ignored         = [];
		$config->reportFile      = null;
		$config->generator       = null;
		$config->filter          = null;
		$config->bootstrap       = [];
		$config->basepath        = null;
		$config->reports         = [ 'full' => null ];
		$config->reportWidth     = 'auto';
		$config->errorSeverity   = 5;
		$config->warningSeverity = 5;
		$config->recordErrors    = true;
		$config->suffix          = '';
		$config->stdin           = false;
		$config->stdinContent    = null;
		$config->stdinPath       = null;
		$config->unknown         = [];

		return $config;
	}
}