<?php
/**
 * Shipper helper: WP CLI command interface
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Shipper API migrations actions
 */
class Shipper_Helper_Wpcli_Api extends Shipper_Helper_Wpcli {
	// phpcs:disable
	/**
	 * Runs api migration preflight check
	 *
	 * @param array $args list of args.
	 * @param array $assoc_args list of extra args.
	 *
	 * ## OPTIONS
	 *
	 * <destination>
	 * : Destination site to export to.
	 *
	 * [--type=<type>]
	 * : Optional migration type check (defaults to export).
	 * ---
	 * default: export
	 * options:
	 *  - export
	 *  - import
	 * ---
	 * phpcs:enable
	 */
	public function preflight( $args, $assoc_args = array() ) {
		$domain      = $args[0];
		$destination = $this->get_validated_domain( $domain );
		if ( empty( $destination ) ) {
			return WP_CLI::error( "Unknown destination: [{$domain}]" );
		}

		$type  = Shipper_Model_Stored_Migration::TYPE_EXPORT;
		$types = array(
			Shipper_Model_Stored_Migration::TYPE_EXPORT,
			Shipper_Model_Stored_Migration::TYPE_IMPORT,
		);
		if ( ! empty( $assoc_args['type'] ) ) {
			$type = $assoc_args['type'];
			if ( ! in_array( $type, $types, true ) ) {
				return WP_CLI::error( "Invalid type: {$type}" );
			}
		}

		$migration = $this->prepare_migration(
			$type,
			$destination
		);
		$ctrl      = Shipper_Controller_Runner_Preflight_Cli::get();
		$ctrl->clear();
		$ctrl->start();

		$status = false;
		while ( ! $status ) {
			$status = ! $ctrl->process_tick();
		}

		$this->render_preflight_results( $ctrl->get_status() );

		$ctrl->clear();
		$migration->clear()->save();
	}

	/**
	 * Exports the site using the API approach
	 *
	 * @param array $args list of args.
	 *
	 * ## OPTIONS
	 *
	 * <destination>
	 * : Destination site to export to.
	 */
	public function export( $args ) {
		$domain      = $args[0];
		$destination = $this->get_validated_domain( $domain );
		if ( empty( $destination ) ) {
			WP_CLI::error( "Unknown destination: [{$domain}]" );
		}

		$ctrl = Shipper_Controller_Runner_Migration::get();
		$ctrl->reset_all();
		$migration = $this->prepare_migration(
			Shipper_Model_Stored_Migration::TYPE_EXPORT,
			$destination
		);

		$start = time();
		$this->run_subtasks( new Shipper_Task_Export_All() );
		$end = time();

		$ctrl->reset_all();
		$migration->clear()->save();

		WP_CLI::success(
			sprintf(
				'Exported site %s to %s in %s',
				Shipper_Model_Stored_Destinations::get_current_domain(),
				$destination,
				human_time_diff( $start, $end )
			)
		);
	}
}

if ( ! class_exists( 'Shipper_Controller_Runner_Preflight_Cli' ) ) {
	class Shipper_Controller_Runner_Preflight_Cli extends Shipper_Controller_Runner_Preflight { // phpcs:ignore

		/**
		 * Make a ping request
		 *
		 * @return bool|void
		 */
		public function ping() {}
	}
}