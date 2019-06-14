<?php
/**
 * Shipper modal template partials: preflight cancel modal
 *
 * @package shipper
 */

?>

<div class="sui-dialog sui-dialog-sm" id="shipper-preflight-cancel-dialog" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<h3 class="sui-dialog-title">
					<?php esc_html_e( 'Cancel Pre-Flight Check', 'shipper' ); ?>
				</h3>
				<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
			</div>
			<div class="sui-box-body">
				<p>
					<?php esc_html_e( 'Are you sure you want to cancel the pre-flight check?', 'shipper' ); ?>
					<?php esc_html_e( 'You’ll have to perform the pre-flight check again if you cancel it.', 'shipper' ); ?>
				</p>

				<div class="shipper-actions">
					<button class="sui-button sui-button-ghost shipper-preflight-continue">
						<?php esc_html_e( 'Go back', 'shipper' ); ?>
					</button>
					<button class="sui-button sui-button-ghost sui-button-red shipper-preflight-cancel">
						<?php esc_html_e( 'Cancel Pre-Flight Check', 'shipper' ); ?>
					</button>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>
