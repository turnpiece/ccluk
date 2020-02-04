<?php
/**
 * Shipper AJAX controllers: package preflight controller class
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Packages meta info AJAX controller class
 */
class Shipper_Controller_Ajax_Packages_Preflight extends Shipper_Controller_Ajax {

	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		$this->add_handler( 'system', array( $this, 'json_preflight_system' ) );
		$this->add_handler( 'files', array( $this, 'json_preflight_files' ) );

		add_action( 'wp_ajax_shipper_package_get_package_size_message', array(
			&$this,
			'json_get_package_size_message'
		) );
	}

	public function json_get_package_size_message() {
		$this->do_request_sanity_check();

		$chk          = new Shipper_Task_Check_Files;
		$package_size = $chk->get_updated_package_size();
		$threshold    = Shipper_Model_Stored_Migration::get_package_size_threshold();
		$exclusions   = new Shipper_Model_Stored_Exclusions;

		$tpl    = new Shipper_Helper_Template;
		$markup = $tpl->get(
			'modals/packages/preflight/issue-package_size-summary',
			array(
				'package_size' => $package_size,
				'threshold'    => $threshold,
			)
		);
		wp_send_json_success( array(
			'excluded'     => count( $exclusions->get_data() ),
			'package_size' => size_format( $package_size ),
			'oversized'    => $package_size > $threshold,
			'markup'       => $markup,
		) );
	}

	public function json_preflight_system() {
		$this->do_request_sanity_check();

		$model = new Shipper_Model_System;
		$task  = new Shipper_Task_Check_Package_System;

		$files = new Shipper_Task_Check_Package_Files;
		$files->restart();

		$task->apply( $model->get_data() );
		$recommendations = $this->get_recommendations_markup( $task );

		return wp_send_json_success( $recommendations );
	}

	public function json_preflight_files() {
		$this->do_request_sanity_check();
		$timer      = 0;
		$start_time = microtime( true );

		//trigger it here so we can exclude files during preflighs
		do_action( 'shipper_package_migration_tick_before' );

		while ( $timer < 3 ) {
			$task = new Shipper_Task_Check_Package_Files;
			$task->apply();
			if ( $task->is_done() ) {
				break;
			}
			$timer = microtime( true ) - $start_time;
		}

		if ( ! $task->is_done() ) {
			return wp_send_json_success( array(
				'is_done' => false,
				'issues'  => array(),
			) ); // Not done yet.
		}

		$recommendations = $this->get_recommendations_markup( $task );
		$task->restart();

		return wp_send_json_success( array(
			'is_done' => true,
			'issues'  => $recommendations,
		) );
	}

	public function get_recommendations_markup( Shipper_Task_Check $task ) {
		$recommendations   = array();
		$tpl               = new Shipper_Helper_Template;
		$type              = $task instanceof Shipper_Task_Check_Package_Files
			? 'files'
			: 'system';
		$show_package_size = false;
		foreach ( Shipper_Helper_Template_Sorter::get_sorted_checks( $task ) as $check ) {
			if ( Shipper_Model_Check::STATUS_OK === $check['status'] ) {
				// Do not deal with the successful checks.
				if ( isset( $check['check_type'] ) && $check['check_type'] != 'package_size' ) {
					continue;
				} elseif ( $show_package_size == false ) {
					continue;
				}
			} else {
				$show_package_size = true;
			}
			$check['task_type'] = $type;
			$recommendations[]  = $tpl->get( 'modals/packages/preflight/issue', $check );
		}

		return $recommendations;
	}
}