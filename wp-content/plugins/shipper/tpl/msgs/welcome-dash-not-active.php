<?php
/**
 * Shipper dash notice templates: plugin not active
 *
 * @package shipper
 */

?>
<div class="sui-notice sui-notice-warning">
	<p>
		<?php echo wp_kses_post( sprintf(
			__( 'Whoops, it appears you still haven\'t activated the WPMU DEV Dashboard plugin. Click <a href="%s">here</a> to activate the plugin and then login with your WPMU DEV account details.', 'shipper' ),
			esc_url( $action )
		) ); ?>
	</p>
</div>
