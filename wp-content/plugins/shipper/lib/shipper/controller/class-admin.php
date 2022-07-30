<?php
/**
 * Shipper controllers: admin controller class
 *
 * Sets up and works with front-facing requests on admin pages.
 *
 * @package shipper
 */

/**
 * Admin controller class
 */
class Shipper_Controller_Admin extends Shipper_Controller {

	/**
	 * Gets order in which menu registration takes place
	 *
	 * @return int Page order
	 */
	public function get_page_order() {
		return 10;
	}

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action(
			( is_multisite() ? 'network_admin_menu' : 'admin_menu' ),
			array( $this, 'add_menu' ),
			$this->get_page_order()
		);
		if ( ! has_filter( 'plugin_action_links_' . plugin_basename( 'shipper/shipper.php' ) ) ) {
			add_filter(
				'plugin_action_links_' . plugin_basename( 'shipper/shipper.php' ),
				array(
					&$this,
					'add_setting_links',
				)
			);
		}
	}

	/**
	 * Returns user shipping capability
	 *
	 * @return string
	 */
	public function get_capability() {
		return is_multisite()
			? 'manage_network_options'
			: 'manage_options';
	}

	/**
	 * Add setting links
	 *
	 * @param string $links links of settings.
	 *
	 * @return array
	 */
	public function add_setting_links( $links ) {
		$mylinks = array(
			'<a href="' . network_admin_url( 'admin.php?page=shipper-settings&tool=migration' ) . '">' . __( 'Settings', 'shipper' ) . '</a>',
		);

		$mylinks = array_merge( $links, $mylinks );
		$mylinks = array_merge(
			$mylinks,
			array(
				'<a target="_blank" href="https://wpmudev.com/docs/wpmu-dev-plugins/shipper/">' . __( 'Docs', 'shipper' ) . '</a>',
			)
		);

		return $mylinks;
	}

	/**
	 * Sets up menu items
	 *
	 * Also sets up front-end dependencies loading on page load.
	 */
	public function add_menu() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			return false;
		}
		$capability = $this->get_capability();

		add_menu_page(
			_x( 'Shipper', 'page label', 'shipper' ),
			_x( 'Shipper', 'menu label', 'shipper' ),
			$capability,
			'shipper',
			array( Shipper_Controller_Admin_Dashboard::get(), 'render_dashboard' ),
			Shipper_Helper_Assets::get_encoded_icon()
		);
	}

	/**
	 * Migrate page migration type selection (default) data getter
	 *
	 * @return array
	 */
	public function handle_migration_destinations_cache() {
		if ( ! shipper_user_can_ship() ) {
			return array();
		}
		$destinations = new Shipper_Model_Stored_Destinations();

		$list   = $destinations->get_data();
		$errors = array();

		if ( empty( $list ) ) {
			// Empty list, not even the current site in it. Add current site.
			$task   = new Shipper_Task_Api_Destinations_Add();
			$result = $task->apply();
			if ( ! empty( $result ) ) {
				// Success - expire destinations so they're refreshed in next step.
				$destinations->set_timestamp( false );

				// Let's also refresh our systems info.
				$info_task = new Shipper_Task_Api_Info_Set();
				$system    = new Shipper_Model_System();
				$info_task->apply( $system->get_data() );
			}
			$errors = array_merge( $errors, $task->get_errors() );
		}

		if ( empty( $errors ) && $destinations->is_expired() ) {
			// Expired destinations cache - update.
			$errors = array_merge( $errors, $this->update_destinations_cache() );
		}

		foreach ( $errors as $error ) {
			Shipper_Helper_Log::write(
				/* translators: %s: error message. */
				sprintf( __( 'Destination error: %s', 'shipper' ), $error->get_error_message() )
			);
		}

		return $errors;
	}


	/**
	 * Updates the destinations cache model
	 *
	 * @return array Errors, if any
	 */
	public function update_destinations_cache() {
		$task   = new Shipper_Task_Api_Destinations_Get();
		$result = $task->apply();
		if ( ! empty( $result ) ) {
			// We got the listing result - update stored destinations cache.
			$destinations = new Shipper_Model_Stored_Destinations();
			$destinations->set_data( $result );
			$destinations->set_timestamp( time() );
			$destinations->save();
		}

		return $task->get_errors();
	}

	/**
	 * Adds shared UI body class
	 *
	 * @see https://wpmudev.github.io/shared-ui/
	 *
	 * @param string $classes Admin page body classes this far.
	 *
	 * @return string
	 */
	public function add_admin_body_class( $classes ) {
		if ( ! shipper_user_can_ship() ) {
			return $classes;
		}

		$cls   = explode( ' ', $classes );
		$cls[] = 'sui-2-10-8';
		$cls[] = 'shipper-admin';
		$cls[] = 'shipper-sui';

		return join( ' ', $cls );
	}

	/**
	 * Adds front-end dependencies that are shared between Shipper admin pages
	 */
	public function add_shared_dependencies() {
		if ( ! shipper_user_can_ship() ) {
			return false;
		}

		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );
		$assets = new Shipper_Helper_Assets();

		wp_enqueue_style(
			'shipper',
			$assets->get_asset( 'css/shipper.css' ),
			null,
			SHIPPER_VERSION
		);
		wp_enqueue_script(
			'shipper',
			$assets->get_asset( 'js/shipper.js' ),
			array( 'jquery', 'underscore', 'clipboard' ),
			SHIPPER_VERSION,
			true
		);

		$message = 'Your account authentication failed. Please try again or contact our support team for help.';
		$model = new Shipper_Model_Stored_Options();
		wp_localize_script(
			'shipper',
			'_shipper',
			array(
				'update_interval'    => Shipper_Helper_Assets::get_update_interval(),
				'per_page'           => $model->get( Shipper_Model_Stored_Options::KEY_PER_PAGE, 10 ),
				'i18n'               => array(
					'google_auth_failed' => Shipper_Helper_Assets::has_docs_links()
						? wp_kses_post( __( 'Your account authentication failed. Please try again or contact our <a href="https://wpmudev.com/hub2/support#get-support" target="_blank">support team</a> for help.', 'shipper' ) )
						: __( 'Your account authentication failed. Please try again or contact our support team for help.', 'shipper' ),
				),
			)
		);

		$this->add_black_friday_dependencies();

		// Trigger this on print, but early enough!
		add_action( 'admin_print_scripts', array( $this, 'reenable_heartbeat' ), 0 );
	}


	/**
	 * Re-adds the heartbeat JS if it's been deregistered by a plugin
	 *
	 * Gets triggered "too late" to ensure it's run in time.
	 */
	public function reenable_heartbeat() {
		if ( wp_script_is( 'heartbeat', 'done' ) ) {
			return false;
		}
		if ( wp_script_is( 'heartbeat', 'registered' ) && wp_script_is( 'heartbeat', 'enqueued' ) ) {
			// Heartbeat's present, move on.
			return false;
		}

		$suffix = wp_scripts_get_suffix();
		wp_scripts()->add(
			'heartbeat',
			"/wp-includes/js/heartbeat$suffix.js",
			array( 'jquery', 'wp-hooks' ),
			false,
			1
		);
		wp_scripts()->localize(
			'heartbeat',
			'heartbeatSettings',
			apply_filters( 'heartbeat_settings', array() )
		);
	}

	/**
	 * Whether or not the current user can access shipper pages
	 *
	 * @return bool
	 */
	public function can_user_access_shipper_pages() {
		$capability = $this->get_capability();
		if ( ! current_user_can( $capability ) ) {
			return false;
		}

		$allowed_users = shipper_get_allowed_users();
		if ( ! empty( $allowed_users ) ) {
			$user_id = get_current_user_id();

			return in_array(
				(int) $user_id,
				array_map( 'intval', $allowed_users ),
				true
			);
		}

		// We passed capabilities check, but we don't have any explicitly allowed users.
		// That's kinda weird, but okay.
		// Let's fall back to allowing access.
		return true;
	}

	/**
	 * Adds Black Friday dependencies
	 */
	private function add_black_friday_dependencies() {
		if ( ! shipper_is_black_friday() ) {
			return;
		}

		$assets = new Shipper_Helper_Assets();

		wp_enqueue_script(
			'shipper-black-friday',
			$assets->get_asset( 'js/shipper-black-friday.js' ),
			null,
			SHIPPER_VERSION,
			true
		);

		wp_localize_script(
			'shipper-black-friday',
			'shipper_bf',
			array(
				'header'  => esc_html__( 'Black Friday Offer!', 'shipper' ),
				'message' => esc_html__( 'Get 11 Pro plugins on unlimited sites and much more with 50% OFF WPMU DEV Agency plan FOREVER', 'shipper' ),
				'notice'  => esc_html__( '*Only admin users can see this message', 'shipper' ),
				'link'    => 'https://wpmudev.com/black-friday/?coupon=BFP-2021&utm_source=shipper_pro&utm_medium=referral&utm_campaign=bf2021',
			)
		);
	}
}