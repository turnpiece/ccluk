<?php

/**
 * Class WP_Hummingbird_Module_Cloudflare
 */
class WP_Hummingbird_Module_Cloudflare extends WP_Hummingbird_Module {

	/**
	 * Module slug name
	 *
	 * @var string
	 */
	protected $slug = 'cloudflare';

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = 'Cloudflare';

	public function run() {}

	/**
	 * Initializes Cloudflare module
	 */
	public function init() {
		// Only run tests in admin.
		if ( ! is_admin() ) {
			return;
		}

		if ( $this->has_cloudflare() ) {
			add_filter( 'wp_hummingbird_is_active_module_' . $this->get_slug(), '__return_true' );
		} else {
			add_filter( 'wp_hummingbird_is_active_module_' . $this->get_slug(), '__return_false' );
		}
	}

	/**
	 * Detect if site is using Cloudflare
	 *
	 * @param bool $force If set to true it will check again
	 *
	 * @return bool
	 */
	public function has_cloudflare( $force = false ) {
		if ( isset( $_GET['wphb-check-cf'] ) ) {
			// If we're checking do not try to check again or it will return a timeout.
			return (bool) WP_Hummingbird_Settings::get_setting( 'connected', $this->slug );
		}

		if ( $force ) {
			WP_Hummingbird_Settings::update_setting( 'connected', false, $this->slug );
		}

		$is_cloudflare_db = WP_Hummingbird_Settings::get_setting( 'connected', $this->slug );

		if ( ! is_numeric( $is_cloudflare_db ) || $force ) {
			$url = add_query_arg( 'wphb-check-cf', 'true', home_url() );
			$head = wp_remote_head( $url, array(
				'sslverify' => false,
			) );

			if ( is_wp_error( $head ) ) {
				// Something weird happened
				$is_cloudflare = false;
			} else {
				$is_cloudflare = false;
				$headers = wp_remote_retrieve_headers( $head );
				if ( isset( $headers['server'] ) && strpos( $headers['server'], 'cloudflare' ) > -1 ) {
					$is_cloudflare = true;
				}
			}

			// Only write if value changes.
			if ( $is_cloudflare_db !== $is_cloudflare ) {
				WP_Hummingbird_Settings::update_setting( 'connected', $is_cloudflare, $this->slug );
			}
		}

		$is_cloudflare = (bool) $is_cloudflare;
		return apply_filters( 'wphb_has_cloudflare', $is_cloudflare );
	}

	public function is_connected() {
		$options = $this->get_options();

		return $options['enabled'];
	}

	public function is_zone_selected() {
		$options = $this->get_options();

		return ! empty( $options['zone'] );
	}

	public function get_plan() {
		$options = $this->get_options();

		return $options['plan'];
	}

	/**
	 * Tries to set the same caching rules in CF
	 */
	private function set_caching_rules() {
		if ( ! $this->is_connected() || ! $this->is_zone_selected() ) {
			return;
		}

		$this->clear_caching_page_rules();

		$expirations = $this->get_filetypes_expirations();

		foreach ( $expirations as $filetype => $expiration ) {
			$this->add_caching_page_rule( $filetype );
		}
	}

	private function clear_caching_page_rules() {
		$rules = $this->get_registered_caching_page_rules();

		foreach ( $rules as $filetype => $id ) {
			$this->delete_caching_page_rule( $filetype );
		}
	}

	private function delete_caching_page_rule( $filetype ) {
		$id = $this->get_registered_caching_page_rule_id( $filetype );
		$this->unregister_caching_page_rule( $filetype );

		if ( ! $this->is_connected() || ! $this->is_zone_selected() ) {
			return;
		}

		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();

		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );

