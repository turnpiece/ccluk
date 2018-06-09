<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Dashboard_Page
 *
 * @since 1.0
 */
class Forminator_Dashboard_Page extends Forminator_Admin_Page {

	/**
	 * Register content boxes
	 *
	 * @since 1.0
	 */
	public function register_content_boxes()
	{
		$this->add_box(
			'dashboard/create',
			__( 'Create modules', Forminator::DOMAIN ),
			'dashboard-create',
			null,
			array( $this, 'dashboard_create_screen' ),
			null
		);
	}

	/**
	 * Print Dashboard box
	 *
	 * @since 1.0
	 */
	public function dashboard_create_screen() {
		$modules = forminator_get_modules();	    	 	 	 	 	 				 	 
		$this->template('dashboard/create-content', array(
			'modules' => $modules
		) );
	}
}