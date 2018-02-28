<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Module
 *
 * @since 1.0
 */
abstract class Forminator_Admin_Module {

	/**
	 * @var array
	 */
	public $pages = array();

	/**
	 * @var string
	 */
	public $page = '';

	/**
	 * @var string
	 */
	public $page_edit = '';

	/**
	 * @var string
	 */
	public $page_entries = '';

	/**
	 * Forminator_Admin_Module constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->init();
		$this->includes();

		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_head', array( $this, "hide_menu_pages" ) );
		add_filter( 'forminator_data', array( $this, "add_js_defaults" ) );
		add_filter( 'forminator_l10n', array( $this, "add_l10n_strings" ) );
		add_filter( 'parent_file', array( $this, 'highlight_admin_parent') );
	}

	/**
	 * Init
	 *
	 * @since 1.0
	 */
	public function init() {
		// Call init instead of __construct in modules
	}

	/**
	 * Attach admin pages
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {}

	/**
	 * Hide pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {}

	/**
	 * Used to include files
	 *
	 * @since 1.0
	 */
	public function includes() {}

	/**
	 * Inject module options to JS
	 *
	 * @since 1.0
	 * @param $data
	 * @return mixed
	 */
	public function add_js_defaults( $data ) {
		return $data;
	}

	/**
	 * Inject l10n strings to JS
	 *
	 * @param $strings
	 * @since 1.0
	 * @return mixed
	 */
	public function add_l10n_strings( $strings ) {
		return $strings;
	}

	/**
	 * Is the admin page being viewed in edit mode
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public static function is_edit() {
		return  (bool) filter_input( INPUT_GET, "id", FILTER_VALIDATE_INT );
	}

	/**
	 * Is the module admin dashboard page
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_admin_home() {
		return  (bool) isset( $_GET['page'] ) && ( $_GET['page'] == $this->page );
	}

	/**
	 * Is the module admin new/edit page
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_admin_wizard() {
		return  (bool) isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_edit );
	}

	/**
	 * Highlight parent page in sidebar
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function highlight_admin_parent( $file ) {
		global $plugin_page;

		if ( $this->page_edit == $plugin_page || $this->page_entries == $plugin_page ) {
			$plugin_page = $this->page;
		}

		return $file;
	}
}