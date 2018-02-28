<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_New_Page
 *
 * @since 1.0
 */
class Forminator_Poll_New_Page extends Forminator_Admin_Page {

	/**
	 * Return wizard title
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function getWizardTitle() {
		if ( isset( $_REQUEST['id'] ) ) {
			return __( "Edit Poll", Forminator::DOMAIN );
		} else {
			return __( "New Poll", Forminator::DOMAIN );
		}
	}
}
