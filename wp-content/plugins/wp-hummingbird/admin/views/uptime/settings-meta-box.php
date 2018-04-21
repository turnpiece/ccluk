<?php
/**
 * Uptime settings meta box.
 *
 * @package Hummingbird
 *
 * @var string $deactivate_url Deactivate URL.
 */

?>
<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'If you no longer wish to use Hummingbird’s Uptime Monitor you can turn it off completely.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="sui-box-settings-col-2">
		<a id="wphb-disable-uptime" href="#"
		   class="sui-button sui-button-ghost"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
		<span class="spinner standalone"></span>
	</div>
</div>
<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'uptime' );
	});
</script>