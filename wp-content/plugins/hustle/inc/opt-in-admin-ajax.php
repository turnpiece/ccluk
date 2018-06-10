<?php
if( !class_exists("Opt_In_Admin_Ajax") ):
/**
 * Class Opt_In_Admin_Ajax
 * Takes care of all the ajax calls to admin pages
 *
 */
class Opt_In_Admin_Ajax {

    private $_hustle;
    private $_admin;

    function __construct( Opt_In $hustle, Opt_In_Admin $admin ){

        $this->_hustle = $hustle;
        $this->_admin = $admin;

        add_action("wp_ajax_render_provider_account_options", array( $this, "render_provider_account_options" ));
        add_action("wp_ajax_refresh_provider_account_details", array( $this, "refresh_provider_account_details" ));
        add_action("wp_ajax_inc_opt_save_new", array( $this, "save_optin" ));
        add_action("wp_ajax_inc_opt_prepare_custom_css", array( $this, "prepare_custom_css" ));
        add_action("wp_ajax_inc_opt_toggle_state", array( $this, "toggle_optin_state" ));
        add_action("wp_ajax_inc_optin_toggle_tracking_activity", array( $this, "toggle_tracking_activity" ));
        add_action("wp_ajax_inc_opt_toggle_optin_type_state", array( $this, "toggle_optin_type_state" ));
        add_action("wp_ajax_inc_opt_toggle_type_test_mode", array( $this, "toggle_type_test_mode" ));
        add_action("wp_ajax_inc_opt_delete_optin", array( $this, "delete_optin" ));
        add_action("wp_ajax_inc_optin_get_email_lists", array( $this, "get_subscriptions_list" ));
        add_action("wp_ajax_inc_optin_export_subscriptions", array( $this, "export_subscriptions" ));
        add_action("wp_ajax_persist_new_welcome_close", array( $this, "persist_new_welcome_close" ));
		add_action("wp_ajax_add_module_field", array( $this, "add_module_field" ) );
		add_action( "wp_ajax_get_error_list", array( $this, "get_error_list" ) );
		add_action( "wp_ajax_clear_logs", array( $this, "clear_logs" ) );
		add_action( "wp_ajax_export_error_logs", array( $this, "export_error_logs" ) );
		add_action( "wp_ajax_sshare_show_page_content", array( $this, "sshare_show_page_content" ) );
		add_action( "wp_ajax_update_hubspot_referrer", array( $this, "update_hubspot_referrer" ) );
        add_action( "wp_ajax_update_constantcontact_referrer", array( $this, "update_constantcontact_referrer" ) );
    }

    /**
     * Renders provider account options based on the selected provider ( provider id )
     *
     * @since 1.0
     */
    function render_provider_account_options(){

        Opt_In_Utils::validate_ajax_call( "change_provider_name" );

        $provider_id =  filter_input( INPUT_GET, "provider_id" );

        $optin_id =  filter_input( INPUT_GET, "optin" );

        if( empty( $provider_id ) )  wp_send_json_error( __("Invalid provider", Opt_In::TEXT_DOMAIN) );

        /**
         * @var $provider Opt_In_Provider_Interface
         */
        $provider = Opt_In::get_provider_by_id( $provider_id );
        $is_allowed = $this->_is_provider_allowed_to_run( $provider );
        if( is_wp_error( $is_allowed )  ){
            wp_send_json_error( $is_allowed->get_error_messages() );
        }

        $provider = Opt_In::provider_instance( $provider );

        $options = $provider->is_authorized() ? $provider->get_account_options( $optin_id ) : $provider->get_options();

        $html = "";
        foreach( $options as $key =>  $option ){
            $html .= $this->_hustle->render("general/option", array_merge( $option, array( "key" => $key ) ), true);
        }

        wp_send_json_success( $html );
    }

