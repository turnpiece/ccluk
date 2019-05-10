<?php
/**
 * Disabled Page caching meta box.
 *
 * @package Hummingbird
 *
 * @var string $activate_url  Activation URL.
 */

?>

<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
	<img class="sui-image"
		src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-pagecaching-disabled.png' ); ?>"
		srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-pagecaching-disabled@2x.png' ); ?> 2x"
		alt="<?php esc_attr_e( 'Page Caching', 'wphb' ); ?>">
<?php endif; ?>

<div class="sui-message-content">
	<p>
		<?php
		esc_html_e(
			'Page caching stores static HTML copies of your pages and posts. These static files are then
			served to visitors, reducing the processing load on the server and dramatically speeding up
			your page load time. It’s probably the best performance feature ever.',
			'wphb'
		);
		?>
	</p>

	<a href="<?php echo esc_url( $activate_url ); ?>" class="sui-button sui-button-blue" id="activate-page-caching">
		<?php esc_html_e( 'Activate', 'wphb' ); ?>
	</a>
</div>
