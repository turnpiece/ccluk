<?php

/**
 * Class WP_Hummingbird_Pro_AJAX is used to parse ajax actions for the PRO version of the plugin.
 *
 * @since 1.5.0
 */
class WP_Hummingbird_Pro_AJAX {

	/**
	 * WP_Hummingbird_Pro_AJAX constructor.
	 */
	public function __construct() {
		// Save settings.
		add_action( 'wp_ajax_wphb_pro_performance_save_reports_settings', array( $this, 'performance_save_scan_settings' ) );
		// Add the selected user.
		add_action( 'wp_ajax_wphb_pro_performance_add_recipient', array( $this, 'performance_add_recipient' ) );
	}

	/**
	 * Process scan settings.
	 *
	 * @since 1.4.5
	 */
	public function performance_save_scan_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		// Get the data from ajax.
		parse_str( $_POST['data'], $data );
		$settings = wphb_get_settings();

		$settings['email-notifications'] = (bool) $data['email-notifications'];
		$settings['email-frequency'] = intval( $data['email-frequency'] );
		$settings['email-day'] = sanitize_text_field( $data['email-day'] );
		$settings['email-time'] = sanitize_text_field( $data['email-time'] );

		// Randomize the minutes, so we don't spam the API.
		$email_time = explode( ':', $settings['email-time'] );
		$email_time[1] = sprintf( '%02d', mt_rand( 0, 59 ) );
		$settings['email-time'] = implode( ':', $email_time );

		$settings['email-recipients'] = array();

		if ( isset( $data['email-recipients'] ) ) {
			foreach ( $data['email-recipients'] as $recipient ) {
				$recipient = json_decode( $recipient );
				if ( $recipient ) {
					$recipient = (array) $recipient;
					$settings['email-recipients'][] = $recipient;
				}
			}
		}

		wphb_update_settings( $settings );

		// Clean all cron.
		wp_clear_scheduled_hook( 'wphb_performance_scan' );
		// Clear last scan time block.
		wphb_update_setting( 'wphb-last-sent-report', '' );
		if ( true === (bool) $settings['email-notifications'] ) {
			// Reschedule.
			$next_scan_time = WP_Hummingbird_Module_Reporting_Cron::get_scheduled_scan_time();
			wp_schedule_single_event( $next_scan_time, 'wphb_performance_scan' );
		}

		wp_send_json_success();
	}

	/**
	 * Add recipient
	 *
	 * @since 1.4.5
	 */
	public function performance_add_recipient() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		$email = sanitize_email( $_POST['email'] );
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please, insert a valid email.', 'wphb' ),
			));
		}

		$name = sanitize_text_field( $_POST['name'] );

		$user = get_user_by( 'email', $email );
		if ( ! ( $user instanceof WP_User ) ) {
			$data = array(
				'avatar'  => wphb_get_avatar_url( get_avatar( 0, 30 ) ),
				'name'    => $name,
				'user_id' => 0,
				'email'   => $email,
			);
		} else {
			$data = array(
				'avatar'  => wphb_get_avatar_url( get_avatar( $user->ID, 30 ) ),
				'name'    => ! empty( $name ) ? $name : wphb_get_display_name( $user->ID ),
				'user_id' => $user->ID,
				'email'   => $email,
			);
		}

		wp_send_json_success( $data );
	}

}