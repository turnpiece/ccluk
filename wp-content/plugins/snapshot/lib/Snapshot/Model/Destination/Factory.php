<?php // phpcs:ignore
/**
 * Facilitates destination spawning
 *
 * @package snapshot
 */

/**
 * Destination factory class
 *
 * @since 3.1.6-beta.1
 */
class Snapshot_Model_Destination_Factory {

	/**
	 * Spawns prepared destination object from snapshot item
	 *
	 * @param array $item Snapshot item.
	 * @param bool $bootstrap Whether to bootstrap the destination setup.
	 *
	 * @return Snapshot_Model_Destination|bool
	 */
	public static function from_item ($item, $bootstrap= true) {
		$snapshot = WPMUDEVSnapshot::instance();
		if (empty($item['destination'])) return false;

		$destination_key = $item['destination'];
		return self::get_destination($destination_key, $bootstrap);
	}

	/**
	 * Spawns prepared destination object from snapshot item
	 *
	 * @param string $destination_key Snapshot destination ID.
	 * @param bool $bootstrap Whether to bootstrap the destination setup.
	 *
	 * @return Snapshot_Model_Destination|bool
	 */
	public static function get_destination ($destination_key, $bootstrap= true) {
		if (empty($destination_key)) return false;

		$snapshot = WPMUDEVSnapshot::instance();

		if ( ! isset( $snapshot->config_data['destinations'][ $destination_key ] ) ) {
			return false;
		}

		$destination = $snapshot->config_data['destinations'][ $destination_key ];
		if ( ! isset( $destination['type'] ) ) {
			return false;
		}

		$cls = $snapshot->get_setting('destinationClasses');
		if ( ! isset( $cls[ $destination['type'] ] ) ) {
			return false;
		}

		$destination_object = $cls[ $destination['type'] ];
		if (!is_object($destination_object)) return false;

		if (!empty($bootstrap)) {
			if (is_callable(array($destination_object, 'set_up_destination'))) {
				$destination_object->init();
				$destination_object->set_up_destination($destination);
			}
		}

		return $destination_object;
	}
}