    /**
     * Refreshes provider account details after the account creds are added and submitted
     *
     * @since 1.0
     */
    function refresh_provider_account_details(){

        Opt_In_Utils::validate_ajax_call( "refresh_provider_details" );

        $provider_id =  filter_input( INPUT_POST, "optin_new_provider_name" );

        $optin_id =  filter_input( INPUT_POST, "optin" );

        if( empty( $provider_id ) )  wp_send_json_error( __("Invalid provider", Opt_In::TEXT_DOMAIN) );

        $api_key =  filter_input( INPUT_POST, "optin_api_key" );
        /**
         * @var $provider Opt_In_Provider_Interface
         */
        $provider = Opt_In::get_provider_by_id( $provider_id );

        /**
         * @var $provider Opt_In_Provider_Abstract
         */
        $provider = Opt_In::provider_instance( $provider );

        $provider->set_arg( "api_key", $api_key );

        if( filter_input( INPUT_POST, "optin_secret_key" ) )
            $provider->set_arg( "secret", filter_input( INPUT_POST, "optin_secret_key" ) );
        if( filter_input( INPUT_POST, "optin_username" ) )
            $provider->set_arg( "username", filter_input( INPUT_POST, "optin_username" ) );
		if ( filter_input( INPUT_POST, "optin_password" ) )
			$provider->set_arg( "password", filter_input( INPUT_POST, "optin_password" ) );

        if( filter_input( INPUT_POST, "optin_account_name" ) )
            $provider->set_arg( "account_name", filter_input( INPUT_POST, "optin_account_name" ) );

        if( filter_input( INPUT_POST, "optin_url" ) )
            $provider->set_arg( "url", filter_input( INPUT_POST, "optin_url" ) );

        $options = $provider->get_options( $optin_id );

        if( !empty( $options ) )
            $provider->update_option( Opt_In::get_const( $provider, 'LISTS' ), serialize( $options ) );


        if( !is_wp_error( $options ) ){
            $html = "";

            foreach( $options as $key =>  $option ){
                $html .= $this->_hustle->render("general/option", array_merge( $option, array( "key" => $key ) ), true);
            }

            wp_send_json_success( $html );
        }else{
            /**
             * @var WP_Error $options
             */
            wp_send_json_error( implode( "<br/>", $options->get_error_messages() ) );
        }

    }

    /**
     * Prepares the custom css string for the live previewer
     *
     * @since 1.0
     */
    function prepare_custom_css(){

        Opt_In_Utils::validate_ajax_call( "inc_opt_prepare_custom_css" );

        $_POST = stripslashes_deep( $_POST );
        if( !isset($_POST['css'] ) ) {
            wp_send_json_error();
        }

        $cssString = $_POST['css'];

        $styles = Opt_In::prepare_css($cssString, ".wph-preview--holder");

        $optin_id = isset( $_POST['optin_id'] ) ? (int) $_POST['optin_id'] : false;

        if( !empty($optin_id) ){
            $optin = Opt_In_Model::instance()->get( $optin_id );
            $design = $optin->design->to_object();
            $design->css = $cssString;
            $optin->update_meta( $this->_hustle->get_const_var(  "KEY_DESIGN", $optin ),  $design );
        }

        wp_send_json_success( $styles );
    }

    /**
     * Saves new optin to db
     *
     * @since 1.0
     */
    function save_optin(){

        Opt_In_Utils::validate_ajax_call( "hustle_save_optin" );

        $_POST = stripslashes_deep( $_POST );
        if( "-1" === $_POST['id']  )
            $res = $this->_admin->save_new( $_POST );
        else
            $res = $this->_admin->update_optin( $_POST );

        wp_send_json( array(
            "success" =>  $res === false ? false: true,
            "data" => $res
        ) );
    }


    /**
     * Toggles optin active state
     *
     * @since 1.0
     */
    function toggle_optin_state(){

        Opt_In_Utils::validate_ajax_call( "inc_opt_toggle_state" );

        $id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

        if( !$id )
            wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

        $result = Opt_In_Model::instance()->get($id)->toggle_state();

        if( $result )
            wp_send_json_success( __("Successful") );
        else
            wp_send_json_error( __("Failed") );
    }

