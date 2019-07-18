<?php
/**
 * Uptime module pages: WP_Hummingbird_Uptime_Page class
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Uptime_Page.
 */
class WP_Hummingbird_Uptime_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Current report.
	 *
	 * @var $current_report
	 */
	private $current_report;

	/**
	 * Uptime module.
	 *
	 * @var WP_Hummingbird_Module_Uptime
	 */
	private $uptime;

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			$this->add_meta_box(
				'uptime/no-membership',
				__( 'Upgrade', 'wphb' ),
				null,
				null,
				null,
				'box-uptime-disabled',
				array(
					'box_content_class' => 'sui-box sui-message',
				)
			);

			return;
		}

		$this->uptime = WP_Hummingbird_Utils::get_module( 'uptime' );

		if ( ! $this->uptime->is_active() ) {
			$this->add_meta_box(
				'uptime-disabled',
				__( 'Get Started', 'wphb' ),
				array( $this, 'uptime_disabled_metabox' ),
				null,
				null,
				'box-uptime-disabled',
				array(
					'box_content_class' => 'sui-box sui-message',
				)
			);

			return;
		}

		$this->current_report = $this->get_current_report();

		if ( $this->uptime->is_active() && is_wp_error( $this->current_report ) ) {
			$this->add_meta_box(
				'uptime',
				__( 'Uptime Monitoring', 'wphb' ),
				array( $this, 'uptime_metabox' ),
				null,
				null,
				'main'
			);

			return;
		}

		// Check if Uptime is active in the server.
		if ( WP_Hummingbird_Module_Uptime::is_remotely_enabled() ) {
			$this->uptime->enable_locally();
		} else {
			$this->uptime->disable_locally();
		}

		$this->add_meta_box(
			'uptime-summary',
			null,
			array( $this, 'uptime_summary_metabox' ),
			null,
			null,
			'summary',
			array(
				'box_content_class' => 'sui-box sui-summary',
			)
		);

		$this->add_meta_box(
			'uptime-response-time',
			__( 'Response Time', 'wphb' ),
			array( $this, 'uptime_metabox' ),
			null,
			null,
			'main'
		);

		$this->add_meta_box(
			'uptime-downtime',
			__( 'Downtime', 'wphb' ),
			array( $this, 'uptime_downtime_metabox' ),
			null,
			null,
			'downtime'
		);

		$this->add_meta_box(
			'uptime/settings',
			__( 'Settings', 'wphb' ),
			null,
			null,
			null,
			'settings'
		);
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		$this->tabs = array(
			'main'          => __( 'Response Time', 'wphb' ),
			'downtime'      => __( 'Downtime', 'wphb' ),
			'notifications' => __( 'Notifications', 'wphb' ),
			'reports'       => __( 'Reports', 'wphb' ),
			'settings'      => __( 'Settings', 'wphb' ),
		);

		if ( is_wp_error( $this->current_report ) || ! $this->current_report ) {
			unset( $this->tabs['downtime'] );
			unset( $this->tabs['notifications'] );
			unset( $this->tabs['reports'] );
			unset( $this->tabs['settings'] );
		}
	}

	/**
	 * Trigger an action before this screen is loaded.
	 */
	public function trigger_load_action() {
		parent::trigger_load_action();

		if ( isset( $_GET['activate'] ) ) { // Input var ok.
			check_admin_referer( 'activate-uptime' );
			WP_Hummingbird_Settings::update_setting( 'enabled', true, 'uptime' );
			wp_safe_redirect( esc_url( WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ) ) );
			exit;
		}

		if ( isset( $_GET['run'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-run-uptime' );

			// Start the test.
			$this->uptime->clear_cache();
			$this->uptime->get_last_report( 'week', true );

			wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce' ) ) );
			exit;
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false; // Input var ok.

		if ( 'enable' === $action ) {
			check_admin_referer( 'wphb-toggle-uptime' );
			$result = $this->uptime->enable();

			if ( is_wp_error( $result ) ) {
				$redirect_to = add_query_arg( 'error', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ) );
				$redirect_to = add_query_arg(
					array(
						'code'    => $result->get_error_code(),
						'message' => rawurlencode( $result->get_error_message() ),
					),
					$redirect_to
				);
				wp_safe_redirect( $redirect_to );
				exit;
			}
			$options = $this->uptime->get_options();

			// Add recipient for notifications if none exist.
			if ( ! isset( $options['notifications']['recipients'] ) || empty( $options['notifications']['recipients'] ) ) {
				$options['notifications']['recipients'][] = WP_Hummingbird_Utils::get_user_for_report();
			}

			// Add recipient for reporting if none exist.
			if ( ! isset( $options['reports']['recipients'] ) || empty( $options['reports']['recipients'] ) ) {
				$options['reports']['recipients'][] = WP_Hummingbird_Utils::get_user_for_report();
			}

			$this->uptime->update_options( $options );

			$redirect_to = add_query_arg( 'run', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ) );
			$redirect_to = add_query_arg( '_wpnonce', wp_create_nonce( 'wphb-run-uptime' ), $redirect_to );

			wp_safe_redirect( $redirect_to );
			exit;
		}

		if ( 'disable' === $action ) {
			check_admin_referer( 'wphb-toggle-uptime' );
			$this->uptime->disable();
			wp_safe_redirect( WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ) );
			exit;
		}
	}

	/**
	 * Hooks for caching pages.
	 */
	public function add_screen_hooks() {
		parent::add_screen_hooks();

		// Icons in the submenu.
		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	/**
	 * Render header.
	 */
	public function render_header() {
		$data_ranges         = $this->get_data_ranges();
		$data_range_selected = $this->get_current_data_range();
		?>

		<div class="sui-header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div class="sui-actions-right">
				<?php if ( WP_Hummingbird_Utils::is_member() && $this->uptime->is_active() ) : ?>
					<label for="wphb-uptime-data-range" class="inline-label header-label sui-hidden-xs sui-hidden-sm">
						<?php esc_html_e( 'Reporting period', 'wphb' ); ?>
					</label>
					<select name="wphb-uptime-data-range" class="uptime-data-range sui-select-sm" id="wphb-uptime-data-range">
						<?php
						foreach ( $data_ranges as $range => $label ) :
							$data_url = add_query_arg(
								array(
									'view'       => $this->get_current_tab(),
									'data-range' => $range,
								),
								WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' )
							);
							?>
							<option value="<?php echo esc_attr( $range ); ?>"
								<?php selected( $data_range_selected, $range ); ?>
									data-url="<?php echo esc_url( $data_url ); ?>">
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_doc_link() ) : ?>
					<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_documentation_url( $this->slug, $this->get_current_tab() ) ); ?>" target="_blank" class="sui-button sui-button-ghost">
						<i class="sui-icon-academy" aria-hidden="true"></i>
						<?php esc_html_e( 'View Documentation', 'wphb' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div><!-- end header -->
		<?php
	}

	/**
	 * We need to insert an extra label to the tabs sometimes
	 *
	 * @param string $tab Current tab.
	 */
	public function after_tab( $tab ) {
		if ( 'notifications' === $tab || 'reports' === $tab ) {
			$options = WP_Hummingbird_Settings::get_setting( $tab, 'uptime' );

			// Nothing to display if not enabled.
			if ( ! $options['enabled'] ) {
				return;
			}

			if ( ! empty( $options['recipients'] ) ) {
				echo '<i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>';
			} else {
				echo '<i class="sui-icon-warning-alert sui-warning" aria-hidden="true"></i>';
			}
		}
	}

	/**
	 * Uptime disabled meta box.
	 */
	public function uptime_disabled_metabox() {
		$activate_url = add_query_arg( 'action', 'enable', WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ) );
		$activate_url = wp_nonce_url( $activate_url, 'wphb-toggle-uptime' );
		$this->view(
			'uptime/disabled-meta-box',
			array(
				'user'         => WP_Hummingbird_Utils::get_current_user_info(),
				'activate_url' => $activate_url,
			)
		);
	}

	/**
	 * Get available data ranges.
	 *
	 * @return array
	 */
	private function get_data_ranges() {
		return array(
			'day'   => __( 'Last 1 day', 'wphb' ),
			'week'  => __( 'Last 7 days', 'wphb' ),
			'month' => __( 'Last 30 days', 'wphb' ),
		);
	}

	/**
	 * Get current data range.
	 *
	 * @return string
	 */
	private function get_current_data_range() {
		return isset( $_GET['data-range'] ) && array_key_exists( $_GET['data-range'], $this->get_data_ranges() ) ? $_GET['data-range'] : 'week'; // Input var ok.
	}

	/**
	 * Uptime first activated data.
	 *
	 * This was put in due to QA testers not being able to replicate the state when first activating uptime without
	 * a new domain.
	 *
	 * @since 1.8.0
	 */
	private function first_activated_state_data() {
		$data = new stdClass();

		$data->is_up           = 1;
		$data->down_reason     = 'UP';
		$data->uptime          = '2s';
		$data->downtime        = null;
		$data->availability    = null;
		$data->period_downtime = null;
		$data->response_time   = null;
		$data->up_since        = time();
		$data->down_since      = null;
		$data->outages         = 0;
		$data->events          = array();
		$data->chart_json      = null;

		return $data;
	}

	/**
	 * Render inner content.
	 */
	protected function render_inner_content() {
		$error = false;

		if ( ! WP_Hummingbird_Utils::is_member() ) {
			parent::render_inner_content();
			return;
		}

		if ( $this->uptime->is_active() && isset( $this->current_report->code ) ) {
			$error = $this->current_report->message;
		}

		$retry_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( 'wphb-toggle-uptime' ),
				'action'   => 'enable',
			),
			WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' )
		);

		$this->view(
			$this->slug . '-page',
			array(
				'error'     => $error,
				'retry_url' => $retry_url,
			)
		);
	}

	/**
	 * Get current report.
	 *
	 * @return bool|WP_Error
	 */
	public function get_current_report() {
		if ( ! is_null( $this->current_report ) ) {
			return $this->current_report;
		}

		$this->current_report = $this->uptime->get_last_report( $this->get_current_data_range() );

		return $this->current_report;
	}

	/**
	 * Uptime meta box.
	 */
	public function uptime_metabox() {
		$error = $downtime_chart_json = '';

		$stats = $this->current_report;
		if ( is_wp_error( $stats ) ) {
			$error      = $stats->get_error_message();
			$error_type = 'error';
		} elseif ( isset( $_GET['error'] ) ) { // Input var ok.
			$error      = urldecode( $_GET['message'] ); // Input var ok.
			$error_type = 'error';
		} else {
			// This is used for testing to create the state where no data exists when uptime is first activated.
			if ( defined( 'WPHB_UPTIME_REFRESH' ) ) {
				$stats = $this->first_activated_state_data();
			}

			if ( empty( $stats->chart_json ) ) {
				$stats->chart_json = $this->get_chart_data( 'dummy' );
			}

			$downtime_chart_json = $this->get_chart_data();
		}

		$retry_url = add_query_arg( 'run', 'true' );
		$retry_url = wp_nonce_url( $retry_url, 'wphb-run-uptime' );

		$args = array(
			'uptime_stats'        => $stats,
			'error'               => $error,
			'retry_url'           => $retry_url,
			'support_url'         => WP_Hummingbird_Utils::get_link( 'support' ),
			'downtime_chart_json' => $downtime_chart_json,
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
		$stats = $this->current_report;

		// This is used for testing to create the state where no data exists when uptime is first activated.
		if ( defined( 'WPHB_UPTIME_REFRESH' ) ) {
			$stats = $this->first_activated_state_data();
		}

		$current_range = array(
			'day'   => __( '1 day', 'wphb' ),
			'week'  => __( '7 days', 'wphb' ),
			'month' => __( '30 days', 'wphb' ),
		);

		$this->view(
			'uptime/summary-meta-box',
			array(
				'uptime_stats'    => $stats,
				'data_range_text' => $current_range[ $this->get_current_data_range() ],
			)
		);
	}

	/**
	 * Uptime downtime meta box.
	 *
	 * @since 1.7.2
	 */
	public function uptime_downtime_metabox() {
		$stats = $this->uptime->get_last_report( $this->get_current_data_range() );
		if ( is_wp_error( $stats ) || isset( $_GET['error'] ) ) { // Input var ok.
			return;
		}

		$this->view(
			'uptime/downtime-meta-box',
			array(
				'uptime_stats'        => $stats,
				'downtime_chart_json' => $this->get_chart_data(),
			)
		);
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
	 *
	 * @param string $type  Chart type.
	 *
	 * @return false|string
	 */
	public function get_chart_data( $type = 'downtime' ) {
		$data = array();
		$time = time();

		$current_data_range = $this->get_current_data_range();

		switch ( $current_data_range ) {
			case 'day':
				$count          = 24;
				$time_increment = 3600;
				break;
			case 'week':
			default:
				$count          = 7;
				$time_increment = 86400;
				break;
			case 'month':
				$count          = 28;
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
			$stats = $this->uptime->get_last_report( $current_data_range );

			// This is used for testing to create the state where no data exists when uptime is first activated.
			if ( defined( 'WPHB_UPTIME_REFRESH' ) ) {
				$stats = $this->first_activated_state_data();
			}
			$time     -= $count * $time_increment;
			$end_time  = time();
			$first     = true;
			$event_arr = ( isset( $stats->events ) && is_array( $stats->events ) ) ? array_reverse( $stats->events ) : array();

			// If no downtime events and uptime has not just been enabled for the first time return Website Available.
			if ( empty( $stats->events ) && ! empty( $stats->chart_json ) ) {
				$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', time() - $time_increment ), date( 'D M d Y H:i:s O', time() ) );
				return wp_json_encode( $data );
			}

			foreach ( $event_arr as $event ) {
				if ( ! empty( $event->down ) && ! empty( $event->up ) ) {
					if ( ! $first ) {
						$data[]   = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $end_time ), date( 'D M d Y H:i:s O', $event->down ) );
						$data[]   = array( 'Downtime', 'Down', 'Down for ' . $event->downtime, date( 'D M d Y H:i:s O', $event->down ), date( 'D M d Y H:i:s O', $event->up ) );
						$end_time = $event->up;
					} else {
						$data[]   = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $time ), date( 'D M d Y H:i:s O', $event->down ) );
						$data[]   = array( 'Downtime', 'Down', 'Down for ' . $event->downtime, date( 'D M d Y H:i:s O', $event->down ), date( 'D M d Y H:i:s O', $event->up ) );
						$end_time = $event->up;
						$first    = false;
					}
				}
			}

			if ( $first ) {
				$data[] = array( 'Downtime', 'Unknown', 'Unknown Availability', date( 'D M d Y H:i:s O', $time - 180 ), date( 'D M d Y H:i:s O', $end_time - 120 ) );
				$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $end_time - 120 ), date( 'D M d Y H:i:s O', $end_time ) );
			} else {
				$data[] = array( 'Downtime', 'Up', 'Website Available', date( 'D M d Y H:i:s O', $end_time ), date( 'D M d Y H:i:s O', time() ) );
			}
		}

		return wp_json_encode( $data );
	}

}
