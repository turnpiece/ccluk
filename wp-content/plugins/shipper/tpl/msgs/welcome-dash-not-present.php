<?php
/**
 * Shipper dash notice templates: plugin not present
 *
 * @package shipper
 */

?>
<div class="sui-notice sui-notice-warning">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: wpmudev dash plugin activation link. */
						__( 'Whoops, it appears you still haven\'t installed the WPMU DEV Dashboard plugin. Click <a href="%s" target="_blank">here</a> to download the plugin.', 'shipper' ),
						esc_url( $action )
					)
				);
				?>
			</p>
		</div>
	</div>
</div>