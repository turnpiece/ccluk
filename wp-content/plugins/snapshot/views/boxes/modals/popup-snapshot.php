<?php
	$ajax_nonce = wp_create_nonce( "snapshot-save-key" );
?>

<div id="ss-show-apikey">

	<div id="wps-snapshot-key" class="snapshot-three wps-popup-modal"><?php // Use "show" class to show the popup, or else remove it to hide popup ?>

		<div class="wps-popup-mask"></div>

		<div class="wps-popup-content">

			<div class="wpmud-box">

				<div class="wpmud-box-title can-close">

					<h3><?php esc_html_e('Add Snapshot Key', SNAPSHOT_I18N_DOMAIN); ?></h3>

					<i class="wps-icon i-close"></i>

				</div>

				<div class="wpmud-box-content">

					<div class="row">

						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

							<?php if (isset( $apiKey ) && !empty( $apiKey )) : ?>

							<p><?php esc_html_e('This is your Snapshot API key. If you have any issues connecting to WPMU DEV’s cloud servers, just reset your key. Don’t worry, resetting your key won’t affect your backups.', SNAPSHOT_I18N_DOMAIN); ?></p>

							<?php else : ?>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-1">
									<p><?php esc_html_e('To enable Managed Backups and your 10GB storage allowance on our WPMU DEV cloud servers, you need to add your Snapshot key.', SNAPSHOT_I18N_DOMAIN); ?></p>

									<p><a target="_blank" href="<?php echo esc_url( $apiKeyUrl ); ?>" class="button button-blue"><?php esc_html_e('Get My Key', SNAPSHOT_I18N_DOMAIN); ?></a></p>

									<p><?php esc_html_e('Once you\'ve got your key, enter it below:', SNAPSHOT_I18N_DOMAIN); ?></p>

								</div>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-2 hidden">
									<p><?php esc_html_e('Please wait while we verify your Snapshot key...', SNAPSHOT_I18N_DOMAIN); ?></p>
								</div>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-3 hidden">
									<div class="wps-snapshot-error wpmud-box-gray">
										<p><?php echo wp_kses_post( sprintf(__('We couldn’t verify your Snapshot key. Try entering it again, or reset it for this website in <a target="_blank" href="%s">The Hub</a> over at WPMU DEV.', SNAPSHOT_I18N_DOMAIN ), 'https://premium.wpmudev.org/hub/' ) ); ?></p>
									</div>
								</div>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-4 hidden">
									<p><?php esc_html_e('This is your Snapshot API key. If you have any issues connecting to WPMU DEV’s cloud servers, just reset your key. Don’t worry, resetting your key won’t affect your backups.', SNAPSHOT_I18N_DOMAIN); ?></p>
								</div>


							<?php endif; ?>

							<form method="post" action="?page=snapshot_pro_settings" data-security="<?php echo esc_attr( $ajax_nonce ); ?>">

								<div class="wps-snapshot-key wpmud-box-gray">

									<input type="text" name="secret-key" id="secret-key" value="<?php echo ( isset( $apiKey ) && !empty( $apiKey ) ) ? esc_attr( $apiKey ) : ''; ?>"  data-url="<?php echo ( isset( $apiKeyUrl ) && !empty( $apiKeyUrl ) ) ? esc_attr( $apiKeyUrl ) : ''; ?>" placeholder="<?php esc_html_e('Enter your key here', SNAPSHOT_I18N_DOMAIN); ?>">

									<button type="submit" name="activate" value="yes" class="button button-gray"><?php esc_html_e('Save Key', SNAPSHOT_I18N_DOMAIN); ?></button>

									<?php $model = new Snapshot_Model_Full_Backup(); ?>
									<p><a href="<?php echo esc_attr( $model->get_current_secret_key_link() ); ?>" target='_blank' class="wps-snapshot-popin-content-step-4 <?php echo ( isset( $apiKey ) && !empty( $apiKey ) ) ? '' : 'hidden'; ?>"><?php esc_html_e('Reset Key', SNAPSHOT_I18N_DOMAIN); ?></a></p>



								</div>

							</form>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>