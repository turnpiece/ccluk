<?php
/**
 * Shipper check tasks: files preflight check (package migrations)
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Files check task class
 */
class Shipper_Task_Check_Package_Files extends Shipper_Task_Check_Files {

	/**
	 * Filelist instance holder
	 *
	 * @var Shipper_Model_Stored_Filelist
	 */
	private $storage;

	/**
	 * Shipper_Task_Check_Package_Files constructor.
	 */
	public function __construct() {
		if ( empty( $this->storage ) ) {
			$this->storage = new Shipper_Model_Stored_Filelist();
		}
	}

	/**
	 * Runs the task
	 *
	 * @param array $args Unused.
	 *
	 * @return bool Whether we encountered any errors
	 */
	public function apply( $args = array() ) {
		$file_zie_threshold  = Shipper_Model_Stored_Migration::get_file_size_threshold();
		$file_name_threshold = 256;

		$over_sized      = array();
		$large_names     = array();
		$total_file_size = 0;

		foreach ( $this->get_files() as $file ) {
			list( $src, $dest, $size ) = $file;

			$total_file_size += filesize( $src );

			if ( $size > $file_zie_threshold ) {
				$over_sized[] = $this->set_path_and_size( $src, $size );
			}

			if ( strlen( $src ) > $file_name_threshold && count( $large_names ) < 100 ) {
				$large_names[] = $this->set_path_and_size( $src, null );
			}
		}

		$large_file_checker      = $this->set_large_files( new Shipper_Model_Check( __( 'Large files', 'shipper' ) ), $over_sized );
		$large_file_name_checker = $this->set_large_files_names( new Shipper_Model_Check( __( 'Files with large names', 'shipper' ) ), $large_names );

		if ( ! empty( $large_file_checker ) ) {
			$this->add_check( $large_file_checker );
		}

		if ( ! empty( $large_file_name_checker ) ) {
			$this->add_check( $large_file_name_checker );
		}

		$this->set_total_file_size( $total_file_size );

		return $this->has_errors();
	}

	/**
	 * Set large files
	 *
	 * @since 1.2.2
	 *
	 * @param Shipper_Model_Check $checker File checker instance.
	 * @param array               $over_sized An array of over-sized files.
	 *
	 * @return object|Shipper_Model_Check
	 */
	private function set_large_files( Shipper_Model_Check $checker, $over_sized ) {
		$count  = count( $over_sized );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $count ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			$tpl    = new Shipper_Helper_Template();
			$markup = $tpl->get(
				'modals/check/preflight-row-files',
				array(
					'files' => $over_sized,
				)
			);

			$checker->set( 'message', $markup );
			$checker->set( 'title', __( 'Large files found', 'shipper' ) );
		}

		$checker->set( 'check_type', 'file_sizes' );
		$checker->set( 'count', $count );

		return $checker->complete( $status );
	}

	/**
	 * Set large file names
	 *
	 * @since 1.2.2
	 *
	 * @param Shipper_Model_Check $checker File checker instance.
	 * @param array               $large_files An array of large files.
	 *
	 * @return object|Shipper_Model_Check
	 */
	private function set_large_files_names( Shipper_Model_Check $checker, $large_files ) {
		$count  = count( $large_files );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $count ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			$tpl    = new Shipper_Helper_Template();
			$markup = $tpl->get(
				'modals/check/preflight-row-files',
				array(
					'files' => $large_files,
				)
			);

			$checker->set( 'message', $markup );
			$checker->set( 'title', __( 'Files with large names found', 'shipper' ) );

		}

		$checker->set( 'check_type', 'file_names' );
		$checker->set( 'count', $count );

		return $checker->complete( $status );
	}

	/**
	 * Save total file size
	 *
	 * @since 1.2.2
	 *
	 * @param int $size file size.
	 */
	private function set_total_file_size( $size ) {
		$this->storage->set( 'files_total_size', $size )->save();
		$db_size = $this->storage->get( 'db_total_size', 0 );

		if ( empty( $db_size ) ) {
			$db_size = $this->update_db_size( $this->storage );
		}

		$package_size = $db_size + $size;

		// Side-effect: update estimate raw size.
		( new Shipper_Model_Stored_Estimate() )->set( 'raw_package_size', $package_size )->save();
	}

	/**
	 * Discard any stored check progress
	 *
	 * @return bool
	 */
	public function restart() {
		return $this->storage->clear()->save();
	}

	/**
	 * Set file path and size
	 *
	 * @param string $path source path.
	 * @param string $size file size.
	 *
	 * @return array
	 */
	public function set_path_and_size( $path, $size ) {
		return array(
			'path' => $path,
			'size' => $size,
		);
	}

	/**
	 * Get array of files
	 *
	 * @return Generator
	 */
	public function get_files() {
		do_action( 'shipper_package_migration_gather_tick_before' );
		$fs = new Shipper_Helper_Fs_Package_Filelist( $this->storage );
		do_action( 'shipper_package_migration_gather_tick_after' );

		return $fs->get_files();
	}

	/**
	 * Gets package size check specifically
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function get_package_size_check() {
		$check  = new Shipper_Model_Check( __( 'Package size', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		$threshold    = Shipper_Model_Stored_Migration::get_package_size_threshold();
		$package_size = $this->get_updated_package_size();

		if ( $package_size > $threshold ) {
			$check->set( 'title', __( 'Package size is large', 'shipper' ) );
			$status = Shipper_Model_Check::STATUS_WARNING;
		}
		$check->set( 'check_type', 'package_size' );

		$tpl = new Shipper_Helper_Template();
		$check->set(
			'message',
			$tpl->get(
				'modals/packages/preflight/issue-package_size-full',
				array(
					'package_size' => $package_size,
					'threshold'    => $threshold,
				)
			)
		);
		$check->set(
			'short_message',
			$tpl->get(
				'modals/packages/preflight/issue-package_size-summary',
				array(
					'package_size' => $package_size,
					'threshold'    => $threshold,
				)
			)
		);

		return $check->complete( $status );
	}
}