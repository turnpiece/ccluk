<?php

/**
 * Admin settings page template.
 *
 * @var bool       $network   Is network settings page?.
 * @var array|bool $ps_levels Pro Sites levels.
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

?>

	<input type="hidden" name="beehive_settings_group" value="general">

	<div class="sui-box-settings-row">

		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Account', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Google API project is suitable for high traffic websites and will result in smoother experience for site admins.', 'ga_trans' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">

			<?php
			/**
			 * Action hook to print form in Google account section.
			 *
			 * @since 3.2.0
			 */
			do_action( 'beehive_settings_google_settings' );
			?>

		</div>

	</div>

<?php if ( $network ) : ?>

	<div class="sui-box-settings-row">

		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Admin pages tracking', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'When enabled, you will get statistics from all admin pages.', 'ga_trans' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<label for="beehive-settings-track-admin" class="sui-toggle">
				<input type="checkbox" id="beehive-settings-track-admin" name="general[track_admin]" value="1" <?php checked( beehive_analytics()->settings->get( 'track_admin', 'general', $network ), 1 ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="beehive-settings-track-admin" class="sui-toggle-label"><?php esc_html_e( 'Enable Admin pages tracking', 'ga_trans' ); ?></label>
		</div>

	</div>

<?php endif; ?>

	<div class="sui-box-settings-row">

		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'IP Anonymization', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'When enabled, visitor IP address will be anonymized.', 'ga_trans' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<label for="beehive-settings-anonymize" class="sui-toggle">
				<input type="checkbox" id="beehive-settings-anonymize" name="general[anonymize]" value="1" <?php checked( beehive_analytics()->settings->get( 'anonymize', 'general', $network ), 1 ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="beehive-settings-anonymize" class="sui-toggle-label"><?php esc_html_e( 'Enable IP Anonymization', 'ga_trans' ); ?></label>

			<?php if ( $network ) : ?>

				<div class="sui-border-frame">
					<label for="beehive-settings-force-anonymize" class="sui-label"><?php esc_html_e( 'Whole network tracking', 'ga_trans' ); ?></label>
					<label for="beehive-settings-force-anonymize" class="sui-checkbox sui-checkbox-sm">
						<input type="checkbox" id="beehive-settings-force-anonymize" name="general[force_anonymize]" value="1" <?php checked( beehive_analytics()->settings->get( 'force_anonymize', 'general', $network ), 1 ); ?>>
						<span aria-hidden="true"></span>
						<span><?php esc_html_e( 'Force on sub-sites tracking', 'ga_trans' ); ?></span>
					</label>
				</div>

			<?php endif; ?>

		</div>

	</div>

	<div class="sui-box-settings-row">

		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Display Advertising', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php printf( __( 'Enable support for Google\'s Display Advertising and get additional demographic and interests reports. You can read more about it <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/3450482?hl=en&ref_topic=3413645&rd=1' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<label for="beehive-settings-advertising" class="sui-toggle">
				<input type="checkbox" id="beehive-settings-advertising" name="general[advertising]" value="1" <?php checked( beehive_analytics()->settings->get( 'advertising', 'general', $network ), 1 ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="beehive-settings-advertising" class="sui-toggle-label"><?php esc_html_e( 'Enable Display Advertising Support', 'ga_trans' ); ?></label>

			<div class="sui-notice sui-toggle-content">
				<p><?php printf( __( 'Note: Enabling this feature requires <a href="%s" target="_blank">updating your privacy policy</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/2700409' ); ?></p>
			</div>
		</div>

	</div>

<?php if ( $ps_levels && $network ) : ?>
	<div class="sui-box-settings-row">

		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Pro Site Permissions', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'We see you have Pro Sites active. Choose which levels you want to access analytics.', 'ga_trans' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<span class="sui-settings-label"><?php esc_html_e( 'Google Analytics Settings', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Choose which Pro Site levels can configure analytics settings.', 'ga_trans' ); ?></span>
			<?php foreach ( $ps_levels as $level => $data ) : ?>
				<label for="beehive-settings-ps-level-<?php echo esc_attr( $level ); ?>" class="sui-checkbox">
					<input type="checkbox" id="beehive-settings-ps-level-<?php echo esc_attr( $level ); ?>" name="general[prosites_settings_level][]" value="<?php echo esc_attr( $level ); ?>" <?php checked( in_array( $level, (array) beehive_analytics()->settings->get( 'prosites_settings_level', 'general', $network, [] ), true ) ); ?> />
					<span aria-hidden="true"></span>
					<span><?php echo esc_attr( $data['name'] ); ?></span>
				</label>
			<?php endforeach; ?>

			<hr/>

			<span class="sui-settings-label"><?php esc_html_e( 'Dashboard Analytics', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Choose which Pro Site levels can view analytics in their WP Admin Dashboard\'s.', 'ga_trans' ); ?></span>
			<?php foreach ( $ps_levels as $level => $data ) : ?>
				<label for="beehive-dashboard-ps-level-<?php echo esc_attr( $level ); ?>" class="sui-checkbox">
					<input type="checkbox" id="beehive-dashboard-ps-level-<?php echo esc_attr( $level ); ?>" name="general[prosites_analytics_level][]" value="<?php echo esc_attr( $level ); ?>" <?php checked( in_array( $level, (array) beehive_analytics()->settings->get( 'prosites_analytics_level', 'general', $network, [] ), true ) ); ?> />
					<span aria-hidden="true"></span>
					<span><?php echo esc_attr( $data['name'] ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>

	</div>
<?php endif; ?>