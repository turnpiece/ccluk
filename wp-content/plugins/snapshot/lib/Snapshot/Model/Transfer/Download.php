<?php // phpcs:ignore
/**
 * Snapshot models: multipart download model
 *
 * @package snapshot
 */


/**
 * Multipart downloader class
 */
class Snapshot_Model_Transfer_Download extends Snapshot_Model_Transfer {

	public function __construct( $path = false ) {
		parent::__construct(
			Snapshot_Model_Transfer::TYPE_DOWNLOAD,
			$path
		);
	}

	/**
	 * Initializes download transfer
	 *
	 * Spawns transfer parts and sets download ID.
	 *
	 * @param int $download_size File size to download.
	 */
	public function initialize( $download_size ) {
		$parts = $this->get_transfer_parts( $download_size );
		$this->set_parts( $parts );
		$this->set_transfer_id( basename( $this->get_path() ) );
	}

	/**
	 * Gets a temporary file path for corresponding part
	 *
	 * @param object $part Snapshot_Model_Transfer_Part instance.
	 *
	 * @return string
	 */
	public function get_part_file_path( Snapshot_Model_Transfer_Part $part ) {
		$path = $this->get_path();
		if ( empty( $path ) ) {
			return $path;
		}

		$fname = sprintf(
			'%s---%s',
			$part->get_index(), md5( $this->get_transfer_id() )
		);

		return trailingslashit(
			wp_normalize_path( dirname( $path ) )
		) . $fname;
	}

	/**
	 * Stitches together temporary files into final destination path
	 *
	 * @return bool
	 */
	public function stitch_temporary_files() {
		$status = true;
		$path = $this->get_path();

		foreach ( $this->get_parts() as $part ) {
			$file = $this->get_part_file_path( $part );
			$index = $part->get_index();
			if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
				// We can't deal with this file - error.
				Snapshot_Helper_Log::error( "Unable to find download part {$index}" );
				return false;
			}
			// phpcs:ignore
			$status = (bool) @file_put_contents( $path, file_get_contents( $file ), FILE_APPEND );
			if ( empty( $status ) ) {
				// Fail stitching a part - error.
				Snapshot_Helper_Log::error( "Unable to stitch download part {$index}" );
				return false;
			}
			// phpcs:ignore
			@unlink( $file );
		}

		return $status;
	}

	public function complete() {
		$status = $this->stitch_temporary_files();
		parent::complete();
		return $status;
	}

}