<?php 
/**
 * BBoss_Plugin_Unit_Tests_Bootstrap
 *
 * @since 2.0
 */
class BBoss_Plugin_Unit_Tests_Bootstrap {

	/** @var \BBoss_Plugin_Unit_Tests_Bootstrap instance */
	protected static $instance = null;

	/** @var string directory where wordpress-tests-lib is installed */
	public $wp_tests_dir;

	/** @var string testing directory */
	public $tests_dir;

	/** @var string plugin directory */
	public $plugin_dir;

	// directory storing dependency plugins
	public $modules_dir;

	/**
	 * Setup the unit testing environment
	 *
	 * @since 2.0
	 */
	function __construct() {

		ini_set( 'display_errors','on' );
		error_reporting( E_ALL );

		$this->tests_dir    = dirname( __FILE__ );// ../plugins/this-plugin/test
		$this->plugin_dir   = dirname( $this->tests_dir );// ../plugins/this-plugin
		$this->modules_dir  = dirname( dirname( $this->tests_dir ) );// ../plugins
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

		$_SERVER['REMOTE_ADDR'] = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? $_SERVER['REMOTE_ADDR'] : '';
		$_SERVER['SERVER_NAME'] = ( isset( $_SERVER['SERVER_NAME'] ) ) ? $_SERVER['SERVER_NAME'] : 'wcsg_test';

		// load test function so tests_add_filter() is available
		require_once( $this->wp_tests_dir  . '/includes/functions.php' );
        
        
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_current_plugin' ) );
        
        // load dependency plugins
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_dependencies' ) );
		
		// Run install routine of dependency plugins, if any
		tests_add_filter( 'setup_theme', array( $this, 'install_dependencies' ) );

		$GLOBALS['wp_options'] = array(
			'active_plugins' => array(
				$this->modules_dir . '/buddypress/bp-loader.php',
				$this->modules_dir . '/buddypress-user-blog/bp-user-blog.php',
			),
		);
        
		// load the WP testing environment
		require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );

		// load testing framework
		$this->includes();

		// manually activate plugins
		$active_plugins   = get_option( 'active_plugins', array() );
		$active_plugins[] = 'buddypress/bp-loader.php';
		$active_plugins[] = 'buddypress-user-blog/bp-user-blog.php';
        
		update_option( 'active_plugins', $active_plugins );
	}

	/**
	 * Load WooCommerce
	 *
	 * @since 2.0
	 */
	public function load_dependencies() {
        // load buddypress
        if ( ! defined( 'BP_TESTS_DIR' ) ) {
            define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../buddypress/tests/phpunit' );
        }
        
        if( file_exists( BP_TESTS_DIR . '/includes/loader.php' ) ){
            // loader.php will ensure that BP gets installed at the right time, and
            // that BP is initialized before your own plugin
            require BP_TESTS_DIR . '/includes/loader.php';
            
        } else {
            die( "BuddyPress tests can't be found. Make sure you have downloaded buddypress plugin from github so that it contains test files. \n" );
        }
	}

    public function load_current_plugin(){
        // bootstrap
        require_once( $this->modules_dir . '/buddypress-user-blog/tests/includes/loader.php' );
        
		// load current plugin file
        require_once( $this->modules_dir . '/buddypress-user-blog/bp-user-blog.php' );
    }
    
	/**
	 * Load WooCommerce for testing
	 *
	 * @since 2.0
	 */
	function install_dependencies() {
        
	}

	/**
	 * Load test cases and factories
	 *
	 * @since 2.0
	 */
	public function includes() {
        //to make the BP_UnitTestCase class available
        require BP_TESTS_DIR . '/includes/testcase.php';
        
        //make available our custom parent unit test class
        require_once( $this->modules_dir . '/buddypress-user-blog/tests/includes/class-bboss-bp-unittestcase.php' );
	}

	/**
	 * Get the single class instance
	 *
	 * @since 2.0
	 * @return WCS_Unit_Tests_Bootstrap
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

BBoss_Plugin_Unit_Tests_Bootstrap::instance();

if( !function_exists( 'bboss_ut_plugin_object' ) ){
    /**
     * Get the plugin's main class object.
     * 
     * @return \BuddyBoss_SAP_Plugin
     */
    function bboss_ut_plugin_object(){
        return buddyboss_sap();
    }
}