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
		add_action( 'wphb_admin_do_meta_boxes_wphb' , array( $this, 'register_dashboard_do_meta_boxes' ), 10 );

		// Advanced tools.
		add_action( 'wphb_admin_do_meta_boxes_wphb-advanced' , array( $this, 'register_advanced_tools_meta_boxes' ), 10 );

		// Performance reports.
		if ( is_multisite() && is_network_admin() || ! is_multisite() ) {
			add_action( 'wphb_admin_do_meta_boxes_wphb-performance', array(
				$this,
				'register_performance_reports_meta_boxes',
			), 10 );
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
			array( $this, 'dashboard_reports_metabox_footer' ),
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
			array( $this, 'performance_reports_metabox' ),
			null,
			array( $this, 'performance_reports_metabox_footer' ),
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
		$file = WPHB_DIR_PATH . "core/pro/admin/views/$name.php";
		$content = '';

		if ( is_file( $file ) ) {

			ob_start();

			if ( isset( $args['id'] ) ) {
				$args['orig_id'] = $args['id'];
				$args['id'] = str_replace( '/', '-', $args['id'] );
			}
			extract( $args );

			/* @noinspection PhpIncludeInspection */
			include( $file );

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
		$performance_module = WP_Hummingbird_Utils::get_module( 'performance' );
		$options = $performance_module->get_options();

		$frequency = '';
		$performance_is_active = false;
		if ( $options['reports'] ) {
			$performance_is_active = true;

			$frequency = $options['frequency'];
			switch ( $frequency ) {
				case 1:
					$frequency = __( 'Daily', 'wphb' );
					break;
				case 7:
					$frequency = __( 'Weekly', 'wphb' );
					break;
				case 30:
					$frequency = __( 'Monthly', 'wphb' );
					break;
			}
		}

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options = $adv_module->get_options();

		$db_cleanup = $options['db_cleanups'];
		$db_frequency = '';
		if ( $db_cleanup && isset( $options['db_frequency'] ) ) {
			switch ( $options['db_frequency'] ) {
				case 1:
					$db_frequency = __( 'Daily', 'wphb' );
					break;
				case 7:
					$db_frequency = __( 'Weekly', 'wphb' );
					break;
				case 30:
					$db_frequency = __( 'Monthly', 'wphb' );
					break;
			}
		}

		$args = compact( 'performance_is_active', 'frequency', 'db_cleanup', 'db_frequency' );
		$this->pro_view( 'dashboard/reports/meta-box', $args );
	}
	/**
	 * Reports meta box footer
	 *
	 * @since 1.7.0
	 */
	public function dashboard_reports_metabox_footer() {
		$this->pro_view( 'dashboard/reports/meta-box-footer', array(
			'url' => WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=reports',
		) );
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
		$options = $adv_module->get_options();

		$fields = WP_Hummingbird_Module_Advanced::get_db_fields();
		foreach ( $fields as $type => $field ) {
			$fields[ $type ]['checked'] = isset( $options['db_tables'][ $type ] ) ? $options['db_tables'][ $type ] : false;
		}

		$this->pro_view( 'advanced/db-settings-meta-box', array(
			'fields'    => $fields,
			'schedule'  => $options['db_cleanups'],
			'frequency' => $options['db_frequency'],
		) );
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
	 * Performance reports meta box footer.
	 */
	public function performance_reports_metabox_footer() {
		$this->pro_view( 'performance/reporting-meta-box-footer', array() );
	}

	/**
	 * Performance reports meta box.
	 */
	public function performance_reports_metabox() {
		/* @var WP_Hummingbird_Module_Performance $perf_module */
		$perf_module = WP_Hummingbird_Utils::get_module( 'performance' );
		$options = $perf_module->get_options();

		$week_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);

		$notification = false;
		$frequency = 7;
		$send_day = $week_days[ array_rand( $week_days, 1 ) ];
		$send_time = mt_rand( 0, 23 ) . ':00';
		$recipients = array();

		if ( WP_Hummingbird_Utils::is_member() ) {
			if ( isset( $options['reports'] ) ) {
				$notification = $options['reports'];
			}

			if ( isset( $options['frequency'] ) ) {
				$frequency = $options['frequency'];
			}

			if ( isset( $options['day'] ) ) {
				$send_day = $options['day'];
			}

			if ( isset( $options['time'] ) ) {
				// Remove the minutes from the hour to not confuse the user.
				$send_time = explode( ':', $options['time'] );
				$send_time[1] = '00';
				$send_time = implode( ':', $send_time );
			}

			if ( isset( $options['recipients'] ) ) {
				$recipients = $options['recipients'];
			}
		}

		$args = compact( 'notification', 'frequency', 'send_day', 'send_time', 'recipients' );
		$this->pro_view( 'performance/reporting-meta-box', $args );
	}

}