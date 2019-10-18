<?php

/**
 * The top pages stats metabox content.
 *
 * @var array $stats Widget stats data.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

?>

<div id="beehive-stats-pages-network" class="postbox">

	<h2 class="hndle"><?php esc_html_e( 'Top Pages', 'ga_trans' ); ?></h2>

	<div class="inside">

		<div class="beehive-widget-wrap">

			<div id="beehive-analytics-pages-stats" class="beehive-lazy-load">

				<?php
				$this->view( 'stats/google/dashboard-widget/elements/table', [
					'id'      => 'analytics-pages-list',
					'class'   => 'paginated',
					'caption' => esc_html__( 'Top 10 visited pages.', 'ga_trans' ),
					'headers' => [
						[
							'col'   => 6,
							'title' => esc_html__( 'Top Pages/most visited', 'ga_trans' ),
						],
						[
							'col'   => 2,
							'title' => esc_html__( 'Avg. time', 'ga_trans' ),
						],
						[
							'col'   => 2,
							'title' => esc_html__( 'Views', 'ga_trans' ),
						],
						[
							'col'   => 2,
							'title' => esc_html__( 'Trend', 'ga_trans' ),
						],
					],
				] );
				?>

			</div>

		</div>

	</div>

</div>