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
		// Schedule advanced tools database cleanup.
		add_action( 'wp_ajax_wphb_pro_advanced_db_schedule', array( $this, 'advanced_db_schedule' ) );

		// Add recipient for Performance and Uptime reports.
		add_action( 'wp_ajax_wphb_pro_add_recipient', array( $this, 'add_recipient' ) );
		// Save Performance and Uptime reports settings.
		add_action( 'wp_ajax_wphb_pro_save_report_settings', array( $this, 'save_report_settings' ) );
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
	 * Schedule database cleanup.
	 *
	 * @since 1.8
	 */
	public function advanced_db_schedule() {
		$this->check_permissions();

		WP_Hummingbird_Module_Cleanup_Cron::reschedule_cron();

		wp_send_json_success();
	}

	/**
	 * Add recipient.
	 *
	 * @since 1.9.3 Unified for Performance and Uptime reports.
	 */
	public function add_recipient() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Current user cannot modify settings.', 'wphb' ),
				)
			);
		}

		// Validate email.
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please, insert a valid email.', 'wphb' ),
				)
			);
		}

		// Validate module.
		$available_modules = array( 'performance', 'uptime' );
		if ( ! isset( $_POST['module'] ) || ! in_array( wp_unslash( $_POST['module'] ), $available_modules, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module not defined.', 'wphb' ),
				)
			);
		}

		$module = sanitize_text_field( wp_unslash( $_POST['module'] ) );

		// Validate setting.
		$available_settings = array( 'reports', 'notifications' );
		if ( ! isset( $_POST['setting'] ) || ! in_array( wp_unslash( $_POST['setting'] ), $available_settings, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Setting not defined.', 'wphb' ),
				)
			);
		}

		$setting = sanitize_text_field( wp_unslash( $_POST['setting'] ) );

		// Validate recipient.
		$reports = WP_Hummingbird_Settings::get_setting( $setting, $module );
		foreach ( $reports['recipients'] as $recipient ) {
			if ( $email === $recipient['email'] ) {
				wp_send_json_error(
					array(
						'message' => __( 'Recipient already exists.', 'wphb' ),
					)
				);
			}
		}

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		wp_send_json_success(
			array(
				'name'  => $name,
				'email' => $email,
			)
		);
	}

	/**
	 * Save Performance and Uptime reports settings.
	 *
	 * @since 1.9.3 Unified for Performance and Uptime reports.
	 */
	public function save_report_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Current user cannot modify settings.', 'wphb' ),
				)
			);
		}

		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error parsing report data.', 'wphb' ),
				)
			);
		}

		// Validate module.
		$available_modules = array( 'performance', 'uptime' );
		if ( ! isset( $_POST['module'] ) || ! in_array( wp_unslash( $_POST['module'] ), $available_modules, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module not defined.', 'wphb' ),
				)
			);
		}

		$module = sanitize_text_field( wp_unslash( $_POST['module'] ) );

		// Get the data from ajax.
		parse_str( $_POST['data'], $data );

		$reports = WP_Hummingbird_Utils::get_module( $module );
		$options = $reports->get_options();

		$type = isset( $data['threshold'] ) ? 'notifications' : 'reports';

		$options[ $type ]['enabled'] = (bool) $data['scheduled-reports'];

		if ( 'reports' === $type ) {
			$options[ $type ]['frequency'] = intval( $data['report-frequency'] );
			if ( 30 === intval( $data['report-frequency'] ) ) {
				$options[ $type ]['day'] = sanitize_text_field( $data['report-day-month'] );
			} else {
				$options[ $type ]['day'] = sanitize_text_field( $data['report-day'] );
			}
			$options[ $type ]['time'] = sanitize_text_field( $data['report-time'] );

			// Randomize the minutes, so we don't spam the API.
			$email_time               = explode( ':', $options[ $type ]['time'] );
			$email_time[1]            = sprintf( '%02d', wp_rand( 0, 59 ) );
			$options[ $type ]['time'] = implode( ':', $email_time );

			// Clear last sent time.
			if ( isset( $options[ $type ]['last_sent'] ) ) {
				$options[ $type ]['last_sent'] = '';
			}

			// Reschedule.
			wp_clear_scheduled_hook( "wphb_{$module}_report" );

			if ( true === (bool) $options['reports']['enabled'] ) {
				// Reschedule. No need to clear again, as we've just cleared on top.
				$next_scan_time = WP_Hummingbird_Module_Reports::get_scheduled_time( $module, false );
				wp_schedule_single_event( $next_scan_time, "wphb_{$module}_report" );
			}
		} else {
			$options[ $type ]['threshold'] = intval( $data['threshold'] );
		}

		$options[ $type ]['recipients'] = array();

		if ( isset( $data['report-recipients'] ) ) {
			foreach ( $data['report-recipients'] as $recipient ) {
				$recipient = json_decode( $recipient );
				if ( $recipient ) {
					$recipient                        = (array) $recipient;
					$options[ $type ]['recipients'][] = $recipient;
				}
			}
		} else {
			$options[ $type ]['enabled']      = false;
			$options[ $type ]['recipients'][] = WP_Hummingbird_Utils::get_user_for_report();
		}

		$reports->update_options( $options );

		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

}