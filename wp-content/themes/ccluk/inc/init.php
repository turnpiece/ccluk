<?php

/**
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCLUK 2.6.7
 */
/* * **************************** MAIN CCLUK THEME CLASS ***************************** */

class CCLUK_Theme
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
	 * Constructor
	 */
	public function __construct()
	{
		/**
		 * Globals, constants, theme path etc
		 */
		$this->globals();

		/**
		 * Load required theme files
		 */
		$this->includes();
	}

	/**
	 * Global variables
	 */
	public function globals()
	{
		// Get theme path
		$this->tpl_dir = get_stylesheet_directory();

		// Get theme url
		$this->tpl_url = get_stylesheet_directory_uri();

		// Get includes path
		$this->inc_dir = $this->tpl_dir . '/inc';

		// Get includes url
		$this->inc_url = $this->tpl_url . '/inc';
	}

	/**
	 * Includes
	 */
	public function includes()
	{
		// Theme setup
		require_once($this->inc_dir . '/theme-functions.php');

		// Ajax file
		require_once($this->inc_dir . '/ajax-load-posts.php');

		//Cache update hook
		require_once($this->inc_dir . '/cache-update-hook.php');
	}
}

new CCLUK_Theme;
