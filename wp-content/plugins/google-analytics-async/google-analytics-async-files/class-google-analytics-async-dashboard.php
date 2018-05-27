<?php
/*  Copyright Maniu, Carson McDonald */
class Google_Analytics_Async_Dashboard {
    var $text_domain;
    var $plugin_url;
    var $required_capability;

    var $ready;
    var $oauth_token;
    var $oauth_secret;

	/**
	 * @var Google_Client
	 */
    var $google_client;
    var $google_login;

    var $profile_id = 0;
    var $post;
    var $stats_source = 0;
    var $setting_page = 0;

    var $base_url = 'https://www.googleapis.com/analytics/v2.4/';
    var $account_base_url = 'https://www.googleapis.com/analytics/v2.4/management/';
    var $account_base_url_new = 'https://www.googleapis.com/analytics/v3/management/';

    var $http_code;
    var $error = false;
    var $cache_timeout = 86400;
    var $cache_timeout_personal = 10800;
    var $cache_name = '';
    var $cache = 0;
    var $load_mode = 'soft';
    var $is_network_admin = 0;

    var $date_range;
    var $start_date;
    var $end_date;
    var $type = 0;
    var $filter = array();

    function __construct() {
        global $google_analytics_async;

        $this->text_domain = $google_analytics_async->text_domain;
        $this->plugin_url = $google_analytics_async->plugin_url;

        include_once( 'helpers-google-analytics-async-dashboard.php' );

        //required capability
	    $track_settings = ga_plus_get_track_settings();

	    $this->required_capability = 'manage_options';
	    if ( ! empty( $track_settings['minimum_capability_reports'] ) ) {
		    $this->required_capability = $track_settings['minimum_capability_reports'];
	    }
	    elseif ( ! empty( $track_settings['minimum_role_capability_reports'] ) ) {
		    $this->required_capability = $track_settings['minimum_role_capability_reports'];
	    }

        //setup correct google analytics data source
        $this->is_network_admin = (is_network_admin() || (wp_doing_ajax() && isset($_POST['network_admin']) && $_POST['network_admin'])) ? 1 : 0;

	    $site_settings = ga_plus_get_settings( 'site' );
	    $network_settings = ga_plus_get_settings( 'network' );
	    if (
	    	! $this->is_network_admin
		    && isset( $site_settings['google_login']['logged_in'] )
		    && ! empty( $site_settings['track_settings']['google_analytics_account_id'] )
	    ) {
		    $this->stats_source = 'site';
	    }
        elseif(
	        $network_settings['google_login']['logged_in']
	        && ! empty( $network_settings['track_settings']['google_analytics_account_id'] )
        ) {
	        $this->stats_source = 'network';
        }

        add_action('admin_init', array($this, 'create_tables' ));

        add_action('admin_init', array($this, 'admin_init_handle_google_login2' ), 20);
        add_action('init', array($this, 'init_handle_google_login2' ));

        add_action('admin_init', array($this, 'settings_page_data'), 5);

        if($this->stats_source) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('network_admin_menu', array($this, 'network_admin_menu'));

            add_action('init', array($this, 'init'));

            add_action('plugins_loaded', array($this, 'init_widgets'));
        }

        add_action('admin_notices', array($this,'reauthenticate_notice'));
        add_action('network_admin_notices', array($this,'reauthenticate_notice'));

