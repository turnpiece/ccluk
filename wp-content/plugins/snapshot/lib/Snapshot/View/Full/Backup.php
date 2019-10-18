<?php // phpcs:ignore

/**
 * Full backups admin view class
 */
class Snapshot_View_Full_Backup {

	/**
	 * Singleton instance
	 *
	 * @var object Snapshot_View_Full_Backup
	 */
	private static $_instance;

	/**
	 * Page index reference
	 *
	 * @var string
	 */
	private $_page_idx;

	/**
	 * Backup model reference
	 *
	 * @var object Snapshot_Model_Full_Backup
	 */
	private $_model;

	/**
	 * Internal constructor
	 *
	 * Also sets up the model reference.
	 */
	private function __construct() {
		$this->_model = new Snapshot_Model_Full_Backup();
	}

	/**
	 * No public clones
	 */
	private function __clone() {}

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_View_Full_Backup
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Public view serving method
	 */
	public function run() {
		$hook = ( is_multisite() && is_network_admin() ? 'network_' : '' ) . 'admin_menu';
		add_action( $hook, array( $this, 'add_pages' ) );
		add_action( 'snapshot_destinations_render_list_before', array( $this, 'render_full_destination' ) );
	}

	/**
	 * Centralized message getter
	 *
	 * @param string $key Message key to look for
	 *
	 * @return string Message
	 */
	public static function get_message( $key ) {
		$me = self::get();
		$messages = array(
			'api_error'               => __( 'We were unable to find the appropriate API info in the remote service response.', SNAPSHOT_I18N_DOMAIN ),
			'request_error'           => __( 'It seems we encountered an issue communicating with the remote service.', SNAPSHOT_I18N_DOMAIN ),
			'check_connection'        => __( 'Please, make sure your site is able to perform remote requests to <code>%1$s</code>.', SNAPSHOT_I18N_DOMAIN ),
			'open_ticket'             => __( 'If you\'re still having this issue, please feel free to open a ticket with us <a href="%s" target="_blank">here</a>.', SNAPSHOT_I18N_DOMAIN ),
			'backup_list_fetch_error' => __( 'We have encountered an error refreshing the backup list.', SNAPSHOT_I18N_DOMAIN ),
			'reset_secret_key'        => __( 'You can reset your key <a href="%s">here</a>', SNAPSHOT_I18N_DOMAIN ),
			'missing_secret_key'      => __( 'You need to have your secret key entered in settings for automatic managed backups to work. You can get your key <a href="%s">here</a>.', SNAPSHOT_I18N_DOMAIN ),
		);
		$msg = ! empty( $messages[ $key ] )
			? $messages[ $key ]
			: false;

		return apply_filters(
			$me->get_filter( 'message' ),
			$msg, $key
		);
	}

