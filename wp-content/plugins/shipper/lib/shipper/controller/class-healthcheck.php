<?php
/**
 * Shipper controllers: migration health status controller
 *
 * @package shipper
 */

/**
 * Health controller class
 */
class Shipper_Controller_Healthcheck extends Shipper_Controller {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		add_action(
			'shipper_kickstarted',
			array( $this, 'handle_migration_kickstart' )
		);
		add_action(
			'shipper_flag_cleared',
			array( $this, 'handle_flag_cleared' )
		);
		add_action(
			'shipper_dev_ping',
			array( $this, 'handle_dev_ping' )
		);
	}

	/**
	 * Handles the migration kickstarts
	 */
	public function handle_migration_kickstart() {
		$this->update_model_flag(
			Shipper_Model_Stored_Healthcheck::KICKSTARTED
		);
	}

	/**
	 * Handles the migration stalls
	 *
	 * Stalls are defined as situations where we have to
	 * clear the lock flags.
	 */
	public function handle_flag_cleared() {
		$this->update_model_flag(
			Shipper_Model_Stored_Healthcheck::STALLED
		);
	}

	/**
	 * Handles the migration remote pings
	 */
	public function handle_dev_ping() {
		$this->update_model_flag(
			Shipper_Model_Stored_Healthcheck::PINGED
		);
	}

	/**
	 * Updates the model flag with latest timestamp
	 *
	 * @param string $flag Model flag constant.
	 */
	public function update_model_flag( $flag ) {
		$model   = new Shipper_Model_Stored_Healthcheck();
		$value   = $model->get( $flag, array() );
		$value[] = time();
		$model->set( $flag, $value )->save();
	}
}