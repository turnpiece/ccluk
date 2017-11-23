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


	/**
	 * Initializes Minify module
	 */
	public function init() {
		if ( self::has_cloudflare() ) {
			add_filter( 'wp_hummingbird_is_active_module_' . $this->get_slug(), '__return_true' );
		}
		else {
			add_filter( 'wp_hummingbird_is_active_module_' . $this->get_slug(), '__return_false' );
		}
	}

	public function run() {}

	/**
	 * Detect if site is using Cloudflare
	 *
	 * @param bool $force If set to true it will check again
	 *
	 * @return bool
	 */
	public static function has_cloudflare( $force = false ) {
		if ( isset( $_GET['wphb-check-cf'] ) ) {
			// If we're checking do not try to check again or it will return a timeout.
			return (bool) get_site_option( 'wphb-is-cloudflare' );
		}
		if ( $force ) {
			delete_site_option( 'wphb-is-cloudflare' );
		}

		$is_cloudflare = get_site_option( 'wphb-is-cloudflare' );

		if ( ! is_numeric( $is_cloudflare ) || $force ) {
			$url = add_query_arg( 'wphb-check-cf', 'true', home_url() );
			$head = wp_remote_head( $url, array( 'sslverify' => false ) );
			if ( is_wp_error( $head ) ) {
				// Something weird happened
				$is_cloudflare = false;
			}
			else {
				$headers = wp_remote_retrieve_headers( $head );
				if ( isset( $headers['server'] ) && strpos( $headers['server'], 'cloudflare' ) > -1 ) {
					$is_cloudflare = true;
					update_site_option( 'wphb-is-cloudflare', 1 );
				}
				else {
					$is_cloudflare = false;
					update_site_option( 'wphb-is-cloudflare', 0 );
				}
			}
		}

		$is_cloudflare = (bool) $is_cloudflare;
		return apply_filters( 'wphb_has_cloudflare', $is_cloudflare );
	}

	public function is_connected() {
		return wphb_get_setting( 'cloudflare-connected' );
	}

	public function is_zone_selected() {
		$zone = wphb_get_setting( 'cloudflare-zone' );
		return ! empty( $zone );
	}

	public function get_plan() {
		return wphb_get_setting( 'cloudflare-plan' );
	}

	/**
	 * Tries to set the same caching rules in CF
	 */
	public function set_caching_rules() {
		if ( ! $this->is_connected() || ! $this->is_zone_selected() ) {
			return;
		}

		$this->clear_caching_page_rules();

		$expirations = self::get_filetypes_expirations();

		foreach ( $expirations as $filetype => $expiration ) {
			$this->add_caching_page_rule( $filetype );
		}
	}

	public function clear_caching_page_rules() {
		$rules = $this->get_registered_caching_page_rules();

		foreach ( $rules as $filetype => $id ) {
			$this->delete_caching_page_rule( $filetype );
		}
	}

	public function delete_caching_page_rule( $filetype ) {
		$id = $this->get_registered_caching_page_rule_id( $filetype );
		$this->unregister_caching_page_rule( $filetype );

		if ( ! $this->is_connected() || ! $this->is_zone_selected() ) {
			return;
		}

		$zone = wphb_get_setting( 'cloudflare-zone' );
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );

		$api->cloudflare->delete_page_rule( $id, $zone );
	}

	public function update_caching_page_rule( $filetype ) {
		// Check if the rule exists already
		$id = $this->get_registered_caching_page_rule_id( $filetype );

		if ( $id ) {
			// Delete the rule and add it a new one
			$this->delete_caching_page_rule( $filetype );
		}

		return $this->add_caching_page_rule( $filetype );
	}

	public function add_caching_page_rule( $filetype ) {
		// If exists, delete it
		$this->delete_caching_page_rule( $filetype );

		if ( ! $this->is_connected() || ! $this->is_zone_selected() ) {
			return false;
		}

		$expirations = self::get_filetypes_expirations();

		if ( ! isset( $expirations[ $filetype ] ) ) {
			return false;
		}

		if ( ! $expirations[ $filetype ] ) {
			return false;
		}

		$targets = self::page_rule_targets( $filetype );
		$actions = self::page_rule_actions( $expirations[ $filetype ] );

		$zone = wphb_get_setting( 'cloudflare-zone' );
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );

		$result = $api->cloudflare->add_page_rule( $targets, $actions, $zone );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$this->register_caching_page_rule( $result->result->id, $filetype );
		return $result->result->id;

	}

	public static function get_filetypes_expirations() {
		$settings = wphb_get_settings();
		$expirations = array();
		$_expirations = array(
			'css' => $settings['caching_expiry_css'],
			'js' => $settings['caching_expiry_javascript'],
			'jpg' => $settings['caching_expiry_images'],
			'png' => $settings['caching_expiry_images'],
			'jpeg' => $settings['caching_expiry_images'],
			'gif' => $settings['caching_expiry_images'],
			'mp3' => $settings['caching_expiry_media'],
			'mp4' => $settings['caching_expiry_media'],
			'ico' => $settings['caching_expiry_media']
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

	public static function page_rule_targets( $filetype ) {
		return array(
			array(
				'target' => 'url',
				'constraint' => array(
					'operator' => 'matches',
					'value' => '*caninomag.es*.' . $filetype
				)
			)
		);
	}

	public static function page_rule_actions( $time ) {
		return array(
			array(
				'id' => 'browser_cache_ttl',
				'value' => $time
			)
		);
	}

	/**
	 * Register a rule added to CF so they can be listed them later
	 *
	 * @param $id
	 * @param $filetype
	 */
	public function register_caching_page_rule( $id, $filetype ) {
		$current_rules = wphb_get_setting( 'cloudflare-page-rules' );
		$current_rules[ $filetype ] = $id;
		wphb_update_setting( 'cloudflare-page-rules', $current_rules );
	}

	/**
	 * Register a rule added to CF so they can be listed them later
	 *
	 * @param $id
	 * @param $filetype
	 */
	public function unregister_caching_page_rule( $filetype ) {
		$current_rules = wphb_get_setting( 'cloudflare-page-rules' );
		if ( isset( $current_rules[ $filetype ] ) ) {
			unset( $current_rules[ $filetype ] );
			wphb_update_setting( 'cloudflare-page-rules', $current_rules );
		}

	}

	public function get_registered_caching_page_rule_id( $filetype ) {
		$current_rules = wphb_get_setting( 'cloudflare-page-rules' );
		return ( isset( $current_rules[ $filetype ] ) ) ? $current_rules[ $filetype ] : false;
	}

	public function get_registered_caching_page_rules() {
		return wphb_get_setting( 'cloudflare-page-rules' );
	}

	/**
	 * Get a list of Cloudflare zones
	 *
	 * @return WP_Error|array
	 */
	public function get_zones_list( $page = 1, $zones = array() ) {
		if ( is_wp_error( $zones ) ) {
			return $zones;
		}
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );
		$result = $api->cloudflare->get_zones_list( $page );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$_zones = $result->result;
		foreach ( $_zones as $zone ) {
			$zones[] = array(
				'value' => $zone->id,
				'label' => $zone->name,
				'plan' => $zone->plan->legacy_id
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
	public function get_page_rules_list() {
		$zone = wphb_get_setting( 'cloudflare-zone' );
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );

		$result = $api->cloudflare->get_page_rules_list( $zone );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $result->result;
	}

	public function set_caching_expiration( $value ) {
		$zone = wphb_get_setting( 'cloudflare-zone' );
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );

		$value = absint( $value );
		$freqs = wphb_get_caching_cloudflare_frequencies();
		if ( ! $value || ! array_key_exists( $value, $freqs ) ) {
			return new WP_Error( 'cf_invalid_value', __( 'Invalid Cloudflare expiration value', 'wphb' ) );
		}

		return $api->cloudflare->set_caching_expiration( $zone, $value );
	}

	public function get_caching_expiration() {
		$zone = wphb_get_setting( 'cloudflare-zone' );
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );
		$result = $api->cloudflare->get_caching_expiration( $zone );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $result->result->value;
	}

	public function purge_cache() {
		$zone = wphb_get_setting( 'cloudflare-zone' );
		$api = wphb_get_api();
		$api->cloudflare->set_auth_email( wphb_get_setting( 'cloudflare-email' ) );
		$api->cloudflare->set_auth_key( wphb_get_setting( 'cloudflare-api-key' ) );
		$result = $api->cloudflare->purge_cache( $zone );
		return $result->result;
	}


}
