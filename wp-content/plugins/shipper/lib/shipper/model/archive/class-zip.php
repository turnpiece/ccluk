<?php
/**
 * Shipper archiving models: ZipArchive implementation
 *
 * @since v1.1
 * @package shipper
 */

/**
 * ZipArchive archive implementation
 */
class Shipper_Model_Archive_Zip extends Shipper_Model_Archive {

	const FLUSH_THRESHOLD = 500;

	/**
	 * Zip instance
	 *
	 * @var $zip
	 */
	private $zip;

	/**
	 * Added files count
	 *
	 * @var int
	 */
	private $added_files = 0;

	/**
	 * Add file to zip
	 *
	 * @param string $source source file path.
	 * @param string $destination destination file path.
	 *
	 * @return bool
	 * @throws \Shipper_Exception Throws Shipper Exception.
	 */
	public function add_file( $source, $destination ) {
		if ( empty( $this->zip ) ) {
			$this->open();
		}
		if ( ! $this->zip->addFile( $source, $destination ) ) {
			throw new Shipper_Exception(
				/* translators: %s: file path. */
				sprintf( __( 'Shipper couldn\'t archive file: %s', 'shipper' ), $source )
			);
		}

		// set compression level.
		$this->added_files ++;

		if ( method_exists( $this->zip, 'setCompressionName' ) ) {
			$this->zip->setCompressionName( $destination, ZipArchive::CM_SHRINK );
		}

		if ( $this->added_files > self::FLUSH_THRESHOLD ) {
			$this->close();
		}

		return true;
	}

	/**
	 * Open the zip
	 *
	 * @throws \Shipper_Exception Throws Shipper Exception.
	 */
	public function open() {
		$this->zip = new ZipArchive();
		if ( true !== $this->zip->open( $this->get_path(), ZipArchive::CREATE ) ) {
			throw new Shipper_Exception(
				/* translators: %s: file path. */
				sprintf( __( 'Shipper could not open target zip file: %s', 'shipper' ), $this->get_path() )
			);
		}
	}

	/**
	 * Close the zip
	 */
	public function close() {
		if ( ! empty( $this->zip ) ) {
			$this->zip->close();
			$this->added_files = 0;
			$this->zip         = null;
		}
	}

	/**
	 * Extract the zip
	 *
	 * @param string $destination destination path.
	 */
	public function extract( $destination ) {
		$this->zip = new ZipArchive();
		if ( true !== $this->zip->open( $this->get_path() ) ) {
			Shipper_Helper_Log::debug(
				sprintf(
					/* translators: %s: file path. */
					__( 'Shipper could not open target zip file: %s', 'shipper' ),
					$this->get_path()
				)
			);
			return;
		}
		$this->zip->extractTo( $destination );
		$this->zip->close();
	}
}