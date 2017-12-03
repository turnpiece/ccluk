<?php
/**
 * Gravatar caching meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $caching_url     Caching URL.
 * @var string $activate_url    Activate URL.
 * @var bool   $is_active       Currently active.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Store local copies of Gravatars to avoid your visitors loading them on every page load.', 'wphb' ); ?></p>
	<?php if ( $is_active ) { ?>
		<div class="wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'Gravatar caching is currently active.', 'wphb' ); ?></p>
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