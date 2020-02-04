<?php
/**
 * Controllers are responsible for mapping requests and events to appropriate
 * actions to be taken.
 *
 * The actions are handled by atomic tasks, boostrapped by controllers.
 *
 * @package shipper
 */

/**
 * Controller abstraction class
 */
abstract class Shipper_Controller extends Shipper_Helper_Singleton {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	abstract public function boot();
}