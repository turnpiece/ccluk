<?php

/**
 * The visitors stats metabox content.
 *
 * @var array $stats Widget stats data.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;

?>

<div id="beehive-stats-visitors-network" class="postbox">

	<h2 class="hndle"><?php esc_html_e( 'Visitors', 'ga_trans' ); ?></h2>

	<div class="inside">

		<div class="beehive-widget-wrap">

			<div id="beehive-analytics-visitors-stats" class="beehive-lazy-load">

				<div class="beehive-options beehive-flushed">

					<div role="tablist" class="beehive-options-tabs">

						<button role="tab" id="beehive-analytics-visitors-overview" class="beehive-tab active" data-tab="overview" data-title="<?php esc_html_e( 'Overview', 'ga_trans' ); ?>" aria-selected="true">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

						<button role="tab" id="beehive-analytics-visitors-sessions" class="beehive-tab" data-tab="sessions" data-title="<?php esc_html_e( 'Sessions', 'ga_trans' ); ?>" aria-selected="false" tabindex="-1">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

						<button role="tab" id="beehive-analytics-visitors-users" class="beehive-tab" data-tab="users" data-title="<?php esc_html_e( 'Users', 'ga_trans' ); ?>" aria-selected="false" tabindex="-1">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

						<button role="tab" id="beehive-analytics-visitors-pageviews" class="beehive-tab" data-tab="pageviews" data-title="<?php esc_html_e( 'Pageviews', 'ga_trans' ); ?>" aria-selected="false" tabindex="-1">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

						<button role="tab" id="beehive-analytics-visitors-page_sessions" class="beehive-tab" data-tab="page_sessions" data-title="<?php esc_html_e( 'Pages/Sessions', 'ga_trans' ); ?>" aria-selected="false" tabindex="-1">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

						<button role="tab" id="beehive-analytics-visitors-average_sessions" class="beehive-tab" data-tab="average_sessions" data-title="<?php esc_html_e( 'Avg. time', 'ga_trans' ); ?>" aria-selected="false" tabindex="-1">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

						<button role="tab" id="beehive-analytics-visitors-bounce_rates" class="beehive-tab" data-tab="bounce_rates" data-title="<?php esc_html_e( 'Bounce Rate', 'ga_trans' ); ?>" aria-selected="false" tabindex="-1">
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
						</button>

					</div>

					<div class="beehive-options-content">

						<div tabindex="0" role="tabpanel" class="beehive-option-content active" data-pane="overview" aria-labelledby="beehive-analytics-visitors-overview">
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-overview-chart" class="beehive-charts beehive-charts-line" hidden></div>

							<?php
							$this->view( 'stats/google/dashboard-widget/elements/notice', [
								'id'      => 'beehive-analytics-notice',
								'type'    => 'info',
								'message' => esc_html__( 'It may take up to 24 hours for data to begin feeding. Please check back soon.', 'ga_trans' ),
								'dismiss' => true,
								'hidden'  => true,
							] );
							?>

						</div>

						<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="sessions" aria-labelledby="beehive-analytics-visitors-sessions" hidden>
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-sessions-chart" class="beehive-charts beehive-charts-line" hidden></div>
						</div>

						<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="users" aria-labelledby="beehive-analytics-visitors-users" hidden>
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-users-chart" class="beehive-charts beehive-charts-line"></div>
						</div>

						<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="pageviews" aria-labelledby="beehive-analytics-visitors-pageviews" hidden>
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-pageviews-chart" class="beehive-charts beehive-charts-line"></div>
						</div>

						<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="page_sessions" aria-labelledby="beehive-analytics-visitors-page_sessions" hidden>
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-page_sessions-chart" class="beehive-charts beehive-charts-line"></div>
						</div>

						<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="average_sessions" aria-labelledby="beehive-analytics-visitors-average_sessions" hidden>
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-average_sessions-chart" class="beehive-charts beehive-charts-line"></div>
						</div>

						<div tabindex="0" role="tabpanel" class="beehive-option-content" data-pane="bounce_rates" aria-labelledby="beehive-analytics-visitors-bounce_rates" hidden>
							<figure class="beehive-charts beehive-charts-loader after-message">

								<img src="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/chart-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/chart-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Empty chart draw', 'ga_trans' ); ?>"/>

								<figcaption>
									<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
									<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
								</figcaption>

							</figure>
							<div id="beehive-analytics-visitors-bounce_rates-chart" class="beehive-charts beehive-charts-line"></div>
						</div>

					</div>

					<div tabindex="-1" class="beehive-options-sidenote" aria-hidden="true">
						<span class="beehive-sidenote-left">
							<i class="beehive-sidenote-indicator" aria-hidden="true"></i>
							<?php esc_html_e( 'Users', 'ga_trans' ); ?>
						</span>
						<span class="beehive-sidenote-right">
							<i class="beehive-sidenote-indicator" aria-hidden="true"></i>
							<?php esc_html_e( 'Pageviews', 'ga_trans' ); ?>
						</span>
					</div>

				</div>

			</div>

		</div>

	</div>

</div>