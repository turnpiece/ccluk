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

	const FLUSH_THRESHOLD = 100;

	private $_zip;
	private $_added_files = 0;

	public function add_file( $source, $destination ) {
		if ( empty( $this->_zip ) ) {
			$this->open();
		}
		if ( ! $this->_zip->addFile( $source, $destination ) ) {
			throw new Shipper_Exception(
				sprintf( __( 'Shipper couldn\'t archive file: %s', 'shipper' ), $source )
			);
		}
		$this->_added_files ++;

		if ( $this->_added_files > self::FLUSH_THRESHOLD ) {
			$this->close();
		}

		return true;
	}

	public function open() {
		$this->_zip = new ZipArchive;
		if ( true !== $this->_zip->open( $this->get_path(), ZipArchive::CREATE ) ) {
			throw new Shipper_Exception(
				sprintf( __( 'Shipper could not open target zip file: %s', 'shipper' ), $this->get_path() )
			);
		}
	}

	public function close() {
		if ( ! is_object( $this->_zip ) ) {
			return;
		}
		$this->_zip->close();
		$this->_added_files = 0;
		$this->_zip         = false;
	}

	public function extract( $destination ) {
		$this->_zip = new ZipArchive();
		if ( ! $this->_zip->open( $this->get_path() ) ) {
			throw new Shipper_Exception(
				sprintf( __( 'Shipper could not open target zip file: %s', 'shipper' ), $this->get_path() )
			);
		}
		$this->_zip->extractTo( $destination );
		$this->_zip->close();
	}

	public function stats() {
		if ( ! is_object( $this->_zip ) ) {
			$this->open();
		}

		return $this->_zip->count();
	}
}