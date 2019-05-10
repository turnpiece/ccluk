<?php
/**
 * Performance historic field data meta box.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var null|stdClass $field_data   Field data object. Null, if none available.
 * @var int           $fcp_fast     FCP fast data.
 * @var int           $fcp_average  FCP average data.
 * @var int           $fcp_slow     FCP slow data.
 * @var int           $fid_fast     FID fast data.
 * @var int           $fid_average  FID average data.
 * @var int           $fid_slow     FID slow data.
 */

?>

<p>
	<?php
	printf(
		/* translators: %1$s - starting a tag, %2$s - ending a tag */
		esc_html__( 'The field data is a historical report about how a particular URL has performed, and represents anonymized performance data from users in the real-world on a variety of devices and network conditions. We use %1$sChrome User Experience Report%2$s to generate insights about the real users’ experience with your webpage over the last 30 days.', 'wphb' ),
		'<a href="https://developers.google.com/web/tools/chrome-user-experience-report/" target="_blank">',
		'</a>'
	);
	?>
</p>

<?php if ( ! $field_data ) : ?>
	<div class="sui-notice">
		<p>
			<?php esc_html_e( 'The Chrome User Experience Report does not have sufficient real-world speed data for this page. Note: This report can take months to populate and is aimed at well established websites.', 'wphb' ); ?>
		</p>
	</div>
<?php else : ?>

<div class="sui-row">
	<div class="sui-col">
		<div class="wphb-border-frame">
			<div class="table-header">
				<strong><?php esc_html_e( 'First Contentful Paint (FCP)', 'wphb' ); ?></strong>
				<?php
				switch ( $field_data->FIRST_CONTENTFUL_PAINT_MS->category ) {
					case 'FAST':
						echo '<i class="sui-icon-check-tick sui-success sui-md" aria-hidden="true"></i>';
						break;
					case 'AVERAGE':
						echo '<i class="sui-icon-warning-alert sui-warning sui-md" aria-hidden="true"></i>';
						break;
					case 'SLOW':
					default:
						echo '<i class="sui-icon-warning-alert sui-error sui-md" aria-hidden="true"></i>';
						break;
				}
				?>
			</div>

			<div class="table-content sui-padding-left sui-padding-right sui-padding-top">
				<p class="sui-description">
					<?php esc_html_e( 'FCP is the point when the browser renders the first bit of content from the DOM - text, an image, SVG, or even a canvas element.', 'wphb' ); ?>
				</p>
			</div>

			<div class="table-content sui-padding-top sui-padding-left sui-padding-right">
				<strong><?php esc_html_e( 'Category', 'wphb' ); ?></strong>
				<span><?php echo esc_html( ucfirst( strtolower( $field_data->FIRST_CONTENTFUL_PAINT_MS->category ) ) ); ?></span>
			</div>

			<div class="table-content sui-padding-left sui-padding-right">
				<strong><?php esc_html_e( 'Avg. FCP', 'wphb' ); ?></strong>
				<span>
					<?php
					/* translators: %s - number of seconds */
					printf( '%s s', esc_html( round( $field_data->FIRST_CONTENTFUL_PAINT_MS->percentile / 1000, 1 ) ) );
					?>
				</span>
			</div>

			<hr>

			<div class="table-content sui-padding-left sui-padding-right sui-padding-top">
				<p class="sui-description">
					<?php esc_html_e( 'Following is the distribution of all the page loads into different FCP categories.', 'wphb' ); ?>
				</p>
			</div>

			<div class="sui-padding-left sui-padding-right sui-padding-bottom">
				<div id="first_contentful_paint"></div>

				<div class="performance-chart-keys">
					<span class="fast-key">
						<?php esc_html_e( 'Fast', 'wphb' ); ?><br>
						<small><?php echo absint( $fcp_fast ) . '%'; ?></small>
					</span>
					<span class="average-key">
						<?php esc_html_e( 'Average', 'wphb' ); ?><br>
						<small><?php echo absint( $fcp_average ) . '%'; ?></small>
					</span>
					<span class="slow-key">
						<?php esc_html_e( 'Slow', 'wphb' ); ?><br>
						<small><?php echo absint( $fcp_slow ) . '%'; ?></small>
					</span>
				</div>
			</div>
		</div>
	</div>

	<div class="sui-col">
		<div class="wphb-border-frame">
			<div class="table-header">
				<strong><?php esc_html_e( 'First Input Delay (FID)', 'wphb' ); ?></strong>
				<?php
				switch ( $field_data->FIRST_INPUT_DELAY_MS->category ) {
					case 'FAST':
						echo '<i class="sui-icon-check-tick sui-success sui-md" aria-hidden="true"></i>';
						break;
					case 'AVERAGE':
						echo '<i class="sui-icon-warning-alert sui-warning sui-md" aria-hidden="true"></i>';
						break;
					case 'SLOW':
					default:
						echo '<i class="sui-icon-warning-alert sui-error sui-md" aria-hidden="true"></i>';
						break;
				}
				?>
			</div>

			<div class="table-content sui-padding-left sui-padding-right sui-padding-top">
				<p class="sui-description">
					<?php esc_html_e( 'FID measure the time that the browser takes to respond to the user\'s first interaction with your page while the page is still loading.', 'wphb' ); ?>
				</p>
			</div>

			<div class="table-content sui-padding-top sui-padding-left sui-padding-right">
				<strong><?php esc_html_e( 'Category', 'wphb' ); ?></strong>
				<span><?php echo esc_html( ucfirst( strtolower( $field_data->FIRST_INPUT_DELAY_MS->category ) ) ); ?></span>
			</div>

			<div class="table-content sui-padding-left sui-padding-right">
				<strong><?php esc_html_e( 'Avg. FID', 'wphb' ); ?></strong>
				<span>
					<?php
					/* translators: %s - number of milliseconds */
					printf( '%s ms', esc_html( $field_data->FIRST_INPUT_DELAY_MS->percentile ) );
					?>
				</span>
			</div>

			<hr>

			<div class="table-content sui-padding-left sui-padding-right sui-padding-top">
				<p class="sui-description">
					<?php esc_html_e( 'Following is the distribution of all the page loads into different FID categories.', 'wphb' ); ?>
				</p>
			</div>

			<div class="sui-padding-left sui-padding-right sui-padding-bottom">
				<div id="first_input_delay"></div>

				<div class="performance-chart-keys">
					<span class="fast-key">
						<?php esc_html_e( 'Fast', 'wphb' ); ?><br>
						<small><?php echo absint( $fid_fast ) . '%'; ?></small>
					</span>
					<span class="average-key">
						<?php esc_html_e( 'Average', 'wphb' ); ?><br>
						<small><?php echo absint( $fid_average ) . '%'; ?></small>
					</span>
					<span class="slow-key">
						<?php esc_html_e( 'Slow', 'wphb' ); ?><br>
						<small><?php echo absint( $fid_slow ) . '%'; ?></small>
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>
