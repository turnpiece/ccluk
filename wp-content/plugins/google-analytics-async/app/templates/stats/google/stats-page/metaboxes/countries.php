<?php

/**
 * The top countries stats metabox content.
 *
 * @var array $stats Widget stats data.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

?>

<div id="beehive-stats-countries-network" class="postbox">

	<h2 class="hndle"><?php esc_html_e( 'Top Countries', 'ga_trans' ); ?></h2>

	<div class="inside">

		<div class="beehive-widget-wrap">

			<div id="beehive-analytics-countries-stats" class="beehive-lazy-load">

				<div class="beehive-row">

					<div class="beehive-col beehive-col-map">

						<div id="beehive-analytics-countries-chart" class="beehive-charts beehive-charts-map"></div>

					</div>

					<div class="beehive-col beehive-col-table">

						<?php
						$this->view( 'stats/google/dashboard-widget/elements/table', [
							'id'      => 'analytics-countries-list',
							'class'   => 'beehive-table-alt',
							'caption' => esc_html__( 'List of top 7 countries visiting your website.', 'ga_trans' ),
							'headers' => [
								[
									'col'   => 8,
									'title' => esc_html__( 'Top Countries', 'ga_trans' ),
								],
								[
									'col'   => 4,
									'title' => esc_html__( 'Pageviews', 'ga_trans' ),
								],
							],
						] );
						?>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>