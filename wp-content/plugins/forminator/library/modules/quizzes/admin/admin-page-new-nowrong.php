<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizz_New_NoWrong
 *
 * @since 1.0
 */
class Forminator_Quizz_New_NoWrong extends Forminator_Admin_Page {

	/**
	 * Return wizard title
	 *
	 * @since 1.0
	 */
	public function getWizardTitle() {
		if ( isset( $_REQUEST['id'] ) ) {
			return __( "Edit Quiz", Forminator::DOMAIN );
		} else {
			return __( "New Quiz", Forminator::DOMAIN );
		}
	}
}