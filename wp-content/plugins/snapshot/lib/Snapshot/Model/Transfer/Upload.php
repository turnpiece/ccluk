<?php // phpcs:ignore
/**
 * Snapshot models: multipart upload model
 *
 * @package snapshot
 */


/**
 * Multipart uploader class
 */
class Snapshot_Model_Transfer_Upload extends Snapshot_Model_Transfer {

	public function __construct( $path = false ) {
		parent::__construct(
			Snapshot_Model_Transfer::TYPE_UPLOAD,
			$path
		);
	}

	/**
	 * Initializes upload transfer
	 *
	 * Spawns transfer parts and sets upload ID.
	 *
	 * @param string $upload_id AWS S3 upload ID.
	 */
	public function initialize( $upload_id ) {
		$parts = $this->get_transfer_parts( filesize( $this->get_path() ) );
		$this->set_parts( $parts );
		$this->set_transfer_id( $upload_id );
	}

	/**
	 * Gets file content corresponding to part
	 *
	 * @param object $part Snapshot_Model_Transfer_Part instance.
	 *
	 * @return string
	 */
	public function get_payload( Snapshot_Model_Transfer_Part $part ) {
		if ( ! file_exists( $this->get_path() ) || ! is_readable( $this->get_path() ) ) {
			return '';
		}
		// phpcs:ignore
		$fp = fopen( $this->get_path(), 'r' );
		if ( ftell( $fp ) !== $part->get_seek() ) {
			fseek( $fp, $part->get_seek() );
		}
		// phpcs:ignore
		return fread( $fp, $part->get_length() );
	}

}