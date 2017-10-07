<?php
/*
Plugin Name: Fix and Repair
Description: Tools for repairing your Google Maps installation.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Far_FormRenderer {
	public function create_fixes_section() {
		_e( 'Use options in this section to repair your Google Maps installation. <em>You may want to back up your WordPress database first.</em>', AGM_LANG );
	}

	public function create_reset_options_box() {
		$please_wait = esc_attr( __( 'Please, wait...', AGM_LANG ) );
		$click_here = esc_attr( __( 'Click here to reset all options to their defaults on this blog', AGM_LANG ) );
		$success = esc_attr( __( 'Success', AGM_LANG ) );
		$failure = esc_attr( __( 'Failure', AGM_LANG ) );
		$stand_by = esc_attr( __( 'Your page will automatically reload in 3s', AGM_LANG ) );
		echo '<a href="#fix_reset_options" id="agm-reset_options">' . $click_here . '</a>';
		echo '&nbsp;<span id="agm-reset_options-result"></span>';
		echo '<div><small>' . __( 'All individual map options will be preserved.', AGM_LANG ) . '</small></div>';
		?>
		<script type="text/javascript">
		jQuery(function () {
			jQuery("#agm-reset_options").click(function () {
				jQuery("#agm-reset_options").text("<?php echo esc_js( $please_wait ); ?>");
				jQuery("#agm-reset_options-result").html( '' );
				jQuery.post(ajaxurl, {"action": "agm_reset_options"}, function (data) {
					var status = false;
					try {
						if (parseInt(data.status)) status = true;
					} catch (ignore) {}
					jQuery("#agm-reset_options-result").html(
						(status ? "<?php echo esc_js( $success ); ?>! <?php echo esc_js( $stand_by ); ?>" : "<?php echo esc_js( $failure ); ?>")
					);
					jQuery("#agm-reset_options").text("<?php echo esc_js( $click_here ); ?>");
					if (status) {
						setTimeout(function () {
							window.location = window.location;
						}, 3000);
					}
				});
				return false;
			});
		});
		</script>
		<?php
	}

	public function create_missing_tables_box() {
		$please_wait = esc_attr( __( 'Please, wait...', AGM_LANG ) );
		$click_here = esc_attr( __( 'Click here to fix missing tables on this blog', AGM_LANG ) );
		$success = esc_attr( __( 'Success', AGM_LANG ) );
		$failure = esc_attr( __( 'Failure', AGM_LANG ) );
		echo '<a href="#fix_missing_table" id="agm-fix_missing_table">' . $click_here . '</a>';
		echo '&nbsp;<span id="agm-fix_missing_table-result"></span>';
		echo '<div><small>' . __( 'Use this option if you suspect that your database table is missing', AGM_LANG ) . '</small></div>';
		?>
		<script type="text/javascript">
		jQuery(function () {
			jQuery("#agm-fix_missing_table").click(function () {
				jQuery("#agm-fix_missing_table").text("<?php echo esc_js( $please_wait ); ?>");
				jQuery("#agm-fix_missing_table-result").html( '' );
				jQuery.post(ajaxurl, {"action": "agm_fix_missing_table"}, function (data) {
					var status = false;
					try {
						if (parseInt(data.status)) status = true;
					} catch (e) {}
					jQuery("#agm-fix_missing_table-result").html(
						(status ? "<?php echo esc_js( $success ); ?>" : "<?php echo esc_js( $failure ); ?>")
					);
					jQuery("#agm-fix_missing_table").text("<?php echo esc_js( $click_here ); ?>");
				});
				return false;
			});
		});
		</script>
		<?php
	}

	public function create_empty_tables_box() {
		$are_you_sure = esc_attr( __( 'This will delete ALL maps on this blog. Are you definitely sure you want to do this?', AGM_LANG ) );
		$please_wait = esc_attr( __( 'Please, wait...', AGM_LANG ) );
		$click_here = esc_attr( __( 'Click here to delete ALL maps on this blog', AGM_LANG ) );
		$success = esc_attr( __( 'Success', AGM_LANG ) );
		$failure = esc_attr( __( 'Failure', AGM_LANG ) );
		echo '<a href="#clear_table" id="agm-clear_table">' . $click_here . '</a>';
		echo '&nbsp;<span id="agm-clear_table-result"></span>';
		echo '<div><small>' . __( 'Use this option to remove ALL tables from this blog <em>(<b>Warning:</b> this option is irreversible)</em>', AGM_LANG ) . '</small></div>';
		?>
		<script type="text/javascript">
		jQuery(function () {
			jQuery("#agm-clear_table").click(function () {
				if (!confirm("<?php echo esc_js( $are_you_sure ); ?>")) return false;
				jQuery("#agm-clear_table").text("<?php echo esc_js( $please_wait ); ?>");
				jQuery("#agm-clear_table-result").html( '' );
				jQuery.post(ajaxurl, {"action": "agm_clear_table"}, function (data) {
					var status = false;
					try {
						if (parseInt(data.status)) status = true;
					} catch (e) {}
					jQuery("#agm-clear_table-result").html(
						(status ? "<?php echo esc_js( $success ); ?>" : "<?php echo esc_js( $failure ); ?>")
					);
					jQuery("#agm-clear_table").text("<?php echo esc_js( $click_here ); ?>");
				});
				return false;
			});
		});
		</script>
		<?php
	}

	public function create_rebuild_maps_box() {
		$click_here = esc_attr( __( 'Click here to rebuild your BuddyPress members profile maps', AGM_LANG ) );
		$description = __( 'Rebuild all profile maps <em>(<b>Warning:</b> this will take a while)</em>', AGM_LANG );
		$drop_buffer = esc_attr( __( 'Please hold on while the old location buffers are purged', AGM_LANG ) );
		$processing_profiles = esc_attr( __( 'Processing profiles... ', AGM_LANG ) );
		$all_done = esc_attr( __( 'All done!', AGM_LANG ) );
		?>
		<a href="#rebuild_maps" id="agm-bp-rebuild_maps"><?php echo esc_js( $click_here ); ?></a>&nbsp;<span id="agm-bp-rebuild_maps-result"></span>
		<div><small><?php echo esc_js( $description ); ?></small></div>
		<script type="text/javascript">
		jQuery(function () {
			jQuery("#agm-bp-rebuild_maps").click(function () {
				var result = jQuery("#agm-bp-rebuild_maps-result");
				result.text("<?php echo esc_js( $drop_buffer ); ?>");
				jQuery.post(ajaxurl, {"action": "agm_bp_drop_buffered_locations"}, function (data) {
					var done = 0, total = 0, status = false;
					try { status = parseInt(data.status) } catch (e) { status = false; }
					try { total = parseInt(data.total) } catch (e) { total = false; }

					if (!status || !total) { // Bail out
						window.location.reload();
						return false;
					}

					function send_process_request () {
						if (done == total) { // We're done here
							result.text("<?php echo esc_js( $all_done ); ?>");
							return false;
						}
						result.text("<?php echo esc_js( $processing_profiles ); ?> " + (done+1) + "/" + total);
						jQuery.post(ajaxurl, {"action": "agm_bp_rebuild_profile_map"}, function () {
							done++;
							send_process_request();
						});
					}
					send_process_request();
				});
				return false;
			});
		});
		</script>
		<?php
	}

	public function create_rename_shortcode_box() {
		$please_wait = esc_attr( __( 'Please, wait...', AGM_LANG ) );
		$click_here = esc_attr( __( 'Click here to update the map shortcode on this blog', AGM_LANG ) );
		$success = esc_attr( __( 'Success', AGM_LANG ) );
		$failure = esc_attr( __( 'Failure', AGM_LANG ) );
		$old_tag = 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ? 'map' : 'agm_map';
		$new_tag = 'agm_map' == $old_tag ? 'map' : 'agm_map';

		global $wpdb;
		$old_tag = 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ? 'map' : 'agm_map';
		$total = (int) $wpdb->get_var( "SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_content LIKE '%[{$old_tag} %';" );

		if ( 0 == $total ) {
			_e( 'Nothing needs to be fixed', AGM_LANG );
			echo '<div><small>' . sprintf( __( 'In this blog there is no post or page that uses the old map shortcode <code>[%s]</code>', AGM_LANG ), $old_tag ) . '</small></div>';
			return false;
		}

		echo '<a href="#rename_shortcode" id="agm-rename_shortcode">' . $click_here . '</a>';
		echo '&nbsp;<span id="agm-rename_shortcode-result"></span>';
		echo '<div><small>' . sprintf( __( 'Use this option after you changed the map shortcode in the "Options" tab above. This will change all <code>[%s]</code> shortcodes to <code>[%s]</code> in <strong>%d</strong> pages/posts (including revisions and unpublished posts).', AGM_LANG ), $old_tag, $new_tag, $total ) . '</small></div>';
		?>
		<script type="text/javascript">
		jQuery(function () {
			jQuery("#agm-rename_shortcode").click(function () {
				jQuery("#agm-rename_shortcode").text("<?php echo esc_js( $please_wait ); ?>");
				jQuery("#agm-rename_shortcode-result").html( '' );
				jQuery.post(ajaxurl, {"action": "agm_rename_shortcode"}, function (data) {
					var status = false;
					try {
						if (parseInt(data.status)) status = true;
					} catch (e) {}
					jQuery("#agm-rename_shortcode-result").html(
						(status ? "<?php echo esc_js( $success ); ?>" : "<?php echo esc_js( $failure ); ?>")
					);
					jQuery("#agm-rename_shortcode").text("<?php echo esc_js( $click_here ); ?>");
				});
				return false;
			});
		});
		</script>
		<?php
	}
};



class Agm_FixAndRepair {

	public static function serve() {
		$me = new Agm_FixAndRepair();
		$me->_add_hooks();
	}

	public function register_settings() {
		if ( is_multisite() && ! current_user_can( 'manage_network_options' ) ) {
			return false; // On multisite, only allow this to Network Admins
		}
		$form = new Agm_Far_FormRenderer();

		add_settings_section(
			'agm_google_maps_repairs',
			__( 'Fixes and repairs', AGM_LANG ),
			array( $form, 'create_fixes_section' ),
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_reset_options',
			__( 'Reset all options to defaults', AGM_LANG ),
			array( $form, 'create_reset_options_box' ),
			'agm_google_maps_options_page', '
			agm_google_maps_repairs'
		);
		add_settings_field(
			'agm_google_maps_create_table',
			__( 'Fix missing table', AGM_LANG ),
			array( $form, 'create_missing_tables_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_repairs'
		);
		add_settings_field(
			'agm_google_maps_empty_table',
			__( 'Clear the table', AGM_LANG ),
			array( $form, 'create_empty_tables_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_repairs'
		);
		add_settings_field(
			'agm_google_maps_rename_shortcode',
			__( 'Rename map shortcode', AGM_LANG ),
			array( $form, 'create_rename_shortcode_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_repairs'
		);

		if ( class_exists( 'Agm_Bp_Pm_UserPages' ) ) {
			add_settings_field(
				'agm_google_maps-bp-rebuild_maps',
				__( 'Rebuild BuddyPress profile maps', AGM_LANG ),
				array( $form, 'create_rebuild_maps_box' ),
				'agm_google_maps_options_page',
				'agm_google_maps_repairs'
			);
		}
	}

	public function json_fix_missing_table() {
		$status = false;
		if ( current_user_can( 'manage_options' ) ) {
			$installer = new AgmPluginInstaller();
			if ( ! $installer->has_database_table() ) {
				$installer->create_database_table();
				$status = $installer->has_database_table();
			} else $status = true;
		}
		header( 'Content-type: application/json' );
		echo json_encode(
			array(
				'status' => $status ? 1 : 0,
			)
		);
		exit();
	}

	public function json_reset_options() {
		$status = false;
		if ( current_user_can( 'manage_options' ) ) {
			$installer = new AgmPluginInstaller();
			$installer->set_default_options();
			$status = true;
		}
		header( 'Content-type: application/json' );
		echo json_encode(
			array(
				'status' => $status ? 1 : 0,
			)
		);
		exit();
	}

	public function json_clear_table() {
		$status = false;
		if ( current_user_can( 'manage_options' ) ) {
			$model = new AgmMapModel();
			$status = $model->clear_table();
		}
		header( 'Content-type: application/json' );
		echo json_encode(
			array(
				'status' => $status ? 1 : 0,
			)
		);
		exit();
	}

	public function json_rename_shortcode() {
		global $wpdb;

		$old_tag = 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ? 'map' : 'agm_map';
		$new_tag = 'agm_map' == $old_tag ? 'map' : 'agm_map';

		header( 'Content-type: application/json' );
		$total = (int) $wpdb->query( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, '[{$old_tag} ', '[{$new_tag} ') WHERE post_content LIKE '%[{$old_tag} %';" );
		die(json_encode(
			array(
				'status' => 1,
				'total' => $total,
			)
		) );
	}

	public function json_bp_drop_buffered_locations() {
		if ( ! class_exists( 'Agm_Bp_Pm_UserPages' ) ) {
			die(-1);
		}
		global $wpdb;

		header( 'Content-type: application/json' );
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key='agm-bp-profile_maps-location'" );
		die(json_encode(
			array(
				'status' => 1,
				'total' => $total,
			)
		) );
	}

	public function json_bp_rebuild_profile_map() {
		if ( ! class_exists( 'Agm_Bp_Pm_UserPages' ) ) {
			die(-1);
		}

		global $wpdb;
		header( 'Content-type: application/json' );
		$sql = "SELECT DISTINCT ID FROM {$wpdb->users} WHERE ID NOT IN (SELECT DISTINCT ID FROM {$wpdb->users} as user, {$wpdb->usermeta} as meta WHERE user.ID=meta.user_id AND meta_key='agm-bp-profile_maps-location' ) LIMIT 1";
		$user_id = (int) $wpdb->get_var( $sql );

		$model = new AgmMapModel;
		$opts = apply_filters( 'agm_google_maps-options-bp_profile_maps', get_option( 'agm_google_maps' ) );
		$address = bp_get_profile_field_data(
			array(
				'field' => @$opts['bp_profile_maps-address_field'],
				'user_id' => $user_id,
			)
		);
		// Skip this guy
		if ( ! $address ) {
			die(json_encode(
				array(
					'user_id' => $user_id,
				)
			) );
		}

		$location = $model->_address_to_marker( $address );
		if ( $location ) {
			$location['body'] = Agm_Bp_Pm_UserPages::get_location_body( $user_id, $address );
		}
		update_user_meta( $user_id, 'agm-bp-profile_maps-location', $location );
		die(json_encode(
			array(
				'user_id' => $user_id,
			)
		) );
	}

	private function _add_hooks() {
		add_action( 'agm_google_maps-options-plugins_options', array($this, 'register_settings' ) );

		// Fixing options AJAX handlers
		add_action( 'wp_ajax_agm_fix_missing_table', array($this, 'json_fix_missing_table' ) );
		add_action( 'wp_ajax_agm_reset_options', array($this, 'json_reset_options' ) );
		add_action( 'wp_ajax_agm_clear_table', array($this, 'json_clear_table' ) );
		add_action( 'wp_ajax_agm_rename_shortcode', array($this, 'json_rename_shortcode' ) );

		add_action( 'wp_ajax_agm_bp_drop_buffered_locations', array($this, 'json_bp_drop_buffered_locations' ) );
		add_action( 'wp_ajax_agm_bp_rebuild_profile_map', array($this, 'json_bp_rebuild_profile_map' ) );
	}
}


if ( is_admin() ) {
	Agm_FixAndRepair::serve();
}