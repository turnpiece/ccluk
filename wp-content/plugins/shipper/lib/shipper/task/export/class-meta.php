<?php
/**
 * Shipper tasks: metadata export task
 *
 * Generates and exports migration manifest file.
 *
 * @package shipper
 */

/**
 * Manifest export class
 */
class Shipper_Task_Export_Meta extends Shipper_Task_Export {

	/**
	 * Task runner method
	 *
	 * Returns (bool)true when the export is done, and
	 * (bool)false otherwise.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration();
		$remote    = new Shipper_Helper_Fs_Remote();

		// Update status flag first.
		$this->has_done_anything = true;
		$files                   = new Shipper_Model_Dumped_Filelist();
		$large                   = new Shipper_Model_Dumped_Largelist();
		$packages                = new Shipper_Model_Dumped_Packagelist();

		$batch = array(
			$this->get_upload_command(
				$this->get_source_path( Shipper_Model_Manifest::MANIFEST_BASENAME, $migration )
			),
			$this->get_upload_command(
				$files->get_file_path()
			),
			$this->get_upload_command(
				$large->get_file_path()
			),
			$this->get_upload_command(
				$packages->get_file_path()
			),
		);
		if ( ! empty( $batch ) ) {
			$status = $remote->execute_batch_queue( $batch );
			if ( empty( $status ) ) {
				Shipper_Helper_Log::write( 'Something went wrong uploading meta files, will re-try' );

				// Attempt re-try.
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets putObject command for a source file
	 *
	 * @param string $source file source.
	 *
	 * @return array
	 */
	public function get_upload_command( $source ) {
		if ( ! file_exists( $source ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					'Non-existent meta file: %s, creating it first',
					$source
				)
			);
			$fs = Shipper_Helper_Fs_File::open( $source, 'w' );
			if ( $fs ) {
				return;
			}

			$fs->fwrite( '' );
		}

		$migration = new Shipper_Model_Stored_Migration();
		$remote    = new Shipper_Helper_Fs_Remote();

		$dest_root   = Shipper_Helper_Fs_Path::clean_fname( $migration->get_destination() );
		$destination = trailingslashit( $dest_root ) . $this->get_destination_path( basename( $source ) );

		return $remote->get_upload_command( $source, $destination );
	}

	/**
	 * Gets readable source path for a manifest file
	 *
	 * Will generate manifest as a side-effect
	 *
	 * @param string $file File basename (sans extension) to export.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string|bool
	 */
	public function get_source_path( $file, $migration ) {
		$manifest    = $this->get_manifest( $migration );
		$destination = Shipper_Helper_Fs_Path::get_temp_dir() . preg_replace( '/[^-_a-z0-9]/i', '', $file ) . '.json';
		$flags       = defined( 'JSON_PRETTY_PRINT' )
			? JSON_PRETTY_PRINT
			: 0;

		$fs = Shipper_Helper_Fs_File::open( $destination, 'w' );
		if ( ! $fs ) {
			return false;
		}
		$res = $fs->fwrite( wp_json_encode( $manifest, $flags ) );
		if ( false === $res ) {
			$this->add_error(
				self::ERR_ACCESS,
				/* translators: %s: file path. */
				sprintf( __( 'Shipper couldn\'t write to file: %s', 'shipper' ), $destination )
			);

			return false;
		}

		return $destination;
	}

	/**
	 * Gets destination type
	 *
	 * Used for classifying output files in the ZIP structure.
	 *
	 * @return string
	 */
	public function get_destination_type() {
		return Shipper_Model_Stored_Migration::COMPONENT_META;
	}

	/**
	 * Gets migration manifest data
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return array
	 */
	public function get_manifest( $migration ) {
		$manifest = Shipper_Model_Manifest::from_migration( $migration );

		return $manifest->get_data();
	}

	/**
	 * Gets the number of steps required to finalize this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return 1;
	}

	/**
	 * Gets the current position in current task finalization
	 *
	 * @return int
	 */
	public function get_current_step() {
		return $this->has_done_anything() ? 1 : 0;
	}

	/**
	 * Gets export task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Gather and pack migration meta information', 'shipper' );
	}
}