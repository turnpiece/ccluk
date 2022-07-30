<?php
/**
 * Shipper settings: Data subpage template
 *
 * @package shipper
 */

$model             = new Shipper_Model_Stored_Options();
$preserve_settings = $model->get( Shipper_Model_Stored_Options::KEY_SETTINGS );
$preserve_data     = $model->get( Shipper_Model_Stored_Options::KEY_DATA );
?>
<div class="sui-box shipper-page-settings-data">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Data', 'shipper' ); ?></h2>
	</div>

	<form method="POST">

	<input type="hidden"
		name="data[shipper-nonce]"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper-data' ) ); ?>" />

	<div class="sui-box-body">
		<p>
			<?php esc_html_e( 'Control what to do with your settings and data.', 'shipper' ); ?>
			<?php esc_html_e( 'Settings are your plugin settings such as acessibiliy, data and notifications settings.', 'shipper' ); ?>
			<?php esc_html_e( 'Data includes the transient information such as migration logs and requirements check data collected over the time.', 'shipper' ); ?>
		</p>

		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<label class="sui-settings-label"><?php esc_html_e( 'Uninstallation', 'shipper' ); ?></label>
				<p class="shipper-description">
					<?php esc_html_e( 'When you uninstall this plugin, what do you want to do with your settings and data?', 'shipper' ); ?>
				</p>
			</div>

			<div class="sui-box-settings-col-2">
				<div class="shipper-form-item">
					<label class="sui-settings-label"><?php esc_html_e( 'Settings', 'shipper' ); ?></label>
					<p class="shipper-description">
						<?php esc_html_e( 'Choose whether to save your settings for next time, or reset them.', 'shipper' ); ?>
					</p>
					<div class="shipper-data-toggles">
						<label data-active="<?php echo esc_attr( $preserve_settings ? 1 : 0 ); ?>">
							<input
								type="radio"<?php checked( $preserve_settings ); ?>
								name="data[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_SETTINGS ); ?>]"
								value="preserve"
							>
							<span><?php esc_html_e( 'Preserve', 'shipper' ); ?></span>
						</label>
						<label data-active="<?php echo esc_attr( $preserve_settings ? 0 : 1 ); ?>">
							<input
								type="radio"<?php checked( empty( $preserve_settings ) ); ?>
								name="data[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_SETTINGS ); ?>]"
								value=""
							>
							<span><?php esc_html_e( 'Reset', 'shipper' ); ?></span>
						</label>
					</div>
				</div>

				<div class="shipper-form-item">
					<label class="sui-settings-label"><?php esc_html_e( 'Data', 'shipper' ); ?></label>
					<p class="shipper-description">
						<?php esc_html_e( 'Choose whether to keep or remove transient data.', 'shipper' ); ?>
					</p>
					<div class="shipper-data-toggles">
						<label data-active="<?php echo esc_attr( $preserve_data ? 1 : 0 ); ?>">
							<input
								type="radio"<?php checked( $preserve_data ); ?>
								name="data[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_DATA ); ?>]"
								value="preserve"
							>
							<span><?php esc_html_e( 'Keep', 'shipper' ); ?></span>
						</label>
						<label data-active="<?php echo esc_attr( $preserve_data ? 0 : 1 ); ?>">
							<input
								type="radio"<?php checked( empty( $preserve_data ) ); ?>
								name="data[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_DATA ); ?>]"
								value=""
							>
							<span><?php esc_html_e( 'Remove', 'shipper' ); ?></span>
						</label>
					</div>
				</div>

			</div>
		</div>

		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<label class="sui-settings-label"><?php esc_html_e( 'Reset Settings', 'shipper' ); ?></label>
				<p class="shipper-description">
					<?php esc_html_e( 'Needing to start fresh?', 'shipper' ); ?>
					<?php esc_html_e( 'Use this button to roll back to the default settings.', 'shipper' ); ?>
				</p>
			</div>
			<div class="sui-box-settings-col-2">
				<div class="shipper-form-item">
					<button
						data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'shipper_reset_settings' ) ); ?>"
						class="sui-button sui-button-ghost shipper-reset-settings">
						<i class="sui-icon-undo" aria-hidden="true"></i>
						<?php esc_html_e( 'Reset', 'shipper' ); ?>
					</button>
					<p class="shipper-description">
						<?php esc_html_e( 'Note: This will instantly revert all settings to their default states without touching your data.', 'shipper' ); ?>
					</p>
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

<?php $this->render( 'modals/settings-reset' ); ?>

</div>