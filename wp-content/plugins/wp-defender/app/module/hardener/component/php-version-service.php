<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class PHP_Version_Service extends Rule_Service implements IRule_Service {

	/**
	 * @return bool
	 */
	public function check() {
		$this->queryVersion();
		if ( version_compare( phpversion(), Settings::instance()->min_php_version, '<=' ) ) {
			return false;
		}

		return true;
	}

	public function process() {

	}

	public function revert() {

	}

	public function listen() {

	}

	protected function queryVersion() {
		$infos         = [
			'7.2' => [ '30 Nov 2019', '30 Nov 2020' ],
			'7.3' => [ '6 Dec 2020', '6 Dec 2021' ],
			'7.4' => [ '28 Nov 2021', '28 Nov 2022' ]
		];
		$minVersion    = null;
		$stableVersion = null;
		foreach ( $infos as $php => $dates ) {
			list( $active, $security ) = $dates;
			//get the one still have security updates
			if ( $minVersion == null && strtotime( $active ) < time() && strtotime( $security ) > time() ) {
				$minVersion = $php;
			}
			//if no min available, we pick the current active
			if ( $minVersion == null && strtotime( $active ) > time() ) {
				$minVersion = $php;
			}
			//pick the nearest the min version, we want stable, not features
			if ( $stableVersion == null && $minVersion != null && version_compare( $php, $minVersion, '>' ) ) {
				$stableVersion = $php;
			}
		}

		$settings                     = Settings::instance();
		$settings->stable_php_version = $stableVersion;
		$settings->min_php_version    = $minVersion;
		$settings->save();
	}
}