<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizz_New_Knowledge
 *
 * @since 1.0
 */
class Forminator_Quizz_New_Knowledge extends Forminator_Admin_Page {

	/**
	 * Return wizard title
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function getWizardTitle() {
		if ( isset( $_REQUEST['id'] ) ) { // WPCS: CSRF OK
			return __( "Edit Quiz", Forminator::DOMAIN );
		} else {
			return __( "New Quiz", Forminator::DOMAIN );
		}
	}
}