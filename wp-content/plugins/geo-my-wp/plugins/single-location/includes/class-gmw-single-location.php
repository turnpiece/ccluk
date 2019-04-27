<?php
/**
 * GEO my WP Single Location class.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Single_Location Class
 *
 * Core class for displaying location information of an object ( post, member, group.. ).
 *
 * You can extend this class to be used with a custom objects.
 *
 * @author Eyal Fitoussi
 *
 * @since 2.6.1
 */
class GMW_Single_Location {

	/**
	 * Array of Incoming arguments
	 *
	 * @var array
	 *
	 * @since 2.6.1
	 */
	protected $defaults = array(
		'element_id'           => 0,
		'object'               => 'post', // replaced item_type.
		'object_type'          => '',
		'object_id'            => 0, // replaced item_id.
		'elements'             => 0,
		'address_fields'       => 'address',
		'additional_info'      => '', // deprecated - replaced with location_meta.
		'location_meta'        => '',
		'units'                => 'metric',
		'map_height'           => '250px',
		'map_width'            => '250px',
		'map_type'             => 'ROADMAP',
		'zoom_level'           => 13,
		'scrollwheel_map_zoom' => 1,
		'expand_map_on_load'   => 0,
		'map_icon_url'         => '',
		'map_icon_size'        => '',
		'info_window'          => 'title,address,distance',
		'user_map_icon_url'    => '',
		'user_map_icon_size'   => '',
		'user_info_window'     => 'Your Location',
		'no_location_message'  => 0,
		/** 'is_widget'            => 0,
		// 'widget_title'         => 0, */
	);

	/**
	 * Array for child class to extends the main array above
	 *
	 * @since 2.6.1.
	 *
	 * Public $args
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Object contains the item location information
	 *
	 * @var object
	 *
	 * @since 2.6.1
	 *
	 * Public $location_data
	 */
	public $location_data;

	/**
	 * Hold the object data ( post, user, group ... ).
	 *
	 * @var object
	 */
	public $object_data;

	/**
	 * Holds the location meta data.
	 *
	 * @var array
	 */
	public $location_meta = false;

	/**
	 * Array contains the current user position if exists
	 *
	 * @var array
	 *
	 * @since 2.6.1.
	 *
	 * Public $user_position
	 */
	public $user_position = array(
		'exists'  => false,
		'lat'     => false,
		'lng'     => false,
		'address' => false,
	);

	/**
	 * Array contains the elements to be output
	 *
	 * @var array
	 *
	 * @since 2.6.1
	 *
	 * Public $this->elements
	 */
	public $elements = array();

	/**
	 * Try to get object ID when missing.
	 *
	 * @return [type] [description]
	 */
	public function get_object_id() {
		return 0;
	}

	/**
	 * Get location data.
	 *
	 * @return [type] [description]
	 */
	public function location_data() {

		// check if provided object ID.
		if ( empty( $this->args['object_type'] ) || empty( $this->args['object_id'] ) ) {
			return;
		}

		// get the location data.
		$location = gmw_get_location_by_object( $this->args['object_type'], $this->args['object_id'] );

		return $location;
	}

	/**
	 * Get the object data ( post, member, user... ).
	 *
	 * @return [type] [description]
	 */
	public function get_object_data() {
		return false;
	}

	/**
	 * Display the title of an item
	 *
	 * @since 2.6.1
	 *
	 * @access public
	 */
	public function title() {}


	/**
	 * Verify that the object exists before getting the location
	 * Object might be deleted or in trash while location data still
	 * exists in databased
	 *
	 * @return [type] [description]
	 */
	public function object_exists() {
		return true;
	}

