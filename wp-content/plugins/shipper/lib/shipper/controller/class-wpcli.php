<?php
/**
 * Shipper controllers: WP CLI command
 *
 * @since v1.1
 * @package shipper
 */

/**
 * WP CLI controller class
 */
class Shipper_Controller_Wpcli extends Shipper_Controller {

	/**
	 * Boot method
	 *
	 * @return false|void
	 */
	public function boot() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return $this->register_commands();
		}
		return false;
	}

	/**
	 * Register commands
	 *
	 * @return void
	 */
	public function register_commands() {
		$cli = new Shipper_Helper_Wpcli();
		WP_CLI::add_command( 'shipper', $cli );
		WP_CLI::add_command( 'shipper package', new Shipper_Helper_Wpcli_Package() );
		WP_CLI::add_command( 'shipper api', new Shipper_Helper_Wpcli_Api() );
	}
}