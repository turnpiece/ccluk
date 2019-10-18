<?php

/** @var WPMUDEVSnapshot_New_Ui_Tester $this */

$item = array_merge(
	array(
		'name' => '',
		'directory' => '',
	), $item
);

?>


<div class="form-content">

	<div id="wps-destination-type" class="form-row">

		<div class="form-col-left">
			<label><?php esc_html_e( 'Type', SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>

		<div class="form-col">
			<i class="wps-typecon dropbox"></i>
			<label><?php esc_html_e( 'Dropbox', SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>

	</div>

	<div id="wps-destination-name" class="form-row">
		<div class="form-col-left">
			<label for="snapshot-destination-name">
				<?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span>
			</label>
		</div>

		<div class="form-col">
			<input type="text" class="inline<?php $this->input_error_class( 'name' ); ?>" name="snapshot-destination[name]" id="snapshot-destination-name"
			       value="<?php echo esc_attr( stripslashes( $item['name'] ) ); ?>">
			<?php $this->input_error_message( 'name' ); ?>
		</div>

	</div>

	<?php if ( isset( $item['tokens']['access']['access_token'] ) ) : ?>

		<div id="wps-destination-dir" class="form-row">
			<div class="form-col-left">
				<label for="snapshot-destination-directory">
					<?php esc_html_e( 'Directory', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span>
				</label>
			</div>

			<div class="form-col">
				<input type="text" class="<?php $this->input_error_class( 'directory' ); ?>" name="snapshot-destination[directory]" id="snapshot-destination-directory" value="<?php echo esc_attr( stripslashes( $item['directory'] ) ); ?>">
				<?php $this->input_error_message( 'directory' ); ?>
			</div>
		</div>

		<div id="wps-destination-auth" class="form-row">
			<div class="form-col-left">
				<label><?php esc_html_e( 'Authenticated', SNAPSHOT_I18N_DOMAIN ); ?></label>
			</div>

			<div class="form-col">

					<?php

					$auth_error = false;

					try {
						$item_object->load_library();
						$destination_classes = WPMUDEVSnapshot::instance()->get_setting( 'destinationClasses' );
						$destination_class = $destination_classes[ $item['type'] ];
						$item_object->oauth = new Kunnu\Dropbox\DropboxApp( $destination_class->get_app_key(), $destination_class->get_app_secret() );
						$item_object->dropbox = new Kunnu\Dropbox\Dropbox( $item_object->oauth );
						if ( isset( $item['tokens']['access']['token_secret'] ) && ! empty( $item['tokens']['access']['token_secret'] ) ) {
							$oauth2_token = $item_object->dropbox->getOAuth2Client()->getAccessTokenFromOauth1( $item['tokens']['access']['token'], $item['tokens']['access']['token_secret'] );
							$oauth2_token = $oauth2_token['oauth2_token'];

							$item['tokens']['access']['access_token'] = $oauth2_token;
							$item['tokens']['access']['token'] = '';
							$item['tokens']['access']['token_secret'] = '';
						}

						$item_object->dropbox->setAccessToken( $item['tokens']['access']['access_token'] );

						$account_info = $item_object->dropbox->getCurrentAccount();
						$space_usage_info = $item_object->dropbox->getSpaceUsage();

					} catch ( Dropbox_Exception_Forbidden $e ) {
						$auth_error = true;
						echo '<p>', esc_html__( 'An error occurred when attempting to connect to Dropbox: ', SNAPSHOT_I18N_DOMAIN ), '</p>';
						echo wp_kses_post( sprintf( '<div class="wps-auth-message error"><p>%s</p></div>', $e->getMessage() ) );
					} catch ( Kunnu\Dropbox\Exceptions\DropboxClientException $e ) {
						$auth_error = true;
						echo '<p>', esc_html__( 'An error occurred when attempting to connect to Dropbox: ', SNAPSHOT_I18N_DOMAIN ), '</p>';
						echo wp_kses_post( sprintf( '<div class="wps-auth-message error"><p>%s</p></div>', $e->getMessage() ) );
					} catch ( Exception $e ) {
						$auth_error = true;
						echo '<p>', esc_html__( 'An error occurred when attempting to connect to Dropbox: ', SNAPSHOT_I18N_DOMAIN ), '</p>';
						echo wp_kses_post( sprintf( '<div class="wps-auth-message error"><p>%s</p></div>', $e->getMessage() ) );
					}
					?>

					<?php
                    if ( ! $auth_error ) {
						if ( empty( $item['directory'] ) && isset( $item['tokens']['access']['access_token'] ) && ! empty( $item['tokens']['access']['access_token'] ) ) {
                        ?>
						<div class="wps-auth-message wps-notice">
							<p><?php esc_html_e( "You've authenticated this Dropbox destination. To finish adding this destination, please specify a folder to store the snapshots in and click Save Destination.", SNAPSHOT_I18N_DOMAIN ); ?></p>
						</div>
						<?php } else { ?>
						<div class="wps-auth-message success">
							<p><?php esc_html_e( 'This destination is authenticated and ready for use.', SNAPSHOT_I18N_DOMAIN ); ?></p>
						</div>
						<?php } ?>
					<?php } ?>

				<p><small><?php esc_html_e("You can re-authenticate at any time if you wish to change this destination's details.", SNAPSHOT_I18N_DOMAIN); ?></small></p>

				<div class="wps-label--checkbox">
					<div class="wps-input--checkbox">
						<input type="checkbox" name="snapshot-destination[force-authorize]" id="snapshot-destination-force-authorize"<?php checked( $auth_error ); ?>>
						<label for="snapshot-destination-force-authorize"></label>
					</div>

					<label for="snapshot-destination-force-authorize"><?php esc_html_e( 'Force Re-Authorize with Dropbox', SNAPSHOT_I18N_DOMAIN ); ?></label>
				</div>

				<div id="wps-destination-token-checkbox" class="form-row">
					<div class="form-col-left">
						<label for="snapshot-destination-token">
							<?php esc_html_e( 'Authorization code', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span>
						</label>
					</div>

					<div class="form-col">
						<input type="text" class="inline" name="snapshot-destination[tokens][access][authorization_token]" id="snapshot-destination-authorization-token"
						       value="<?php echo esc_attr( stripslashes( $item['tokens']['access']['authorization_token'] ) ); ?>">
						<?php $this->input_error_message( 'authorization_token' ); ?>
					</div>

					<input type="hidden" name="snapshot-destination[force-authorize]" id="snapshot-destination-force-authorize" value="on">

				</div>

			</div>
		</div>

		<?php if ( isset( $account_info ) && $account_info ) : ?>

			<div id="wps-destination-account" class="form-row">

				<div class="form-col-left">
					<label><?php esc_html_e( 'Account', SNAPSHOT_I18N_DOMAIN ); ?></label>
				</div>

				<div class="form-col">
					<table cellpadding="0" cellspacing="0">
						<tbody>

							<?php if ( !empty( $account_info->getDisplayName() ) ) : ?>
								<tr>
									<th><?php esc_html_e('Name', SNAPSHOT_I18N_DOMAIN); ?></th>

									<td>
									<?php echo esc_html( $account_info->getDisplayName() ); ?>
									<input type="hidden" name="snapshot-destination[account_info][display_name]" value="<?php echo esc_attr( $account_info->getDisplayName() ); ?>">
									</td>

								</tr>

								<tr>

									<th><?php esc_html_e('Email', SNAPSHOT_I18N_DOMAIN); ?></th>

									<td>
									<?php echo esc_html( $account_info->getEmail() ); ?>
									<input type="hidden" name="snapshot-destination[account_info][email]" value="<?php echo esc_attr( $account_info->getEmail() ); ?>">
									</td>

								</tr>

							<?php endif ; ?>

							<?php if ( !empty( $account_info->getAccountId() ) ) : ?>
								<tr>

									<th><?php esc_html_e('UID', SNAPSHOT_I18N_DOMAIN); ?></th>

									<td>
									<?php echo esc_html( $account_info->getAccountId() ); ?>
									<input type="hidden" name="snapshot-destination[account_info][uid]" value="<?php echo esc_attr( $account_info->getAccountId() ); ?>" />
									</td>

								</tr>
							<?php endif ; ?>

							<?php if ( !empty( $account_info->getCountry() ) ) : ?>
								<tr>

									<th><?php esc_html_e('Country', SNAPSHOT_I18N_DOMAIN); ?></th>
									<td>
									<?php echo esc_html( $account_info->getCountry() ); ?>
									<input type="hidden" name="snapshot-destination[account_info][country]" value="<?php echo esc_attr( $account_info->getCountry() ); ?>" />
									</td>

								</tr>
							<?php endif ; ?>

						</tbody>

					</table>

				</div>

			</div>

			<div id="wps-destination-storage" class="form-row">

				<div class="form-col-left">

					<label><?php esc_html_e( 'Storage Used', SNAPSHOT_I18N_DOMAIN ); ?></label>

				</div>

				<div class="form-col">

					<p><?php echo number_format( ( ( $space_usage_info['used'] ) * 100 ) / ( $space_usage_info['allocation']["allocated"] ), 2, '.', '' ); ?>%</p>
					<input type="hidden" name="snapshot-destination[account_info][quota_info][normal]" value="<?php echo esc_attr( $space_usage_info['used'] ); ?>"/>
					<input type="hidden" name="snapshot-destination[account_info][quota_info][quota]" value="<?php echo esc_attr( $space_usage_info['allocation']["allocated"] ); ?>"/>

				</div>

			</div>

		<?php endif; ?>

	<?php endif; ?>

	<?php if ( ! isset( $item['tokens']['access']['access_token'] ) || empty( $item['tokens']['access']['access_token'] ) ) : ?>

		<div id="wps-destination-auth" class="form-row">

			<div class="form-col-left">

				<label><?php esc_html_e( 'Authenticated', SNAPSHOT_I18N_DOMAIN ); ?></label>

			</div>

			<?php if ( isset( $item['tokens']['access']['token_secret'] ) ) : ?>

			<div class="form-col">

				<div class="wps-auth-message error">
					<p><?php esc_html_e( 'This destination is using old Dropbox authentication system, please re-authenticate.', SNAPSHOT_I18N_DOMAIN ); ?></p>
				</div>

				<p><small><?php esc_html_e( 'The first step in the Dropbox setup is Authorizing Snapshot to communicate with your Dropbox account. Dropbox requires that you grant Snapshot access to your account. This is required in order for Snapshot to upload files to your Dropbox account.', SNAPSHOT_I18N_DOMAIN ); ?></small></p>

			</div>

			<input type="hidden" name="snapshot-destination[directory]" id="snapshot-destination-directory" value="
				<?php
				echo esc_attr( stripslashes( $item['directory'] ) );
                ?>
                ">

			<?php else : ?>

			<div class="form-col">

				<div class="wps-auth-message error">
					<p><?php esc_html_e( 'This destination is not authenticated yet.', SNAPSHOT_I18N_DOMAIN ); ?></p>
				</div>

				<p><small><?php esc_html_e( 'The first step in the Dropbox setup is Authorizing Snapshot to communicate with your Dropbox account. Dropbox requires that you grant Snapshot access to your account. This is required in order for Snapshot to upload files to your Dropbox account.', SNAPSHOT_I18N_DOMAIN ); ?></small></p>

			</div>

			<?php endif; ?>


		</div>

		<div id="wps-destination-token" class="form-row">
			<div class="form-col-left">
				<label for="snapshot-destination-token">
					<?php esc_html_e( 'Authorization code', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span>
				</label>
			</div>

			<div class="form-col">
				<input type="text" class="inline" name="snapshot-destination[tokens][access][authorization_token]" id="snapshot-destination-authorization-token"
				       value="<?php echo esc_attr( stripslashes( $item['tokens']['access']['authorization_token'] ) ); ?>">
				<?php $this->input_error_message( 'authorization_token' ); ?>
			</div>

			<input type="hidden" name="snapshot-destination[force-authorize]" id="snapshot-destination-force-authorize" value="on">

		</div>

	<?php endif; ?>

	<?php

	// Store the Token - Access as hidden fields
	if ( isset( $item['tokens']['access']['token'] ) ) {
    ?>

		<input type="hidden" name="snapshot-destination[tokens][access][token]" value="<?php echo esc_attr( $item['tokens']['access']['token'] ); ?>">

	<?php
    }

	if ( isset( $item['tokens']['access']['token_secret'] ) ) {
    ?>

		<input type="hidden" name="snapshot-destination[tokens][access][token_secret]" value="<?php echo esc_attr( $item['tokens']['access']['token_secret'] ); ?>">

	<?php
    }

	if ( isset( $item['tokens']['access']['access_token'] ) ) {
    ?>

		<input type="hidden" name="snapshot-destination[tokens][access][access_token]" value="<?php echo esc_attr( $item['tokens']['access']['access_token'] ); ?>">

	<?php } ?>

	<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo esc_attr( $item['type'] ); ?>">

</div>
