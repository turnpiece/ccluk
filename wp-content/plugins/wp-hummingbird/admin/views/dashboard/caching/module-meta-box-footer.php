<?php
/**
 * Browser caching meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $caching_url  Url to browser caching module.
 * @var bool $cf_active      Is CloudFlare connected.
 */

?>
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $caching_url ); ?>" class="button button-ghost" name="submit">
		<i class="hb-wpmudev-icon-wrench-tool wphb-dash-icon"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
	<?php if ( $cf_active ) : ?>
		<span class="status-text alignright dash-cloudflare-connected-status"><?php esc_html_e( 'CloudFlare is connected', 'wphb' ); ?></span>
	<?php endif; ?>
</div>