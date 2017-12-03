<?php
/**
 * Performance meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @since 1.7.0
 * @var bool   $notifications    Performance cron reports status.
 * @var string $url              Url to performance module.
 */

?>
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost">
		<i class="hb-wpmudev-icon-eye wphb-dash-icon"></i>
		<?php esc_html_e( 'View Full Report', 'wphb' ); ?>
	</a>
	<span class="status-text alignright">
		<?php $notifications ? esc_html_e( 'Automated performance tests are enabled', 'wphb' ) : esc_html_e( 'Automated performance tests are disabled', 'wphb' ); ?>
	</span>
</div>