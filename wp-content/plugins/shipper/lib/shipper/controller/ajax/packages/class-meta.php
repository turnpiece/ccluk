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

	/**
	 * Boot method.
	 *
	 * @return false
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		$this->add_handler( 'reset', array( $this, 'json_reset_package' ) );
		$this->add_handler( 'create', array( $this, 'json_create_package' ) );

		$this->add_handler( 'download_package', array( $this, 'download_package' ) );
		$this->add_handler( 'download_installer', array( $this, 'download_installer' ) );
		$this->add_handler( 'refresh_db_tree', array( $this, 'refresh_db_tree' ) );

		// Sub-site search handler.
		$this->add_handler( 'search_sub_site', array( $this, 'search_sub_site' ) );
	}

	/**
	 * Refresh db tree
	 *
	 * @return void
	 */
	public function refresh_db_tree() {
		$this->do_request_sanity_check();
		$model        = new Shipper_Model_Stored_PackageMeta();
		$data         = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
		$site_id      = ! empty( $data['site_id'] ) ? $data['site_id'] : '';
		$network_type = ! empty( $data['network_type'] ) ? $data['network_type'] : '';

		$model->set( Shipper_Model_Stored_PackageMeta::NETWORK_TYPE, $network_type );
		$model->set( Shipper_Model_Stored_PackageMeta::NETWORK_SUBSITE_ID, $site_id );
		$model->save();
		$tpl = new Shipper_Helper_Template();
		$tpl->render( 'modals/packages/create/settings-database' );
		exit;
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

		Shipper_Helper_System::optimize();
		$model = new Shipper_Model_Stored_Package();

		if ( ! $model->has_package() ) {
			return $this->redirect();
		}

		$path = $model->get_package();

		if ( ! is_readable( $path ) ) {
			return $this->redirect();
		}

		$filename = $model->get_package_name();

		header( 'Content-Description: File Transfer' );
		header( 'Pragma: public' );
		header( 'Cache-Control: no-cache' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize( $path ) );
		header( "Content-Disposition: attachment; filename={$filename}" );

		$fs = Shipper_Helper_Fs_File::open( $path );

		if ( ! $fs ) {
			return false;
		}

		while ( ! $fs->eof() ) {
			echo $fs->fread( round( 3 * 1024 * 1024 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			ob_flush();
			flush();
			// @RIPS\Annotation\Ignore
			usleep( 10000 );
		}

		if ( connection_status() !== CONNECTION_NORMAL ) {
			echo 'Connection aborted';
		}

		exit;
	}

	/**
	 * Download the pre-processed installer file
	 */
	public function download_installer() {
		$this->do_request_sanity_check( 'shipper-package-download', self::TYPE_GET );

		$model = new Shipper_Model_Stored_Package();
		if ( ! $model->has_package() ) {
			return $this->redirect();
		}

		$path = plugin_dir_path( SHIPPER_PLUGIN_FILE ) . 'lib/installer/installer.php';
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return $this->redirect();
		}

		$fs = Shipper_Helper_Fs_File::open( $path );

		if ( ! $fs ) {
			return false;
		}

		$content  = $fs->fread( $fs->getSize() );
		$filename = basename( $path );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: text/plain' );
		header( 'Content-Type: text/plain' );
		header( "Content-Disposition: attachment; filename={$filename}" );

		echo preg_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'/\{\{SHIPPER_INSTALLER_PASSWORD\}\}/',
			$model->get( Shipper_Model_Stored_Package::KEY_PWD, '' ),
			preg_replace(
				'/\{\{SHIPPER_INSTALLER_SALT\}\}/',
				md5( shipper_get_site_uniqid( microtime() ) ),
				$content
			)
		);

		wp_die();
	}

	/**
	 * Resets package data
	 */
	public function json_reset_package() {
		$this->do_request_sanity_check( 'shipper-reset-package' );

		( new Shipper_Model_Stored_Migration() )->clear()->save();
		( new Shipper_Model_Stored_Package() )->clear()->save();
		( new Shipper_Model_Stored_Exclusions() )->clear()->save();

		return wp_send_json_success();
	}

	/**
	 * Creates a new package
	 */
	public function json_create_package() {
		$this->do_request_sanity_check( 'shipper-create-package' );

		$data = wp_parse_args(
			stripslashes_deep( $_POST ), // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
			array(
				'name'           => '',
				'password'       => '',
				'exclude_files'  => array(),
				'exclude_tables' => array(),
				'exclude_extra'  => array(),
			)
		);

		// Clear previously saved blacklisted files.
		( new Shipper_Model_Stored_Exclusions() )->clear()->save();

		$meta = new Shipper_Model_Stored_PackageMeta();
		$meta->set( Shipper_Model_Stored_PackageMeta::TABLES_PICKED, $data['exclude_tables'] );

		/**
		 * Because the tables work a bit different than other, get and all ignore the missing choices
		 * So we will need to get the different
		 */
		$model                  = new Shipper_Model_Database();
		$table_included         = ! empty( $data['exclude_tables'] ) ? $data['exclude_tables'] : array();
		$table_excluded         = array_diff( $model->get_tables_list(), $data['exclude_tables'] );
		$data['exclude_tables'] = $table_excluded;
		$model                  = new Shipper_Model_Stored_Package();
		$model->clear();

		$name = sanitize_file_name( $data['name'] );
		if ( empty( $name ) ) {
			$name = 'package-' . gmdate( 'YmdHis' );
		}
		$model->set( Shipper_Model_Stored_Package::KEY_NAME, $name );
		$model->set(
			Shipper_Model_Stored_Package::KEY_DATE,
			strtotime( current_time( 'mysql' ) )
		);

		$model->set(
			Shipper_Model_Stored_Package::KEY_PWD,
			sanitize_text_field( $data['password'] )
		);
		$model->save();

		// categorize the custom table so we can specific treat it later.
		// custom table is the case thus don't have db prefix.
		$all_tables   = Shipper_Helper_Template_Sorter::get_grouped_tables( $meta );
		$other_tables = array();
		foreach ( $table_included as $tbl ) {
			if ( in_array( $tbl, $all_tables[ Shipper_Helper_Template_Sorter::OTHER_TABLES ], true ) ) {
				$other_tables[] = $tbl;
			}
		}
		$meta->set( $meta::KEY_OTHER_TABLES, $other_tables );

		if ( ! empty( $data['exclude_files'] ) && is_array( $data['exclude_files'] ) ) {
			$meta->set(
				$meta::KEY_EXCLUSIONS_FS,
				array_map( 'sanitize_text_field', $data['exclude_files'] )
			);
		} else {
			$meta->set( $meta::KEY_EXCLUSIONS_FS, array() );
		}

		if ( ! empty( $data['exclude_tables'] ) && is_array( $data['exclude_tables'] ) ) {
			$meta->set(
				$meta::KEY_EXCLUSIONS_DB,
				array_map( 'sanitize_text_field', $data['exclude_tables'] )
			);
		} else {
			$meta->set( $meta::KEY_EXCLUSIONS_DB, array() );
		}

		if ( ! empty( $data['exclude_extra'] ) && is_array( $data['exclude_extra'] ) ) {
			$meta->set(
				$meta::KEY_EXCLUSIONS_XX,
				array_map( 'sanitize_text_field', $data['exclude_extra'] )
			);
		} else {
			$meta->set( $meta::KEY_EXCLUSIONS_XX, array() );
		}

		$meta->save();

		return wp_send_json_success();
	}

	/**
	 * Search site
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function search_sub_site() {
		$this->do_request_sanity_check( 'shipper_search_sub_site', self::TYPE_GET );

		$args           = array();
		$get            = wp_unslash( $_GET ); // phpcs:ignore
		$search         = ! empty( $get['search'] ) ? sanitize_text_field( $get['search'] ) : '';
		$args['search'] = $search;

		$sites = Shipper_Helper_MS::get_all_sites( $args );
		$sites = array_map(
			function( $site ) {
				return array(
					'site_id'  => $site->blog_id,
					'site_url' => esc_url(
						$site->domain . rtrim( $site->path, '/' )
					),
				);
			},
			$sites
		);

		wp_send_json_success( $sites );
	}
}