<?php

/**
 * Google account settings template for modal.
 *
 * @var array  $accounts      Google analytics accounts.
 * @var bool   $network       Network flag.
 * @var string $tracking_code Tracking code.
 * @var array  $roles         Roles.
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;

?>

<div class="sui-box">

	<div class="sui-box-banner" role="banner" aria-hidden="true">
		<img src="<?php echo Template::asset_url( 'images/onboarding/setup.png' ); ?>" srcset="<?php echo Template::asset_url( 'images/onboarding/setup.png' ); ?> 1x, <?php echo Template::asset_url( 'images/onboarding/setup@2x.png' ); ?> 2x">
	</div>

	<div class="sui-box-header sui-lg sui-block-content-center">

		<h2 id="beehive-onboarding-setup-title" class="sui-box-title"><?php esc_html_e( 'Set up your account', 'ga_trans' ); ?></h2>

		<span id="beehive-onboarding-setup-description" class="sui-description">
			<?php esc_html_e( 'We have successfully connected your Google account. The next step is to choose your Analytics View to get data feeding.', 'ga_trans' ); ?>
		</span>

		<button data-a11y-dialog-hide="beehive-onboarding-setup" class="sui-dialog-close beehive-onboarding-skip" aria-label="<?php esc_html_e( 'Close this dialog window', 'ga_trans' ); ?>"></button>

	</div>

	<div class="sui-box-body sui-lg">

		<div class="beehive-box-border-bottom beehive-onboarding-google-profile-section">

			<div class="sui-form-field">
				<label for="google-modal-account-id" class="sui-label"><?php esc_html_e( 'Choose your analytics view (profile)', 'ga_trans' ); ?></label>
				<select id="google-modal-account-id">
					<?php if ( empty( $accounts ) ) : ?>
						<option value=""><?php esc_html_e( 'No website information', 'ga_trans' ); ?></option>
					<?php else : ?>
						<?php foreach ( $accounts as $account_id => $account_name ) : ?>
							<option value="<?php echo esc_attr( $account_id ); ?>" <?php selected( beehive_analytics()->settings->get( 'account_id', 'google', $network ), $account_id ); ?>><?php echo esc_html( $account_name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
			<?php if ( empty( $accounts ) ) : ?>
				<div class="sui-notice sui-notice-error">
					<p>
						<?php
						// Show error message if Analytics profile is not available.
						printf(
							__( 'You don\'t have any Google Analytics profile connected to your account. To get going, just <a href="%s" target="_blank">sign up for Google Analytics</a>.', 'ga_trans' ),
							'https://analytics.google.com/analytics/web/'
						); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( empty( $tracking_code ) ) : // Required this option only if the tracking code is not entered. ?>
				<div class="sui-form-field">
					<label for="google-modal-auto-track" class="sui-checkbox">
						<input type="checkbox" id="google-modal-auto-track" <?php checked( beehive_analytics()->settings->get( 'auto_track', 'google', $network ), 1 ); ?> value="1"/>
						<span aria-hidden="true"></span>
						<span class="beehive-small-label"><?php esc_html_e( 'Automatically add the Google Analytics tracking code to the <head> of my site', 'ga_trans' ); ?></span>
					</label>
				</div>
			<?php endif; ?>

		</div>

		<div class="sui-form-field">
			<span class="sui-label"><?php esc_html_e( 'Display Analytics statistics to:', 'ga_trans' ); ?></span>
			<?php foreach ( $roles as $role => $label ) : ?>
				<?php $checked = in_array( $role, beehive_analytics()->settings->get( 'roles', 'permissions', $network, [] ), true ) || 'administrator' === $role; ?>
				<label for="google-modal-role-<?php echo esc_attr( $role ); ?>" class="sui-checkbox sui-checkbox-stacked">
					<input type="checkbox" id="google-modal-role-<?php echo esc_attr( $role ); ?>" class="google-modal-roles" value="<?php echo esc_attr( $role ); ?>" <?php checked( $checked ); ?> <?php disabled( $role, 'administrator' ); ?> />
					<span aria-hidden="true"></span>
					<span><?php echo esc_attr( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>

		<?php if ( $network ) : // Only network admin require this. ?>
			<div class="beehive-onboarding-toggle">
				<label for="beehive-onboarding-roles-overwrite" class="sui-toggle">
					<input type="checkbox" id="beehive-onboarding-roles-overwrite" value="1" <?php checked( beehive_analytics()->settings->get( 'overwrite_cap', 'permissions', $network ), 1 ); ?>>
					<span class="sui-toggle-slider"></span>
				</label>
				<label for="beehive-onboarding-anonymize" class="sui-toggle-label"><?php esc_html_e( 'Allow site admins to overwrite this setting', 'ga_trans' ); ?></label>
			</div>
		<?php endif; ?>

		<div class="sui-form-field sui-block-content-center">
			<button class="sui-button" id="<?php echo $network ? '' : 'beehive-onboarding-finish'; ?>" data-a11y-dialog-tour-next><?php esc_html_e( 'Continue', 'ga_trans' ); ?></button>
		</div>

	</div>

</div>