	/**
	 * Register pages
	 */
	public function add_pages() {
		if ( is_multisite() ) {
			if ( ! is_super_admin() ) {
				return;
			}
			if ( ! is_network_admin() ) {
				return;
			}
		}

		$this->_page_idx = add_submenu_page(
			'snapshots_edit_panel',
			_x( 'Managed Backups', 'page label', SNAPSHOT_I18N_DOMAIN ),
			_x( 'Managed Backups', 'menu label', SNAPSHOT_I18N_DOMAIN ),
			$this->get_page_role(),
			'snapshots_full_backup_panel',
			array( $this, 'render_page' )
		);
		add_action( 'load-' . $this->get_page_idx(), array( $this, 'add_dependencies' ) );

		return $this->_page_idx;
	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter( $filter = false ) {
		if ( empty( $filter ) ) {
			return false;
		}
		if ( ! is_string( $filter ) ) {
			return false;
		}

		return 'snapshot-views-full_backup-' . $filter;
	}

	/**
	 * Get access roles for this page
	 *
	 * @return string WP role name
	 */
	public function get_page_role() {
		return is_multisite()
			? 'export'
			: 'manage_snapshots_items';
	}

	/**
	 * Return registered page ID
	 *
	 * @return string
	 */
	public function get_page_idx() {
		return $this->_page_idx;
	}

	/**
	 * Checks if current page is snapshot full backups admin page
	 *
	 * @return bool
	 */
	public function is_current_admin_page() {
		if ( ! is_admin() && ! is_network_admin() ) {
			return false;
		} // Not admin

		$idx = $this->get_page_idx();
		if ( ! $idx ) {
			return false;
		} // No page

		$screen = function_exists( 'get_current_screen' )
			? get_current_screen()
			: false;
		if ( empty( $screen ) || ! is_object( $screen ) || empty( $screen->id ) ) {
			return false;
		} // We don't know yet

		$sfx = is_multisite() && is_network_admin()
			? '-network'
			: '';

		return in_array( $screen->id, array( $idx, "{$idx}{$sfx}" ), true );
	}

	/**
	 * Decide on what actual pages should be rendering
	 */
	public function render_page() {
		$page = false;
		if ( ! current_user_can( $this->get_page_role() ) ) {
			return false;
		}

		if ( ! $this->_model->has_dashboard() ) {
			$page = 'get_started';
		} else if ( ! $this->_model->is_active() ) {
			$page = 'activate_backup';
		} else {
			$page = 'backups';
			$this->_model->get_backups(); // Refresh cache (if possible) and catch any errors early on
		}

		if ( ! empty( $page ) ) {
			$this->_include( $page );
		}

		// Reset any cached API errors, so we show full,
		// errorless interface on subsequent page reloads if possible
		$this->_model->remote()->reset_api_caches();
	}

	/**
	 * Renders full backups destinations fragment
	 */
	public function render_full_destination() {
		if ( ! current_user_can( 'manage_snapshots_destinations' ) ) {
			return '';
		}
		$this->add_dependencies();
		$this->_include( 'destination' );
	}

	/**
	 * Inject dependencies
	 */
	public function add_dependencies() {
		$root = untrailingslashit( WPMUDEVSnapshot::instance()->get_plugin_url() );
		$version = WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_VERSION' );
		wp_enqueue_style( 'snapshot-admin', $root . '/css/snapshots-admin-styles.css', false, $version );
		wp_enqueue_style( 'snapshot-full_backup-admin', $root . '/css/snapshots-full_backup-admin.css', false, $version );

		add_thickbox();
		wp_enqueue_script( 'snapshot-full_backup-admin', $root . '/js/snapshot-full_backup-admin.js', array( 'jquery', 'thickbox' ), $version );
		wp_localize_script( 'snapshot-full_backup-admin', '_snp_vars', array(
			'l10n' => array(
				'generic_error'      => __( 'Aw shucks, something went wrong :( Instead of the beautiful response we expected, we got this:', SNAPSHOT_I18N_DOMAIN ),
				'starting'           => __( 'Starting...', SNAPSHOT_I18N_DOMAIN ),
				'processing'         => __( 'Processing (step %d)', SNAPSHOT_I18N_DOMAIN ),
				'processing_percent' => __( 'Backing up... %d%', SNAPSHOT_I18N_DOMAIN ),
				'finishing'          => __( 'Finishing up', SNAPSHOT_I18N_DOMAIN ),
				'estimating'         => __( 'Estimating backup size, this might take a bit. Please, hold on', SNAPSHOT_I18N_DOMAIN ),
				'snapshot_logs'      => __( 'Snapshot Logs', SNAPSHOT_I18N_DOMAIN ),
			),
		) );
	}

	/**
	 * Template inclusion helper
	 *
	 * @param string|bool $template Template to load
	 *
	 * @return string
	 */
	private function _include( $template = false ) {
		return Snapshot_View_Template::get( 'full' )->load(
			$template,
			array( 'model' => $this->_model )
		);
	}
}