        add_action('wp_ajax_load_google_analytics', array(&$this, 'load_google_analytics'));
        add_action('wp_ajax_nopriv_load_google_analytics', array(&$this, 'load_google_analytics'));
    }


    function create_tables() {
        global $wpdb;

        $ver = apply_filters('gaplus_ver', 0);
        if(!$ver)
            $ver = get_site_option('gaplus_ver', 0);

        if($ver < 1) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->base_prefix}gaplus_login'" ) != $wpdb->base_prefix.'gaplus_login' ) {

                $table = "CREATE TABLE `{$wpdb->base_prefix}gaplus_login` (
                    `id` int(11) NOT NULL auto_increment,
                    `user_id` varchar(255),
                    `token` longtext,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

                $result = $wpdb->query( $table );
            }

            update_site_option( 'gaplus_ver', 1 );
        }
    }

    function init_widgets() {
        global $google_analytics_async;
        if (
            !empty( $google_analytics_async->network_settings['track_settings']['supporter_only'] )
            && function_exists('is_pro_site')
            && !is_pro_site(get_current_blog_id(), $google_analytics_async->network_settings['track_settings']['supporter_only'])
            && apply_filters('ga_allow_checks', true)
        )
            return;

        include_once 'class-widget-ga-most-popular-content.php';
        add_action( 'widgets_init', function () {
            return register_widget("Google_Analytics_Async_Frontend_Widget");
        } );
    }

    function admin_init() {
        global $pagenow;

        //load only for: dashboard, post type page and correct ajax call
         if(
            current_user_can($this->required_capability) &&
            (
                ($pagenow == 'index.php' && !isset($_GET['page'])) ||
                ($pagenow == 'index.php' && isset($_GET['page']) && empty($_GET['page'])) ||
                ($pagenow == 'index.php' && isset($_GET['page']) && $_GET['page'] == 'google-analytics-statistics') ||
                ($pagenow == 'post.php' && isset($_GET['post'])) ||
                (isset($_POST['action']) && $_POST['action'] == 'load_google_analytics')
            )
        ) {
            $this->set_up_ga_data();
        }
    }
    function init() {
        if( wp_doing_ajax() && (isset($_POST['action']) && $_POST['action'] == 'load_google_analytics') )
            $this->set_up_ga_data();
    }

    function admin_enqueue_scripts($hook) {
        wp_register_script('google_charts_api', 'https://www.google.com/jsapi');
        wp_enqueue_script('google_charts_api');

        wp_register_script('google_analytics_async', $this->plugin_url . 'google-analytics-async-files/ga-async.js', array('jquery','sack', 'google_charts_api'), 330);
        wp_enqueue_script('google_analytics_async');
        //configure parameters for JS
        $this->setup_script_variables('google_analytics_async');

        wp_register_style( 'GoogleAnalyticsAsyncStyle', $this->plugin_url . 'google-analytics-async-files/ga-async.css', array(), 39);
        wp_enqueue_style( 'GoogleAnalyticsAsyncStyle' );
    }

    function setup_script_variables($script_name) {
        $params = array();

        if($this->post)
            $params['post'] = $this->post;
        if(isset($this->cache['chart_visitors']))
            $params['chart_visitors'] = $this->cache['chart_visitors'];
        if($this->type == 'statistics_page' && isset($this->cache['chart_countries']))
            $params['chart_countries'] = $this->cache['chart_countries'];
        $params['type'] = $this->type;
        $params['date_range'] = $this->date_range;
        $params['chart_visitors_title'] = ($this->date_range == 12) ? __('Month', $this->text_domain) : (($this->date_range == 3) ? __('Week', $this->text_domain) : __('Day', $this->text_domain));
        $params['load_mode'] = $this->load_mode;


        if(!is_admin()) {
            $params['ajax_url'] = admin_url('admin-ajax.php');
            $params['problem_loading_data'] = __('Problem loading data', $this->text_domain);
        }
        elseif(is_network_admin())
            $params['network_admin'] = 1;

        wp_localize_script( $script_name, 'ga', $params );
    }

    function admin_menu() {
        $network_settings = ga_plus_get_settings( 'network' );
        if(
            !current_user_can($this->required_capability) ||
            (
                $this->stats_source == 'network'
                && !is_super_admin()
                && !empty($network_settings['track_settings']['supporter_only_reports'])
                && function_exists('is_pro_site')
                && !is_pro_site(get_current_blog_id(), $network_settings['track_settings']['supporter_only_reports'])
            )
        )
            return;

        add_dashboard_page(__('Statistics', $this->text_domain), __('Statistics', $this->text_domain), $this->required_capability, 'google-analytics-statistics', array( $this, 'google_analytics_statistics_page' ) );
    }

    function network_admin_menu() {
        add_dashboard_page(__('Statistics', $this->text_domain), __('Statistics', $this->text_domain), $this->required_capability, 'google-analytics-statistics', array( $this, 'google_analytics_statistics_page' ) );
    }

    function register_google_analytics_dashboard_widget() {
	    $network_settings = ga_plus_get_settings( 'network' );
        if(
            !current_user_can($this->required_capability) ||
            (
                $this->stats_source == 'network'
                && !is_super_admin()
                && !empty( $network_settings['track_settings']['supporter_only_reports'] )
                && function_exists('is_pro_site')
                && !is_pro_site(get_current_blog_id(), $network_settings['track_settings']['supporter_only_reports'])
            )
        )
            return;

        wp_add_dashboard_widget('google_analytics_dashboard', __('Statistics - Last 30 Days', $this->text_domain), array(&$this, 'google_analytics_widget'));
    }

    function register_google_analytics_post_widget() {
	    $network_settings = ga_plus_get_settings( 'network' );
        if(
            !current_user_can($this->required_capability) ||
            (
                $this->stats_source == 'network'
                && !is_super_admin()
                && !empty( $network_settings['track_settings']['supporter_only_reports'] )
                && function_exists('is_pro_site')
                && !is_pro_site(get_current_blog_id(), $network_settings['track_settings']['supporter_only_reports'])
            )
        )
            return;

        $screens = array( 'post', 'page' );

        foreach ( $screens as $screen )
            add_meta_box('google_analytics_dashboard', __('Statistics - Last 30 Days', $this->text_domain), array(&$this, 'google_analytics_widget'), $screen, 'normal');
    }

    function reauthenticate_notice() {
        $site_settings = ga_plus_get_settings( 'site' );
        $network_settings = ga_plus_get_settings( 'network' );
        if(current_user_can('manage_options') && isset($site_settings['google_login']['logged_in']) && $site_settings['google_login']['logged_in'] == '1')
            echo '<div class="error"><p>'.sprintf(__('Google Analytics dashboard statistics require reauthentication. You can do it <a href="%s">here</a> and this message will disappear.', $this->text_domain), admin_url('options-general.php?page=google-analytics')).'</p></div>';

        if(is_super_admin() && isset($network_settings['google_login']['logged_in']) && $network_settings['google_login']['logged_in'] == '1')
            echo '<div class="error"><p>'.sprintf(__('Google Analytics dashboard network statistics require reauthentication. You can do it <a href="%s">here</a> and this message will disappear.', $this->text_domain), network_admin_url('settings.php?page=google-analytics')).'</p></div>';
    }

    function settings_page_data() {
        global $pagenow;

        //load only for: dashboard, post type page and correct ajax call
        if(($pagenow == 'settings.php' || $pagenow == 'options-general.php') && isset($_GET['page']) && $_GET['page'] == 'google-analytics') {
            //this is just for getting accounts for now and then we need current settings... always.
	        $google_login = ga_plus_get_google_login_settings();
	        if ( ga_plus_is_google_login_logged_in() ) {
                $this->oauth_token = $google_login['token'];
                $this->oauth_secret = $google_login['token_secret'];
            }

            $this->google_login = ga_plus_get_google_login_settings();

            $this->setting_page = is_network_admin() ? 'network' : 'site';
        }
    }

    function init_handle_google_login2() {
        //we might be getting authentication code from google - we might need to redirect to ga setting page
        if(isset($_GET['state'])) {
            $state = json_decode(urldecode($_GET['state']), true);
            if(isset($state['gaplus_login']) && wp_verify_nonce( $state['gaplus_login'], 'gaplus_login' )) {
                $code = isset($_GET['code']) ? $_GET['code'] : false;
                $url = $state['orgin'] == 'network' ? network_admin_url('settings.php') : get_admin_url($state['orgin']).'options-general.php';
                $url = $url.'?page=google-analytics&code='.$code.'&gaplus_loggedin='.wp_create_nonce('gaplus_loggedin');

                wp_redirect(esc_url_raw($url));
                exit();
            }
        }
    }

    function admin_init_handle_google_login2() {
        global $wpdb;

        //lets verify nonces for certain actions righ away
        if(isset($_GET['gaplus_loggedin']) && !wp_verify_nonce($_GET['gaplus_loggedin'], 'gaplus_loggedin'))
            return;
        if(isset($_POST['gaplus_access_by_api']) && !wp_verify_nonce($_POST['gaplus_access_by_api'], 'gaplus_access_by_api'))
            return;
        if(isset($_POST['gaplus_access_by_code']) && !wp_verify_nonce($_POST['gaplus_access_by_code'], 'gaplus_access_by_code'))
            return;

        $is_network = is_network_admin() ? 'network' : '';

        include_once 'externals/google/autoload.php';

        $this->google_client = new GAPGoogle_Client();
        $this->google_client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly', 'https://www.googleapis.com/auth/userinfo.profile'));
        $this->google_client->setAccessType('offline');

	    $google_api = ga_plus_get_google_api_settings( 'network' );

        //lets save api detials if user tries to configure them
        if(isset($_POST['gaplus_access_by_api'])) {
	        $new_google_api_settings = array(
		        'client_id' => $_POST['client_id'],
		        'client_secret' => $_POST['client_secret'],
		        'api_key' => $_POST['api_key'],
		        'verified' => false
	        );
            ga_plus_update_setting( 'google_api', $new_google_api_settings, $is_network );

            $this->google_client->setApprovalPrompt('force');
            $this->google_client->setRedirectUri(network_site_url());
            $this->google_client->setClientId($_POST['client_id']);
            $this->google_client->setClientSecret($_POST['client_secret']);
            $this->google_client->setDeveloperKey($_POST['api_key']);
        }
        elseif(isset($_GET['gaplus_loggedin']) || (isset($google_api['verified']) && $google_api['verified'] == true )) {
            $this->google_client->setApprovalPrompt('force');
            $this->google_client->setRedirectUri(network_site_url());
            $this->google_client->setClientId($google_api['client_id']);
            $this->google_client->setClientSecret($google_api['client_secret']);
            $this->google_client->setDeveloperKey($google_api['api_key']);
        }
        else {
            $this->google_client->setApplicationName('Google Analytics +');
            $this->google_client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $this->google_client->setClientId(apply_filters('ga_project_client_id', '640050123521-r5bp4142nh6dkh8bn0e6sn3pv852v3fm.apps.googleusercontent.com'));
            $this->google_client->setClientSecret(apply_filters('ga_project_client_secret', 'wWEelqN4DvE2DJjUPp-4KSka'));
            $this->google_client->setDeveloperKey(apply_filters('ga_project_key', 'AIzaSyBGtoZs_e4AgakpuM1q04KdwfBxkt7TQv8'));
        }

        //lets save orgin used to login and redirect to login page
        if(isset($_POST['gaplus_access_by_api']) || (isset($_GET['gaplus_login']) && wp_verify_nonce( $_GET['gaplus_login'], 'gaplus_login' ))) {
            $orgin = $is_network ? 'network' : get_current_blog_id();

            $this->google_client->setState(urlencode(json_encode(array('gaplus_login' => wp_create_nonce('gaplus_login'), 'orgin' => $orgin))));

            wp_redirect($this->google_client->createAuthUrl());
            exit();
        }

        if(isset($_POST['gaplus_access_by_code']) || isset($_GET['gaplus_loggedin'])) {
            if($_REQUEST['code']) {
                try {
	                $result = ga_plus_code_authenticate( $_REQUEST['code'] );
                    $db_token_id = ga_plus_update_saved_token( $result['google_user_id'], $result['token_object'] );

                    if(isset($_GET['gaplus_loggedin']) && $is_network) {
	                    $google_api = ga_plus_get_google_api_settings( 'network' );
                        $google_api['verified'] = true;
                        ga_plus_update_setting( 'google_api', $google_api, $is_network );
                    }

                    //lets store data for site
	                ga_plus_refresh_google_login_settings( $db_token_id, $is_network );

                    wp_redirect(add_query_arg(array('dmsg' => urlencode(__('You are successfuly logged in.', $this->text_domain)), 'type' => 'success', 'gaplus_loggedin' => false, 'code' => false)));
                    exit();
                } catch (GAPGoogle_IO_Exception $e) {
                    wp_redirect(add_query_arg(array('dmsg' => urlencode(esc_html($e)), 'type' => 'error')));
                    exit();
                } catch (GAPGoogle_Service_Exception $e) {
                    wp_redirect(add_query_arg(array('dmsg' => urlencode(esc_html("(" . $e->getCode() . ") " . $e->getMessage())), 'type' => 'error', 'gaplus_loggedin' => false, 'code' => false)));
                    exit();
                } catch (Exception $e) {
                	ga_plus_reset_google_login_settings( $is_network );
                }
            }

            wp_redirect(add_query_arg(array('dmsg' => urlencode(__('Authorisation error.', $this->text_domain)), 'type' => 'error', 'gaplus_loggedin' => false, 'code' => false)));
            exit();
        }

        //this handles logging out
        if( isset($_GET['gaplus_logout']) && wp_verify_nonce($_GET['gaplus_logout'], 'gaplus_logout') ) {
            if(($is_network && !is_super_admin()) || !current_user_can('manage_options'))
                die(__('Cheatin&#8217; uh?'));

	        ga_plus_update_setting( 'google_login', array(), $is_network );

	        $google_api = ga_plus_get_google_api_settings( 'network' );
            if( $google_api && $is_network) {
                $google_api['verified'] = false;
	            ga_plus_update_setting( 'google_api', $google_api, $is_network );
            }

            $redirect_url = $is_network ? admin_url('/network/settings.php') : admin_url('/options-general.php');

            wp_redirect(add_query_arg(array('page' => 'google-analytics', 'dmsg' => urlencode(__( 'Logout successful!', $this->text_domain ))), $redirect_url));
            exit();
        }

        //this is used to translate token data from token table to old method
        if(isset($this->google_login['token_id']) && $this->google_login['token_id']) {
            global $wpdb;
            $db_token = ga_plus_get_saved_token_by_token_id( $this->google_login['token_id'] );
            if($db_token) {
                $this->google_login['token'] = $this->google_login['orginal_token'] = json_encode( $db_token );
                $this->google_login['expire'] = $db_token->created + $db_token->expires_in;
            }
        }

        if(isset($this->google_login['logged_in']) && $this->google_login['logged_in'] == 2) {
            //lets wait a minute before we try to refresh a token after failure
            if(isset($this->google_login['fail_time']) && $this->google_login['fail_time']+MINUTE_IN_SECONDS < time())
                return;

	        if ( $this->google_login['expire'] < time() ) {
		        if ( $this->setting_page ) {
			        $source = $this->setting_page == 'network' ? 'network' : '';
		        } else {
			        $source = $this->stats_source == 'network' ? 'network' : '';
		        }

		        try {
			        ga_plus_refresh_google_login_token( $this->google_login, $source );
			        $this->google_login = ga_plus_get_google_login_settings();
		        } catch ( GAPGoogle_IO_Exception $e ) {
			        $this->handle_refesh_token_failure( $source, $e );
		        } catch ( Exception $e ) {
			        $this->handle_refesh_token_failure( $source, $e );
		        }
	        }
        }
    }

    function handle_refesh_token_failure($source, $e) {
        //lets store reason for failure separately
	    ga_plus_update_setting( 'google_login_failure', $e->getMessage(), $source );

        $this->google_login['fail_time'] = time();
        $this->google_login['fail_count'] = isset($this->google_login['fail_count']) ? $this->google_login['fail_count']+1 : 1;

        if($this->google_login['fail_count'] < 10)
	        ga_plus_update_setting( 'google_login', $this->google_login, $source );
        else
	        ga_plus_update_setting( 'google_login', array(), $source );
    }

    function prepare_authentication_header($url) {
        $token = $this->google_login['token'];
        $token_object = json_decode($token);
        $orginal_token = $this->google_login['orginal_token'];
        $orginal_token_object = json_decode($orginal_token);

        $headers['Authorization'] = $orginal_token_object->token_type.' '.$token_object->access_token;

        return $headers;
    }

    function get_accounts() {
        $headers = $this->prepare_authentication_header($this->account_base_url_new.'accounts/~all/webproperties/~all/profiles');
        $response = wp_remote_get($this->account_base_url_new.'accounts/~all/webproperties/~all/profiles', array('sslverify' => false, 'headers' => $headers));
        if(is_wp_error($response)) {
            $response_body = wp_remote_retrieve_body($response);
            $response = json_decode($response_body);
            if(isset($response->error->message))
                $this->error = $response_body;
            return false;
        }
        else {
            $this->http_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body($response);

            if($this->http_code != 200) {
                $response = json_decode($response_body);
                if(isset($response->error->message))
                    $this->error = $response_body;
                return false;
            }
            else {
                $response = json_decode($response_body);
                $this->error = '';
                $host_ready = '';

                $current_site_url = rtrim(get_site_url(), "/");

                $is_network = is_network_admin() ? 'network' : '';

                $accounts = array();
                foreach($response->items as $analytics_profile) {
                    $tracking_code = $analytics_profile->webPropertyId;
                    $account_id = 'ga:'.$analytics_profile->id;
                    $title = $analytics_profile->name;
                    $website_url = rtrim($analytics_profile->websiteUrl, "/");

                    $this->profile_id = $account_id;
                    $current_settings = ga_plus_get_settings( 'current' );
                    if(!isset($save_settings) && (empty($current_settings['track_settings']['tracking_code']) || empty($current_settings['track_settings']['google_analytics_account_id']))) {
                        if($current_site_url == $website_url) {
                            if(empty($current_settings['track_settings']['tracking_code'])){
                                $current_settings['track_settings']['tracking_code'] = $tracking_code;
                            }
                            if(empty($current_settings['track_settings']['google_analytics_account_id'])) {
                                $current_settings['track_settings']['google_analytics_account_id'] = $account_id;
                            }
	                        ga_plus_update_setting( 'track_settings', $current_settings['track_settings'], $is_network );
                        }
                    }

                    $accounts[$account_id] = $title.' - '.$website_url.' ('.$tracking_code.')';
                }

                return $accounts;
            }
        }
    }

    function set_up_ga_data() {
        global $pagenow;

        //filter variables
        $this->cache_timeout = apply_filters('ga_cache_timeout', $this->cache_timeout);
        $this->cache_timeout_personal = apply_filters('ga_cache_timeout_personal', $this->cache_timeout);


        //setup correct google analytics profile id
        if($this->stats_source == 'site') {
        	$login_settings = ga_plus_get_google_login_settings( 'site' );
        	$track_settings = ga_plus_get_track_settings( 'site' );
            $this->profile_id = $track_settings['google_analytics_account_id'];

            $this->oauth_token = $login_settings['token'];
            $this->oauth_secret = $login_settings['token_secret'];

            $this->google_login = $login_settings;

            //change cache timeout for site based stats
            $this->cache_timeout = $this->cache_timeout_personal;
        }
        elseif($this->stats_source == 'network') {
	        $login_settings = ga_plus_get_google_login_settings( 'network' );
	        $track_settings = ga_plus_get_track_settings( 'network' );
            $this->profile_id = $track_settings['google_analytics_account_id'];

            $this->oauth_token = $login_settings['token'];
            $this->oauth_secret = $login_settings['token_secret'];

            $this->google_login = $login_settings;
        }

        //set up filters to show correct stats for current site
        if($this->stats_source == 'network'&& !$this->is_network_admin) {
            global $dm_map;

            $url = method_exists($dm_map, 'domain_mapping_siteurl') ? $dm_map->domain_mapping_siteurl(home_url()) : home_url();

            $site_url_parts = explode('/', str_replace('http://', '', str_replace('https://', '', $url)));
            if(!$site_url_parts)
                $site_url_parts = explode('/', str_replace('http://', '', str_replace('https://', '', site_url())));

            $this->filter[] = 'ga:hostname=='.$site_url_parts[0];

            //if its in subdirectory mode, then set correct beggining for page path
            if(count($site_url_parts) > 1) {
                unset($site_url_parts[0]);
                $pagepath = implode('/', $site_url_parts);

                $this->filter[] = 'ga:pagePath=~^/'.$pagepath.'/.*';
            }
        }

        if($this->profile_id && $this->oauth_token && $this->oauth_secret) {
            //set up date related variables needed to get data
            $this->date_range =
            (isset($_GET['date_range']) && ($_GET['date_range'] == 3 || $_GET['date_range'] == 12)) ? $_GET['date_range'] :
            ((isset($_POST['date_range']) && ($_POST['date_range'] == 3 || $_POST['date_range'] == 12)) ? $_POST['date_range'] : 1);

            $start_date = time() - (60 * 60 * 24 * 30 * $this->date_range);
            $this->start_date = date('Y-m-d', $start_date);
            $this->end_date = date('Y-m-d');

            //configure filter for posts to display proper data
            $this->post = ($pagenow == 'post.php' && isset($_GET['post']) && $_GET['post']) ? $_GET['post'] : ((isset($_POST['post']) && $_POST['post']) ? $_POST['post'] : 0);
            if($this->post)
                $this->filter[] = 'ga:pagePath=~/'.basename(get_permalink($this->post)).'/$';

            //exclude stuff we dont want
            $this->filter[] = 'ga:pagePath!@preview=true';

            //configure type to know what kind of data should be loaded
            if(is_admin()) {
                if(isset($_POST['action']) && $_POST['action'] == 'load_google_analytics' && isset($_POST['type']))
                    $this->type = $_POST['type'];
                elseif($pagenow == 'index.php' && !isset($_GET['page']))
                    $this->type = 'widget';
                elseif($pagenow == 'index.php' && isset($_GET['page']) && $_GET['page'] == 'google-analytics-statistics')
                    $this->type = 'statistics_page';
                elseif($this->post)
                    $this->type = 'post';
                else
                    $this->type = 'unknown';
            }
            else
                $this->type = 'frontend_widget';

            //set up correct/unique for stats cache name
            $this->cache_name = 'gac32_'.$this->profile_id.get_current_blog_id().$this->is_network_admin.$this->start_date.$this->end_date.$this->post;

            //if its a ajax call, we dont want cached version
            if(!wp_doing_ajax())
                $this->cache = get_transient($this->cache_name);

            //unset cache if it needs to get more data
            if($this->type != 'widget' && $this->cache && count($this->cache) == 1)
                $this->cache = 0;

            if($this->type != 'frontend_widget') {
                add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

                //add all the widgets for dashboards and posts
                add_action('wp_dashboard_setup', array(&$this, 'register_google_analytics_dashboard_widget'));
                add_action('wp_network_dashboard_setup', array(&$this, 'register_google_analytics_dashboard_widget'));
                add_action('add_meta_boxes', array(&$this, 'register_google_analytics_post_widget'));
            }
        }
    }

    //load correct data and prepare to display
    function load_google_analytics($args = array()) {
        if($this->type != 'frontend_widget' && !current_user_can( $this->required_capability ))
            die(__('Cheatin&#8217; uh?'));

        //if no cache, data will be requested by ajax
        if(!$this->cache) {
            //set up correct chart details for choosen date range
            if($this->date_range == 3)
                $date_details = array('dimension' => 'ga:yearWeek', 'format' => 'M W');
            elseif($this->date_range == 12)
                $date_details = array('dimension' => 'ga:yearMonth', 'format' => 'M Y');
            else
                $date_details = array('dimension' => 'ga:date', 'format' => 'M d');

            $dates_data = $this->request('simple', '', $date_details['dimension'], 'ga:visits,ga:pageviews,ga:newVisits');
            //Load advanced statistics
            if($this->type == 'statistics_page' || $this->type == 'post' || $this->type == 'frontend_widget') {
                if(!$this->error)
                    $summary_data = $this->request('simple', '', '','ga:visits,ga:pageviews,ga:newVisits,ga:percentNewVisits,ga:visitBounceRate,ga:avgSessionDuration,ga:pageviewsPerVisit,ga:percentNewVisits');
                if(!$this->error)
                    $keywords_data = $this->request('simple', 7, 'ga:keyword', 'ga:visits', '-ga:visits');
                if(!$this->error)
                    $sources_data = $this->request('simple', 7, 'ga:source', 'ga:visits', '-ga:visits');
                //Load statistics for statistics page
                if($this->type == 'statistics_page' || $this->type == 'frontend_widget') {
                    if(!$this->error && !$this->post)
                        $pages_data = $this->request('advanced', 20, 'ga:hostname,ga:pageTitle,ga:pagePath', 'ga:pageviews,ga:visits,ga:newVisits', '-ga:visits');
                    if(!$this->error)
                        $countries_data = $this->request('simple', 15, 'ga:country', 'ga:visits', '-ga:visits');
                }
            }

            $stats = array();
            $return = array('type' => $this->type);
            if($this->error) {
                if (strpos($this->error,'GDataauthErrorAuthorizationInvalid') !== false) {
                    if(is_network_admin())
                        $settings_page_url = admin_url('network/settings.php?page=google-analytics');
                    else
                        $settings_page_url = admin_url('options-general.php?page=google-analytics');
                    $this->error = '<a href="'.$settings_page_url.'">'.__('Please logout and login to Google Account.', $this->text_domain).'</a>';
                }
                $return['html'] = __('Error loading data.', $this->text_domain).' '.$this->error;
                $return['error'] = true;
            }
            else {
                $return['error'] = false;

                $translate_dates = array(
                    'Jan' => __('Jan', $this->text_domain),
                    'Feb' => __('Feb', $this->text_domain),
                    'Mar' => __('Mar', $this->text_domain),
                    'Apr' => __('Apr', $this->text_domain),
                    'May' => __('May', $this->text_domain),
                    'Jun' => __('Jun', $this->text_domain),
                    'Jul' => __('Jul', $this->text_domain),
                    'Aug' => __('Aug', $this->text_domain),
                    'Sep' => __('Sep', $this->text_domain),
                    'Oct' => __('Oct', $this->text_domain),
                    'Nov' => __('Nov', $this->text_domain),
                    'Dec' => __('Dec', $this->text_domain)
                );
                $stats['chart_visitors'] = array(array(__('Date', $this->text_domain), __('Pageviews', $this->text_domain), __('Visits', $this->text_domain), __('Unique Visitors', $this->text_domain)));
                foreach($dates_data as $day => $data) {
                    if($this->date_range == 3)
                        $day = substr_replace($day, 'W', 4, 0);
                    elseif($this->date_range == 12)
                        $day = $day.'01';

                    $time = strtotime($day)+(get_option('gmt_offset')*60*60);

                    $date = str_replace(date('M', $time), $translate_dates[date('M', $time)], date($date_details['format'], $time));
                    $stats['chart_visitors'][] = array($date, (int)$data['ga:pageviews'], (int)$data['ga:visits'], (int)$data['ga:newVisits']);
                }
                $return['chart_visitors'] = $stats['chart_visitors'];

                //setup advanced statistics
                if($this->type == 'statistics_page' || $this->type == 'post' || $this->type == 'frontend_widget') {
                    $stats['top_posts'] = $top_searches = $top_referers = array();

                    $stats['visits'] = isset($summary_data['value']['ga:visits']) ? number_format($summary_data['value']['ga:visits']) : '-';
                    $stats['pageviews'] = isset($summary_data['value']['ga:pageviews']) ? number_format($summary_data['value']['ga:pageviews']) : '-';
                    $stats['unique_visitors'] = isset($summary_data['value']['ga:newVisits']) ? number_format($summary_data['value']['ga:newVisits']) : '-';
                    $stats['page_per_visit'] = isset($summary_data['value']['ga:pageviewsPerVisit']) ? number_format($summary_data['value']['ga:pageviewsPerVisit']) : '-';
                    $stats['bounce_rate'] = isset($summary_data['value']['ga:visitBounceRate']) ? number_format($summary_data['value']['ga:visitBounceRate']).'%' : '-';
                    $stats['avg_visit_duration'] = isset($summary_data['value']['ga:avgSessionDuration']) ? date("H:i:s",$summary_data['value']['ga:avgSessionDuration']) : '-';
                    $stats['new_visits'] = isset($summary_data['value']['ga:percentNewVisits']) ? number_format($summary_data['value']['ga:percentNewVisits']).'%' : '-';

                    foreach($keywords_data as $keyword => $stat)
                        if($keyword != "(not set)")
                            $stats['top_searches'][] = array('keyword' => $keyword, 'stat' => $stat);

                    foreach($sources_data as $source => $stat)
                        if($source != "(not set)")
                            $stats['top_referers'][] = array('source' => $source, 'stat' => $stat);

                    //setup statistics for statistics page
                    if($this->type == 'statistics_page' || $this->type == 'frontend_widget') {
                        if(isset($pages_data))
                            foreach($pages_data as $page) {
                                $url = $page['value'];
                                $title = $page['children']['value'];
                                $pageviews = $page['children']['children']['children']['ga:pageviews'];
                                $visits = $page['children']['children']['children']['ga:visits'];
                                $unique_visitors = $page['children']['children']['children']['ga:newVisits'];
                                $host = $page['children']['children']['value'];

                                $stats['top_pages'][] = array('host' => $host, 'url' => $url, 'title' => $title, 'pageviews' => $pageviews, 'visits' => $visits, 'unique_visitors' => $unique_visitors);
                            }

                        $stats['chart_countries'] = array(array(__('Country', $this->text_domain), __('Visits', $this->text_domain)));
                        foreach($countries_data as $country => $visits)
                            $stats['chart_countries'][] = array($country, (int)$visits);
                        $return['chart_countries'] = $stats['chart_countries'];
                    }
                }

                //set cache data if its all good
                set_transient($this->cache_name, $stats, $this->cache_timeout);

                //prepare correct data for ajax return
                if($this->type == 'frontend_widget')
                    $return['html'] = $this->google_analytics_frontend_widget_html($stats, $args);
                elseif($this->type == 'post')
                    $return['html'] = $this->google_analytics_widget_extended_html($stats, $args);
                elseif($this->type == 'statistics_page')
                    $return['html'] = $this->google_analytics_statistics_page_html($stats, $args);
                else
                    $return['html'] = $this->google_analytics_widget_html($stats, $args);

                $return['stats'] = $stats;
            }

            echo json_encode($return);
            die();
        }
        else
            //prepare correct data cache based return
            if($this->type == 'frontend_widget')
                return $this->google_analytics_frontend_widget_html($this->cache, $args);
            elseif($this->type == 'post')
                return $this->google_analytics_widget_extended_html($this->cache, $args);
            elseif($this->type == 'statistics_page')
                return $this->google_analytics_statistics_page_html($this->cache, $args);
            else
                return $this->google_analytics_widget_html($this->cache, $args);
    }

    function google_analytics_widget() {
        if($this->is_network_admin)
            $statistics_page_url = admin_url('network/index.php?page=google-analytics-statistics');
        else
            $statistics_page_url = admin_url('index.php?page=google-analytics-statistics');

        if(!$this->cache) {
            if($this->type == 'post' && $this->load_mode == 'soft')
                $text = '<p class="post-loader"><a id="load-post-stats" class="button button-primary" href="#">'.__('Load Post Stats', $this->text_domain).'</a><span class="loading"><img alt="'.__( 'Loading...', $this->text_domain ).'" src="'.includes_url('images/spinner-2x.gif').'"/></span></p>';
            else
                $text = '<p>'.__('Loading...', $this->text_domain).'</p>';
            echo '<div id="google-analytics-widget">'.$text.'</div><p class="textright"><a class="button button-primary" href="'.$statistics_page_url.'">'.__('See All Stats', $this->text_domain).'</a></p>';
        }
        else
            echo '<div id="google-analytics-widget">'.$this->load_google_analytics().'</div><p class="textright"><a class="button button-primary" href="'.$statistics_page_url.'">'.__('See All Stats', $this->text_domain).'</a></p>';
    }

    function google_analytics_widget_html($stats) {
        $return = '
            <div aria-hidden="true" class="google_analytics_chart_holder">
                <div id="google-analytics-chart-visitors" class="google_analytics_chart" style="width: 100%; height: 300px;"></div>
            </div>';

        return $return;
    }

    function google_analytics_widget_extended_html($stats) {
        $return = '
            <div aria-hidden="true" class="google_analytics_chart_holder">
                <div id="google-analytics-chart-visitors" class="google_analytics_chart" style="width: 100%; height: 300px;"></div>
            </div>

            <div class="google-analytics-basic-stats">
                <ul>
                    <li><label>'.__( 'Visits', $this->text_domain ).'</label><span>'.esc_html($stats['visits']).'</span></li>
                    <li><label>'.__( 'Unique Visitors', $this->text_domain ).'</label><span>'.esc_html($stats['unique_visitors']).'</span></li>
                    <li><label>'.__( 'Pageviews', $this->text_domain ).'</label><span>'.esc_html($stats['pageviews']).'</span></li>
                    <li><label>'.__( 'Pages / Visit', $this->text_domain ).'</label><span>'.esc_html($stats['page_per_visit']).'</span></li>
                    <li><label>'.__( 'Bounce Rate', $this->text_domain ).'</label><span>'.esc_html($stats['bounce_rate']).'</span></li>
                    <li><label>'.__( 'Avg. Visit Dur.', $this->text_domain ).'</label><span>'.esc_html($stats['avg_visit_duration']).'</span></li>
                    <li><label>'.__( 'New Visits', $this->text_domain ).'</label><span>'.esc_html($stats['new_visits']).'</span></li>
                </ul>
            </div>';
        if((isset($stats['top_searches']) && $stats['top_searches']) || (isset($stats['top_referers']) && $stats['top_referers'])) {
            $return .= '
                <div class="google-analytics-extended-stats">';

                if((isset($stats['top_searches']) && $stats['top_searches']) || (isset($stats['top_referers']) && $stats['top_referers'])) {
                    $return .= '
                        <div class="google_analytics_top_searches_referrals">';

                    if(isset($stats['top_searches']) && $stats['top_searches']) {
                        $top_searches = array();
                        foreach ($stats['top_searches'] as $key => $data)
                            $top_searches[] = '<tr><td>'.$data['keyword'].'</td><td align="right">'.esc_html($data['stat']).'</td></tr>';

                        $return .= '
                            <div id="postcustomstuff" class="google-analytics-searches">
                                <table class="wp-list-table widefat google-analytics-table">
                                    <thead>
                                        <tr>
                                            <th>'.__( 'Top Searches', $this->text_domain ).'</th>
                                            <th class="right">'.__( 'Visits', $this->text_domain ).'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    '.implode($top_searches).'
                                    </tbody>
                                </table>
                            </div>';
                    }

                    if(isset($stats['top_referers']) && $stats['top_referers']) {
                        $top_referers = array();
                        foreach ($stats['top_referers'] as $key => $data)
                            $top_referers[] = '<tr><td>'.esc_html($data['source']).'</td><td align="right">'.esc_html($data['stat']).'</td></tr>';

                        $return .= '
                            <div id="postcustomstuff" class="google-analytics-top-referrals last">
                                <table class="wp-list-table widefat google-analytics-table">
                                    <thead>
                                        <tr>
                                            <th>'.__( 'Top Referrals', $this->text_domain ).'</th>
                                            <th class="right">'.__( 'Visits', $this->text_domain ).'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        '.implode($top_referers).'
                                    </tbody>
                                </table>
                            </div>';
                    }

                    $return .= '
                        </div>';
                }
            $return .= '
                </div>
            ';
        }

        return $return;
    }

    function google_analytics_statistics_page() {
        if(!$this->cache)
            echo '<div id="google-analytics-statistics-page"><div class="loading"><img alt="'.__( 'Loading...', $this->text_domain ).'" src="'.includes_url('images/spinner-2x.gif').'"/></div></div>';
        else
            echo '<div id="google-analytics-statistics-page">'.$this->load_google_analytics().'</div>';
    }

    function google_analytics_statistics_page_html($stats) {
        if($this->date_range == 1)
            $statistics_description = ' - '.__( 'Last Month', $this->text_domain );
        elseif($this->date_range == 3)
            $statistics_description = ' - '.__( 'Last 3 Months', $this->text_domain );
        elseif($this->date_range == 12)
            $statistics_description = ' - '.__( 'Last Year', $this->text_domain );

        if($this->is_network_admin)
            $statistics_page_url = admin_url('network/index.php?page=google-analytics-statistics');
        else
            $statistics_page_url = admin_url('index.php?page=google-analytics-statistics');

        $return = '
            <div class="wrap">
                <h2>
                    '.__( 'Statistics', $this->text_domain ).$statistics_description.'
                    <a href="'.add_query_arg('date_range', false, $statistics_page_url).'" class="add-new-h2">'.__( 'Last Month', $this->text_domain ).'</a>
                    <a href="'.add_query_arg('date_range', 3, $statistics_page_url).'" class="add-new-h2">'.__( 'Last 3 Months', $this->text_domain ).'</a>
                    <a href="'.add_query_arg('date_range', 12, $statistics_page_url).'" class="add-new-h2">'.__( 'Last Year', $this->text_domain ).'</a>
                </h2>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="postbox-container-1" class="postbox-container">

                            <div aria-hidden="true" id="side-sortables" class="meta-box-sortables ui-sortable">
                                <div class="postbox google-analytics-countries">
                                    <h3 class="hndle"><span>'.__( 'Visitors: Country', $this->text_domain ).'</span></h3>
                                    <div class="inside">
                                        <div id="google-analytics-chart-countries" class="google-analytics-chart"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="side-sortables" class="meta-box-sortables ui-sortable">
                                <div class="postbox google_analytics_top_searches_referrals">
                                    <h3 class="hndle"><span>'.__( 'Referrers', $this->text_domain ).'</span></h3>
                                    <div class="inside">';



                                        $return .= '
                                            <div id="postcustomstuff" class="google-analytics-searches">
                                                <table class="wp-list-table widefat google-analytics-table">
                                                    <thead>
                                                        <tr>
                                                            <th>'.__( 'Top Searches', $this->text_domain ).'</th>
                                                            <th>'.__( 'Visits', $this->text_domain ).'</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>';

                                            if(isset($stats['top_searches']) && $stats['top_searches']) {
                                                $top_searches = array();
                                                foreach ($stats['top_searches'] as $key => $data)
                                                    $return .= '
                                                        <tr><td>'.esc_html($data['keyword']).'</td><td>'.esc_html($data['stat']).'</td></tr>';
                                            }
                                            else
                                                $return .= '
                                                        <tr><td colspan="2">'.__( 'Data is not available yet', $this->text_domain ).'</td></tr>';

                                                $return .= '
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div id="postcustomstuff" class="google-analytics-top-referrals last">
                                                <table class="wp-list-table widefat google-analytics-table">
                                                    <thead>
                                                        <tr>
                                                            <th>'.__( 'Top Referrals', $this->text_domain ).'</th>
                                                            <th>'.__( 'Visits', $this->text_domain ).'</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>';
                                            if(isset($stats['top_referers']) && $stats['top_referers']) {
                                                $top_referers = array();
                                                foreach ($stats['top_referers'] as $key => $data)
                                                    $return .= '
                                                        <tr><td>'.esc_html($data['source']).'</td><td>'.esc_html($data['stat']).'</td></tr>';
                                            }
                                            else
                                                $return .= '
                                                        <tr><td colspan="2">'.__( 'Data is not available yet', $this->text_domain ).'</td></tr>';

                                                $return .= '
                                                    </tbody>
                                                </table>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="postbox-container-2" class="postbox-container">
                            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                <div class="postbox">
                                    <h3 class="hndle"><span>'.__( 'Visitors', $this->text_domain ).'</span></h3>
                                    <div class="inside">
                                        <div aria-hidden="true" id="google-analytics-chart-visitors" class="google_analytics_chart"></div>
                                        <div class="google-analytics-basic-stats">
                                            <ul>
                                                <li><label>'.__( 'Visits', $this->text_domain ).'</label><span>'.esc_html($stats['visits']).'</span></li>
                                                <li><label>'.__( 'Unique Visitors', $this->text_domain ).'</label><span>'.esc_html($stats['unique_visitors']).'</span></li>
                                                <li><label>'.__( 'Pageviews', $this->text_domain ).'</label><span>'.esc_html($stats['pageviews']).'</span></li>
                                                <li><label>'.__( 'Pages / Visit', $this->text_domain ).'</label><span>'.esc_html($stats['page_per_visit']).'</span></li>
                                                <li><label>'.__( 'Bounce Rate', $this->text_domain ).'</label><span>'.esc_html($stats['bounce_rate']).'</span></li>
                                                <li><label>'.__( 'Avg. Visit Dur.', $this->text_domain ).'</label><span>'.esc_html($stats['avg_visit_duration']).'</span></li>
                                                <li><label>'.__( 'New Visits', $this->text_domain ).'</label><span>'.esc_html($stats['new_visits']).'</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                <div class="postbox">
                                    <h3 class="hndle"><span>'.__( 'Content', $this->text_domain ).'</span></h3>
                                    <div class="inside">
                                        <div id="postcustomstuff" class="google-analytics-top_posts-pages">
                                            <table class="wp-list-table widefat google-analytics-table">
                                                <thead>
                                                    <tr>
                                                        <th>'.__( 'Top Posts / Pages', $this->text_domain ).'</th>
                                                        <th>'.__( 'Visits', $this->text_domain ).'</th>
                                                        <th>'.__( 'Unique', $this->text_domain ).'</th>
                                                        <th>'.__( 'Views', $this->text_domain ).'</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                        if(isset($stats['top_pages']) && $stats['top_pages']) {
                                            //fixes for top pages to generate correct URL and merge data
                                            $pages_ready = array();
                                            foreach ($stats['top_pages'] as $key => $data) {
                                                $url = $this->get_url_from_google_data($data);

                                                if(!array_key_exists($url, $pages_ready))
                                                    $pages_ready[$url] = $data;
                                                else {
                                                    $pages_ready[$url]['visits'] = $pages_ready[$url]['visits'] + $data['visits'];
                                                    $pages_ready[$url]['unique_visitors'] = $pages_ready[$url]['unique_visitors'] + $data['unique_visitors'];
                                                    $pages_ready[$url]['pageviews'] = $pages_ready[$url]['pageviews'] + $data['pageviews'];
                                                }
                                             }
                                            foreach ($pages_ready as $url => $data) {
                                                $return .= '
                                                    <tr><td><a href="'.esc_url('http://'.$url).'">'.esc_html($data['title']).'</a></td><td>'.esc_html($data['visits']).'</td><td>'.esc_html($data['unique_visitors']).'</td><td>'.esc_html($data['pageviews']).'</td></tr>';
                                            }
                                        }
                                        else
                                            $return .= '
                                                    <tr><td colspan="3">'.__( 'Data is not available yet', $this->text_domain ).'</td></tr>';

                                            $return .= '
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';

        return $return;
    }

    function google_analytics_frontend_widget_html($stats, $args = array()) {
        global $google_analytics_frontend_widget_count, $dm_map;

        $return = '';
        $count = 0;
        $duplicate_check = array();
        foreach ($stats['top_pages'] as $key => $data) {
            $url = $this->get_url_from_google_data($data);
            if(method_exists($dm_map, 'domain_mapping_siteurl')) {
                $mapped_url = str_replace(array('http://', 'https://'), '', $dm_map->domain_mapping_siteurl(home_url()));
                $home_url = str_replace(array('http://', 'https://'), '', home_url());
                $url = str_replace($mapped_url, $home_url, $url);
            }
            $postid = url_to_postid( (is_ssl() ? 'https://' : 'http://').$url );
            if(!$postid)
                continue;

            $post = get_post($postid);

            if($post->post_type != 'post')
                continue;

            if(in_array($postid, $duplicate_check))
                continue;

            $duplicate_check[] = $postid;

            $count ++;
            $return .= '<li><a href="'.(is_ssl() ? 'https://' : 'http://').$url.'">'.esc_html($post->post_title).'</a></li>';

            if(isset($args['number']) && $count == $args['number'])
                break;
        }

        if(!$count)
            $return .= '<li>'.__( 'No data yet', $this->text_domain ).'</li>';

        $google_analytics_frontend_widget_count = $count;

        return $return;
    }

    function google_analytics_frontend_widget($args = array()) {
        if(!$this->cache)
            echo '<ul class="google-analytics-frontend-widget"><li>'.__( 'Loading...', $this->text_domain ).'</li></ul>';
        else
            echo '<ul class="google-analytics-frontend-widget">'.$this->load_google_analytics($args).'</ul>';
    }

    function get_url_from_google_data($data) {
        if (strpos(substr($data['url'], 1), $data['host']) === 0)
            $url = substr($data['url'], 1);
        elseif(strpos($data['url'], $data['host']) === 0)
            $url = $data['url'];
        else
            $url = $data['host'].$data['url'];

        return $url;
    }

    function request($type, $max_results = '', $dimensions = '', $metrics = '', $sort = '') {
        $url_parameters = array(
            'ids' => $this->profile_id,
            'start-date' => $this->start_date,
            'end-date' => $this->end_date,
            'samplingLevel' => 'HIGHER_PRECISION'
        );
        if(!empty($max_results))
            $url_parameters['max-results'] = $max_results;
        if(!empty($dimensions))
            $url_parameters['dimensions'] = $dimensions;
        if(!empty($metrics))
            $url_parameters['metrics'] = $metrics;
        if(!empty($sort))
            $url_parameters['sort'] = $sort;
        if($this->filter && count($this->filter) > 0)
            $url_parameters['filters'] = implode(';', $this->filter);
        $url = add_query_arg($url_parameters, $this->base_url . 'data');

        $response = wp_remote_get($url, array('sslverify' => false, 'headers' => $this->prepare_authentication_header($url)));
        if($response && is_wp_error($response)) {
            $this->error = $response->get_error_message();
            return false;
        }
        else {
            $this->http_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            if($this->http_code != 200) {
                $this->error = $response_body;
                return false;
            }
            else {
                $xml = simplexml_load_string($response_body);

                $return_values = array();
                foreach($xml->entry as $entry) {
                    if($type == 'simple') {
                        if($dimensions == '')
                            $dim_name = 'value';
                        else {
                            $dimension = $entry->xpath('dxp:dimension');
                            $dimension_attributes = $dimension[0]->attributes();
                            $dim_name = (string)$dimension_attributes['value'];
                        }

                        $metric = $entry->xpath('dxp:metric');
                        if(sizeof($metric) > 1) {
                            foreach($metric as $single_metric) {
                                $metric_attributes = $single_metric->attributes();
                                $return_values[$dim_name][(string)$metric_attributes['name']] = (string)$metric_attributes['value'];
                            }
                        }
                        else {
                            $metric_attributes = $metric[0]->attributes();
                            $return_values[$dim_name] = (string)$metric_attributes['value'];
                        }
                    }
                    else {
                        $metrics = array();
                        foreach($entry->xpath('dxp:metric') as $metric) {
                            $metric_attributes = $metric->attributes();
                            $metrics[(string)$metric_attributes['name']] = (string)$metric_attributes['value'];
                        }

                        $last_dimension_var_name = null;
                        foreach($entry->xpath('dxp:dimension') as $dimension) {
                            $dimension_attributes = $dimension->attributes();

                            $dimension_var_name = 'dimensions_' . strtr((string)$dimension_attributes['name'], ':', '_');
                            $$dimension_var_name = array();

                            if($last_dimension_var_name == null)
                                $$dimension_var_name = array('name' => (string)$dimension_attributes['name'],'value' => (string)$dimension_attributes['value'],'children' => $metrics);
                            else
                                $$dimension_var_name = array('name' => (string)$dimension_attributes['name'],'value' => (string)$dimension_attributes['value'],'children' => $$last_dimension_var_name);

                            $last_dimension_var_name = $dimension_var_name;
                        }
                        array_push($return_values, $$last_dimension_var_name);
                    }
                }

                return $return_values;
            }
        }
    }
}

global $google_analytics_async_dashboard;
$google_analytics_async_dashboard = new Google_Analytics_Async_Dashboard();