	/**
	 * [__construct description]
	 *
	 * @param array $atts [description].
	 */
	public function __construct( $atts = array() ) {

		// item_type replaced by object - remove in the future.
		if ( empty( $atts['object'] ) && ! empty( $atts['item_type'] ) ) {

			$atts['object'] = $atts['item_type'];

			gmw_trigger_error( '[gmw_single_location] attribute item_type is deprecated since version 3.0. Use "object" instead.' );

			unset( $atts['item_type'] );
		}

		// item_id replaced by object_id - remove in the future.
		if ( empty( $atts['object_id'] ) && ! empty( $atts['item_id'] ) ) {

			$atts['object_id'] = $atts['item_id'];

			gmw_trigger_error( '[gmw_single_location] shortcode attribute item_id is deprecated since version 3.0. Use object_id instead.' );

			unset( $atts['item_id'] );
		}

		// additional_info replaced by location_meta - remove in the future.
		/*if ( empty( $atts['location_meta'] ) && ! empty( $atts['additional_info'] ) ) {

			$atts['location_meta'] = $atts['additional_info'];

			gmw_trigger_error( '[gmw_single_location] shortcode attribute additional_info is deprecated since version 3.0. Use location_meta instead.', E_USER_NOTICE );

			unset( $atts['additional_info'] );
		}*/

		if ( ! empty( $atts['additional_info'] ) ) {

			//$atts['location_meta'] = $atts['additional_info'];

			//gmw_trigger_error( '[gmw_single_location] shortcode attribute additional_info is deprecated since version 3.0. Use location_meta instead.', E_USER_NOTICE );

			unset( $atts['additional_info'] );
		}

		if ( isset( $atts['item_map_icon'] ) ) {

			$atts['map_icon_url'] = $atts['item_map_icon'];

			unset( $atts['item_map_icon'] );
		}

		if ( isset( $atts['object_map_icon'] ) ) {

			$atts['map_icon_url'] = $atts['object_map_icon'];

			unset( $atts['object_map_icon'] );
		}

		if ( isset( $atts['item_info_window'] ) ) {

			$atts['info_window'] = $atts['item_info_window'];

			unset( $atts['item_info_window'] );
		}

		if ( isset( $atts['object_info_window'] ) ) {

			$atts['info_window'] = $atts['object_info_window'];

			unset( $atts['object_info_window'] );
		}

		if ( isset( $atts['user_map_icon'] ) ) {

			$atts['user_map_icon_url'] = $atts['user_map_icon'];

			unset( $atts['user_map_icon'] );
		}

		// extend the default args.
		$this->args = array_merge( $this->defaults, $this->args );

		// get the shortcode atts.
		$this->args = shortcode_atts( $this->args, $atts, 'gmw_single_location' );

		// set random element id if not exists.
		$this->args['element_id'] = ! empty( $this->args['element_id'] ) ? $this->args['element_id'] : wp_rand( 100, 549 );

		// in case object_type is missing.
		if ( empty( $this->args['object_type'] ) ) {
			$this->args['object_type'] = $this->args['object'];
		}

		// If icon size provided, make it an array.
		if ( ! empty( $this->args['map_icon_size'] ) ) {
			$this->args['map_icon_size'] = explode( ',', $this->args['map_icon_size'] );
		}

		// Default icon URL and size.
		if ( '' === $this->args['map_icon_url'] ) {

			$this->args['map_icon_url'] = GMW()->default_icons['location_icon_url'];

			// use default icon size if no size provided.
			if ( '' === $this->args['map_icon_size'] ) {
				$this->args['map_icon_size'] = GMW()->default_icons['location_icon_size'];
			}
		}

		// If icon size provided, make it an array.
		if ( ! empty( $this->args['user_map_icon_size'] ) ) {
			$this->args['user_map_icon_size'] = explode( ',', $this->args['user_map_icon_size'] );
		}

		// Default icon URL and size.
		if ( '' === $this->args['user_map_icon_url'] ) {

			$this->args['user_map_icon_url'] = GMW()->default_icons['user_location_icon_url'];

			// use default icon size if no size provided.
			if ( '' === $this->args['user_map_icon_size'] ) {
				$this->args['user_map_icon_size'] = GMW()->default_icons['user_location_icon_size'];
			}
		}

		// get elements to display.
		$this->elements_value = explode( ',', str_replace( ' ', '', $this->args['elements'] ) );

		// for older version - to be removed.
		foreach ( $this->elements_value as $key => $value ) {

			if ( 'additional_info' === $value ) {

				$this->elements_value[ $key ] = 'location_meta';

				gmw_trigger_error( 'The additional_info value of the [gmw_single_location] shortcode attribute "elements" is deprecated since version 3.0. Use location_meta instead.' );
			}

			if ( 'live_directions' === $value ) {

				$this->elements_value[ $key ] = 'directions_form';

				gmw_trigger_error( 'The live_directions value of the [gmw_single_location] shortcode attribute "elements" is deprecated since version 3.0. Use directions_form instead.' );
			}
		}

		$object_exists = $this->object_exists();

		// check that object exists before anything else.
		if ( empty( $object_exists ) ) {
			return;
		}

		// check that we have at least one element to display.
		if ( empty( $this->elements_value ) ) {
			return;
		}

		if ( empty( $this->args['object_id'] ) ) {
			$this->args['object_id'] = $this->get_object_id();
		}

		// get the locaiton data.
		$this->location_data = $this->location_data();

		// abort if no location found and no need to show message.
		if ( empty( $this->location_data ) && empty( $this->args['no_location_message'] ) ) {
			return;
		}

		// get the object data.
		$this->object_data = $this->get_object_data();

		// generate the elements array.
		$this->elements['element_wrap_start'] = '<div id="gmw-single-location-wrapper-' . esc_attr( $this->args['element_id'] ) . '" class="gmw-single-location-wrapper gmw-sl-wrapper ' . esc_attr( $this->args['object'] ) . ' gmw-single-' . esc_attr( $this->args['object'] ) . '-sc-wrapper">';

		/** Check if this is widget and we use widget title */
		/** If ( $this->args['is_widget'] && ! empty( $this->args['widget_title'] ) ) {
			$this->elements['widget_title'] = true;
		} */

		// if no location found.
		if ( empty( $this->location_data ) ) {

			// generate element for the title ( if title exists in elements ).
			if ( in_array( 'title', $this->elements_value, true ) ) {
				$this->elements['title'] = false;
			}

			// generate element for the no location message.
			$this->elements['no_location_message'] = false;

			// otherwise, generate additional data.
		} else {

			// get labels.
			$this->labels = $this->labels();

			// check for last location in URL.
			if ( ! empty( $_GET['lat'] ) && ! empty( $_GET['lng'] ) ) { // WPCS: CSRF ok.

				$this->user_position['exists'] = true;
				$this->user_position['lat']    = sanitize_text_field( wp_unslash( $_GET['lat'] ) ); // WPCS: CSRF ok.
				$this->user_position['lng']    = sanitize_text_field( wp_unslash( $_GET['lng'] ) ); // WPCS: CSRF ok.

				$address = '';

				if ( ! empty( $_GET['address'] ) ) {

					if ( is_array( $_GET['address'] ) ) {

						$address = implode( ' ', $_GET['address'] ); // WPCS: XSS ok, sanitization ok, CSRF ok.

					} else {
						$address = $_GET['address']; // WPCS: XSS ok, sanitization ok, CSRF ok.
					}
				}

				$this->user_position['address'] = sanitize_text_field( wp_unslash( $address ) );

				// Otherwise check for user location in cookies.
			} elseif ( ! empty( $_COOKIE['gmw_ul_lat'] ) && ! empty( $_COOKIE['gmw_ul_lng'] ) ) {

				$this->user_position['exists']  = true;
				$this->user_position['lat']     = urldecode( wp_unslash( $_COOKIE['gmw_ul_lat'] ) ); // WPCS: sanitization ok.
				$this->user_position['lng']     = urldecode( wp_unslash( $_COOKIE['gmw_ul_lng'] ) ); // WPCS: sanitization ok.
				$this->user_position['address'] = ! empty( $_COOKIE['gmw_ul_address'] ) ? urldecode( wp_unslash( $_COOKIE['gmw_ul_address'] ) ) : ''; // WPCS: sanitization ok.
			}

			// generate elements.
			foreach ( $this->elements_value as $value ) {
				$this->elements[ $value ] = false;
			}
		}

		$this->elements['element_wrap_end'] = '</div>';
	}

