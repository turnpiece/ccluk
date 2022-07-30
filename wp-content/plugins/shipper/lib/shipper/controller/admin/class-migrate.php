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
		return parent::get_page_order() + 2;
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
			_x( 'API Migration', 'page label', 'shipper' ),
			_x( 'API Migration', 'menu label', 'shipper' ),
			$capability,
			'shipper-api',
			array( $this, 'page_migrate' )
		);
		add_action( "load-{$migrate}", array( $this, 'add_migrate_dependencies' ) );

		add_action( "load-{$migrate}", array( $this, 'do_migration_complete_redirection' ) );
	}

	/**
	 * Adds front-end dependencies specific for the migrate page
	 */
	public function add_migrate_dependencies() {
		if ( ! shipper_user_can_ship() ) {
			return false;
		}
		$this->add_shared_dependencies();
	}

	/**
	 * Redirects admin migration page during migration actual processing
	 */
	public function do_migration_complete_redirection() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			return false;
		}

		$migration      = new Shipper_Model_Stored_Migration();
		$not_actionable = $migration->is_completed() || $migration->is_empty();
		if ( $not_actionable && isset( $_GET['begin'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's not a form
			wp_safe_redirect(
				esc_url_raw(
					remove_query_arg(
						array(
							'type',
							'site',
							'check',
							'begin',
							'is_excludes_picked',
						)
					)
				)
			);
			die;
		}
	}

	/**
	 * Render migration setup pageset
	 *
	 * @param string $type Migration type.
	 * @param int    $site Destination site ID.
	 * @param bool   $check Whether we're at the preflight check stage or not.
	 * @param bool   $is_excludes_picked Whether the package is excluded or not.
	 * @param bool   $db_prefix_check Whether to check db prefix or not.
	 * @param bool   $network is it network or single site.
	 */
	public function render_page_migrate_setup( $type, $site, $check = false, $is_excludes_picked = false, $db_prefix_check = false, $network = false ) {
		if ( ! $this->can_user_access_shipper_pages() ) {
			wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		/**
		 * Clear previously stored ping
		 *
		 * Since 1.2.6
		 */
		( new Shipper_Model_Stored_Ping() )->clear()->save();

		$errors       = $this->handle_migration_destinations_cache();
		$destinations = new Shipper_Model_Stored_Destinations();

		$tpl                 = new Shipper_Helper_Template();
		$estimated_model     = new Shipper_Model_Stored_Estimate();
		$package_size        = size_format( $estimated_model->get( Shipper_Model_Stored_Migration::PACKAGE_SIZE ) );
		$estimated_time      = $estimated_model->get_migration_time_span()['high'];
		$estimated_time_unit = $estimated_model->get_migration_time_span()['unit'];

		$tpl->render(
			'pages/migration/selection',
			array(
				'type'               => $type,
				'site'               => $site,
				'check'              => $check,
				'size'               => $package_size,
				'time'               => $estimated_time,
				'time_unit'          => $estimated_time_unit,
				'errors'             => $errors,
				'network'            => $network,
				'destinations'       => $destinations,
				'db_prefix_check'    => $db_prefix_check,
				'is_excludes_picked' => $is_excludes_picked,
			)
		);
	}

	/**
	 * Render migration progress
	 *
	 * @since 1.1.4 added the $notice_dismissed argument
	 *
	 * @param string $type Migration type.
	 * @param int    $site Destination site ID.
	 * @param bool   $notice_dismissed Is migration notice dismissed.
	 */
	public function render_page_migrate_progress( $type, $site, $notice_dismissed ) {
		if ( ! $this->can_user_access_shipper_pages() ) {
			return wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$errors       = $this->handle_migration_destinations_cache();
		$destinations = new Shipper_Model_Stored_Destinations();

		$tpl                 = new Shipper_Helper_Template();
		$estimated_model     = new Shipper_Model_Stored_Estimate();
		$package_size        = size_format( $estimated_model->get( Shipper_Model_Stored_Migration::PACKAGE_SIZE ) );
		$estimated_time      = $estimated_model->get_migration_time_span()['high'];
		$estimated_time_unit = $estimated_model->get_migration_time_span()['unit'];

		$tpl->render(
			'pages/migration/progress',
			array(
				'type'             => $type,
				'site'             => $site,
				'check'            => true,
				'begin'            => true,
				'size'             => $package_size,
				'time'             => $estimated_time,
				'time_unit'        => $estimated_time_unit,
				'progress'         => 0,
				'errors'           => $errors,
				'destinations'     => $destinations,
				'notice_dismissed' => $notice_dismissed,
			)
		);
	}

	/**
	 * Dispatches migrate page states.
	 */
	public function page_migrate() {
		if ( ! $this->can_user_access_shipper_pages() ) {
			wp_die( esc_html( __( 'Nope.', 'shipper' ) ) );
		}

		$migration        = new Shipper_Model_Stored_Migration();
		$destinations     = new Shipper_Model_Stored_Destinations();
		$meta             = new Shipper_Model_Stored_MigrationMeta();
		$notice_dismissed = $migration->get( Shipper_Model_Stored_Migration::NOTICE_DISMISSED, false );

		if ( $migration->is_active() ) {
			// If we have active migration, let's go with it.
			$type               = $migration->get_type();
			$site_hash          = Shipper_Model_Stored_Migration::TYPE_EXPORT === $type
				? $destinations->get_by_domain( $migration->get_destination() )
				: $destinations->get_by_domain( $migration->get_source() );
			$site               = ! empty( $site_hash['site_id'] ) ? $site_hash['site_id'] : 0;
			$check              = true;
			$begin              = true;
			$is_excludes_picked = true;
			$dbprefix           = true;
		}

		$get = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $type ) ) {
			$type = ! empty( $get['type'] ) ? sanitize_text_field( $get['type'] ) : false;
		}

		if ( Shipper_Model_Stored_Migration::TYPE_IMPORT === $type ) {
			$is_excludes_picked = true;
		}
		$network = null;
		if ( empty( $type ) && ! $migration->is_active() ) {
			$meta->clear();
			$meta->save();
		}
		if ( is_multisite() && Shipper_Model_Stored_Migration::TYPE_EXPORT === $type ) {
			$network = $meta->get( Shipper_Model_Stored_MigrationMeta::NETWORK_MODE );
			$site_id = $meta->get( Shipper_Model_Stored_MigrationMeta::NETWORK_SUBSITE_ID );

			if ( 'subsite' === $network ) {
				if ( ! $site_id ) {
					$network = false;
				} else {
					$network = true;
				}
			} elseif ( 'whole_network' === $network ) {
				$network = true;
			}
		}

		if ( empty( $site ) ) {
			$site = ! empty( $get['site'] ) ? (int) sanitize_text_field( $get['site'] ) : false;
		}

		if ( Shipper_Helper_MS::can_ms_subsite_import() && Shipper_Model_Stored_Migration::TYPE_IMPORT === $type ) {
			$site_id = $meta->get( Shipper_Model_Stored_MigrationMeta::NETWORK_SUBSITE_ID );
			if ( $site_id ) {
				$network = true;
			}
		} elseif ( Shipper_Model_Stored_Migration::TYPE_IMPORT === $type ) {
			$network = true;
		}

		if ( empty( $check ) ) {
			$check = ! empty( $get['check'] )
				? sanitize_text_field( $get['check'] )
				: false;
		}

		if ( empty( $begin ) ) {
			$begin = ! empty( $get['begin'] )
				? sanitize_text_field( $get['begin'] )
				: false;
		}
		if ( empty( $is_excludes_picked ) ) {
			$is_excludes_picked = ! empty( $get['is_excludes_picked'] )
				? sanitize_text_field( $get['is_excludes_picked'] )
				: false;
		}

		if ( empty( $dbprefix ) ) {
			$dbprefix = $meta->get_dbprefix_option();
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
				// We have done the preflight, what's next?.
				if ( ! empty( $begin ) ) {
					$migration = new Shipper_Model_Stored_Migration();
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
				$estimate = new Shipper_Model_Stored_Estimate();
				$estimate->clear()->save();
			}
		}

		return empty( $type ) || ( is_multisite() && empty( $network ) ) || empty( $site ) || empty( $check ) || empty( $begin ) || empty( $is_excludes_picked ) || empty( $dbprefix )
			? $this->render_page_migrate_setup( $type, $site, $check, $is_excludes_picked, $dbprefix, $network )
			: $this->render_page_migrate_progress( $type, $site, $notice_dismissed );
	}
}