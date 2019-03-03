<?php
/**
 * Shipper stubs: stub runner
 *
 * Used in local testing.
 * Sets up and boots the stubs.
 *
 * @package shipper
 */

/**
 * Stubs main class
 */
class Shipper_Stub_Main extends Shipper_Helper_Singleton {

	/**
	 * Boots the stubs and sets up event listeners.
	 */
	public function boot() {
		$controllers = array(
			'destinations',
			'info',
			'migrations',
		);
		foreach ( $controllers as $ctrl ) {
			$cname = 'Shipper_Stub_Api_' . ucfirst( $ctrl );
			if ( class_exists( $cname ) ) {
				$controller = call_user_func( array( $cname, 'get' ) );
				$controller->boot();
			}
		}

		add_filter( 'shipper_api_service_url', array( $this, 'stubify_api_url' ) );
		add_filter( 'shipper_api_request_args', array( $this, 'stubify_request_args' ) );

		add_filter( 'shipper_api_request_args', array( $this, 'stubify_destination_domain' ), 10, 2 );
	}

	/**
	 * Sends all API requests to our own stub handlers
	 *
	 * @param string $service_url Shipper DEV API service URL.
	 *
	 * @return string
	 */
	public function stubify_api_url( $service_url ) {
		return add_query_arg(
			'action',
			'shipper_stub_api_',
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Augment the stub request args
	 *
	 * Mostly to ensure the calls are made as the user themselves.
	 *
	 * @param array $args Service call arguments.
	 *
	 * @return array
	 */
	public function stubify_request_args( $args ) {
		$source = ! empty( $_COOKIE ) ? $_COOKIE : array();
		if ( empty( $source ) ) { return $args; }

		$args['cookies'] = array();
		foreach ( $source as $name => $value ) {
			if ( ! preg_match( '/^(wp-|wordpress_)/', $name ) ) { continue; } // Only WP cookies, pl0x.
			$args['cookies'][] = new WP_Http_Cookie(array(
				'name' => $name,
				'value' => $value,
			));
		}

		return $args;
	}

	/**
	 * Stubifies request body domains for migration status update.
	 *
	 * This is so local exports are actually tracked as (fake) remote ones.
	 *
	 * @param array  $args Service call arguments.
	 * @param string $endpoint Requested endpoint.
	 *
	 * @return array
	 */
	public function stubify_destination_domain( $args, $endpoint ) {
		if ( 'set_migration_status' !== $endpoint ) { return $args; }
		if ( empty( $args['body']['domain'] ) ) { return $args; }

		$migration = new Shipper_Model_Stored_Migration;
		$args['body']['domain'] = $migration->get_destination();

		return $args;
	}

	/**
	 * Hardcoded known domains getter helper
	 *
	 * @return array
	 */
	public function get_known_domains() {
		return array(
			array(
				'domain' => Shipper_Model_Stored_Destinations::get_current_domain(),
				'site_id' => 1312,
				'home_url' => Shipper_Model_Stored_Destinations::get_current_domain(),
				'admin_url' => trailingslashit( Shipper_Model_Stored_Destinations::get_current_domain() ) . 'wp-admin/',
			),
			array(
				'domain' => $this->get_domain( 'http://example.com' ),
				'site_id' => 13,
				'home_url' => $this->get_domain( 'http://example.com' ),
				'admin_url' => trailingslashit( $this->get_domain( 'http://example.com' ) ) . 'wp-admin/',
			),
			array(
				'domain' => $this->get_domain( 'https://redbull.com' ),
				'site_id' => 12,
				'home_url' => $this->get_domain( 'https://redbull.com' ),
				'admin_url' => trailingslashit( $this->get_domain( 'https://redbull.com' ) ) . 'wp-admin/',
			),
		);
	}

	/**
	 * Normalized domain getter helper
	 *
	 * @param string $url URL to normalize.
	 *
	 * @return string
	 */
	public function get_domain( $url ) {
		return Shipper_Model_Stored_Destinations::get_normalized_domain( $url );
	}
}