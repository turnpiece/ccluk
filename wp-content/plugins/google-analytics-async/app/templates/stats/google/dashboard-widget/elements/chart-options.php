<?php
/**
 * Beehive Chart Options.
 *
 * @var string $id      Id.
 * @var bool   $button  Is button type?.
 * @var bool   $inline  Is inline?.
 * @var bool   $flushed Is flushed?.
 * @var bool   $network Network flag.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Permission;

$class = 'beehive-options';

if ( isset( $flushed ) && true === $flushed ) {
	$class .= ' beehive-flushed';
}

$general_stats = [
	'sessions'         => [
		'title'   => esc_html__( 'Sessions', 'ga_trans' ),
		'tooltip' => esc_html__( 'Total number of Sessions within the date range. A session is the period of time user is actively engaged with your website, app, etc.', 'ga_trans' ),
	],
	'users'            => [
		'title'   => esc_html__( 'Users', 'ga_trans' ),
		'tooltip' => esc_html__( 'Users who have initiated at least one session during the date range.', 'ga_trans' ),
	],
	'pageviews'        => [
		'title'   => esc_html__( 'Pageviews', 'ga_trans' ),
		'tooltip' => esc_html__( 'Pageviews is the total number of pages viewed. Repeated views of a single page are counted.', 'ga_trans' ),
	],
	'page_sessions'    => [
		'title'   => esc_html__( 'Pages/Sessions', 'ga_trans' ),
		'tooltip' => esc_html__( 'Pages/Sessions (Average Page Depth) is the average number of pages viewed during a session. Repeated views of a single page are counted.', 'ga_trans' ),
	],
	'average_sessions' => [
		'title'   => esc_html__( 'Avg. time', 'ga_trans' ),
		'tooltip' => esc_html__( 'The average length of a session.', 'ga_trans' ),
	],
	'bounce_rates'     => [
		'title'   => esc_html__( 'Bounce Rate', 'ga_trans' ),
		'tooltip' => esc_html__( 'The percentage of single-page sessions in which there was no interaction with the page. A bounced session has a duration of 0 seconds.', 'ga_trans' ),
	],
];
?>

<div <?php echo empty( $id ) ? '' : 'id="beehive-' . esc_html( $id ) . '"'; ?> class="<?php echo esc_attr( $class ); ?>">

	<?php if ( true === $button ) : ?>

		<div role="tablist" class="beehive-options-tabs">

			<?php foreach ( $general_stats as $key => $results ) : ?>

				<?php if ( Permission::has_report_cap( 'audience', $key, 'dashboard', $network ) ) : // Only when capable. ?>

					<button role="tab" id="beehive-option-tab-<?php echo esc_attr( $key ); ?>" class="beehive-tab" data-tab="<?php echo esc_attr( $key ); ?>" data-title="<?php echo esc_html( $results['title'] ); ?>" aria-selected="false" tabindex="-1">
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
					</button>

				<?php endif; ?>

			<?php endforeach; ?>

		</div>

	<?php else : ?>

		<ul class="beehive-options-list">

			<?php foreach ( $general_stats as $key => $results ) : ?>

				<li data-type="<?php echo esc_attr( $key ); ?>" data-title="<?php echo esc_html( $results['title'] ); ?>" data-tooltip="<?php echo esc_html( $results['tooltip'] ); ?>">
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Stats are being loaded', 'ga_trans' ); ?></span>
				</li>

			<?php endforeach; ?>

		</ul>

	<?php endif; ?>

</div>