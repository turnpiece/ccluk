<?php
/**
 * Shipper modal template partials: preflight cancel modal
 *
 * @package shipper
 */

?>

<div class="sui-modal sui-modal-sm" aria-hidden="true">
	<div
		role="dialog"
		id="shipper-preflight-cancel-dialog"
		class="sui-modal-content"
		>
		<div class="sui-box" role="document">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
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

	</div><?php // .sui-modal-content ?>
</div><?php // .sui-modal ?>