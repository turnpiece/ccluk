<?php

/**
 * Handles quicktags replacement in text.
 *
 * Shortcodes:
 *   [map] .. process_tags()
 *   [agm_map] .. process_tags()
 */
class AgmMarkerReplacer {

	/**
	 * This is used to output map related data in the page footer, after the
	 * content is finished
	 *
	 * @since  2.8.6.1
	 */
	private static $_footers = array();

	/**
	 * Constructor
	 */
	function __construct() {
		$this->model = new AgmMapModel();
	}

	/**
	 * Creates a replacer and registers shortcodes.
	 *
	 * @access public
	 * @static
	 */
	static function register() {
		$me = new AgmMarkerReplacer();
		$me->register_shortcodes();
	}

	/**
	 * Registers shortcodes for processing.
	 *
	 * @access private
	 */
	private function register_shortcodes() {
		if ( 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ) {
			/*
			 * Always register the alternative shortcode:
			 * We had issues when "map" shortcode was already used by another plugin.
			 *
			 * @since 2.8.6.1
			 */
			add_shortcode( 'agm_map', array( $this, 'process_tags' ) );
		} else {
			// This is the default shortcode.
			add_shortcode( 'map', array( $this, 'process_tags' ) );
		}

		// When using shortcode-text we display map data in the page footer.
		add_action( 'wp_footer', array( $this, 'footer_content' ) );
	}

	/**
	 * Creates markup to insert a single map.
	 *
	 * @access private
	 * @return string HTML code that displays a map.
	 */
	public function create_tag( $map, $overrides = array() ) {
		$map = apply_filters( 'agm-create-tag', $map, $overrides );
		if ( empty( $map['id'] ) ) {
			return '';
		}

		if ( is_array( $overrides ) ) {
			$map = array_merge( $map, $overrides );
		}

		$elid = 'map-' . md5( microtime() . rand() );
		$content = apply_filters( 'agm_google_maps-shortcode-tag_content', '', $map );
		$rpl = '<div class="agm_google_maps" id="' . $elid . '">' . $content . '</div>';
		$rpl .= '<script type="text/javascript">_agmMaps.push({selector: "#' . $elid . '", data: ' . json_encode( $map ) . '});</script>';

		AgmDependencies::ensure_presence();

		return $rpl;
	}

	/**
	 * Creates markup to insert multiple maps.
	 *
	 * @access private
	 */
	public function create_tags( $maps, $overrides = array() ) {
		if ( ! is_array( $maps ) ) {
			return '';
		}
		$ret = '';
		foreach ( $maps as $map ) {
			$ret .= $this->create_tag( $map, $overrides );
		}
		return $ret;
	}

	/**
	 * Creates a map overlay.
	 * Takes all resulting maps from a query and merges all
	 * markers into one map with default settings.
	 *
	 * @access private
	 */
	public function create_overlay_tag( $maps, $overrides = array() ) {
		if ( ! is_array( $maps ) ) {
			return '';
		}

		$map = $this->model->merge_markers( $maps );
		return $this->create_tag( $map, $overrides );
	}

	/**
	 * Inserts a map for tags with ID attribute set.
	 *
	 * @access private
	 */
	public function process_map_id_tag( $map_id, $overrides = array() ) {
		$map = $this->model->get_map( $map_id );
		return $this->create_tag( $map, $overrides );
	}

	/**
	 * Inserts a map for tags with query attribute set.
	 *
	 * @access private
	 */
	private function process_map_query_tag( $query, $overrides = array(), $overlay = false, $network = false ) {
		$result = '';
		$data = null;

		switch ( $query ) {
			case 'current_post':
				$data = $this->model->get_current_post_maps();
				break;

			case 'random':
				if ( $network ) { $data = $this->model->get_random_network_map(); }
				else { $data = $this->model->get_random_map(); }
				break;

			case 'all':
				if ( $network ) { $data = $this->model->get_all_network_maps(); }
				else { $data = $this->model->get_all_maps(); }
				break;

			default:
				if ( $network ) { $data = $this->model->get_custom_network_maps( $query ); }
				else { $data = $this->model->get_custom_maps( $query ); }
				break;
		}

		if ( $overlay ) {
			$result = $this->create_overlay_tag( $data, $overrides );
		} else {
			$result = $this->create_tags( $data, $overrides );
		}

		return $result;
	}

