<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Hummingbird_Pro_Admin {

	public function init() {
		// Dashboard is a little special. There's a bug that prevents to add meta boxes in another way
		add_action( 'wphb_admin_do_meta_boxes_wphb' , array( $this, 'register_dashboard_do_meta_boxes' ), 10 );
	}

	/**
	 * Register Dashboard Reporting meta box
	 *
	 * @param WP_Hummingbird_Dashboard_Page $dashboard_page
	 */
	public function register_dashboard_do_meta_boxes( $dashboard_page ) {
		/* Reports */
		if ( WP_Hummingbird_Utils::is_member() ) {
			$dashboard_page->add_meta_box(
				'dashboard-reports',
				__( 'Reports', 'wphb' ),
				array( $this, 'dashboard_reports_metabox' ),
				null,
				array( $this, 'dashboard_reports_metabox_footer' ),
				'box-dashboard-right',
				array(
					'box_class' => 'dev-box content-box content-box-one-col-center',
				)
			);
		}
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
		/* @var WP_Hummingbird_Module_Performance $performance_module */
		$performance_module = WP_Hummingbird_Utils::get_module( 'performance' );
		$options = $performance_module->get_options();

		$uptime_module = WP_Hummingbird_Utils::get_module( 'uptime' );
		$uptime_is_active = $uptime_module->is_active();

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

		$this->pro_view( 'dashboard/reports/meta-box', compact( 'performance_is_active', 'uptime_is_active', 'frequency' ) );
	}
	/**
	 * Reports meta box footer
	 *
	 * @since 1.7.0
	 */
	public function dashboard_reports_metabox_footer() {
		$url = WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=reports';
		$this->pro_view( 'dashboard/reports/meta-box-footer', compact( 'url' ) );
	}

	/**
	 * Load an admin PRO view
	 *
	 * @param $name
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function pro_view( $name, $args = array(), $echo = true ) {
		$file = WPHB_DIR_PATH . "core/pro/admin/views/$name.php";
		$content = '';

		if ( is_file( $file ) ) {

			ob_start();

			if ( class_exists( 'WDEV_Plugin_Ui' ) ) {
				WDEV_Plugin_Ui::output();
			}

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

}