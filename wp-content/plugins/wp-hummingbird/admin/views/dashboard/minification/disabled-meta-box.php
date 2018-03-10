<?php
/**
 * Asset optimization disabled meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $minification_url  URL to minification module.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Compress, combine and position your assets to dramatically improve your pageload speed.', 'wphb' ); ?></p>
</div>

<div class="buttons">
	<a href="<?php echo esc_url( $minification_url ); ?>" class="button" id="minifiy-website">
		<?php esc_html_e( 'Activate', 'wphb' ); ?>
	</a>
</div>