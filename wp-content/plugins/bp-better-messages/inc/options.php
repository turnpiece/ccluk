<?php
defined( 'ABSPATH' ) || exit;

class BP_Better_Messages_Options
{

    protected $path;
    public $settings;

    public static function instance()
    {

        static $instance = null;

        if ( null === $instance ) {
            $instance = new BP_Better_Messages_Options;
            $instance->setup_globals();
            $instance->setup_actions();
        }

        return $instance;
    }

    public function setup_globals()
    {
        $this->path = BP_Better_Messages()->path . '/views/';

        $defaults = array(
            'mechanism'       => 'ajax',
            'thread_interval' => 3,
            'site_interval'   => 10,
            'messagesPerPage' => 20,
            'attachmentsFormats' => array(),
            'attachmentsRetention' => 365,
            'attachmentsEnable' => '0',
            'attachmentsHide' => '1',
            'attachmentsRandomName' => '1',
	        'attachmentsMaxSize' => wp_max_upload_size() / 1024 / 1024,
            'miniChatsEnable' => '0',
            'searchAllUsers' => '0',
            'disableSubject' => '0',
            'disableEnterForTouch' => '1',
            'chatPage' => '0',
            'messagesStatus' => '0',
            'fastStart' => '1',
            'miniThreadsEnable' => '0',
            'miniFriendsEnable' => '0',
            'friendsMode' => '0',
            'singleThreadMode' => '0',
            'redirectToExistingThread' => '0',
            'disableGroupThreads' => '0'
        );

        $args = get_option( 'bp-better-chat-settings', array() );

        if( ! bpbm_fs()->can_use_premium_code() ){
            $args['mechanism']        = 'ajax';
            $args['miniChatsEnable']  = '0';
            $args['messagesStatus']   = '0';
            $args['miniThreadsEnable']   = '0';
        }

        $this->settings = wp_parse_args( $args, $defaults );
    }

    public function setup_actions()
    {
        add_action( 'admin_menu', array( $this, 'settings_page' ) );
    }

    /**
     * Settings page
     */
    public function settings_page()
    {
        add_menu_page(
            __( 'BP Better Messages' ),
            __( 'Better Messages' ),
            'manage_options',
            'bp-better-messages',
            array( $this, 'settings_page_html' ),
            'dashicons-format-chat'
        );
    }

    public function settings_page_html()
    {
        if ( isset( $_POST[ '_wpnonce' ] )
            && !empty( $_POST[ '_wpnonce' ] )
            && wp_verify_nonce( $_POST[ '_wpnonce' ], 'bp-better-messages-settings' )
        ) {
            unset( $_POST[ '_wpnonce' ], $_POST[ '_wp_http_referer' ] );

            if ( isset( $_POST[ 'save' ] ) ) {
                unset( $_POST[ 'save' ] );
                $this->update_settings( $_POST );
            }
        }

        include( $this->path . 'layout-settings.php' );
    }

    public function update_settings( $settings )
    {
	    if( ! isset($settings['attachmentsEnable']) ) $settings['attachmentsEnable'] = '0';
        if( ! isset($settings['attachmentsHide']) )   $settings['attachmentsHide'] = '0';
        if( ! isset($settings['attachmentsRandomName']) )   $settings['attachmentsRandomName'] = '0';
        if( ! isset($settings['miniChatsEnable']) )   $settings[ 'miniChatsEnable' ] = '0';
        if( ! isset($settings['searchAllUsers']) )   $settings[ 'searchAllUsers' ] = '0';
        if( ! isset($settings['disableSubject']) )   $settings[ 'disableSubject' ] = '0';
        if( ! isset($settings['disableEnterForTouch']) )   $settings[ 'disableEnterForTouch' ] = '0';
        if( ! isset($settings['messagesStatus']) )   $settings[ 'messagesStatus' ] = '0';
        if( ! isset($settings['fastStart']) )   $settings[ 'fastStart' ] = '0';
        if( ! isset($settings['miniFriendsEnable']) )   $settings[ 'miniFriendsEnable' ] = '0';
        if( ! isset($settings['miniThreadsEnable']) )   $settings[ 'miniThreadsEnable' ] = '0';
        if( ! isset($settings['friendsMode']) )   $settings[ 'friendsMode' ] = '0';
        if( ! isset($settings['singleThreadMode']) )   $settings[ 'singleThreadMode' ] = '0';
        if( ! isset($settings['redirectToExistingThread']) ) $settings[ 'redirectToExistingThread' ] = '0';
        if( ! isset($settings['disableGroupThreads']) ) $settings[ 'disableGroupThreads' ] = '0';


        foreach ( $settings as $key => $value ) {
	        /**
	         * Processing checkbox groups
	         */
	        if( is_array($value) ){
                $this->settings[$key] = array();
                foreach($value as $val){
                    $this->settings[$key][] = sanitize_text_field( $val );
                }
            } else {
                $this->settings[ $key ] = sanitize_text_field( $value );
            }
        }

        update_option( 'bp-better-chat-settings', $this->settings );
    }
}

function BP_Better_Messages_Options()
{
    return BP_Better_Messages_Options::instance();
}