<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro;

use Hammer\Base\Behavior;
use Hammer\Helper\Log_Helper;
use Hammer\Helper\WP_Helper;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Util\Timing;
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
	protected $tokenizers = array();
	protected $patterns = array();
	protected $skipTo = null;
	protected $content = null;

	static $code;

	public function processItemInternal( $args, $current ) {
		set_time_limit( - 1 );
		$start            = microtime( true );
		$this->model      = $args['model'];
		$this->patterns   = $args['patterns'];
		$this->tokenizers = array();
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

		if ( ! class_exists( 'PHP_CodeSniffer\Tokenizers\PHP' ) ) {
			$this->loadDependency();
		}
		if ( ! defined( 'PHP_CODESNIFFER_VERBOSITY' ) ) {
			define( 'PHP_CODESNIFFER_VERBOSITY', 0 );
		}

//		ini_set('xdebug.max_nesting_level',9999);

		$whitelist = array(
			'91a64bb27cea9ce52a34703d740bc261'
		);
		if ( in_array( md5_file( $file ), $whitelist ) ) {
			return true;
		}

		$content = file_get_contents( $file );

		$config    = $this->makeConfig();
		$tokenizer = new \PHP_CodeSniffer\Tokenizers\PHP( $content, $config, PHP_EOL );
		//set position to 0
		$this->content                      = $content;
		$this->tokenizers                   = $tokenizer->getTokens();
		Scan\Component\Token_Utils::$tokens = $this->tokenizers;
		Scan\Component\Token_Utils::$code   = $this->content;
		$scanError                          = array();
		$engines                            = array(
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\Eval_Detector',
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\File_Inclusion_Detector',
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\Code_Injection_Detector',
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\Command_Injection_Detector',
			'WP_Defender\Module\Scan\Behavior\Pro\Scans\Object_Injection_Detector',
			//'WP_Defender\Module\Scan\Behavior\Pro\Scans\Xss_Detector',
		);
		foreach ( $this->tokenizers as $stackPtr => $token ) {
			foreach ( $engines as $engine ) {
				$ret = ( new $engine( $stackPtr, $token ) )->run();
				if ( is_array( $ret ) ) {
					$scanError[] = $ret;
				}
			}
		}

		$scanError = array_filter( $scanError );
		if ( count( $scanError ) ) {
			//filter it out so no duplicate
			foreach ( $scanError as $k => $line ) {
				$start = $line['offset'];
				$end   = $line['offset'] + $line['length'];
				foreach ( $scanError as $j => $l ) {
					//if this child of any, unset it
					if ( $k == $j ) {
						continue;
					}
					if ( $start > $l['offset'] && $end < $l['offset'] + $l['length'] ) {
						unset( $scanError[ $k ] );
						break;
					}
				}
			}

			$item           = new Scan\Model\Result_Item();
			$item->type     = 'content';
			$item->raw      = array(
				'file' => $file,
				'meta' => $scanError
			);
			$item->parentId = $this->model->id;
			$item->status   = Scan\Model\Result_Item::STATUS_ISSUE;
			$item->save();
		}
		$content                            = null;
		$this->tokenizers                   = null;
		Scan\Component\Token_Utils::$tokens = null;
		$this->skipTo                       = null;
		$this->content                      = null;
		unset( $content );

		unset( $this->tokens );

		return true;
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
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => 'WP_Defender\Behavior\Utils'
		);
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
		$config->showSources     = false;
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