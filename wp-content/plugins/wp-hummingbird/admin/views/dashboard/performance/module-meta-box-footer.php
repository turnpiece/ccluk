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
<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
	<i class="sui-icon-eye" aria-hidden="true"></i>
	<?php esc_html_e( 'View Full Report', 'wphb' ); ?>
</a>
<div class="sui-actions-right">
	<span class="status-text">
		<?php $notifications ? esc_html_e( 'Automated performance tests are enabled', 'wphb' ) : esc_html_e( 'Automated performance tests are disabled', 'wphb' ); ?>
	</span>
</div>