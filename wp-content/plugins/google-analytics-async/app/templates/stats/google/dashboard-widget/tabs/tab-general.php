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

use Beehive\Core\Helpers\Permission;

?>

<?php
if ( ! $logged_in && ! $can_get_stats ) : // Not logged in.

	$this->view( 'stats/google/dashboard-widget/elements/notice', [
		'id'      => 'beehive-analytics-general-auth',
		'type'    => 'error',
		'message' => sprintf( __( 'Please, <a href="%s">authorize your account</a> to see the statistics.', 'ga_trans' ), $settings_url ),
		'dismiss' => true,
	] );

else : // No data found.

	$this->view( 'stats/google/dashboard-widget/elements/notice', [
		'id'      => 'beehive-analytics-general-info',
		'type'    => 'info',
		'message' => esc_html__( 'It may take up to 24 hours for data to begin feeding. Please check back soon.', 'ga_trans' ),
		'dismiss' => true,
		'hidden'  => true,
	] );

endif;
?>

<?php if ( Permission::has_report_cap( 'general', 'summary', 'dashboard', $network ) ) : ?>
	<?php
	$this->view( 'stats/google/dashboard-widget/elements/chart-options', [
		'id'      => 'analytics-general-stats',
		'button'  => false,
		'inline'  => false,
		'network' => $network,
	] );
	?>
<?php endif; ?>

<?php if ( Permission::has_report_cap( 'general', 'top_pages', 'dashboard', $network ) || Permission::has_report_cap( 'general', 'top_countries', $network ) ) : ?>

	<div class="beehive-row">

		<?php if ( Permission::has_report_cap( 'general', 'top_pages', 'dashboard', $network ) ) : ?>
			<div class="beehive-col">

				<?php
				$this->view( 'stats/google/dashboard-widget/elements/table', [
					'id'      => 'analytics-general-pages',
					'class'   => 'beehive-table-alt',
					'caption' => esc_html__( 'Top 5 visited pages.', 'ga_trans' ),
					'headers' => [
						[
							'col'   => 8,
							'title' => esc_html__( 'Top Pages', 'ga_trans' ),
						],
						[
							'col'   => 4,
							'title' => esc_html__( 'Visits', 'ga_trans' ),
						],
					],
				] );
				?>

			</div>
		<?php endif; ?>

		<?php if ( Permission::has_report_cap( 'general', 'top_countries', 'dashboard', $network ) ) : ?>
			<div class="beehive-col">

				<?php
				$this->view( 'stats/google/dashboard-widget/elements/table', [
					'id'      => 'analytics-general-countries',
					'class'   => 'beehive-table-alt',
					'caption' => esc_html__( 'Top 5 countries visiting your website.', 'ga_trans' ),
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
		<?php endif; ?>

	</div>

<?php endif; ?>