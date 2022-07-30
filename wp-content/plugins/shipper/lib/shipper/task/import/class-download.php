<?php
/**
 * Shipper tasks: import, migration archive download
 *
 * It will download the migration archive and set it up
 * in the migration staging area.
 *
 * @package shipper
 */

/**
 * Shipper import download class
 */
class Shipper_Task_Import_Download extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Download the migration meta information', 'shipper' );
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
		$migration = new Shipper_Model_Stored_Migration();
		$domain    = $migration->get_source();

		$local_root = trailingslashit(
			Shipper_Helper_Fs_Path::get_temp_dir()
		) . Shipper_Model_Stored_Migration::COMPONENT_META;
		if ( ! file_exists( $local_root ) ) {
			wp_mkdir_p( $local_root );
		}
		$domain_root = trailingslashit(
			Shipper_Helper_Fs_Path::clean_fname( $domain )
		) . Shipper_Model_Stored_Migration::COMPONENT_META;

		$files   = new Shipper_Model_Dumped_Filelist();
		$large   = new Shipper_Model_Dumped_Largelist();
		$package = new Shipper_Model_Dumped_Packagelist();
		$metas   = array(
			$files->get_file_name(),
			$large->get_file_name(),
			$package->get_file_name(),
			'migration_manifest.json',
		);

		$remote = new Shipper_Helper_Fs_Remote();
		$batch  = array();
		foreach ( $metas as $basename ) {
			$source      = trailingslashit( $domain_root ) . $basename;
			$destination = trailingslashit( $local_root ) . $basename;
			$batch[]     = $remote->get_download_command( $source, $destination );
		}
		if ( ! empty( $batch ) ) {
			$status = $remote->execute_batch_queue( $batch );
			if ( empty( $status ) ) {
				Shipper_Helper_Log::write(
					'Something went wrong donwloading meta files, will re-try'
				);

				// Attempt re-try.
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets total steps this task is expected to take
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return 1;
	}

	/**
	 * Gets where we are with the task
	 *
	 * @return int
	 */
	public function get_current_step() {
		return 1;
	}
}