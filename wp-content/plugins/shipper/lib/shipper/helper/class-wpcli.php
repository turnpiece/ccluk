<?php
/**
 * Shipper helper: WP CLI command interface
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Handles Shipper migration actions.
 */
class Shipper_Helper_Wpcli {


	/**
	 * Excludes a path from a migration fileset
	 *
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : Path fragment to exclude (will exclude everything matching the passed arg).
	 */
	public function exclude( $args ) {
		$fragment = $args[0];

		$files = new Shipper_Task_Check_Package_Files;
		$files->restart();
		while ( ! $files->is_done() ) {
			$files->apply();
		}

		$storage = new Shipper_Model_Stored_Filelist;
		$oversized = $storage->get( 'oversized', array() );
		$exclusions = new Shipper_Model_Stored_Exclusions;
		$all = $exclusions->get_data();

		$updated = 0;
		foreach ( $oversized as $info ) {
			$path = ! empty( $info['path'] )
				? $info['path']
				: false;
			if ( empty( $path ) ) {
				continue;
			}
			$result = (bool) stristr( $path, $fragment );
			if ( empty( $result ) ) {
				continue;
			}
			if ( in_array( $path, array_keys( $all ) ) ) {
				continue;
			}
			$exclusions->set( $path, md5( $path ) );
			$updated += 1;
		}

		if ( ! empty( $updated ) ) {
			WP_CLI::success( "Excluded {$updated} paths, run preflight to confirm" );
			$exclusions->save();
		}

		$files->restart();
	}

	/**
	 * Includes a path to a migration fileset
	 *
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : Path fragment to include (will include everything matching the passed arg).
	 */
	public function include( $args ) {
		$fragment = $args[0];

		$files = new Shipper_Task_Check_Package_Files;
		$files->restart();
		while ( ! $files->is_done() ) {
			$files->apply();
		}

		$storage = new Shipper_Model_Stored_Filelist;
		$oversized = $storage->get( 'oversized', array() );
		$exclusions = new Shipper_Model_Stored_Exclusions;
		$all = $exclusions->get_data();

		$updated = 0;
		foreach ( $oversized as $info ) {
			$path = ! empty( $info['path'] )
				? $info['path']
				: false;
			if ( empty( $path ) ) {
				continue;
			}
			$result = (bool) stristr( $path, $fragment );
			if ( empty( $result ) ) {
				continue;
			}
			if ( ! in_array( $path, array_keys( $all ) ) ) {
				continue;
			}
			$exclusions->remove( $path );
			$updated += 1;
		}

		if ( ! empty( $updated ) ) {
			WP_CLI::success( "Included {$updated} previously excluded paths, run preflight to confirm" );
			$exclusions->save();
		}

		$files->restart();
	}

	protected function get_validated_domain( $domain ) {
		$destinations = new Shipper_Model_Stored_Destinations;
		if ( $destinations->is_expired() ) {
			$ctrl = Shipper_Controller_Admin::get();
			$ctrl->update_destinations_cache();
			$destinations->load();
		}
		$dest = $destinations->get_by_domain( $domain );

		return ! empty( $dest ) && ! empty( $dest['domain'] )
			? $dest['domain']
			: false;
	}

	protected function run_subtasks( $overall ) {
		foreach( $overall->get_tasks() as $type => $task ) {
			$total = $task->get_total_steps();
			$progress = WP_CLI\Utils\make_progress_bar(
				"Processing {$type} task: {$total}",
				$total
			);
			$this_far = 0;
			while ( ! $task->apply() ) {
				$current = $task->get_current_step();
				$progress->tick(
					$current - $this_far
				);
				$this_far = $current;
			}
			$progress->finish();
		}
	}

	protected function prepare_migration( $type, $destination ) {
		$migration = new Shipper_Model_Stored_Migration;
		$migration->clear()->save();

		$migration->prepare(
			Shipper_Model_Stored_Destinations::get_current_domain(),
			$destination,
			$type,
			false
		);
		$migration->begin();

		return $migration;
	}

	protected function get_cli_dest() {
		return 'whatever.org';
	}

	protected function render_preflight_results( $preflight ) {
		$check_types = $preflight->get_check_types();
		$has_remote_package = in_array(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG,
			$check_types
		);
		$messages = array(
			'warnings' => array(),
			'errors' => array(),
		);
		foreach( $check_types as $type ) {
			foreach ( $preflight->get_check( $type ) as $check ) {
				if ( Shipper_Model_Check::STATUS_WARNING === $check['status'] ) {
					$msg = "{$type}: {$check['title']}";
					if ( Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG === $type ) {
						$tmp = preg_replace(
							'/\s+/',
							' ',
							strtr(
								wp_strip_all_tags( $check['message'] ),
								array( "\n" => ' ', "\r" => " " )
							)
						);
						$msg .= "\n{$tmp}";
					}
					$messages['warnings'][] = $msg;
				}
				if ( Shipper_Model_Check::STATUS_ERROR === $check['status'] ) {
					$messages['errors'][] = "{$type}: {$check['title']}";
				}
			}
		}
		$messages['warnings'] = array_unique( $messages['warnings'] );
		$messages['errors'] = array_unique( $messages['errors'] );
		if ( ! empty( $messages['warnings'] ) ) {
			foreach( $messages['warnings'] as $warning ) {
				WP_CLI::warning( $warning );
			}
		}
		if ( ! empty( $messages['errors'] ) ) {
			foreach( $messages['errors'] as $error ) {
				WP_CLI::error( $error );
			}
		}

		if ( empty( $has_remote_package ) ) {
			$this->render_preflight_file_results();
		}

		$cback = empty( $messages['errors'] ) ? 'success' : 'error';
		WP_CLI::$cback(
			sprintf(
				'Done with %d warnings and %d errors',
				count( $messages['warnings'] ),
				count( $messages['errors'] )
			)
		);
	}

	protected function render_preflight_file_results() {
		$storage = new Shipper_Model_Stored_Filelist;
		$oversized = $storage->get( 'oversized', array() );

		$estimate = new Shipper_Model_Stored_Estimate;
		$package_size = $estimate->get( 'raw_package_size', 0 );

		$exclusions_model = new Shipper_Model_Stored_Exclusions;
		$exclusions = array_keys( $exclusions_model->get_data() );
		foreach ( $exclusions as $exc ) {
			$package_size -= filesize( $exc );
		}

		$files = array();
		foreach( $oversized as $item ) {
			$excl = ( in_array( $item['path'], $exclusions ) )
				? 'Yes'
				: 'No';
			$size = size_format( $item['size'] );
			$files[] = array(
				'Excluded' => $excl,
				'Path' => $item['path'],
				'Size' => $size,
			);
		}
		if ( ! empty( $files ) ) {
			WP_CLI\Utils\format_items(
				'table',
				$files,
				array( 'Excluded', 'Path', 'Size' )
			);
		}
		WP_CLI::line(
			sprintf(
				'Package size: %s',
				size_format( $package_size )
			)
		);
	}
}