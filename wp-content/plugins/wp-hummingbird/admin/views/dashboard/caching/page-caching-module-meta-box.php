<?php
/**
 * Page caching meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $caching_url     Caching URL.
 * @var string $activate_url    Activate URL.
 * @var bool   $is_active       Currently active.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Store static HTML copies of your pages and posts to reduce the processing load on your server and dramatically speed up your page load time.', 'wphb' ); ?></p>
	<?php if ( $is_active ) { ?>
		<div class="wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'Page caching is currently active.', 'wphb' ); ?></p>
		</div>
	<?php } ?>
</div>

<?php if ( ! $is_active ) : ?>
	<div class="buttons">
		<a href="<?php echo esc_url( $activate_url ); ?>" class="button" id="activate-page-caching">
			<?php esc_html_e( 'Activate', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>