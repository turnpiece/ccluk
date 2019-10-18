<?php

/**
 * General stats tab template.
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

<?php
if ( ! $logged_in && ! $can_get_stats ) : // Not logged in.

	$this->view( 'stats/google/dashboard-widget/elements/notice', [
		'id'      => 'beehive-analytics-traffic-auth',
		'type'    => 'error',
		'message' => sprintf( __( 'Please, <a href="%s">authorize your account</a> to see the statistics.', 'ga_trans' ), $settings_url ),
		'dismiss' => true,
	] );

elseif ( empty( $data ) ) : // No data found.

	$this->view( 'stats/google/dashboard-widget/elements/notice', [
		'type'    => 'info',
		'message' => esc_html__( 'It may take up to 24 hours for data to begin feeding. Please check back soon.', 'ga_trans' ),
		'dismiss' => true,
		'hidden'  => true,
	] );

endif;
?>

<?php if ( Permission::has_report_cap( 'traffic', 'countries', 'dashboard', $network ) ) : ?>

	<div class="beehive-row-traffic-top">

		<div class="beehive-col beehive-col-map">

			<?php if ( ! $logged_in && ! $can_get_stats ) : // Not logged in. ?>

				<figure class="beehive-charts beehive-charts-loader">

					<img src="<?php echo esc_url( Template::asset_url( 'images/map-empty.png' ) ); ?>" srcset="<?php echo esc_url( Template::asset_url( 'images/map-empty.png' ) ); ?> 1x, <?php echo esc_url( Template::asset_url( 'images/map-empty@2x.png' ) ); ?> 2x" alt="<?php esc_html_e( 'Map without stats.', 'ga_trans' ); ?>"/>

					<figcaption>
						<i class="sui-icon-loader sui-lg sui-loading" aria-hidden="true"></i>
						<?php esc_html_e( 'Fetching latest data...', 'ga_trans' ); ?>
					</figcaption>

				</figure>

			<?php else : ?>

				<div id="beehive-analytics-traffic-stats" class="beehive-charts beehive-charts-map" style="width: 100%;"></div>

			<?php endif; ?>

		</div>

		<div class="beehive-col beehive-col-auto">

			<?php
			$this->view( 'stats/google/dashboard-widget/elements/table', [
				'id'      => 'analytics-traffic-list',
				'class'   => 'beehive-table-alt',
				'caption' => esc_html__( 'List of all countries visiting your website.', 'ga_trans' ),
				'headers' => [
					[
						'col'   => 8,
						'title' => esc_html__( 'Countries', 'ga_trans' ),
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

<?php endif; ?>

<?php if ( $logged_in || $can_get_stats ) : ?>

	<div class="beehive-row-traffic-bottom" hidden>

		<?php if ( Permission::has_report_cap( 'traffic', 'mediums', 'dashboard', $network ) ) : ?>
			<div class="beehive-col beehive-col-auto" hidden>
				<div id="beehive-analytics-traffic-mediums" class="beehive-charts beehive-charts-pie"></div>
			</div>
		<?php endif; ?>

		<?php if ( Permission::has_report_cap( 'traffic', 'search_engines', 'dashboard', $network ) ) : ?>
			<div class="beehive-col beehive-col-auto" hidden>
				<div id="beehive-analytics-traffic-search_engines" class="beehive-charts beehive-charts-pie"></div>
			</div>
		<?php endif; ?>

		<?php if ( Permission::has_report_cap( 'traffic', 'social_networks', 'dashboard', $network ) ) : ?>
			<div class="beehive-col beehive-col-auto" hidden>
				<div id="beehive-analytics-traffic-social_networks" class="beehive-charts beehive-charts-pie"></div>
			</div>
		<?php endif; ?>

	</div>

<?php endif; ?>