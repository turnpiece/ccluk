<?php
/**
 * Shipper tasks: S3 credentials getter task
 *
 * @package shipper
 */

/**
 * Credentials getting task class
 */
class Shipper_Task_Api_Info_Creds extends Shipper_Task_Api {

	/**
	 * Asks Hub API for the S3 credentials details
	 *
	 * @param array $args Not used.
	 *
	 * @return array Migration status info
	 */
	public function apply( $args = array() ) {
		$domain = Shipper_Model_Stored_Destinations::get_current_domain();

		$token = $this->get_token( $domain );
		if ( empty( $token ) ) {
			$this->record_non_success(
				'info-token',
				self::ERR_SERVICE,
				__( 'Service error: unable to acquire mediation token', 'shipper' )
			);
			return array();
		}

		$creds = $this->get_creds( $domain, $token );

		return $creds;
	}

	/**
	 * Gets domain token for the request
	 *
	 * @param string $domain Domain to get token for.
	 *
	 * @return string
	 */
	public function get_token( $domain ) {
		$hasher = new Shipper_Helper_Hash( Shipper_Helper_Hash::INTERVAL_MEDIUM );
		$model  = new Shipper_Model_Api();
		$token  = $hasher->get_hash(
			$model->get( 'api_secret' ),
			$model->get( 'api_key' )
		);

		$status = $this->get_response(
			'info-token',
			self::METHOD_GET,
			array(
				'domain' => $domain,
				'time'   => time(),
				'token'  => $token,
			)
		);

		$data  = ! empty( $status['data'] )
			? $status['data']
			: array();
		$token = ! empty( $data['token'] )
			? $data['token']
			: '';

		return $token;
	}

	/**
	 * Gets actual upload creds
	 *
	 * @param string $domain Domain to grant to.
	 * @param string $token Service token.
	 *
	 * @return array
	 */
	public function get_creds( $domain, $token ) {
		$status = $this->get_response(
			'info-creds',
			self::METHOD_GET,
			array(
				'domain' => $domain,
				'token'  => $token,
			)
		);

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'info-creds',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return array();
		}

		$data = ! empty( $status['data'] )
			? $status['data']
			: array();

		if ( ! empty( $data ) ) {
			$this->record_success( 'info-creds' );
		}
		return $data;
	}

}