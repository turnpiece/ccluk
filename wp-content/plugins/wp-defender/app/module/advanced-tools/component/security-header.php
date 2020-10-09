<?php

namespace WP_Defender\Module\Advanced_Tools\Component;

use Hammer\WP\Component;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings;

abstract class Security_Header extends Component {

	/**
	 * @var string
	 */
	static $rule_slug;

	/**
	 * Use for cache
	 *
	 * @var Security_Headers_Settings
	 */
	public $model;

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
	 * @return string
	 */
	abstract function getTitle();

	/**
	 * @return mixed
	 */
	abstract function addHooks();

	/**
	 * Safe way to get cached model
	 *
	 * @return Security_Headers_Settings
	 */
	protected function getModel() {
		if ( is_object( $this->model ) ) {
			return $this->model;
		}

		return $this->model = Security_Headers_Settings::instance();
	}

	/**
	 * Check if the header is out or not
	 *
	 * @param $header
	 * @param $somewhere
	 *
	 * @return bool
	 */
	protected function maybeSubmitHeader( $header, $somewhere ) {
		if ( false === $somewhere ) {
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


	/**
	 * @param $url
	 * @param $origin
	 * @param $ttl
	 *
	 * @return array|mixed
	 */
	protected function headRequest( $url, $origin, $ttl = null ) {
		$model  = $this->getModel();
		$cached = $model->getDataValues( 'head_requests' );
		if ( ! is_array( $cached ) ) {
			$cached = array();
		}
		if ( isset( $cached[ $url ] ) ) {
			$cache = $cached[ $url ];
			if ( $cache['ttl'] > time() ) {

				return $cache['data'];
			}
		}

		//no cache or cache expired
		$request = wp_remote_head(
			$url,
			array(
				'user-agent' => 'WP Defender self ping - ' . $origin,
			)
		);
		if ( ! is_wp_error( $request ) ) {
			$headers = wp_remote_retrieve_headers( $request );
			$headers = $headers->getAll();
			if ( null === $ttl ) {
				$ttl = strtotime( '+1 day' );
			}
			$headers['response_code'] = wp_remote_retrieve_response_code( $request );
			$cached[ $url ]           = array(
				'ttl'  => apply_filters( 'wd_head_request_ttl', $ttl ),
				'data' => $headers,
			);
			$model->setDataValues( 'head_requests', $cached );
			Utils::instance()->log( sprintf( 'Fetched header for %s into cache', $url ), 'security-headers' );

			return $headers;
		}

		return $request;
	}
}