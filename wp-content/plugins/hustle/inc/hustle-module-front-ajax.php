<?php

class Hustle_Module_Front_Ajax {

	private $_hustle;

	function __construct( Opt_In $hustle ){
		$this->_hustle = $hustle;
		// When module is viewed
		add_action("wp_ajax_module_viewed", array( $this, "module_viewed" ));
		add_action("wp_ajax_nopriv_module_viewed", array( $this, "module_viewed" ));

		// When module form is submitted
		add_action("wp_ajax_module_form_submit", array( $this, "submit_form" ));
		add_action("wp_ajax_nopriv_module_form_submit", array( $this, "submit_form" ));

		// When cta is clicked
		add_action("wp_ajax_hustle_cta_converted", array( $this, "log_cta_conversion" ) );
		add_action("wp_ajax_nopriv_hustle_cta_converted", array( $this, "log_cta_conversion" ) );

		// When SShare is converted to
		add_action("wp_ajax_hustle_sshare_converted", array( $this, "log_sshare_conversion" ) );
		add_action("wp_ajax_nopriv_hustle_sshare_converted", array( $this, "log_sshare_conversion" ) );
	}


	function submit_form(){
		$data = $_POST['data'];
		parse_str( $data['form'], $form_data );

		if( !is_email( $form_data['email'] ) )
			wp_send_json_error( __("Invalid email address", Opt_In::TEXT_DOMAIN) );

		$module = Hustle_Module_Model::instance()->get( $data['module_id'] );

		$module_type = $data['type'];
		$provider = false;
		$api_result = false;
		$local_saved = false;
		$is_save_to_local = (bool) $module->content->save_local_list;
		$is_test_mode = (bool) $module->test_mode;
		$has_active_email_service = (bool) $module->content->active_email_service;

		if( $has_active_email_service ){

			$provider = Opt_In::get_provider_by_id( $module->content->active_email_service );
			$provider = Opt_In::provider_instance( $provider );

			if( !is_subclass_of( $provider, "Opt_In_Provider_Abstract") && !$is_test_mode )
			   wp_send_json_error( __("Invalid provider", Opt_In::TEXT_DOMAIN) );

		}

		if( $is_save_to_local && !$is_test_mode && !$provider){ // Save to local collection
			$local_subscription_data = wp_parse_args( $form_data, array(
				'module_type' => $module_type,
				'time' => current_time( 'timestamp' ),
			) );

			$local_saved = $module->add_local_subscription( $local_subscription_data );

			if ( is_wp_error( $local_saved ) ) {
				// Send the error back
				wp_send_json_error( $local_saved->get_error_messages() );
			}
		}

		if ( $local_saved && !$has_active_email_service ) {
			// if no provider and was able to save it locally
			$this->log_conversion($module, $data);
			wp_send_json_success( $local_saved );
		}

		if( $provider ) {
			$api_result = $provider->subscribe( $module, $form_data );
		}


		if( ( $api_result && !is_wp_error( $api_result ) ) && ( !$local_saved || !is_wp_error( $local_saved ) )  ){
			$this->log_conversion($module, $data);

			if($is_save_to_local){
				$local_subscription_data = wp_parse_args( $form_data, array(
					'module_type' => $module_type,
					'time' => current_time( 'timestamp' ),
				) );

				$local_saved = $module->add_local_subscription( $local_subscription_data );

				if ( is_wp_error( $local_saved ) ) {
					// Send the error back
					wp_send_json_error( $local_saved->get_error_messages() );
				}
			}

			$message = $api_result ? $api_result : $local_saved;
			wp_send_json_success( $message );
		}

		$collected_errs_messages = array();
		if( is_wp_error( $api_result )  )
			$collected_errs_messages = $api_result->get_error_messages();

		if( is_wp_error( $local_saved )  ) {
			$collected_errs_messages = array_merge( $collected_errs_messages, $local_saved->get_error_messages() );
		}

		if( $collected_errs_messages !== array()  ){
			wp_send_json_error( $collected_errs_messages);
		}

		wp_send_json_error( $api_result );
	}

