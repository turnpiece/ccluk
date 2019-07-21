<?php
/**
 * Shipper models: migration manifest
 *
 * Holds migration manifest info.
 *
 * @package shipper
 */

/**
 * Manifest model
 */
class Shipper_Model_Manifest extends Shipper_Model {

	const MANIFEST_BASENAME = 'migration_manifest';

	/**
	 * Manifest from migration factory method
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return object Shipper_Model_Manifest instance
	 */
	public static function from_migration( Shipper_Model_Stored_Migration $migration ) {
		global $table_prefix;
		$manifest = new self;
		$manifest->set_data(array(
			'version' => SHIPPER_VERSION,
			'created' => date( 'r' ),
			'source' => $migration->get_source(),
			'destination' => $migration->get_destination(),
			'abspath' => ABSPATH,
			'table_prefix' => $table_prefix,
		));
		return $manifest;
	}

	/**
	 * Manifest from source factory method
	 *
	 * @param string $source Pickled manifest representation location.
	 *
	 * @return object Shipper_Model_Manifest instance
	 */
	public static function from_source( $source ) {
		$manifest = new self;

		if ( ! file_exists( $source ) ) { return $manifest; }
		if ( ! is_readable( $source ) ) { return $manifest; }

		$cnt = file_get_contents( $source );
		if ( empty( $cnt ) ) { return $manifest; }

		$json = json_decode( $cnt, true );
		if ( empty( $json ) || ! is_array( $json ) ) { return $manifest; }

		foreach ( $json as $key => $value ) {
			$manifest->set( $key, $value );
		}
		return $manifest;
	}
}
