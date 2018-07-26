<?php
/**
 * Abstract Option model.
 *
 * @uses WP Transient API to persist data.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Model
 */
class MS_Model_Transient extends MS_Model {

	/**
	 * Save content in wp_option table.
	 *
	 * Update WP cache and instance singleton.
	 *
	 * @since  1.0.0
	 */
	public function save() {
		$this->before_save();

		$option_key = $this->option_key();
		$settings 	= array();

		$data = MS_Factory::serialize_model( $this );
		foreach ( $data as $field => $val ) {
			$settings[ $field ] = $this->$field;
		}

		MS_Factory::set_transient( $option_key, $settings, DAY_IN_SECONDS );

		$this->after_save();

		wp_cache_set( $option_key, $this, 'MS_Model_Transient' );
	}

	/**
	 * Delete transient.
	 *
	 * @since  1.0.0
	 */
	public function delete() {
		do_action( 'ms_model_transient_delete_before', $this );

		$option_key = $this->option_key();
		MS_Factory::delete_transient( $option_key );
		wp_cache_delete( $option_key, 'MS_Model_Transient' );

		do_action( 'ms_model_transient_delete_after', $this );
	}

	/**
	 * Returns the option name of the current object.
	 *
	 * @since  1.0.0
	 * @api Used by MS_Factory
	 *
	 * @return string The option key.
	 */
	public function option_key() {
		// Option key should be all lowercase.
		$key = strtolower( get_class( $this ) );

		// Network-wide mode uses different options then single-site mode.
		if ( MS_Plugin::is_network_wide() ) {
			$key .= '-network';
		}

		return substr( $key, 0, 45 );
	}

}