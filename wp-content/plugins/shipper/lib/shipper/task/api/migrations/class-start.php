<?php
/**
 * Shipper tasks: migration starting Hub action
 *
 * @package shipper
 */

/**
 * Migration remote starting task class
 */
class Shipper_Task_Api_Migrations_Start extends Shipper_Task_Api {

	/**
	 * Asks Hub API for the remote migration start on the destination URL
	 *
	 * @param array $args Uses the source/target keys to hold domain info.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$source = ! empty( $args['source'] )
			? $args['source']
			: false
		;

		if ( empty( $source ) ) {
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing domain to attempt migration from', 'shipper' )
			);
			return false;
		}

		$target = ! empty( $args['target'] )
			? $args['target']
			: false
		;

		if ( empty( $target ) ) {
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing domain to attempt migration to', 'shipper' )
			);
			return false;
		}

		$type = ! empty( $args['type'] )
			? $args['type']
			: false
		;

		if ( empty( $type ) ) {
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing migration type to attempt', 'shipper' )
			);
			return false;
		}

		$status = $this->get_response( 'migration-start', self::METHOD_POST, array(
			'source' => $source,
			'target' => $target,
			'type' => $type,
			'version' => SHIPPER_VERSION,
		));

		if ( empty( $status['success'] ) ) {
			$msg = array();
			if ( ! empty( $status['message'] ) ) {
				$msg[] = $status['message'];
			}
			$msg[] = $this->get_formatted_error( $status );

			$this->add_error(
				self::ERR_SERVICE,
				sprintf(
					__( 'Service error: %s', 'shipper' ),
					join( '; ', $msg )
				)
			);
			return false;
		}

		return true;
	}

}