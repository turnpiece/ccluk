<?php

/**
 * The top referrals stats metabox content.
 *
 * @var array $stats Widget stats data.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

?>

<div id="beehive-stats-referrals-network" class="postbox">

	<h2 class="hndle"><?php esc_html_e( 'Referrals', 'ga_trans' ); ?></h2>

	<div class="inside">

		<div class="beehive-widget-wrap">

			<div id="beehive-analytics-referrals-stats" class="beehive-lazy-load">

				<div class="beehive-row beehive-empty-chart-row sui-hidden-important">

					<div class="beehive-col beehive-col-chart">
						<div id="beehive-analytics-referral-empty" class="beehive-charts beehive-charts-pie beehive-charts-pie-empty"></div>
					</div>
					<div class="beehive-col beehive-col-legend">
						<ul>
							<li>
								<i aria-hidden="true"></i>
								<span><?php esc_html_e( 'No information', 'ga_trans' ); ?></span>
							</li>
						</ul>
					</div>

				</div>

				<div class="beehive-row">

					<div class="beehive-col beehive-col-chart">
						<div id="beehive-analytics-referral-mediums" class="beehive-charts beehive-charts-pie"></div>
					</div>

				</div>

				<div class="beehive-row">

					<div class="beehive-col beehive-col-chart">
						<div id="beehive-analytics-referral-search_engines" class="beehive-charts beehive-charts-pie"></div>
					</div>

				</div>

				<div class="beehive-row">

					<div class="beehive-col beehive-col-chart">
						<div id="beehive-analytics-referral-social_networks" class="beehive-charts beehive-charts-pie"></div>
					</div>

				</div>

			</div>

		</div>

	</div>

</div>