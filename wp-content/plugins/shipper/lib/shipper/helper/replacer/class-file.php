<?php
/**
 * Shipper helpers: file replacer
 *
 * Handles transformations related to file migrations.
 *
 * @package shipper
 */

/**
 * File replacer helper class
 */
class Shipper_Helper_Replacer_File extends Shipper_Helper_Replacer {

	/**
	 * Transforms file contents
	 *
	 * Stores transformed intermediate file as a side-effect.
	 *
	 * @param string $path Path to a file to transform.
	 *
	 * @return string Path to a transformed file
	 */
	public function transform( $path ) {
		if ( ! is_readable( $path ) ) {
			// We can't deal with this, move on.
			return $path;
		}

		// Okay, so now we migrate the file.
		$filename    = md5( $path );
		$destination = Shipper_Helper_Fs_Path::get_temp_dir() . $filename;

		$fs = Shipper_Helper_Fs_File::open( $path );

		if ( ! $fs ) {
			return false;
		}

		$content = $fs->fread( $fs->getSize() );

		$replacer = new Shipper_Helper_Replacer_String( $this->get_direction() );
		$replacer->set_codec_list( $this->get_codec_list() );

		$fs = Shipper_Helper_Fs_File::open( $destination, 'w' );

		if ( ! $fs ) {
			return false;
		}

		$fs->fwrite( $replacer->transform( $content ) );

		return $destination;
	}
}
