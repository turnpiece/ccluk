<?php

/**
 * Class WP_Hummingbird_Module_Server
 *
 * A parent class for those modules that offers a piece of code to
 * setup the server (gzip and caching)
 */
abstract class WP_Hummingbird_Module_Server extends WP_Hummingbird_Module {

	protected $transient_slug = false;

	protected $status;

	public function run() {}

	/**
	 * Initializes the module. Always executed even if the module is deactivated.
	 */
	public function init() {
		// Fetch status of selected module.
		$this->status = $this->get_analysis_data();
		if ( false === $this->status ) {
			// Force only when we don't have any data yet.
			$this->status = $this->get_analysis_data( true );
		}
	}

	/**
	 * Return the analized data for the module
	 *
	 * @param bool $force If set to true, cache will be cleared before getting the data.
	 * @param bool $check_api If set to true, the api will be checked.
	 *
	 * @return mixed Analysis data
	 */
	public function get_analysis_data( $force = false, $check_api = false ) {
		if ( ! $this->transient_slug ) {
			return false;
		}

		$transient = 'wphb-' . $this->transient_slug . '-data';
		$results = get_site_option( $transient );

		if ( $force ) {

			$this->clear_analysis_data();


			if ( $check_api ) {
				$results = $this->analize_data( true );
			} else {
				$results = $this->analize_data();
			}

			update_site_option( $transient, $results );

		}

		return $results;
	}

	/**
	 * Analize the data
	 *
	 * @param bool $check_api If set to true, the api will be checked.
	 *
	 * @return mixed
	 */
	protected abstract function analize_data( $check_api = false );

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
		if ( ! method_exists( $this, $method ) ) {
			return '';
		}

		return call_user_func( array( $this, $method ) );
	}

}