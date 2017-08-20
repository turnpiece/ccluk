<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

add_filter( 'bboss_licensed_packages', 'bpublog_register_licensed_package' );
function bpublog_register_licensed_package( $packages=array() ){
    $this_package = array(
        'id'        => 'bpublog',
        'name'      => __( 'BuddyPress User Blog', 'bp-user-blog' ),
        'products'  => array(
            //key should be unique for every individual buddyboss product
            //and if product X is included in 2 different packages, key for product X must be same in both packages.
            'BPUBLOG' => array(
                'software_ids'  => array( 'BPUBLOG_1S', 'BPUBLOG_5S', 'BPUBLOG_20S' ),
                'name'          => 'BuddyPress User Blog',
            ),
        ),
    );
    $packages['bpublog'] = $this_package;
    return $packages;
}

add_filter( 'bboss_updatable_products', 'bpublog_register_updatable_product' );
function bpublog_register_updatable_product( $products ){
    //key should be exactly the same as product key above
    $products['BPUBLOG'] = array(
        //'path'  => plugin_basename(__FILE__),
        //@todo: this needs to be updated for all products
        'path'      => 'buddypress-user-blog/bp-user-blog.php',
        'id'        => 202,
        'software_ids'  => array( 'BPUBLOG_1S', 'BPUBLOG_5S', 'BPUBLOG_20S' ),
        'type'      => 'plugin',
    );
    return $products;
}

if( !function_exists( 'bboss_notice_install_updater' ) ) {
    add_action( 'admin_notices', 'bboss_notice_install_updater' );
    function bboss_notice_install_updater(){
        if( is_plugin_active( 'buddyboss-updater/buddyboss-updater.php' ) || is_plugin_active_for_network( 'buddyboss-updater/buddyboss-updater.php' ) )
            return;

        //should we show admin notices at all?
        if( 'no' == bblicenses_switch__show_admin_notices() )
            return;

        $plugin_link = "<a href='https://www.buddyboss.com/tutorials/buddyboss-updater-plugin/' target='_blank' rel='noopener'>Buddyboss Updater.</a>";
        $notice_install_buddyboss_updater = sprintf( __( "To receive updates for BuddyBoss products, please install and activate the plugin %s", 'bp-user-blog' ), $plugin_link );

        echo "<div class='error'><p>{$notice_install_buddyboss_updater}</p></div>";
    }
}

if( !function_exists( 'bblicenses_switch__show_admin_notices' ) ){
    function bblicenses_switch__show_admin_notices(){
        if( !get_transient( 'bblicenses_switch__show_admin_notices' ) ){
            $api_host  = 'https://buddyboss.com/';
            $http_username     = '';
            $http_password     = '';

            $show_admin_notices = 'no';

            //do an api request
            $request_params = array(
                'bboss_license_api' => '1',
                'request'           => 'check-switch',
                'switch'            => 'show_admin_notices',
            );

            $request_url = add_query_arg( $request_params, $api_host );

            if( $is_http_auth_req ){
                $headers = array( 'Authorization' => 'Basic ' . base64_encode( "{$http_username}:{$http_password}" ) );
                $q_response = wp_remote_get( $request_url, array( 'headers' => $headers, 'timeout' => 50 ) );
            } else {
                $q_response = wp_remote_get( $request_url, array( 'timeout' => 50 ) );
            }

            if( !is_wp_error( $q_response ) && $q_response['response']['code'] == 200 ){
                $response = (array) json_decode( $q_response['body'] );
                if( $response['status'] && $response['val'] == 'yes' ){
                    $show_admin_notices = 'yes';
                }
            }

            set_transient( 'bblicenses_switch__show_admin_notices', $show_admin_notices, 2 * HOUR_IN_SECONDS );
        }

        return get_transient( 'bblicenses_switch__show_admin_notices' );
    }
}
