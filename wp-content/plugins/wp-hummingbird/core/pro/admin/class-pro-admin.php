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
		// Dashboard is a little special. There's a bug that prevents to add meta boxes in another way.
		add_action( 'wphb_admin_do_meta_boxes_wphb' , array( $this, 'register_dashboard_do_meta_boxes' ), 10 );
	}

	/**
	 * Register Dashboard Reporting meta box
	 *
	 * @param WP_Hummingbird_Dashboard_Page $dashboard_page  Dashboard page.
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
				'box-dashboard-right'
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