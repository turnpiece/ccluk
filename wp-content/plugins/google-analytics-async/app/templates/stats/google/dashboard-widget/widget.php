<?php
/**
 * The dashboard widget template.
 *
 * @var bool   $logged_in       Is logged in?.
 * @var array  $periods         Periods list.
 * @var bool   $network         Is network admin?.
 * @var string $settings_url    Settings page url.
 * @var string $statistics_url  Statistics page url.
 * @var string $selected_period Selected date.
 * @var bool   $delay_notice    Should show delay notice.
 * @var bool   $can_get_stats   Can view status?.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Permission;

?>

<div class="beehive-widget-wrap">

	<?php wp_nonce_field( 'beehive_admin_nonce', 'beehive_admin_nonce' ); // This can be used for form processing. ?>

	<?php if ( Permission::has_report_cap( 'general', false, 'dashboard', $network ) || Permission::has_report_cap( 'audience', false, 'dashboard', $network ) || Permission::has_report_cap( 'top_pages', false, $network ) || Permission::has_report_cap( 'traffic', false, $network ) ) : ?>

		<?php
		$this->view( 'stats/google/dashboard-widget/elements/widget-header', [
			'logged_in'       => $logged_in,
			'network'         => $network,
			'periods'         => $periods,
			'settings_url'    => $settings_url,
			'statistics_url'  => $statistics_url,
			'selected_period' => $selected_period,
		] );
		?>

		<div id="beehive-analytics-widget-tabs" class="beehive-tabs">

			<?php if ( Permission::has_report_cap( 'general', false, 'dashboard', $network ) || Permission::has_report_cap( 'audience', false, 'dashboard', $network ) || Permission::has_report_cap( 'top_pages', false, 'dashboard', $network ) || Permission::has_report_cap( 'traffic', false, 'dashboard', $network ) ) : ?>

				<div role="tablist" class="beehive-tabs-menu">

					<?php if ( Permission::has_report_cap( 'general', false, 'dashboard', $network ) ) : ?>
						<button role="tab" id="beehive-widget-general-tab" class="beehive-tab active" aria-selected="true" data-tab="general">
							<?php esc_html_e( 'General Stats', 'ga_trans' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( Permission::has_report_cap( 'audience', false, 'dashboard', $network ) ) : ?>
						<button role="tab" id="beehive-widget-general-audience" class="beehive-tab" data-tab="audience" aria-selected="false" tabindex="-1">
							<?php esc_html_e( 'Audience', 'ga_trans' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( Permission::has_report_cap( 'pages', false, 'dashboard', $network ) ) : ?>
						<button role="tab" id="beehive-widget-general-pages" class="beehive-tab" data-tab="pages" aria-selected="false" tabindex="-1">
							<?php esc_html_e( 'Top Pages & Views', 'ga_trans' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( Permission::has_report_cap( 'traffic', false, 'dashboard', $network ) ) : ?>
						<button role="tab" id="beehive-widget-general-traffic" class="beehive-tab" data-tab="traffic" aria-selected="false" tabindex="-1">
							<?php esc_html_e( 'Traffic', 'ga_trans' ); ?>
						</button>
					<?php endif; ?>

				</div>

			<?php endif; ?>

			<div class="beehive-tabs-content">

				<!-- TAB: General Stats -->
				<?php if ( Permission::has_report_cap( 'general', false, 'dashboard', $network ) ) : ?>
					<div tabindex="0" role="tabpanel" class="beehive-tab beehive-lazy-load active" data-pane="general" aria-labelledby="beehive-widget-general-tab">

						<?php
						$this->view( 'stats/google/dashboard-widget/tabs/tab-general', [
							'logged_in'     => $logged_in,
							'network'       => $network,
							'settings_url'  => $settings_url,
							'can_get_stats' => $can_get_stats,
						] );
						?>

					</div>
				<?php endif; ?>

				<!-- TAB: Audience -->
				<?php if ( Permission::has_report_cap( 'audience', false, 'dashboard', $network ) ) : ?>
					<div tabindex="0" role="tabpanel" class="beehive-tab beehive-lazy-load" data-pane="audience" aria-labelledby="beehive-widget-audience-tab" hidden>

						<?php
						$this->view( 'stats/google/dashboard-widget/tabs/tab-audience', [
							'logged_in'     => $logged_in,
							'network'       => $network,
							'settings_url'  => $settings_url,
							'can_get_stats' => $can_get_stats,
						] );
						?>

					</div>
				<?php endif; ?>

				<!-- TAB: Pages -->
				<?php if ( Permission::has_report_cap( 'pages', false, 'dashboard', $network ) ) : ?>
					<div tabindex="0" role="tabpanel" class="beehive-tab beehive-lazy-load" data-pane="pages" aria-labelledby="beehive-widget-pages-tab" hidden>

						<?php
						$this->view( 'stats/google/dashboard-widget/tabs/tab-pages', [
							'logged_in'     => $logged_in,
							'network'       => $network,
							'settings_url'  => $settings_url,
							'can_get_stats' => $can_get_stats,
						] );
						?>

					</div>
				<?php endif; ?>

				<!-- TAB: Traffic -->
				<?php if ( Permission::has_report_cap( 'traffic', false, 'dashboard', $network ) ) : ?>
					<div tabindex="0" role="tabpanel" class="beehive-tab beehive-lazy-load" data-pane="traffic" aria-labelledby="beehive-widget-traffic-tab" hidden>

						<?php
						$this->view( 'stats/google/dashboard-widget/tabs/tab-traffic', [
							'logged_in'     => $logged_in,
							'network'       => $network,
							'settings_url'  => $settings_url,
							'can_get_stats' => $can_get_stats,
						] );
						?>

					</div>
				<?php endif; ?>

			</div>

		</div>

	<?php else : ?>

		<div class="beehive-analytics-empty-message">

			<!-- TODO: Replace this with real image and adjust styles. -->
			<span class="dummy-image"></span>

			<?php if ( Permission::is_admin_user( $network ) ) : ?>

				<p><?php printf( __( 'No analytics type has been chosen to be displayed here. <a href="%s">Go to settings</a> and select which statistics you want to show.', 'ga_trans' ), esc_url( $settings_url ) ); ?></p>

			<?php else : ?>

				<p><?php esc_html_e( 'No analytics type has been chosen to be displayed here.', 'ga_trans' ); ?></p>

			<?php endif; ?>

		</div>

	<?php endif; ?>

</div>