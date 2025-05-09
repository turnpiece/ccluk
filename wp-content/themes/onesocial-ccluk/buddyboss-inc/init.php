<?php

/**
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial 1.0.0
 */
/* * **************************** MAIN BUDDYBOSS THEME CLASS ***************************** */

class BuddyBoss_Theme
{

	/**
	 * BuddyBoss parent/main theme path
	 * @var string
	 */
	public $tpl_dir;

	/**
	 * BuddyBoss parent theme url
	 * @var string
	 */
	public $tpl_url;

	/**
	 * BuddyBoss includes path
	 * @var string
	 */
	public $inc_dir;

	/**
	 * BuddyBoss includes url
	 * @var string
	 */
	public $inc_url;

	/**
	 * BuddyBoss options array
	 * @var array
	 */
	public $opt;

	/**
	 * BuddyBoss modules array
	 * @var array
	 */
	public $mods;

	/**
	 * Check if BuddyPress is active
	 */
	public $buddypress_active;

	/**
	 * Check if BBPress is active
	 */
	public $bbpress_active;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		/**
		 * Globals, constants, theme path etc
		 */
		$this->globals();

		/**
		 * Load BuddyBoss options
		 */
		$this->options();

		/**
		 * Load required theme files
		 */
		$this->includes();

		/**
		 * Actions/filters
		 */
		$this->actions_filters();

		/**
		 * Assets
		 */
		$this->assets();
	}

	/**
	 * Global variables
	 */
	public function globals()
	{
		global $bp, $buddyboss_debug_log, $buddyboss_js_params;

		// Get theme path
		$this->tpl_dir = get_stylesheet_directory();

		// Get theme url
		$this->tpl_url = get_stylesheet_directory_uri();

		// Get includes path
		$this->inc_dir = $this->tpl_dir . '/buddyboss-inc';

		// Get includes url
		$this->inc_url = $this->tpl_url . '/buddyboss-inc';

		if (!defined('BUDDYBOSS_DEBUG')) {
			define('BUDDYBOSS_DEBUG', false);
		}

		// Set BuddyPress and BBPress as inactive by default, then we hook into
		// their init actions to set these variables to true when they're active
		$this->buddypress_active = false;
		$this->bbpress_active	 = false;

		// A variable to hold the event log
		$buddyboss_debug_log = "";

		// Child themes can add variables to this array for JS on the front end
		if (empty($buddyboss_js_params)) {
			$buddyboss_js_params = array();
		}
	}

	/**
	 * Load options
	 */
	public function options()
	{
		$opt = get_option('buddyboss_theme_options');
	}

	/**
	 * Includes
	 */
	public function includes()
	{
		// Theme setup
		require_once($this->inc_dir . '/theme-functions.php');
		require_once($this->inc_dir . '/extra-functions.php');

		// Ajax file
		require_once($this->inc_dir . '/ajax-load-posts.php');

		// Recommend Posts
		if (! function_exists('buddyboss_sap')) {
			require_once($this->inc_dir . '/recommend-posts.php');
		}

		// Option settings
		require_once($this->inc_dir . '/buddyboss-framework/options/setting-options.php');

		// User Options & Settings
		require_once($this->inc_dir . '/users-options.php');

		// Login popup
		require_once($this->inc_dir . "/popup/user_email_verify.php");
		require_once($this->inc_dir . "/popup/ajax_login.php");
		require_once($this->inc_dir . "/popup/ajax_register.php");

		// Debug functions
		require_once($this->inc_dir . '/debug.php');

		if (BUDDYBOSS_DEBUG)
			require_once($this->inc_dir . '/bp-debug.php');

		//Cache update hook
		require_once($this->inc_dir . '/cache-update-hook.php');
	}

	function backend_should_load()
	{

		if (is_admin()) {
			return true;
		}

		$onesocial_typography = get_transient("onesocial_typography");

		if (empty($onesocial_typography)) {
			return true;
		}

		return false;
	}

	/**
	 * Actions and filters
	 */
	public function actions_filters()
	{
		if (BUDDYBOSS_DEBUG) {
			add_action('bp_footer', 'buddyboss_dump_log');
		}

		// If BuddyPress or BBPress is active we'll update our
		// global variable (theme uses this later on)
		add_action('bp_init', array($this, 'set_buddypress_active'));
		add_action('bbp_init', array($this, 'set_bbpress_active'));
	}

	/**
	 * Assets
	 */
	public function assets()
	{
		if (!class_exists('BP_Legacy')) {
			return false;
		}
	}

	/**
	 * Set BuddyPress global variable to true
	 */
	public function set_buddypress_active()
	{
		$this->buddypress_active = true;
	}

	/**
	 * Set BBPress global variable to true
	 */
	public function set_bbpress_active()
	{
		$this->bbpress_active = true;
	}

	/**
	 * Utility function for loading modules
	 */
	public function add_mod($mod_info)
	{
		if (!isset($mod_info['name'])) {
			wp_die(__('Module does not have the proper info array', 'onesocial'));
		}

		$this->mods[$mod_info['name']] = $mod_info;

		return true;
	}

	/**
	 * Check if a module is active
	 */
	public function is_active($name)
	{
		$active = false;

		// Check for active module
		if (isset($this->mods[$name]) && isset($this->mods[$name]['active']) && $this->mods[$name]['active'] == true) {
			$active = true;
		}

		// Check for active module (old way, soon to be deprecated)
		if (isset($this->opt['mod_' . $name])) {
			return $this->opt['mod_' . $name];
		}

		return $active;
	}
}

$GLOBALS['onesocial'] = new BuddyBoss_Theme;

function onesocial()
{
	return $GLOBALS['onesocial'];
}

/*
 * Change BuddyPress avatar size
 */
if (!defined('BP_AVATAR_FULL_WIDTH')) {
	define('BP_AVATAR_FULL_WIDTH', 280);
}

if (!defined('BP_AVATAR_FULL_HEIGHT')) {
	define('BP_AVATAR_FULL_HEIGHT', 280);
}
