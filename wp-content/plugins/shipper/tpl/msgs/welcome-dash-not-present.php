<?php
/**
 * Shipper dash notice templates: plugin not present
 *
 * @package shipper
 */

?>
<div class="sui-notice sui-notice-warning">
	<p>
		<?php echo wp_kses_post( sprintf(
			__( 'Whoops, it appears you still haven\'t installed the WPMU DEV Dashboard plugin. Click <a href="%s" target="_blank">here</a> to download the plugin.', 'shipper' ),
			esc_url( $action )
		) ); ?>
	</p>
</div>