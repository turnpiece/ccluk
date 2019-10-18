<?php

/**
 * Audience tab template.
 *
 * @var bool   $logged_in     Is logged in?.
 * @var bool   $network       Is network admin?.
 * @var string $settings_url  Settings page url.
 * @var bool   $can_get_stats Can view status?.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;
use Beehive\Core\Helpers\Permission;

?>

<div class="beehive-options-wrapper">

	<?php
	$this->view( 'stats/google/dashboard-widget/elements/chart-options', [
		'id'      => 'analytics-audience-stats',
		'button'  => true,
		'flushed' => true,
		'network' => $network,
	] );
	?>

	<?php if ( ! $logged_in && ! $can_get_stats ) :  // Not logged in. ?>

		<figure class="beehive-charts beehive-charts-loader">

			<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

			<figcaption>
				<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
				<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
			</figcaption>

		</figure>

	<?php else : ?>

		<div class="beehive-options-content">

			<div tabindex="0" role="tabpanel" class="beehive-option-content active" data-pane="loading">

				<figure class="beehive-charts beehive-charts-loader after-message">

					<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

					<figcaption>
						<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
						<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
					</figcaption>

				</figure>

			</div>

			<?php if ( Permission::has_report_cap( 'audience', 'sessions', 'dashboard', $network ) ) : ?>
				<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="sessions" aria-labelledby="beehive-option-tab-sessions" hidden="hidden">
					<div id="beehive-audience-sessions-chart" class="beehive-charts beehive-charts-line"></div>
				</div>
			<?php endif; ?>

			<?php if ( Permission::has_report_cap( 'audience', 'users', 'dashboard', $network ) ) : ?>
				<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="users" aria-labelledby="beehive-option-tab-users" hidden="hidden">
					<div id="beehive-audience-users-chart" class="beehive-charts beehive-charts-line"></div>
				</div>
			<?php endif; ?>

			<?php if ( Permission::has_report_cap( 'audience', 'pageviews', 'dashboard', $network ) ) : ?>
				<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="pageviews" aria-labelledby="beehive-option-tab-pageviews" hidden="hidden">
					<div id="beehive-audience-pageviews-chart" class="beehive-charts beehive-charts-line"></div>
				</div>
			<?php endif; ?>

			<?php if ( Permission::has_report_cap( 'audience', 'page_sessions', 'dashboard', $network ) ) : ?>
				<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="page_sessions" aria-labelledby="beehive-option-tab-page_sessions" hidden="hidden">
					<div id="beehive-audience-page_sessions-chart" class="beehive-charts beehive-charts-line"></div>
				</div>
			<?php endif; ?>

			<?php if ( Permission::has_report_cap( 'audience', 'average_sessions', 'dashboard', $network ) ) : ?>
				<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="average_sessions" aria-labelledby="beehive-option-tab-average_sessions" hidden="hidden">
					<div id="beehive-audience-average_sessions-chart" class="beehive-charts beehive-charts-line"></div>
				</div>
			<?php endif; ?>

			<?php if ( Permission::has_report_cap( 'audience', 'bounce_rates', 'dashboard', $network ) ) : ?>
				<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="bounce_rates" aria-labelledby="beehive-option-tab-bounce_rates" hidden="hidden">
					<div id="beehive-audience-bounce_rates-chart" class="beehive-charts beehive-charts-line"></div>
				</div>
			<?php endif; ?>

		</div>

	<?php endif; ?>

	<div class="beehive-users">

		<?php
		if ( ! $logged_in && ! $can_get_stats ) : // Not logged in.

			$this->view( 'stats/google/dashboard-widget/elements/notice', [
				'id'      => 'beehive-analytics-audience-auth',
				'type'    => 'error',
				'message' => sprintf( __( 'Please, <a href="%s">authorize your account</a> to see the statistics.', 'ga_trans' ), $settings_url ),
				'dismiss' => true,
			] );

		else : // No data found.

			$this->view( 'stats/google/dashboard-widget/elements/notice', [
				'type'    => 'info',
				'message' => esc_html__( 'We haven\'t collected enough data. Please check back soon.', 'ga_trans' ),
				'dismiss' => true,
				'hidden'  => true,
			] );
			?>

			<div class="beehive-users-resume empty">

				<div id="beehive-analytics-audience-stats-donut" class="beehive-charts beehive-charts-pie"></div>

				<div class="beehive-users-stats">

					<p class="beehive-visitors-empty"><?php esc_html_e( 'No data for this period of time', 'ga_trans' ); ?></p>

					<p class="beehive-visitors-old"><?php printf( esc_html__( '%s returning visitors', 'ga_trans' ), '<span></span>' ); ?></p>

					<p class="beehive-visitors-new"><?php printf( esc_html__( '%s new visitors', 'ga_trans' ), '<span></span>' ); ?></p>

				</div>

			</div>

		<?php endif; ?>

	</div>

</div>