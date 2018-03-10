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
		// Save performance scan settings.
		add_action( 'wp_ajax_wphb_pro_performance_save_reports_settings', array( $this, 'performance_save_scan_settings' ) );
		// Add the selected user.
		add_action( 'wp_ajax_wphb_pro_performance_add_recipient', array( $this, 'performance_add_recipient' ) );
		// Schedule advanced tools database cleanup
		add_action( 'wp_ajax_wphb_pro_advanced_db_schedule', array( $this, 'advanced_db_schedule' ) );
	}

	/**
	 * Check ajax referer and user caps.
	 *
	 * @since 1.8
	 */
	private function check_permissions() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}
	}

	/**
	 * Process scan settings.
	 *
	 * @since 1.4.5
	 */
	public function performance_save_scan_settings() {
		$this->check_permissions();

		// Get the data from ajax.
		parse_str( $_POST['data'], $data );

		$reports = WP_Hummingbird_Utils::get_module( 'performance' );
		$options = $reports->get_options();

		$options['reports']   = (bool) $data['email-notifications'];
		$options['frequency'] = intval( $data['email-frequency'] );
		$options['day']       = sanitize_text_field( $data['email-day'] );
		$options['time']      = sanitize_text_field( $data['email-time'] );

		// Randomize the minutes, so we don't spam the API.
		$email_time = explode( ':', $options['time'] );
		$email_time[1] = sprintf( '%02d', mt_rand( 0, 59 ) );
		$options['time'] = implode( ':', $email_time );

		$options['recipients'] = array();

		if ( isset( $data['email-recipients'] ) ) {
			foreach ( $data['email-recipients'] as $recipient ) {
				$recipient = json_decode( $recipient );
				if ( $recipient ) {
					$recipient = (array) $recipient;
					$options['recipients'][] = $recipient;
				}
			}
		}

		// Clear last scan time block.$reports->update_options( $options );
		$options['last_sent'] = '';

		$reports->update_options( $options );

		// Clean all cron.
		wp_clear_scheduled_hook( 'wphb_performance_scan' );

		if ( true === (bool) $options['reports'] ) {
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
		$this->check_permissions();

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
				'avatar'  => WP_Hummingbird_Utils::get_avatar_url( get_avatar( 0, 30 ) ),
				'name'    => $name,
				'user_id' => 0,
				'email'   => $email,
			);
		} else {
			$data = array(
				'avatar'  => WP_Hummingbird_Utils::get_avatar_url( get_avatar( $user->ID, 30 ) ),
				'name'    => ! empty( $name ) ? $name : WP_Hummingbird_Utils::get_display_name( $user->ID ),
				'user_id' => $user->ID,
				'email'   => $email,
			);
		}

		wp_send_json_success( $data );
	}

	/**
	 * Schedule database cleanup.
	 *
	 * @since 1.8
	 */
	public function advanced_db_schedule() {
		$this->check_permissions();

		WP_Hummingbird_Module_Cleanup_Cron::reschedule_cron();

		wp_send_json_success();
	}

}