<?php
/**
 * Performance report dismissed meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var bool   $notifications     Performance cron reports status.
 * @var bool   $disabled          Last scan done to soon.
 * @var string $scan_link         URL to perform new scan.
 */

?>
<div class="content">
	<div class="wphb-notice wphb-notice-grey-info">
		<p><?php esc_html_e( 'You chose to ignore your last performance test. Run a new test to see new recommendations.', 'wphb' ); ?></p>
	</div>
</div>

<div class="buttons">
	<?php if ( ! $disabled ) : ?>
		<a href="<?php echo esc_url( $scan_link ); ?>" <?php disabled( $disabled ); ?> class="button"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
	<?php endif; ?>
	<span class="status-text alignright">
		<?php $notifications ? esc_html_e( 'Automated performance tests are enabled', 'wphb' ) : esc_html_e( 'Automated performance tests are disabled', 'wphb' ); ?>
	</span>
</div>