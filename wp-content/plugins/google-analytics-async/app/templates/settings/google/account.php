<?php

/**
 * Google account settings template for network.
 *
 * @var array  $accounts      Google analytics accounts.
 * @var bool   $network       Network flag.
 * @var string $tracking_code Tracking ID.
 * @var array  $user          User name and email.
 */

defined( 'WPINC' ) || die();

?>

<?php
/**
 * Action hook to show notifications on Google account screen.
 *
 * @since 3.2.0
 */
do_action( 'beehive_google_account_notice' );
?>

<label class="sui-label"><?php esc_html_e( 'Connected Google Account', 'ga_trans' ); ?></label>

<div class="sui-border-frame google-account-overview">

	<div class="sui-box-builder sui-flushed">
		<div class="sui-builder-fields">

			<div class="sui-builder-field">

				<div class="sui-builder-field-label">
					<?php if ( ! empty( $user['photo'] ) ) : ?>
						<span class="beehive-google-user-photo">
							<img src="<?php echo esc_url( $user['photo'] ); ?>" alt="<?php echo esc_attr( $user['name'] ); ?>">
						</span>
					<?php endif; ?>
					<span>
						<span class="beehive-google-user-name"><?php echo empty( $user['name'] ) ? __( 'No account information', 'ga_trans' ) : esc_attr( $user['name'] ); ?></span>
						<span class="beehive-google-user-email"><?php echo empty( $user['email'] ) ? '' : esc_html( $user['email'] ); ?></span>
					</span>
				</div>

				<div class="sui-dropdown">
					<button class="sui-button-icon sui-dropdown-anchor">
						<i class="sui-icon-widget-settings-config" aria-hidden="true"></i>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Open options', 'ga_trans' ); ?></span>
					</button>
					<ul>
						<li>
							<button type="button" id="google-switch-user" data-a11y-dialog-show="beehive-google-switch-confirm">
								<i class="sui-icon-update" aria-hidden="true"></i>
								<?php esc_html_e( 'Switch user', 'ga_trans' ); ?>
							</button>
						</li>
						<li>
							<button type="button" id="google-logout-user" data-a11y-dialog-show="beehive-google-logout-confirm">
								<i class="sui-icon-logout" aria-hidden="true"></i>
								<?php esc_html_e( 'Log out', 'ga_trans' ); ?>
							</button>
						</li>
					</ul>
				</div>

			</div>

		</div>
	</div>

</div>


<div class="sui-border-frame google-account-selector">

	<div class="sui-form-field">
		<label for="beehive-settings-google-account-id" class="sui-label"><?php esc_html_e( 'Choose your view (profile)', 'ga_trans' ); ?></label>
		<select class="sui-select" name="google[account_id]" id="beehive-settings-google-account-id">
			<?php if ( empty( $accounts ) ) : ?>
				<option value=""><?php esc_html_e( 'No website information', 'ga_trans' ); ?></option>
			<?php else : ?>
				<?php foreach ( $accounts as $account_id => $account_name ) : ?>
					<option value="<?php echo esc_attr( $account_id ); ?>" <?php selected( beehive_analytics()->settings->get( 'account_id', 'google', $network ), $account_id ); ?>><?php echo esc_html( $account_name ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<div class="sui-description"><?php esc_html_e( 'Site not here? Try logging into another account above!', 'ga_trans' ); ?></div>
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
	</div>

	<?php if ( ! empty( $accounts ) ) : // Required this option only if the tracking ID is not entered. ?>
		<div class="sui-form-field">
			<label for="beehive-settings-google-auto-track" class="sui-checkbox sui-checkbox-sm">
				<input type="checkbox" name="google[auto_track]" id="beehive-settings-google-auto-track" <?php checked( beehive_analytics()->settings->get( 'auto_track', 'google', $network ), 1 ) ?> value="1"/>
				<span aria-hidden="true"></span>
				<span><?php esc_html_e( 'Automatically add the Google Analytics tracking code to the <head> of my site', 'ga_trans' ); ?></span>
			</label>
		</div>
	<?php endif; ?>

</div>