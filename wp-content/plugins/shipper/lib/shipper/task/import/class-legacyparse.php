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
class Shipper_Task_Import_LegacyParse extends Shipper_Task_Import {

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
		$migration = new Shipper_Model_Stored_Migration;
		$manifest  = $this->get_manifest( Shipper_Model_Manifest::MANIFEST_BASENAME );

		// @TODO populate any migration data
		$migration->set( 'export_time', $manifest->get( 'created' ) );
		$migration->set( 'import_time', date( 'r' ) );

		$source_prefix = $manifest->get( 'table_prefix' );
		if ( ! empty( $source_prefix ) ) {
			$migration->set( 'source_prefix', $source_prefix );
		}

		$dumped = new Shipper_Model_Dumped_Filelist;
		$migration->set( 'total-manifest-files', $dumped->get_statements_count() );

		$large = new Shipper_Model_Dumped_Largelist;
		$migration->set( 'total-manifest-large-files', $large->get_statements_count() );

		$migration->save();

		return true;
	}

	/**
	 * Gets migration manifest data
	 *
	 * @param string $file File basename (sans extension) to export.
	 *
	 * @return array
	 */
	public function get_manifest( $file ) {
		$source = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) .
		          trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_META ) .
		          preg_replace( '/[^-_a-z0-9]/i', '', $file ) .
		          '.json';

		return Shipper_Model_Manifest::from_source( $source );
	}

}