	/**
	 * Create labels for the elements
	 *
	 * @since 2.6.1
	 *
	 * Public $labes
	 */
	public function labels() {

		return apply_filters(
			'gmw_sl_labels',
			array(
				'distance'        => __( 'Distance: ', 'geo-my-wp' ),
				'directions'      => __( 'Directions', 'geo-my-wp' ),
				'from'            => __( 'From:', 'geo-my-wp' ),
				'show_directions' => __( 'Show directions', 'geo-my-wp' ),
			),
			$this->args,
			$this->location_data,
			$this
		);
	}

	/**
	 *
	 * Get address
	 *
	 * @since 2.6.1
	 * @access public
	 *
	 * The address of the displayed item
	 */
	public function address() {

		// if item has no location, abort!
		if ( empty( $this->location_data ) ) {
			return ! empty( $this->args['no_location_message'] ) ? $this->no_location_message() : false;
		}

		// get the full address.
		if ( empty( $this->args['address_fields'] ) || 'address' === $this->args['address_fields'] ) {

			$address = ! empty( $this->location_data->formatted_address ) ? $this->location_data->formatted_address : $this->location_data->address;

			// Otherwise, get specific address fields.
		} else {

			$this->args['address_fields'] = ! is_array( $this->args['address_fields'] ) ? explode( ',', $this->args['address_fields'] ) : $this->args['address_fields'];

			$address_array = array();

			foreach ( $this->args['address_fields'] as $field ) {

				if ( empty( $this->location_data->$field ) ) {
					continue;
				}

				$address_array[] = $this->location_data->$field;
			}

			$address = implode( ' ', $address_array );
		}

		$output = '<div class="gmw-sl-address gmw-sl-element"><i class="gmw-location-icon gmw-icon-location"></i><span class="address">' . esc_attr( stripslashes( $address ) ) . '</span></div>';

		return apply_filters( 'gmw_sl_address', $output, $address, $this->args, $this->location_data, $this->user_position, $this );
	}

