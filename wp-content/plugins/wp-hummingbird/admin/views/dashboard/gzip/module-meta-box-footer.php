<?php
/**
 * Gzip meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $gzip_url  Url to gzip module.
 */

?>
<a href="<?php echo esc_url( $gzip_url ); ?>" class="sui-button sui-button-ghost">
	<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
	<?php esc_html_e( 'Configure', 'wphb' ); ?>
</a>