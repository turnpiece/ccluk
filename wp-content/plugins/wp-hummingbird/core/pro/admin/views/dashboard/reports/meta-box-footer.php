<?php
/**
 * Reports meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @since 1.7.0
 * @var string $caching_url  Url to reports module.
 */

?>
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost" name="submit">
		<i class="hb-wpmudev-icon-wrench-tool wphb-dash-icon"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
</div>