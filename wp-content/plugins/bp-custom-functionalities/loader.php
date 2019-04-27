<?php
/**
 * Plugin Name: BP Custom Functionalities
 * Plugin URI:  https://prashantdev.wordpress.com
 * Description: Much needed custom functionalities like locking buddypress for guest users, locking bbPress for guest users, locking buddypress for paid membership pro membership levels, members exclusion from members directory based on roles and private profile.
 * Author:      Prashant Singh
 * Author URI:  https://profiles.wordpress.org/prashantvatsh
 * Version:     1.0.2
 * Text Domain: bp-custom-functionalities
 * License:     GPLv2 or later
 */

defined( 'ABSPATH' ) || exit;


add_action('plugins_loaded','bp_cfunc_check_is_buddypress');
function bp_cfunc_check_is_buddypress(){
	if ( function_exists('bp_is_active') ) {
		require( dirname( __FILE__ ) . '/bp-custom-functionalities.php' );
	}else{
		add_action( 'admin_notices', 'bp_cfunc_buddypress_inactive__error' );
	}
}

function bp_cfunc_buddypress_inactive__error(){
	$class = 'notice notice-error';
	$message = __( 'BP Custom Functionalities requires BuddyPress to be active and running.', 'bp-custom-functionalities' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

// Restrict BuddyPress
function bp_cfunc_guest_redirect() {
	$lock = get_option('ps_lock_bp', true);
	if($lock){
		global $bp;
		if ( is_buddypress() || bp_is_user() ) {
	        if(!is_user_logged_in()) {
	            wp_redirect(site_url());
				exit;
	        }
	    }
	}
	$lockbb = get_option('ps_lock_bb', true);
	if($lockbb){
		if ( class_exists( 'bbPress' ) && is_bbpress() ) {
	        if(!is_user_logged_in()) {
	            wp_redirect(site_url());
				exit;
	        }
	    }
	}
}
add_filter('get_header','bp_cfunc_guest_redirect',1);



//Restrict Member's Profile
add_action( 'wp', 'bp_cfunc_member_redirect' );
function bp_cfunc_member_redirect(){
	$restrict_profile = get_option('ps_restrict_member', true);
	if($restrict_profile && bp_is_user()){
		global $bp;
	    $current_user_id = (int) trim($bp->loggedin_user->id);
	    $member_id  = (int) trim($bp->displayed_user->id);
	    if (!current_user_can('manage_options') && $current_user_id !== $member_id)
	    {
	        wp_redirect(site_url());
	        exit;
	    }
	}
}

//Exclude User Roles From Member Directory
add_action('bp_ajax_querystring','bp_cfunc_exclude_roles_members_dir',20,2);
function bp_cfunc_exclude_roles_members_dir($ps=false,$object=false){
    $exc_roles = get_option('ps_exclude_roles', true);
    $users=array();

    if(!empty($exc_roles)){
	    foreach ($exc_roles as $key => $value) {
	    	$roles[] = $value;
		}
		$users = get_users( array( 'role__in' => $roles, 'fields' => 'ID' ) );
	}

	if($users){

	    if($object!='members')
	        return $ps;
	        
	    $excluded_user = implode(',',$users);
	  
	    $args=wp_parse_args($ps);
	    
	    if(!empty($args['user_id']))
	        return $ps;
	    
	    if(!empty($args['exclude']))
	        $args['exclude']=$args['exclude'].','.$excluded_user;
	    else 
	        $args['exclude']=$excluded_user;
	      
	    $ps=build_query($args);
	   
    }
    return $ps;
}


//Restrict Membership levels From BuddyPress
add_action('get_header','bp_cfunc_restrict_levels');
function bp_cfunc_restrict_levels(){
	global $bp, $membership_levels;
    $exc_levels = get_option('ps_exclude_levels', true);
    if( is_user_logged_in() && !empty($membership_levels) && !current_user_can('manage_options')){

    	if ( is_buddypress() || bp_is_user() ) {
	    	global $current_user;
			$current_user_membership_level = pmpro_getMembershipLevelForUser($current_user->ID);
			if(empty($current_user_membership_level)){
	    		wp_redirect(site_url());
				exit;
	    	}
	    }

    }

    if(!empty($exc_levels) && !empty($membership_levels)){

		if ( is_buddypress() || bp_is_user() ) {
	        if(is_user_logged_in() && !current_user_can('manage_options')){
	        	global $current_user;
				$current_user_membership_level = pmpro_getMembershipLevelForUser($current_user->ID);
	        	if(in_array($current_user_membership_level->ID, $exc_levels) ){
	        		wp_redirect(site_url());
					exit;
	        	}
	    	}
		}

	}
}
?>