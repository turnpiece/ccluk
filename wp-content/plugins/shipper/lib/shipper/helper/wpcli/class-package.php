<?php
/**
 * Shipper helper: WP CLI command interface
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Shipper package migrations actions
 */
class Shipper_Helper_Wpcli_Package extends Shipper_Helper_Wpcli {

	/**
	 * Runs package preflight check
	 */
	public function preflight() {
		$model = new Shipper_Model_Stored_Preflight();
		$model->start( $this->get_cli_dest() );

		$system = new Shipper_Model_System();
		$local  = new Shipper_Task_Check_Package_System();
		$local->restart();
		$local->apply( $system->get_data() );
		$model->set_check(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM,
			array_map(
				function( $chk ) {
					return $chk->get_data();
				},
				$local->get_checks()
			)
		);

		$files = new Shipper_Task_Check_Package_Files();
		$files->restart();
		while ( ! $files->is_done() ) {
			$files->apply();
		}
		$model->set_check(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES,
			array_map(
				function( $chk ) {
					return $chk->get_data();
				},
				$files->get_checks()
			)
		);

		$this->render_preflight_results( $model );
	}

	/**
	 * Creates the site package installer and outputs to STDOUT
	 *
	 * @param array $args list of args.
	 * @param array $assoc_args list of assoc args.
	 *
	 * ## OPTIONS
	 *
	 * [--password=<password>]
	 * : Package password (optional, will use the one from model if available).
	 */
	public function installer( $args, $assoc_args ) {
		$password = ! empty( $assoc_args['password'] )
			? $assoc_args['password']
			: '';
		if ( empty( $password ) ) {
			$model    = new Shipper_Model_Stored_Package();
			$password = $model->get( Shipper_Model_Stored_Package::KEY_PWD, '' );
		}

		$path = plugin_dir_path( SHIPPER_PLUGIN_FILE ) . 'lib/installer/install.php';
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return WP_CLI::error( "Could not find the source file: {$path}" );
		}

		echo wp_kses_post(
			preg_replace(
				'/\{\{SHIPPER_INSTALLER_PASSWORD\}\}/',
				$password,
				preg_replace(
					'/\{\{SHIPPER_INSTALLER_SALT\}\}/',
					md5( shipper_get_site_uniqid( microtime() ) ),
					file_get_contents( $path ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				)
			)
		);
		die;
	}

	/**
	 * Creates the site export package
	 *
	 * @param array $args list of args.
	 * @param array $assoc_args list of assoc args.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<name>]
	 * : Package name (optional).
	 *
	 * [--password=<password>]
	 * : Package password (optional).
	 */
	public function create( $args, $assoc_args ) {
		$start = time();

		$model = new Shipper_Model_Stored_Package();
		$model->clear()->save();

		$name = ! empty( $assoc_args['name'] )
			? sanitize_file_name( $assoc_args['name'] )
			: '';

		if ( empty( $name ) ) {
			$name = 'package-' . gmdate( 'YmdHis', $start );
		}
		$model->set( Shipper_Model_Stored_Package::KEY_NAME, $name );
		$model->set( Shipper_Model_Stored_Package::KEY_DATE, $start );
		if ( ! empty( $assoc_args['password'] ) ) {
			$model->set(
				Shipper_Model_Stored_Package::KEY_PWD,
				sanitize_text_field( $assoc_args['password'] )
			);
		}

		$model->save();

		$migration = $this->prepare();
		do_action( 'shipper_package_migration_tick_before' );
		$this->run_subtasks( new Shipper_Task_Package_All() );
		do_action( 'shipper_package_migration_tick_after' );
		$end = time();

		$source      = $model->get_package_path();
		$destination = trailingslashit( getcwd() ) .
			sanitize_file_name(
				$model->get( Shipper_Model_Stored_Package::KEY_NAME )
			) .
		'.zip';
		if ( ! file_exists( $source ) ) {
			WP_CLI::error( "Unable to find package {$source} - something went wrong" );
		} else {
			$package_size = filesize( $source );
			rename( $source, $destination );
			WP_CLI::success(
				sprintf(
					'Created package %s ( %s ) in %s',
					$destination,
					size_format( $package_size ),
					human_time_diff( $start, $end )
				)
			);
		}

		$migration->complete();
	}

	/**
	 * Prepare method
	 *
	 * @return \Shipper_Model_Stored_Migration
	 */
	protected function prepare() {
		$migration = new Shipper_Model_Stored_Migration();
		$migration->clear()->save();

		$this->prepare_migration(
			Shipper_Model_Stored_Migration::TYPE_EXPORT,
			$this->get_cli_dest()
		);

		Shipper_Helper_Log::clear();

		$storage = new Shipper_Model_Stored_Filelist();
		$storage->clear()->save();

		$files             = new Shipper_Model_Dumped_Filelist();
		$filelist_manifest = $files->get_file_path();
		if ( file_exists( $filelist_manifest ) ) {
			unlink( $filelist_manifest );
		}

		$files             = new Shipper_Model_Dumped_Largelist();
		$filelist_manifest = $files->get_file_path();
		if ( file_exists( $filelist_manifest ) ) {
			unlink( $filelist_manifest );
		}

		$tablelist = new Shipper_Model_Stored_Tablelist();
		$tablelist->clear()->save();

		$migration->begin();
		Shipper_Helper_Log::write(
			__( 'Package migration start', 'shipper' )
		);

		return $migration;
	}
}