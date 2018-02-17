<?php

class WP_Hummingbird_Uptime_Page extends WP_Hummingbird_Admin_Page {

	private $current_report;

	/**
	 * WP_Hummingbird_Uptime_Page constructor.
	 *
	 * @param string $slug        The slug name to refer to this menu by (should be unique for this menu).
	 * @param string $page_title  The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string $menu_title  The text to be used for the menu.
	 * @param bool   $parent      Parent or child.
	 * @param bool   $render      Use a callback function.
	 */
	public function __construct( $slug, $page_title, $menu_title, $parent = false, $render = true ) {
		parent::__construct( $slug, $page_title, $menu_title, $parent, $render );

		$this->tabs = array(
			'main'     => __( 'Response Time', 'wphb' ),
			'downtime' => __( 'Downtime', 'wphb' ),
			'settings' => __( 'Settings', 'wphb' ),
		);

	}

	public function render_header() {

		$data_ranges = $this->get_data_ranges();
		$data_range_selected = isset( $_GET['data-range'] ) && array_key_exists( $_GET['data-range'], $this->get_data_ranges() ) ? $_GET['data-range'] : 'week';
		$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'main';
		?>

		<section id="header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			$module = wphb_get_module( 'uptime' );
			$is_active = $module->is_active(); ?>
			<div class="actions">
				<?php if ( wphb_is_member() && $is_active ) : ?>
					<label for="wphb-uptime-data-range" class="inline-label header-label hide-to-mobile"><?php esc_html_e( 'Reporting period', 'wphb' ); ?></label>
					<select name="wphb-uptime-data-range" class="uptime-data-range" id="wphb-uptime-data-range">
						<?php
						foreach ( $data_ranges as $range => $label ) :
							$data_url = add_query_arg(
								array(
									'view'       => $current_view,
									'data-range' => $range,
								),
								wphb_get_admin_menu_url( 'uptime' )
							);
							?>
							<option
									value="<?php echo esc_attr( $range ); ?>"
								<?php selected( $data_range_selected, $range ); ?>
									data-url="<?php echo esc_url( $data_url ); ?>">
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<a href="<?php echo esc_url( wphb_get_documentation_url( $this->slug, $this->get_current_tab() ) ); ?>" target="_blank" class="button button-ghost documentation-button">
					<?php esc_html_e( 'View Documentation', 'wphb' ); ?>
				</a>
			</div>
		</section><!-- end header -->
		<?php
	}

	public function register_meta_boxes() {
		// Check if Uptime is active in the server.
		if ( WP_Hummingbird_Module_Uptime::is_remotely_enabled() ) {
			wphb_uptime_enable_locally();
		} else {
			wphb_uptime_disable_locally();
		}

		$this->run_actions();

		/* @var WP_Hummingbird_Module_Uptime $module */
		$module = wphb_get_module( 'uptime' );
		$is_active = $module->is_active();
		$uptime_report = '';
		if ( $is_active ) {
			$uptime_report = wphb_uptime_get_last_report( $this->get_current_data_range() );
		}

		if ( ! wphb_is_member() ) {
			$this->add_meta_box(
				'uptime-no-membership',
				__( 'Uptime', 'wphb' ),
				array( $this, 'uptime_membership_metabox' ),
				null,
				null,
				'box-uptime-disabled',
				null
			);
		} elseif ( $is_active && is_wp_error( $uptime_report ) ) {
			$this->add_meta_box(
				'uptime',
				__( 'Uptime', 'wphb' ),
				array( $this, 'uptime_metabox' ),
				null,
				null,
				'main',
				null
			);
		} elseif ( ! $is_active && wphb_is_member() ) {
			$this->add_meta_box(
				'uptime-disabled',
				__( 'Get Started', 'wphb' ),
				array( $this, 'uptime_disabled_metabox' ),
				null,
				null,
				'box-uptime-disabled',
				array(
					'box_class' => 'dev-box content-box content-box-one-col-center',
				)
			);
		} else {
			$this->add_meta_box(
				'uptime-summary',
				null,
				array( $this, 'uptime_summary_metabox' ),
				null,
				null,
				'summary',
				array(
					'box_class' => 'dev-box content-box content-box-two-cols-image-left',
				)
			);
			$this->add_meta_box(
				'uptime-response-time',
				__( 'Response Time', 'wphb' ),
				array( $this, 'uptime_metabox' ),
				null,
				null,
				'main',
				null
			);
			$this->add_meta_box(
				'uptime-downtime',
				__( 'Downtime', 'wphb' ),
				array( $this, 'uptime_downtime_metabox' ),
				null,
				null,
				'downtime',
				null
			);
			$this->add_meta_box(
				'uptime-settings',
				__( 'Settings', 'wphb' ),
				array( $this, 'uptime_settings_metabox' ),
				null,
				null,
				'settings',
				null
			);
		}

	}

