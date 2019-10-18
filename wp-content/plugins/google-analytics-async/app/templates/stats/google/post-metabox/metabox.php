<?php

/**
 * The post meta box template.
 *
 * @var array  $content    Widget stats content.
 * @var string $url        Stats widget url.
 * @var array  $stats      Stats data.
 * @var string $start_date Period start date.
 * @var string $end_date   Period end date.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

$top_row = [
	'users'     => [
		'title'        => esc_html__( 'Users', 'ga_trans' ),
		'tooltip'      => esc_html__( 'Users who have initiated at least one session during the date range.', 'ga_trans' ),
		// translators: %1$s - Value, %2$s - Date.
		'screenreader' => sprintf( esc_html__( '%1$s users who have initiated at least one session between %2$s.', 'ga_trans' ), '<span class="beehive-value"></span>', '<span class="beehive-date"></span>' ),
	],
	'pageviews' => [
		'title'        => esc_html__( 'Pageviews', 'ga_trans' ),
		'tooltip'      => esc_html__( 'Pageviews is the total number of pages viewed. Repeated views of a single page are counted.', 'ga_trans' ),
		// translators: %1$s - Value, %2$s - Date.
		'screenreader' => sprintf( esc_html__( '%1$s views of this page between %2$s.', 'ga_trans' ), '<span class="beehive-value"></span>', '<span class="beehive-date"></span>' ),
	],
	'sessions'  => [
		'title'        => esc_html__( 'Sessions', 'ga_trans' ),
		'tooltip'      => esc_html__( 'Total number of Sessions within the date range. A session is the period of time user is actively engaged with your website, app, etc.', 'ga_trans' ),
		// translators: %1$s - Value, %2$s - Date.
		'screenreader' => sprintf( esc_html__( '%1$s number of sessions within %2$s.', 'ga_trans' ), '<span class="beehive-value"></span>', '<span class="beehive-date"></span>' ),
	],
];

$bottom_row = [
	'page_sessions'    => [
		'title'        => esc_html__( 'Pages/Sessions', 'ga_trans' ),
		'tooltip'      => esc_html__( 'Pages/Sessions (Average Page Depth) is the average number of pages viewed during a session. Repeated views of a single page are counted.', 'ga_trans' ),
		// translators: %1$s - Value, %2$s - Date.
		'screenreader' => sprintf( esc_html__( '%1$s page sessions between %2$s.', 'ga_trans' ), '<span class="beehive-value"></span>', '<span class="beehive-date"></span>' ),
	],
	'average_sessions' => [
		'title'        => esc_html__( 'Avg. Time', 'ga_trans' ),
		'tooltip'      => esc_html__( 'The average length of a session.', 'ga_trans' ),
		// translators: %1$s - Value, %2$s - Date.
		'screenreader' => sprintf( esc_html__( 'This page has been viewed for about %1$s between %2$s.', 'ga_trans' ), '<span class="beehive-value"></span>', '<span class="beehive-date"></span>' ),
	],
	'bounce_rates'     => [
		'title'        => esc_html__( 'Bounce Rate', 'ga_trans' ),
		'tooltip'      => esc_html__( 'The percentage of single-page sessions in which there was no interaction with the page. A bounced session has a duration of 0 seconds.', 'ga_trans' ),
		// translators: %1$s - Value, %2$s - Date.
		'screenreader' => sprintf( esc_html__( '%1$s of single-page sessions without interaction between %2$s.', 'ga_trans' ), '<span class="beehive-value"></span>', '<span class="beehive-date"></span>' ),
	],
];

?>

<?php wp_nonce_field( 'beehive_admin_nonce', 'beehive_admin_nonce' ); // This can be used for form processing. ?>

<div class="beehive-post-stats-wrap">

	<div class="sui-row">

		<?php foreach ( $top_row as $key => $stat ) : ?>

			<?php $value = isset( $stats[ $key ]['value'] ) ? $stats[ $key ]['value'] : '-'; ?>

			<?php
			// Empty value.
			if ( ! isset( $stats[ $key ]['trend'] ) || empty( $stats[ $key ]['value'] ) || '00:00:00' === $stats[ $key ]['value'] ) {
				$trend_class = 'none';
				$trend_value = '<i class="sui-icon-pause"></i>';
			} elseif ( is_numeric( $stats[ $key ]['trend'] ) && $stats[ $key ]['trend'] > 0 ) {
				$trend_class = 'up';
				$trend_value = abs( $stats[ $key ]['trend'] );
			} elseif ( is_numeric( $stats[ $key ]['trend'] ) && $stats[ $key ]['trend'] < 0 ) {
				$trend_class = 'down';
				$trend_value = abs( $stats[ $key ]['trend'] );
			} elseif ( is_numeric( $stats[ $key ]['trend'] ) && 0 === (int) $stats[ $key ]['trend'] ) {
				$trend_class = 'equal';
				$trend_value = '<i class="sui-icon-pause"></i>';
			}
			?>

			<div id="beehive-post-status-<?php echo esc_attr( $key ); ?>" class="sui-col-md-4">

				<div class="beehive-block">

					<h3 tabindex="0" class="sui-screen-reader-text"><?php echo $stat['screenreader']; // phpcs:disable ?></h3>

					<div tabindex="-1" class="beehive-block-wrapper" aria-hidden="true">

						<span class="beehive-tooltip" data-tooltip="<?php echo esc_html( $stat['tooltip'] ); ?>">
							<i class="sui-icon-info sui-md"></i>
						</span>

						<div class="beehive-block-content">

							<div class="beehive-block-content-top">
								<span class="beehive-name"><?php echo esc_html( $stat['title'] ); ?></span>
								<span class="beehive-value"><?php echo $value; ?></span>
							</div>

							<div class="beehive-block-content-bottom">
								<span class="beehive-date"><?php echo $start_date; ?> - <?php echo $end_date; ?></span>
								<span class="beehive-trend" data-trend="<?php echo $trend_class; ?>"><?php echo $trend_value; ?></span>
							</div>

						</div>

					</div>

				</div>

			</div>

		<?php endforeach; ?>

	</div>

	<div class="sui-row">

		<?php foreach ( $bottom_row as $key => $stat ) : ?>

			<?php $value = isset( $stats[ $key ]['value'] ) ? $stats[ $key ]['value'] : '-'; ?>

			<?php
			// Empty value.
			if ( ! isset( $stats[ $key ]['trend'] ) || empty( $stats[ $key ]['value'] ) || '00:00:00' === $stats[ $key ]['value'] ) {
				$trend_class = 'none';
				$trend_value = '<i class="sui-icon-pause"></i>';
			} elseif ( is_numeric( $stats[ $key ]['trend'] ) && $stats[ $key ]['trend'] > 0 ) {
				$trend_class = 'up';
				$trend_value = abs( $stats[ $key ]['trend'] );
			} elseif ( is_numeric( $stats[ $key ]['trend'] ) && $stats[ $key ]['trend'] < 0 ) {
				$trend_class = 'down';
				$trend_value = abs( $stats[ $key ]['trend'] );
			} elseif ( is_numeric( $stats[ $key ]['trend'] ) && 0 === (int) $stats[ $key ]['trend'] ) {
				$trend_class = 'equal';
				$trend_value = '<i class="sui-icon-pause"></i>';
			}
			?>

			<?php $value = 'bounce_rates' === $key && is_numeric( $value ) ? $value . '%' : $value; // Bounce rate is in % ?>

			<div id="beehive-post-status-<?php echo esc_attr( $key ); ?>" class="sui-col-md-4">

				<div class="beehive-block">

					<h3 tabindex="0" class="sui-screen-reader-text"><?php echo $stat['screenreader']; // phpcs:disable ?></h3>

					<div tabindex="-1" class="beehive-block-wrapper" aria-hidden="true">

						<span class="beehive-tooltip" data-tooltip="<?php echo esc_html( $stat['tooltip'] ); ?>">
							<i class="sui-icon-info sui-md"></i>
						</span>

						<div class="beehive-block-content">

							<div class="beehive-block-content-top">
								<span class="beehive-name"><?php echo esc_html( $stat['title'] ); ?></span>
								<span class="beehive-value"><?php echo $value; ?></span>
							</div>

							<div class="beehive-block-content-bottom">
								<span class="beehive-date"><?php echo $start_date; ?> - <?php echo $end_date; ?></span>
								<span class="beehive-trend" data-trend="<?php echo $trend_class; ?>"><?php echo $trend_value; ?></span>
							</div>

						</div>

					</div>

				</div>

			</div>

		<?php endforeach; ?>

	</div>

</div>