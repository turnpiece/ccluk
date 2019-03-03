<?php

class AgmGdpr {

	private function __construct() {}
	private function __clone() {}

	public static function serve() {
		$me = new AgmGdpr;
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action( 'admin_init', array( $this, 'add_privacy_copy' ) );

		add_filter(
			'wp_privacy_personal_data_exporters',
			array( $this, 'register_data_exporter' )
		);
		add_filter(
			'wp_privacy_personal_data_erasers',
			array( $this, 'register_data_eraser' )
		);
	}

	/**
	 * Adds privacy body copy text
	 */
	public function add_privacy_copy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return false;
		}
		wp_add_privacy_policy_content(
			__( 'Google Maps Pro', AGM_LANG ),
			$this->get_policy_content()
		);
	}

	/**
	 * Registers data exporters for maps
	 *
	 * @param array $exporters Exporters this far.
	 *
	 * @return array
	 */
	public function register_data_exporter( $exporters ) {
		$exporters['agm_google_maps-autocreated'] = array(
			'exporter_friendly_name' => __( 'Google Maps Pro autocreated maps', AGM_LANG ),
			'callback' => array( $this, 'export_autocreated_maps' ),
		);
		$exporters['agm_google_maps-associated'] = array(
			'exporter_friendly_name' => __( 'Google Maps Pro associated maps', AGM_LANG ),
			'callback' => array( $this, 'export_associated_maps' ),
		);
		return $exporters;
	}

	/**
	 * Registers data erasers for maps
	 *
	 * @param array $erasers erasers this far.
	 *
	 * @return array
	 */
	public function register_data_eraser( $erasers ) {
		$erasers['agm_google_maps-autocreated'] = array(
			'eraser_friendly_name' => __( 'Google Maps Pro autocreated maps', AGM_LANG ),
			'callback' => array( $this, 'erase_autocreated_maps' ),
		);
		$erasers['agm_google_maps-associated'] = array(
			'eraser_friendly_name' => __( 'Google Maps Pro associated maps', AGM_LANG ),
			'callback' => array( $this, 'erase_associated_maps' ),
		);
		return $erasers;
	}

	/**
	 * Exports associated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function export_associated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_associated_maps( $user->ID );

		return $this->get_exported_maps_data(
			$maps,
			'associated',
			__( 'Associated maps', AGM_LANG )
		);
	}

	/**
	 * Erases associated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function erase_associated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_associated_maps( $user->ID );

		return $this->erase_maps_data( $maps );
	}

	/**
	 * Exports autocreated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function export_autocreated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_autocreated_maps( $user->ID );

		return $this->get_exported_maps_data(
			$maps,
			'autocreated',
			__( 'Autocreated maps', AGM_LANG )
		);
	}

	/**
	 * Erases autocreated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function erase_autocreated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_autocreated_maps( $user->ID );

		return $this->erase_maps_data( $maps );
	}

	/**
	 * Packs up maps data into exportable format
	 *
	 * @param array  $maps Maps to export.
	 * @param string $group Group ID.
	 * @param string $label Group label.
	 *
	 * @return array
	 */
	public function get_exported_maps_data( $maps, $group, $label ) {
		$result = array(
			'data' => array(),
			'done' => true,
		);
		if ( empty( $maps ) ) {
			return $result;
		}
		$exports = array();
		foreach ( $maps as $map ) {
			$exports[] = array(
				'item_id' => 'map-' . md5( serialize( $map ) ),
				'group_id' => 'agm_google_maps-' . $group,
				'group_label' => $label,
				'data' => array(
					array(
						'name' => __( 'Map', AGM_LANG ),
						'value' => wp_json_encode( $map ),
					),
				),
			);
		}

		$result['data'] = $exports;
		return $result;
	}

	/**
	 * Actually erases the maps data
	 *
	 * @param array $maps A list of map hashes to remove.
	 *
	 * @return array Response hash
	 */
	public function erase_maps_data( $maps ) {
		$map_ids = wp_list_pluck( $maps, 'id' );
		$response = array(
			'items_removed' => 0,
			'items_retained' => false,
			'messages' => array(),
			'done' => true,
		);

		if ( empty( $map_ids ) ) {
			return $response;
		}

		$model = new AgmMapModel;
		$status = $model->batch_delete_maps( $map_ids );

		$response['items_retained'] = ! $status;
		$response['items_removed'] = count( $map_ids );

		return $response;
	}

	/**
	 * Gets maps associated with posts written by author
	 *
	 * @param int $author_id Post author ID.
	 *
	 * @return array
	 */
	public function get_associated_maps( $author_id ) {
		$model = new AgmMapModel;
		return $model->get_custom_maps(array(
			'post_type' => 'any',
			'post_status' => 'any',
			'author' => $author_id,
			'limit' => 500,
		));
	}

	/**
	 * Gets auto-created maps table IDs by post author
	 *
	 * @param int $author_id Post author ID.
	 *
	 * @return array
	 */
	public function get_autocreated_map_ids( $author_id ) {
		$map_ids = array();

		$post_ids = get_posts(array(
			'post_type' => 'any',
			'post_status' => 'any',
			'author' => $author_id,
			'meta_key' => 'agm_map_created',
			'meta_compare' => 'EXISTS',
			'fields' => 'ids',
			'limit' => 500,
		));
		if ( ! is_array( $post_ids ) ) {
			return $map_ids;
		}

		foreach ( $post_ids as $pid ) {
			$map_id = (int) get_post_meta( (int) $pid, 'agm_map_created', true );
			if ( empty( $map_id ) ) {
				continue;
			}
			$map_ids[] = $map_id;
		}

		return $map_ids;
	}

	/**
	 * Gets actual auto-created maps by post author
	 *
	 * @param int $author_id Post author ID.
	 *
	 * @return array
	 */
	public function get_autocreated_maps( $author_id ) {
		$maps = array();
		$map_ids = $this->get_autocreated_map_ids( $author_id );
		if ( empty( $map_ids ) ) {
			return $maps;
		}

		$model = new AgmMapModel;

		return $model->get_maps_by_ids( $map_ids );
	}

	public function get_policy_content() {
		return '' .
			'<h3>' . __( 'Third parties', AGM_LANG ) . '</h3>' .
			'<p>' . __( 'This site will track your (anonymous) location data using your browser API and share it with Google Maps API service.', AGM_LANG ) . '</p>' .
			'<p>' . __( 'This site also includes third party resources from Google Maps API, which may be setting cookies on its own.', AGM_LANG ) . '</p>' .
			'<h3>' . __( 'Check-ins', AGM_LANG ) . '</h3>' .
			'<p>' . __( 'This site might be tracking your location (with your consent) in a form of user check-in, anonymous or per-user. This information can be exported and removed.', AGM_LANG ) . '</p>' .
			'<h3>' . __( 'For site members', AGM_LANG ) . '</h3>' .
			'<p>' . __( 'This site might be using your provided adress info (if any) to show it on a map and thus share it with the Google Maps API. This information can be removed.', AGM_LANG ) . '</p>' .
			'<h3>' . __( 'For content creators', AGM_LANG ) . '</h3>' .
			'<p>' . __( 'This site might be automatically creating maps according to provided location data, and/or associating them with the content you create, such as posts and BuddyPress activity updates. This content can be exported and removed.', AGM_LANG ) . '</p>' .
		'';
	}

}