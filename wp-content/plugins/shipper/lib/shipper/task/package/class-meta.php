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
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$manifest = $this->create_manifest_file();
		if ( empty( $manifest ) ) {
			throw new Shipper_Exception( 'Unable to create manifest' );
		}

		$zip = Shipper_Task_Package::get_zip();
		$destination = trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_META ) .
			basename( $manifest );
		if ( ! $zip->add_file( $manifest, $destination ) ) {
			throw new Shipper_Exception( 'Unable to pack manifest' );
		}
		$zip->close();

		return true;
	}

	/**
	 * Creates a manifest file
	 *
	 * @return string|bool Path to file on success, (bool)false on failure
	 */
	public function create_manifest_file() {
		$manifest = $this->get_manifest();
		$destination = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) .
			Shipper_Helper_Fs_Path::clean_fname( Shipper_Model_Manifest::MANIFEST_BASENAME ) .
		'.json';
		$flags = defined( 'JSON_PRETTY_PRINT' )
			? JSON_PRETTY_PRINT
			: 0
		;
		$res = file_put_contents( $destination, json_encode( $manifest, $flags ) );
		if ( false === $res ) {
			throw new Shipper_Exception( sprintf(
				'Unable to write manifest to file: %s',
				$destination
			) );
			return false;
		}

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
		$task = new Shipper_Task_Export_Meta;
		$migration = new Shipper_Model_Stored_Migration;
		$manifest = $task->get_manifest( $migration );

		$manifest['codecs'] = $this->get_all_codec_replacements();

		return $manifest;
	}

	/**
	 * Gets a hash of known codec replacements
	 *
	 * @return array
	 */
	public function get_all_codec_replacements() {
		$result = array();
		foreach( $this->get_all_codecs() as $key => $codec ) {
			$replacements = $codec->get_replacements_list();
			foreach( $replacements as $value => $macro ) {
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
		$result = array();
		$myself = dirname( SHIPPER_PLUGIN_FILE );
		$codec_path = realpath(
			trailingslashit( $myself ) .
			'lib/shipper/helper/codec'
		);
		$codecs = glob( trailingslashit( $codec_path ) . '*.php' );
		foreach( $codecs as $codec ) {
			$key = preg_replace(
				'/^class-/',
				'',
				pathinfo( $codec, PATHINFO_FILENAME )
			);
			$class_name = 'Shipper_Helper_Codec_' . ucfirst( $key );
			if ( ! class_exists( $class_name ) ) {
				continue;
			}
			$obj = new $class_name;
			$result[ $key ] = $obj;
		}

		return $result;
	}

}