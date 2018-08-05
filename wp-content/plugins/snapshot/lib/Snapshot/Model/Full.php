<?php // phpcs:ignore

abstract class Snapshot_Model_Full {

	protected $_errors = array();


	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	abstract public function get_model_type();

	/**
	 * Check for existence of any errors
	 *
	 * @return bool
	 */
	public function has_errors() {
		return empty( $this->_errors );
	}


	/**
	 * Get value from config
	 *
	 * @param string $key Config key to check
	 * @param mixed  $fallback Fallback value
	 *
	 * @return mixed
	 */
	public function get_config( $key = false, $fallback = false ) {
		$snp = WPMUDEVSnapshot::instance();
		$config = ! empty( $snp->config_data['config'] )
			? $snp->config_data['config']
			: array();
		if ( ! isset( $config['full'] ) ) {
			$config['full'] = array();
		}

		return ! empty( $key )
			? ( isset( $config['full'][ $key ] ) ? $config['full'][ $key ] : $fallback )
			: $config['full'];
	}

	/**
	 * Set config value and store options
	 *
	 * @param string $key Key to set
	 * @param mixed  $value Value for key
	 *
	 * @return bool
	 */
	public function set_config( $key, $value ) {
		$snap = WPMUDEVSnapshot::instance();
		if ( ! isset( $snap->config_data['config']['full'] ) ) {
			$snap->config_data['config']['full'] = array();
		}
		$snap->config_data['config']['full'][ $key ] = $value;

		return $snap->save_config();
	}

	/**
	 * Get errors as array of strings ready for showing.
	 *
	 * @return array
	 */
	public function get_errors() {
		return is_array( $this->_errors )
			? $this->_errors
			: array();
	}

	/**
	 * Proxies to expose private method
	 *
	 * @param string $file File name to process.
	 *
	 * @return mixed Timestamp (as string) on success, (bool)false on failure
	 */
	public function get_file_timestamp_from_name ($file) {
		return $this->_get_file_timestamp_from_name($file);
	}

	/**
	 * Set error string for display
	 *
	 * @param string $msg Error message
	 */
	protected function _set_error( $msg ) {
		$this->_errors[] = $msg;
	}

	/**
	 * Parse the timestamp from full backup filename
	 *
	 * Relies on `Snapshot_Helper_Backup::postprocess()` file naming convention
	 *
	 * @param string $file File name to process
	 *
	 * @return mixed Timestamp (as string) on success, (bool)false on failure
	 */
	protected function _get_file_timestamp_from_name( $file ) {
		if ( empty( $file ) ) {
			return false;
		}

		$timestamp = preg_replace( '/^' . Snapshot_Helper_Backup::FINAL_PREFIX . '-([0-9]+)-.*\.zip$/', '\1', $file );
		if ( ! is_numeric( $timestamp ) ) {
			return false;
		}

		return $timestamp;
	}


	/**
	 * Find the oldest file item
	 *
	 * @param array $list Optional raw items list
	 * @param int   $pivot_filename Optional filename to be used as relative anchor
	 *
	 * @return mixed Oldest file item, or (bool)false if nothing found
	 */
	protected function _get_oldest_file_item( $list = array(), $pivot_filename = false ) {
		$list = ! empty( $list ) && is_array( $list )
			? $list
			: array();
		$oldest = ! empty( $pivot_filename )
			? (int) $this->_get_file_timestamp_from_name( $pivot_filename )
			: false;

		$file_item = false;
		foreach ( $list as $item ) {
			if ( empty( $item['timestamp'] ) || empty( $item['name'] ) ) {
				continue;
			}
			$ts = (int) $item['timestamp'];
			if ( ! $oldest || $ts < $oldest ) {
				$oldest = $ts;
				$file_item = $item;
			}
		}
		return $file_item;
	}

