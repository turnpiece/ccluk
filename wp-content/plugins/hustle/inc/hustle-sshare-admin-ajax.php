<?php

if ( !class_exists('Hustle_SShare_Admin_Ajax', false) ):

class Hustle_SShare_Admin_Ajax {

	private static $_hustle;
	private static $_admin;

	function __construct( $hustle, $admin ) {

		self::$_hustle = $hustle;
		self::$_admin = $admin;

		add_action( 'wp_ajax_hustle_save_sshare_module', array( $this, 'save' ) );
		add_action('wp_ajax_hustle_sshare_module_toggle_state', array( $this, 'toggle_module_state' ));
		add_action('wp_ajax_hustle_sshare_module_toggle_type_state', array( $this, 'toggle_module_type_state' ));
		add_action('wp_ajax_hustle_sshare_toggle_tracking_activity', array( $this, 'toggle_tracking_activity' ));
		add_action('wp_ajax_hustle_sshare_toggle_test_activity', array( $this, 'toggle_test_activity' ));
		add_action('wp_ajax_hustle_sshare_delete', array( $this, 'delete' ));
	}

	function save() {
		Opt_In_Utils::validate_ajax_call( "hustle_save_sshare_module" );

		$_POST = stripslashes_deep( $_POST );

		if( "-1" === $_POST['id']  )
			$res = self::$_admin->save_new( $_POST );
		else
			$res = self::$_admin->update_module( $_POST );

		wp_send_json( array(
			"success" =>  $res === false ? false: true,
			"data" => $res
		) );
	}

	function toggle_module_state(){

		Opt_In_Utils::validate_ajax_call( "sshare_module_toggle_state" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if( !$id )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$result = Hustle_Module_Model::instance()->get($id)->toggle_state();

		if( $result )
			wp_send_json_success( __("Successful") );
		else
			wp_send_json_error( __("Failed") );
	}

	function toggle_module_type_state(){

		Opt_In_Utils::validate_ajax_call( "sshare_toggle_module_type_state" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );
		$enabled = trim( filter_input( INPUT_POST, 'enabled', FILTER_SANITIZE_STRING ) );

		if( !$id || !$type )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$sshare =  Hustle_SShare_Model::instance()->get($id);

		if( !in_array( $type, Hustle_SShare_Model::get_types() ))
			wp_send_json_error(__("Invalid environment: " . $type, Opt_In::TEXT_DOMAIN));

		$settings = $sshare->get_sshare_display_settings()->to_array();
		$test_types = (array) json_decode( $sshare->get_meta( self::$_hustle->get_const_var( "TEST_TYPES", $sshare ) ) );

		if ( isset( $settings[ $type . '_enabled' ] ) ) {
			$settings[ $type . '_enabled' ] = $enabled;
			try {
				// try to save new settings
				$sshare->update_meta( self::$_hustle->get_const_var( "KEY_SETTINGS", $sshare ), $settings );

				if ( isset( $test_types[$type] ) ) {
					// clear test types
					unset($test_types[$type]);
				}
				$sshare->update_meta( self::$_hustle->get_const_var( "TEST_TYPES", $sshare ), $test_types );

				wp_send_json_success( __("Successful") );

			} catch (Exception $e) {
				wp_send_json_error( __("Failed") );
			}
		} else {
			wp_send_json_error( __("Failed") );
		}
	}

	function toggle_tracking_activity(){

		Opt_In_Utils::validate_ajax_call( "sshare_toggle_tracking_activity" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

		if( !$id || !$type )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$ss =  Hustle_SShare_Model::instance()->get($id);

		if( !in_array( $type, Hustle_SShare_Model::get_types() ))
			wp_send_json_error(__("Invalid environment: " . $type, Opt_In::TEXT_DOMAIN));

		$result = $ss->toggle_type_track_mode( $type );

		if( $result && !is_wp_error( $result ) )
			wp_send_json_success( __("Successful") );
		else
			wp_send_json_error( $result->get_error_message() );
	}

	function toggle_test_activity(){
		Opt_In_Utils::validate_ajax_call( "sshare_toggle_test_activity" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

		if( !$id || !$type )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$ss =  Hustle_SShare_Model::instance()->get($id);

		if( !in_array( $type, Hustle_SShare_Model::get_types() ))
			wp_send_json_error(__("Invalid environment: " . $type, Opt_In::TEXT_DOMAIN));

		$result = $ss->toggle_type_test_mode( $type );

		if( $result && !is_wp_error( $result ) )
			wp_send_json_success( __("Successful") );
		else
			wp_send_json_error( $result->get_error_message() );
	}

	function delete(){
		Opt_In_Utils::validate_ajax_call( "social-sharing-delete" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if( !$id  )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$result = Hustle_SShare_Model::instance()->get( $id )->delete();

		if( $result )
			wp_send_json_success( __("Successful") );
		else
			wp_send_json_error( __("Error deleting", Opt_In::TEXT_DOMAIN)  );
	}
}

endif;