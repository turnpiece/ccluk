<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener;

use Hammer\Base\Component;
use Hammer\Base\Container;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Hardener;

class Rule_Service extends Component {
	/**
	 * Attach Utils to all component rules
	 *
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	/**
	 * @param $curr_status
	 * @param $slug
	 */
	protected function store( $curr_status, $slug ) {
		$settings = Hardener\Model\Settings::instance();
		switch ( $curr_status ) {
			case 'fixed':
				$settings->addToResolved( $slug );
				break;
			case 'ignore':
				$settings->addToIgnore( $slug );
				break;
			case 'issue':
				$settings->addToIssues( $slug );
				break;
			default:
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $curr_status, Utils::instance()->getUserIp() ) );
				break;
		}
	}

	/**
	 * @param $slug
	 */
	public function ignore( $slug ) {
		self::store( 'ignore', $slug );
	}

	/**
	 * A helper function for child class
	 * @return string
	 */
	public function retrieveWPConfigPath() {
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			return ( ABSPATH . 'wp-config.php' );
		} elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			return ( dirname( ABSPATH ) . '/wp-config.php' );
		} elseif ( defined( 'WD_TEST' ) && constant( 'WD_TEST' ) == true ) {
			//case tests
			return '/tmp/wordpress-tests-lib/wp-tests-config.php';
		}
	}

	/**
	 * @param $config
	 *
	 * @return bool|int|string
	 */
	protected function findDefaultHookLine( $config ) {
		global $wpdb;
		$pattern = '/^\$table_prefix\s*=\s*[\'|\"]' . $wpdb->prefix . '[\'|\"]/';
		foreach ( $config as $k => $line ) {
			if ( preg_match( $pattern, $line ) ) {
				return $k;
			}
		}

		return false;
	}

	/**
	 * @param $url
	 * @param $origin
	 *
	 * @return array|mixed|\WP_Error
	 */
	protected function headRequest( $url, $origin, $ttl = null ) {
		$settings = Hardener\Model\Settings::instance();
		$cached   = $settings->getDValues( 'head_requests' );
		if ( ! is_array( $cached ) ) {
			$cached = [];
		}
		if ( isset( $cached[ $url ] ) ) {
			$cache = $cached[ $url ];
			if ( $cache['ttl'] > time() ) {
				//we'll use the cache
				//Utils::instance()->log( sprintf( 'Header for %s return from cached', $url ) );

				return $cache['data'];
			}
		}

		//no cache or cache expired
		$request = wp_remote_head( $url, [
			'user-agent' => 'WP Defender self ping - ' . $origin
		] );
		if ( ! is_wp_error( $request ) ) {
			$headers = wp_remote_retrieve_headers( $request );
			$headers = $headers->getAll();
			if ( $ttl === null ) {
				$ttl = strtotime( '+1 day' );
			}
			$headers['response_code'] = wp_remote_retrieve_response_code( $request );
			$cached[ $url ]           = [
				'ttl'  => apply_filters( 'wd_tweaks_head_request_ttl', $ttl ),
				'data' => $headers
			];
			$settings->setDValues( 'head_requests', $cached );
			Utils::instance()->log( sprintf( 'Fetched header for %s into cache', $url ), 'tweaks' );

			return $headers;
		}

		return $request;
	}

	/**
	 * @param $url
	 */
	public function clearHeadRequest( $url ) {
		$settings = Hardener\Model\Settings::instance();
		$cached   = $settings->getDValues( 'head_requests' );
		unset( $cached[ $url ] );
		$settings->setDValues( 'head_requests', $cached );
	}
}