	private function run_actions() {
		$action = isset( $_GET['action'] ) ? $_GET['action'] : false;

		if ( 'enable' === $action ) {
			check_admin_referer( 'wphb-toggle-uptime' );

			if ( ! current_user_can( wphb_get_admin_capability() ) ) {
				return;
			}

			/* @var WP_Hummingbird_Module_Uptime $uptime_module */
			$uptime_module = wphb_get_module( 'uptime' );
			$result = $uptime_module->enable();

			if ( is_wp_error( $result ) ) {
				$redirect_to = add_query_arg( 'error', 'true', wphb_get_admin_menu_url( 'uptime' ) );
				$redirect_to = add_query_arg( array(
					'code' => $result->get_error_code(),
					'message' => urlencode( $result->get_error_message() ),
				), $redirect_to );
				wp_redirect( $redirect_to );
				exit;
			}

			$redirect_to = add_query_arg( 'run', 'true', wphb_get_admin_menu_url( 'uptime' ) );
			$redirect_to = add_query_arg( '_wpnonce', wp_create_nonce( 'wphb-run-uptime' ), $redirect_to );

			wp_redirect( $redirect_to );
			exit;
		}

		if ( 'disable' === $action ) {
			check_admin_referer( 'wphb-toggle-uptime' );

			if ( ! current_user_can( wphb_get_admin_capability() ) ) {
				return;
			}

			/* @var WP_Hummingbird_Module_Uptime $uptime_module */
			$uptime_module = wphb_get_module( 'uptime' );
			$uptime_module->disable();

			wp_redirect( wphb_get_admin_menu_url( 'uptime' ) );
		}

		if ( isset( $_GET['run'] ) ) {
			check_admin_referer( 'wphb-run-uptime' );

			if ( ! current_user_can( wphb_get_admin_capability() ) ) {
				return;
			}

			// Start the test
			/* @var WP_Hummingbird_Module_Uptime $uptime_module */
			$uptime_module = wphb_get_module( 'uptime' );
			$uptime_module->clear_cache();

			// Start the test
			wphb_uptime_get_last_report( 'week', true );

			wp_redirect( remove_query_arg( array( 'run', '_wpnonce' ) ) );
			exit;
		}
	}

	public function on_load() {
		if ( isset( $_GET['activate'] ) && current_user_can( wphb_get_admin_capability() ) ) {
			check_admin_referer( 'activate-uptime' );

			$options = wphb_get_settings();
			$options['uptime'] = true;
			wphb_update_settings( $options );

			wp_redirect( esc_url( wphb_get_admin_menu_url( 'uptime' ) ) );
			exit;
		}
	}

	public function enqueue_scripts( $hook ) {
		parent::enqueue_scripts( $hook );
		wp_enqueue_script( 'wphb-google-chart', "https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['corechart','timeline']}]}", array( 'jquery' ) );
	}

	public function uptime_disabled_metabox() {
		// Get current user name
		$user = wphb_get_current_user_info();
		$activate_url = add_query_arg( 'action', 'enable', wphb_get_admin_menu_url( 'uptime' ) );
		$activate_url = wp_nonce_url( $activate_url, 'wphb-toggle-uptime' );
		$this->view( 'uptime/disabled-meta-box', array(
			'user'         => $user,
			'activate_url' => $activate_url,
		));
	}

	private function get_data_ranges() {
		return array(
			'day'   => __( 'Last 1 day', 'wphb' ),
			'week'  => __( 'Last 7 days', 'wphb' ),
			'month' => __( 'Last 30 days', 'wphb' ),
		);
	}

	private function get_current_data_range() {
		return isset( $_GET['data-range'] ) && array_key_exists( $_GET['data-range'], $this->get_data_ranges() ) ? $_GET['data-range'] : 'week';
	}

