<?php
/**
 * Admin class for Pro functions.
 *
 * @package Hummingbird
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Pro_Admin
 */
class WP_Hummingbird_Pro_Admin {

	/**
	 * Init function.
	 */
	public function init() {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			return;
		}

		// Dashboard is a little special. There's a bug that prevents to add meta boxes in another way.
		add_action( 'wphb_admin_do_meta_boxes_wphb', array( $this, 'register_dashboard_do_meta_boxes' ), 10 );

		// Advanced tools.
		add_action( 'wphb_admin_do_meta_boxes_wphb-advanced', array( $this, 'register_advanced_tools_meta_boxes' ), 10 );

		// Advanced tools.
		add_action( 'wphb_admin_do_meta_boxes_wphb-uptime', array( $this, 'register_uptime_meta_boxes' ), 10 );

		// Performance reports.
		if ( is_multisite() && is_network_admin() || ! is_multisite() ) {
			add_action( 'wphb_admin_do_meta_boxes_wphb-performance', array( $this, 'register_performance_reports_meta_boxes' ), 10 );
		}
	}

	/**
	 * Register Dashboard Reporting meta box.
	 *
	 * @param WP_Hummingbird_Dashboard_Page $dashboard_page  Dashboard page.
	 */
	public function register_dashboard_do_meta_boxes( $dashboard_page ) {
		/* Reports */
		$dashboard_page->add_meta_box(
			'dashboard-reports',
			__( 'Reports', 'wphb' ),
			array( $this, 'dashboard_reports_metabox' ),
			null,
			null,
			'box-dashboard-right'
		);
	}

	/**
	 * Register Advanced Tools meta box.
	 *
	 * @since 1.9.1
	 *
	 * @param WP_Hummingbird_Dashboard_Page $advanced_tools  Advanced tools page.
	 */
	public function register_advanced_tools_meta_boxes( $advanced_tools ) {
		/* Advanced tools db settings */
		$advanced_tools->add_meta_box(
			'advanced/db-settings',
			__( 'Settings', 'wphb' ),
			array( $this, 'db_settings_metabox' ),
			null,
			array( $this, 'db_settings_metabox_footer' ),
			'db'
		);
	}

	/**
	 * Register Performance Reports meta box.
	 *
	 * @since 1.9.1  Moved from admin/performance.
	 *
	 * @param WP_Hummingbird_Performance_Report_Page $performance  Performance reports page.
	 */
	public function register_performance_reports_meta_boxes( $performance ) {
		/* Performance report settings */
		$performance->add_meta_box(
			'performance/reporting',
			__( 'Reports', 'wphb' ),
			array( $this, 'common_reports_metabox' ),
			null,
			array( $this, 'common_reports_metabox_footer' ),
			'reports'
		);
	}

	/**
	 * Register Uptime Notifications meta box.
	 *
	 * @since 1.9.3
	 *
	 * @param WP_Hummingbird_Uptime_Page $uptime  Uptime page.
	 */
	public function register_uptime_meta_boxes( $uptime ) {
		/* Uptime notifications settings (enabled) */
		$uptime->add_meta_box(
			'uptime/notifications',
			__( 'Notifications', 'wphb' ),
			array( $this, 'notifications_metabox' ),
			null,
			array( $this, 'common_reports_metabox_footer' ),
			'notifications'
		);

		$uptime->add_meta_box(
			'uptime/reporting',
			__( 'Reporting', 'wphb' ),
			array( $this, 'common_reports_metabox' ),
			null,
			array( $this, 'common_reports_metabox_footer' ),
			'reports'
		);
	}

	/**
	 * Load an admin PRO view
	 *
	 * @param string $name  Meta box name.
	 * @param array  $args  Arguments array.
	 * @param bool   $echo  Echo or return.
	 *
	 * @return string
	 */
	public function pro_view( $name, $args = array(), $echo = true ) {
		$file    = WPHB_DIR_PATH . "core/pro/admin/views/{$name}.php";
		$content = '';

		if ( is_file( $file ) ) {
			ob_start();

			if ( isset( $args['id'] ) ) {
				$args['orig_id'] = $args['id'];
				$args['id']      = str_replace( '/', '-', $args['id'] );
			}

			extract( $args );

			/* @noinspection PhpIncludeInspection */
			include $file;

			$content = ob_get_clean();
		}

		if ( ! $echo ) {
			return $content;
		}

		echo $content;
	}

	/**
	 * *************************
	 * DASHBOARD
	 *
	 * @since 1.4.5
	 ***************************/

	/**
	 * Reports meta box
	 */
	public function dashboard_reports_metabox() {
		/* @var WP_Hummingbird_Module_Performance $performance_module */
		$options = WP_Hummingbird_Utils::get_module( 'performance' )->get_options();

		$frequency = '';

		$performance_is_active = false;

		if ( $options['reports']['enabled'] ) {
			$performance_is_active = true;

			$frequency = $options['reports']['frequency'];
			switch ( $frequency ) {
				case 1:
					$frequency = __( 'Daily', 'wphb' );
					break;
				case 7:
				default:
					$frequency = __( 'Weekly', 'wphb' );
					break;
				case 30:
					$frequency = __( 'Monthly', 'wphb' );
					break;
			}
		}

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options    = $adv_module->get_options();

		$db_cleanup   = $options['db_cleanups'];
		$db_frequency = '';
		if ( $db_cleanup && isset( $options['db_frequency'] ) ) {
			switch ( $options['db_frequency'] ) {
				case 1:
					$db_frequency = __( 'Daily', 'wphb' );
					break;
				case 7:
				default:
					$db_frequency = __( 'Weekly', 'wphb' );
					break;
				case 30:
					$db_frequency = __( 'Monthly', 'wphb' );
					break;
			}
		}

		$options = WP_Hummingbird_Utils::get_module( 'uptime' )->get_options();
		$uptime  = $options['reports']['enabled'];

		$uptime_frequency = '';
		if ( $uptime && isset( $options['reports']['frequency'] ) ) {
			switch ( $options['reports']['frequency'] ) {
				case 1:
					$uptime_frequency = __( 'Daily', 'wphb' );
					break;
				case 7:
				default:
					$uptime_frequency = __( 'Weekly', 'wphb' );
					break;
				case 30:
					$uptime_frequency = __( 'Monthly', 'wphb' );
					break;
			}
		}

		$args = compact( 'performance_is_active', 'frequency', 'db_cleanup', 'db_frequency', 'uptime', 'uptime_frequency' );
		$this->pro_view( 'dashboard/reports/meta-box', $args );
	}

	/**
	 * *************************
	 * ADVANCED TOOLS
	 *
	 * @since 1.9.1
	 ***************************/

	/**
	 * Advanced tools db settings meta box.
	 */
	public function db_settings_metabox() {
		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options    = $adv_module->get_options();

		$fields = WP_Hummingbird_Module_Advanced::get_db_fields();
		foreach ( $fields as $type => $field ) {
			$fields[ $type ]['checked'] = isset( $options['db_tables'][ $type ] ) ? $options['db_tables'][ $type ] : false;
		}

		$this->pro_view(
			'advanced/db-settings-meta-box',
			array(
				'fields'    => $fields,
				'schedule'  => $options['db_cleanups'],
				'frequency' => $options['db_frequency'],
			)
		);
	}

	/**
	 * Performance reports meta box footer.
	 */
	public function db_settings_metabox_footer() {
		$this->pro_view( 'advanced/db-settings-meta-box-footer', array() );
	}

	/**
	 * *************************
	 * PERFORMANCE TEST
	 *
	 * @since 1.9.1
	 ***************************/

	/**
	 * Common reports meta box.
	 *
	 * @since 1.9.4  Made it common for performance and uptime reports.
	 */
	public function common_reports_metabox() {
		$module = 'performance';

		if ( isset( $_GET['page'] ) ) {
			$module = sanitize_key( wp_unslash( $_GET['page'] ) );
			$module = substr( $module, 5 );
		}

		$options = WP_Hummingbird_Settings::get_setting( 'reports', $module );

		if ( empty( $options ) || ! is_array( $options ) ) {
			// TODO: display generic page that module is not found.
			return;
		}

		$week_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);

		$notification   = false;
		$notice_class   = 'default';
		$notice_message = __( 'Reporting is currently inactive. Activate it and choose your schedule below.', 'wphb' );
		$frequency      = 7;
		$send_day       = $week_days[ array_rand( $week_days, 1 ) ];
		$send_time      = mt_rand( 0, 23 ) . ':00';
		$recipients     = array();

		if ( WP_Hummingbird_Utils::is_member() ) {
			if ( isset( $options['enabled'] ) ) {
				$notification = $options['enabled'];
			}

			if ( isset( $options['frequency'] ) ) {
				$frequency = $options['frequency'];
			}

			if ( isset( $options['day'] ) ) {
				$send_day = $options['day'];
			}

			if ( isset( $options['time'] ) ) {
				// Remove the minutes from the hour to not confuse the user.
				$send_time    = explode( ':', $options['time'] );
				$send_time[1] = '00';
				$send_time    = implode( ':', $send_time );
			}

			if ( isset( $options['recipients'] ) ) {
				$recipients = $options['recipients'];
			}
		}

		if ( $notification ) {
			$recipients_count = count( $recipients );

			if ( 0 !== $recipients_count ) {
				$notice_class   = 'success';
				$notice_message = $this->get_reporting_message( ucfirst( $module ), $frequency, $send_day, $recipients_count );
			} else {
				$notice_class   = 'warning';
				$notice_message = __( "Reporting is enabled but you haven't added any recipients yet.", 'wphb' );
			}
		}

		$this->pro_view(
			'common/reports-meta-box',
			array(
				'enabled'        => $notification,
				'frequency'      => $frequency,
				'module'         => $module,
				'notice_class'   => $notice_class,
				'notice_message' => $notice_message,
				'recipients'     => $recipients,
				'send_day'       => $send_day,
				'send_time'      => $send_time,
			)
		);
	}

	/**
	 * Common reports meta box footer.
	 *
	 * @since 1.9.4  Made it common for performance and uptime reports.
	 */
	public function common_reports_metabox_footer() {
		$this->pro_view( 'common/reports-meta-box-footer', array() );
	}

	/**
	 * *************************
	 * UPTIME NOTIFICATIONS
	 *
	 * @since 1.9.3
	 ***************************/

	/**
	 * Uptime notifications meta box.
	 *
	 * @since 1.9.3
	 */
	public function notifications_metabox() {
		$notifications_settings = WP_Hummingbird_Settings::get_setting( 'notifications', 'uptime' );
		$notice_class           = 'default';
		$notice_message         = __( "Email notifications are off which means you won't get notified if visitors can't access this website.", 'wphb' );

		if ( $notifications_settings['enabled'] ) {
			$recipients_count = count( $notifications_settings['recipients'] );
			if ( 0 !== $recipients_count ) {
				$notice_class   = 'success';
				if ( isset( $notifications_settings['threshold'] ) && 0 < $notifications_settings['threshold'] ) {
					$notice_message = sprintf(
						/* translators: %d: Number of recipients */
						__( 'Email notifications are enabled and will be triggered if your website has been down for more than %d minutes.', 'wphb' ),
						absint( $notifications_settings['threshold'] )
					);
				} else {
					$notice_message = __( 'Email notifications are enabled and will be triggered as soon as your website goes down.', 'wphb' );
				}
			} else {
				$notice_class   = 'warning';
				$notice_message = __( "Email notifications are enabled but you haven't added any recipients yet.", 'wphb' );
			}
		}

		$this->pro_view(
			'uptime/notifications-meta-box',
			array(
				'downtime_url'     => WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ) . '&view=downtime',
				'notice_class'     => $notice_class,
				'notice_message'   => $notice_message,
				'reports_settings' => $notifications_settings,
			)
		);
	}

	/**
	 * Get Reporting notice message.
	 *
	 * @since 1.9.3
	 * @since 1.9.4  Moved here from Uptime module. Added $frequency, $day and $module params.
	 *
	 * @param $day
	 * @param int    $frequency         Report frequency.
	 * @param string $module            Module name.
	 * @param string $recipients_count  Recipient count.
	 *
	 * @return string
	 */
	private function get_reporting_message( $module, $frequency, $day, $recipients_count ) {
		switch ( $frequency ) {
			case 1:
				$notice_message = sprintf(
				/* translators: %s: Module name, %d: Number of recipients */
					__( '%s reports are sending daily to %d recipients.', 'wphb' ),
					esc_html( $module ),
					esc_html( $recipients_count )
				);
				$notice_frequency = __( 'daily', 'wphb' );
				if ( 1 === $recipients_count ) {
					$notice_message = __( 'Uptime reports are sending daily to 1 recipient.', 'wphb' );
				}
				break;
			case 7:
				$notice_message = sprintf(
				/* translators: %1$s: Module name, %2$s: Weekday %3$d: Number of recipients */
					__( '%1$s reports are sending weekly on %2$s to %3$d recipients.', 'wphb' ),
					esc_html( $module ),
					esc_html( $day ),
					esc_html( $recipients_count )
				);
				$notice_frequency = __( 'weekly', 'wphb' );
				break;
			default:
				$notice_message = sprintf(
				/* translators: %1$s: Module name, %2$s: Weekday %3$d: Number of recipients */
					__( '%1$s reports are sending monthly on %2$s to %3$d recipients.', 'wphb' ),
					esc_html( $module ),
					esc_html( $day ),
					esc_html( $recipients_count )
				);
				$notice_frequency = __( 'monthly', 'wphb' );
				break;
		}

		if ( 1 === $recipients_count ) {
			$notice_message = sprintf(
			/* translators: %1$s: Module name, %2$s: Frequency of reports */
				__( '%1$s reports are sending %2$s to 1 recipient.', 'wphb' ),
				esc_html( $module ),
				esc_html( $notice_frequency )
			);
		}

		return $notice_message;
	}

}