    function toggle_tracking_activity(){

        Opt_In_Utils::validate_ajax_call( "optin-toggle-tracking-activity" );

        $id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
        $type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

        if( !$id || !$type )
            wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));


        if( !is_object( Opt_In_Model::instance()->get($id)->settings->{$type} ) )
            wp_send_json_error(__("Invalid environment: " . $type, Opt_In::TEXT_DOMAIN));

        $result = Opt_In_Model::instance()->get($id)->toggle_type_track_mode( $type );

        if( $result && !is_wp_error( $result ) )
            wp_send_json_success( __("Successful") );
        else
            wp_send_json_error( $result->get_error_message() );
    }

    /**
     * Toggles optin type active state
     *
     * @since 1.0
     */
    function toggle_optin_type_state(){

        Opt_In_Utils::validate_ajax_call( "inc_opt_toggle_optin_type_state" );

        $id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
        $type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

        if( !$id || !$type )
            wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));



        if( !is_object( Opt_In_Model::instance()->get($id)->settings->{$type} ) )
            wp_send_json_error(__("Invalid environment: " . $type, Opt_In::TEXT_DOMAIN));

        $result = Opt_In_Model::instance()->get($id)->toggle_state( $type );

        if( $result && !is_wp_error( $result ) )
            wp_send_json_success( __("Successful") );
        else
            wp_send_json_error( $result->get_error_message() );
    }

    /**
     * Toggles optin type test mode
     *
     * @since 1.0
     */
    function toggle_type_test_mode(){

        Opt_In_Utils::validate_ajax_call( "inc_opt_toggle_type_test_mode" );

        $id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
        $type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

        if( !$id || !$type )
            wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));


        if( !is_object( Opt_In_Model::instance()->get($id)->settings->{$type} ) )
            wp_send_json_error(__("Invalid environment: " . $type, Opt_In::TEXT_DOMAIN));

        $result = Opt_In_Model::instance()->get($id)->toggle_type_test_mode( $type );

        if( $result && !is_wp_error( $result ) )
            wp_send_json_success( __("Successful") );
        else
            wp_send_json_error( $result->get_error_message() );
    }

    /**
     * Delete optin
     */
    function delete_optin(){

        Opt_In_Utils::validate_ajax_call( "inc_opt_delete_optin" );

        $id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

        if( !$id )
            wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

        $result = Opt_In_Model::instance()->get($id)->delete();

        if( $result )
            wp_send_json_success( __("Successful") );
        else
            wp_send_json_error( __("Failed") );
    }

    /**
     * Checks conditions required to run given provider
     *
     * @param $provider
     * @return bool|WP_Error
     */
    private function _is_provider_allowed_to_run($provider ){
        $err = new WP_Error();
        if( 'Opt_In_ConstantContact' === $provider && version_compare( PHP_VERSION, '5.3', '<' ) ){
            $err->add("Constant Contact Not Allowed", __("This provider requires PHP5.3+ and can't be used with current server. Please upgrade to use this provider.", Opt_In::TEXT_DOMAIN) );
            return $err;
        }

        return true;
    }

    /**
     * Retrieves the subscription list from db
     *
     *
     * @since 1.1.0
     */
    function get_subscriptions_list(){
        Opt_In_Utils::validate_ajax_call("wpoi_get_emails_list");

        $id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

        if( !$id )
            wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

        $subscriptions = Opt_In_Model::instance()->get($id)->get_local_subscriptions();

        if( $subscriptions )
            wp_send_json_success( array(
                "subscriptions" => $subscriptions,
				'module_fields'=> Opt_In_Model::instance()->get($id)->get_design()->__get( 'module_fields' ),
            ) );
		else
            wp_send_json_error( __("Failed to fetch subscriptions", Opt_In::TEXT_DOMAIN) );
    }

	/**
     * Save persistent choice of closing new welcome notice on dashboard
     *
     * @since 2.0.2
     */
	function persist_new_welcome_close() {
		Opt_In_Utils::validate_ajax_call( "hustle_new_welcome_notice" );
		update_option("hustle_new_welcome_notice_dismissed", true);
		wp_send_json_success();
	}


    function export_subscriptions(){
        Opt_In_Utils::validate_ajax_call( 'inc_optin_export_subscriptions' );

        $id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

        if( !$id )
            die(__("Invalid Request", Opt_In::TEXT_DOMAIN));

        $optin = Opt_In_Model::instance()->get($id);
		$module_fields = Opt_In_Model::instance()->get($id)->get_design()->__get( 'module_fields' );
        $subscriptions = $optin->get_local_subscriptions();

		$fields = array();

		foreach ( $module_fields as $field ) {
			$fields[ $field['name'] ] = $field['label'];
		}
		$csv = implode( ', ', $fields ) . "\n";

        foreach( $subscriptions as $row ){
			$subscriber_data = array();

			foreach ( $fields as $key => $label ) {
				// Check for legacy
				if ( isset( $row->f_name ) && 'first_name' == $key ) $key = 'f_name';
				if ( isset( $row->l_name ) && 'last_name' == $key ) $key = 'l_name';

				$subscriber_data[ $key ] = isset( $row->$key ) ? $row->$key : '';
			}
			$csv .= implode( ', ', $subscriber_data ) . "\n";
        }

        $file_name = strtolower( sanitize_file_name( $optin->optin_name ) ) . ".csv";

        header("Content-type: application/x-msdownload",true,200);
        header("Content-Disposition: attachment; filename=$file_name");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $csv;
        die();

    }

	/**
	 * Validate new/updated custom module field.
	 **/
	function add_module_field() {
		Opt_In_Utils::validate_ajax_call( 'optin_add_module_field' );
		$input = stripslashes_deep( $_REQUEST );

		if ( ! empty( $input ) ) {
			$provider = $input['provider'];
			$registered_providers = $this->_hustle->get_providers();
			$can_add = array( 'success' => true, 'field' => $input['field'] );

			if ( isset( $registered_providers[ $provider ] ) ) {
				$provider_class = $registered_providers[ $provider ]['class'];

				if ( class_exists( $provider_class )
					&& method_exists( $provider_class, 'add_custom_field' ) ) {
					$optin = Opt_In_Model::instance()->get( $input['optin_id'] );
					$can_add = call_user_func( array( $provider_class, 'add_custom_field' ), $input['field'], $optin );
				}
			}

			if ( isset( $can_add['success'] ) ) {
				wp_send_json_success( $can_add );
			} else {
				wp_send_json_error( $can_add );
			}
		}
	}

	function get_error_list() {
		Opt_In_Utils::validate_ajax_call( 'optin_get_error_logs' );
		$id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );

		if ( (int) $id > 0 ) {
			$optin = Opt_In_Model::instance()->get( $id );
			$error_log = $optin->get_error_log();
			$module_fields = $optin->get_design()->__get( 'module_fields' );
			wp_send_json_success( array( 'logs' => $error_log, 'module_fields' => $module_fields ) );
		}
		wp_send_json_error(true);
	}

	function clear_logs() {
		Opt_In_Utils::validate_ajax_call( 'optin_clear_logs' );
		$id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );

		if ( (int) $id > 0 ) {
			Opt_In_Model::instance()->get( $id )->clear_error_log();
		}
		wp_send_json_success(true);
	}

	function export_error_logs() {
		Opt_In_Utils::validate_ajax_call( 'optin_export_error_logs' );
		$id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );

		if ( (int) $id > 0 ) {
			$optin = Opt_In_Model::instance()->get( $id );
			$error_log = $optin->get_error_log();
			$module_fields = $optin->get_design()->__get( 'module_fields' );
			$csv = array(array());
			$keys = array();

			foreach ( $module_fields as $field ) {
				$csv[0][] = $field['label'];
				$keys[] = $field['name'];
			}
			$csv[0][] = __( 'Error', Opt_In::TEXT_DOMAIN );
			$csv[0][] = __( 'Date', Opt_In::TEXT_DOMAIN );
			array_push( $keys, 'error', 'date' );

			if ( ! empty( $error_log ) ) {
				foreach ( $error_log as $log ) {
					$logs = array();

					foreach ( $keys as $key ) {
						$logs[ $key ] = sanitize_text_field( $log->$key );
					}
					$csv[] = $logs;
				}
			}

			foreach ( $csv as $index => $_csv ) {
				$csv[ $index ] = implode( ',', $_csv );
			}

			$file_name = strtolower( sanitize_file_name( $optin->optin_name ) ) . "-errors.csv";
			header("Content-type: application/x-msdownload",true,200);
			header("Content-Disposition: attachment; filename=$file_name");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo implode( "\n", $csv );
			die();
		}
		wp_send_json_error(true);
	}

    function sshare_show_page_content() {
		Opt_In_Utils::validate_ajax_call( "hustle_ss_stats_paged_data" );

        $page_id = filter_input( INPUT_POST, 'page_id', FILTER_VALIDATE_INT );
        $offset = ($page_id - 1) * 5;
        $ss_share_stats = Hustle_Social_Sharing_Collection::instance()->get_share_stats( $offset, 5 );

        foreach($ss_share_stats as $key => $ss_stats) {
            $ss_share_stats[$key]->page_url = ( $ss_stats->ID != 0 ) ? esc_url(get_permalink($ss_stats->ID)) : esc_url(get_home_url());
            $ss_share_stats[$key]->page_title = ( $ss_stats->ID != 0 ) ? $ss_stats->post_title : get_bloginfo();
        }

		wp_send_json_success( array(
            'ss_share_stats' => $ss_share_stats
        ) );
	}

	function update_hubspot_referrer() {
		Opt_In_Utils::validate_ajax_call( "hustle_hubspot_referrer" );

		$optin_id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );

		if ( class_exists( 'Opt_In_HubSpot_Api') ) {
			$hubspot = new Opt_In_HubSpot_Api();
			$hubspot->get_authorization_uri( $optin_id );
		}
	}

    function update_constantcontact_referrer() {
        Opt_In_Utils::validate_ajax_call( "hustle_constantcontact_referrer" );

		$optin_id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );
		if ( version_compare( PHP_VERSION, '5.3', '>=' ) && class_exists( 'Opt_In_ConstantContact_Api') ) {
			$constantcontact = new Opt_In_ConstantContact_Api();
			$constantcontact->get_authorization_uri( $optin_id );
		}
    }
}
endif;