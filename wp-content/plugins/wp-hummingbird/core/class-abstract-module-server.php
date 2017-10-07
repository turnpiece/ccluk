<?php

/**
 * Class WP_Hummingbird_Module_Server
 *
 * A parent class for those modules that offers a piece of code to
 * setup the server (gzip and caching)
 */
abstract class WP_Hummingbird_Module_Server extends WP_Hummingbird_Module {

	protected $transient_slug = false;

	public function run() {}
	public function init() {}

	/**
	 * Return the analized data for the module
	 *
	 * @param bool $force If set to true, cache will be cleared before getting the data
	 *
	 * @return mixed Analysis data
	 */
	public function get_analysis_data( $force = false ) {
		if ( ! $this->transient_slug ) {
			return false;
		}

		$transient = 'wphb-' . $this->transient_slug . '-data';
		$results = get_site_option( $transient );

		if ( $force ) {

			$this->clear_analysis_data();

			$results = $this->analize_data();

			update_site_option( $transient, $results );

		}

		return $results;
	}

	/**
	 * Analize the data
	 *
	 * @return mixed
	 */
	protected abstract function analize_data();

	/**
	 * Clear the module cache
	 */
	public function clear_analysis_data() {
		delete_site_option( 'wphb-' . $this->transient_slug . '-data' );
	}

	/**
	 * Get the server code snippet
	 *
	 * @param string $server Server name (nginx,apache...)
	 *
	 * @return string
	 */
	public function get_server_code_snippet( $server ) {
		$method = 'get_' . str_replace( array( '-', ' ' ), '', strtolower( $server ) ) . '_code';
		if ( ! method_exists( $this, $method ) )
			return '';

		return call_user_func( array( $this, $method ) );
	}
}