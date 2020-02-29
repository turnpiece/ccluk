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

class Content_Yara_Scan extends Behavior {
	const CONTENT_CHECKSUM = 'cleanchecksum', FILES_TRIED = 'filestried';
	/**
	 * @var Scan\Model\Scan
	 */
	protected $model;
	protected $oldChecksum = null;
	protected $tries = null;
	protected $tokenizers = array();
	protected $patterns = array();
	protected $file = null;
	protected $content = null;

	static $code;

	public function processItemInternal( $args, $current ) {
		set_time_limit( - 1 );
		$this->model    = $args['model'];
		$this->file     = $current;
		$this->patterns = $args['patterns'];

		if ( ( $oid = Scan_Api::isIgnored( $current ) ) !== false ) {
			//if this is ignored, we just need to update the parent ID
			$item           = Scan\Model\Result_Item::findByID( $oid );
			$item->parentId = $this->model->id;
			$item->save();

			return true;
		}

		if ( $this->hasPassingThis( $current ) ) {
			//cause we fail in this too many times, by pass this
			return true;
		}

		try {
			$ret = $this->scanAFile( $current );
		} catch ( \Exception $e ) {
			$ret = false;
		}
		$end = microtime( true );

		return $ret;
	}

	private function hasPassingThis( $file ) {
		$cache  = WP_Helper::getCache();
		$cached = $cache->get( self::FILES_TRIED, [] );
		if ( ! isset( $cached[ $file ] ) ) {
			$cached = [
				$file => 1
			];
		} else {
			$cached[ $file ] += 1;
		}
		//save it
		$cache->set( self::FILES_TRIED, $cached );
		if ( $cached[ $file ] > 5 ) {
			return false;
		}

		return false;
	}

	public function scanAFile( $file ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$content    = file_get_contents( $file );
		$scanErrors = [];
		$engine     = new Scan\Behavior\Pro\Scans\Yara_Engine( $content, $this->patterns );
		$scanErrors = $engine->run();
		if ( ! empty( $scanErrors ) ) {
			$filterEngine = new Scan\Behavior\Pro\Scans\Filter_Engine( $content, $scanErrors );
			$scanErrors   = $filterEngine->run();
		}

		if ( empty( $scanErrors ) ) {
			//return to process next file
			return true;
		}
		//create a md5 for this
		$this->record( $file, $scanErrors );

		return true;
	}

	private function record( $file, $scanErrors ) {
		$item           = new Scan\Model\Result_Item();
		$item->type     = 'content';
		$item->raw      = array(
			'file' => $file,
			'meta' => $scanErrors
		);
		$item->parentId = $this->model->id;
		$item->status   = Scan\Model\Result_Item::STATUS_ISSUE;
		$item->save();
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