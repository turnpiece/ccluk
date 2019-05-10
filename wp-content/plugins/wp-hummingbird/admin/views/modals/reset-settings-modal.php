<?php
/**
 * Reset settings modal.
 *
 * @since 2.0.0
 * @package Hummingbird
 */

?>

<div class="dialog sui-dialog sui-dialog-sm wphb-reset-settings-modal" aria-hidden="true" id="wphb-reset-settings-modal">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>
	<div class="sui-dialog-content" aria-labelledby="resetSettings" aria-describedby="dialogDescription" role="dialog">
		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<h3 class="sui-box-title" id="resetSettings">
					<?php esc_html_e( 'Reset Settings', 'wphb' ); ?>
				</h3>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'Are you sure you want to reset Hummingbird’s settings back to the factory defaults?', 'wphb' ); ?></p>

				<div class="sui-block-content-center">
					<a class="sui-button sui-button-ghost" data-a11y-dialog-hide>
						<?php esc_html_e( 'Cancel', 'wphb' ); ?>
					</a>
					<a class="sui-button sui-button-ghost sui-button-red sui-button-icon-left" onclick="WPHB_Admin.settings.confirmReset()">
						<i class="sui-icon-trash" aria-hidden="true"></i>
						<?php esc_html_e( 'Reset settings', 'wphb' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
