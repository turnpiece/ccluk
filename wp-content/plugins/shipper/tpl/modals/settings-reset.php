<?php
/**
 * Shipper modal template partials: reset settings cancel modal
 *
 * @package shipper
 */

?>

<div class="sui-dialog sui-dialog-sm" id="shipper-settings-reset-dialog" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<h3 class="sui-dialog-title">
					<?php esc_html_e( 'Reset Settings', 'shipper' ); ?>
				</h3>
				<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
			</div>
			<div class="sui-box-body">
				<p>
					<?php esc_html_e( 'Are you sure you want to reset Shipper’s settings back to the factory defaults?', 'shipper' ); ?>
				</p>

				<div class="shipper-actions">
					<button class="sui-button sui-button-ghost shipper-goback">
						<?php esc_html_e( 'Cancel', 'shipper' ); ?>
					</button>
					<button class="sui-button sui-button-ghost sui-button-red shipper-reset">
						<i class="sui-icon-undo" aria-hidden="true"></i>
						<?php esc_html_e( 'Reset', 'shipper' ); ?>
					</button>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>
