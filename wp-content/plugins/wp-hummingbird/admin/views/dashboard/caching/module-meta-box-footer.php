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
<a href="<?php echo esc_url( $caching_url ); ?>" class="sui-button sui-button-ghost" name="submit">
	<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
	<?php esc_html_e( 'Configure', 'wphb' ); ?>
</a>
<?php if ( $cf_active ) : ?>
	<div class="sui-actions-right">
		<span class="status-text">
			<?php esc_html_e( 'CloudFlare is connected', 'wphb' ); ?>
		</span>
	</div>
<?php endif; ?>