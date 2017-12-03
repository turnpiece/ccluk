<?php
/**
 * Gzip meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $gzip_url  Url to gzip module.
 */

?>
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $gzip_url ); ?>" class="button button-ghost">
		<i class="hb-wpmudev-icon-wrench-tool wphb-dash-icon"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
</div>