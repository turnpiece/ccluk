<?php

/**
 * Handles public maps interface.
 */
class AgmUserMaps {

	/**
	 * Entry method.
	 *
	 * Creates and handles the Userland interface for the Plugin.
	 *
	 * @access public
	 * @static
	 */
	static function serve () {
		$me = new AgmUserMaps();
		$me->add_hooks();
		$me->model = new AgmMapModel();
	}

	/**
	 * Include additional styles.
	 */
	function css_additional_styles () {
		$css = AgmMapModel::get_config( 'additional_css' );
		if ($css) {
			echo "<style type='text/css'>{$css}</style>";
		}
	}

	/**
	 * Checks post meta and injects the map, if needed.
	 */
	function process_post_meta( $body ) {
		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', $wp_current_filter ) || in_array( 'the_excerpt', $wp_current_filter ) ) {
			return $body; // Do NOT do this in excerpts
		}

		// @since 2.8.6.1
		$shortcode_tag = 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ? 'agm_map' : 'map';

		$fields = AgmMapModel::get_config( 'custom_fields_map' );
		$options = AgmMapModel::get_config( 'custom_fields_options' );
		$post_id = get_the_ID();

		// Check if we have already done this
		$map_id = get_post_meta( $post_id, 'agm_map_created', true );

		$latitude = $longitude = $address = false;
		if ( $fields['latitude_field'] ) {
			$latitude = get_post_meta( $post_id, $fields['latitude_field'], true );
		}
		if ( $fields['longitude_field'] ) {
			$longitude = get_post_meta( $post_id, $fields['longitude_field'], true );
		}
		if ( $fields['address_field'] ) {
			/*
			 * We allow the address-field to contain a list of field names
			 * @since 2.9.0.5
			 */
			$address = '';
			$address_fields = explode( ',', $fields['address_field'] );
			foreach ( $address_fields as $address_field ) {
				$address_field = trim( $address_field );
				$field_value = get_post_meta( $post_id, $address_field, true );
				$address .= $field_value . ' ';
			}
		}

		$latitude = apply_filters( 'agm_google_maps-post_meta-latitude', $latitude );
		$longitude = apply_filters( 'agm_google_maps-post_meta-longitude', $longitude );
		$address = apply_filters( 'agm_google_maps-post_meta-address', $address );

		if ( ! $map_id ) {
			// try to creatr map based on lat/lng.
			if ( ! $latitude && ! $longitude && ! $address ) {
				return $body; // Nothing to process
			}
			$map_id = $this->model->autocreate_map( $post_id, $latitude, $longitude, $address );
		} else {
			// Create map based on saved settings.
			$map = $this->model->get_map( $map_id );
			//TODO: can we make this condition simpler? 90% duplicate code
			if ( $address ) {
				if ( $address != $map['markers'][0]['title'] ) {
					if ( isset( $fields['discard_old'] ) && $fields['discard_old'] ) {
						$this->model->delete_map( array('id' => $map_id) );
					}
					$map_id = $this->model->autocreate_map( $post_id, $latitude, $longitude, $address );
				}
			} else if ( $latitude && $longitude ) {
				if ( $latitude != $map['markers'][0]['position'][0] || $longitude != $map['markers'][0]['position'][1] ) {
					if ( isset( $fields['discard_old'] ) && $fields['discard_old'] ) {
						$this->model->delete_map( array('id' => $map_id) );
					}
					$map_id = $this->model->autocreate_map( $post_id, $latitude, $longitude, $address );
				}
			}
		}

		if ( ! $map_id ) {
			return $body;
		}

		if ( isset($options['autoshow_map']) && $options['autoshow_map'] ) {
			$shortcode_attributes = apply_filters(
				'agm_google_maps-autogen_map-shortcode_attributes',
				array(
					'id' => $map_id,
				)
			);
			$tmp = array();
			foreach ( $shortcode_attributes as $key => $value ) {
				$tmp[] = $key . '="' . $value . '"';
			}
			$shortcode = '[' . $shortcode_tag . ' ' . join( ' ', $tmp ) . ']';
			if ( 'top' == $options['map_position'] ) {
				$body = "{$shortcode}\n" . $body;
			} else {
				$body .= "\n{$shortcode}";
			}
		}
		return $body;
	}

	/**
	 * Adds needed hooks.
	 *
	 * @access private
	 */
	private function add_hooks() {
		// Step 1: Additional styles
		add_action('wp_head', array($this, 'css_additional_styles'));

		// Step2: Register custom fields processing
		if ( AgmMapModel::get_config( 'use_custom_fields' ) ) {
			add_filter( 'the_content', array( $this, 'process_post_meta' ), 1 ); // Note the order
		}


		// Step3: Process map tags
		$rpl = AgmMarkerReplacer::register();
	}
}