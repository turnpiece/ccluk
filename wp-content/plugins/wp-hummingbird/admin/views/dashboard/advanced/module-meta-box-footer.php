<?php
/**
 * Advanced tools meta box footer.
 *
 * @since 1.8
 * @package Hummingbird
 *
 * @var string $url  Url to settings page.
 */

?>

<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
	<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
	<?php esc_html_e( 'Configure', 'wphb' ); ?>
</a>