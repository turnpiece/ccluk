<?php
/**
 * Shipper controllers: package migrations
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Admin pages controller, package migrations page
 */
class Shipper_Controller_Admin_Packages extends Shipper_Controller_Admin {

	/**
	 * Gets order in which menu registration takes place
	 *
	 * @return int Page order
	 */
	public function get_page_order() {
		return parent::get_page_order() + 1;
	}

	/**
	 * Boot method.
	 *
	 * @return bool|void
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		// add menu.
		parent::boot();

		// we need to show the admin notices if the.
		if ( $this->is_install_files_leftover() ) {
			add_action( 'wp_loaded', array( &$this, 'cleanup_installer' ) );
			add_action( 'admin_notices', array( $this, 'installer_leftover_warning' ) );
		}
		add_action( 'wp_loaded', array( &$this, 'maybe_settle_wpmudev' ) );
	}

	/**
	 * Maybe settle wpmudev.
	 */
	public function maybe_settle_wpmudev() {
		if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			return;
		}
		if ( ! shipper_user_can_ship() ) {
			return;
		}

		$analytics_enabled = WPMUDEV_Dashboard::$site->get_option( 'analytics_enabled' );
		$analytics_site_id = WPMUDEV_Dashboard::$site->get_option( 'analytics_site_id' );
		if ( $analytics_enabled && ! $analytics_site_id ) {
			// generate one.
			WPMUDEV_Dashboard::$api->analytics_enable();
		}
	}

	/**
	 * Remove shipper-working & installer.php
	 */
	public function cleanup_installer() {
		if ( ! shipper_user_can_ship() ) {
			return;
		}

		$post_data = wp_unslash( $_POST );

		if ( empty( $post_data['_wpnonce'] ) || ! wp_verify_nonce( $post_data['_wpnonce'], 'shipper-installer-cleanup' ) ) {
			return;
		}

		$working_path   = ABSPATH . 'shipper-working';
		$installer_path = ABSPATH . 'installer.php';
		if ( is_dir( $working_path ) ) {
			$status = Shipper_Helper_Fs_Path::rmdir_r( $working_path, null );
			if ( $status ) {
				// we remove all the insider, now just need to remove the folder.
				@rmdir( $working_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- intentional
			}
		}
		if ( file_exists( $installer_path ) ) {
			@unlink( $installer_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- intentional
		}
		$path  = ABSPATH;
		$files = glob( "{$path}*.shipper.zip" );
		foreach ( $files as $file ) {
			@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- intentional
		}
	}

	/**
	 * Is install files leftover.
	 *
	 * @return bool
	 */
	private function is_install_files_leftover() {
		$working_path   = ABSPATH . 'shipper-working';
		$installer_path = ABSPATH . 'installer.php';
		if ( is_dir( $working_path ) || file_exists( $installer_path ) ) {
			return true;
		}
		$path  = ABSPATH;
		$files = glob( "{$path}*.shipper.zip" );
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Show a warning if we found the installer still untouch
	 */
	public function installer_leftover_warning() {
		if ( ! $this->is_install_files_leftover() ) {
			return;
		}
		$class       = 'notice notice-error';
		$message     = esc_html__( 'We’ve found the installer files on this site. We highly recommend running the cleanup to delete all the installer files as soon as the migration is finished since they contain some sensitive information about your site. ', 'shipper' );
		$nonce_field = wp_nonce_field( 'shipper-installer-cleanup' );
		$button      = '<button class="button button-primary" type="submit">' . esc_html__( 'Run cleanup', 'shipper' ) . '</button>';
		$message    .= sprintf( '<form method="post">%1$s %2$s</form>', $nonce_field, $button );
		wp_kses_post( sprintf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ) );
	}

	/**
	 * Sets up menu items
	 *
	 * Also sets up front-end dependencies loading on page load.
	 */
	public function add_menu() {
		$capability = $this->get_capability();
		if ( ! $this->can_user_access_shipper_pages() ) {
			return false;
		}

		$packages = add_submenu_page(
			'shipper',
			_x( 'Package Migration', 'page label', 'shipper' ),
			_x( 'Package Migration', 'menu label', 'shipper' ),
			$capability,
			'shipper-packages',
			array( $this, 'page_packages' )
		);
		add_action( "load-{$packages}", array( $this, 'add_packages_dependencies' ) );
		add_action( "load-{$packages}", array( $this, 'save_settings' ) );
	}

	/**
	 * Saves the submitted settings
	 */
	public function save_settings() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$tool = false;
		if ( ! empty( $_GET['tool'] ) ) {
			$tool = sanitize_text_field( wp_unslash( $_GET['tool'] ) );
		}
		if ( 'settings' !== $tool ) {
			return false;
		}

		$data = wp_unslash( $_POST );

		if ( empty( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'shipper-settings' ) ) {
			return false;
		}

		$model = new Shipper_Model_Stored_Options();

		if ( isset( $data['database-use-binary'] ) ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_PACKAGE_DB_BINARY,
				! empty( $data['database-use-binary'] )
			);
		}

		if ( ! empty( $data['database-export-rows'] ) ) {
			$rows = (int) $data['database-export-rows'];
			if ( ! empty( $rows ) ) {
				$model->set(
					Shipper_Model_Stored_Options::KEY_PACKAGE_DB_LIMIT,
					$rows
				);
			}
		}

		if ( isset( $data['archive-use-binary'] ) ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_PACKAGE_ZIP_BINARY,
				! empty( $data['archive-use-binary'] )
			);
		}

		if ( ! empty( $data['archive-buffer-size'] ) ) {
			$buff = (int) $data['archive-buffer-size'];
			if ( ! empty( $buff ) ) {
				$model->set(
					Shipper_Model_Stored_Options::KEY_PACKAGE_ZIP_LIMIT,
					$buff
				);
			}
		}

		if ( isset( $data['safe-mode'] ) ) {
			$model->set( Shipper_Model_Stored_Options::KEY_PACKAGE_SAFE_MODE, (bool) $data['safe-mode'] );
		}

		$model->save();
		wp_safe_redirect( esc_url_raw( add_query_arg( 'saved', true ) ) );
		die();
	}

	/**
	 * Renders the packages main page
	 */
	public function page_packages() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$tool     = 'migration';
		$get_data = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $get_data['tool'] ) ) {
			$tool = sanitize_text_field( $get_data['tool'] );
		}

		// Clear abandoned packages.
		$model = new Shipper_Model_Stored_Package();
		$meta  = new Shipper_Model_Stored_PackageMeta();
		if ( $model->has_package() && ! $model->get( Shipper_Model_Stored_Package::KEY_CREATED ) ) {
			$model->clear()->save();
			$path = Shipper_Model_Fs_Package::get_root_path();
			Shipper_Helper_Fs_Path::rmdir_r( $path, '' );
			@rmdir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- intentional
		}
		$show_flash = false;
		if ( $meta->get( 'show_flash' ) ) {
			$show_flash = true;
			$meta->set( 'show_flash', false );
			$meta->save();
		}

		$tpl = new Shipper_Helper_Template();
		$tpl->render(
			'pages/packages/main',
			array(
				'current_tool' => $tool,
				'show_flash'   => $show_flash,
			)
		);
	}

	/**
	 * Adds front-end dependencies specific for the packages page
	 */
	public function add_packages_dependencies() {
		if ( ! shipper_user_can_ship() ) {
			return false;
		}
		$this->add_shared_dependencies();
	}
}