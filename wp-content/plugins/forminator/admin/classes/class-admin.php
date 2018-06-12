<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin
 *
 * @since 1.0
 */
class Forminator_Admin {

	/**
	 * @var array
	 */
	public $pages = array();

	/**
	 * Forminator_Admin constructor.
	 */
	public function __construct() {
		$this->includes();

		// Init admin pages
		add_action( 'admin_menu', array( $this, 'add_dashboard_page' ) );

		// Init Admin AJAX class
		new Forminator_Admin_AJAX();

		/**
		 * Triggered when Admin is loaded
		 */
		do_action( 'forminator_admin_loaded' );
	}

	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	private function includes() {
		// Admin pages
		include_once forminator_plugin_dir() . 'admin/pages/dashboard-page.php';
		include_once forminator_plugin_dir() . 'admin/pages/entries-page.php';
		include_once forminator_plugin_dir() . 'admin/pages/integrations-page.php';
		include_once forminator_plugin_dir() . 'admin/pages/settings-page.php';

		// Admin AJAX
		include_once forminator_plugin_dir() . 'admin/classes/class-admin-ajax.php';

		// Admin Data
		include_once forminator_plugin_dir() . 'admin/classes/class-admin-data.php';

		// Admin l10n
		include_once forminator_plugin_dir() . 'admin/classes/class-admin-l10n.php';
	}

	/**
	 * Initialize Dashboard page
	 *
	 * @since 1.0
	 */
	public function add_dashboard_page() {
		$this->pages['forminator'] = new Forminator_Dashboard_Page( 'forminator', 'dashboard', __( 'Forminator Pro', Forminator::DOMAIN ), __( 'Forminator Pro', Forminator::DOMAIN ), false, false );
		$this->pages['forminator-dashboard'] = new Forminator_Dashboard_Page( 'forminator', 'dashboard', __( 'Forminator Dashboard', Forminator::DOMAIN ), __( 'Dashboard', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Add Integrations page
	 *
	 * @since 1.1
	 */
	public function add_integrations_page() {
		add_action( 'admin_menu', array( $this, 'init_integrations_page' ) );
	}

	/**
	 * Initialize Integrations page
	 *
	 * @since 1.1
	 */
	public function init_integrations_page() {
		$this->pages['forminator-integrations'] = new Forminator_Integrations_Page(
			'forminator-integrations',
			'integrations',
			__( 'Integrations', Forminator::DOMAIN ),
			__( 'Integrations', Forminator::DOMAIN ),
			'forminator'
		);

		//TODO: remove this after converted to JS
		$addons = Forminator_Addon_Loader::get_instance()->get_addons()->to_array();
		foreach ($addons as $slug => $addon_array) {
			$addon_class = forminator_get_addon($slug);

			if ($addon_class && is_callable( array( $addon_class, 'admin_hook_html_version' ))) {
				call_user_func( array( $addon_class, 'admin_hook_html_version' ));
			}
		}

	}

	/**
	 * Add Settings page
	 *
	 * @since 1.0
	 */
	public function add_settings_page() {
		add_action( 'admin_menu', array( $this, 'init_settings_page' ) );
	}

	/**
	 * Initialize Settings page
	 *
	 * @since 1.0
	 */
	public function init_settings_page() {
		$this->pages['forminator-settings'] = new Forminator_Settings_Page( 'forminator-settings', 'settings', __( 'Global Settings', Forminator::DOMAIN ), __( 'Settings', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Add Entries page
	 *
	 * @since 1.0.5
	 */
	public function add_entries_page() {
		add_action( 'admin_menu', array( $this, 'init_entries_page' ) );
	}

	/**
	 * Initialize Entries page
	 *
	 * @since 1.0.5
	 */
	public function init_entries_page() {
		$this->pages['forminator-entries'] = new Forminator_Entries_Page(
			'forminator-entries',
			'entries',
			__( 'Forminator Submissions', Forminator::DOMAIN ),
			__( 'Submissions', Forminator::DOMAIN ),
			'forminator'
		);
	}
}