	/**
	 * Show Distance
	 *
	 * @since 2.6.1
	 * @access public
	 *
	 * Get the distance betwwen the user's position to the item being displayed
	 */
	public function distance() {

		// if item has no location, abort!
		if ( empty( $this->location_data ) ) {
			return ! empty( $this->args['no_location_message'] ) ? $this->no_location_message() : false;
		}

		// check for user position.
		if ( ! $this->user_position['exists'] ) {
			return;
		}

		if ( 'k' === $this->args['units'] || 'metric' === $this->args['units'] ) {
			$units = 'km';
		} else {
			$units = 'mi';
		}

		$distance = gmw_calculate_distance( $this->user_position['lat'], $this->user_position['lng'], $this->location_data->lat, $this->location_data->lng, $this->args['units'] );

		$output  = '<div class="gmw-sl-distance gmw-sl-element">';
		$output .= '<i class="gmw-distance-icon gmw-icon-compass"></i>';
		$output .= '<span class="label">' . esc_attr( $this->labels['distance'] ) . '</span> ';
		$output .= '<span>' . $distance . ' ' . $units . '</span></div>';

		return apply_filters( 'gmw_sl_distance', $output, $distance, $units, $this->args, $this->location_data, $this->user_position, $this );
	}

	/**
	 * Map element
	 *
	 * @since 2.6.1
	 * @access public
	 */
	public function map() {

		// if item has no location, abort!
		if ( empty( $this->location_data ) ) {
			return ! empty( $this->args['no_location_message'] ) ? $this->no_location_message() : false;
		}

		// map args.
		$map_args = array(
			'map_id'         => $this->args['element_id'],
			'map_type'       => 'single_location',
			'prefix'         => 'sl',
			/** 'map_element'     => 'gmw-map-'.$this->args['element_id'], */
			'zoom_position'  => array(
				'lat' => $this->location_data->lat,
				'lng' => $this->location_data->lng,
			),
			'map_width'      => $this->args['map_width'],
			'map_height'     => $this->args['map_height'],
			'expand_on_load' => $this->args['expand_map_on_load'],
			'init_visible'   => true,
		);

		$locations = array(
			0 => array(
				'ID'                  => $this->location_data->object_id,
				'location_id'         => $this->location_data->ID,
				'object_id'           => $this->location_data->object_id,
				'object_type'         => $this->location_data->object_type,
				'lat'                 => $this->location_data->lat,
				'lng'                 => $this->location_data->lng,
				'map_icon'            => apply_filters( 'gmw_sl_post_map_icon', $this->args['map_icon_url'], $this->args, $this->location_data, $this->user_position, $this ),
				'icon_size'           => $this->args['map_icon_size'],
				'info_window_content' => $this->info_window_content(),
			),
		);

		$map_options = array(
			'mapTypeId'         => $this->args['map_type'],
			'zoom'              => $this->args['zoom_level'],
			'mapTypeControl'    => true,
			'streetViewControl' => false,
			'scrollwheel'       => ! empty( $this->args['scrollwheel_map_zoom'] ) ? true : false,
			'panControl'        => false,
		);

		$user_position = array(
			'lat'        => $this->user_position['lat'],
			'lng'        => $this->user_position['lng'],
			'address'    => $this->user_position['address'],
			'map_icon'   => $this->args['user_map_icon_url'],
			'icon_size'  => $this->args['user_map_icon_size'],
			'iw_content' => ! empty( $this->args['user_info_window'] ) ? $this->args['user_info_window'] : null,
		);

		return gmw_get_map( $map_args, $map_options, $locations, $user_position );
	}

