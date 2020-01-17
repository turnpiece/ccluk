<?php
/**
 * Shipper messages: wizard ready to sail error notice template
 *
 * @package shipper
 */

$local = $result['checks']['local'];
$local_errs = (int) $local['breaking_errors_count'];

$remote = $result['checks']['remote'];
$remote_errs = (int) $remote['breaking_errors_count'];
?>
<div class="sui-notice sui-notice-error shipper-main-error">
	<p>

		<?php if ( ! empty( $local_errs ) && empty( $remote_errs ) ) { ?>
			<?php echo wp_kses_post(
				__('We\'ve encountered potential migration issues with your <a href="#shipper-tab-source">source</a> website.', 'shipper' ) ); ?>
		<?php } else if ( empty( $local_errs ) && ! empty( $remote_errs ) ) { ?>
			<?php echo wp_kses_post(
				__('We\'ve encountered potential migration issues with your <a href="#shipper-tab-destination">destination</a> website.', 'shipper' ) ); ?>
		<?php } else if ( ! empty( $local_errs ) && ! empty( $remote_errs ) ) { ?>
			<?php echo wp_kses_post(
				__('We\'ve encountered potential migration issues with both your <a href="#shipper-tab-source">source</a> and <a href="#shipper-tab-destination">destination</a> website.', 'shipper' ) ); ?>
		<?php } else { ?>
			<?php echo wp_kses_post(
				__('We\'ve encountered potential migration issues.', 'shipper' ) ); ?>
		<?php } ?>

		<?php esc_html_e( 'Take a look at what we\'ve picked up and adjust as necessary.', 'shipper' ); ?>
		<?php esc_html_e( 'When you\'re ready, re-run the pre-flight check to see if you\'ve fixed the issues.', 'shipper' ); ?>
	</p>
</div>