	/**
	 * Find the newest file item
	 *
	 * @param array $list Optional raw items list
	 * @param int   $pivot_filename Optional filename to be used as relative anchor
	 *
	 * @return mixed Newest file item, or (bool)false if nothing found
	 */
	protected function _get_newest_file_item( $list = array(), $pivot_filename = false ) {
		$list = ! empty( $list ) && is_array( $list )
			? $list
			: array();
		$newest = ! empty( $pivot_filename )
			? $this->_get_file_timestamp_from_name( $pivot_filename )
			: false;
		$file_item = false;
		foreach ( $list as $item ) {
			if ( empty( $item['timestamp'] ) || empty( $item['name'] ) ) {
				continue;
			}
			$ts = (int) $item['timestamp'];
			if ( ! $newest || $ts > $newest ) {
				$newest = $ts;
				$file_item = $item;
			}
		}
		return $file_item;
	}

	/**
	 * Find the file item immediately newer than the pivot
	 *
	 * @param array $list Optional raw items list
	 * @param int   $pivot_filename Optional filename to be used as relative anchor
	 *
	 * @return mixed Newest file item, or (bool)false if nothing found
	 */
	protected function _get_newer_file_item( $list = array(), $pivot_filename = false ) {
		$list = ! empty( $list ) && is_array( $list )
			? $list
			: array();
		$oldest = ! empty( $pivot_filename )
			? (int) $this->_get_file_timestamp_from_name( $pivot_filename )
			: false;
		usort( $list, array( $this, 'compare_by_timestamp' ) );

		$file_item = false;
		foreach ( $list as $item ) {
			if ( empty( $item['timestamp'] ) || empty( $item['name'] ) ) {
				continue;
			}
			$ts = (int) $item['timestamp'];
			if ( $ts > $oldest ) {
				$file_item = $item;
				break;
			}
		}
		return $file_item;
	}

	/**
	 * Helper for sorting file item lists by timestamp
	 *
	 * @param array $a File item A
	 * @param array $b File item B
	 *
	 * @return bool
	 */
	public function compare_by_timestamp( $a, $b ) {
		if (
			empty( $a['timestamp'] ) || ! is_numeric( $a['timestamp'] )
			||
			empty( $b['timestamp'] ) || ! is_numeric( $b['timestamp'] )
		) {
			return 0;
		}
		return (int) $a['timestamp'] > $b['timestamp'];
	}

	/**
	 * Find the oldest file item name
	 *
	 * @param array $list Optional raw items list
	 * @param int   $pivot_filename Optional filename to be used as relative anchor
	 *
	 * @return string Oldest file name
	 */
	protected function _get_oldest_filename( $list = array(), $pivot_filename = false ) {
		$item = $this->_get_oldest_file_item( $list, $pivot_filename );
		$filename = ! empty( $item['name'] )
			? $item['name']
			: false;
		return $filename;
	}

	/**
	 * Find the newest file item name
	 *
	 * @param array $list Optional raw items list
	 * @param int   $pivot_filename Optional filename to be used as relative anchor
	 *
	 * @return string Newest file name
	 */
	protected function _get_newest_filename( $list = array(), $pivot_filename = false ) {
		$item = $this->_get_newest_file_item( $list, $pivot_filename );
		$filename = ! empty( $item['name'] )
			? $item['name']
			: false;
		return $filename;
	}

	/**
	 * Find the file item name immediately newer than the pivot
	 *
	 * @param array $list Optional raw items list
	 * @param int   $pivot_filename Optional filename to be used as relative anchor
	 *
	 * @return string Newest file name
	 */
	protected function _get_newer_filename( $list = array(), $pivot_filename = false ) {
		$item = $this->_get_newer_file_item( $list, $pivot_filename );
		$filename = ! empty( $item['name'] )
			? $item['name']
			: false;
		return $filename;
	}


	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter( $filter = false ) {
		if ( empty( $filter ) ) {
			return false;
		}
		if ( ! is_string( $filter ) ) {
			return false;
		}
		return 'snapshot-model-full-' . $this->get_model_type() . '-' . $filter;
	}
}