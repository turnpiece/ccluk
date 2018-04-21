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
<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
	<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
	<?php esc_html_e( 'Configure', 'wphb' ); ?>
</a>
<div class="sui-actions-right">
	<span class="status-text">
		<?php $cdn_status ? esc_html_e( 'WPMU DEV CDN is active', 'wphb' ) : esc_html_e( 'WPMU DEV CDN is disabled', 'wphb' ); ?>
	</span>
</div>