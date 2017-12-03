<?php
/**
 * Smush meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $url  Url to smush module.
 */

?>
<div class="buttons buttons-on-left">
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost" id="smush-link">
		<i class="hb-wpmudev-icon-wrench-tool wphb-dash-icon"></i>
		<?php esc_html_e( 'Configure', 'wphb' ); ?>
	</a>
</div>