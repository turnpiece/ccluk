<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Component\Error_Code;

class Blacklist extends Behavior {
	const CACHE_KEY = 'wpdefender_blacklist_status', CACHE_TIME = 1800;
	private $end_point = "https://premium.wpmudev.org/api/defender/v1/blacklist-monitoring";

	public function toggleStatus( $status = null, $format = true ) {
		$api = Utils::instance()->getAPIKey();
		if ( ! $api ) {
			wp_send_json_error( array(
				'message' => __( "A WPMU DEV subscription is required for blocklist monitoring", wp_defender()->domain )
			) );
		}
		if ( $status == null ) {
			$status = get_site_transient( self::CACHE_KEY );
		}
		$endpoint = $this->end_point . '?domain=' . Utils::instance()->stripProtocol( network_site_url() );
		if ( intval( $status ) === - 1 ) {
			$result = Utils::instance()->devCall( $endpoint, array(), array(
				'method' => 'POST'
			), true );
			//re update status
			set_site_transient( self::CACHE_KEY, 1, self::CACHE_TIME );
		} else {
			$result = Utils::instance()->devCall( $endpoint, array(), array(
				'method' => 'DELETE'
			), true );
			set_site_transient( self::CACHE_KEY, - 1, self::CACHE_TIME );
		}

		if ( $format == false ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => __( "Whoops, it looks like something went wrong. Details: ", wp_defender()->domain ) . $result->get_error_message()
			) );
		}

		$this->pullBlackListStatus();
	}

	/**
	 * @param bool $format
	 *
	 * @return int|\WP_Error
	 */
	public function pullBlackListStatus( $format = true ) {
		$currStatus = get_site_transient( self::CACHE_KEY );
		if ( $currStatus === false ) {
			$currStatus = $this->_pullStatus();
			set_site_transient( self::CACHE_KEY, $currStatus, self::CACHE_TIME );
		}
		if ( $format == false ) {
			return $currStatus;
		}
		if ( is_wp_error( $currStatus ) ) {
			wp_send_json_error( [
				'message' => $currStatus->get_error_message()
			] );
		}
		wp_send_json_success( [
			'status' => $currStatus
		] );
	}

	/**
	 * @return int|\WP_Error
	 */
	private function _pullStatus() {
		$endpoint = $this->end_point . '?domain=' . network_site_url();
		$result   = Utils::instance()->devCall( $endpoint, array(), array(
			'method'  => 'GET',
			'timeout' => 5
		), true );
		if ( is_wp_error( $result ) ) {
			//this mean error when firing to API
			return new \WP_Error( Error_Code::API_ERROR, $result->get_error_message() );
		}
		$response_code = wp_remote_retrieve_response_code( $result );
		$body          = wp_remote_retrieve_body( $result );
		$body          = json_decode( $body, true );
		if ( $response_code == 412 ) {
			//this mean disable
			return - 1;
		} elseif ( isset( $body['services'] ) && is_array( $body['services'] ) ) {
			$status = 1;
			foreach ( $body['services'] as $service ) {
				if ( $service['blacklisted'] == true && $service['last_checked'] != false ) {
					$status = 0;
					break;
				}
			}

			return $status;
		} else {
			//fallbacl error
			return new \WP_Error( Error_Code::INVALID, esc_html__( "Something wrong happened, please try again.", wp_defender()->domain ) );
		}
	}
}