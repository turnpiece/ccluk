<?php
/**
 * Shipper settings: Migration subpage template
 *
 * @package shipper
 */

$constants = new Shipper_Model_Constants_Shipper();
$model     = new Shipper_Model_Stored_Options();

$use_uploads = $constants->is_defined( 'WORKING_DIRECTORY_ROOT' );
if ( empty( $use_uploads ) ) {
	$use_uploads = $model->get( Shipper_Model_Stored_Options::KEY_UPLOADS );
}

$skip_wpconfig = $model->get( Shipper_Model_Stored_Options::KEY_SKIPCONFIG );
$skip_emails   = $model->get( Shipper_Model_Stored_Options::KEY_SKIPEMAILS );
?>
<div class="sui-box shipper-page-settings-migration">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'API Migration', 'shipper' ); ?></h2>
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
					<?php esc_html_e( 'Shipper temporarily stores progress information and other temporary files generated while migration in a working directory.', 'shipper' ); ?>
				</span>
			</div>

			<div class="sui-box-settings-col-2">
				<div class="shipper-form-item">
					<label for="shipper-use-uploads" class="sui-toggle">
						<input
							type="checkbox" <?php checked( $use_uploads ); ?>
							id="shipper-use-uploads"
							name="migration[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_UPLOADS ); ?>]"
							value="1"
							aria-labelledby="shipper-use-uploads-label"
						>
						<span class="sui-toggle-slider" area-hidden="true"></span>
						<span id="shipper-use-uploads-label" class="sui-toggle-label">
							<?php esc_html_e( 'Use uploads directory as working directory', 'shipper' ); ?>
						</span>
					</label>
					<div class="sui-toggle-content">
						<p class="shipper-description">
							<?php esc_html_e( 'By default, we use the temp directory as the working directory. However, because of some restrictions implied by few hosts, the migration tends to fail. In such cases, you can try enabling this option where we’ll create a working directory in your uploads folder. ', 'shipper' ); ?>
						</p>
					</div>
				</div>

				<?php if ( $use_uploads ) { ?>
					<div class="sui-notice sui-notice-warning">
						<div class="sui-notice-content">
							<p><?php echo esc_html( __( 'Since the uploads folder is web accessible, your temporary data will be accessible on web until migration completes.', 'shipper' ) ); ?></p>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>

		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<label class="sui-settings-label"><?php esc_html_e( 'Advanced options', 'shipper' ); ?></label>
				<span class="sui-description">
					<?php esc_html_e( 'Have better control over your migrations with these advanced migration settings.', 'shipper' ); ?>
				</span>
			</div>

			<div class="sui-box-settings-col-2">

				<div class="shipper-form-item">
					<label for="shipper-skip-emails" class="sui-toggle">
						<input
							type="checkbox" <?php checked( $skip_emails ); ?>
							id="shipper-skip-emails"
							name="migration[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_SKIPEMAILS ); ?>]"
							value="1"
							aria-labelledby="shipper-skip-emails-label"
						>
						<span class="sui-toggle-slider" area-hidden="true"></span>
						<span for="shipper-skip-emails-label" class="sui-toggle-label">
							<?php esc_html_e( 'Do not replace domain name in email', 'shipper' ); ?>
						</span>
					</label>
					<div class="sui-toggle-content">
						<p class="shipper-description">
							<?php esc_html_e( 'By default, Shipper replaces all the instances of your source domain with the destination domain. However, if you use an email on your site linked with your source domain and don\'t want the email to be changed then keep this option enabled.', 'shipper' ); ?>
						</p>
					</div>
				</div>

				<div class="shipper-form-item">
					<label for="shipper-skip-config" class="sui-toggle">
						<input
							type="checkbox" <?php checked( $skip_wpconfig ); ?>
							id="shipper-skip-config"
							name="migration[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_SKIPCONFIG ); ?>]"
							value="1"
							aria-labelledby="shipper-skip-config-label"
						>
						<span class="sui-toggle-slider" aria-hidden="true"></span>
						<span for="shipper-skip-config-label" class="sui-toggle-label">
							<?php esc_html_e( 'Do not migrate wp-config file', 'shipper' ); ?>
						</span>
					</label>
					<div class="sui-toggle-content">
						<p class="shipper-description">
							<?php esc_html_e( 'Enable this option if you don’t want to migrate the wp-config file from your source site to the destination site.', 'shipper' ); ?>
						</p>
					</div>
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