	/**
	 * Directions function
	 *
	 * @since 2.6.1
	 *
	 * @access public
	 */
	public function directions_link() {

		// if item has no location, abort!
		if ( empty( $this->location_data ) ) {
			return ! empty( $this->args['no_location_message'] ) ? $this->no_location_message() : false;
		}

		$element_id = esc_attr( $this->args['element_id'] );
		$object     = esc_attr( $this->args['object'] );

		$output  = '';
		$output .= "<div id=\"gmw-sl-directions-link-wrapper-{$element_id}\" class=\"gmw-sl-directions-link-wrapper gmw-sl-element gmw-sl-{$object}-direction-link-wrapper\">";
		$output .= '<div class="trigger-wrapper">';
		$output .= '<i class="gmw-icon-location-thin"></i>';
		$output .= "<a href=\"#\" id=\"form-trigger-{$element_id}\" class=\"form-trigger\" onclick=\"event.preventDefault();jQuery(this).closest( '.gmw-sl-element' ).find( '.directions-link-form-wrapper' ).slideToggle();\">" . esc_attr( $this->labels['directions'] ) . '</a>';
		$output .= '</div>';
		$output .= "<div id=\"directions-link-form-wrapper-{$element_id}\" class=\"directions-link-form-wrapper\" style=\"display:none;\">";
		$output .= '<form action="https://maps.google.com/maps" method="get" target="_blank">';
		$output .= '<div class="address-field-wrapper">';
		$output .= '<label for="start-address-' . $element_id . '">' . esc_attr( $this->labels['from'] ) . ' </label>';
		$output .= '<input type="text" size="35" id="origin-' . $element_id . '" class="origin-field" name="saddr" value="' . esc_attr( $this->user_position['address'] ) . '" placeholder="Your location" />';
		$output .= "<a href=\"#\" class=\"get-directions-link-submit gmw-icon-search\" onclick=\"jQuery( this ).closest( 'form' ).submit();\"></a>";
		$output .= '</div>';
		$output .= '<input type="hidden" name="daddr" value="' . esc_attr( $this->location_data->address ) . '" />';
		$output .= '</form>';
		$output .= '</div>';
		$output .= '</div>';

		return apply_filters( 'gmw_sl_directions', $output, $this->args, $this->location_data, $this->user_position, $this );
	}

	/**
	 * Live directions function
	 *
	 * @since 2.6.1
	 * @access public
	 */
	public function directions_form() {

		// if item has no location, abort!
		if ( empty( $this->location_data ) ) {
			return ! empty( $this->args['no_location_message'] ) ? $this->no_location_message() : false;
		}

		$element_id = esc_attr( $this->args['element_id'] );

		$args = array(
			'element_id'  => $this->args['element_id'],
			'origin'      => $this->user_position['address'],
			'destination' => $this->location_data->address,
		);

		$output  = '<div class="gmw-sl-directions-trigger-wrapper">';
		$output .= '<i class="gmw-directions-icon gmw-icon-location-thin"></i>';
		$output .= "<a href=\"#\" id=\"gmw-sl-directions-trigger-{$element_id}\" class=\"gmw-sl-directions-trigger\" onclick=\"event.preventDefault();jQuery('#gmw-directions-form-wrapper-{$element_id}, #gmw-directions-panel-wrapper-{$element_id}').slideToggle();\">" . esc_attr( $this->labels['show_directions'] ) . '</a>';
		$output .= '</div>';

		$output .= gmw_get_directions_form( $args );

		// for older versions.
		$output = apply_filters( 'gmw_sl_live_directions', $output, $this->args, $this->location_data, $this->user_position, $this );

		return apply_filters( 'gmw_sl_directions_form', $output, $this->args, $this->location_data, $this->user_position, $this );
	}

