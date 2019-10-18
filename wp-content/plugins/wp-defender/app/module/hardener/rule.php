<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;
use WP_Defender\Module\Hardener\Model\Settings;

/**
 * Class Rule
 * @package WP_Defender\Module\Hardener
 */
abstract class Rule extends Component {

	/**
	 *
	 * @var string
	 */
	static $slug;

	/**
	 * Return this rule content, we will try to use renderPartial
	 *
	 * @return mixed
     * @deprecated since 2.2
	 */
	abstract function getDescription();

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	abstract function getErrorReason();

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	abstract function getSuccessReason();

	/**
	 * @return array
	 */
	public function getMiscData() {
		return array();
	}

	/**
	 * @return mixed
	 */
	abstract function check();

	/**
	 * implement the revert function
	 *
	 * @return mixed
	 */
	abstract function revert();

	/**
	 * implement the process function
	 * @return mixed
	 */
	abstract function process();

	/**
	 * @return mixed
	 */
	abstract function getTitle();

	/**
	 * @return mixed
	 */
	public function ignore() {
		$setting = Settings::instance();
		$setting->addToIgnore( static::$slug );
	}

	/**
	 *
	 */
	public function restore() {
		$setting = Settings::instance();
		$setting->addToIssues( static::$slug );
	}

	/**
	 * Return Service class
	 * @return mixed
	 */
	abstract function getService();

	/**
	 * generate a nonce field
	 */
	public function createNonceField() {
		wp_nonce_field( self::$slug, '_wdnonce' );
	}

	/**
	 * @return mixed
	 */
	abstract function addHooks();

	/**
	 * @return false|int
	 */
	public function verifyNonce() {
		if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			return false;
		}

		$nonce = HTTP_Helper::retrievePost( '_wdnonce' );

		return wp_verify_nonce( $nonce, self::$slug );
	}

	/**
	 * @return bool
	 */
	public function isIgnored() {
		$ignored = Settings::instance()->ignore;

		return in_array( static::$slug, $ignored );
	}

	/**
	 * @return string
	 */
	public function getCssClass() {
		if ( $this->isIgnored() ) {
			return '';
		}

		if ( $this->check() ) {
			return 'sui-success';
		}

		return 'sui-warning';
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	/**
	 * A helper for tweak with security header, we need to check if the header is out or not
	 *
	 * @param $header
	 * @param $somewhere
	 *
	 * @return bool
	 */
	protected function maybeSubmitHeader( $header, $somewhere ) {
		if ( $somewhere == false ) {
			return true;
		}
		$list  = headers_list();
		$match = false;
		foreach ( $list as $item ) {
			if ( stristr( $item, $header ) ) {
				$match = true;
			}
		}

		return $match;
	}
}