	public function arguments_to_overrides( $atts = array() ) {
		$overrides = array();
		$map_types = array(
			'ROADMAP',
			'SATELLITE',
			'HYBRID',
			'TERRAIN',
		);
		if ( ! empty( $atts['height'] ) ) { $overrides['height'] = $atts['height']; }
		if ( ! empty( $atts['width'] ) ) { $overrides['width'] = $atts['width']; }
		if ( ! empty( $atts['zoom'] ) ) { $overrides['zoom'] = $atts['zoom']; }
		if ( ! empty( $atts['map_type'] ) && in_array( strtoupper( $atts['map_type'] ), $map_types ) ) {
			$overrides['map_type'] = strtoupper( $atts['map_type'] );
		}

		if ( @$atts['show_map'] ) { $overrides['show_map'] = (int) agm_positive_values( $atts['show_map'] ); }
		if ( @$atts['show_markers'] ) { $overrides['show_markers'] = (int) agm_positive_values( $atts['show_markers'] ); }
		if ( @$atts['show_images'] ) { $overrides['show_images'] = (int) agm_positive_values( $atts['show_images'] ); }
		if ( @$atts['show_posts'] ) { $overrides['show_posts'] = (int) agm_positive_values( $atts['show_posts'] ); }
		if ( @$atts['plot_routes'] ) { $overrides['plot_routes'] = (int) agm_positive_values( $atts['plot_routes'] ); }

		return apply_filters( 'agm-shortcode-overrides', $overrides, $atts );
	}

	/**
	 * Returns an array with the shortcode default values.
	 *
	 * @since  2.9
	 * @return array Default values for map shortcodes.
	 */
	public function default_values( $extra_fields = null ) {
		// These values are the core attributes used by [map]/[agm_map]
		$defaults = array(
			'id' => null,
			'query' => null,
			'overlay' => null,
			'network' => null,
		// Appearance overrides
			'height' => null,
			'width' => null,
			'zoom' => null,
			'show_map' => null,
			'show_markers' => null,
			'show_images' => null,
			'show_posts' => null,
			'map_type' => null,
		// Command switches
			'plot_routes' => null,
		);

		if ( is_array( $extra_fields ) ) {
			// Add any extra fields to the array (used by certain shortcodes).
			$defaults = array_merge( $defaults, $extra_fields );
		}

		// Apply the filter.
		$defaults = apply_filters(
			'agm-shortcode-defaults',
			$defaults
		);

		return $defaults;
	}

	/**
	 * Processes text and replaces recognized tags.
	 *
	 * @access public
	 */
	public function process_tags( $atts, $content = null ) {
		global $post;
		$body = false;
		$defaults = $this->default_values();
		$atts = shortcode_atts(
			$defaults,
			$atts
		);

		// Stacked queries fix
		$atts['query'] = str_replace(
			array( '&amp;', '&#038;' ),
			'&',
			$atts['query']
		);

		$atts = apply_filters( 'agm-shortcode-process', $atts );
		$overrides = $this->arguments_to_overrides( $atts );
		if ( ! defined( 'AGM_USE_POST_INDEXER' ) || ! AGM_USE_POST_INDEXER ) {
			$atts['network'] = false; // Can't do this without Post Indexer
		}

		$map_id = 'map';
		if ( $atts['id'] ) {
			// Single map, no overlay
			$body = $this->process_map_id_tag( $atts['id'], $overrides );
			$map_id = 'map-' . $atts['id'];
		} else if ( $atts['query'] ) {
			$body = $this->process_map_query_tag(
				$atts['query'],
				$overrides,
				$atts['overlay'],
				$atts['network']
			);
		}

		/**
		 * If $content is specified then there will be no map but a text-link
		 * on the page. Clicking that text-link will open a popup with the map.
		 */
		if ( ! empty( $content ) ) {
			self::$_footers[] = // The map is invisible at page load.
			'<div ' .
				'style="display:none;" ' .
				'id="' . esc_attr( $map_id ) . '-container" ' .
				'class="agm-map-box" ' .
				'data-width="' . esc_attr( $atts['width'] ) . '" ' .
				'data-height="' . esc_attr( $atts['height'] ) . '" >' .
				$body .
			'</div>';

			$body = // The text-link.
			'<a href="#' . esc_attr( $map_id ) . '" id="' . esc_attr( $map_id ) . '" class="agm-map-popup">' .
				$content .
			'</a>';
		}

		return $body;
	}

	/**
	 * Display content in the page footer.
	 * Needed when the map is used like this: [map]Text[/map].
	 * Problem is, that the map will always create a new paragraph, even when
	 * hidden. That's why we move the map code to the footer.
	 *
	 * @since  2.8.6.1
	 */
	public function footer_content() {
		echo '' . implode( '', self::$_footers );
	}
};