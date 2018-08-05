<?php

/**
 * @var WPMUDEVSnapshot_New_Ui_Tester $this
 * @var SnapshotDestinationGoogleDrive $item_object
 * @var array $item
 */

if ( ! isset( $_GET['item'] ) || empty( $item['name'] ) ) {
	$form_step = 1;
} else if ( empty( $item['clientid'] ) || empty( $item['clientsecret'] ) ) {
	$form_step = 2;
} else if ( empty( $item['access_token'] ) ) {
	$form_step = 3;
} else {
	$form_step = 4;
}

$item = array_merge(
	array(
		'name' => '',
		'directory' => '',
		'clientid' => '',
		'clientsecret' => '',
	), $item
);

?>

<input type="hidden" name="snapshot-destination[form-step]" id="snapshot-destination-form-step" value="<?php echo esc_attr( $form_step ); ?>"/>

<div class="form-content">

	<div id="wps-destination-type" class="form-row">
		<div class="form-col-left">
			<label><?php esc_html_e( 'Type', SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>

		<div class="form-col">
			<i class="wps-typecon google"></i>
			<label><?php esc_html_e( 'Google Drive', SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>
	</div>

	<div id="wps-destination-name" class="form-row">
		<div class="form-col-left">
			<label for="snapshot-destination-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col upload-progress">
			<input type="text" class="inline<?php $this->input_error_class( 'name' ); ?>" name="snapshot-destination[name]" id="snapshot-destination-name" value="<?php echo esc_attr( $item['name'] ); ?>">
			<?php $this->input_error_message( 'name' ); ?>
		</div>
	</div>

	<div id="wps-destination-dir" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-directory"><?php esc_html_e( "Directory ID", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<input type="text" class="inline<?php $this->input_error_class( 'directory' ); ?>" name="snapshot-destination[directory]" id="snapshot-destination-directory"
			       value="<?php echo esc_attr( $item['directory'] ); ?>">

			<?php $this->input_error_message( 'directory' ); ?>

			<p>
				<small>
					<?php

					esc_html_e( "This isn't a traditional directory path like /app/snapshot/ but a unique directory ID that Google Drive use for their filesystem. ", SNAPSHOT_I18N_DOMAIN );
					echo wp_kses(
						sprintf(
							__( 'To retrieve your directory ID, follow <a %s>these instructions</a>.', SNAPSHOT_I18N_DOMAIN ),
							'class="show-instructions" data-instructions="#directory-instructions"'
						),
						array(
						    'a' => array(
						        'class' => array(),
						        'data-instructions' => array()
						    )
						)
					);

					?>
				</small>
			</p>

			<ol class="instructions" id="directory-instructions">
				<li><?php echo wp_kses_post( __( 'Go to your <a href="https://drive.google.com/#my-drive" target="_blank">Drive account</a>. Navigate to or create a new directory where you want to upload the Snapshot archives. Make sure you are viewing the destination directory.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
				<li><?php echo wp_kses_post( __( 'The URL for the directory will be something similar to <em>https://drive.google.com/#folders/0B6GD66ctHXXCOWZKNDRIRGJJXS3</em>. The Directory ID would be the last part after <em>/#folders/</em>: <strong>0B6GD66ctHXXCOWZKNDRIRGJJXS3.</strong>', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
				<li><?php echo wp_kses_post( sprintf( esc_html__( 'You may specify multiple Directory IDs separated by a comma "%s"', SNAPSHOT_I18N_DOMAIN ), ',' ) ); ?></li>
			</ol>

		</div>

	</div>

	<?php if ( $form_step > 1 ) : ?>

		<div id="wps-destination-clientid" class="form-row">

			<div class="form-col-left">
				<label for="snapshot-destination-clientid"><?php esc_html_e( 'Client ID', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
			</div>

			<div class="form-col upload-progress">

				<input type="text" class="inline<?php $this->input_error_class( 'clientid' ); ?>" name="snapshot-destination[clientid]" id="snapshot-destination-clientid"
						value="<?php if ( isset( $item['clientid'] ) ) echo esc_attr( sanitize_text_field( $item['clientid'] ) ); ?>"/>

				<?php $this->input_error_message( 'clientid' ); ?>

				<p><small>
                <?php

					echo wp_kses(
						sprintf(
							__( 'Follow <a %s>these instructions</a> to retrieve your Client ID and Secret.', SNAPSHOT_I18N_DOMAIN ),
							'class="show-instructions" data-instructions="#clientid-instructions"'
						),
						array(
						    'a' => array(
						        'class' => array(),
						        'data-instructions' => array()
						    )
						)
					);

					?>
                    </small></p>

				<ol class="instructions" id="clientid-instructions">
					<li><?php echo wp_kses_post( sprintf( __( 'Go to the %s', SNAPSHOT_I18N_DOMAIN ), '<a href="https://console.developers.google.com/cloud-resource-manager" target="_blank">' . __( 'Google API Console', SNAPSHOT_I18N_DOMAIN ) . '</a>' ) ); ?></li>
					<li><?php echo wp_kses_post( __( 'Select an existing project or create a new one. If creating a new project, you will need to enter a name, but the ID is not important and can be ignored.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
					<li><?php echo wp_kses_post( __( 'Once the Project creation is completed go to the <strong>API Manager</strong>. Here you need to enable the <strong>Drive API</strong>', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
					<li><?php echo wp_kses_post( __( 'Next, go to the <strong>API Manager > Credentials</strong> section. Click <strong>Create Credentials > OAuth 2.0 client ID</strong>. In the popup select the <strong>Application Type</strong> as <strong>Web application</strong>. In the field <strong>Authorized redirect URI</strong> copy the value from the <strong>Redirect URI</strong> field below. Then click the <strong>Create Client ID</strong> button.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
					<li><?php echo wp_kses_post( __( 'After the popup closes copy the Client ID and Client Secret from the Google page and paste into the form fields.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
				</ol>

			</div>

		</div>

		<div id="wps-destination-secretid" class="form-row">

			<div class="form-col-left">
				<label for="snapshot-destination-clientsecret"><?php esc_html_e( 'Client Secret', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
			</div>

			<div class="form-col upload-progress">

				<input type="password" class="inline<?php $this->input_error_class( 'clientsecret' ); ?>" name="snapshot-destination[clientsecret]" id="snapshot-destination-clientsecret"
				       value="<?php echo esc_attr( $item['clientsecret'] ); ?>">

				<?php $this->input_error_message( 'clientsecret' ); ?>
			</div>

		</div>

		<div id="wps-destination-redirect" class="form-row">

			<div class="form-col-left">
				<label for="snapshot-destination-redirecturi"><?php esc_html_e( 'Redirect URL', SNAPSHOT_I18N_DOMAIN ); ?></label>
			</div>

			<div class="form-col">

				<?php

				$item['redirecturi'] = self_admin_url( 'admin.php' );
				$query_vars = array( 'page', 'snapshot-action', 'type', 'item', 'destination-noonce-field' );
				if ( 2 === $form_step && ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ){
					wp_die();
				}
				foreach ( $query_vars as $query_var ) {
					if ( isset( $_GET[ $query_var ] ) ) {
						$item['redirecturi'] = add_query_arg( $query_var, $_GET[ $query_var ], $item['redirecturi'] );

					}
				}

				?>

				<div class="wps-input--copy">
					<input type="text" name="snapshot-destination[redirecturi]" id="snapshot-destination-redirecturi" class="disabled"
					       value="<?php echo esc_url( $item['redirecturi'] ); ?>">

					<button class="button button-gray copy-to-clipboard" data-clipboard-target="#snapshot-destination-redirecturi">
						<?php esc_html_e('Copy URL', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
				</div>

				<p><small><?php esc_html_e( 'When your create your new credentials, add this as the redirect URL.', SNAPSHOT_I18N_DOMAIN ); ?></small></p>
			</div>

		</div>

	<?php endif; ?>

	<?php if ( $form_step > 2 ) : ?>

		<div id="wps-destination-auth" class="form-row">

			<div class="form-col-left">
				<label><?php esc_html_e( 'Authenticated', SNAPSHOT_I18N_DOMAIN ); ?></label>
			</div>

			<div class="form-col">

				<?php

				$auth_error = false;
				$item_object->init();
				$item_object->load_class_destination( $item );

				if ( $form_step > 3 && ! empty( $item_object->destination_info['access_token'] ) ) {

					echo '<div class="wps-auth-message success"><p>';
					esc_html_e( 'This destination is authenticated and ready for use.', SNAPSHOT_I18N_DOMAIN );
					echo '</p></div>';

				} else if ( ! empty( $_GET['code'] ) ) {

					$item_object->login();

					if ( is_object( $item_object->client ) ) {

						try {
							$item_object->client->authenticate( $_GET['code'] );

						} catch (Google_0814_Auth_Exception $e) {
							$auth_error = true;
							echo '<div class="wps-auth-message error">';
							echo wp_kses_post( '<p>', esc_html__( 'An error occurred authenticating with Google: ', SNAPSHOT_I18N_DOMAIN ), '<br>', $e->getMessage(), '</p>' );
							echo '<p>', esc_html__( 'Please check your client ID and secret ID before resubmitting this form to try again', SNAPSHOT_I18N_DOMAIN ), '</p>';
							echo '</div>';
						}

						$item_object->destination_info['access_token'] = $item_object->client->getAccessToken();

						if ( ! empty( $item_object->destination_info['access_token'] ) ) {
							echo '<div class="wps-auth-message warning"><p>';
							esc_html_e( 'Success. The Google Access Token has been received.', SNAPSHOT_I18N_DOMAIN );
							echo ' <strong>', esc_html__( 'You must save this form one last time to retain the token.', SNAPSHOT_I18N_DOMAIN ), '</strong> ';
							esc_html_e( 'The stored token will be used in the future when connecting to Google.', SNAPSHOT_I18N_DOMAIN );
							echo '</p></div>';
						}
					}

				} else {

					echo '<div class="wps-auth-message warning"><p>';
					esc_html_e( 'To finish adding this destination you must authenticate it with Google Drive.', SNAPSHOT_I18N_DOMAIN );
					echo '</p></div>';
				}

				if ( ! $auth_error ) {
					$auth_url = $item_object->getAuthorizationUrl();
					if ( $auth_url ) {
                    ?>

						<p class="wps-auth-button">
							<a id="snapshot-destination-authorize-connection" class="button button-blue" href="<?php echo esc_url( $auth_url ); ?>">
								<?php
								echo empty( $item_object->destination_info['access_token'] ) ?
									esc_html__( 'Authorize', SNAPSHOT_I18N_DOMAIN ) :
									esc_html__( 'Re-Authorize', SNAPSHOT_I18N_DOMAIN );
								?>
							</a>
						</p>

					<?php
                    } else {
						echo '<div class="wps-auth-message error"><p>', esc_html__( 'Unable to obtain an authorization URL from Google', SNAPSHOT_I18N_DOMAIN ), '</p></div>';
					}
				}

				if ( ! empty( $item_object->destination_info['access_token'] ) ) {

					printf(
						'<input type="hidden" name="snapshot-destination[access_token]" id="snapshot-destination-access_token" value="%s">',
						esc_attr( $item_object->destination_info['access_token'] )
					);
				}

				?>

			</div>

		</div>

	<?php endif; ?>

	<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo esc_attr( $item['type'] ); ?>"/>

</div>