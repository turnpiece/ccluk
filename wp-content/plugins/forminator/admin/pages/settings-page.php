<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Settings_Page
 *
 * @since 1.0
 */
class Forminator_Settings_Page extends Forminator_Admin_Page {

	/**
	 * Addons data that will be sent to settings page
	 *
	 * @var array
	 */
	private $addons_data = array();
	public  $addons_list = array();

	public function enqueue_scripts( $hook ) {
		parent::enqueue_scripts( $hook );
		wp_localize_script( 'forminator-admin', 'forminator_addons_data', $this->addons_data );
	}

	public function before_render() {
		if ( Forminator::is_addons_feature_enabled() ) {
			$this->prepare_addons();
		}
	}

	private function prepare_addons() {
		// cleanup activated addons
		Forminator_Addon_Loader::get_instance()->cleanup_activated_addons();

		Forminator_Addon_Admin_Ajax::get_instance()->generate_nonce();

		$addons_list = forminator_get_registered_addons_list();

		$this->addons_data = array(
			'addons_list' => $addons_list,
			'nonce'       => Forminator_Addon_Admin_Ajax::get_instance()->get_nonce(),
		);

		$this->addons_list = forminator_get_registered_addons_list();
	}
}