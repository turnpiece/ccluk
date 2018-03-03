<?php
/**
 * Class BBUB_Sap_Functions_Test
 *
 * @package Buddypress_User_Blog
 */

class BBUB_Sap_Functions_Test extends \BBoss_BP_UnitTestCase {
    
    
	/**
     * @test
     * @covers sap_save_post
	 */
	function sap_save_post() {
        /**
         * This function must be testd with all kinds of varying inputs.
         * But since this is tied to ajax calls, it is impossible to test it without separating the logic from ajax bits.
         */
        
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
	}
    
    /**
     * @test
     * @covers sap_render_category_recursive_fun
     */
    function sap_render_category_recursive_fun(){
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
    
    /**
     * @test
     * @covers sap_categorized_blog
     */
    function sap_categorized_blog(){
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
