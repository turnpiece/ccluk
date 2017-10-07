<?php

/**
 * Mediates all database interactions.
 *
 * This is where all the map data is saved and loaded.
 */
class AgmMapModel {

	/**
	 * Name of the table where map data is located.
	 * No table prefix.
	 *
	 * @access private
	 */
	var $_table_name = 'agm_maps';

	/**
	 * Internal cache of plugin options to minimize DB access
	 *
	 * @since 2.8.6.1
	 */
	private static $_options = null;


	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Returns the name of maps database table.
	 *
	 * @access public
	 * @return string Name of the table with prefix.
	 */
	public function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . $this->_table_name;
	}

	/**
	 * Removes all maps from database.
	 *
	 * @return bool
	 */
	public function clear_table() {
		global $wpdb;

		$table = $this->get_table_name();
		$wpdb->query( "TRUNCATE {$table}" );
		$rows = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) );
		return ( 0 === $rows );
	}

	/**
	 * Fetches maps associated with current WP posts.
	 *
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_current_maps() {
		global $wpdb, $wp_query;

		$table = $this->get_table_name();
		$posts = $wp_query->get_posts();
		$where_string = $this->prepare_query_string( $posts );

		if ( ! $where_string ) {
			return false;
		}

		$maps = $wpdb->get_results(
			"SELECT * FROM {$table} {$where_string}",
			ARRAY_A
		);

		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}
		return $maps;
	}

	/**
	 * Fetches maps associated with current WP post - singular one, even on
	 * archive pages.
	 *
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_current_post_maps() {
		global $wpdb, $post;

		$table = $this->get_table_name();
		$posts = array( $post );
		$where_string = $this->prepare_query_string( $posts );

		if ( ! $where_string ) {
			return false;
		}

		$maps = $wpdb->get_results(
			"SELECT * FROM {$table} {$where_string}",
			ARRAY_A
		);

		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}
		return $maps;
	}

	/**
	 * Fetches all maps associated with any posts.
	 *
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_all_posts_maps() {
		global $wpdb;

		$table = $this->get_table_name();

		$maps = $wpdb->get_results(
			"SELECT * FROM {$table} WHERE post_ids <> 'a:0:{}'",
			ARRAY_A
		);

		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}
		return $maps;
	}

	/**
	 * Fetches all maps.
	 *
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_all_maps() {
		global $wpdb;

		$table = $this->get_table_name();
		$maps = $wpdb->get_results(
			"SELECT * FROM {$table}",
			ARRAY_A
		);

		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}
		return $maps;
	}

	/**
	 * Fetches a random map.
	 *
	 * @return mixed Map array on success, false on failure
	 */
	public function get_random_map() {
		global $wpdb;

		$table = $this->get_table_name();
		$map = $wpdb->get_row(
			"SELECT * FROM {$table} ORDER BY RAND() LIMIT 1",
			ARRAY_A
		);

		return $map ? array( $this->prepare_map( $map ) ) : false;
	}

	/**
	 * Fetches maps associated with posts found with custom WP posts query.
	 *
	 * @param string Custom WP posts query
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_custom_maps( $query ) {
		global $wpdb;

		$table = $this->get_table_name();

		// By default we want to search for addesses in all post-types.
		$defaults = array(
			'post_type' => 'any',
		);

		$query = wp_parse_args(
			$query,
			$defaults
		);

		$wpq = new WP_Query( $query );
		$posts = $wpq->posts ? $wpq->posts : array();
		$where_string = $this->prepare_query_string( $posts );

		if ( ! $where_string ) {
			return false;
		}

		$sql = "SELECT * FROM {$table} {$where_string}";
		$maps = $wpdb->get_results( $sql, ARRAY_A );

		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}
		return $maps;
	}

	/**
	 * Returns an array of blog_ids
	 *
	 * @since  2.9
	 * @return array|false (non multisite returns false)
	 */
	protected function get_blogs() {
		global $wpdb;
		static $Blogs = null;

		if ( null === $Blogs ) {
			if ( is_multisite() ) {
				$sql = "
					SELECT blog_id
					FROM {$wpdb->base_prefix}blogs
					WHERE public='1'
						AND archived='0'
						AND mature='0'
						AND spam='0'
						AND deleted='0'
				";
				$Blogs = $wpdb->get_col( $sql );
				if ( ! is_array( $Blogs ) ) {
					$Blogs = false;
				}
			} else {
				$Blogs = false;
			}
		}

		return $Blogs;
	}

	/**
	 * Fetches all maps on the network.
	 * This could potentially be quite slow.
	 *
	 * @return Maps array on success, false on failure
	 */
	public function get_all_network_maps() {
		$maps = array();
		$blogs = $this->get_blogs();
		if ( ! $blogs ) {
			return $this->get_all_maps();
		}

		foreach ( $blogs as $blog ) {
			$blog_maps = array();
			switch_to_blog( $blog );
			$blog_maps = $this->get_all_maps();

			foreach ( $blog_maps as $idx => $map ) {
				$blog_maps[$idx]['blog_id'] = $blog;
			}

			$maps = array_merge( $maps, $blog_maps );
			restore_current_blog();
		}

		return $maps;
	}

	/**
	 * Fetches random map from random blog on the network.
	 *
	 * @return Maps array on success, false on failure
	 */
	public function get_random_network_map() {
		$map = false;
		$blogs = $this->get_blogs();
		if ( ! $blogs ) {
			return $this->get_random_map();
		}

		for ( $iter = 0; ($iter < 10 && ! $map); $iter += 1 ) {
			// Pick a random blog.
			$blog = $blogs[array_rand( $blogs )];
			switch_to_blog( $blog );

			// Pick a random map from that blog.
			$map = $this->get_random_map();
			restore_current_blog();
		}

		return $map;
	}

	/**
	 * Fetches network maps associated with posts found with custom WP posts query.
	 * Requires Post Indexer plugin.
	 *
	 * @param string Custom WP posts query
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_custom_network_maps( $query ) {
		global $wpdb;

		if ( ! defined( 'AGM_USE_POST_INDEXER' ) || ! AGM_USE_POST_INDEXER ) {
			return false;
		}

		$query = wp_parse_args(
			$query,
			array()
		);

		if ( ! isset( $query['tag'] ) ) {
			return false;
		}
		$tags = explode( ',', $query['tag'] );

		$posts = Agm_PostIndexer::get_posts_by_tags( $tags );
		if ( empty( $posts ) ) {
			return false;
		}

		$sql = array();
		foreach ( $posts as $post ) {
			$blog_id = Agm_PostIndexer::get_post_blog_id( $post );
			$post_id = Agm_PostIndexer::get_post_post_id( $post );
			switch_to_blog( $blog_id );
			$len = strlen( $post_id );

			$table = $this->get_table_name();
			$sql[] = "
				SELECT
					*,
					'{$blog_id}' as blog_id
				FROM {$table}
				WHERE {$table}.post_ids LIKE '%s:{$len}:\"{$post_id}\";%'
			";
			restore_current_blog();
		}
		$sql = join( ' UNION ', $sql );
		$maps = $wpdb->get_results( $sql, ARRAY_A );

		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}
		return $maps;
	}

	/**
	 * Fetches maps by list of IDs.
	 *
	 * @param array List of map IDs
	 * @return mixed Maps array on success, false on failure
	 */
	public function get_maps_by_ids( $ids ) {
		global $wpdb;

		if ( ! is_array( $ids ) ) {
			return false;
		}

		$clean = array();
		foreach ( $ids as $id ) {
			if ( (int) $id ) {
				$clean[] = (int) $id;
			}
		}
		if ( empty( $clean ) ) {
			return false;
		}

		$table = $this->get_table_name();
		$list = join( ',', $clean );
		$sql = "
			SELECT *
			FROM {$table}
			WHERE id IN( {$list} )
		";

		$maps = $wpdb->get_results( $sql, ARRAY_A );
		if ( is_array( $maps ) ) {
			foreach ( $maps as $k => $v ) {
				$maps[$k] = $this->prepare_map( $v );
			}
		}

		return $maps;
	}

	/**
	 * Fetches a list of post titles.
	 *
	 * @param array List of post IDs to fetch titles for
	 * @return mixed Post titles/IDs array on success, false on failure
	 */
	public function get_post_titles( $ids ) {
		global $wpdb;

		if ( ! is_array( $ids ) ) {
			return false;
		}

		$blog_id = false;
		$posts = array();
		$blogs_to_posts = array();

		foreach ( $ids as $k => $v ) {
			if ( false !== strpos( $v, '|' ) ) {
				list( $blog_id, $v ) = explode( '|', $v );
				$blog_id = (int) $blog_id;
			}

			if ( is_array( @$blogs_to_posts[$blog_id] ) ) {
				$blogs_to_posts[$blog_id] = array_merge( $blogs_to_posts[$blog_id], array( $v ) );
			} else {
				$blogs_to_posts[$blog_id] = array( $v );
			}
		}

		foreach ( $blogs_to_posts as $blog_id => $ids ) {
			if ( $blog_id ) {
				switch_to_blog( $blog_id );
			}

			$table = $wpdb->prefix . 'posts';
			$ids_string = join( ', ', $ids );
			$sql = "
			SELECT
				id,
				post_title
			FROM {$table}
			WHERE ID IN ( {$ids_string} )
			";

			$result = $wpdb->get_results( $sql, ARRAY_A );

			foreach ( $result as $rid => $post ) {
				$post['permalink'] = get_permalink( $post['id'] );
				$posts[] = $post;
			}

			if ( $blog_id ) {
				restore_current_blog();
			}
		}

		if ( ! $posts ) {
			return false;
		}

		return $posts;
	}

	/**
	 * Fetches a list of existing maps ids/titles.
	 *
	 * @param int $page (optional) Page to retrieve
	 * @param int $limit (optional) Upper limit to fetch
	 * @return mixed Map id/title array on success, false on failure
	 */
	public function get_maps( $page = false, $limit = false ) {
		global $wpdb;

		if ( ! defined( 'AGM_GET_MAPS_LIMIT' ) ) {
			define( 'AGM_GET_MAPS_LIMIT', 50 );
		}

		$paged = false;
		if ( $limit >= 0 ) {
			$builtin_limit = AGM_GET_MAPS_LIMIT ? AGM_GET_MAPS_LIMIT : 50;
			$limit = apply_filters(
				'agm_google_maps-get_maps-limit',
				( $limit ? $limit : $builtin_limit )
			);
			$start = $page ? $page * $limit : 0;
			$stop = $limit;
			$paged = "LIMIT {$start}, {$stop}";
		}
		$table = $this->get_table_name();
		return $wpdb->get_results( "SELECT id, title FROM {$table} {$paged}", ARRAY_A );
	}

	/**
	 * Returns the total count of maps on the current blog.
	 *
	 * @return int
	 */
	public function get_maps_total() {
		global $wpdb;
		static $Count = null;

		if ( null === $Count ) {
			$table = $this->get_table_name();
			$Count = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) );
		}

		return $Count;
	}

	/**
	 * Returns the plugin settings.
	 *
	 * @since 2.8.6.1
	 */
	static public function get_config( $key ) {
		if ( null === self::$_options ) {
			self::$_options = apply_filters(
				'agm_google_maps-options',
				get_option( 'agm_google_maps', array() )
			);
		}
		return @self::$_options[ $key ];
	}

	/**
	 * Updates the plugin settings.
	 *
	 * @since 2.8.6.1
	 */
	static public function set_config( $key, $new_value ) {
		if ( null === self::$_options ) {
			self::get_config( $key );
		}
		self::$_options[ $key ] = $new_value;
		update_option( 'agm_google_maps', self::$_options );
	}


	/**
	 * Gets a particular map.
	 *
	 * @param int Map id
	 * @return mixed Map array on success, false on failure
	 */
	public function get_map( $id ) {
		global $wpdb;

		$map = wp_cache_get( $id, 'agm_map' );
		if ( empty( $map ) ) {
			$id = absint( $id );
			$table = $this->get_table_name();
			$sql = "
				SELECT *
				FROM {$table}
				WHERE id=%s
			";
			$sql = $wpdb->prepare( $sql, $id );
			$map = $wpdb->get_row( $sql, ARRAY_A );

			$map = $this->prepare_map( $map );
			wp_cache_set( $id, $map, 'agm_map' );
		}

		return $map;
	}

	/**
	 * Returns a list of map default options.
	 *
	 * @return mixed Maps defaults array
	 */
	public function get_map_defaults() {
		$defaults = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );

		if ( ! isset( $defaults['image_limit'] ) ) { $defaults['image_limit'] = 10; }
		if (   isset( $defaults['use_custom_fields'] ) ) { unset( $defaults['use_custom_fields'] ); }
		if (   isset( $defaults['custom_fields_map'] ) ) { unset( $defaults['custom_fields_map'] ); }
		if (   isset( $defaults['custom_fields_options'] ) ) { unset( $defaults['custom_fields_options'] ); }

		// Set all values in the array to boolean FALSE
		$ret = array_filter( $defaults );

		$ret['snapping'] = (int) @$defaults['snapping'];
		if ( isset( $defaults['directions_snapping'] ) ) {
			$ret['directions_snapping'] = (int) $defaults['directions_snapping'];
		} else {
			$ret['directions_snapping'] = 1;
		}

		return $ret;
	}

	/**
	 * Saves a map.
	 *
	 * @param array $map Map data to save.
	 * @return mixed Id on success, false on failure
	 */
	public function save_map( $map ) {
		global $wpdb;

		// Make sure the $map['id'] field exists.
		lib3()->array->equip( $map, 'id' );

		$id = absint( $map['id'] );
		$table = $this->get_table_name();
		$data = $this->prepare_for_save( $map );
		$ret = false;

		if ( ! empty( $id ) ) {
			$result = $wpdb->update( $table, $data, array( 'id' => $id ) );
			$ret = $result !== false ? $id : false;
		} else {
			$result = $wpdb->insert( $table, $data );
			$ret = $result ? $wpdb->insert_id : false;
		}

		if ( $ret ) {
			/*
			 * When a map is changed then clear the object cache.
			 * The cache will be re-created on next get_map() call.
			 *
			 * Problem was, that new maps were missing some defaults settings,
			 * so the preview/rendering of the map failed due to a javascript
			 * error on WP-Engine (or with Memcache enabled)
			 */
			wp_cache_delete( $ret, 'agm_map' );
		}

		return $ret;
	}

	/**
	 * Removes a map from the database.
	 *
	 * @param array Array containing 'id' key
	 * @return mixed Deleted id on success, false on failure
	 */
	public function delete_map( $data ) {
		global $wpdb;

		$id = absint( $data['id'] );
		$table = $this->get_table_name();

		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id = %d", $id ) );
		return $result ? $id : false;
	}

	/**
	 * Deletes maps by list of IDs.
	 *
	 * @param array List of map IDs
	 * @return bool true on success, false on failure
	 */
	public function batch_delete_maps( $ids ) {
		if ( ! is_array( $ids ) ) {
			return false;
		}

		$clean = array();
		foreach ( $ids as $id ) {
			if ( is_numeric( $id ) ) {
				$clean[] = absint( $id );
			}
		}

		if ( empty( $clean ) ) {
			return false;
		}

		$count = 0;
		foreach ( $clean as $id ) {
			$res = $this->delete_map( array( 'id' => $id ) );
			if ( $res ) { $count += 1; }
		}

		return $count;
	}

	/**
	 * Prepares a complex query string.
	 * Used to find maps associated to posts.
	 *
	 * @param array A list of posts
	 * @return mixed Maps array on success, false on failure
	 */
	public function prepare_query_string( $posts ) {
		$where = array();
		if ( ! is_array( $posts ) ) {
			return false;
		}

		foreach ( $posts as $post ) {
			$id = absint( $post->ID );
			$len = strlen( $post->ID );

			// The WHERE condition searches the contens of a serialized PHP array.
			$where[] = "'%s:{$len}:\"{$id}\";%'";
		}
		return 'WHERE post_ids LIKE ' . join( ' OR post_ids LIKE ', $where );
	}

	/**
	 * Prepares map array for serving to front end.
	 *
	 * @param array Map array
	 * @return array Prepared map array
	 */
	public function prepare_map( $map ) {
		if ( ! $map ) {
			return false;
		}

		$markers = unserialize( @$map['markers'] );
		$options = unserialize( @$map['options'] );
		$post_ids = unserialize( @$map['post_ids'] );
		$defaults = $this->get_map_defaults();

		// Data is force-escaped by WP, so compensate for that
		$result = array_map(
			'stripslashes_deep',
			array(
				'markers'                => $markers,
				'defaults'               => $defaults,
				'post_ids'               => array_values( $post_ids ), // Reindex array (prevent conversion to object)
				'id'                     => @$map['id'],
				'title'                  => @$map['title'],
				'height'                 => @$options['height'],
				'width'                  => @$options['width'],
				'zoom'                   => @$options['zoom'],
				'map_type'               => @$options['map_type'],
				'map_alignment'          => @$options['map_alignment'],
				'show_map'               => agm_positive_values( @$options['show_map'], 1, 0 ),
				'show_posts'             => agm_positive_values( @$options['show_posts'], 1, 0 ),
				'show_markers'           => agm_positive_values( @$options['show_markers'], 1, 0 ),
				'show_images'            => agm_positive_values( @$options['show_images'], 1, 0 ),
				'image_size'             => @$options['image_size'],
				'image_limit'            => @$options['image_limit'],
				'show_panoramio_overlay' => agm_positive_values( @$options['show_panoramio_overlay'], 1, 0 ),
				'panoramio_overlay_tag'  => @$options['panoramio_overlay_tag'],
				'street_view'            => @$options['street_view'],
				'street_view_pos'        => @$options['street_view_pos'],
				'street_view_pov'        => @$options['street_view_pov'],
			)
		);

		if ( isset( $map['blog_id'] ) ) {
			$result['blog_id'] = $map['blog_id'];
		}

		$result['markers'] = apply_filters( 'agm-load-markers', $result['markers'], $markers );
		$result = apply_filters( 'agm-load-options', $result, $options );

		return $result;
	}

	/**
	 * Prepares raw map data for saving to the database.
	 *
	 * @param array Raw map data array
	 * @return array Map array prepared for storage
	 */
	public function prepare_for_save( $data ) {
		// Normalize marker contents
		if ( is_array( $data['markers'] ) ) {
			foreach ( $data['markers'] as $k => $v ) {
				// Line remarked to allow full icon URL
				// @since 2.8.5
				//$data['markers'][$k]['icon'] = basename( $v['icon'] );

				if ( ! current_user_can( 'unfiltered_html' ) ) {
					$data['markers'][$k]['body'] = wp_filter_post_kses( $v['body'] );
				}
			}
		}
		$post_ids = is_array( @$data['post_ids'] ) ? array_unique( $data['post_ids'] ) : array();

		// Pack options
		$map_options = array(
			'height'                 => @$data['height'],
			'width'                  => @$data['width'],
			'zoom'                   => @$data['zoom'],
			'map_type'               => strtoupper( @$data['map_type'] ),
			'map_alignment'          => strtolower( @$data['map_alignment'] ),
			'show_map'               => agm_positive_values( @$data['show_map'], 1, 0 ),
			'show_posts'             => agm_positive_values( @$data['show_posts'], 1, 0 ),
			'show_markers'           => agm_positive_values( @$data['show_markers'], 1, 0 ),
			'show_images'            => agm_positive_values( @$data['show_images'], 1, 0 ),
			'image_size'             => @$data['image_size'],
			'image_limit'            => (int) @$data['image_limit'],
			'show_panoramio_overlay' => agm_positive_values( @$data['show_panoramio_overlay'], 1, 0 ),
			'panoramio_overlay_tag'  => @$data['panoramio_overlay_tag'],
			'street_view'            => @$data['street_view'],
			'street_view_pos'        => @$data['street_view_pos'],
			'street_view_pov'        => @$data['street_view_pov'],
		);

		$data['title'] = apply_filters( 'agm-save-title', $data['title'], $data );
		$data['markers'] = apply_filters( 'agm-save-markers', $data['markers'], $data );
		$post_ids = apply_filters( 'agm-save-post-ids', $post_ids, $data );
		$map_options = apply_filters( 'agm-save-options', $map_options, $data );

		// Make sure we fit
		$data['title'] = substr($data['title'], 0, 50);

		// Return prepped data array
		return array(
			'title'    => $data['title'],
			'markers'  => serialize( $data['markers'] ),
			'post_ids' => serialize( $post_ids ),
			'options'  => serialize( $map_options ),
		);
	}

	public function merge_markers( $maps ) {
		if ( ! is_array( $maps ) ) {
			return false;
		}

		$defaults = $this->get_map_defaults();
		$show_map = 1;
		$show_markers = 1;
		$show_images = 1;

		$markers = array();
		foreach ( $maps as $map ) {
			if ( ! is_array( $map['markers'] ) ) {
				$map['markers'] = array();
			}
			$markers = array_merge( $markers, $map['markers'] );
		}

		// Merge in all the post ids too.
		// This is for widget show_posts option.
		$new_markers = $markers;
		foreach ( $maps as $map ) {
			if ( isset( $map['blog_id'] ) && is_array( $map['post_ids'] ) ) {
				foreach ( $map['post_ids'] as $key => $val ) {
					$map['post_ids'][$key] = $map['blog_id'] . "|{$val}";
				}
			}
			foreach ( $markers as $mid => $marker ) {
				$post_ids = isset( $marker['post_ids'] ) ? $marker['post_ids'] : array();
				if ( ! is_array( $map['markers'] ) ) {
					$map['markers'] = array();
				}
				if ( in_array( $marker, $map['markers'] ) ) {
					if ( ! empty( $map['post_ids'] ) ) {
						$post_ids = array_merge( $post_ids, $map['post_ids'] );
					}
					if ( is_array( @$new_markers[$mid]['post_ids'] ) ) {
						$new_markers[$mid]['post_ids'] = array_merge( $new_markers[$mid]['post_ids'], $post_ids );
					} else {
						$new_markers[$mid]['post_ids'] = $post_ids;
					}
				}
			}
		}
		$markers = agm_array_multi_unique( $new_markers );

		return array(
			'id'           => md5( rand(). microtime() ),
			'defaults'     => $defaults,
			'markers'      => $markers,
			'show_map'     => agm_positive_values( $show_map, 1, 0 ),
			'show_markers' => agm_positive_values( $show_markers, 1, 0 ),
			'show_images'  => agm_positive_values( $show_images, 1, 0 ),
			'zoom'         => $defaults['zoom'],
		);
	}

	/**
	 * Autocreates and saves a map from supplied values.
	 *
	 * @param  int|false $post_id Post that is linked with the map.
	 * @param  float|false $lat
	 * @param  float|false $lon
	 * @param  string|false $address
	 * @param  int|false $associated_post_id Post that is associated with map.
	 *         The difference between post_id: Each post_id can be LINKED to
	 *         exactly 1 map. When the linked-map changes, the old map gets
	 *         deleted. However, the post can be ASSOCIATED with many maps.
	 */
	public function autocreate_map( $post_id, $lat, $lon, $address, $associated_post_id = null, $args = array()  ) {
		$opts = apply_filters(
			'agm_google_maps-options',
			get_option( 'agm_google_maps' )
		);

		$do_associate = false;
		if ( isset( $opts['custom_fields_options']['associate_map'] ) ) {
			$do_associate = $opts['custom_fields_options']['associate_map'];

			if ( null === $associated_post_id && ! empty( $post_id ) ) {
				$associated_post_id = $post_id;
			}
		}

		if ( ! $lat || ! $lon ) {
			$geo = $this->_address_to_marker( $address );
		} else {
			$geo = $this->_position_to_marker( $lat, $lon );
		}

		if ( ! $geo ) {
			return false; // Geolocation failed
		}

		$map = $this->get_map_defaults();
		$map['title'] = $geo['title'];
		$map['show_map'] = 1;
		$map['show_markers'] = 1;
		$map['markers'] = array( $geo );
		$map['show_posts'] = !empty($args['show_posts']) ? $args['show_posts'] : false;

		if ( $do_associate ) {
			$map['post_ids'] = array("{$associated_post_id}");
		}

		$map_id = $this->save_map( $map );

		if ( ! $map_id ) {
			return false;
		}

		if ( ! empty( $post_id ) ) {
			update_post_meta( $post_id, 'agm_map_created', $map_id );
		}

		return $map_id;
	}

	/**
	 * Converts valid address information to map marker.
	 *
	 * @access private
	 */
	public function _address_to_marker( $address ) {
		$result = $this->geocode_address( $address );
		if ( ! $result ) {
			return false;
		}

		return array(
			'title'    => $address,
			'body'     => '',
			'icon'     => 'marker.png',
			'position' => array(
				$result->geometry->location->lat,
				$result->geometry->location->lng,
			),
		);
	}

	/**
	 * Converts latitude/longitude pair to map marker.
	 *
	 * @access private
	 */
	public function _position_to_marker( $lat, $lon ) {
		$url = "http://maps.google.com/maps/api/geocode/json?latlng={$lat},{$lon}&sensor=false";
		$result = wp_remote_get( $url );
		if ( is_wp_error( $result ) ) {
			return false; // Request fail
		}

		if ( wp_remote_retrieve_response_code( $result ) != 200 ) {
			return false; // Request fail
		}

		$json = json_decode( $result['body'] );

		if ( ! $json ) {
			return false;
		}

		$obj = false;
		if ( isset( $json->results ) && isset( $json->results[0] ) ) {
			$obj = $json->results[0];
		}

		if ( empty( $obj ) ) {
			return false;
		}

		return array(
			'title'    => $obj->formatted_address,
			'body'     => '',
			'icon'     => 'marker.png',
			'position' => array(
				$obj->geometry->location->lat,
				$obj->geometry->location->lng,
			),
		);
	}

	/**
	 * Algorithm adapted from http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
	 */
	public function find_bounding_coordinates( $lat, $lng, $distance_meters ) {
		$rad_dist = $distance_meters / 6371000; // 6371000 is the radius of earth in meters.
		$rad_lat = deg2rad( $lat );
		$rad_lng = deg2rad( $lng );

		$min_lat = $rad_lat - $rad_dist;
		$max_lat = $rad_lat + $rad_dist;

		if ( $min_lat > deg2rad( -90 ) && $max_lat < deg2rad( 90 ) ) {
			$delta_lng = asin( sin( $rad_dist ) / cos( $rad_lat ) );
			$min_lng = $rad_lng - $delta_lng;
			if ( $min_lng < deg2rad( -180 ) ) {
				$min_lng += 2 * E_PI;
			}
			$max_lng = $rad_lng + $delta_lng;
			if ( $max_lng > deg2rad( 180 ) ) {
				$max_lng -= 2 * E_PI;
			}
		} else {
			// a pole is within the distance
			$min_lat = Math.max( $min_lat, deg2rad( -90 ) );
			$max_lat = Math.min( $max_lat, deg2rad( 90 ) );
			$min_lng = deg2rad( -180 );
			$max_lng = deg2rad( 180 );
		}

		$min_lat = rad2deg( $min_lat );
		$min_lng = rad2deg( $min_lng );

		$max_lat = rad2deg( $max_lat );
		$max_lng = rad2deg( $max_lng );
		return array( $min_lat, $max_lat, $min_lng, $max_lng );
	}

	public function geocode_address( $address ) {
		$urladd = rawurlencode( $address );
		$url = "http://maps.google.com/maps/api/geocode/json?address={$urladd}&sensor=false";
		$result = wp_remote_get( $url );
		if ( is_wp_error( $result ) ) {
			return false; // Request fail
		}
		if ( wp_remote_retrieve_response_code( $result ) != 200 ) {
			return false; // Request fail
		}

		$json = json_decode( $result['body'] );

		if ( ! $json ) {
			return false;
		}
		return isset( $json->results[0] ) ? $json->results[0] : false;
	}
};


