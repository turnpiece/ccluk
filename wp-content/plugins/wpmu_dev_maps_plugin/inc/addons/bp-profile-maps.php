<?php
/*
Plugin Name: BuddyPress profile maps
Description: Automatically creates a Map for BuddyPress profiles from a profile address field (if you don't already have such a field, you will have to create one). <br />It also adds a new shortcode to display a map with all BuddyPress members: <code>[agm_members_map]</code><br /><b>Requires BuddyPress with extended profiles enabled</b>.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0.1
Requires:    BuddyPress
Author:      Ve Bailovity (Incsub)
*/


if ( defined( 'BP_PLUGIN_DIR' ) ) :


	/*
	============================*\
	================================
	==                            ==
	==           SHARED           ==
	==                            ==
	================================
	\*============================*/

	class Agm_Bp_Pm_Shared extends AgmAddonBase {

		public static function serve( $me ) {
			// Called when updating profile via admin dashboard.
			add_action(
				'xprofile_updated_profile',
				array( $me, 'xprofile_updated' ),
				10, 3
			);
		}

		protected function _get_options( $key ) {
			$opts = apply_filters(
				'agm_google_maps-options-bp_profile_maps',
				get_option( 'agm_google_maps' ),
				$key
			);

			if ( isset( $opts[ 'bp_profile_maps-' . $key ] ) ) {
				return $opts[ 'bp_profile_maps-' . $key ];
			} else {
				return '';
			}
		}

		/**
		 * All Profile fields have been updated. Recreate the Map!
		 *
		 * This actually does not re-create the map but simply removes the old
		 * map from the users profile. A fresh map will be created the next time
		 * anyone visits the users profile.
		 */
		public function xprofile_updated( $user_id, $fields, $err ) {
			$address_field = $this->_get_options( 'address_field' );

			if ( in_array( $address_field, $fields ) ) {
				// Clear current user location.
				update_user_meta(
					$user_id,
					'agm-bp-profile_maps-location',
					''
				);
			}
		}
	}


	/*
	===========================*\
	===============================
	==                           ==
	==           ADMIN           ==
	==                           ==
	===============================
	\*===========================*/


	class Agm_Bp_Pm_AdminPages extends Agm_Bp_Pm_Shared {

		private $_db;
		private $_data;

		private function __construct() {
			global $wpdb;
			$this->_db = $wpdb;
		}

		public static function serve() {
			$me = new Agm_Bp_Pm_AdminPages();
			$me->_add_hooks();
			parent::serve( $me );
		}

		private function _add_hooks() {
			add_action(
				'agm_google_maps-options-plugins_options',
				array( $this, 'register_settings' )
			);

			// GDPR compliance.
			add_filter(
				'wp_privacy_personal_data_erasers',
				array( $this, 'register_data_eraser' )
			);
		}

		public function register_settings() {
			add_settings_section(
				'agm_google_bp_profile_fields',
				__( 'BuddyPress profile', AGM_LANG ),
				array( $this, 'create_dependencies_box' ),
				'agm_google_maps_options_page'
			);

			if ( ! defined( 'BP_VERSION' ) ) {
				return false;
			}
			if ( ! class_exists( 'BP_XProfile_Group' ) ) {
				return false;
			}

			add_settings_field(
				'agm_google_maps_bp_profile_address',
				__( 'Address profile field', AGM_LANG ),
				array( $this, 'create_address_field_mapping_box' ),
				'agm_google_maps_options_page',
				'agm_google_bp_profile_fields'
			);
			add_settings_field(
				'agm_google_maps_bp_profile_show_in_profile',
				__( 'Show map in user profile', AGM_LANG ),
				array( $this, 'create_show_in_profile_box' ),
				'agm_google_maps_options_page',
				'agm_google_bp_profile_fields'
			);
			add_settings_field(
				'agm_google_maps_bp_profile_show_in_members_list',
				__( 'Show map in members list', AGM_LANG ),
				array( $this, 'create_show_in_members_list_box' ),
				'agm_google_maps_options_page',
				'agm_google_bp_profile_fields'
			);
			add_settings_field(
				'agm_google_maps_bp_profile_avatars_as_markers',
				__( 'Use avatars as map markers', AGM_LANG ),
				array( $this, 'create_avatars_as_markers_box' ),
				'agm_google_maps_options_page',
				'agm_google_bp_profile_fields'
			);
			add_settings_field(
				'agm_google_maps_bp_profile_global_members',
				__( 'Global map with all members', AGM_LANG ),
				array( $this, 'create_global_members_box' ),
				'agm_google_maps_options_page',
				'agm_google_bp_profile_fields'
			);
		}

		public function register_data_eraser( $erasers ) {
			$erasers['agm_google_maps-profile_maps'] = array(
				'eraser_friendly_name' => __( 'Google Maps Pro profile maps', AGM_LANG ),
				'callback' => array( $this, 'erase_profile_maps_data' ),
			);
			return $erasers;
		}

		public function erase_profile_maps_data( $email, $page = 1 ) {
			$user = get_user_by( 'email', $email );
			$status = false;

			$meta = get_user_meta(
				$user->ID,
				'agm-bp-profile_maps-location',
				true
			);
			if ( ! empty( $meta ) ) {
				delete_user_meta(
					$user->ID,
					'agm-bp-profile_maps-location'
				);
				$status = true;
			}
			return array(
				'items_removed' => $status,
				'items_retained' => false,
				'messages' => array(),
				'done' => true,
			);
		}

		public function create_dependencies_box() {
			if ( ! defined( 'BP_VERSION' ) || ! class_exists( 'BP_XProfile_Group' ) ) :
				?>
				<p>
					<?php _e(
						'This Add-On required BuddyPress plugin with active ' .
						'<strong>Extended Profiles</strong> compoennt to work.',
						AGM_LANG
					); ?>
				</p>
				<?php
			endif;
		}

		public function create_address_field_mapping_box() {
			$xfields = array();
			$xgroups = BP_XProfile_Group::get( array( 'fetch_fields' => true ) );

			if ( ! empty( $xgroups ) ) {
				foreach ( $xgroups as $xgroup ) {
					$xfields[ $xgroup->name ] = $xgroup->fields;
				}
			}

			$address = $this->_get_options( 'address_field' );

			?>
			<label for="agm-bp_profile_maps-address_field">
				<?php _e( 'This profile field holds the address of my users:', AGM_LANG ); ?>
			</label>
			<select name="agm_google_maps[bp_profile_maps-address_field]" id="agm-bp_profile_maps-address_field">
				<option value=""><?php _e( 'Please, select a field', AGM_LANG ); ?></option>
				<?php foreach ( $xfields as $group => $fields ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
					<?php foreach ( $fields as $field ) : ?>
					<option value="<?php echo esc_attr( $field->id ); ?>"
						<?php selected( $field->id, $address ); ?>>
						<?php echo esc_html( $field->name ); ?>
					</option>
					<?php endforeach; ?>
				</optgroup>
				<?php endforeach; ?>
			</select>
			<?php
		}

		public function create_show_in_profile_box() {
			$show_in_profile = $this->_get_options( 'show_in_profile' );
			$values = array(
				''       => __( 'Do not show in profile', AGM_LANG ),
				'before' => __( 'Show map before profile fields', AGM_LANG ),
				'after'  => __( 'Show map after profile fields', AGM_LANG ),
			);

			foreach ( $values as $key => $val ) {
				?>
				<label for="agm-bp_profile_maps-show_in_profile-<?php echo esc_attr( $key ); ?>">
					<input type="radio"
						name="agm_google_maps[bp_profile_maps-show_in_profile]"
						value="<?php echo esc_attr( $key ); ?>"
						id="agm-bp_profile_maps-show_in_profile-<?php echo esc_attr( $key ); ?>"
						<?php checked( $key, $show_in_profile ); ?> />
					<?php echo esc_html( $val ); ?>
				</label>
				<br />
				<?php
			}
		}

		public function create_show_in_members_list_box() {
			$show_in_members_list = $this->_get_options( 'show_in_members_list' );
			$values = array(
				''       => __( 'Do not show in members list', AGM_LANG ),
				'before' => __( 'Show map before members list', AGM_LANG ),
				'after'  => __( 'Show map after members list', AGM_LANG ),
			);

			foreach ( $values as $key => $val ) {
				?>
				<label for="agm-bp_profile_maps-show_in_members_list-<?php echo esc_attr( $key ); ?>">
					<input type="radio"
						name="agm_google_maps[bp_profile_maps-show_in_members_list]"
						value="<?php echo esc_attr( $key ); ?>"
						id="agm-bp_profile_maps-show_in_members_list-<?php echo esc_attr( $key ); ?>"
						<?php checked( $key, $show_in_members_list ); ?> />
					<?php echo esc_html( $val ); ?>
				</label>
				<br />
				<?php
			}
		}

		public function create_avatars_as_markers_box() {
			$avatars_as_markers = $this->_get_options( 'avatars_as_markers' );
			$values = array(
				''  => __( 'No', AGM_LANG ),
				'1' => __( 'Yes', AGM_LANG ),
			);

			foreach ( $values as $key => $val ) {
				?>
				<label for="agm-bp_profile_maps-avatars_as_markers-<?php echo esc_attr( $key ); ?>">
					<input type="radio"
						name="agm_google_maps[bp_profile_maps-avatars_as_markers]"
						value="<?php echo esc_attr( $key ); ?>"
						id="agm-bp_profile_maps-avatars_as_markers-<?php echo esc_attr( $key ); ?>"
						<?php checked( $key, $avatars_as_markers ); ?> />
					<?php echo esc_html( $val ); ?>
				</label>
				<br />
				<?php
			}
		}

		public function create_global_members_box() {
			_e(
				'Use this shortcode on any page to display a map with all ' .
				'BuddyPress members:' .
				'<p><code>[agm_members_map]</code></p>' .
				'<p><code>[agm_all_profiles_map]</code> (<em>identical to ' .
				'agm_members_map for backwards compatibility</em>)</p>',
				AGM_LANG
			);
		}

	}


	/*
	===============================*\
	===================================
	==                               ==
	==           FRONT END           ==
	==                               ==
	===================================
	\*===============================*/


	class Agm_Bp_Pm_UserPages extends Agm_Bp_Pm_Shared {

		private $_model;

		public function __construct() {
			$this->_model = new AgmMapModel();
			$this->_add_hooks();
		}

		public static function serve() {
			$me = new Agm_Bp_Pm_UserPages();
			parent::serve( $me );
		}

		private function _add_hooks() {
			// Cosmetics.
			$positions = array( 'before', 'after' );

			$show_in_profile = $this->_get_options( 'show_in_profile' );
			if ( $show_in_profile && in_array( $show_in_profile, $positions ) ) {
				add_action(
					'bp_' . $show_in_profile . '_profile_loop_content',
					array( $this, 'show_current_user_on_map' )
				);
			}

			$show_in_members_list = $this->_get_options( 'show_in_members_list' );
			if ( $show_in_members_list && in_array( $show_in_members_list, $positions ) ) {
				add_action(
					'bp_' . $show_in_members_list . '_directory_members_list',
					array( $this, 'show_all_users_on_map' )
				);
			}

			add_shortcode(
				'agm_all_profiles_map',
				array( $this, 'handle_all_profiles_shortcode' )
			);
			add_shortcode(
				'agm_members_map',
				array( $this, 'handle_all_profiles_shortcode' )
			);
		}

		/**
		 * Handle the profiles shortcode.
		 */
		public function handle_all_profiles_shortcode( $atts, $content = '' ) {
			$limit = $atts && isset( $atts['limit'] ) ? (int) $atts['limit'] : false;
			return agm_bp_profiles_map( $atts, $limit );
		}

		/**
		 * Shows current users address on a map.
		 */
		public function show_current_user_on_map() {
			global $bp;

			if ( ! function_exists( 'xprofile_get_field_id_from_name' ) ) {
				$this->admin_note( __( 'BuddyPress XProfile not activated', AGM_LANG ) );
				return false;
			}

			$user_id = $bp->displayed_user->id;
			$address = $this->_get_user_address( $user_id );

			if ( ! $address ) {
				$this->admin_note( __( 'No address defined', AGM_LANG ) );
				return false;
			}

			$location = $this->_address_to_location( $user_id, $address );
			if ( ! $location ) {
				$this->admin_note( __( 'No map found for this address', AGM_LANG ) );
				return false;
			}

			echo '<div id="agm-bp-profile_map">' . $this->_create_map( array( $location ) ) . '</div>';
		}

		/**
		 * Shows all displayed users on a map.
		 */
		public function show_all_users_on_map() {
			$member_ids = array();
			$limit = apply_filters(
				'agm_google_maps-bp_profile_map-user_limit',
				AGM_BP_PROFILE_MAP_USER_LIMIT
			);
			$overrides = apply_filters(
				'agm_google_maps-bp_profile_map-all_users_overrides',
				array()
			);

			// Get member ids
			if ( bp_has_members( array( 'per_page' => $limit ) ) ) {
				while ( bp_members() ) {
					bp_the_member();
					$member_ids[] = bp_get_member_user_id();
				}
			}
			if ( function_exists( 'bp_rewind_members' ) ) { bp_rewind_members(); }

			echo '' . $this->show_users_on_map( $member_ids, $overrides );
		}

		/**
		 * Creates the actual map markup from an array of user IDs.
		 */
		public function show_users_on_map( $member_ids, $overrides = array() ) {
			// Get members' locations.
			$markers = array();
			foreach ( $member_ids as $member_id ) {
				$marker = $this->member_to_marker( $member_id );
				if ( $marker ) {
					$markers[] = $marker;
				}
			}
			if ( ! $markers ) { return false; }

			$overrides = apply_filters(
				'agm-shortcode-overrides',
				$overrides, $overrides
			);
			return '<div id="agm-bp-profiles_map">' .
				$this->_create_map( $markers, false, $overrides ) .
				'</div>';
		}

		public function member_to_marker( $member_id ) {
			$location = $this->_get_member_location( $member_id );

			if ( $location ) {
				if ( $this->_get_options( 'avatars_as_markers' ) ) {
					$location['icon'] = $this->_get_member_avatar( $member_id, false );
				}
			}

			return $location;
		}

		/**
		 * Manually fold conflicting markers
		 *
		 * In cases when we have multiple profile markers on
		 * the same location, but no map cluster add-on, we
		 * use this to fold conflicting marker descriptions into
		 * one single one.
		 *
		 * @param array $markers Markers to fold
		 *
		 * @return array Folded markers
		 */
		private function _fold_markers( $markers ) {
			if ( empty( $markers ) || ! is_array( $markers ) ) { return $markers; }
			$folded = array();
			$known = array();

			$fold_by_key = apply_filters(
				'agm_google_maps-bp_profile_map-fold_key',
				'title'
			);
			$default_marker = apply_filters(
				'agm_google_maps-bp_profile_map-fold_marker',
				'marker.png'
			);

			foreach ( $markers as $marker ) {
				$fold_key = ! empty( $marker[ $fold_by_key ] )
					? $marker[ $fold_by_key ]
					: false
				;
				// Deal with non-scalar fold keys by
				// converting them to string first
				if ( is_array( $fold_key ) ) { $fold_key = md5( serialize( $fold_key ) ); }

				if ( empty( $known[ $fold_key ] ) ) { $known[ $fold_key ] = array(); }
				$known[ $fold_key ][] = $marker;
			}
			foreach ( $known as $idx => $list ) {
				if ( empty( $list ) ) { continue; }

				// Nothing to fold here, carry on
				if ( 1 === count( $list ) ) {
					$folded[] = end( $list );
					continue;
				}

				$tmp = array();
				$description = '';

				foreach ( $list as $mrk ) {
					if ( empty( $tmp['position'] ) ) {
						// First folded item, copy and be done with it
						$tmp = array_merge( $tmp, $mrk );
					}
					// Handle the cases when profile images are used as marker icons
					$tmp['icon'] = $default_marker; // As this doesn't make sense anymore

					$description .= '' .
						( ! empty( $mrk['body'] ) ? $mrk['body'] : '') .
					'';
				}

				// Let's wrap the body once more so we float properly
				$tmp['body'] = '<div>' . $description . '</div>';

				$folded[] = $tmp;
			}

			return $folded;
		}

		/**
		 * Creates a map from a list of markers.
		 */
		private function _create_map( $markers, $id = false, $overrides = array() ) {
			if ( ! $markers ) {
				return false;
			}
			$id = $id ? $id : md5( time() . rand() );

			if ( ! class_exists( 'Agm_Mc_UserPages' ) ) {
				// We don't have the map cluster add-on,
				// so let's collapse the markers manually
				$markers = $this->_fold_markers( $markers );
			}

			$map = $this->_model->get_map_defaults();
			$map['defaults'] = $this->_model->get_map_defaults();
			$map['id'] = $id;
			$map['show_map'] = 1;
			$map['show_markers'] = 0;
			$map['markers'] = $markers;

			$codec = new AgmMarkerReplacer();
			return $codec->create_tag( $map, $overrides );
		}

		/**
		 * Maps a user by ID to a map marker.
		 */
		private function _get_member_location( $user_id ) {
			$address = $this->_get_user_address( $user_id );
			if ( ! $address ) {
				return false;
			}

			$location = $this->_address_to_location( $user_id, $address );
			return $location ? $location : false;
		}

		/**
		 * Maps users ID to actual address by querying
		 * the address xprofile field.
		 */
		private function _get_user_address( $user_id ) {
			if ( ! function_exists( 'bp_get_profile_field_data' ) ) {
				return false;
			}

			$address_field = $this->_get_options( 'address_field' );
			if ( ! $address_field ) { return false; }

			// http://premium.wpmudev.org/forums/topic/visibility-issue-with-google-maps-and-bp-profile-add-on
			if ( ! self::bp_current_user_can_see_field( $user_id, $address_field ) ) { return false; }

			$address = bp_get_profile_field_data(
				array(
					'field' => $address_field,
					'user_id' => $user_id,
				)
			);

			// Allows using multiple Xprofile fields for address construction.
			$address = apply_filters(
				'agm_google_maps-bp_profile_map-user_address',
				$address, $user_id
			);

			return $address ? $address : false;
		}

		/**
		 * Maps address to a map marker location.
		 * Caches data in user meta table to save time on future requests.
		 */
		private function _address_to_location( $user_id, $address ) {
			$location = get_user_meta(
				$user_id,
				'agm-bp-profile_maps-location',
				true
			);

			if ( $location ) {
				return $location;
			}

			// We still don't have location for this guy yet.
			// Lets geotag him!
			$location = $this->_model->_address_to_marker( $address );
			if ( $location ) {
				$location['body'] = $this->get_location_body( $user_id, $address );
			}

			update_user_meta(
				$user_id,
				'agm-bp-profile_maps-location',
				$location
			);

			return $location;
		}

		/**
		 * Gets member avatar.
		 */
		private function _get_member_avatar( $user_id, $as_html = true, $size = 'icon' ) {
			$width = $height = 32;
			switch ( $size ) {
				case 'medium':
					$width = $height = 48;
					break;

				case 'large':
					$width = $height = 64;
					break;
			}
			$avatar = bp_core_fetch_avatar(
				array(
					'object' => 'user',
					'item_id' => $user_id,
					'width' => $width,
					'height' => $height,
					'html' => $as_html,
				)
			);

			// Catch protocol-less avatars
			if ( preg_match( '~^//~', $avatar ) ) {
				$avatar = preg_replace( '~^//~', (is_ssl() ? 'https://' : 'http://'), $avatar );
			}

			return $avatar;
		}

		/**
		 * Creates map marker body.
		 * Used to cache map markers as user meta.
		 */
		public static function get_location_body( $user_id, $address ) {
			$name = bp_core_get_user_displayname( $user_id );
			$url = bp_core_get_user_domain( $user_id );

			ob_start();
			?>
			<div>
				<p class="agm-bp-profiles_map-user_link-container">
					<a class="agm-bp-profiles_map-user_link"
						href="<?php echo esc_url( $url ); ?>"
						title="<?php echo esc_attr( $name ); ?>">
						<?php echo esc_html( $name ); ?>
					</a>
				</p>
				<p class="agm-bp-profiles_map-user_address"><?php echo esc_html( $address ); ?></p>
			</div>
			<?php
			$code = ob_get_clean();

			return apply_filters(
				'agm_google_maps-bp_profile_map-location_markup',
				$code, $user_id, $address
			);
		}

		/**
		 * Check if the specified field can be displayed to the current visitor.
		 *
		 * @since  2.0.9.3
		 * @param int $user_id The user that is displayed (not the current user!)
		 * @param int $field_id The field which is displayed
		 * @return bool True if the current user is allowed to see the field value.
		 */
		static public function bp_current_user_can_see_field( $user_id, $field_id ) {
			$denied_levels = bp_xprofile_get_hidden_field_types_for_user(
				$user_id,
				get_current_user_id()
			);

			$denied_fields = bp_xprofile_get_fields_by_visibility_levels(
				$user_id,
				$denied_levels
			);

			return ! in_array( $field_id, $denied_fields );
		}

	};

	/* ----- Template tags ----- */

	/**
	 * Show all users on one large map.
	 */
	function agm_bp_profiles_map( $overrides = array(), $limit = false ) {
		global $wpdb;

		$handler = new Agm_Bp_Pm_UserPages();

		$limit = (int) $limit ? (int) $limit : AGM_BP_PROFILE_MAP_USER_LIMIT;
		$limit = apply_filters( 'agm_google_maps-bp_profile_map-user_limit', $limit );

		$sql = "
			SELECT ID
			FROM {$wpdb->users}
			LIMIT {$limit}
		";
		$user_ids = $wpdb->get_col( $sql );
		return $handler->show_users_on_map( $user_ids, $overrides );
	}

	// Set initial user limit to 1k. Overridable in wp-config.php
	if ( ! defined( 'AGM_BP_PROFILE_MAP_USER_LIMIT' ) ) {
		define( 'AGM_BP_PROFILE_MAP_USER_LIMIT', 1000 );
	}

	// Allow simple-case address overrides in a define. Overridable in wp-config.php
	if ( defined( 'AGM_BP_PROFILE_MAP_USE_ADDRESS_FIELDS' ) && AGM_BP_PROFILE_MAP_USE_ADDRESS_FIELDS ) {
		function agm_bp_profiles_map_address_override( $default_address, $user_id ) {
			$src = explode( ',', AGM_BP_PROFILE_MAP_USE_ADDRESS_FIELDS );
			if ( ! $src ) { return $default_address; }

			$denied = false;
			$data = array();
			foreach ( $src as $val ) {
				$val = trim( $val ); // Field-ID or Field-name

				if ( ! is_numeric( $val ) ) {
					$val = xprofile_get_field_id_from_name( $val );
				}

				// http://premium.wpmudev.org/forums/topic/visibility-issue-with-google-maps-and-bp-profile-add-on
				if ( ! Agm_Bp_Pm_UserPages::bp_current_user_can_see_field( $user_id, $val ) ) {
					$denied = true;
					break;
				}

				$data[] = bp_get_profile_field_data(
					array(
						'field' => $val,
						'user_id' => $user_id,
					)
				);
			}
			if ( ! $denied ) {
				$address = trim( join( ', ', array_filter( $data ) ) );
			}
			return $address ? $address : $default_address;
		}
		add_filter(
			'agm_google_maps-bp_profile_map-user_address',
			'agm_bp_profiles_map_address_override',
			10, 2
		);
	}

	if ( is_admin() ) {
		Agm_Bp_Pm_AdminPages::serve();
	} else {
		Agm_Bp_Pm_UserPages::serve();
	}

endif;