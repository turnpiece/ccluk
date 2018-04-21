<?php
/**
 * Uptime meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @since 1.7.0
 * @var string $url  Url to uptime module.
 */

?>
<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
	<i class="sui-icon-eye" aria-hidden="true"></i>
	<?php esc_html_e( 'View stats', 'wphb' ); ?>
</a>
<div class="sui-actions-right">
	<span class="status-text">
		<?php esc_html_e( 'Downtime notifications are enabled', 'wphb' ); ?>
	</span>
</div>