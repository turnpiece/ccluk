<?php
/**
 * Shipper tasks: import, manifest parser
 *
 * Will import the migration manifest and populate
 * migration metadata from it.
 *
 * @package shipper
 */

/**
 * Manifest parser class
 */
class Shipper_Task_Import_Parse extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Parse migration meta information', 'shipper' );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration      = new Shipper_Model_Stored_Migration();
		$migration_meta = new Shipper_Model_Stored_MigrationMeta();

		$manifest = $this->get_manifest();
		$migration->set( 'export_time', $manifest->get( 'created' ) );
		$migration->set( 'import_time', gmdate( 'r' ) );

		if ( ! empty( $migration_meta->get( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_OPTION ) ) ) {
			// It's API import migration.
			$prefix_option = $migration_meta->get( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_OPTION );
			$prefix_value  = $migration_meta->get( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_VALUE );
		} else {
			// It's API export migration.
			$prefix_option = $manifest->get( 'dbprefix_option' );
			$prefix_value  = $manifest->get( 'dbprefix_value' );
		}

		/**
		 * Update the `db_prefix` only when all the tables are exported. Sometimes, user may want to change the db_prefix
		 * Without exporting all the tables, which will end up with db error.
		 *
		 * @since 1.1.4
		 */
		$is_important_tables_missing = ! ! $manifest->get( 'is_important_tables_missing' );
		$source_prefix               = $manifest->get( 'table_prefix' );

		if ( $is_important_tables_missing ) {
			Shipper_Helper_Log::write( __( 'Some important tables are not imported, so we won\'t update the db_prefix', 'shipper' ) );
			$migration->set( 'destination_prefix', false );
		} else {
			// So all the important tables are included.
			if ( 'source' === $prefix_option ) {
				$migration->set( 'destination_prefix', $source_prefix );
			} elseif ( 'custom' === $prefix_option ) {
				$migration->set( 'destination_prefix', $prefix_value );
			} else {
				// Fallback, change nothing.
				$migration->set( 'destination_prefix', false );
			}
		}

		$migration->set( 'export_type', $manifest->get( 'export_type' ) );

		if ( ! empty( $source_prefix ) ) {
			$migration->set( 'source_prefix', $source_prefix );
		}

		$migration->set( 'other_tables', $manifest->get( 'other_tables' ) );
		$dumped = new Shipper_Model_Dumped_Filelist();
		$migration->set( 'total-manifest-files', $dumped->get_statements_count() );

		$large = new Shipper_Model_Dumped_Largelist();
		$migration->set( 'total-manifest-large-files', $large->get_statements_count() );
		$migration->save();

		return true;
	}
}