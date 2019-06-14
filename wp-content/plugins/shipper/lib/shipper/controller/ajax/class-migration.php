<?php
/**
 * Shipper AJAX controllers: migration controller class
 *
 * @package shipper
 */

/**
 * Migration AJAX controller class
 */
class Shipper_Controller_Ajax_Migration extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) { return false; }

		add_action( 'wp_ajax_shipper_reset_migration', array( $this, 'json_reset_migration' ) );
		add_action( 'wp_ajax_shipper_cancel_migration', array( $this, 'json_cancel_migration' ) );
		add_action( 'wp_ajax_shipper_migration_errors', array( $this, 'html_migration_errors' ) );
	}

	/**
	 * Gets migration errors as HTML
	 */
	public function html_migration_errors() {
		$this->do_request_sanity_check();
		$migration = new Shipper_Model_Stored_Migration;
		$tpl = new Shipper_Helper_Template;
		$tpl->render('pages/migration/progress-errors', array(
			'errors' => $migration->get( 'errors' ),
			'type' => $migration->get( 'type' ),
			'has_remote_error' => $migration->get( 'has_remote_error' ),
			'destination' => $migration->get_destination(),
		));
		wp_die();
	}

	/**
	 * Resets migration before it even starts
	 */
	public function json_reset_migration() {
		$this->do_request_sanity_check( 'shipper-reset-migration', self::TYPE_POST );
		$migration = new Shipper_Model_Stored_Migration;
		$migration->clear();
		$migration->save();

		return wp_send_json_success();
	}

	/**
	 * Cancels the active migration
	 */
	public function json_cancel_migration() {
		$this->do_request_sanity_check();
		Shipper_Controller_Runner_Migration::get()->attempt_cancel();
		$migration = new Shipper_Model_Stored_Migration;
		$status = $migration->is_active();
		return empty( $status )
			? wp_send_json_success()
			: wp_send_json_error();
	}

}
