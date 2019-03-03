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
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) { return false; }

		add_action(
			(is_multisite() ? 'network_admin_menu' : 'admin_menu'),
			array( $this, 'add_menu' )
		);
	}

	/**
	 * Returns user shipping capability
	 *
	 * @return string
	 */
	public function get_capability() {
		return is_multisite()
			? 'manage_network_options'
			: 'manage_options'
		;
	}

	/**
	 * Sets up menu items
	 *
	 * Also sets up front-end dependencies loading on page load.
	 */
	public function add_menu() {
		$capability = $this->get_capability();
		if ( ! current_user_can( $capability ) ) { return false; }

		add_menu_page(
			_x( 'Shipper', 'page label', 'shipper' ),
			_x( 'Shipper', 'menu label', 'shipper' ),
			$capability,
			'shipper',
			array( $this, 'page_migrate' ),
			Shipper_Helper_Assets::get_encoded_icon()
		);

		$migrate = add_submenu_page(
			'shipper',
			_x( 'Migrate', 'page label', 'shipper' ),
			_x( 'Migrate', 'menu label', 'shipper' ),
			$capability,
			'shipper',
			array( $this, 'page_migrate' )
		);
		$tools = add_submenu_page(
			'shipper',
			_x( 'Tools', 'page label', 'shipper' ),
			_x( 'Tools', 'menu label', 'shipper' ),
			$capability,
			'shipper-tools',
			array( $this, 'page_tools' )
		);
		$settings = add_submenu_page(
			'shipper',
			_x( 'Settings', 'page label', 'shipper' ),
			_x( 'Settings', 'menu label', 'shipper' ),
			$capability,
			'shipper-settings',
			array( $this, 'page_settings' )
		);
		add_action( "load-{$migrate}", array( $this, 'add_migrate_dependencies' ) );
		add_action( "load-{$tools}", array( $this, 'add_tools_dependencies' ) );
		add_action( "load-{$settings}", array( $this, 'add_settings_dependencies' ) );

		add_action( "load-{$migrate}", array( $this, 'do_migration_begin_redirection' ) );
		add_action( "load-{$migrate}", array( $this, 'do_migration_complete_redirection' ) );
		add_action( "load-{$settings}", array( $this, 'save_settings' ) );
	}

	/**
	 * Redirects migration page to begin the migration actual processing
	 */
	public function do_migration_begin_redirection() {
		if ( ! shipper_user_can_ship() ) { return false; }

		if ( ! empty( $_GET['begin'] ) ) {
			// Already beginning.
			return false;
		}

		$args = array(
			'type',
			'site',
			'check',
		);
		$do_not_begin = false;
		foreach ( $args as $arg ) {
			if ( empty( $_GET[ $arg ] ) ) {
				$do_not_begin = true;
				break;
			}
		}
		if ( $do_not_begin ) {
			// Don't do anything.
			return false;
		}

		Shipper_Controller_Runner_Migration::get()->begin();
		wp_safe_redirect( esc_url_raw( add_query_arg( 'begin', true ) ) );
		die;
	}

	/**
	 * Redirects admin migration page during migration actual processing
	 */
	public function do_migration_complete_redirection() {
		if ( ! shipper_user_can_ship() ) { return false; }

		$migration = new Shipper_Model_Stored_Migration;
		$not_actionable = $migration->is_completed() || $migration->is_empty();
		if ( $not_actionable && isset( $_GET['begin'] ) ) {
			wp_safe_redirect(esc_url_raw(remove_query_arg(array(
				'type',
				'site',
				'check',
				'begin',
			))));
			die;
		}
	}

	/**
	 * Render migration setup pageset
	 *
	 * @param string $type Migration type.
	 * @param int    $site Destination site ID.
	 * @param bool   $check Whether we're at the preflight check stage or not.
	 */
	public function render_page_migrate_setup( $type, $site, $check = false ) {
		if ( ! shipper_user_can_ship() ) { return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) ); }

		$errors = $this->update_destinations();
		$destinations = new Shipper_Model_Stored_Destinations;

		$tpl = new Shipper_Helper_Template;
		$tpl->render( 'pages/migration/selection', array(
			'type' => $type,
			'site' => $site,
			'check' => $check,
			'errors' => $errors,
			'destinations' => $destinations,
		));
	}

	/**
	 * Render migration progress pageset
	 *
	 * @param string $type Migration type.
	 * @param int    $site Destination site ID.
	 */
	public function render_page_migrate_progress( $type, $site ) {
		if ( ! shipper_user_can_ship() ) { return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) ); }

		$errors = $this->update_destinations();
		$destinations = new Shipper_Model_Stored_Destinations;

		$tpl = new Shipper_Helper_Template;
		$tpl->render( 'pages/migration/progress', array(
			'type' => $type,
			'site' => $site,
			'check' => true,
			'begin' => true,
			'progress' => 0,
			'errors' => $errors,
			'destinations' => $destinations,
		));
	}

	/**
	 * Dispatches migrate page states.
	 */
	public function page_migrate() {
		if ( ! shipper_user_can_ship() ) { return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) ); }

		$migration = new Shipper_Model_Stored_Migration;
		$destinations = new Shipper_Model_Stored_Destinations;

		if ( $migration->is_active() ) {
			// If we have active migration, let's go with it.
			$type = $migration->get_type();
			$site_hash = Shipper_Model_Stored_Migration::TYPE_EXPORT === $type
				? $destinations->get_by_domain( $migration->get_destination() )
				: $destinations->get_by_domain( $migration->get_source() );
			$site = $site_hash['site_id'];
			$check = true;
			$begin = true;
		}

		if ( empty( $type ) ) {
			$type = ! empty( $_GET['type'] )
				? sanitize_text_field( $_GET['type'] )
				: false
			;
		}

		if ( empty( $site ) ) {
			$site = ! empty( $_GET['site'] )
				? (int) sanitize_text_field( $_GET['site'] )
				: false
			;
		}

		if ( empty( $check ) ) {
			$check = ! empty( $_GET['check'] )
				? sanitize_text_field( $_GET['check'] )
				: false
			;
		}

		if ( empty( $begin ) ) {
			$begin = ! empty( $_GET['begin'] )
				? sanitize_text_field( $_GET['begin'] )
				: false
			;
		}

		// First, check if we have the stuff to skip preflight.
		if ( ! empty( $type ) && ! empty( $site ) && empty( $check ) ) {
			$ctrl = Shipper_Controller_Runner_Preflight::get();
			if ( $ctrl->is_done() && ! $ctrl->has_issues() ) {
				// So apparently we ran the preflight and there are no issues.
				// Carry on with the migration.
				$check = true;
			}
		}

		// Decision time!
		if ( ! empty( $type ) && ! empty( $site ) ) {
			// Alright, we have the initial selection set up.
			// Let's see where do we go from here.
			if ( empty( $check ) ) {
				// About to run pre-flight check. Prepare the migration.
				Shipper_Controller_Runner_Migration::get()->prepare( $type, $site );
			} else {
				// We have done the preflight, what's next?
				if ( ! empty( $begin ) ) {
					// Okay, migration bootstrapped and ready to go - run it.
					Shipper_Controller_Runner_Migration::get()->run();
				}
			}
		} else {
			// Init page. Clear preflight.
			$ctrl = Shipper_Controller_Runner_Preflight::get()->clear();
			if ( ! $migration->is_active() ) {
				$estimate = new Shipper_Model_Stored_Estimate;
				$estimate->clear()->save();
			}
		}

		return empty( $type ) || empty( $site ) || empty( $check ) || empty( $begin )
			? $this->render_page_migrate_setup( $type, $site, $check )
			: $this->render_page_migrate_progress( $type, $site );
	}

	/**
	 * Migrate page migration type selection (default) data getter
	 *
	 * @return array
	 */
	public function update_destinations() {
		if ( ! shipper_user_can_ship() ) { return array(); }
		$destinations = new Shipper_Model_Stored_Destinations;

		$list = $destinations->get_data();
		$errors = array();

		if ( empty( $list ) ) {
			// Empty list, not even the current site in it. Add current site.
			$task = new Shipper_Task_Api_Destinations_Add;
			$result = $task->apply();
			if ( ! empty( $result ) ) {
				// Success - expire destinations so they're refreshed in next step.
				$destinations->set_timestamp( false );

				// Let's also refresh our systems info.
				$info_task = new Shipper_Task_Api_Info_Set;
				$system = new Shipper_Model_System;
				$info_task->apply( $system->get_data() );
			}
			$errors = array_merge( $errors, $task->get_errors() );
		}

		if ( empty( $errors ) && $destinations->is_expired() ) {
			// Expired destinations cache - update.
			$task = new Shipper_Task_Api_Destinations_Get;
			$result = $task->apply();
			if ( ! empty( $result ) ) {
				// We got the listing result - update stored destinations cache.
				$destinations->set_data( $result );
				$destinations->set_timestamp( time() );
				$destinations->save();
			}
			$errors = array_merge( $errors, $task->get_errors() );
		}

		foreach ( $errors as $error ) {
			Shipper_Helper_Log::write(
				sprintf( __( 'Destination error: %s', 'shipper' ), $error->get_error_message() )
			);
		}

		return $errors;
	}

	/**
	 * Renders the tools page
	 */
	public function page_tools() {
		if ( ! shipper_user_can_ship() ) { return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) ); }

		$tool = 'logs';
		if ( ! empty( $_GET['tool'] ) ) {
			$tool = sanitize_text_field( $_GET['tool'] );
		}

		$tpl = new Shipper_Helper_Template;
		$tpl->render( 'pages/tools/main', array( 'current_tool' => $tool ) );
	}

	/**
	 * Renders the settings page
	 */
	public function page_settings() {
		if ( ! shipper_user_can_ship() ) { return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) ); }

		$tool = 'notifications';
		if ( ! empty( $_GET['tool'] ) ) {
			$tool = sanitize_text_field( $_GET['tool'] );
		}

		$tpl = new Shipper_Helper_Template;
		$tpl->render( 'pages/settings/main', array( 'current_tool' => $tool ) );
	}

	/**
	 * Saves the submitted settings
	 */
	public function save_settings() {
		if ( ! shipper_user_can_ship() ) { return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) ); }

		$tool = false;
		if ( ! empty( $_GET['tool'] ) ) {
			$tool = sanitize_text_field( $_GET['tool'] );
		}
		if ( empty( $tool ) ) { return false; }

		if ( empty( $_POST[ $tool ] ) ) {
			return false; // Nothing to do here.
		}
		if ( empty( $_POST[ $tool ]['shipper-nonce'] ) ) {
			return false; // Can't validate.
		}
		if ( ! wp_verify_nonce( $_POST[ $tool ]['shipper-nonce'], "shipper-{$tool}" ) ) {
			return false; // Invalid.
		}
		$model = new Shipper_Model_Stored_Options;

		// A11n.
		if ( 'accessibility' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_A11N,
				! empty( $_POST[ $tool ][ Shipper_Model_Stored_Options::KEY_A11N ] )
			);
		}

		// Preservation settings.
		if ( 'data' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_SETTINGS,
				! empty( $_POST[ $tool ][ Shipper_Model_Stored_Options::KEY_SETTINGS ] )
			);
			$model->set(
				Shipper_Model_Stored_Options::KEY_DATA,
				! empty( $_POST[ $tool ][ Shipper_Model_Stored_Options::KEY_DATA ] )
			);
		}

		// Migration settings.
		if ( 'migration' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_UPLOADS,
				! empty( $_POST[ $tool ][ Shipper_Model_Stored_Options::KEY_UPLOADS ] )
			);
		}

		if ( 'pagination' === $tool ) {
			$model->set(
				Shipper_Model_Stored_Options::KEY_PER_PAGE,
				intval( $_POST[ $tool ][ Shipper_Model_Stored_Options::KEY_PER_PAGE ] )
			);
		}

		$model->save();
		wp_safe_redirect( esc_url_raw( add_query_arg( 'saved', true ) ) );
		die;
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
		if ( ! shipper_user_can_ship() ) { return $classes; }

		$cls = explode( ' ', $classes );
		$cls[] = 'sui-2-3-15';
		$cls[] = 'shipper-admin';
		$cls[] = 'shipper-sui';
		return join( ' ', $cls );
	}

	/**
	 * Adds front-end dependencies that are shared between Shipper admin pages
	 */
	public function add_shared_dependencies() {
		if ( ! shipper_user_can_ship() ) { return false; }

		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );
		$assets = new Shipper_Helper_Assets;

		wp_enqueue_style(
			'shipper',
			$assets->get_asset( 'css/shipper.css' ),
			null,
			SHIPPER_VERSION
		);
		wp_enqueue_script(
			'shipper',
			$assets->get_asset( 'js/shipper.js' ),
			array( 'jquery' ),
			SHIPPER_VERSION,
			true
		);
		$model = new Shipper_Model_Stored_Options;
		wp_localize_script(
			'shipper',
			'_shipper',
			array(
				'update_interval' => Shipper_Helper_Assets::get_update_interval(),
				'per_page' => $model->get( Shipper_Model_Stored_Options::KEY_PER_PAGE, 10 )
			)
		);
	}

	/**
	 * Adds front-end dependencies specific for the migrate page
	 */
	public function add_migrate_dependencies() {
		if ( ! shipper_user_can_ship() ) { return false; }
		$this->add_shared_dependencies();
	}

	/**
	 * Adds front-end dependencies specific for the tools page
	 */
	public function add_tools_dependencies() {
		if ( ! shipper_user_can_ship() ) { return false; }
		$this->add_shared_dependencies();
	}

	/**
	 * Adds front-end dependencies specific for the settings page
	 */
	public function add_settings_dependencies() {
		if ( ! shipper_user_can_ship() ) { return false; }
		$this->add_shared_dependencies();
	}
}