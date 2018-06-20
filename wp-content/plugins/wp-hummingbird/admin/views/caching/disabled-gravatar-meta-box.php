<?php
/**
 * Disabled Gravatar caching meta box.
 *
 * @package Hummingbird
 *
 * @var string $activate_url  Activation URL.
 */

?>

<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
	 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-gravatarcaching-disabled.png' ); ?>"
	 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-gravatarcaching-disabled@2x.png' ); ?> 2x"
	 alt="<?php esc_attr_e( 'Gravatar Caching', 'wphb' ); ?>">

<p class="sui-margin-bottom">
	<?php _e( 'Gravatar Caching stores local copies of avatars used in comments and in your theme. You<br>
		can control how often you want the cache purged depending on how your website is set up.', 'wphb' ); ?>
</p>

<a href="<?php echo esc_url( $activate_url ); ?>" class="sui-button sui-button-primary" id="activate-page-caching">
	<?php esc_html_e( 'Activate', 'wphb' ); ?>
</a>