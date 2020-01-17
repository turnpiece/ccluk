<?php
/**
 * Shipper tasks: import, setup postprocessing task.
 *
 * Fires post-import, used to flush caches etc.
 *
 * @package shipper
 */

/**
 * Shipper import remote scrub class
 */
class Shipper_Task_Import_Postprocess extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Postprocess the new install', 'shipper' );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->postprocess_object_cache();
		$this->postprocess_hummingbird();
		$this->postprocess_elementor();

		return true; // One and done.
	}

	/**
	 * Flushes all object caches
	 *
	 * @return bool
	 */
	public function postprocess_object_cache() {
		Shipper_Helper_Log::write( 'Flushing caches' );
		shipper_flush_cache();
		return true;
	}

	/**
	 * Postprocesses Hummingbird caches
	 *
	 * @return bool
	 */
	public function postprocess_hummingbird() {
		if ( ! class_exists( 'WP_Hummingbird' ) ) {
			return false;
		}

		Shipper_Helper_Log::write( __( 'Detected Hummingbird, flushing caches', 'shipper' ) );

		$hummingbird = WP_Hummingbird::get_instance();
		foreach ( $hummingbird->core->modules as $module ) {
			if ( ! $module->is_active() ) {
				continue;
			}
			Shipper_Helper_Log::write(sprintf(
				__( 'Flushing caches for %s', 'shipper' ),
				get_class( $module )
			));
			$module->clear_cache();
		}

		return true;
	}

	/**
	 * Postprocesses Elementor caches
	 *
	 * @return bool
	 */
	public function postprocess_elementor() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		try {
			\Elementor\Plugin::$instance->settings->update_css_print_method();
		} catch ( Exception $e ) {
			Shipper_Helper_Log::write(sprintf(
				__( 'Detected unknown Elementor version, flushing caches not possible %s', 'shipper' ),
				$e->getMessage()
			));
			return false;
		}
		Shipper_Helper_Log::write( __( 'Detected Elementor, flushing caches', 'shipper' ) );

		return true;
	}

}