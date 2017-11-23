<?php

/**
 * Class WP_Hummingbird_Performance_Report_Page
 */
class WP_Hummingbird_Performance_Report_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Status of error. If true, than we have some error.
	 *
	 * @var bool $has_error True if error present.
	 */
	public $has_error;

	/**
	 * WP_Hummingbird_Performance_Report_Page constructor.
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
			'main'    => __( 'Improvements', 'wphb' ),
			'reports' => __( 'Reporting', 'wphb' ),
		);

		// We need to actually tweak these tasks.
		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	/**
	 * Render header.
	 */
	public function render_header() {
		$this->get_error_status();

		$last_report = wphb_performance_get_last_report();

		// Check to see if there's a fresh report on the server.
		if ( false === $last_report && ! wphb_performance_is_doing_report() ) {
			wphb_performance_refresh_report();
			$last_report = wphb_performance_get_last_report();
		}

		$run_url = add_query_arg( 'run', 'true', wphb_get_admin_menu_url( 'performance' ) );
		$run_url = wp_nonce_url( $run_url, 'wphb-run-performance-test' );
		$can_run_scan = WP_Hummingbird_Module_Performance::can_run_test();
		?>
		<div class="wphb-notice wphb-notice-success hidden" id="wphb-notice-performance-report-settings-updated">
			<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
		</div>
		<section id="header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div class="actions">
				<?php if ( true === $can_run_scan ) : ?>
					<a href="<?php echo esc_url( $run_url ); ?>" class="button button-grey"><?php esc_html_e( 'New Test', 'wphb' ); ?></a>
					<?php
				else :
					/* translators: %d: number of minutes. */
					$tooltip = sprintf( __( 'Hummingbird is just catching her breath - you can run another test in %d minutes', 'wphb' ), esc_attr( $can_run_scan ) );
					?>
					<a href="#" class="button button-grey tooltip-l tooltip-bottom" disabled="disabled" tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"><?php esc_html_e( 'New Test', 'wphb' ); ?></a>
				<?php endif; ?>
				<a href="<?php echo esc_url( wphb_get_documentation_url( $this->slug, $this->get_current_tab() ) ); ?>" target="_blank" class="button button-ghost documentation-button">
					<?php esc_html_e( 'View Documentation', 'wphb' ); ?>
				</a>
			</div>
		</section><!-- end header -->
		<?php
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		if ( isset( $_GET['run'] ) ) {
			check_admin_referer( 'wphb-run-performance-test' );

			if ( ! current_user_can( wphb_get_admin_capability() ) ) {
				return;
			}

			if ( wphb_performance_is_doing_report() ) {
				return;
			}
			// Start the test
			wphb_performance_init_scan();

			wp_redirect( remove_query_arg( array( 'run', '_wpnonce' ) ) );
			exit;
		}
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		// Default to empty meta box if doing performance scan, or we will get php notices.
		if ( ! wphb_performance_is_doing_report() ) {
			$this->add_meta_box(
				'performance-welcome',
				null,
				array( $this, 'performance_welcome_metabox' ),
				null,
				null,
				'summary',
				array(
					'box_class' => 'dev-box content-box content-box-two-cols-image-left',
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
					'box_class'         => 'dev-box content-box-one-col-center',
					'box_content_class' => 'box-content no-side-padding',
				)
			);

			$this->add_meta_box(
				'reporting-summary',
				__( 'Reports', 'wphb' ),
				array( $this, 'reporting_metabox' ),
				array( $this, 'reporting_metabox_header' ),
				array( $this, 'reporting_metabox_footer' ),
				'reports',
				array(
					'box_class'         => 'dev-box content-box-one-col-center',
					'box_content_class' => 'box-content no-padding',
					'box_footer_class'  => wphb_is_member() ? 'box-footer' : 'box-footer wphb-reporting-no-membership',
				)
			);
		} else {
			$this->add_meta_box(
				'performance-summary',
				__( 'Performance test', 'wphb' ),
				array( $this, 'performance_empty_metabox' ),
				null,
				null,
				'main',
				array(
					'box_class'         => 'dev-box content-box-one-col-center',
					'box_content_class' => 'box-content no-side-padding',
				)
			);
		} // End if().
	}

	public function performance_summary_metabox() {
		$last_test = wphb_performance_get_last_report();
		$doing_report = wphb_performance_is_doing_report();

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
				$last_test = $last_test->data;
				/*$this->has_error = false;*/
			}

			$retry_url = add_query_arg( 'run', 'true', wphb_get_admin_menu_url( 'performance' ) );
			$retry_url = wp_nonce_url( $retry_url, 'wphb-run-performance-test' );

			$this->view(
				'performance/summary-meta-box',
				array(
					'last_test'     => $last_test,
					'error'         => $this->has_error,
					'error_details' => $error_details,
					'error_text'    => $error_text,
					'retry_url'     => $retry_url,
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

	public function performance_welcome_metabox() {
		$last_report = wphb_performance_get_last_report();

		$last_score = '';
		$improvement = 0;
		if ( ! is_wp_error( $last_report ) ) {
			$last_report = $last_report->data;

			$last_score = false;
			if ( $last_report->last_score ) {
				$improvement = $last_report->score - $last_report->last_score['score'];
				$last_score = $last_report->last_score['score'];
			}
		}

		$this->view(
			'performance/module-resume-meta-box',
			array(
				'last_report'     => $last_report,
				'improvement'     => $improvement,
				'last_score'      => $last_score,
				'recommendations' => wphb_get_number_of_issues( 'performance' ),
			)
		);
	}

	public function performance_summary_metabox_header() {
		$title = __( 'Improvements', 'wphb' );
		$last_report = wphb_performance_get_last_report();
		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$last_report = $last_report->data;
		}
		$this->view(
			'performance/summary-meta-box-header',
			array(
				'title'       => $title,
				'last_report' => $last_report,
			)
		);
	}

	public function performance_empty_metabox() {
		$this->view(
			'performance/empty-summary-meta-box',
			array(
				'doing_report' => true,
			)
		);
	}

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
		$this->view( 'performance/reporting-meta-box', $args );
	}

	/**
	 * Reporting meta box header.
	 *
	 * @since 1.5.0
	 */
	public function reporting_metabox_header() {
		$title = __( 'Reports', 'wphb' );
		$this->view( 'performance/reporting-meta-box-header', compact( 'title' ) );
	}

	/**
	 * Reporting meta box footer.
	 *
	 * @since 1.5.0
	 */
	public function reporting_metabox_footer() {
		$this->view( 'performance/reporting-meta-box-footer', array() );
	}

	/**
	 * See if there are any errors. Set the variable to true if some errors are found.
	 *
	 * @since 1.4.5
	 */
	private function get_error_status() {
		$this->has_error = false;
		$last_test = wphb_performance_get_last_report();
		if ( is_wp_error( $last_test ) ) {
			$this->has_error = true;
		}
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

		$last_test = wphb_performance_get_last_report();
		if ( ! $last_test ) {
			return;
		}

		$class = '';
		if ( isset( $last_test->data ) ) {
			switch ( $last_test->data->score_class ) {
				case 'aplus':
				case 'a':
				case 'b':
					$class = 'green';
					break;
				case 'c':
				case 'd':
					$class = 'yellow';
					break;
				case 'e':
				case 'f':
					$class = 'red';
					break;
			}
		}
		if ( ! $this->has_error ) {
			echo ' <span class="hide-on-mobile wphb-button-label wphb-button-label-' . $class . '">' . wphb_get_number_of_issues( 'performance' ) . '</span>';
		} else {
			echo ' <i class="hide-on-mobile hb-wpmudev-icon-warning"></i>';
		}
	}

}