	/**
	 * Live directions panel
	 * Holder for the results of the live directions
	 *
	 * @since 2.6.1
	 */
	public function directions_panel() {

		$output = gmw_get_directions_panel( $this->args['element_id'] );

		return apply_filters( 'gmw_sl_directions_panel', $output, $this->args, $this->location_data, $this->user_position, $this );
	}

	/**
	 * Display location meta
	 *
	 * @since 3.0
	 *
	 * @access public
	 */
	public function location_meta() {

		if ( empty( $this->args['location_meta'] ) ) {
			return false;
		}

		$contact_info = explode( ',', $this->args['location_meta'] );

		$output  = '<div class="gmw-sl-location-metas gmw-sl-element gmw-sl-additional-info-wrapper">';
		$output .= gmw_get_location_meta_list( $this->location_data->ID, $contact_info );
		$output .= '</div>';

		// for older version - to be removed.
		$output = apply_filters( 'gmw_sl_additional_info', $output, $this->args, $this->location_data, $this->user_position, $this );

		return apply_filters( 'gmw_sl_location_meta', $output, $this->args, $this->location_data, $this->user_position, $this );
	}

	/**
	 * Create the content of the info window
	 *
	 * @since 2.5
	 */
	public function info_window_content() {

		if ( empty( $this->args['info_window'] ) ) {
			return false;
		}

		// get info window elements.
		$iw_elements_array = explode( ',', $this->args['info_window'] );

		$iw_elements = array();

		$iw_elements['iw_start'] = '<div class="gmw-iw-wrapper gmw-sl-iw-wrapper ' . esc_attr( $this->args['object'] ) . '">';

		foreach ( $iw_elements_array as $value ) {
			$iw_elements[ $value ] = false;
		}

		$iw_elements['iw_end'] = '</div>';

		if ( isset( $iw_elements['distance'] ) ) {
			$iw_elements['distance'] = $this->distance();
		}
		if ( isset( $iw_elements['title'] ) ) {
			$iw_elements['title'] = $this->title();
		}
		if ( isset( $iw_elements['address'] ) ) {
			$iw_elements['address'] = $this->address();
		}
		if ( isset( $iw_elements['location_meta'] ) ) {
			$iw_elements['location_meta'] = ! empty( $this->location_meta ) ? $this->location_meta : $this->location_meta();
		}

		$output = apply_filters( 'gmw_sl_object_info_window', $iw_elements, $this->args, $this->location_data, $this->user_position, $this );

		return implode( ' ', $output );
	}

	/**
	 * Display no location message
	 *
	 * @since 2.6.1
	 *
	 * @access public
	 */
	public function no_location_message() {

		return apply_filters( 'gmw_sl_no_location_message', '<h3 class="no-location">' . esc_attr( $this->args['no_location_message'] ) . '</h3>', $this->location_data, $this->args, $this->user_position, $this );
	}

	/**
	 * Display elements based on arguments
	 *
	 * @since 2.6.1
	 *
	 * @access public
	 */
	public function output() {

		// check that we have at least one element to display.
		if ( empty( $this->elements_value ) ) {
			return;
		}

		/** If ( ! empty( $this->elements['widget_title'] ) ) {
		// $this->elements['widget_title'] = html_entity_decode( $this->args['widget_title'] );
		// } */

		// loop through and generate the elements.
		foreach ( $this->elements as $element => $value ) {

			if ( method_exists( $this, $element ) ) {

				$this->elements[ $element ] = $this->$element();
			}
		}

		do_action( 'gmw_sl_before_output_elements', $this->elements, $this->args, $this->location_data, $this->user_position );

		$output = implode( '', $this->elements );

		return apply_filters( 'gmw_sl_display_output', $output, $this->elements, $this->args, $this->location_data, $this->user_position, $this );
	}
}
