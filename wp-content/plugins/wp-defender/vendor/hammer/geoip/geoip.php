<?php
/**
 * Author: Hoang Ngo
 */

namespace Hammer\GeoIP;

use Hammer\WP\Component;
use MaxMind\Db\Reader;

class GeoIp extends Component {
	/**
	 * @var \GeoIp2\Database\Reader
	 */
	protected $provider;

	public function __construct( $dbPath, $type = 'maxmind' ) {
		$this->provider = new Reader( $dbPath );
	}

	/**
	 * @param $ip
	 *
	 * @return array|bool
	 * @throws Reader\InvalidDatabaseException
	 */
	public function ipToCountry( $ip ) {
		$info = $this->provider->get( $ip );
		if ( ! is_array( $info ) ) {
			return false;
		}

		$country = array(
			'iso'  => $info['country']['iso_code'],
			'name' => $info['country']['names']['en']
		);

		return $country;
	}

}