<?php
/**
 * Shipper tasks: destination pinging task
 *
 * Used to check if the Hub can actively communicate with
 * destination in both directions (i.e. we can do stuff to
 * it _from_ the Hub API).
 *
 * @package shipper
 */

/**
 * Destination adder task class
 */
class Shipper_Task_Api_Destinations_Ping extends Shipper_Task_Api {

	/**
	 * Adds a destination via the Hub API
	 *
	 * @param array $args Uses the domain key to ping the actual domain.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$domain = ! empty( $args['domain'] )
			? $args['domain']
			: false;

		if ( empty( $domain ) ) {
			$this->clear_cached_api_response( 'destinations-ping' );
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing domain to check', 'shipper' )
			);
			return false;
		}

		$status = $this->get_response(
			'destinations-ping',
			self::METHOD_GET,
			array(
				'domain' => $domain,
			)
		);

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'destinations-ping',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: domain name. */
					__( 'Domain %s is not Hub-accessible', 'shipper' ),
					$domain
				)
			);
			return false;
		}

		$this->record_success( 'destinations-ping' );
		return true;
	}

}