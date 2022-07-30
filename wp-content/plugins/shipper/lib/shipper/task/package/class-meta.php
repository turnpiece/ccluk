<?php
/**
 * Shipper package tasks: meta info storage task
 *
 * @package shipper
 */

/**
 * Package meta info class
 */
class Shipper_Task_Package_Meta extends Shipper_Task_Package {

	/**
	 * Apply method.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 * @throws Shipper_Exception Throws exception.
	 */
	public function apply( $args = array() ) {
		$manifest = $this->create_manifest_file();
		if ( empty( $manifest ) ) {
			throw new Shipper_Exception( 'Unable to create manifest' );
		}

		$zip         = Shipper_Task_Package::get_zip();
		$destination = trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_META ) . basename( $manifest );
		if ( ! $zip->add_file( $manifest, $destination ) ) {
			throw new Shipper_Exception( 'Unable to pack manifest' );
		}
		if ( $zip ) {
			$zip->close();
		}

		return true;
	}

	/**
	 * Creates a manifest file
	 *
	 * @return string|bool Path to file on success, (bool)false on failure
	 * @throws \Shipper_Exception Throws exception.
	 */
	public function create_manifest_file() {
		$manifest    = $this->get_manifest();
		$destination = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) . Shipper_Helper_Fs_Path::clean_fname( Shipper_Model_Manifest::MANIFEST_BASENAME ) . '.json';
		$flags       = defined( 'JSON_PRETTY_PRINT' )
			? JSON_PRETTY_PRINT
			: 0;

		$fs = Shipper_Helper_Fs_File::open( $destination, 'w' );

		if ( ! $fs || ! $fs->isWritable() ) {
			throw new Shipper_Exception(
				sprintf(
					/* translators: %s: destination path. */
					'Unable to write manifest to file: %s',
					$destination
				)
			);
		}

		$fs->fwrite( wp_json_encode( $manifest, $flags ) );

		return $destination;
	}

	/**
	 * Creates the augmented manifest data.
	 *
	 * Proxies the manifest creation from export task.
	 *
	 * @return array Manifest data hash
	 */
	public function get_manifest() {
		$task                     = new Shipper_Task_Export_Meta();
		$migration                = new Shipper_Model_Stored_Migration();
		$meta                     = new Shipper_Model_Stored_PackageMeta();
		$manifest                 = $task->get_manifest( $migration );
		$manifest['codecs']       = $this->get_all_codec_replacements();
		$manifest['export_type']  = $meta->get_mode();
		$manifest['other_tables'] = $meta->get( $meta::KEY_OTHER_TABLES );
		$manifest['network_type'] = $meta->get_mode();
		$manifest['site_info']    = Shipper_Helper_MS::get_site_info( $meta->get_site_id() );

		return $manifest;
	}

	/**
	 * Gets a hash of known codec replacements
	 *
	 * @return array
	 */
	public function get_all_codec_replacements() {
		$result = array();
		foreach ( $this->get_all_codecs() as $key => $codec ) {
			$replacements = $codec->get_replacements_list();
			foreach ( $replacements as $value => $macro ) {
				$result[ $macro ] = $codec->get_original_value( $value );
			}
		}

		return $result;
	}

	/**
	 * Gets a list of all known codec objects
	 *
	 * @return array
	 */
	public function get_all_codecs() {
		$result     = array();
		$myself     = dirname( SHIPPER_PLUGIN_FILE );
		$codec_path = realpath(
			trailingslashit( $myself ) .
			'lib/shipper/helper/codec'
		);
		$codecs     = glob( trailingslashit( $codec_path ) . '*.php' );
		foreach ( $codecs as $codec ) {
			$key        = preg_replace(
				'/^class-/',
				'',
				pathinfo( $codec, PATHINFO_FILENAME )
			);
			$class_name = 'Shipper_Helper_Codec_' . ucfirst( $key );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}
			$obj            = new $class_name();
			$result[ $key ] = $obj;
		}

		return $result;
	}

}