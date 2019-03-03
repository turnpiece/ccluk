<?php
/**
 * Shipper settings: Migration subpage template
 *
 * @package shipper
 */

$constants = new Shipper_Model_Constants_Shipper;
$use_uploads = $constants->is_defined( 'WORKING_DIRECTORY_ROOT' );
if ( empty( $use_uploads ) ) {
	$model = new Shipper_Model_Stored_Options;
	$use_uploads = $model->get( Shipper_Model_Stored_Options::KEY_UPLOADS );
}
?>
<div class="sui-box shipper-page-settings-migration">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Migration', 'shipper' ); ?></h2>
	</div>

	<form method="POST">

	<input type="hidden"
		name="migration[shipper-nonce]"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper-migration' ) ); ?>" />

	<div class="sui-box-body">

		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<label class="sui-settings-label"><?php esc_html_e( 'Working Directory', 'shipper' ); ?></label>
				<span class="sui-description">
					<?php esc_html_e(
						'Shipper temporarily stores progress information and other temporary files generated while migration in a working directory.',
						'shipper'
					); ?>
				</span>
			</div>

			<div class="sui-box-settings-col-2">
				<div class="shipper-form-item">
					<label class="sui-toggle">
						<input type="checkbox" <?php checked( $use_uploads ); ?>
						       id="shipper-use-uploads"
						       name="migration[<?php echo Shipper_Model_Stored_Options::KEY_UPLOADS; ?>]"
						       value="1"/>
						<span class="sui-toggle-slider"></span>
					</label>
					<label for="shipper-use-uploads" class="sui-toggle-label">
						<?php esc_html_e( 'Use uploads directory as working directory', 'shipper' ); ?>
					</label>
					<div class="sui-toggle-content">
						<p class="sui-description">
							<?php esc_html_e(
								'By default, we use the temp directory as the working directory. However, because of some restrictions implied by few hosts, the migration tends to fail. In such cases, you can try enabling this option where we’ll create a working directory in your uploads folder. ',
								'shipper'
							); ?>
						</p>
					</div>
				</div>

				<?php if ( $use_uploads ) { ?>
					<div class="sui-notice sui-notice-warning">
						<div class="sui-notice-content">
							<p><?php echo esc_html(
								__( 'Since the uploads folder is web accessible, your temporary data will be accessible on web until migration completes.', 'shipper' )
							); ?></p>
						</div>
					</div>
				<?php } ?>

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