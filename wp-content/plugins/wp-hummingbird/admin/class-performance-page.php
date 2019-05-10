<?php
/**
 * Performance page.
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Performance_Report_Page
 */
class WP_Hummingbird_Performance_Report_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Status of error. If true, than we have some error.
	 *
	 * @since 1.8.2  Changed to private.
	 *
	 * @var bool $has_error True if error present.
	 */
	private $has_error = false;

	/**
	 * Latest report.
	 *
	 * @since 1.8.2
	 *
	 * @var wp_error|array|object $report  Latest performance report.
	 */
	private $report;

	/**
	 * Report dismissed.
	 *
	 * @since 1.8.2
	 *
	 * @var bool $dismissed  Dismiss status.
	 */
	private $dismissed = false;

	/**
	 * Can run new performance test.
	 *
	 * @since 1.8.2
	 *
	 * @var bool $can_run_test
	 */
	private $can_run_test = true;

	/**
	 * Report type: desktop or mobile.
	 *
	 * @since 2.0.0
	 *
	 * @var string $type
	 */
	private $type = 'desktop';

	/**
	 * Render header.
	 */
	public function render_header() {
		$types = array(
			'desktop' => __( 'Desktop', 'wphb' ),
			'mobile'  => __( 'Mobile', 'wphb' ),
		);

		if ( isset( $_GET['report-dismissed'] ) ) { // Input var ok.
			$this->admin_notices->show( 'updated', __( 'You have successfully ignored this performance test.', 'wphb' ), 'success' );
		}
		?>
		<div class="sui-notice-top sui-notice-success sui-hidden" id="wphb-notice-performance-report-settings-updated">
			<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
		</div>
		<div class="sui-header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="sui-actions-right">
				<?php if ( WP_Hummingbird_Module_Performance::get_last_report() ) : ?>
					<label for="wphb-performance-report-type" class="inline-label header-label sui-hidden-xs sui-hidden-sm">
						<?php esc_html_e( 'Show results for', 'wphb' ); ?>
					</label>
					<select name="wphb-performance-report-type" class="sui-select-sm" id="wphb-performance-report-type">
						<?php foreach ( $types as $type => $label ) : ?>
							<?php
							$data_url = add_query_arg(
								array(
									'view'       => $this->get_current_tab(),
									'data-range' => $type,
								),
								WP_Hummingbird_Utils::get_admin_menu_url( 'performance' )
							);
							?>
							<option value="<?php echo esc_attr( $type ); ?>"
								<?php selected( $this->type, $type ); ?> data-url="<?php echo esc_url( $data_url ); ?>">
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
	 * Overwrite parent render_inner_content method.
	 *
	 * Render content for display.
	 *
	 * @since 1.8.2
	 */
	protected function render_inner_content() {
		$this->view(
			$this->slug . '-page',
			array(
				'report' => $this->report,
			)
		);
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		$this->tabs = array(
			'main'     => __( 'Score Metrics', 'wphb' ),
			'audits'   => __( 'Audits', 'wphb' ),
			'historic' => __( 'Historic Field Data', 'wphb' ),
			'reports'  => __( 'Reports', 'wphb' ),
			'settings' => __( 'Settings', 'wphb' ),
		);

		if ( is_multisite() && ! is_network_admin() ) {
			unset( $this->tabs['reports'] );
		}

		if ( $this->has_error ) {
			unset( $this->tabs['audits'] );
			unset( $this->tabs['historic'] );
		}

		if ( isset( $_GET['run'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-run-performance-test' );

			if ( WP_Hummingbird_Module_Performance::is_doing_report() ) {
				return;
			}

			// Start the test.
			WP_Hummingbird_Utils::get_module( 'performance' )->init_scan();

			wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce' ) ) );
			exit;
		}

		// Process form submit from expiry settings.
		if ( isset( $_POST['dismiss_report'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-dismiss-performance-report' );

			WP_Hummingbird_Module_Performance::dismiss_report( true );

			$redirect_to = add_query_arg(
				array(
					'report-dismissed' => true,
				)
			);
			wp_safe_redirect( $redirect_to );
		}
	}

	/**
	 * Init performance module, prior to page load.
	 *
	 * The logic behind this is following:
	 * - First check if there's a report in the db.
	 * - If not - check one on the API.
	 * - If no report on API, display the error that no report was found.
	 *
	 * @since 2.0.0
	 */
	private function init() {
		$selected_type = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
		if ( $selected_type ) {
			$this->type = $selected_type;
		}

		$is_doing_report = WP_Hummingbird_Utils::get_module( 'performance' )->is_doing_report();

		// Try to get the current report from the database.
		if ( ! $is_doing_report ) {
			// This needs to be here, because it's the first block that runs on page load.
			$this->report = WP_Hummingbird_Module_Performance::get_last_report();
		}

		// Is that a report with errors?
		if ( is_wp_error( $this->report ) || ( $this->report && is_null( $this->report->data->{$this->type}->metrics ) ) ) {
			$this->has_error = true;
		}

		$this->dismissed    = WP_Hummingbird_Module_Performance::report_dismissed( $this->report );
		$this->can_run_test = WP_Hummingbird_Module_Performance::can_run_test( $this->report );
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		$this->init();

		// Default to empty meta box if doing performance scan, or we will get php notices.
		if ( WP_Hummingbird_Utils::get_module( 'performance' )->is_doing_report() || ! $this->report ) {
			/**
			 * Empty meta box.
			 */
			$this->add_meta_box(
				'performance/empty',
				__( 'Performance test', 'wphb' ),
				null,
				null,
				null,
				'main',
				array(
					'box_content_class' => 'sui-box sui-message',
				)
			);

			return;
		}

		if ( $this->has_error ) {
			/**
			 * Error meta box.
			 */
			$this->add_meta_box(
				'performance/error',
				__( 'Score Metrics', 'wphb' ),
				array( $this, 'error_metabox' ),
				null,
				null,
				'main'
			);
		}

		/**
		 * Summary meta box.
		 */
		$this->add_meta_box(
			'performance-welcome',
			null,
			array( $this, 'summary_metabox' ),
			null,
			null,
			'summary',
			array(
				'box_class'         => 'sui-box sui-summary',
				'box_content_class' => false,
			)
		);

		if ( $this->report && ! $this->has_error ) {
			/**
			 * Score Metrics meta box.
			 */
			$this->add_meta_box(
				'performance/metrics',
				__( 'Score Metrics', 'wphb' ),
				array( $this, 'metrics_metabox' ),
				array( $this, 'reports_metabox_header' ),
				null,
				'main',
				array(
					'box_content_class' => false,
				)
			);

			/**
			 * Audits meta boxes.
			 */
			$this->add_meta_box(
				'performance/audits/opportunities',
				__( 'Opportunities', 'wphb' ),
				array( $this, 'opportunities_audit_metaboxes' ),
				array( $this, 'reports_metabox_header' ),
				null,
				'audits',
				array(
					'box_content_class' => false,
				)
			);

			$this->add_meta_box(
				'performance/audits/diagnostics',
				__( 'Diagnostics', 'wphb' ),
				array( $this, 'diagnostics_audit_metaboxes' ),
				null,
				null,
				'audits',
				array(
					'box_content_class' => false,
				)
			);

			$this->add_meta_box(
				'performance/audits/passed',
				__( 'Passed Audits', 'wphb' ),
				array( $this, 'passed_audit_metaboxes' ),
				null,
				null,
				'audits',
				array(
					'box_content_class' => false,
				)
			);

			/**
			 * Historic Field Data meta box.
			 */
			$this->add_meta_box(
				'performance/field-data',
				__( 'Historic Field Data', 'wphb' ),
				array( $this, 'historic_field_data_metabox' ),
				array( $this, 'reports_metabox_header' ),
				null,
				'historic'
			);

			if ( is_multisite() && is_network_admin() || ! is_multisite() ) {
				$this->add_meta_box(
					'performance/reporting',
					__( 'Reports', 'wphb' ),
					null,
					null,
					null,
					'reports',
					array(
						'box_content_class' => 'sui-box-body sui-upsell-items',
					)
				);
			}
		}

		$this->add_meta_box(
			'settings-summary',
			__( 'Settings', 'wphb' ),
			array( $this, 'settings_metabox' ),
			null,
			array( $this, 'settings_metabox_footer' ),
			'settings'
		);
	}

	/**
	 * Performance metrics meta box.
	 */
	public function metrics_metabox() {
		$retry_url = wp_nonce_url(
			add_query_arg( 'run', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) ),
			'wphb-run-performance-test'
		);

		$this->view(
			'performance/metrics-meta-box',
			array(
				'last_test'        => $this->report->data->{$this->type},
				'report_dismissed' => $this->dismissed,
				'can_run_test'     => $this->can_run_test,
				'retry_url'        => $retry_url,
				'type'             => $this->type,
			)
		);
	}

	/**
	 * Performance welcome meta box.
	 */
	public function summary_metabox() {
		$last_report = $this->report;

		$opportunities = $diagnostics = $passed_audits = '-';

		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$last_report = $last_report->data;

			if ( ! is_null( $last_report->{$this->type}->audits->opportunities ) ) {
				$opportunities = count( get_object_vars( $last_report->{$this->type}->audits->opportunities ) );
			}

			if ( ! is_null( $last_report->{$this->type}->audits->diagnostics ) ) {
				$diagnostics = count( get_object_vars( $last_report->{$this->type}->audits->diagnostics ) );
			}

			if ( ! is_null( $last_report->{$this->type}->audits->passed ) ) {
				$passed_audits = count( get_object_vars( $last_report->{$this->type}->audits->passed ) );
			}
		}

		$this->view(
			'performance/summary-meta-box',
			array(
				'type'             => $this->type,
				'last_report'      => $last_report,
				'opportunities'    => $opportunities,
				'diagnostics'      => $diagnostics,
				'passed_audits'    => $passed_audits,
				'report_dismissed' => $this->dismissed,
				'is_doing_report'  => WP_Hummingbird_Module_Performance::is_doing_report(),
			)
		);
	}

	/**
	 * Settings meta box.
	 *
	 * @since 1.7.1
	 */
	public function settings_metabox() {
		$performance_settings = WP_Hummingbird_Settings::get_settings( 'performance' );

		$this->view(
			'performance/settings-meta-box',
			array(
				'dismissed'     => $this->dismissed,
				'widget'        => $performance_settings['widget'],
				'hub'           => $performance_settings['hub'],
				'subsite_tests' => $performance_settings['subsite_tests'],
			)
		);
	}

	/**
	 * Reporting meta box footer.
	 *
	 * @since 1.7.1
	 */
	public function settings_metabox_footer() {
		$this->view( 'performance/settings-meta-box-footer', array() );
	}

	/**
	 * Error meta box.
	 *
	 * @since 2.0.0
	 */
	public function error_metabox() {
		$error_text = sprintf(
			/* translators: %s - type of report */
			esc_html__( 'There was a problem fetching the %s test results. Please try running a new scan.', 'wphb' ),
			esc_html( $this->type )
		);

		$error_details = '';

		if ( is_wp_error( $this->report ) ) {
			$error_text    = $this->report->get_error_message();
			$error_details = $this->report->get_error_data();
		}

		if ( is_array( $error_details ) && isset( $error_details['details'] ) ) {
			$error_details = $error_details['details'];
		} else {
			$error_details = '';
		}

		$retry_url = wp_nonce_url(
			add_query_arg( 'run', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) ),
			'wphb-run-performance-test'
		);

		$this->view(
			'performance/error-meta-box',
			array(
				'error_details' => $error_details,
				'error_text'    => $error_text,
				'retry_url'     => $retry_url,
			)
		);
	}

	/**
	 * Performance audits meta boxes (Opportunities).
	 *
	 * @since 2.0.0
	 */
	public function opportunities_audit_metaboxes() {
		$this->view(
			'performance/audits-meta-box',
			array(
				'last_test'        => $this->report->data->{$this->type}->audits->opportunities,
				'report_dismissed' => $this->dismissed,
				'type'             => 'opportunities',
			)
		);
	}

	/**
	 * Performance audits meta boxes (Diagnostics).
	 *
	 * @since 2.0.0
	 */
	public function diagnostics_audit_metaboxes() {
		$this->view(
			'performance/audits-meta-box',
			array(
				'last_test'        => $this->report->data->{$this->type}->audits->diagnostics,
				'report_dismissed' => $this->dismissed,
				'type'             => 'diagnostics',
			)
		);
	}

	/**
	 * Performance audits meta boxes (Passed Audits).
	 *
	 * @since 2.0.0
	 */
	public function passed_audit_metaboxes() {
		$this->view(
			'performance/audits-meta-box',
			array(
				'last_test'        => $this->report->data->{$this->type}->audits->passed,
				'report_dismissed' => $this->dismissed,
				'type'             => 'passed',
			)
		);
	}

	/**
	 * Historic Field Data met box.
	 *
	 * @since 2.0.0
	 */
	public function historic_field_data_metabox() {
		$field_data = $this->report->data->{$this->type}->field_data;

		$fcp_fast = $fcp_average = $fcp_slow = false;
		$fid_fast = $fid_average = $fid_slow = false;

		if ( $field_data ) {
			$fcp_fast    = round( $field_data->FIRST_CONTENTFUL_PAINT_MS->distributions[0]->proportion * 100 );
			$fcp_average = round( $field_data->FIRST_CONTENTFUL_PAINT_MS->distributions[1]->proportion * 100 );
			$fcp_slow    = round( $field_data->FIRST_CONTENTFUL_PAINT_MS->distributions[2]->proportion * 100 );

			$fid_fast    = round( $field_data->FIRST_INPUT_DELAY_MS->distributions[0]->proportion * 100 );
			$fid_average = round( $field_data->FIRST_INPUT_DELAY_MS->distributions[1]->proportion * 100 );
			$fid_slow    = round( $field_data->FIRST_INPUT_DELAY_MS->distributions[2]->proportion * 100 );

			$i10n = array(
				'fcp' => array(
					'fast'         => $fcp_fast,
					'fast_desc'    => sprintf(
						/* translators: %d - number of percent */
						esc_html__( '%d%% of loads for this page have a fast (< 1 s) First Contentful Paint (FCP).', 'wphb' ),
						absint( $fcp_fast )
					),
					'average'      => $fcp_average,
					'average_desc' => sprintf(
						/* translators: %d - number of percent */
						esc_html__( '%d%% of loads for this page have an average (1 s ~ 2.5 s) First Contentful Paint (FCP).', 'wphb' ),
						absint( $fcp_average )
					),
					'slow'         => $fcp_slow,
					'slow_desc'    => sprintf(
						/* translators: %d - number of percent */
						esc_html__( '%d%% of loads for this page have a slow (> 2.5 s) First Contentful Paint (FCP).', 'wphb' ),
						absint( $fcp_slow )
					),
				),
				'fid' => array(
					'fast'         => $fid_fast,
					'fast_desc'    => sprintf(
						/* translators: %d - number of percent */
						esc_html__( '%d%% of loads for this page have a fast (< 50 ms) First Input Delay (FID).', 'wphb' ),
						absint( $fid_fast )
					),
					'average'      => $fid_average,
					'average_desc' => sprintf(
						/* translators: %d - number of percent */
						esc_html__( '%d%% of loads for this page have an average (50 ms ~ 250 ms) First Input Delay (FID).', 'wphb' ),
						absint( $fid_average )
					),
					'slow'         => $fid_slow,
					'slow_desc'    => sprintf(
						/* translators: %d - number of percent */
						esc_html__( '%d%% of loads for this page have a slow (> 250 ms) First Input Delay (FID).', 'wphb' ),
						absint( $fid_slow )
					),
				),
			);

			wp_localize_script( 'wphb-google-chart', 'wphbHistoricFieldData', $i10n );
		}

		$this->view(
			'performance/field-data-meta-box',
			compact( 'field_data', 'fcp_fast', 'fcp_average', 'fcp_slow', 'fid_fast', 'fid_average', 'fid_slow' )
		);
	}

	/**
	 * Common audits header meta box.
	 */
	public function reports_metabox_header() {
		$run_url = add_query_arg( 'run', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) );
		$run_url = wp_nonce_url( $run_url, 'wphb-run-performance-test' );

		$title = $this->get_tab_name( $this->get_current_tab() );
		$title = 'Audits' === $title ? __( 'Opportunities', 'wphb' ) : $title;

		$this->view(
			'performance/report-meta-box-header',
			array(
				'can_run_test' => $this->can_run_test,
				'run_url'      => $run_url,
				'title'        => $title,
			)
		);
	}

}
