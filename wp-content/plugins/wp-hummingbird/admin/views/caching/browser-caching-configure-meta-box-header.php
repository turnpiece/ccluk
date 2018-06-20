<?php
/**
 * Browser caching settings header meta box.
 *
 * @package Hummingbird
 *
 * @var string $title      Title of the module.
 * @var bool   $cf_active  Cloudflare status.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( ! $cf_active ) : ?>
	<div class="sui-actions-right">
		<p class="wphb-label-notice-inline sui-hidden-xs sui-hidden-sm">
			<?php esc_html_e( 'Using Cloudflare?', 'wphb' ); ?>
			<a href="#" class="connect-cloudflare-link">
				<?php esc_html_e( 'Connect account', 'wphb' ); ?>
			</a>
		</p>
	</div>
<?php endif; ?>