	protected function render_inner_content() {
		$data_ranges = $this->get_data_ranges();
		$data_range = $this->get_current_data_range();

		$error = false;

		/** @var WP_Hummingbird_Module_Uptime $module */
		$module = wphb_get_module( 'uptime' );
		$is_active = $module->is_active();

		if ( $is_active ) {
			$uptime_stats = wphb_uptime_get_last_report( $data_range );
			if ( isset( $uptime_stats->code ) && $is_active ) {
				$error = $uptime_stats->message;
			} elseif ( false === $uptime_stats ) {
				$is_active = false;
			}
		}

		$retry_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( 'wphb-toggle-uptime' ),
				'action'   => 'enable',
			),
			wphb_get_admin_menu_url( 'uptime' )
		);

		$args = array(
			'error'     => $error,
			'retry_url' => $retry_url,
		);

		$this->view( $this->slug . '-page', $args );
	}

	public function get_current_report() {
		if ( ! is_null( $this->current_report ) ) {
			return $this->current_report;
		}

		$data_ranges = $this->get_data_ranges();
		$data_range = isset( $_GET['data-range'] ) && array_key_exists( $_GET['data-range'], $data_ranges ) ? $_GET['data-range'] : 'week';
		$this->current_report = wphb_uptime_get_last_report( $data_range );
		return $this->current_report;
	}

	public function uptime_metabox() {
		$error = '';

		$stats = wphb_uptime_get_last_report( $this->get_current_data_range() );
		if ( is_wp_error( $stats ) ) {
			$error = $stats->get_error_message();
			$error_type = 'error';
		} else {
			if ( isset( $_GET['error'] ) ) {
				$error = urldecode( $_GET['message'] );
				$error_type = 'error';
			}
		}
		if ( empty( $stats->chart_json ) ) {
			$stats->chart_json = $this->get_chart_data( 'dummy' );
		}

		$retry_url = add_query_arg( 'run', 'true' );
		$retry_url = wp_nonce_url( $retry_url, 'wphb-run-uptime' );

		$args = array(
			'uptime_stats' => $stats,
			'error' => $error,
			'retry_url' => $retry_url,
			'support_url' => wphb_support_link(),
			'downtime_chart_json' => $this->get_chart_data(),
		);

		if ( ! empty( $error_type ) ) {
			$args['error_type'] = $error_type;
		}

		$this->view( 'uptime/meta-box', $args );
	}

	/**
	 * Uptime summary meta box.
	 *
	 * @since 1.5.0
	 */
	public function uptime_summary_metabox() {
		if ( ! wphb_is_member() ) {
			return;
		}

		$current_data_range = $this->get_current_data_range();
		$stats = wphb_uptime_get_last_report( $current_data_range );

		$current_range = array(
			'day'   => __( '1 day', 'wphb' ),
			'week'  => __( '7 days', 'wphb' ),
			'month' => __( '30 days', 'wphb' ),
		);

		$this->view( 'uptime/summary-meta-box', array(
			'uptime_stats'    => $stats,
			'data_range_text' => $current_range[ $current_data_range ],
		));
	}

	/**
	 * Uptime downtime meta box.
	 *
	 * @since 1.7.2
	 */
	public function uptime_downtime_metabox() {
		if ( ! wphb_is_member() ) {
			return;
		}

		$stats = wphb_uptime_get_last_report( $this->get_current_data_range() );
		if ( is_wp_error( $stats ) ) {
			return;
		} else {
			if ( isset( $_GET['error'] ) ) {
				return;
			}
		}

		$this->view( 'uptime/downtime-meta-box', array(
			'uptime_stats'        => $stats,
			'downtime_chart_json' => $this->get_chart_data(),
		));
	}

	/**
	 * Uptime settings meta box.
	 *
	 * @since 1.7.1
	 */
	public function uptime_settings_metabox() {
		if ( ! wphb_is_member() ) {
			return;
		}

		$deactivate_url = wphb_uptime_get_last_report( $this->get_current_data_range() );
		$this->view( 'uptime/settings-meta-box', array(
			'deactivate_url' => $deactivate_url,
		));
	}
	/**
	 * Uptime no membership meta box.
	 */
	public function uptime_membership_metabox() {
		$this->view( 'uptime/no-membership-meta-box', array() );
	}
	/**
	 * Get dummy data for uptime charts when the user first enables Uptime and no data has been collected yet.
	 *
	 * @since 1.7.1
	 */
	public function get_chart_data( $type = 'downtime' ) {
		$data = array();
		$count = 0;
		$time = time();
		$time_increment = 0;
		$current_data_range = $this->get_current_data_range();
		switch ( $current_data_range ) {
			case 'day':
				$count = 24;
				$time_increment = 3600;
				break;
			case 'week':
				$count = 7;
				$time_increment = 86400;
				break;
			case 'month':
				$count = 28;
				$time_increment = 86400;
				break;
		}
		if ( 'dummy' === $type ) {
			$data[] = array( date( 'D M d Y H:i:s O', $time ), 1 );
			for ( $i = $count; $i > 0; $i-- ) {
				$time -= $time_increment;
				array_unshift( $data, array( date( 'D M d Y H:i:s O', $time ), null ) );
			}
		} else {
			// Do the data for the downtime graph until we get the API working.
			// JSON data looks like [ 'Downtime', Status, Tooltip, Start-period, End-period ].
			$stats = wphb_uptime_get_last_report( $current_data_range );
			$time -= $count * $time_increment;
			$end_time = time();
			$first = true;
			$event_arr = array_reverse( $stats->events );
			foreach ( $event_arr as $event ) {
				if ( ! empty( $event->down ) && ! empty( $event->up ) ) {
					if ( ! $first ) {
						$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $end_time ), date( 'D M d Y H:i:s O', $event->down ) );
						$data[] = array( 'Downtime', 'Down', 'Down for ' . $event->downtime, date( 'D M d Y H:i:s O', $event->down ), date( 'D M d Y H:i:s O', $event->up ) );
						$end_time = $event->up;
					} else {
						$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $time ), date( 'D M d Y H:i:s O', $event->down ) );
						$data[] = array( 'Downtime', 'Down', 'Down for ' . $event->downtime, date( 'D M d Y H:i:s O', $event->down ), date( 'D M d Y H:i:s O', $event->up ) );
						$end_time = $event->up;
						$first = false;
					}
				}
			}
			if ( $first ) {
				$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $time ), date( 'D M d Y H:i:s O', $end_time ) );
			} else {
				$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $end_time ), date( 'D M d Y H:i:s O', time() ) );
			}
		}

		return wp_json_encode( $data );
	}

}