<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_CForm_New_Page
 *
 * @since 1.0
 */
class Forminator_CForm_New_Page extends Forminator_Admin_Page {

	/**
	 * Get wizard title
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function getWizardTitle() {
		if ( isset( $_REQUEST['id'] ) ) { // WPCS: CSRF OK
			return __( "Edit Form", Forminator::DOMAIN );
		} else {
			return __( "New Form", Forminator::DOMAIN );
		}
	}
}