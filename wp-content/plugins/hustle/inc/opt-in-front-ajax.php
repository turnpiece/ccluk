<?php

class Opt_In_Front_Ajax {

    private $_hustle;

    function __construct( Opt_In $hustle ){
        $this->_hustle = $hustle;
        // When optin is viewed
        add_action("wp_ajax_inc_opt_optin_viewed", array( $this, "optin_viewed" ));
        add_action("wp_ajax_nopriv_inc_opt_optin_viewed", array( $this, "optin_viewed" ));

        // When optin form is submitted
        add_action("wp_ajax_inc_opt_submit_opt_in", array( $this, "submit_optin" ));
        add_action("wp_ajax_nopriv_inc_opt_submit_opt_in", array( $this, "submit_optin" ));
    }


    function submit_optin(){
        $data = $_POST['data'];
        parse_str( $data['form'], $form_data );

        if( !is_email( $form_data['inc_optin_email'] ) )
            wp_send_json_error( __("Invalid email address", Opt_In::TEXT_DOMAIN) );

		$subscribe_data = array();

		// Remove inc_optin prefix on module fields
		foreach ( $form_data as $key => $value ) {
			if ( preg_match( '%inc_optin_%', $key ) ) $key = str_replace( 'inc_optin_', '', $key );

			$subscribe_data[ $key ] = $value;
		}

        $e_newsletter_data = array();
        $e_newsletter_data['member_email'] = $subscribe_data['email'];

        if( isset( $form_data['first_name'] ) )
            $e_newsletter_data['member_fname'] = $subscribe_data['f_name'] = $form_data['first_name'];

        if( isset( $form_data['last_name'] ) )
            $e_newsletter_data['member_lname'] = $subscribe_data['l_name'] = $form_data['last_name'];


        $optin = Opt_In_Model::instance()->get( $data['optin_id'] );
		$test_mode = (bool) $optin->test_mode;
		$save_to_collection = (bool) $optin->save_to_collection;

        $optin_type = $data["type"];
        $api_result = false;
        $local_save = false;

        if( $this->_hustle->get_e_newsletter()->is_plugin_active() && $optin->sync_with_e_newsletter ){
            $this->_hustle->get_e_newsletter()->subscribe( $e_newsletter_data, $optin->get_e_newsletter_groups() );
        }

        if( $save_to_collection && !$test_mode ){ // Save to local collection
			$local_subscription_data = wp_parse_args( $subscribe_data, array(
				'optin_type' => $optin_type,
				'time' => current_time( 'timestamp' ),
			) );

            $local_save = $optin->add_local_subscription( $local_subscription_data );

			if ( is_wp_error( $local_save ) ) {
				// Send the error back
				wp_send_json_error( $local_save->get_error_messages() );
			}
        }

        $provider = false;
        if( $optin->optin_provider ){

            $provider = Opt_In::get_provider_by_id( $optin->optin_provider );
            $provider = Opt_In::provider_instance( $provider );

            if( !is_subclass_of( $provider, "Opt_In_Provider_Abstract") && !$test_mode )
               wp_send_json_error( __("Invalid provider", Opt_In::TEXT_DOMAIN) );

        } else if ( $local_save ) {
			// if no provider and was able to save it locally
			$this->log_conversion($optin, $data);
			wp_send_json_success( $local_save );
		}


        if( $provider ) {
			$subscribe_data = $this->pre_process_fields( $subscribe_data );
            $api_result = $provider->subscribe( $optin, $subscribe_data );
        }

        if( ( $api_result && !is_wp_error( $api_result ) ) && ( !$local_save || !is_wp_error( $local_save ) )  ){
			$this->log_conversion($optin, $data);
            $message = $api_result ? $api_result : $local_save;
            wp_send_json_success( $message );
        }

        $collected_errs_messages = array();
        if( is_wp_error( $api_result )  )
            $collected_errs_messages = $api_result->get_error_messages();

        if( is_wp_error( $local_save )  ) {
            $collected_errs_messages = array_merge( $collected_errs_messages, $local_save->get_error_messages() );
		}

        if( $collected_errs_messages !== array()  ){
            wp_send_json_error( $collected_errs_messages);
        }

        wp_send_json_error( $api_result );
    }

    function optin_viewed(){
        $data = $_REQUEST['data'];

        $optin_id = is_array( $data ) ?  $data['optin_id'] : null;
        $optin_type = is_array( $data ) ?  $data['type'] : null;

        if( empty( $optin_id ) )
            wp_send_json_error( __("Invalid Request: Opt-in id invalid") );

        $optin = Opt_In_Model::instance()->get( $optin_id );

         $res = $optin->log_view( array(
            'page_type' => $data['page_type'],
            'page_id'   => $data['page_id'],
            'optin_id' => $optin_id,
             'uri' => $data['uri']
        ), $optin_type );

        if( is_wp_error( $res ) || empty( $data ) )
            wp_send_json_error( __("Error saving stats") );
        else
            wp_send_json_success( __("Stats Successfully saved") );

    }

	function log_conversion( $optin, $data ) {
		$optin_type = ( isset( $data['type'] ) ) ? $data['type'] : '';
		$tracking_types = $optin->get_tracking_types();
		if ( $tracking_types && ( (bool) $tracking_types[$optin_type] ) ) {
			$optin->log_conversion( array(
				'page_type' => $data['page_type'],
				'page_id'   => $data['page_id'],
				'optin_id' => $optin->id
			), $optin_type );
		}
	}

	function pre_process_fields( $data ) {
		$newdata = array();

		foreach ( $data as $key => $value ) {
			$_key = str_replace( 'inc_optin_', '', $key );
			$newdata[ $_key ] = $value;
		}

		return $newdata;
	}
}