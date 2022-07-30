<?php
/**
 * System info model
 *
 * Holds reference to system info data
 *
 * @package shipper
 */

/**
 * Sysinfo model class
 */
class Shipper_Model_System extends Shipper_Model {

	const INFO_PHP    = 'php';
	const INFO_SERVER = 'server';
	const INFO_DB     = 'mysql';
	const INFO_WP     = 'wordpress';

	/**
	 * Constructor
	 *
	 * Sets internal storage data to submodels
	 */
	public function __construct() {
		$this->set_data(
			array(
				self::INFO_PHP    => new Shipper_Model_System_Php(),
				self::INFO_DB     => new Shipper_Model_System_Db(),
				self::INFO_SERVER => new Shipper_Model_System_Server(),
				self::INFO_WP     => new Shipper_Model_System_Wp(),
			)
		);
	}

	/**
	 * Aggregates all submodels info
	 *
	 * @return array
	 */
	public function get_data() {
		$models = parent::get_data();
		$data   = array();
		foreach ( $models as $key => $model ) {
			$data[ $key ] = $model->get_data();
		}
		return $data;
	}

	/**
	 * Gets nicely formatted value for user output
	 *
	 * Proxies submodel output value.
	 *
	 * @param string $model Submodel to use.
	 * @param string $key Value key.
	 * @param mixed  $fallback Fallback value to use (optional).
	 *
	 * @return string
	 */
	public function get_output_value( $model, $key, $fallback = false ) {
		if ( ! in_array( $model, array_keys( $this->get_data() ), true ) ) {
			return $fallback;
		}

		$model = $this->get( $model );
		if ( ! is_callable( array( $model, 'get_output_value' ) ) ) {
			return $fallback;
		}

		return $model->get_output_value( $key, $fallback );
	}
}