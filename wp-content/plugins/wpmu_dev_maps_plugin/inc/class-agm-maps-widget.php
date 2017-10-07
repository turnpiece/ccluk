<?php

/**
 * Sidebar widget for Google Maps Plugin.
 */
class AgmMapsWidget extends WP_Widget {
	public function __construct() {
		//parent::WP_Widget( false, $name = 'Google Maps Widget' );
		parent::__construct( false, $name = 'Google Maps Widget' );
		$this->model = new AgmMapModel();
	}

	public function form( $instance ) {
		$fields = array(
			'title',
			'height',
			'width',
			'query',
			'query_custom',
			'network',
			'map_id',
			'show_as_one',
			'show_map',
			'show_markers',
			'show_images',
			'show_posts',
			'zoom',
		);
		lib3()->array->equip( $instance, $fields );

		$title = esc_attr( $instance['title'] );
		$height = esc_attr( $instance['height'] );
		$width = esc_attr( $instance['width'] );
		$query = esc_attr( $instance['query'] );
		$query_custom = esc_attr( $instance['query_custom'] );
		$network = esc_attr( $instance['network'] );
		$map_id = esc_attr( $instance['map_id'] );
		$show_as_one = esc_attr( $instance['show_as_one'] );
		$show_map = esc_attr( $instance['show_map'] );
		$show_markers = esc_attr( $instance['show_markers'] );
		$show_images = esc_attr( $instance['show_images'] );
		$show_posts = esc_attr( $instance['show_posts'] );
		$zoom = esc_attr( $instance['zoom'] );

		// Set defaults
		$height = $height ? $height : 200;
		$width = $width ? $width : 200;
		$query_custom = ('custom' == $query) ? $query_custom : '';
		$network = ('custom' == $query) ? $network : '';
		$show_as_one = ( isset( $instance['show_as_one'] ) ) ? $show_as_one : 1;
		$show_map = ( isset( $instance['show_map'] ) ) ? $show_map : 1;
		$show_markers = ( isset( $instance['show_markers'] ) ) ? $show_markers : 1;
		$show_images = $show_images ? $show_images : 0;
		$show_posts = $show_posts ? $show_posts : 1;

		$zoom_items = array(
			'1' => __( 'Earth', AGM_LANG ),
			'3' => __( 'Continent', AGM_LANG ),
			'5' => __( 'Region', AGM_LANG ),
			'7' => __( 'Nearby Cities', AGM_LANG ),
			'12' => __( 'City Plan', AGM_LANG ),
			'15' => __( 'Details', AGM_LANG ),
		);

		// Load *all* map titles/ids
		$maps = $this->model->get_maps( null, -1 );

		include AGM_VIEWS_DIR . 'widget_settings.php';
	}

	public function update( $new_instance, $old_instance ) {
		$fields = array(
			'title',
			'height',
			'width',
			'query',
			'query_custom',
			'network',
			'map_id',
			'show_as_one',
			'show_map',
			'show_markers',
			'show_images',
			'show_posts',
			'zoom',
		);
		lib3()->array->equip( $new_instance, $fields );

		$instance = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['height']       = strip_tags( $new_instance['height'] );
		$instance['width']        = strip_tags( $new_instance['width'] );
		$instance['query']        = strip_tags( $new_instance['query'] );
		$instance['query_custom'] = strip_tags( $new_instance['query_custom'] );
		$instance['network']      = strip_tags( $new_instance['network'] );
		$instance['map_id']       = strip_tags( $new_instance['map_id'] );
		$instance['show_as_one']  = (int) $new_instance['show_as_one'];
		$instance['show_map']     = (int) $new_instance['show_map'];
		$instance['show_markers'] = (int) $new_instance['show_markers'];
		$instance['show_images']  = (int) $new_instance['show_images'];
		$instance['show_posts']   = (int) $new_instance['show_posts'];
		$instance['zoom']         = (int) $new_instance['zoom'];
		return $instance;
	}

	public function widget( $args, $instance ) {
		$fields = array(
			'title',
			'height',
			'width',
			'query',
			'query_custom',
			'network',
			'map_id',
			'show_as_one',
			'show_map',
			'show_markers',
			'show_images',
			'show_posts',
		);
		lib3()->array->equip( $instance, $fields );

		extract( $args );
		$title        = apply_filters( 'widget_title', $instance['title'] );
		$height       = (int) $instance['height'];
		$height       = $height ? $height : 200; // Apply default
		$width        = (int) $instance['width'];
		$width        = $width ? $width : 200; // Apply default
		$query        = $instance['query'];
		$query_custom = $instance['query_custom'];
		$network      = $instance['network'];
		$map_id       = $instance['map_id'];
		$show_as_one  = (int) agm_positive_values( $instance['show_as_one'] );
		$show_map     = (int) agm_positive_values( $instance['show_map'] );
		$show_markers = (int) agm_positive_values( $instance['show_markers'] );
		$show_images  = (int) agm_positive_values( $instance['show_images'] );
		$show_posts   = (int) agm_positive_values( $instance['show_posts'] );
		$zoom         = (int) $instance['zoom'];

		$maps = $this->get_maps(
			$query,
			$query_custom,
			$map_id,
			$show_as_one,
			$network
		);

		echo '' . $before_widget;
		if ( $title ) {
			echo '' . $before_title . $title . $after_title;
		}

		if ( is_array( $maps ) ) {
			foreach ( $maps as $map ) {
				$selector = 'agm_widget_map_' . md5( microtime() . rand() );
				$map['show_posts'] = (int) $show_posts;
				$map['height'] = $height;
				$map['width'] = $width;
				$map['show_map'] = $show_map;
				$map['show_markers'] = $show_markers;
				$map['show_images'] = $show_images;

				if ( $zoom ) {
					$map['zoom'] = $zoom;
				}

				AgmDependencies::ensure_presence();
				?>
				<div class="agm-google_map-widget" id="<?php echo esc_attr( $selector ); ?>"></div>
				<script type="text/javascript">
				_agmMaps[_agmMaps.length] = {
					selector: "#<?php echo esc_attr( $selector ); ?>",
					data: <?php echo json_encode( $map ); ?>
				};
				</script>
				<?php
			}
		}

		echo '' . $after_widget;
	}

	public function get_maps( $query, $custom, $map_id, $show_as_one, $network ) {
		$ret = false;
		switch ( $query ) {
			case 'current':
				$ret = $this->model->get_current_maps();
				break;

			case 'all_posts':
				$ret = $this->model->get_all_posts_maps();
				break;

			case 'all':
				$ret = $this->model->get_all_maps();
				break;

			case 'random':
				$ret = $this->model->get_random_map();
				break;

			case 'custom':
				if ( $network ) {
					$ret = $this->model->get_custom_network_maps( $custom );
				} else {
					$ret = $this->model->get_custom_maps( $custom );
				}
				break;

			case 'id':
				$ret = array( $this->model->get_map( $map_id ) );
				break;

			default:
				$ret = false;
				break;
		}

		if ( $ret && $show_as_one ) {
			return array( $this->model->merge_markers( $ret ) );
		}

		return $ret;
	}

}