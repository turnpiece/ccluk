<?php
/**
 * Shipper tasks: export process abstract class
 *
 * All export tasks will inherit from this.
 *
 * @package shipper
 */

/**
 * Export task class
 */
abstract class Shipper_Task_Export extends Shipper_Task {

	const ERR_REMOTE = 'error_remote';

	/**
	 * Gets export task label
	 *
	 * @return string
	 */
	abstract public function get_work_description();

	/**
	 * Gets readable source path for a file.
	 *
	 * @param string $path Absolute file path.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string
	 */
	abstract public function get_source_path( $path, $migration );

	/**
	 * Gets destination type
	 *
	 * Used for classifying output files in the ZIP structure.
	 *
	 * @return string
	 */
	abstract public function get_destination_type();

	/**
	 * Gets the number of steps required to finalize this task
	 *
	 * @return int
	 */
	abstract public function get_total_steps();

	/**
	 * Gets the current position in current task finalization
	 *
	 * @return int
	 */
	abstract public function get_current_step();

	const ERR_ZIP      = 'error_zip';
	const ERR_ACCESS   = 'error_access';
	const ERR_SQL      = 'error_database';
	const ERR_TRANSFER = 'error_transfer';

	/**
	 * Have we done anything flag
	 *
	 * @var bool
	 */
	protected $has_done_anything = false;

	/**
	 * Checks if this task has done anything this far
	 *
	 * @return bool
	 */
	public function has_done_anything() {
		return ! ! $this->has_done_anything;
	}

	/**
	 * Gets archive path for current migration.
	 *
	 * @param string $destination Destination site.
	 *
	 * @return string
	 */
	public function get_archive_path( $destination ) {
		$root = Shipper_Helper_Fs_Path::get_working_dir();

		$destination = Shipper_Helper_Fs_Path::clean_fname( $destination );

		return "{$root}{$destination}.zip";
	}

	/**
	 * Gets currently exported archive size
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return int Size, in bytes
	 */
	public function get_archive_size( $migration ) {
		$size    = 0;
		$archive = $this->get_archive_path( $migration->get_destination() );

		if ( ! file_exists( $archive ) ) {
			return $size;
		}

		return filesize( $archive );
	}

	/**
	 * Sets up and opens output ZIP archive
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return bool|resource
	 */
	public function get_zip( $migration ) {
		$archive = $this->get_archive_path( $migration->get_destination() );
		$zip     = new ZipArchive();
		if ( true !== $zip->open( $archive, ZipArchive::CREATE ) ) {
			$this->add_error(
				self::ERR_ZIP,
				/* translators: %s: file path. */
				sprintf( __( 'Shipper could not open target zip file: %s', 'shipper' ), $archive )
			);
			return false;
		}

		return $zip;
	}

	/**
	 * Creates files-specific relative path for zip archive
	 *
	 * @param string $path Absolute path to a file.
	 *
	 * @return string
	 */
	public function get_destination_path( $path ) {
		$base    = Shipper_Model_Env::is_flywheel()
			? WP_CONTENT_DIR
			: ABSPATH;
		$root    = ! empty( $this->files )
			? $this->files->get_root()
			: $base;
		$relpath = Shipper_Helper_Fs_Path::get_relpath( $path, $root );

		$pfx = $this->get_destination_type();
		if (
			Shipper_Model_Env::is_flywheel() &&
			Shipper_Model_Stored_Migration::COMPONENT_FS === $pfx
		) {
			$pfx = "{$pfx}/wp-content";
		}

		return $pfx . '/' . $relpath;
	}

	/**
	 * Gets current task finalization status percentage
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		$total   = (int) $this->get_total_steps();
		$current = (int) $this->get_current_step();

		if ( empty( $total ) ) {
			$total = 1;
		}

		$result  = ( 100 / $total ) * $current;
		$is_done = is_callable( array( $this, 'is_done' ) )
			? $this->is_done()
			: $current > $total;

		return $result < 100
			? $result
			: ( $is_done ? 100 : 99 );
	}
}