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
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost">
		<i class="hb-wpmudev-icon-eye wphb-dash-icon"></i>
		<?php esc_html_e( 'View stats', 'wphb' ); ?>
	</a>
	<span class="status-text alignright">
		<?php esc_html_e( 'Downtime notifications are enabled', 'wphb' ); ?>
	</span>
</div>