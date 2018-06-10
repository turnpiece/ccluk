<?php
if( !class_exists("Hustle_Upgrade_Free_Admin") ):

/**
 * Class Hustle_Upgrade_Free_Admin
 */
class Hustle_Upgrade_Free_Admin {

	private $_hustle;

	function __construct( Opt_In $hustle ){

		$this->_hustle = $hustle;

		add_action( 'admin_menu', array( $this, "register_admin_menu" ) );
		add_action( 'admin_head', array( $this, "hide_unwanted_submenus" ) );

	}

	/**
	 * Registers admin menu page
	 *
	 * @since 1.0
	 */
	function register_admin_menu() {

		add_submenu_page( 'hustle', __("Upgrade", Opt_In::TEXT_DOMAIN) , __("Upgrade", Opt_In::TEXT_DOMAIN) , "manage_options", Hustle_Module_Admin::UPGRADE_PAGE,  array( $this, "render_upgrade_free_page" )  );

	}

	/**
	 * Removes the submenu entries for content creation
	 *
	 * @since 2.0
	 */
	function hide_unwanted_submenus(){
		remove_submenu_page( 'hustle', Hustle_Module_Admin::UPGRADE_PAGE );
	}

	/**
	 * Renders upgrade free info
	 *
	* @since 3.0
	 */
	function render_upgrade_free_page( ) {

		$this->_hustle->render( 'admin/new-free-info', array(
			'page_title' => __( 'Upgrade', Opt_In::TEXT_DOMAIN ),
		));
	}
}

endif;