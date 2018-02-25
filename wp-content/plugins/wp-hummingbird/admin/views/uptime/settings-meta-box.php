<?php
/**
 * Uptime settings meta box.
 *
 * @package Hummingbird
 *
 * @var string $deactivate_url Deactivate URL.
 */

?>
<div class="settings-form">
	<div class="col-third">
		<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'If you no longer wish to use Hummingbird’s Uptime Monitor you can turn it off completely.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="col-two-third">
		<a id="wphb-disable-uptime" href="#"
		   class="button button-ghost"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
		<span class="spinner standalone"></span>
	</div>
</div>
<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'uptime' );
	});
</script>