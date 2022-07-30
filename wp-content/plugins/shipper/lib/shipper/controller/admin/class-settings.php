<?php
/**
 * Shipper controllers: admin settings page
 *
 * @since v1.0.3
 * @package shipper
 */

/**
 * Admin pages controller, settings page
 */
class Shipper_Controller_Admin_Settings extends Shipper_Controller_Admin {

	/**
	 * Gets order in which menu registration takes place
	 *
	 * @return int Page order
	 */
	public function get_page_order() {
		return parent::get_page_order() + 3;
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

		$settings = add_submenu_page(
			'shipper',
			_x( 'Settings', 'page label', 'shipper' ),
			_x( 'Settings', 'menu label', 'shipper' ),
			$capability,
			'shipper-settings',
			array( $this, 'page_settings' )
		);

		add_action( "load-{$settings}", array( $this, 'add_settings_dependencies' ) );
		add_action( "load-{$settings}", array( $this, 'save_settings' ) );
	}

	/**
	 * Renders the settings page
	 */
	public function page_settings() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$tool = 'notifications';
		$get  = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $get['tool'] ) ) {
			$tool = sanitize_text_field( $get['tool'] );
		}

		$tpl = new Shipper_Helper_Template();
		$tpl->render( 'pages/settings/main', array( 'current_tool' => $tool ) );
	}

	/**
	 * Adds front-end dependencies specific for the settings page
	 */
	public function add_settings_dependencies() {
		if ( ! shipper_user_can_ship() ) {
			return false; }
		$this->add_shared_dependencies();
	}

	/**
	 * Saves the submitted settings
	 */
	public function save_settings() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$tool = false;
		$get  = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $get['tool'] ) ) {
			$tool = sanitize_text_field( $get['tool'] );
		}
		if ( empty( $tool ) ) {
			return false;
		}

		$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $post[ $tool ] ) ) {
			return false; // Nothing to do here.
		}

		if ( empty( $post[ $tool ]['shipper-nonce'] ) ) {
			return false; // Can't validate.
		}

		if ( ! wp_verify_nonce( $post[ $tool ]['shipper-nonce'], "shipper-{$tool}" ) ) {
			return false; // Invalid.
		}

		$model = new Shipper_Model_Stored_Options();

		// A11n.
		if ( 'accessibility' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_A11N,
				! empty( $_POST[ $tool ][ Shipper_Model_Stored_Options::KEY_A11N ] )
			);
		}

		// Preservation settings.
		if ( 'data' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_SETTINGS,
				! empty( $post[ $tool ][ Shipper_Model_Stored_Options::KEY_SETTINGS ] )
			);
			$model->set(
				Shipper_Model_Stored_Options::KEY_DATA,
				! empty( $post[ $tool ][ Shipper_Model_Stored_Options::KEY_DATA ] )
			);
		}

		// Migration settings.
		if ( 'migration' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_UPLOADS,
				! empty( $post[ $tool ][ Shipper_Model_Stored_Options::KEY_UPLOADS ] )
			);
			$model->set(
				Shipper_Model_Stored_Options::KEY_SKIPCONFIG,
				! empty( $post[ $tool ][ Shipper_Model_Stored_Options::KEY_SKIPCONFIG ] )
			);
			$model->set(
				Shipper_Model_Stored_Options::KEY_SKIPEMAILS,
				! empty( $post[ $tool ][ Shipper_Model_Stored_Options::KEY_SKIPEMAILS ] )
			);
		}

		if ( 'pagination' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_PER_PAGE,
				absint( $post[ $tool ][ Shipper_Model_Stored_Options::KEY_PER_PAGE ] )
			);
		}

		// Access control.
		if ( 'permissions' === $tool ) {
			$data     = $post[ $tool ];
			$user_ids = ! empty( $data[ Shipper_Model_Stored_Options::KEY_USER_ACCESS ] )
				? $data[ Shipper_Model_Stored_Options::KEY_USER_ACCESS ]
				: array();
			$user_ids = array_values( array_filter( array_map( 'intval', $user_ids ) ) );
			$model->set(
				Shipper_Model_Stored_Options::KEY_USER_ACCESS,
				$user_ids
			);
		}

		$model->save();
		wp_safe_redirect( esc_url_raw( add_query_arg( 'saved', true ) ) );
		die;
	}
}