/**
 * Variation of standard array_unique that works on multidimensional arrays.
 *
 * @param array Array to be processed.
 * @return array Processed array.
 */
function agm_array_multi_unique( $array ) {
	if ( ! is_array( $array ) ) {
		return $array;
	}
	$ret = array();
	$hashes = array();

	foreach ( $array as $k => $v ) {
		$hash = md5( serialize( $v ) );
		if ( isset( $hashes[$hash] ) ) { continue; }
		$hashes[$hash] = $hash;
		$ret[] = $v;
	}

	return $ret;
}

/**
 * Positive values helper, used for getting positive values in
 * shortcode parsing.
 *
 * @param  string $val Optional. If specified the function checks if this value
 *                is a positive expression.
 * @return array|bool Either the list of positive expressions  -or-
 *                Boolean value if $val is a positive value
 */
function agm_positive_values() {
	static $Exp_True = array( 'yes', 'true', 'on', '1' );

	$has_val = false;
	$true_val = true;
	$false_val = false;

	switch ( func_num_args() ) {
		case 3: $false_val = func_get_arg( 2 );
		case 2: $true_val = func_get_arg( 1 );
		case 1: $val = func_get_arg( 0 ); $has_val = true;
	}

	if ( ! $has_val ) {
		return $Exp_True;
	} else {
		$val = (string) $val;
		if ( true === $val || in_array( $val, $Exp_True ) ) {
			return $true_val;
		} else {
			return $false_val;
		}
	}
}

/**
 * Negative values helper, used for getting negative values in
 * shortcode parsing.
 *
 * @param  string $val Optional. If specified the function checks if this value
 *                is a negative expression.
 * @return array|bool Either the list of negative expressions  -or-
 *                Boolean value if $val is a negative value
 */
function agm_negative_values() {
	static $Exp_False = array( 'no', 'false', 'off', '0', '' );

	$has_val = false;
	$true_val = true;
	$false_val = false;

	switch ( func_num_args() ) {
		case 3: $false_val = func_get_arg( 2 );
		case 2: $true_val = func_get_arg( 1 );
		case 1: $val = func_get_arg( 0 ); $has_val = true;
	}

	if ( ! $has_val ) {
		return $Exp_False;
	} else {
		$val = (string) $val;
		if ( false === $val || in_array( $val, $Exp_False ) ) {
			return $true_val;
		} else {
			return $false_val;
		}
	}
}