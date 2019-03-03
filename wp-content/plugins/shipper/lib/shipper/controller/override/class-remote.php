<?php
/**
 * Shipper controllers: remote overrides
 *
 * @package shipper
 */

/**
 * Remote overrides controller class
 */
class Shipper_Controller_Override_Remote extends Shipper_Controller_Override {

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		$constants = $this->get_constants();

		if ( $constants->is_defined( 'SHIPPER_SKIP_REMOTE_SCRUB' ) ) {
			$this->apply_remote_scrubbing_skip();
		}
	}

	/**
	 * Binds to remote scrubbing filter skips
	 *
	 * @return bool Bound callback value
	 */
	public function apply_remote_scrubbing_skip() {
		$constants = $this->get_constants();
		$scrub_cback = $constants->get( 'SHIPPER_SKIP_REMOTE_SCRUB' )
			? '__return_true'
			: '__return_false'
		;
		add_filter( 'shipper_import_skip_scrub', $scrub_cback );

		return call_user_func( $scrub_cback );
	}
}