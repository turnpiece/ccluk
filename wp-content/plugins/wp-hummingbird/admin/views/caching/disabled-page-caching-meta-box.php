<?php
/**
 * Disabled Page caching meta box.
 *
 * @package Hummingbird
 *
 * @var string $activate_url  Activation URL.
 */

?>

<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
	 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-pagecaching-disabled.png' ); ?>"
	 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-pagecaching-disabled@2x.png' ); ?> 2x"
	 alt="<?php esc_attr_e( 'Page Caching', 'wphb' ); ?>">

<p class="sui-margin-bottom">
	<?php _e( 'Page caching stores static HTML copies of your pages and posts. These static files are then<br>
		served to visitors, reducing the processing load on the server and dramatically speeding up<br>
		your page load time. It’s probably the best performance feature ever.', 'wphb' ); ?>
</p>

<a href="<?php echo esc_url( $activate_url ); ?>" class="sui-button sui-button-primary" id="activate-page-caching">
	<?php esc_html_e( 'Activate', 'wphb' ); ?>
</a>