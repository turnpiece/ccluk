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
	 * @param Shipper_Model_Stored_Migration $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return object Shipper_Model_Manifest instance
	 */
	public static function from_migration( Shipper_Model_Stored_Migration $migration ) {
		global $table_prefix;
		$manifest = new self();
		$meta     = new Shipper_Model_Stored_MigrationMeta();
		$manifest->set_data(
			array(
				'version'                     => SHIPPER_VERSION,
				'created'                     => gmdate( 'r' ),
				'source'                      => $migration->get_source(),
				'destination'                 => $migration->get_destination(),
				'abspath'                     => ABSPATH,
				'table_prefix'                => $table_prefix,
				'dbprefix_option'             => $meta->get( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_OPTION ),
				'dbprefix_value'              => $meta->get( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_VALUE ),
				'export_type'                 => $meta->get_mode(),
				'other_tables'                => $meta->get( Shipper_Model_Stored_MigrationMeta::KEY_OTHER_TABLES ),
				'network_type'                => $meta->get_mode(),
				'site_info'                   => Shipper_Helper_MS::get_site_info( $meta->get_site_id() ), // store plugins/themes for activate it later when package migration finalize.
				'is_important_tables_missing' => $migration->is_important_tables_missing(),
				'package_size'                => $migration->get_size(), // Store package size so that we can use it later in dashboard page (API migration).
				'is_wp_config_skipped'        => $migration->is_wp_config_skipped(),
			)
		);

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
		$manifest = new self();

		if ( ! file_exists( $source ) ) {
			return $manifest;
		}

		$fs = Shipper_Helper_Fs_File::open( $source );

		if ( ! $fs || ! $fs->isReadable() ) {
			return $manifest;
		}

		$content = $fs->fread( $fs->getSize() );

		if ( empty( $content ) ) {
			return $manifest;
		}

		$json = json_decode( $content, true );
		if ( empty( $json ) || ! is_array( $json ) ) {
			return $manifest;
		}

		foreach ( $json as $key => $value ) {
			$manifest->set( $key, $value );
		}

		return $manifest;
	}
}