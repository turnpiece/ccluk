<?php
/**
 * Shipper controllers: table overrides
 *
 * @package shipper
 */

/**
 * Tables overrides controller class
 */
class Shipper_Controller_Override_Tables extends Shipper_Controller_Override {

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		$constants = $this->get_constants();

		if ( $constants->get( 'SHIPPER_EXPORTED_TABLE_CHARSET' ) ) {
			add_filter(
				'shipper_export_tables_create_charset',
				array( $this, 'apply_exported_table_charset' )
			);
		}

		if ( $constants->get( 'SHIPPER_EXPORTED_TABLE_COLLATION' ) ) {
			add_filter(
				'shipper_export_tables_create_collate',
				array( $this, 'apply_exported_table_collation' )
			);
		}

		if ( $constants->get( 'SHIPPER_IMPORT_TABLE_PREFIX' ) ) {
			$this->apply_import_table_prefix();
		}
	}

	/**
	 * Binds to needed filters in order to apply the new table prefix
	 */
	public function apply_import_table_prefix() {
		if ( ! defined( 'SHIPPER_IMPORT_TABLE_PREFIX' ) ) {
			return false;
		}

		$prefix = SHIPPER_IMPORT_TABLE_PREFIX;
		if ( empty( $prefix ) || ! is_string( $prefix ) ) {
			return false;
		}

		$affected_codecs = array(
			'var',
			'preoptionname',
			'premetakey',
		);
		foreach ( $affected_codecs as $codec ) {
			add_filter(
				"shipper_codec_{$codec}_macro_table_prefix_decode",
				array( $this, 'get_import_table_prefix' )
			);
		}
		add_filter(
			'shipper_destination_table_name',
			array( $this, 'apply_destination_table_prefix' )
		);
	}

	/**
	 * Gets the new import table prefix
	 *
	 * @param string $source Optional source prefix.
	 *
	 * @return string New prefix, or source on failure.
	 */
	public function get_import_table_prefix( $source = '' ) {
		$constants = $this->get_constants();
		if ( ! $constants->is_defined( 'SHIPPER_IMPORT_TABLE_PREFIX' ) ) {
			return $source;
		}

		$prefix = $constants->get( 'SHIPPER_IMPORT_TABLE_PREFIX' );
		if ( empty( $prefix ) || ! is_string( $prefix ) ) {
			return $source;
		}
		return $prefix;
	}

	/**
	 * Applies the new table prefix to imported table name
	 *
	 * @param string $table Source table name.
	 *
	 * @return string
	 */
	public function apply_destination_table_prefix( $table ) {
		$prefix = $this->get_import_table_prefix();
		if ( empty( $prefix ) ) {
			return $table;
		}

		global $wpdb;
		return preg_replace(
			'/^' . preg_quote( $wpdb->base_prefix, '/' ) . '/',
			$prefix,
			$table
		);
	}

	/**
	 * Applies export table charset
	 *
	 * @param string $charset Source charset.
	 *
	 * @return string Defined value
	 */
	public function apply_exported_table_charset( $charset ) {
		if ( ! defined( 'SHIPPER_EXPORTED_TABLE_CHARSET' ) ) { return $charset; }
		return SHIPPER_EXPORTED_TABLE_CHARSET;
	}

	/**
	 * Applies export table collation
	 *
	 * @param string $collation Source collation.
	 *
	 * @return string Defined value
	 */
	public function apply_exported_table_collation( $collation ) {
		if ( ! defined( 'SHIPPER_EXPORTED_TABLE_COLLATION' ) ) { return $collation; }
		return SHIPPER_EXPORTED_TABLE_COLLATION;
	}
}