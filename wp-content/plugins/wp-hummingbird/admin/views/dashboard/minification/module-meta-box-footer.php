<?php
/**
 * Asset optimization meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @since 1.7.0
 * @var bool   $cdn_status  CDN status.
 * @var string $url         Url to minification module.
 */

?>
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost">
		<i class="hb-wpmudev-icon-wrench-tool wphb-dash-icon"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
	<span class="status-text alignright">
		<?php $cdn_status ? esc_html_e( 'WPMU DEV CDN is active', 'wphb' ) : esc_html_e( 'WPMU DEV CDN is disabled', 'wphb' ); ?>
	</span>
</div>