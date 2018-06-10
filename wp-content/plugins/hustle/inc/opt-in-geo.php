<?php

/**
 * Geolocation utility functions
 *
 * Most of the methods are courtesy Philipp Stracker
 *
 * Class Opt_In_Geo
 */
class Opt_In_Geo
{
	/**
	 * Site option key
	 *
	 * @var COUNTRY_IP_MAP
	 */
	const COUNTRY_IP_MAP = "wpoi-county-id-map";

	/**
	 * Tries to get the public IP address of the current user.
	 *
	 * @return string The IP Address
	 */
	public function get_user_ip() {
		static $ip_address = null;

		if ( null === $ip_address ) {
			$ip_addr = lib3()->net->current_ip()->ip;
			if ( $ip_addr ) {
				$ip_address = $ip_addr;
			} else {
				$ip_address = 'UNKNOWN';
			}
		}

		return $ip_address;
	}

	/**
	 * Checks if the users IP address belongs to a certain country.
	 *
	 * @return bool
	 */
	public function get_user_country() {
		// Grab the users IP address
		$ip = $this->get_user_ip();

		// See if an add-on provides the country for us.
		$country = apply_filters( 'wpoi-get-user-country', "", $ip );

		if ( empty( $country ) ) {
			$country = $this->get_country_from_ip( $ip );
		}

		if ( empty( $country ) ) {
			$country = 'XX';
		}

		return $country;
	}

	/**
	 * Returns a list of available ip-resolution services.
	 *
	 * @return array List of available webservices.
	 */
	private function _get_geo_services() {
		static $Geo_service = null;
		if ( null === $Geo_service ) {
			$Geo_service = array();

			$Geo_service['hostip'] = (object) array(
				'label' => 'Host IP',
				'url'   => 'http://api.hostip.info/country.php?ip=%ip%',
				'type'  => 'text',
			);

			$Geo_service['telize'] = (object) array(
				'label' => 'Telize',
				'url'   => 'http://www.telize.com/geoip/%ip%',
				'type'  => 'json',
				'field' => 'country_code',
			);

			$Geo_service['freegeo'] = (object) array(
				'label' => 'Free Geo IP',
				'url'   => 'http://freegeoip.net/json/%ip%',
				'type'  => 'json',
				'field' => 'country_code',
			);

			/**
			 * Allow other modules/plugins to register a geo service.
			 */
			$Geo_service = apply_filters( 'wpoi-geo-services', $Geo_service );
		}

		return $Geo_service;
	}


	/**
	 * Returns the lookup-service details
	 *
	 * @return object Service object for geo lookup
	 */
	private function _get_service($type = null ) {
		$service = false;

		if ( null === $type ) {

			$remote_ip_url = apply_filters("wpoi-remote-ip-url", "");
			if (  !empty( $remote_ip_url )  ) {
				$type = '';
			} else {
				$type = 'freegeo';
			}
		}

		if ( '' == $type ) {
			$service = (object) array(
				'url' => $remote_ip_url,
				'label' => 'wp-config.php',
				'type' => 'text',
			);
		} else if ( 'geo_db' === $type ) {
			$service = (object) array(
				'url' => 'db',
				'label' => __( 'Local IP Lookup Table', Opt_In::TEXT_DOMAIN ),
				'type' => 'text',
			);
		} else {
			$geo_service = $this->_get_geo_services();

			$service = @$geo_service[ $type ];
		}

		return $service;
	}

	/**
	 * Queries an external geo-API to find the country of the specified IP.
	 *
	 * @param  string $ip The IP Address.
	 * @param  object $service Lookup-Service details.
	 * @return string The country code.
	 */
	private function _country_from_api($ip, $service ) {
		$country = false;

		if ( is_object( $service ) && ! empty( $service->url ) ) {
			$url = str_replace( '%ip%', $ip, $service->url );
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response )
				&& '200' == $response['response']['code']
				&& 'XX' != $response['body']
			) {
				if ( 'text' == $service->type ) {
					$country = trim( $response['body'] );
				} else if ( 'json' == $service->type ) {
					$data = (array) json_decode( $response['body'] );
					$country = @$data[ @$service->field ];
				}
			}
		}

		return $country;
	}

	/**
	 * Updates ip-country map and stores in  options ( sitemeta ) table
	 *
	 * @param $ip
	 * @param $country
	 * @return mixed
	 */
	private function _update_ip_county_map( $ip, $country ){
		$country_ip_map[ $ip ] = $country;
		update_site_option( self::COUNTRY_IP_MAP, $country_ip_map );
		return $country;
	}

	/**
	 * Retrieves ip-country map from options ( sitemeta ) table
	 *
	 * @return array
	 */
	private function _get_ip_county_map(){
		return get_site_option( self::COUNTRY_IP_MAP, array() );
	}

	/**
	 * Returns country string using ip address
	 *
	 * @param $ip
	 * @return string
	 */
	function get_country_from_ip( $ip ){
		$ip = (string) $ip;

		if( "127.0.0.1" === $ip  )
			return $this->_update_ip_county_map( $ip, "localhost" );

		$country_ip_map = $this->_get_ip_county_map();
		if( isset( $country_ip_map[ $ip ] ) ) {
			if ( !empty( $country_ip_map[ $ip ] ) ) {
				return $country_ip_map[ $ip ];
			}
		}
		$service = $this->_get_service();
		$country = $this->_country_from_api( $ip, $service );

		if ( !empty( $country ) ) {
			return $this->_update_ip_county_map( $ip, $country );
		}

		return 'XX';
	}

}