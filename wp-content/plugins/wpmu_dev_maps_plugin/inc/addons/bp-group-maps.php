<?php
/*
Plugin Name: BuddyPress group maps
Description: Allows your BuddyPress groups to add location maps.
Example:     [agm_group_map group_id="1"], [agm_groups_map]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0.2
Requires:    BuddyPress
Author:      Ve Bailovity (Incsub)
*/

if ( defined( 'BP_PLUGIN_DIR' ) ) :

	class Agm_Bp_GroupMaps {

		const SLUG = 'map';

		private function __construct() {}

		public static function serve() {
			$me = new Agm_Bp_GroupMaps();
			$me->_add_hooks();
		}

		private function _add_hooks() {
			// Group creation
			add_action(
				'bp_after_group_details_creation_step',
				array( $this, 'bp_group_create_get_output' )
			);
			add_action(
				'groups_create_group_step_save_group-details',
				array( $this, 'bp_group_create_save_data' )
			);

			// Map settings for admins
			add_action(
				'bp_after_group_details_admin',
				array( $this, 'bp_group_edit_get_output' )
			);
			add_action(
				'groups_details_updated',
				array( $this, 'bp_group_edit_save_data' )
			);

			// Output
			add_action(
				'bp_init',
				array( $this, 'add_maps_tab' )
			);
			add_action(
				'bp_before_group_members_content',
				array( $this, 'bp_group_members_get_output' )
			);

			// Shortcode
			add_shortcode(
				'agm_group_map',
				array( $this, 'process_group_shortcode' )
			);
			add_shortcode(
				'agm_groups_map',
				array( $this, 'process_groups_shortcode' )
			);
		}

		private function _get_current_group() {
			global $bp;
			if ( ! $bp->is_single_item ) {
				return false;
			}

			$group = $bp->groups->current_group;
			if ( ! $group ) {
				return false;
			}

			return $group;
		}

		// Singular form
		public function process_group_shortcode( $args = array(), $content = false ) {
			$group_args = wp_parse_args(
				$args,
				array( 'group_id' => false )
			);
			if ( empty( $group_args['group_id'] ) ) {
				return $content;
			}

			$rpl = new AgmMarkerReplacer();
			$overrides = $rpl->arguments_to_overrides( $args );
			$tag = $this->create_group_maps_user_body( $group_args['group_id'], $overrides );

			return empty( $tag ) ? $content : $tag;
		}

		// Plural form
		public function process_groups_shortcode( $args = array(), $content = false ) {
			$groups_args = wp_parse_args(
				$args,
				array(
					'exclude' => false,
					'show_members' => false,
					'group_marker' => 'icon',
					'group_details' => 'address',
					'group_icon' => '',
				)
			);

			$exclude = array();
			if ( ! empty( $groups_args['exclude'] ) ) {
				$exclude = array_filter(
					array_map( 'intval', array_map( 'trim', explode( ',', $groups_args['exclude'] ) ) )
				);
			}

			$rpl = new AgmMarkerReplacer();
			$overrides = $rpl->arguments_to_overrides( $args );
			$raw = groups_get_groups(
				array(
					'user_id' => false,
					'exclude' => $exclude,
				)
			);
			if ( empty( $raw['groups'] ) ) {
				return $content;
			}

			$maps = array();
			foreach ( $raw['groups'] as $group ) {
				if ( empty( $group->id ) ) {
					continue;
				}
				$map = $this->_get_group_map(
					$group->id,
					array(
						'show_members' => $groups_args['show_members'], // show if set
						'group_marker' => $groups_args['group_marker'], // icon|avatar
						'group_details' => $groups_args['group_details'], // description|address|both|none
						'group_icon' => $groups_args['group_icon'], // when group_marker=='icon'
					)
				);
				if ( ! $map ) {
					continue;
				}
				$maps[] = $map;
			}
			if ( empty( $maps ) ) {
				return $content;
			}

			return $rpl->create_overlay_tag( $maps, $overrides );
		}

		public function add_maps_tab() {
			$group = $this->_get_current_group();
			if ( ! $group ) {
				return false;
			}

			$groups_link = bp_get_group_permalink( $group );

			// Show separate map page if set so in settings
			$data = groups_get_groupmeta( $group->id, 'agm-group_map' );
			if ( empty( $data['map_tab'] ) ) {
				return false;
			}

			if ( $this->_check_user_map_show_privileges( get_current_user_id(), $group->id ) ) {
				bp_core_new_subnav_item(
					array(
						'name' => __( 'Group Map', AGM_LANG ),
						'slug' => self::SLUG . '-show',
						'parent_url' => $groups_link,
						'parent_slug' => $group->slug,
						'screen_function' => array( $this, 'bind_bp_groups_user_page' ),
					)
				);
			}
		}

		public function bp_group_members_get_output() {
			$group = $this->_get_current_group();
			if ( ! $group ) {
				return false;
			}

			// Show separate map page if set so in settings
			//$data = groups_get_groupmeta( $group->id, 'agm-group_map' );
			//if ( empty( $data['map_tab'] ) ) {
			//	return false;
			//}

			// Show group map on members list page if set so in settings
			$data = groups_get_groupmeta( $group->id, 'agm-group_map' );
			if ( empty( $data['map_list'] ) ) {
				return false;
			}
			$this->show_group_maps_user_body();
		}


		public function bp_group_create_save_data() {
			global $bp;
			$this->_save_data( $bp->groups->new_group_id );
		}

		public function bp_group_edit_save_data( $group_id ) {
			$this->_save_data( $group_id );
		}

		private function _save_data( $group_id ) {
			global $current_user;
			if ( ! $group_id || ! groups_is_user_admin( $current_user->id, $group_id ) ) {
				return false;
			}

			if ( empty( $_POST['agm-group_map'] ) ) {
				return false;
			}
			groups_update_groupmeta(
				$group_id,
				'agm-group_map',
				stripslashes_deep( $_POST['agm-group_map'] )
			);
			update_option( '_agm-group_map-for-' . $group_id, false );
		}


		public function bp_group_create_get_output() {
			if ( ! bp_user_can_create_groups() ) {
				return false;
			}
			$this->_show_group_maps_admin_body();
		}

		public function bp_group_edit_get_output() {
			global $bp, $current_user;

			// Only Group Admins see the settings area
			if ( ! groups_is_user_admin( $current_user->id, $bp->groups->current_group->id ) ) {
				return false;
			}

			$this->_show_group_maps_admin_body( $bp->groups->current_group->id );
		}

		private function _show_group_maps_admin_body( $group_id = false ) {
			$data = groups_get_groupmeta( $group_id, 'agm-group_map' );
			$data = wp_parse_args(
				( is_array( $data ) ? $data : array() ),
				array(
					'address' => '',
					'show_map' => 'all',
					'map_tab' => 1,
					'map_list' => 1,
					'member_locations' => 1,
				)
			);
			?>
			<fieldset id="agm-group_map">
				<legend style="display:none"><?php _e( 'Group map', AGM_LANG ); ?></legend>
				<?php
				// - This group has location: address (please use full address)
				?>
				<label for="agm-group_map-address"><?php _e( 'This group has address:', AGM_LANG ); ?></label>
				<input type="text" name="agm-group_map[address]" id="agm-group_map-address" value="<?php echo esc_attr( $data['address'] ); ?>" />
				<?php
				// - Show group map to: a) all, b) members, c) moderators
				?>
				<label><?php _e( 'Show group map to:', AGM_LANG ); ?></label>
				<label for="agm-group_map-show_map-all">
					<input type="radio" id="agm-group_map-show_map-all" name="agm-group_map[show_map]" value="all" <?php checked( 'all', $data['show_map'] ); ?> />
					<?php _e( 'All', AGM_LANG ); ?>
				</label>
				<label for="agm-group_map-show_map-members">
					<input type="radio" id="agm-group_map-show_map-members" name="agm-group_map[show_map]" value="members" <?php checked( 'members', $data['show_map'] ); ?> />
					<?php _e( 'Members', AGM_LANG ); ?>
				</label>
				<label for="agm-group_map-show_map-moderators">
					<input type="radio" id="agm-group_map-show_map-moderators" name="agm-group_map[show_map]" value="moderators" <?php checked( 'moderators', $data['show_map'] ); ?> />
					<?php _e( 'Moderators', AGM_LANG ); ?>
				</label>
				<?php
				// - Show separate Group Map tab
				?>
				<label for="agm-group_map-map_tab">
					<input type="hidden" name="agm-group_map[map_tab]" value="" />
					<input type="checkbox" id="agm-group_map-map_tab" name="agm-group_map[map_tab]" value="1" <?php checked( 1, $data['map_tab'] ); ?> />
					<?php _e( 'Show separate Group Map tab', AGM_LANG ); ?>
				</label>
				<?php
				// - Show map on members list page
				?>
				<label for="agm-group_map-map_list">
					<input type="hidden" name="agm-group_map[map_list]" value="" />
					<input type="checkbox" id="agm-group_map-map_list" name="agm-group_map[map_list]" value="1" <?php checked( 1, $data['map_list'] ); ?> />
					<?php _e( 'Show map on members list page', AGM_LANG ); ?>
				</label>
			<?php
			// - Also show members locations (requires BuddyPress Profile maps add-on)
			if ( class_exists( 'Agm_Bp_Pm_UserPages' ) ) :
				?>
				<label for="agm-group_map-member_locations">
					<input type="hidden" name="agm-group_map[member_locations]" value="" />
					<input type="checkbox" id="agm-group_map-member_locations" name="agm-group_map[member_locations]" value="1" <?php checked( 1, $data['member_locations'] ); ?> />
					<?php _e( 'Also show members locations', AGM_LANG ); ?>
					<em><?php _e( '(requires BuddyPress Profile maps add-on)', AGM_LANG ); ?></em>
				</label>
			<?php
			endif;
			// end form
			?>
			</fieldset>
			<?php
		}

		public function bind_bp_groups_user_page() {
			add_action(
				'bp_template_content',
				array( $this, 'show_group_maps_user_body' )
			);

			bp_core_load_template(
				apply_filters( 'bp_core_template_plugin', 'groups/single/plugins' )
			);
		}

		public function show_group_maps_user_body( $group_id = false, $overrides = array() ) {
			echo '' . $this->create_group_maps_user_body( $group_id, $overrides );
		}

		public function create_group_maps_user_body( $group_id = false, $overrides = array() ) {
			if ( ! $group_id ) {
				global $bp;
				$group_id = $bp->groups->current_group->id;
			}
			if ( ! $group_id ) {
				return false;
			}

			$user_id = get_current_user_id();
			$allow = $this->_check_user_map_show_privileges( $user_id, $group_id );

			return $allow
				? $this->_get_group_map_tag( $group_id, $overrides )
				: false
			;
		}

		private function _check_user_map_show_privileges( $user_id, $group_id ) {
			$allow = false;
			$data = groups_get_groupmeta( $group_id, 'agm-group_map' );
			$show = ! empty( $data['show_map'] ) ? $data['show_map'] : 'all';

			if ( 'all' == $show || is_super_admin() ) {
				$allow = true;
			} else {
				if ( 'members' == $show ) {
					$allow = groups_is_user_member( $user_id, $group_id );
				} else if ( 'moderators' == $show ) {
					$allow = groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id );
				}
			}
			return $allow;
		}

		private function _get_group_map( $group_id, $data_overrides = array() ) {
			$group = groups_get_group( array( 'group_id' => $group_id ) );

			$data_overrides = wp_parse_args(
				$data_overrides,
				array( 'show_members' => true )
			);

			$data = groups_get_groupmeta( $group_id, 'agm-group_map' );
			$model = new AgmMapModel();
			$map_id = get_option( '_agm-group_map-for-' . $group_id, false );

			if ( empty( $data['address'] ) ) {
				$address = apply_filters( 'agm-group_map-default_group_address', false, $group_id, $data );
			} else {
				$address = $data['address'];
			}
			$map = false;

			if ( $map_id ) {
				$map = $model->get_map( $map_id );
			} else if ( ! $map_id && $address ) {
				$map_id = $model->autocreate_map( false, false, false, $address );
				if ( ! $map_id ) {
					return false;
				}
				update_option( '_agm-group_map-for-' . $group_id, $map_id );
				$map = $model->get_map( $map_id );
			} else {
				$map = $model->get_map_defaults();
				$map['defaults'] = $model->get_map_defaults();
				$map['id'] = $group_id . md5( time() . rand() );
				$map['show_map'] = 1;
				$map['show_markers'] = 0;
				$map['markers'] = array();
			}

			$has_group_marker = ( 1 == count( $map['markers'] ) && ! empty( $map['markers'][0] ) );

			switch ( @$data_overrides['group_marker'] ) {
				case 'avatar':
					if ( function_exists( 'bp_core_fetch_avatar' ) && $has_group_marker ) {
						// Set correct marker-icon for the group-marker
						$avatar = bp_core_fetch_avatar(
							array(
								'object' => 'group',
								'item_id' => $group_id,
								'width' => 32, // Hardcoding sizes, change this
								'height' => 32,
								'html' => false,
							)
						);
						if ( ! empty( $avatar ) ) {
							$map['markers'][0]['icon'] = $avatar;
						}
					}
					break;

				case 'icon':
					if ( strlen( @$data_overrides['group_icon'] ) && $has_group_marker ) {
						$map['markers'][0]['icon'] = $data_overrides['group_icon'];
					}
					break;
			}

			// Marker with index 0 is the group-marker.
			if ( $has_group_marker ) {
				// Also fix the group title
				$map['markers'][0]['title'] = $group->name;
				$details = isset($data_overrides['group_details'])
					? $data_overrides['group_details']
					: false
				;
				switch ( $details ) {
					case 'both':
					case 'all':
					case 'full':
						$map['markers'][0]['body'] = $group->description . '<div class="agm-address">' . $address . '</div>';
						break;

					case 'desc':
					case 'description':
					case 'info':
						$map['markers'][0]['body'] = $group->description;
						break;

					case 'no':
					case 'none':
					case 'empty':
					case 'off':
						$map['markers'][0]['body'] = '';
						break;

					case 'address':
					case 'default':
					default:
						$map['markers'][0]['body'] = $address;
						break;
				}
			}

			// Try to add markers for all the members.
			if ( ! empty( $data['member_locations'] ) && ! empty( $data_overrides['show_members'] ) && class_exists( 'Agm_Bp_Pm_UserPages' ) ) {
				$profile = new Agm_Bp_Pm_UserPages();
				$markers = $map['markers'];
				$members = groups_get_group_members(array(
					'group_id' => $group_id,
					'exclude_admins_mods' => false,
				));

				if ( $members && ! empty( $members['members'] ) ) {
					foreach ( $members['members'] as $member ) {
						$marker = $profile->member_to_marker( $member->ID );
						if ( $marker ) {
							$markers[] = $marker;
						}
					}
				}
				$map['markers'] = $markers;
			}

			if ( empty( $map['markers'] ) ) {
				return false;
			} else {
				return $map;
			}
		}

		private function _get_group_map_tag( $group_id, $overrides = array() ) {
			$map = $this->_get_group_map( $group_id );
			if ( ! $map ) {
				return false;
			}

			$codec = new AgmMarkerReplacer();
			return $codec->create_tag( $map, $overrides );
		}
	}

	Agm_Bp_GroupMaps::serve();

endif;