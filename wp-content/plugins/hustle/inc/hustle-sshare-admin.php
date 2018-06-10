<?php

if( !class_exists("Hustle_SShare_Admin") ):

class Hustle_SShare_Admin extends Opt_In {

	function __construct() {

		add_action( 'admin_init', array( $this, "check_free_version" ) );
		add_action( 'admin_menu', array( $this, "register_admin_menu" ) );
		add_action( 'admin_head', array( $this, "hide_unwanted_submenus" ) );
		add_filter("hustle_optin_vars", array( $this, "register_current_json" ) );
	}

	function register_admin_menu() {
		// Social Sharings
		add_submenu_page( 'hustle', __("Social Sharing", Opt_In::TEXT_DOMAIN) , __("Social Sharing", Opt_In::TEXT_DOMAIN) , "manage_options", Hustle_Module_Admin::SOCIAL_SHARING_LISTING_PAGE,  array( $this, "render_sshare_listing" )  );
		add_submenu_page( 'hustle', __("New Social Sharing", Opt_In::TEXT_DOMAIN) , __("New Social Sharing", Opt_In::TEXT_DOMAIN) , "manage_options", Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE,  array( $this, "render_sshare_wizard_page" )  );
	}

	/**
	 * Removes the submenu entries for content creation
	 *
	 * @since 3.0
	 */
	function hide_unwanted_submenus(){
		remove_submenu_page( 'hustle', Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE );
	}

	/**
	 * Renders menu page based on if we already any optin
	 *
	* @since 3.0
	 */
	function render_sshare_wizard_page() {
		$module_id = filter_input( INPUT_GET, "id", FILTER_VALIDATE_INT );
		$provider = filter_input( INPUT_GET, "provider" );
		$current_section = Hustle_Module_Admin::get_current_section();

		$this->render( "/admin/sshare/wizard", array(
			'section' => ( !$current_section ) ? 'services' : $current_section,
			'is_edit' => Hustle_Module_Admin::is_edit(),
			'module_id' => $module_id,
			'module' => $module_id ? Hustle_SShare_Model::instance()->get( $module_id ) : $module_id,
			'widgets_page_url' => get_admin_url(null, 'widgets.php'),
			'save_nonce' => wp_create_nonce('hustle_save_sshare_module'),
		));
	}

	/**
	 * Check if using free version then redirect to upgrade page
	 *
	* @since 3.0
	 */
	function check_free_version() {
		if (  isset( $_GET['page'] ) && $_GET['page'] == Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE ) {
			$collection_args = array( 'module_type' => 'social_sharing' );
			$total_sshares = count(Hustle_Module_Collection::instance()->get_all( null, $collection_args ));
			if ( Opt_In_Utils::_is_free() && ! Hustle_Module_Admin::is_edit() && $total_sshares >= 1 ) {
				wp_safe_redirect( 'admin.php?page=' . Hustle_Module_Admin::UPGRADE_PAGE );
				exit;
			}
		}
	}

	/**
	 * Renders Social Sharing listing page
	 *
	 * @since 3.0
	 */
	function render_sshare_listing(){
		$current_user = wp_get_current_user();
		$new_module = isset( $_GET['module'] ) ? Hustle_SShare_Model::instance()->get( intval($_GET['module'] ) ) : null;
		$updated_module = isset( $_GET['updated_module'] ) ? Hustle_SShare_Model::instance()->get( intval($_GET['updated_module'] ) ) : null;
		$types = Hustle_SShare_Model::get_types();

		$this->render("admin/sshare/listing", array(
			'sshares' => Hustle_Module_Collection::instance()->get_all( null, array( 'module_type' => 'social_sharing' ) ),
			'new_module' =>  $new_module,
			'updated_module' =>  $updated_module,
			'types' => $types,
			'add_new_url' => admin_url( "admin.php?page=" . Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE ),
			'user_name' => ucfirst($current_user->display_name)
		));
	}

	private function _is_edit(){
		return  (bool) filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) && isset( $_GET['page'] ) && $_GET['page'] === Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE;
	}

	function register_current_json( $current_array ){
		if( Hustle_Module_Admin::is_edit() && isset( $_GET['page'] ) && $_GET['page'] == Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE ){

			$ss = Hustle_SShare_Model::instance()->get( filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) );
			$all_ss = Hustle_Module_Collection::instance()->get_all( null, array( 'module_type' => 'social_sharing' ) );
			$total_ss = count($all_ss);
			$current_section = Hustle_Module_Admin::get_current_section();
			$current_array['current'] = array(
				'listing_page' => Hustle_Module_Admin::SOCIAL_SHARING_LISTING_PAGE,
				'wizard_page' => Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE,
				'content' => $ss->get_sshare_content()->to_array(),
				'design' => $ss->get_sshare_design()->to_array(),
				'settings' => $ss->get_sshare_display_settings()->to_array(),
				'types' => $ss->get_sshare_display_types()->to_array(),
				'section' => ( !$current_section ) ? 'services' : $current_section,
				'is_ss_limited' => (int) ( Opt_In_Utils::_is_free() && '-1' === $_GET['id'] && $total_ss >= 1 )
			);
		}

		return $current_array;
	}

	/**
	 * Saves new optin to db
	 *
	 * @since 3.0
	 *
	 * @param $data
	 * @return mixed
	 */
	public function save_new( $data ){
		$module = new Hustle_SShare_Model();

		// save to modules table
		$module->module_name = $data['module']['module_name'];
		$module->module_type = Hustle_SShare_Model::SOCIAL_SHARING_MODULE;
		$module->active = (int) $data['module']['active'];
		$module->test_mode = (int) $data['module']['test_mode'];
		$module->save();

		// save to meta table
		$module->add_meta( $this->get_const_var( "KEY_CONTENT", $module ), $data['content'] );
		$module->add_meta( $this->get_const_var( "KEY_DESIGN", $module ), $data['design'] );
		$module->add_meta( $this->get_const_var( "KEY_SETTINGS", $module ), $data['settings'] );
		$module->add_meta( $this->get_const_var( "KEY_SHORTCODE_ID", $module ),  $data['shortcode_id'] );

		return $module->id;

	}


	public function update_module( $data ){
		if( !isset( $data['id'] ) ) return false;

		$module = Hustle_SShare_Model::instance()->get( $data['id'] );

		// save to modules table
		$module->module_name = $data['module']['module_name'];
		$module->module_type = Hustle_SShare_Model::SOCIAL_SHARING_MODULE;
		$module->active = (int) $data['module']['active'];
		$module->test_mode = (int) $data['module']['test_mode'];
		$module->save();

		// save to meta table
		$module->update_meta( $this->get_const_var( "KEY_CONTENT", $module ), $data['content'] );
		$module->update_meta( $this->get_const_var( "KEY_DESIGN", $module ), $data['design'] );
		$module->update_meta( $this->get_const_var( "KEY_SETTINGS", $module ), $data['settings'] );
		$module->update_meta( $this->get_const_var( "KEY_SHORTCODE_ID", $module ), $data['shortcode_id'] );

		return $module->id;
	}
}

endif;