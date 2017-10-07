<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Hummingbird_Pro_Admin {

	public function init() {
		add_filter( 'wphb_admin_page_tabs_wphb-performance', array( $this, 'performance_tabs' ) );
		add_filter( 'wphb_admin_page_tabs_wphb-performance', array( $this, 'performance_tabs' ) );

		add_action( 'wphb_load_admin_page_wphb-performance' , array( $this, 'register_performance_meta_boxes' ) );

		// Dashboard is a little special. There's a bug that prevents to add meta boxes in another way
		add_action( 'wphb_admin_do_meta_boxes_wphb' , array( $this, 'register_dashboard_do_meta_boxes' ), 10 );
	}

	/**
	 * Add Reporting to performance tabs
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function performance_tabs( $tabs ) {
		$tabs['reports'] = __( 'Reporting', 'wphb' );
		return $tabs;
	}

	/**
	 * Register Performance Reporting meta box
	 */
	public function register_performance_meta_boxes() {
		$hb = wp_hummingbird();
		/** @var WP_Hummingbird_Performance_Report_Page $performance_page */
		$performance_page = $hb->admin->get_admin_page( 'wphb-performance' );
		if ( $performance_page ) {
			if ( ! wphb_is_member() ) {
				$performance_page->add_meta_box( 'performance-summary', __( 'Reporting', 'wphb' ), array( $this, 'reporting_metabox' ), array( $this, 'reporting_metabox_header' ), array( $this, 'reporting_metabox_footer' ), 'reports', array( 'box_class' => 'dev-box content-box-one-col-center', 'box_content_class' => 'box-content no-padding' ) );
			} else {
				$performance_page->add_meta_box( 'performance-summary', __( 'Reporting', 'wphb' ), array( $this, 'reporting_metabox' ), array( $this, 'reporting_metabox_header' ), null, 'reports', array( 'box_class' => 'dev-box content-box-one-col-center', 'box_content_class' => 'box-content no-padding' ) );
			}
		}
	}

	/**
	 * Register Dashboard Reporting meta box
	 *
	 * @param WP_Hummingbird_Dashboard_Page $dashboard_page
	 */
	public function register_dashboard_do_meta_boxes( $dashboard_page ) {
		/* Reports */
		if ( wphb_is_member() ) {
			$dashboard_page->add_meta_box( 'dashboard-reports', __( 'Reports', 'wphb' ), array( $this, 'dashboard_reports_metabox' ), null, null, 'box-dashboard-left', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
		}
	}


	/*********************************
	/** PERFORMANCE                  *
	 *********************************/
	/**
	 * Reporting meta box.
	 *
	 * @since 1.4.5
	 */
	public function reporting_metabox() {
		$settings = wphb_get_settings();

		$week_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);

		$hour = mt_rand( 0, 23 );

		$notification = wphb_is_member() ? $settings['email-notifications'] : false;
		$frequency = wphb_is_member() ? $settings['email-frequency'] : 7;
		$send_day = wphb_is_member() ? $settings['email-day'] : $week_days[ array_rand( $week_days, 1 ) ];
		$send_time = $hour . ':00';
		if ( wphb_is_member() ) {
			// Remove the minutes from the hour to not confuse the user.
			$send_time = explode( ':', $settings['email-time'] );
			$send_time[1] = '00';
			$send_time = implode( ':', $send_time );
		}
		$recipients = wphb_is_member() ? $settings['email-recipients'] : array();

		$args = compact( 'notification', 'frequency', 'send_day', 'send_time', 'recipients' );
		$this->pro_view( 'performance/reporting-meta-box', $args );
	}

	/**
	 * Reporting meta box header.
	 *
	 * @since 1.5.0
	 */
	public function reporting_metabox_header() {
		$title = __( 'Reports', 'wphb' );
		$this->pro_view( 'performance/reporting-meta-box-header', array(
			'title' => $title,
		));
	}

	/**
	 * Reporting meta box footer.
	 *
	 * @since 1.5.0
	 */
	public function reporting_metabox_footer() {
		$this->pro_view( 'performance/reporting-meta-box-footer', array() );
	}

	/*********************************
	/** DASHBOARD                    *
	 *********************************/
	/**
	 * Reports meta box
	 *
	 * @since 1.4.5
	 */
	public function dashboard_reports_metabox() {
		$performance_module = wphb_get_module( 'performance' );
		$performance_is_active = $performance_module->is_active();

		$uptime_module = wphb_get_module( 'uptime' );
		$uptime_is_active = $uptime_module->is_active();

		$frequency = '';
		if ( $performance_is_active ) {
			$settings = wphb_get_settings();
			$frequency = $settings['email-frequency'];
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

		$this->pro_view( 'dashboard/reports/meta-box', compact( 'performance_is_active', 'uptime_is_active', 'frequency' ) );
	}

	/**
	 * Load an admin PRO view
	 */
	public function pro_view( $name, $args = array(), $echo = true ) {
		$file = wphb_plugin_dir() . "core/pro/admin/views/$name.php";
		$content = '';

		if ( is_file ( $file ) ) {

			ob_start();

			if ( class_exists( 'WDEV_Plugin_Ui' ) ) {
				WDEV_Plugin_Ui::output();
			}

			if ( isset( $args['id'] ) ) {
				$args['orig_id'] = $args['id'];
				$args['id'] = str_replace( '/', '-', $args['id'] );
			}
			extract( $args );

			include( $file );

			$content = ob_get_clean();
		}

		if ( ! $echo )
			return $content;

		echo $content;

	}
}