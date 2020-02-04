<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class DB_Prefix extends Rule {
	static $slug = 'db-prefix';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/db-prefix' );
	}

	function check() {
		return $this->getService()->check();
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
	}

	function getMiscData() {
		$prefix = wp_generate_password( 6, false );

		return [
			'prefix' => 'wp_' . $prefix . '_'
		];
	}

	function revert() {
		if ( Settings::instance()->is_prefix_changed == true ) {
			$ret = $this->getService()->revert();
			if ( ! is_wp_error( $ret ) ) {
				Settings::instance()->addToIssues( self::$slug );
			} else {
				wp_send_json_error( array(
					'message' => $ret->get_error_message()
				) );
			}
		}
	}

	public function getTitle() {
		return __( "Change default database prefix", wp_defender()->domain );
	}

	function process() {
		$dbprefix                       = HTTP_Helper::retrievePost( 'dbprefix' );
		$this->getService()->new_prefix = $dbprefix;
		$ret                            = $this->getService()->process();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		} else {
			//leave the rest to the @Rest.processTweak
		}
	}

	/**
	 * @return DB_Prefix_Service
	 */
	function getService() {
		if ( static::$service == null ) {
			static::$service = new DB_Prefix_Service();
		}

		return static::$service;
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "Your database prefix is the default wp_ prefix.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		global $wpdb;

		return sprintf( __( "Your database prefix is set to <strong>%s</strong> and is unique, %s would be proud.", wp_defender()->domain ), $wpdb->prefix, \WP_Defender\Behavior\Utils::instance()->getDisplayName() );
	}
}