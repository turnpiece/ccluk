<?php
/*
Plugin Name: Map location shortcode
Description: Create a map inline in your posts, via a simple shortcode.
Example:     [location address="202 / 120 Bay Street,Port Melbourne"], [location coordinates="-37.84119,144.94071"]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Map_LocationShortcode extends AgmAddonBase {

	private function __construct() {
		$this->_add_hooks();
	}

	public static function serve() {
		$me = new Agm_Map_LocationShortcode();
	}

	private function _add_hooks() {
		add_shortcode(
			'location',
			array( $this, 'process_location' )
		);
	}

	/**
	 * Output a custom map based on a location (either address or coordinates)
	 *
	 * @since  1.0.0
	 */
	public function process_location( $args = array(), $content = '' ) {
		$location_args = shortcode_atts(
			array(
				'coordinates' => false,
				'address' => false,
                'show_posts' => false,
			),
			$args
		);

		if ( ! array_filter( $location_args ) ) {
			return $content;
		}

		$post_id = $this->get_the_id();

		if ( ! empty( $location_args['coordinates'] ) ) {
			return $this->map_from_coordinates(
				$post_id,
				$location_args['coordinates'],
				$args,
				$content
			);
		}

		if ( ! empty( $location_args['address'] ) ) {
			return $this->map_from_address(
				$post_id,
				$location_args['address'],
				$args,
				$content
			);
		}

		return $content;
	}

	/**
	 * Generate a map based on coordinates
	 *
	 * @since  1.0.0
	 */
	private function map_from_coordinates( $post_id, $coordinates, $args = array(), $content = false ) {
		$key = md5( serialize( $args ) );
		$map_id = $this->get_option( 'location', $key );

		$map_id = $this->validate_map_id( $map_id );

		if ( ! $map_id ) {
			$this->clean_map_cache();

			list( $lat, $lng ) = array_map( 'trim', explode( ',', $coordinates ) );
			if ( empty( $lat ) || empty( $lng ) ) { return $content; }

			// Create and store the map in DB.
			$model = $this->map_model();
			$map_id = $model->autocreate_map( false, $lat, $lng, false, $post_id, $args );

			if ( ! $map_id ) {
				$this->admin_note( __( 'Could not create map from shortcode', AGM_LANG ) );
				return $content;
			}

			$this->set_option( 'location', $key, $map_id );
		}

		$args['id'] = $map_id;
		$codec = new AgmMarkerReplacer();
		return $codec->process_tags( $args, $content );
	}

	/**
	 * Generate a map based on an address
	 *
	 * @since  1.0.0
	 */
	private function map_from_address( $post_id, $address, $args = array(), $content = false ) {
		$key = md5( serialize( $args ) );
		$map_id = $this->get_option( 'location', $key );

		$map_id = $this->validate_map_id( $map_id );

		if ( ! $map_id ) {
			$this->clean_map_cache();

			// Create and store the map in DB.
			$model = $this->map_model();
			$map_id = $model->autocreate_map( false, false, false, $address, $post_id, $args );

			if ( ! $map_id ) {
				$this->admin_note( __( 'Could not create map from shortcode', AGM_LANG ) );
				return $content;
			}

			$this->set_option( 'location', $key, $map_id );
		}

		$args['id'] = $map_id;
		$codec = new AgmMarkerReplacer();
		return $codec->process_tags( $args, $content );
	}

	/**
	 * Clears non-existant maps from the location cache
	 *
	 * @since  2.9.0.4
	 */
	private function clean_map_cache() {
		$map_ids = $this->get_option( 'location' );

		foreach ( $map_ids as $hash => $map_id ) {
			$valid = $this->validate_map_id( $map_id );

			if ( ! $valid ) {
				$this->del_option( 'location', $hash );
			}
		}
	}

	/**
	 * Confirms if the given map_id really exists in database.
	 *
	 * @since  2.9.0.4
	 * @param  int $map_id A possible map ID
	 * @return int|false Either the specified map ID or false (ID does not exist)
	 */
	private function validate_map_id( $map_id ) {
		$result = false;
		$model = $this->map_model();
		$map = $model->get_map( $map_id );

		if ( $map ) {
			$result = $map_id;
		}

		return $result;
	}
}

Agm_Map_LocationShortcode::serve();