	function log_cta_conversion(){
		$data = json_decode( file_get_contents( 'php://input' ) );
		$data = get_object_vars( $data );

		$module_id = is_array( $data ) ? $data['module_id'] : null;

		if( empty( $module_id ) )
			wp_send_json_error( __("Invalid Request!", Opt_In::TEXT_DOMAIN ) . $module_id );

		$module = Hustle_Module_Model::instance()->get( $module_id );

		$res = new WP_Error();
		if ( $module->id ) {
			$res = $this->log_conversion($module, $data);
		}

		if( is_wp_error( $res ) || empty( $data ) )
			wp_send_json_error( __("Error saving stats", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_success( __("Stats Successfully saved", Opt_In::TEXT_DOMAIN) );
	}

	function log_sshare_conversion(){
		$data = json_decode( file_get_contents( 'php://input' ) );
		$data = get_object_vars( $data );

		$module_id = is_array( $data ) ? $data['module_id'] : null;
		$type = is_array( $data ) ? $data['type'] : null;
		$track = is_array( $data ) ? (bool) $data['track'] : false;
		$source = is_array( $data ) ? $data['source'] : '';
		$service_type = is_array( $data ) ? $data['service_type'] : false;

		if( empty( $module_id ) )
			wp_send_json_error( __("Invalid Request: Invalid Social Sharing ", Opt_In::TEXT_DOMAIN ) . $module_id );

		$ss = Hustle_SShare_Model::instance()->get( $module_id );

		// only update the social counter for Native Social Sharing
		if( $service_type && $service_type == 'native' && $source ) {
			$social = str_replace( '_icon', '', $source );
			$services_content = $ss->get_sshare_content()->to_array();

			if( isset($services_content['social_icons']) && isset($services_content['social_icons'][$social]) ) {
				$social_data = $services_content['social_icons'][$social];
				$social_data['counter'] = ( (int) $social_data['counter'] ) + 1;
				$services_content['social_icons'][$social] = $social_data;
				$ss->update_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $ss ), $services_content );
			}
		}

		$res = new WP_Error();
		if( $ss->id && $track )
			$res = $ss->log_conversion( array(
				'page_type' => $data['page_type'],
				'page_id'   => $data['page_id'],
				'module_id' => $ss->id,
				'uri' => $data['uri'],
				'module_type' => 'social_sharing',
				'source' => $data['source']
			), $type );

			// update meta for social sharing share stats
			$ss->log_share_stats($data['page_id']);

		if( is_wp_error( $res ) || empty( $data ) )
			wp_send_json_error( __("Error saving stats", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_success( __("Stats Successfully saved", Opt_In::TEXT_DOMAIN) );
	}

	function module_viewed(){
		$data = json_decode( file_get_contents( 'php://input' ) );
		$data = get_object_vars( $data );

		$module_id = is_array( $data ) ?  $data['module_id'] : null;
		$module_type = is_array( $data ) ?  $data['module_type'] : null;
		$display_type = is_array( $data ) ?  $data['type'] : null;

		if( empty( $module_id ) )
			wp_send_json_error( __("Invalid Request: Module id invalid") );

		$module = Hustle_Module_Model::instance()->get( $module_id );

		$res = new WP_Error();

		if( $module->id )
			$res = $module->log_view( array(
				'page_type' => $data['page_type'],
				'page_id'   => $data['page_id'],
				'module_id' => $module_id,
				'uri' => $data['uri'],
				'module_type' => $module_type
			), $display_type );

		if( is_wp_error( $res ) || empty( $data ) )
			wp_send_json_error( __("Error saving stats", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_success( __("Stats Successfully saved", Opt_In::TEXT_DOMAIN) );

	}

	function log_conversion( $module, $data ) {
		$module_type = ( isset( $data['type'] ) ) ? $data['type'] : '';
		$tracking_types = $module->get_tracking_types();
		if ( $tracking_types && ( (bool) $tracking_types[$module_type] ) ) {
			$module->log_conversion( array(
				'page_type' => $data['page_type'],
				'page_id'   => $data['page_id'],
				'module_id' => $module->id
			), $module_type );
		}
	}
}