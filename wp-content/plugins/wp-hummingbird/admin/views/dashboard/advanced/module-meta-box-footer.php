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

<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost">
		<i class="hb-wpmudev-icon-wrench-tool wphb-dash-icon"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
</div>