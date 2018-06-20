<?php
/**
 * Asset optimization disabled meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $minification_url  URL to minification module.
 */

?>
<p><?php esc_html_e( 'Compress, combine and position your assets to dramatically improve your page load speed.', 'wphb' ); ?></p>

<a href="<?php echo esc_url( $minification_url ); ?>" class="sui-button sui-button-primary" id="minifiy-website">
	<?php esc_html_e( 'Activate', 'wphb' ); ?>
</a>