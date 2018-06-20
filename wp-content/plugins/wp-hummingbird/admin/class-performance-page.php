<?php

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
	 * Render header.
	 */
	public function render_header() {
		$this->report = WP_Hummingbird_Module_Performance::get_last_report();
		if ( is_wp_error( $this->report ) ) {
			$this->has_error = true;
		}

		// Check to see if there's a fresh report on the server.
		if ( false === $this->report && ! WP_Hummingbird_Module_Performance::is_doing_report() ) {
			WP_Hummingbird_Module_Performance::refresh_report();
		}

		$this->dismissed = WP_Hummingbird_Module_Performance::report_dismissed( $this->report );
		$this->can_run_test = WP_Hummingbird_Module_Performance::can_run_test( $this->report );

		$run_url = add_query_arg( 'run', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) );
		$run_url = wp_nonce_url( $run_url, 'wphb-run-performance-test' );

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
				<?php if ( true === $this->can_run_test ) : ?>
					<a href="<?php echo esc_url( $run_url ); ?>" class="sui-button">
						<?php esc_html_e( 'New Test', 'wphb' ); ?>
					</a>
					<?php
				else :
					$tooltip = sprintf(
						/* translators: %d: number of minutes. */
						_n(
							'Hummingbird is just catching her breath - you can run another test in %d minute',
							'Hummingbird is just catching her breath - you can run another test in %d minutes',
							$this->can_run_test,
							'wphb'
						),
						number_format_i18n( $this->can_run_test )
					);
					?>
					<span class="sui-tooltip sui-tooltip-bottom sui-tooltip-constrained" disabled="disabled" data-tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true">
						<a href="#" class="sui-button wphb-disabled-test" disabled="disabled" aria-hidden="true">
							<?php esc_html_e( 'New Test', 'wphb' ); ?>
						</a>
					</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_documentation_url( $this->slug, $this->get_current_tab() ) ); ?>" target="_blank" class="sui-button sui-button-ghost">
					<i class="sui-icon-academy" aria-hidden="true"></i>
					<?php esc_html_e( 'View Documentation', 'wphb' ); ?>
				</a>
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
		$this->view( $this->slug . '-page', array(
			'report' => $this->report,
		));
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		$this->tabs = array(
			'main'    => __( 'Improvements', 'wphb' ),
		);
		if ( is_multisite() && is_network_admin() ) {
			$this->tabs['reports'] = __( 'Reporting', 'wphb' );
			$this->tabs['settings'] = __( 'Settings', 'wphb' );
		} elseif ( ! is_multisite() ) {
			$this->tabs['reports'] = __( 'Reporting', 'wphb' );
		}

		// We need to actually tweak these tasks.
		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );

		if ( isset( $_GET['run'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-run-performance-test' );

			/* @var WP_Hummingbird_Module_Performance $perf_module */
			$perf_module = WP_Hummingbird_Utils::get_module( 'performance' );

			if ( WP_Hummingbird_Module_Performance::is_doing_report() ) {
				return;
			}

			// Start the test.
			$perf_module->init_scan();

			wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce' ) ) );
			exit;
		}

		// Process form submit from expiry settings.
		if ( isset( $_POST['dismiss_report'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-dismiss-performance-report' );

			WP_Hummingbird_Module_Performance::dismiss_report( true );

			$redirect_to = add_query_arg( array(
				'report-dismissed' => true,
			) );
			wp_safe_redirect( $redirect_to );
		}
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		// Default to empty meta box if doing performance scan, or we will get php notices.
		if ( ! WP_Hummingbird_Module_Performance::is_doing_report() ) {
			$this->add_meta_box(
				'performance-welcome',
				null,
				array( $this, 'performance_welcome_metabox' ),
				null,
				null,
				'summary',
				array(
					'box_class' => 'sui-box sui-summary',
					'box_content_class' => false,
				)
			);
			$this->add_meta_box(
				'performance-summary',
				__( 'Improvements', 'wphb' ),
				array( $this, 'performance_summary_metabox' ),
				array( $this, 'performance_summary_metabox_header' ),
				null,
				'main',
				array(
					'box_content_class' => false,
				)
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

			if ( is_multisite() && is_network_admin() ) {
				$this->add_meta_box(
					'settings-summary',
					__( 'Settings', 'wphb' ),
					array( $this, 'settings_metabox' ),
					null,
					array( $this, 'settings_metabox_footer' ),
					'settings'
				);
			}
		} else {
			$this->add_meta_box(
				'performance-summary',
				__( 'Performance test', 'wphb' ),
				array( $this, 'performance_empty_metabox' ),
				null,
				null,
				'main',
				array(
					'box_content_class' => 'sui-box-body sui-block-content-center',
				)
			);
		} // End if().
	}

	/**
	 * Summary meta box.
	 */
	public function performance_summary_metabox() {
		$last_test = $this->report;
		$doing_report = WP_Hummingbird_Module_Performance::is_doing_report();

		$error_details = '';
		$error_text = '';
		if ( $last_test ) {
			if ( is_wp_error( $last_test ) ) {
				$error_text = $last_test->get_error_message();
				$error_details = $last_test->get_error_data();
				if ( is_array( $error_details ) && isset( $error_details['details'] ) ) {
					$error_details = $error_details['details'];
				} else {
					$error_details = '';
				}

				$this->has_error = true;
			} else {
				$last_test = $this->report->data;
			}

			$retry_url = add_query_arg( 'run', 'true', WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) );
			$retry_url = wp_nonce_url( $retry_url, 'wphb-run-performance-test' );

			$this->view(
				'performance/summary-meta-box',
				array(
					'last_test'        => $last_test,
					'error'            => $this->has_error,
					'error_details'    => $error_details,
					'error_text'       => $error_text,
					'retry_url'        => $retry_url,
					'report_dismissed' => $this->dismissed,
					'can_run_test'     => $this->can_run_test,
					'is_subsite'       => ! is_main_site(),
				)
			);
		} else {
			$this->view(
				'performance/empty-summary-meta-box',
				array(
					'doing_report' => $doing_report,
				)
			);
		} // End if().
	}

	/**
	 * Performance welcome meta box.
	 */
	public function performance_welcome_metabox() {
		$last_report = $this->report;

		$last_score = false;
		$improvement = 0;

		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$last_report = $last_report->data;

			if ( $last_report->last_score ) {
				$improvement = $last_report->score - $last_report->last_score['score'];
				$last_score = $last_report->last_score['score'];
			}
		}

		$this->view(
			'performance/module-resume-meta-box',
			array(
				'last_report'      => $last_report,
				'improvement'      => $improvement,
				'last_score'       => $last_score,
				'recommendations'  => WP_Hummingbird_Utils::get_number_of_issues( 'performance', $this->report ),
				'report_dismissed' => $this->dismissed,
				'is_doing_report'  => WP_Hummingbird_Module_Performance::is_doing_report(),
			)
		);
	}

	/**
	 * Performance summary meta box header.
	 */
	public function performance_summary_metabox_header() {
		$this->view(
			'performance/summary-meta-box-header',
			array(
				'title'     => __( 'Improvements', 'wphb' ),
				'dismissed' => $this->dismissed,
			)
		);
	}

	/**
	 * Empty performance meta box.
	 */
	public function performance_empty_metabox() {
		$this->view(
			'performance/empty-summary-meta-box',
			array(
				'doing_report' => true,
			)
		);
	}

	/**
	 * Settings meta box.
	 *
	 * @since 1.7.1
	 */
	public function settings_metabox() {
		$subsite_tests = WP_Hummingbird_Settings::get_setting( 'subsite_tests', 'performance' );

		$args = compact( 'subsite_tests' );
		$this->view( 'performance/settings-meta-box', $args );
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
	 * We need to insert an extra label to the tabs sometimes
	 *
	 * @param string $tab Current tab.
	 */
	public function after_tab( $tab ) {
		if ( 'main' !== $tab ) {
			return;
		}

		if ( ! $this->report ) {
			return;
		}

		$class = '';
		if ( isset( $this->report->data->score_class ) ) {
			switch ( $this->report->data->score_class ) {
				case 'aplus':
				case 'a':
				case 'b':
					$class = 'success';
					break;
				case 'c':
				case 'd':
					$class = 'warning';
					break;
				case 'e':
				case 'f':
					$class = 'error';
					break;
			}
		}
		if ( $this->dismissed ) {
			echo ' <i class="sui-icon-info" aria-hidden="true"></i>';
		} elseif ( ! $this->has_error ) {
			echo ' <span class="sui-tag sui-tag-' . esc_attr( $class ) . '">' . esc_html( WP_Hummingbird_Utils::get_number_of_issues( 'performance', $this->report ) ) . '</span>';
		} else {
			echo ' <i class="sui-icon-warning-alert sui-warning" aria-hidden="true"></i>';
		}
	}

}