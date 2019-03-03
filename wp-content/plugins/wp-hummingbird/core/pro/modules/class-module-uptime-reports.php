<?php
/**
 * Uptime reports and notifications module: WP_Hummingbird_Module_Uptime_Reports class
 *
 * @since 1.9.3
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Module_Uptime_Reports.
 * Only for premium users.
 *
 * @since 1.9.3
 */
class WP_Hummingbird_Module_Uptime_Reports extends WP_Hummingbird_Module {

	/**
	 * Initialize the module
	 *
	 * @since 1.9.3
	 */
	public function init() {
		// Default settings.
		add_filter( 'wp_hummingbird_default_options', array( $this, 'add_default_options' ) );
	}


	/**
	 * Execute the module actions.
	 *
	 * @since 1.9.3
	 */
	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * @since 1.9.3
	 */
	public function clear_cache() {}

	/**
	 * Add a set of default options to Hummingbird settings.
	 *
	 * @since  1.9.3
	 *
	 * @param  array $options  List of default Hummingbird settings.
	 *
	 * @return array
	 */
	public function add_default_options( $options ) {
		$options['uptime']['notifications']['threshold']  = 0;
		$options['uptime']['notifications']['recipients'] = array();

		$week_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);

		$options['uptime']['reports']['frequency']  = 7;
		$options['uptime']['reports']['day']        = $week_days[ array_rand( $week_days, 1 ) ];
		$options['uptime']['reports']['time']       = wp_rand( 0, 23 ) . ':00';
		$options['uptime']['reports']['recipients'] = array();

		return $options;
	}

	/**
	 * Get Reporting notice message.
	 *
	 * @since 1.9.3
	 *
	 * @param string $recipients_count  Recipient count.
	 *
	 * @return string
	 */
	public function get_uptime_reporting_message( $recipients_count ) {
		$reports_settings = WP_Hummingbird_Settings::get_setting( 'reports', 'uptime' );

		switch ( $reports_settings['frequency'] ) {
			case 1:
				$notice_message = sprintf(
					/* translators: %d: Number of recipients */
					__( 'Uptime reports are sending daily to %d recipients.', 'wphb' ),
					esc_html( $recipients_count )
				);
				$notice_frequency = __( 'daily', 'wphb' );
				if ( 1 === $recipients_count ) {
					$notice_message = __( 'Uptime reports are sending daily to 1 recipient.', 'wphb' );
				}
				break;
			case 7:
				$notice_message = sprintf(
					/* translators: %1$s: Weekday %2$d: Number of recipients */
					__( 'Uptime reports are sending weekly on %1$s to %2$d recipients.', 'wphb' ),
					esc_html( $reports_settings['day'] ),
					esc_html( $recipients_count )
				);
				$notice_frequency = __( 'weekly', 'wphb' );
				break;
			default:
				$notice_message = sprintf(
					/* translators: %1$s: Weekday %2$d: Number of recipients */
					__( 'Uptime reports are sending monthly on %1$s to %2$d recipients.', 'wphb' ),
					esc_html( $reports_settings['day'] ),
					esc_html( $recipients_count )
				);
				$notice_frequency = __( 'monthly', 'wphb' );
				break;
		}

		if ( 1 === $recipients_count ) {
			$notice_message = sprintf(
				/* translators: %s: Frequency of reports */
				__( 'Uptime reports are sending %s to 1 recipient.', 'wphb' ),
				esc_html( $notice_frequency )
			);
		}

		return $notice_message;
	}

	/**
	 * Get Notifications notice message.
	 *
	 * @since 1.9.3
	 *
	 * @param string $recipients_count  Recipient count.
	 *
	 * @return string
	 */
	public function get_uptime_notifications_message( $recipients_count ) {
		$reports_settings = WP_Hummingbird_Settings::get_setting( 'reports', 'uptime' );

		if ( isset( $reports_settings['threshold'] ) && 0 < $reports_settings['threshold'] ) {
			$notice_message = sprintf(
				/* translators: %d: Number of recipients */
				__( 'Email notifications are enabled and will be triggered if your website has been down for more than %d minutes.', 'wphb' ),
				absint( $recipients_count )
			);
		} else {
			$notice_message = __( 'Email notifications are enabled and will be triggered instantly once you site is down.', 'wphb' );
		}

		return $notice_message;
	}

}