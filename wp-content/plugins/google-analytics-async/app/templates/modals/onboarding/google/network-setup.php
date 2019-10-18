<?php

/**
 * Google account settings template for network admin modal.
 *
 * @var string $login_url Google login url.
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;
use Beehive\Core\Helpers\General;

?>

<div class="sui-box">

	<div class="sui-box-banner" role="banner" aria-hidden="true">
		<img src="<?php echo Template::asset_url( 'images/onboarding/welcome.png' ); ?>" srcset="<?php echo Template::asset_url( 'images/onboarding/welcome.png' ); ?> 1x, <?php echo Template::asset_url( 'images/onboarding/welcome@2x.png' ); ?> 2x">
	</div>

	<div class="sui-box-header sui-lg sui-block-content-center">

		<h2 id="beehive-onboarding-setup-title" class="sui-box-title"><?php printf( __( 'Welcome to %s', 'ga_trans' ), General::plugin_name() ); ?></h2>

		<span id="beehive-onboarding-setup-description" class="sui-description">
            <?php printf(
	            __( '%s, welcome to the hottest Google Analytics plugin for WordPress. Let\'s get started by connecting your Google Analytics account so that we can feed your analytics data.  If you only want to enable tracking without reports, you can paste your analytics code via the link below.', 'ga_trans' ),
	            General::get_user_name() // Current user name.
            ); ?>
        </span>

		<button data-a11y-dialog-hide="beehive-onboarding-setup" class="sui-dialog-close beehive-onboarding-skip" aria-label="Close this dialog window"></button>

	</div>

	<div class="sui-box-body sui-lg">

		<div id="beehive-modal-google-setup-notice"></div>

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
						<label for="google-modal-access-code" class="sui-label"><?php esc_html_e( 'Access Code', 'ga_trans' ); ?></label>
						<input type="text" id="google-modal-access-code" placeholder="<?php esc_html_e( 'Place access code here', 'ga_trans' ); ?>" class="sui-form-control">
						<span class="sui-error-message"></span>
					</div>

					<div class="sui-form-field">
						<button type="button" id="beehive-modal-connect-google" class="sui-button sui-button-blue">
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
						<label for="google-modal-client-id" class="sui-label"><?php esc_html_e( 'Google Client ID', 'ga_trans' ); ?></label>
						<input type="text" id="google-modal-client-id" placeholder="<?php esc_html_e( 'Place Google Client ID here', 'ga_trans' ); ?>" class="sui-form-control" value="<?php echo beehive_analytics()->settings->get( 'client_id', 'google', true ); ?>">
						<span class="sui-error-message"></span>
					</div>

					<div class="sui-form-field">
						<label for="google-modal-client-secret" class="sui-label"><?php esc_html_e( 'Google Client Secret', 'ga_trans' ); ?></label>
						<input type="text" id="google-modal-client-secret" placeholder="<?php esc_html_e( 'Place Google Client Secret here', 'ga_trans' ); ?>" class="sui-form-control" value="<?php echo beehive_analytics()->settings->get( 'client_secret', 'google', true ); ?>">
						<span class="sui-error-message"></span>
					</div>

					<div class="sui-form-field">
						<button type="button" id="beehive-modal-authorize-google" class="sui-button sui-button-blue">
							<span class="sui-loading-text"><?php esc_html_e( 'Authorize', 'ga_trans' ); ?></span>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						</button>
					</div>
				</div>

			</div>

		</div>

		<span class="beehive-modal-forward-link sui-block-content-center">
            <a href="#" data-a11y-dialog-tour-next><?php esc_html_e( 'Add Google Analytics tracking ID', 'ga_trans' ); ?></a>
        </span>

	</div>

</div>