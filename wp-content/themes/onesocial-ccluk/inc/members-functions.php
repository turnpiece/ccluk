<?php

// members functions

// disable public messaging
add_filter('bp_get_send_public_message_button', '__return_false');

/**
 * Output the markup for the message recipient tabs.
 */
function ccluk_message_get_recipient_tabs() {
	$recipients = explode( ' ', bp_get_message_get_recipient_usernames() );

	foreach ( $recipients as $recipient ) {

		$user_id = bp_is_username_compatibility_mode()
			? bp_core_get_userid( $recipient )
			: bp_core_get_userid_from_nicename( $recipient );

		if ( ! empty( $user_id ) ) : ?>

			<li id="un-<?php echo esc_attr( $recipient ); ?>" class="friend-tab">
				<a href="<?php echo bp_core_get_user_domain( $user_id ) ?>" class="user-link"><?php
					echo bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'thumb', 'width' => 25, 'height' => 25 ) );
					echo bp_core_get_user_displayname( $user_id );
				?></a>
			</li>

		<?php endif;
	}
}


// Fix for deprecation BP function called by OneSocial theme

if (!function_exists('bp_is_user_forums')) :

    function bp_is_user_forums() {
        return false;
    }

endif;

/*
// Customize user menu
// remove forums, groups and friends tabs
function ccluk_remove_forums_from_profile()
{
    bp_core_remove_nav_item('forums');
}
add_action('bp_forums_setup_nav','ccluk_remove_forums_from_profile');

function ccluk_remove_groups_from_profile()
{
    bp_core_remove_nav_item('groups');
}
add_action('bp_groups_setup_nav','ccluk_remove_groups_from_profile');

function ccluk_remove_friends_from_profile()
{
    bp_core_remove_nav_item('friends');
}
add_action('bp_friends_setup_nav','ccluk_remove_friends_from_profile');
*/
// add messages to nav
add_action( 'bp_setup_nav', function() {

    $bp = buddypress();

    bp_core_new_nav_item(
        array(
            'name' => __('Messages', 'buddypress'),
            'slug' => $bp->messages->slug,
            'position' => 50,
            'show_for_displayed_user' => false,
            'screen_function' => 'messages_screen_inbox',
            'default_subnav_slug' => 'inbox',
            'item_css_id' => $bp->messages->id
        )
    );
});

// remove submenu links from adminbar
function ccluk_remove_admin_bar_links() {
    if ( is_admin() ) { //nothing to do on admin
        return;
    }
    global $wp_admin_bar;

    $rm_items = array(
        'forums',
        'friends',
        'groups',
        'notifications-read',
        'notifications-unread',
        'settings-general',
        'settings-notifications',
        'settings-profile',
        'settings-delete-account',
        'messages-inbox',
        'messages-starred',
        'messages-sentbox',
        'messages-compose',
        'messages-notices',
        'xprofile-public',
        'xprofile-edit',
        'xprofile-change-avatar',
        'activity-personal',
        'activity-friends',
        'activity-groups',
        'activity-favorites',
        'activity-mentions'
    );

    foreach( $rm_items as $item )
        $wp_admin_bar->remove_menu( 'my-account-'.$item );

    //error_log( print_r( $wp_admin_bar, true ) );
}
//add_action( 'wp_before_admin_bar_render', 'ccluk_remove_admin_bar_links' );
