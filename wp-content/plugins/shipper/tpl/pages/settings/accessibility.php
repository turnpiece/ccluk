<?php
/**
 * Shipper settings: Accessibility subpage template
 *
 * @package shipper
 */

$model    = new Shipper_Model_Stored_Options();
$use_a11n = $model->get( Shipper_Model_Stored_Options::KEY_A11N );
?>
<div class="sui-box shipper-page-settings-accessibility">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Accessibility', 'shipper' ); ?></h2>
	</div>

	<form method="POST">

	<input type="hidden"
		name="accessibility[shipper-nonce]"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper-accessibility' ) ); ?>" />

	<div class="sui-box-body">
		<p><?php esc_html_e( 'Enable support for any accessibility enhancements available in the plugin interface.', 'shipper' ); ?></p>

		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<label class="sui-settings-label"><?php esc_html_e( 'High Contrast Mode', 'shipper' ); ?></label>
				<span class="shipper-description"><?php esc_html_e( 'Increase the visibility and accessibility of the elements and components of the plugin to meet WCAG AAA requirements.', 'shipper' ); ?></span>
			</div>

			<div class="sui-box-settings-col-2">
				<div class="shipper-form-item">
					<label for="shipper-use-a11n" class="sui-toggle">
						<input
							type="checkbox" <?php checked( $use_a11n ); ?>
							id="shipper-use-a11n"
							name="accessibility[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_A11N ); ?>]"
							value="1"
							aria-labelledby="shipper-high-contrast-mode"
						>
						<span class="sui-toggle-slider" area-hidden="true"></span>
						<span id="shipper-high-contrast-mode" class="sui-toggle-label">
							<?php esc_html_e( 'Enable high contrast mode', 'shipper' ); ?>
						</span>
					</label>
				</div>
			</div>
		</div>

	</div>

	<div class="sui-box-footer shipper-settings-footer">
		<div class="sui-col">
			<div class="shipper-actions">
				<button class="sui-button sui-button-primary">
					<?php esc_html_e( 'Save changes', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</div>

	</form>

</div>