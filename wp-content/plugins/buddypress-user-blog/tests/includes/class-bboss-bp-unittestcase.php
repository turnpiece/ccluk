<?php
/**
 * Class BBUB_Sap_Functions_Test
 *
 * @package Buddypress_User_Blog
 */

/**
 * Class to perform some common logic in all our test cases.
 */
class BBoss_BP_UnitTestCase extends \BP_UnitTestCase {
    /**
     * Original user id
     * @var type int
     */
    protected static $orig_user;
    
    /**
     * New user id
     * @var type int
     */
    protected static $new_user;

    /**
     * Most of the test functions here need a logged in user.
     * So, lets set a logged in user globally, instead of duplicating code in each test function.
     */
    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
        
        self::$orig_user = get_current_user_id();
        
        self::$new_user = self::factory()->user->create( array( 'role' => 'subscriber' ) );
        self::set_current_user( self::$new_user );
    }

    public static function tearDownAfterClass(){
        self::set_current_user( self::$orig_user );
    }
    
    function assertPreConditions() {
		parent::assertPreConditions();

		// do_action( 'bp_setup_globals' ) is called in parent's ( BP_UnitTestCase ) assertPreConditions method.
        // This removes logged in user info
		// Lets reset it
		self::set_current_user( self::$new_user );
	}
}