		$api->cloudflare->delete_page_rule( $id, $options['zone'] );
	}

	private function update_caching_page_rule( $filetype ) {
		// Check if the rule exists already
		$id = $this->get_registered_caching_page_rule_id( $filetype );

		if ( $id ) {
			// Delete the rule and add it a new one
			$this->delete_caching_page_rule( $filetype );
		}

		return $this->add_caching_page_rule( $filetype );
	}

	private function add_caching_page_rule( $filetype ) {
		// If exists, delete it
		$this->delete_caching_page_rule( $filetype );

		if ( ! $this->is_connected() || ! $this->is_zone_selected() ) {
			return false;
		}

		$expirations = $this->get_filetypes_expirations();

		if ( ! isset( $expirations[ $filetype ] ) ) {
			return false;
		}

		if ( ! $expirations[ $filetype ] ) {
			return false;
		}

		$targets = self::page_rule_targets( $filetype );
		$actions = self::page_rule_actions( $expirations[ $filetype ] );

		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();
		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );

		$result = $api->cloudflare->add_page_rule( $targets, $actions, $options['zone'] );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$this->register_caching_page_rule( $result->result->id, $filetype );
		return $result->result->id;

	}

	private function get_filetypes_expirations() {
		$options = $this->get_options();

		$expirations = array();
		$_expirations = array(
			'css'  => $options['expiry_css'],
			'js'   => $options['expiry_javascript'],
			'jpg'  => $options['expiry_images'],
			'png'  => $options['expiry_images'],
			'jpeg' => $options['expiry_images'],
			'gif'  => $options['expiry_images'],
			'mp3'  => $options['expiry_media'],
			'mp4'  => $options['expiry_media'],
			'ico'  => $options['expiry_media'],
		);

		foreach ( $_expirations as $filetype => $time ) {
			if ( ! $time ) {
				$expirations[ $filetype ] = false;
				continue;
			}

			$time = explode( '/', $time );
			if ( count( $time ) != 2 ) {
				$expirations[ $filetype ] = false;
				continue;
			}

			$time = absint( ltrim( $time[1], 'A' ) );

			if ( ! $time ) {
				$expirations[ $filetype ] = false;
				continue;
			}

			$expirations[ $filetype ] = $time;
		}

		return $expirations;
	}

	private static function page_rule_targets( $filetype ) {
		return array(
			array(
				'target'     => 'url',
				'constraint' => array(
					'operator' => 'matches',
					'value'    => '*caninomag.es*.' . $filetype,
				),
			),
		);
	}

	private static function page_rule_actions( $time ) {
		return array(
			array(
				'id'    => 'browser_cache_ttl',
				'value' => $time,
			),
		);
	}

	/**
	 * Register a rule added to CF so they can be listed them later
	 *
	 * @param $id
	 * @param $filetype
	 */
	private function register_caching_page_rule( $id, $filetype ) {
		$options = $this->get_options();
		$options['page_rules'][ $filetype ] = $id;
		$this->update_options( $options );
	}

	/**
	 * Register a rule added to CF so they can be listed them later
	 *
	 * @param $filetype
	 */
	private function unregister_caching_page_rule( $filetype ) {
		$options = $this->get_options();

		if ( isset( $options['page_rules'][ $filetype ] ) ) {
			unset( $options['page_rules'][ $filetype ] );
			$this->update_options( $options );
		}
	}

	private function get_registered_caching_page_rule_id( $filetype ) {
		$options = $this->get_options();

		return ( isset( $options['page_rules'][ $filetype ] ) ) ? $options['page_rules'][ $filetype ] : false;
	}

	private function get_registered_caching_page_rules() {
		$options = $this->get_options();

		return $options['page_rules'];
	}

	/**
	 * Get a list of Cloudflare zones
	 *
	 * @param int $page
	 * @param array $zones
	 *
	 * @return WP_Error|array
	 */
	public function get_zones_list( $page = 1, $zones = array() ) {
		if ( is_wp_error( $zones ) ) {
			return $zones;
		}
		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();
		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );
		$result = $api->cloudflare->get_zones_list( $page );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$_zones = $result->result;
		foreach ( $_zones as $zone ) {
			$zones[] = array(
				'value' => $zone->id,
				'label' => $zone->name,
				'plan'  => $zone->plan->legacy_id,
			);
		}

		if ( $result->result_info->total_pages > $page ) {
			// Get the next page
			return $this->get_zones_list( ++$page, $zones );
		}

		return $zones;
	}

	/**
	 * Get a list of all page rules in CF
	 *
	 * @return WP_Error|array
	 */
	private function get_page_rules_list() {
		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();
		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );

		$result = $api->cloudflare->get_page_rules_list( $options['zone'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $result->result;
	}

	public function set_caching_expiration( $value ) {
		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();
		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );

		$value = absint( $value );
		$freqs = WP_Hummingbird_Utils::get_cloudflare_frequencies();
		if ( ! $value || ! array_key_exists( $value, $freqs ) ) {
			return new WP_Error( 'cf_invalid_value', __( 'Invalid Cloudflare expiration value', 'wphb' ) );
		}

		return $api->cloudflare->set_caching_expiration( $options['zone'], $value );
	}

	public function get_caching_expiration() {
		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();
		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );
		$result = $api->cloudflare->get_caching_expiration( $options['zone'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $result->result->value;
	}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * @since 1.7.1 Changed name from purge_cache to clear_cache
	 *
	 * @return mixed
	 */
	public function clear_cache() {
		$options = $this->get_options();
		$api = WP_Hummingbird_Utils::get_api();
		$api->cloudflare->set_auth_email( $options['email'] );
		$api->cloudflare->set_auth_key( $options['api_key'] );
		$result = $api->cloudflare->purge_cache( $options['zone'] );
		return $result->result;
	}

	/**
	 * Check if Cloudflare is disconnected.
	 */
	public function disconnect() {
		$options = $this->get_options();
		$this->clear_caching_page_rules();

		$options['enabled']   = false;
		$options['connected'] = false;
		$options['email']     = '';
		$options['api_key']   = '';
		$options['zone']      = '';
		$options['zone_name'] = '';
		$options['plan']      = '';

		$this->update_options( $options );
	}

}