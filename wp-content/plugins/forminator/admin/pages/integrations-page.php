<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Integrations_Page
 *
 * @since 1.1
 */
class Forminator_Integrations_Page extends Forminator_Admin_Page {

	/**
	 * Addon list as array
	 *
	 * @var array
	 */
	public $addons_list = array();

	/**
	 * @var array
	 */
	public $addons_list_grouped_by_connected = array();

	public $addon_nonce = '';

	/**
	 * Executed Action before render the page
	 */
	public function before_render() {
		// cleanup addons on integrations page
		Forminator_Addon_Loader::get_instance()->cleanup_activated_addons();

		$this->addons_list                      = forminator_get_registered_addons_list();
		$this->addons_list_grouped_by_connected = forminator_get_registered_addons_grouped_by_connected();

		Forminator_Addon_Admin_Ajax::get_instance()->generate_nonce();
		$this->addon_nonce = Forminator_Addon_Admin_Ajax::get_instance()->get_nonce();
		add_filter( 'forminator_data', array( $this, "add_addons_js_data" ) );
	}

	public function add_addons_js_data( $data ) {
		if ( Forminator::is_addons_feature_enabled() ) {
			$data['addons']      = forminator_get_registered_addons_list();
			$data['addon_nonce'] = $this->addon_nonce;
		}

		return $data;
	}
}