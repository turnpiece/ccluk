<?php
/**
 * Shipper message templates: migration destination delete success
 *
 * @package shipper
 */

?>
<div style="display:none"
	id="migration-notice-inline-dismiss"
	class="sui-notice-top sui-notice-success sui-can-dismiss shipper-destdelete-success">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: destination site. */
					__( '%s has been successfully removed from your destinations.', 'shipper' ),
					'<b class="shipper-destdelete-target"></b>'
				)
			);
			?>
			</p>
		</div>
	</div>

	<div class="sui-notice-actions">
		<button class="sui-button-icon" data-notice-close="migration-notice-inline-dismiss">
			<i class="sui-icon-check" aria-hidden="true"></i>
			<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
		</button>
	</div>
</div>