<?php
/**
 * Class BBUB_Bookmarks_Test
 *
 * @package Buddypress_User_Blog
 */

class BBUB_Bookmarks_Test extends \BBoss_BP_UnitTestCase {
    /**
     * Flag to check if bookmarks option is enabled or not.
     * If not, we'll skip all the tests.
     * 
     * @var type string
     */
    protected $_is_bookmarks_enabled = '';
    
    public function is_bookmarks_enabled(){
        if( !$this->_is_bookmarks_enabled ){
            $main_obj = bboss_ut_plugin_object();
            
            $flag = $main_obj->option( 'bookmark_post' );
            
            if( $flag ){
                $this->_is_bookmarks_enabled = 'yes';
            } else {
                $this->_is_bookmarks_enabled = 'no';
            }
        }
    
        return 'yes' == $this->_is_bookmarks_enabled ? true : false;
    }
    
	/**
	 * Test - sap_process_bookmark
     * 
     * @test
     * @covers sap_mark_post_as_bookmarked
     * @covers sap_user_has_bookmarked_post
     * @covers sap_remove_post_as_bookmarked
	 */
	function sap_process_bookmark() {
        if( !$this->is_bookmarks_enabled() ){
            $this->markTestSkipped( "Bookmarks not enabled." );
            return false;// don't run tests
        }
        
        $current_user_id = bp_loggedin_user_id();
        
		//1. Create a new post
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        
        //2. Add this post to current user's bookmarks
        sap_mark_post_as_bookmarked( $newpost, $current_user_id );
        
        //3. Confirm if bookmark added
        $this->assertTrue( sap_user_has_bookmarked_post( $current_user_id, $newpost ), 'Bookmark should have been added, but it\'s not.' );
        
        //4. Remove bookmark
        sap_remove_post_as_bookmarked( $newpost, $current_user_id );
        
        //5. Confirm removal
        $this->assertFalse( sap_user_has_bookmarked_post( $current_user_id, $newpost ), 'Bookmark should have been removed, but it\'s not.' );
	}
    
    /**
	 * Negative Test - sap_process_bookmark
     * 
     * @test 
     * @covers sap_mark_post_as_bookmarked
	 */
	function negative_sap_process_bookmark() {
        if( !$this->is_bookmarks_enabled() ){
            $this->markTestSkipped( "Bookmarks not enabled." );
            return false;// don't run tests
        }
        
        $current_user_id = bp_loggedin_user_id();
        
        $fake_post_id = -5;//negative integer
        sap_mark_post_as_bookmarked( $fake_post_id, $current_user_id );
        $this->assertFalse( sap_user_has_bookmarked_post( $current_user_id, $fake_post_id ), 'Passing negative integer as post id: Bookmark should not have been added, but it is.' );
        
        $fake_post_id = 'string value';
        sap_mark_post_as_bookmarked( $fake_post_id, $current_user_id );
        $this->assertFalse( sap_user_has_bookmarked_post( $current_user_id, $fake_post_id ), 'Passing string value as post id: Bookmark should not have been added, but it is.' );
	}
    
    /**
     * @test 
     * @covers sap_remove_bookmark_on_post_delete
     */
    function sap_remove_bookmark_on_post_delete(){
        if( !$this->is_bookmarks_enabled() ){
            $this->markTestSkipped( "Bookmarks not enabled." );
            return false;// don't run tests
        }
        
        $current_user_id = bp_loggedin_user_id();
        
		//1. Create a new post
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        
        //2. Add this post to current user's bookmarks
        sap_mark_post_as_bookmarked( $newpost, $current_user_id );
        
        //3. Trash the post
        wp_delete_post( $newpost );
        
        //4. Assert that post is removed from user's bookmarks
        $this->assertFalse( sap_user_has_bookmarked_post( $current_user_id, $newpost ), 'Bookmark not removed after post is trashed.' );
        
        //1. Create a new post
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        
        //2. Add this post to current user's bookmarks
        sap_mark_post_as_bookmarked( $newpost, $current_user_id );
        
        //3. Permanently delete the post
        wp_delete_post( $newpost, true );
        
        //4. Assert that post is removed from user's bookmarks
        $this->assertFalse( sap_user_has_bookmarked_post( $current_user_id, $newpost ), 'Bookmark not removed after post is deleted.' );
    }
    
    /**
     * @test
     * @covers sap_get_bookmark_button
     */
    function sap_get_bookmark_button(){
        if( !$this->is_bookmarks_enabled() ){
            $this->markTestSkipped( "Bookmarks not enabled." );
            return false;// don't run tests
        }
        
        //bookmark button is only displayed if is_single is true
        $newpost = self::factory()->post->create( array( 'post_title' => 'Unit Test post' ) );
        $permalink = get_permalink( $newpost );
        $this->go_to( $permalink );
        
        $bookmark_button = sap_get_bookmark_button();
        $this->assertNotEmpty( $bookmark_button, 'Bookmark button html is empty. It shouldn\'t be.' );
        
        $this->go_to( home_url() );
        
        $this->assertFalse( is_single(), 'is_single should return false, but it is true.' );
        
        $bookmark_button = sap_get_bookmark_button();
        $this->assertEmpty( $bookmark_button, 'Bookmark button html should be empty. It is not.' );
    }
}