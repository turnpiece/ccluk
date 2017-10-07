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
<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( ! $cf_active ) : ?>
	<div class="buttons buttons-group">
		<p class="wphb-label-notice-inline hide-to-mobile">
			<?php esc_html_e( 'Using Cloudflare?', 'wphb' ); ?>
			<a href="#" id="connect-cloudflare-link">
				<?php esc_html_e( 'Connect account', 'wphb' ); ?>
			</a>
		</p>
	</div>
<?php endif; ?>