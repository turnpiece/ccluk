<?php
/**
 * Class BBUB_Sap_Class_Test
 *
 * @package Buddypress_User_Blog
 */

class BBUB_Sap_Class_Test extends \BBoss_BP_UnitTestCase {
    
	/**
     * @test
     * @covers BuddyBoss_SAP_BP_Component::catch_transition_post_type_status
	 */
	function catch_transition_post_type_status() {
        
        $current_user_id = bp_loggedin_user_id();
        
		//1. Create a new post
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post', 'post_author' => $current_user_id, 'post_status' => 'pending' ) );
        
        wp_update_post( array( 'ID' => $newpost, 'post_status' => 'publish' ) );
        
        //2. Check if notification was added
        global $wpdb;
        $notification = $wpdb->get_row( 
            $wpdb->prepare(
                "SELECT * FROM " . buddypress()->notifications->table_name . " WHERE user_id = %d AND item_id = %d AND component_action = 'post_approved' LIMIT 1",
                $current_user_id,
                $newpost
            ),
            ARRAY_A
        );
        
        $this->assertNotEmpty( $notification, 'Notification was not added.' );
        
        
        //3. Load post url and check if notification was mark as read
        /*
         * This test is not working. When we go_to post url, 
         * mark_notification_read function hooked to template_redirect is not called, for reasons unknown yet.
         * 
        $url = add_query_arg( array( 'action' => 'bp_sap_mark_read' ), get_permalink( $newpost ) );
        $url = wp_nonce_url( $url, 'sap_notif_mark_read' );
        $this->go_to( $url );
        
        $notification = $wpdb->get_row( 
            $wpdb->prepare(
                "SELECT * FROM " . buddypress()->notifications->table_name . " WHERE user_id = %d AND item_id = %d AND component_action = 'post_approved' LIMIT 1",
                $current_user_id,
                $newpost
            ),
            ARRAY_A
        );
        
        $this->assertEquals( 0, $notification['is_new'], 'Notification should have been marked as read. But it is not.' );*/
        
        
        //4. Delete post
        wp_delete_post( $newpost );
        
        //5. Check if notification was deleted
        $notification = $wpdb->get_row( 
            $wpdb->prepare(
                "SELECT * FROM " . buddypress()->notifications->table_name . " WHERE user_id = %d AND item_id = %d AND component_action = 'post_approved' LIMIT 1",
                $current_user_id,
                $newpost
            ),
            ARRAY_A
        );
        $this->assertEmpty( $notification, 'Notification was not deleted.' );
	}
}
