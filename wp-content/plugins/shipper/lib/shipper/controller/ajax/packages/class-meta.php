<?php
/**
 * Shipper AJAX controllers: package meta controller class
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Packages meta info AJAX controller class
 */
class Shipper_Controller_Ajax_Packages_Meta extends Shipper_Controller_Ajax {

	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		$this->add_handler( 'reset', array( $this, 'json_reset_package' ) );
		$this->add_handler( 'create', array( $this, 'json_create_package' ) );

		$this->add_handler( 'download_package', array( $this, 'download_package' ) );
		$this->add_handler( 'download_installer', array( $this, 'download_installer' ) );
	}

	/**
	 * Redirects to main page as part of download error recovery
	 */
	public function redirect() {
		wp_safe_redirect(
			network_admin_url( 'admin.php?page=shipper-packages' )
		);
		wp_die();
	}

	/**
	 * Download the package file
	 */
	public function download_package() {
		$this->do_request_sanity_check( 'shipper-package-download', self::TYPE_GET );

		$model = new Shipper_Model_Stored_Package;
		if ( ! $model->has_package() ) {
			return $this->redirect();
		}

		$path = $model->get_package();
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return $this->redirect();
		}
		$filename = sanitize_file_name(
			            $model->get( Shipper_Model_Stored_Package::KEY_NAME )
		            ) . '.shipper.zip';

		@header( 'Content-Description: File Transfer' );
		header( 'Pragma: public' );
		header( "Cache-Control: no-cache" );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize( $path ) );
		header( "Content-Disposition: attachment; filename={$filename}" );

		$file = fopen( $path, "r" );
		while ( ! feof( $file ) ) {
			// send the current file part to the browser
			print( @fread( $file, round( 8 * 1024 * 1024 ) ) );
			// flush the content to the browser
			ob_flush();
			flush();
			sleep( 1 );
		}
		if ( connection_status() !== CONNECTION_NORMAL ) {
			echo "Connection aborted";
		}
		fclose( $file );
		exit();
	}

	/**
	 * Download the pre-processed installer file
	 */
	public function download_installer() {
		$this->do_request_sanity_check( 'shipper-package-download', self::TYPE_GET );

		$model = new Shipper_Model_Stored_Package;
		if ( ! $model->has_package() ) {
			return $this->redirect();
		}

		$path = plugin_dir_path( SHIPPER_PLUGIN_FILE ) . 'lib/installer/installer.php';
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return $this->redirect();
		}

		$filename = basename( $path );
		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: text/plain' );
		@header( 'Content-Type: text/plain' );
		@header( "Content-Disposition: attachment; filename={$filename}" );

		echo preg_replace(
			'/\{\{SHIPPER_INSTALLER_PASSWORD\}\}/',
			$model->get( Shipper_Model_Stored_Package::KEY_PWD, '' ),
			preg_replace(
				'/\{\{SHIPPER_INSTALLER_SALT\}\}/',
				md5( shipper_get_site_uniqid( microtime() ) ),
				file_get_contents( $path )
			)
		);

		wp_die();
	}

	/**
	 * Resets package data
	 */
	public function json_reset_package() {
		$this->do_request_sanity_check( 'shipper-reset-package' );

		$migration = new Shipper_Model_Stored_Migration;
		$migration->clear()->save();

		$model = new Shipper_Model_Stored_Package;
		$model->clear()->save();

		return wp_send_json_success();
	}

	/**
	 * Creates a new package
	 */
	public function json_create_package() {
		$this->do_request_sanity_check( 'shipper-create-package' );

		$data = wp_parse_args(
			stripslashes_deep( $_POST ),
			array(
				'name'           => '',
				'password'       => '',
				'exclude_files'  => array(),
				'exclude_tables' => array(),
				'exclude_extra'  => array(),
			)
		);
		/**
		 * Because the tables work a bit different than other, get and all ignore the missing choices
		 * So we will need to get the different
		 */
		$model                  = new Shipper_Model_Database;
		$table_excluded         = array_diff( $model->get_tables_list(), $data['exclude_tables'] );
		$data['exclude_tables'] = $table_excluded;
		$model                  = new Shipper_Model_Stored_Package;
		$model->clear();

		$name = sanitize_file_name( $data['name'] );
		if ( empty( $name ) ) {
			$name = 'package-' . date( 'YmdHis' );
		}
		$model->set( Shipper_Model_Stored_Package::KEY_NAME, $name );
		$model->set(
			Shipper_Model_Stored_Package::KEY_DATE,
			time()
		);

		$model->set(
			Shipper_Model_Stored_Package::KEY_PWD,
			sanitize_text_field( $data['password'] )
		);

		if ( ! empty( $data['exclude_files'] ) && is_array( $data['exclude_files'] ) ) {
			$model->set(
				Shipper_Model_Stored_Package::KEY_EXCLUSIONS_FS,
				array_map( 'sanitize_text_field', $data['exclude_files'] )
			);
		}

		if ( ! empty( $data['exclude_tables'] ) && is_array( $data['exclude_tables'] ) ) {
			$model->set(
				Shipper_Model_Stored_Package::KEY_EXCLUSIONS_DB,
				array_map( 'sanitize_text_field', $data['exclude_tables'] )
			);
		}

		if ( ! empty( $data['exclude_extra'] ) && is_array( $data['exclude_extra'] ) ) {
			$model->set(
				Shipper_Model_Stored_Package::KEY_EXCLUSIONS_XX,
				array_map( 'sanitize_text_field', $data['exclude_extra'] )
			);
		}

		$model->save();

		return wp_send_json_success();
	}
}