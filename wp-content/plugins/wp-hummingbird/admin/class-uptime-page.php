<?php

class WP_Hummingbird_Uptime_Page extends WP_Hummingbird_Admin_Page {

	private $current_report;

	public function render_header() {

		$data_ranges = $this->get_data_ranges();
		$data_range_selected = isset( $_GET['data-range'] ) && array_key_exists( $_GET['data-range'], $this->get_data_ranges() ) ? $_GET['data-range'] : 'week';
		?>

		<section id="header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			$module = wphb_get_module( 'uptime' );
			$is_active = $module->is_active();
			if ( wphb_is_member() && $is_active ) : ?>
				<div class="actions status">
					<div class="toggle-group toggle-group-with-buttons">
						<div class="tooltip-box">
							<span class="toggle" tooltip="<?php esc_attr_e( 'Disable Uptime', 'wphb' ); ?>">
								<input type="checkbox" id="wphb-disable-uptime" class="toggle-checkbox" name="wphb-disable-uptime" <?php checked( wphb_get_setting( 'uptime' ) ); ?>>
								<label for="wphb-disable-uptime" class="toggle-label"></label>
							</span>
						</div>
					</div>
					<span class="spinner right"></span>
				</div>
				<div class="actions">
					<span class="spinner left"></span>
					<label for="wphb-uptime-data-range" class="inline-label"><?php esc_html_e( 'Reporting period', 'wphb' ); ?></label>
					<select name="wphb-uptime-data-range" id="wphb-uptime-data-range">
						<?php foreach ( $data_ranges as $range => $label ) : ?>
							<option
									value="<?php echo esc_attr( $range ); ?>"
								<?php selected( $data_range_selected, $range ); ?>
									data-url="<?php echo esc_url( add_query_arg( 'data-range', $range, wphb_get_admin_menu_url( 'uptime' ) ) ); ?>">
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
		</section><!-- end header -->
		<?php
	}

	public function register_meta_boxes() {
		// Check if Uptime is active in the server.
		if ( wphb_is_uptime_remotely_enabled() ) {
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
			$this->add_meta_box( 'uptime-no-membership', __( 'Uptime', 'wphb' ), array( $this, 'uptime_membership_metabox' ), null, null, 'main', null );
		} elseif ( $is_active && is_wp_error( $uptime_report ) ) {
			$this->add_meta_box( 'uptime', __( 'Uptime', 'wphb' ), array( $this, 'uptime_metabox' ), null, null, 'main', null );
		} elseif ( ! $is_active && wphb_is_member() ) {
			$this->add_meta_box(
				'uptime-disabled',
				__( 'Uptime', 'wphb' ),
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
			$this->add_meta_box( 'uptime-response-time', __( 'Response Time', 'wphb' ), array( $this, 'uptime_metabox' ), array( $this, 'uptime_metabox_header' ), null, 'main', null );
			$this->add_meta_box( 'uptime-downtime', __( 'Downtime', 'wphb' ), array( $this, 'uptime_downtime_metabox' ), null, null, 'main', null );
		}

	}

	private function run_actions() {
		$action = isset( $_GET['action'] ) ? $_GET['action'] : false;

		if ( 'enable' === $action ) {
			check_admin_referer( 'wphb-toggle-uptime' );

			if ( ! current_user_can( wphb_get_admin_capability() ) ) {
				return;
			}

			$result = wphb_uptime_enable();
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

			wphb_uptime_disable();

			wp_redirect( wphb_get_admin_menu_url( 'uptime' ) );
		}

		if ( isset( $_GET['run'] ) ) {
			check_admin_referer( 'wphb-run-uptime' );

			if ( ! current_user_can( wphb_get_admin_capability() ) ) {
				return;
			}

			// Start the test
			wphb_uptime_clear_cache();

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
		wp_enqueue_script( 'wphb-google-chart', "https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['corechart']}]}", array( 'jquery' ) );
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
			'day'   => __( 'Last Day', 'wphb' ),
			'week'  => __( 'Last Week', 'wphb' ),
			'month' => __( 'Last Month', 'wphb' ),
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

		$retry_url = add_query_arg( 'run', 'true' );
		$retry_url = wp_nonce_url( $retry_url, 'wphb-run-uptime' );

		$args = array(
			'uptime_stats' => $stats,
			'error' => $error,
			'retry_url' => $retry_url,
			'support_url' => wphb_support_link(),
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

		$stats = wphb_uptime_get_last_report( $this->get_current_data_range() );

		$this->view( 'uptime/summary-meta-box', array(
			'uptime_stats' => $stats,
		));
	}

	/**
	 * Uptime header for meta box.
	 *
	 * @since 1.5.0
	 */
	public function uptime_metabox_header() {
		$this->view( 'uptime/meta-box-header', array(
			'title' => __( 'Response Time', 'wphb' ),
		));
	}

	/**
	 * Uptime downtime meta box.
	 *
	 * @since 1.5.0
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
			'uptime_stats' => $stats,
		));
	}

	public function uptime_membership_metabox() {
		$this->view( 'uptime/no-membership-meta-box', array() );
	}

}