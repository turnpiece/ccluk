<?php
/**
 * Shipper models: Amazon S3 multipart upload/download abstraction
 *
 * Holds S3 multipart transfer info, including parts.
 *
 * @package shipper
 */

/**
 * Stored creds model class
 */
abstract class Shipper_Model_Stored_Multipart extends Shipper_Model_Stored {

	const KEY_TRANSFER_ID = 'transfer_id';
	const KEY_PARTS       = 'parts';

	const KEY_PART_DONE = 'is_done';
	const KEY_PART_IDX  = 'index';

	/**
	 * Gets upload parts queue
	 *
	 * @return array
	 */
	public function get_parts() {
		$parts = $this->get( self::KEY_PARTS, array() );
		return $parts;
	}

	/**
	 * Gets total number of parts
	 *
	 * @return int
	 */
	public function get_parts_count() {
		$parts = $this->get_parts();
		return count( $parts );
	}

	/**
	 * Gets number of parts already transfered
	 *
	 * @return int
	 */
	public function get_transfered_count() {
		$parts    = $this->get_parts();
		$uploaded = 0;

		foreach ( $parts as $part ) {
			if ( ! empty( $part[ self::KEY_PART_DONE ] ) ) {
				$uploaded++;
			}
		}

		return $uploaded;
	}

	/**
	 * Adds upload part to the queue
	 *
	 * @param array $part Part to add.
	 *
	 * @return object
	 */
	public function add_part( $part ) {
		if ( ! is_array( $part ) ) {
			// Error - @TODO log this.
			return $this;
		}
		$parts = $this->get_parts();
		$idx   = count( $parts );

		$part[ self::KEY_PART_DONE ] = false;
		$part[ self::KEY_PART_IDX ]  = $idx;

		$parts[ $idx ] = $part;

		return $this->set( self::KEY_PARTS, $parts );
	}

	/**
	 * Gets individual part hash
	 *
	 * @param int $idx Part index.
	 *
	 * @return array
	 */
	public function get_part( $idx ) {
		$parts = $this->get_parts();
		if ( empty( $parts[ $idx ] ) ) {
			return array();
		}

		return $parts[ $idx ];
	}

	/**
	 * Returns next upload part
	 *
	 * @return array Transfer part hash
	 */
	public function get_next() {
		$parts = $this->get_parts();
		$next  = array();
		foreach ( $parts as $part ) {
			if ( ! empty( $part[ self::KEY_PART_DONE ] ) ) {
				continue;
			}
			$next = $part;
			break;
		}
		return $next;
	}

	/**
	 * Completes part tranfer
	 *
	 * @param array|int $idx Part index or part.
	 *
	 * @return bool
	 */
	public function complete_part( $idx ) {
		if ( is_array( $idx ) ) {
			$idx = isset( $idx[ self::KEY_PART_IDX ] ) && is_numeric( $idx[ self::KEY_PART_IDX ] )
				? (int) $idx[ self::KEY_PART_IDX ]
				: $idx;
		}
		if ( ! is_numeric( $idx ) ) {
			return false;
		}

		$parts = $this->get_parts();
		if ( empty( $parts[ $idx ] ) ) {
			return false;
		}

		$parts[ $idx ][ self::KEY_PART_DONE ] = true;
		$this->set( self::KEY_PARTS, $parts );

		$this->save();

		return true;
	}

	/**
	 * Gets transfer ID for the current batch
	 *
	 * @return string
	 */
	public function get_transfer_id() {
		return $this->get( self::KEY_TRANSFER_ID, '' );
	}

	/**
	 * Checks if we have a registered transfer
	 *
	 * @return bool
	 */
	public function has_transfer() {
		$transfer = $this->get_transfer_id();
		$parts    = $this->get_parts();

		return ! empty( $transfer ) && ! empty( $parts );
	}

	/**
	 * Checks if the current upload queue is all done
	 *
	 * @return bool
	 */
	public function is_done() {
		$next = $this->get_next();
		return $this->has_transfer() && empty( $next );
	}

	/**
	 * Creates an upload queue
	 *
	 * @param string $transfer_id ID of the current transfer.
	 * @param array  $parts Upload parts.
	 *
	 * @return object
	 */
	public function create( $transfer_id, $parts ) {
		$this->set_data(
			array(
				self::KEY_TRANSFER_ID => $transfer_id,
				self::KEY_PARTS       => array(),
			)
		);
		if ( is_array( $parts ) ) {
			foreach ( $parts as $part ) {
				$this->add_part( $part );
			}
		}
		return $this->save();
	}

	/**
	 * Gets a list of offsets and lengths for a given file size
	 *
	 * @param int $total_size Total size to break apart.
	 * @param int $increment Optional individual part size.
	 *
	 * @return array
	 */
	public function get_calculated_transfer_parts( $total_size, $increment = false ) {
		$parts = array();

		if ( ! (int) $increment ) {
			$increment = 25 * 1024 * 1024;
		}

		if ( ! (int) $increment || ! (int) $total_size ) {
			return $parts;
		}

		$total_parts = ceil( (int) $total_size / (int) $increment );

		for ( $i = 0; $i < $total_parts; $i++ ) {
			$parts[] = array(
				'seekTo' => $i * $increment,
				'length' => $increment,
			);
		}

		return $parts;
	}
}