<?php
/**
 * Shipper controllers: admin tools page
 *
 * @since v1.0.3
 * @package shipper
 */

/**
 * Admin pages controller, tools page
 */
class Shipper_Controller_Admin_Tools extends Shipper_Controller_Admin {

	/**
	 * Gets order in which menu registration takes place
	 *
	 * @return int Page order
	 */
	public function get_page_order() {
		return parent::get_page_order() + 4;
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

		$tools = add_submenu_page(
			'shipper',
			_x( 'Tools', 'page label', 'shipper' ),
			_x( 'Tools', 'menu label', 'shipper' ),
			$capability,
			'shipper-tools',
			array( $this, 'page_tools' )
		);
		add_action( "load-{$tools}", array( $this, 'add_tools_dependencies' ) );
	}

	/**
	 * Renders the tools page
	 */
	public function page_tools() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$tool = 'logs';
		$get  = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $get['tool'] ) ) {
			$tool = sanitize_text_field( $get['tool'] );
		}

		$tpl = new Shipper_Helper_Template();
		$tpl->render( 'pages/tools/main', array( 'current_tool' => $tool ) );
	}

	/**
	 * Adds front-end dependencies specific for the tools page
	 */
	public function add_tools_dependencies() {
		if ( ! shipper_user_can_ship() ) {
			return false; }
		$this->add_shared_dependencies();
	}
}