<?php
/**
 * Class BBUB_Recommend_Posts_Test
 *
 * @package Buddypress_User_Blog
 */

class BBUB_Recommend_Posts_Test extends \BBoss_BP_UnitTestCase {
    /**
     * Flag to check if recommend post option is enabled or not.
     * If not, we'll skip all the tests.
     * 
     * @var type string
     */
    protected $_is_recommendation_enabled = '';
    
    public function is_recommendation_enabled(){
        if( !$this->_is_recommendation_enabled ){
            $main_obj = bboss_ut_plugin_object();
            
            $flag = $main_obj->option( 'recommend_post' );
            
            if( $flag ){
                $this->_is_recommendation_enabled = 'yes';
            } else {
                $this->_is_recommendation_enabled = 'no';
            }
        }
    
        return 'yes' == $this->_is_recommendation_enabled ? true : false;
    }
    
    /**
     * @test
     * @covers sl_get_ip
     */
    function sl_get_ip(){
        $this->assertFalse( empty( sl_get_ip() ), 'sl_get_ip is returning empty value. It should return my ip.' );
    }
    
    /**
     * @test
     * @covers already_liked
     * @depends sl_get_ip
     */
    function already_liked(){
        if( !$this->is_recommendation_enabled() ){
            $this->markTestSkipped( "Recommended posts not enabled." );
            return false;// don't run tests
        }
        
        $other_user_id = ( bp_loggedin_user_id() * 1 ) + 1;
        
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        
        //current user has liked post
        update_post_meta( $newpost, '_user_liked', array( bp_loggedin_user_id() ) );
        $this->assertTrue( already_liked( $newpost, false ) );
        
        //current user has not liked post
        update_post_meta( $newpost, '_user_liked', array( $other_user_id ) );
        $this->assertFalse( already_liked( $newpost, false ) );
        
        //current user has liked post
        update_post_meta( $newpost, '_user_liked', array( $other_user_id, bp_loggedin_user_id() ) );
        $this->assertTrue( already_liked( $newpost, false ) );
        
        $comment_id = self::factory()->comment->create( array( 'comment_content' => 'This is a test comment.', 'user_id' => bp_loggedin_user_id() ) );
        
        //current user has not liked comment
        update_comment_meta( $comment_id, '_user_comment_liked', array( $other_user_id ) );
        $this->assertFalse( already_liked( $comment_id, true ) );
        
        //current user has liked comment
        update_comment_meta( $comment_id, '_user_comment_liked', array( $other_user_id, bp_loggedin_user_id() ) );
        $this->assertTrue( already_liked( $comment_id, true ) );
        
        /**
         * test with guest users, using ip
         */
        $orig_user = bp_loggedin_user_id();
        $this->set_current_user( 0 );
        $user_ip = sl_get_ip();
        
        //current user has not liked post
        delete_post_meta( $newpost, '_user_IP' );
        $this->assertFalse( already_liked( $newpost, false ) );
        
        //current user has liked post
        update_post_meta( $newpost, '_user_IP', array( $user_ip ) );
        $this->assertTrue( already_liked( $newpost, false ) );
        
        //current user has not liked comment
        delete_comment_meta( $comment_id, '_user_comment_IP' );
        $this->assertFalse( already_liked( $comment_id, true ) );
        
        //current user has liked comment
        update_comment_meta( $comment_id, '_user_comment_IP', array( $user_ip ) );
        $this->assertTrue( already_liked( $comment_id, true ) );
        
        $this->set_current_user( $orig_user );
    }
    
    /**
     * @test
     * @covers add_recommended_activity
     */
    function add_recommended_activity(){
        if( !$this->is_recommendation_enabled() ){
            $this->markTestSkipped( "Recommended posts not enabled." );
            return false;// don't run tests
        }
        
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        
        $activity_id_recorded = add_recommended_activity( bp_loggedin_user_id(), $newpost );
        $this->assertNotEmpty( $activity_id_recorded, 'add_recommended_activity didn\'t return the expected new activity id.' );
    }
    
    /**
     * @test
     * @covers process_simple_like
     * @depends already_liked
     * @depends add_recommended_activity
	 */
    function process_simple_like(){
        $this->_process_simple_like();
    }
    
	/**
     * @test
     * @covers process_simple_like
     * @depends already_liked
     * @depends add_recommended_activity
	 */
	function negative_process_simple_like() {
        $orig_user = get_current_user_id();
        
        //testing with guest user
        self::set_current_user( 0 );
        
        $this->_process_simple_like( 'Testing with guest user - ' );
        
        //log the user back in
        self::set_current_user( $orig_user );
	}
    
	protected function _process_simple_like( $err_prefix = 'Testing with member - ' ) {
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        
        $retval = process_simple_like( array( 'post_id' => $newpost ) );
        
        $this->assertEquals( 'liked', @$retval['status'] );
        
        if( !empty( $retval ) && isset( $retval['status'] ) ){
            //already liked should return true
            $this->assertTrue( already_liked( $newpost, false ), $err_prefix . 'already_liked should have returned true, but it didn\'t' );
            
            //check if activity added or not
            global $wpdb;
            $activity = $wpdb->get_row( "SELECT * FROM " . buddypress()->activity->table_name . " ORDER BY id DESC LIMIT 1", ARRAY_A );
            $this->assertEquals( 'recommended_post_activity', @$activity['type'], 'There doesn\'t seem to be an activity created after recommendation action.' );
            $this->assertEquals( $newpost, @$activity['item_id'], 'There doesn\'t seem to be an activity created after recommendation action.' );
        }
        
        //Now, remove from likes and confirm
        $retval = process_simple_like( array( 'post_id' => $newpost ) );
        //already liked should now return false
        $this->assertFalse( already_liked( $newpost, false ), $err_prefix . 'already_liked should have returned false, but it didn\'t' );
	}
    
    /**
     * @test
     * @covers sl_format_count
     */
    function sl_format_count(){
        $this->assertEquals( '1K', sl_format_count( 1000 ) );
        $this->assertEquals( '10.98K', sl_format_count( 10976 ) );
        $this->assertEquals( '999.11M', sl_format_count( 999111111.98765 ) );
    }
}