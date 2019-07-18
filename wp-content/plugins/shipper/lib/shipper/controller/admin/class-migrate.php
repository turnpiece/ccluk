<?php
/**
 * Shipper controllers: admin migrate page
 *
 * @since v1.0.3
 * @package shipper
 */

/**
 * Admin pages controller, migrate page
 */
class Shipper_Controller_Admin_Migrate extends Shipper_Controller_Admin {

	/**
	 * Gets order in which menu registration takes place
	 *
	 * @return int Page order
	 */
	public function get_page_order() {
		return parent::get_page_order() + 1;
	}

	/**
	 * Sets up menu items
	 *
	 * Also sets up front-end dependencies loading on page load.
	 */
	public function add_menu() {
		$capability = $this->get_capability();
		if ( ! $this->can_user_access_shipper_pages() ) {
			return false;
		}

		$migrate = add_submenu_page(
			'shipper',
			_x( 'Migrate', 'page label', 'shipper' ),
			_x( 'Migrate', 'menu label', 'shipper' ),
			$capability,
			'shipper',
			array( $this, 'page_migrate' )
		);
		add_action( "load-{$migrate}", array( $this, 'add_migrate_dependencies' ) );

		add_action( "load-{$migrate}", array( $this, 'do_migration_complete_redirection' ) );
	}

	/**
	 * Adds front-end dependencies specific for the migrate page
	 */
	public function add_migrate_dependencies() {
		if ( ! shipper_user_can_ship() ) { return false; }
		$this->add_shared_dependencies();
	}

	/**
	 * Redirects admin migration page during migration actual processing
	 */
	public function do_migration_complete_redirection() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			return false;
		}

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
		if ( ! $this->can_user_access_shipper_pages() ) {
			return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$errors = $this->handle_migration_destinations_cache();
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
		if ( ! $this->can_user_access_shipper_pages() ) {
			return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$errors = $this->handle_migration_destinations_cache();
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
		if ( ! $this->can_user_access_shipper_pages() ) {
			return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

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
					$migration = new Shipper_Model_Stored_Migration;
					if ( ! $migration->is_active() ) {
						Shipper_Controller_Runner_Migration::get()->begin();
						Shipper_Controller_Runner_Migration::get()->run();
					}
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
}
