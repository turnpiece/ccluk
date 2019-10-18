<?php

/**
 * Google account settings template for network.
 *
 * @var string $login_url Google login url.
 */

defined( 'WPINC' ) || die();

?>

<div class="sui-form-field">
	<?php
	/**
	 * Action hook to show notifications on Google setup screen.
	 *
	 * @since 3.2.0
	 */
	do_action( 'beehive_google_setup_notice' );
	?>

	<div id="beehive-google-setup-notice"></div>
</div>

<div class="sui-side-tabs sui-tabs">

	<div data-tabs>
		<div class="active"><?php esc_html_e( 'Connect with Google', 'ga_trans' ); ?></div>
		<div><?php esc_html_e( 'Set up API Project', 'ga_trans' ); ?></div>
	</div>

	<div data-panes>
		<div class="sui-tab-boxed beehive-google-setup-connect active">
			<div class="sui-form-field">
				<span class="sui-description"><?php esc_html_e( 'Easily connect with Google by clicking the “Connect with Google” button and pasting the access code below. Please note, we only retrieve analytics information and no personally identifiable data.', 'ga_trans' ); ?></span>
			</div>

			<div class="sui-form-field">
				<a type="button" href="<?php echo esc_url( $login_url ); ?>" target="_blank" class="sui-button beehive-connect-google-btn">
					<i class="sui-icon-google-connect" aria-hidden="true"></i>
					<?php esc_html_e( 'Connect with Google', 'ga_trans' ); ?>
				</a>
			</div>

			<div class="sui-form-field">
				<label for="google-access-code" class="sui-label"><?php esc_html_e( 'Access Code', 'ga_trans' ); ?></label>
				<input type="text" id="google-access-code" placeholder="<?php esc_html_e( 'Place access code here', 'ga_trans' ); ?>" class="sui-form-control">
				<span class="sui-error-message"></span>
			</div>

			<div class="sui-form-field">
				<button type="button" id="beehive-connect-google" class="sui-button sui-button-blue">
					<span class="sui-loading-text"><?php esc_html_e( 'Authorize', 'ga_trans' ); ?></span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>
			</div>

		</div>
		<div class="sui-tab-boxed">
			<div class="sui-form-field">
				<span class="sui-description"><?php printf( __( 'We recommend to set up API project, as it results in smoother experience for site admins. You can check the documentation on how to set up API project <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://premium.wpmudev.org/docs/wpmu-dev-plugins/beehive/#set-up-api-project' ); ?></span>
			</div>

			<div class="sui-form-field">
				<label for="google-client-id" class="sui-label"><?php esc_html_e( 'Google Client ID', 'ga_trans' ); ?></label>
				<input type="text" id="google-client-id" name="google[client_id]" placeholder="<?php esc_html_e( 'Place Google Client ID here', 'ga_trans' ); ?>" class="sui-form-control" value="<?php echo esc_html( beehive_analytics()->settings->get( 'client_id', 'google', true ) ); ?>">
				<span class="sui-error-message"></span>
			</div>

			<div class="sui-form-field">
				<label for="google-client-secret" class="sui-label"><?php esc_html_e( 'Google Client Secret', 'ga_trans' ); ?></label>
				<input type="text" id="google-client-secret" name="google[client_secret]" placeholder="<?php esc_html_e( 'Place Google Client Secret here', 'ga_trans' ); ?>" class="sui-form-control" value="<?php echo esc_html( beehive_analytics()->settings->get( 'client_secret', 'google', true ) ); ?>">
				<span class="sui-error-message"></span>
			</div>

			<div class="sui-form-field">
				<button type="button" id="beehive-authorize-google" class="sui-button sui-button-blue">
					<span class="sui-loading-text"><?php esc_html_e( 'Authorize', 'ga_trans' ); ?></span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>
			</div>
		</div>
	</div>

</div>