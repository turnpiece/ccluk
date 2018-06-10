<?php


class Hustle_Settings_Admin_Ajax
{
	private $_hustle;

	private $_admin;

	function __construct( Opt_In $hustle, Hustle_Settings_Admin $admin )
	{
		$this->_hustle = $hustle;
		$this->_admin = $admin;

		add_action("wp_ajax_hustle_toggle_module_for_user", array( $this, "toggle_module_for_user" ));
		add_action("wp_ajax_hustle_get_providers_edit_modal_content", array( $this, "get_providers_edit_modal_content" ));
		add_action("wp_ajax_hustle_save_providers_edit_modal", array( $this, "save_providers_edit_modal" ));
		add_action("wp_ajax_hustle_shortcode_render", array( $this, "shortcode_render" ));
	}

	function toggle_module_for_user(){
		Opt_In_Utils::validate_ajax_call("hustle_modules_toggle");

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$user_type = filter_input( INPUT_POST, 'user_type', FILTER_SANITIZE_STRING );

		$module = Hustle_Module_Model::instance()->get( $id );

		$result = $module->toggle_activity_for_user( $user_type );

		if( is_wp_error( $result ) )
			wp_send_json_error( $result->get_error_messages() );

		wp_send_json_success( sprintf( __("Successfully toggled for user type %s", Opt_In::TEXT_DOMAIN), $user_type ) );
	}

	function get_providers_edit_modal_content(){
		Opt_In_Utils::validate_ajax_call("hustle_edit_providers");

		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		$source = filter_input( INPUT_GET, 'source', FILTER_SANITIZE_STRING );

		if( !$id || !$source )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));


		if( $source === "optin" ){
			$module = Hustle_Module_Model::instance()->get( $id );

			$html = $this->_hustle->render("admin/settings/providers-edit-modal-content", array(
				"providers" => $this->_hustle->get_providers(),
				// "selected_provider" => $module->optin_provider,
				"optin" => $module
			), true);

			wp_send_json_success( array(
				"html" => $html,
				"provider_options_nonce" => wp_create_nonce("change_provider_name")
			) );
		}


	}

	function save_providers_edit_modal(){
		Opt_In_Utils::validate_ajax_call("hustle-edit-service-save");

		var_dump($_POST);die;
		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$source = filter_input( INPUT_POST, 'source', FILTER_SANITIZE_STRING );

		if( !$id || !$source )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));


		if( $source === "optin" ){
			$module = Hustle_Module_Model::instance()->get( $id );

			$html = $this->_hustle->render("admin/settings/providers-edit-modal-content", array(
				"providers" => $this->_hustle->get_providers(),
				// "selected_provider" => $module->optin_provider,
				"optin" => $module
			), true);

			wp_send_json_success( array(
				"html" => $html,
				"provider_options_nonce" => wp_create_nonce("change_provider_name")
			) );
		}
	}

	function shortcode_render() {
		Opt_In_Utils::validate_ajax_call("hustle_shortcode_render");

		$content = filter_input( INPUT_POST, 'content' );
		$rendered_content = apply_filters( 'the_content', $content );

		wp_send_json_success( array(
			"content" => $rendered_content
		));
	}
}