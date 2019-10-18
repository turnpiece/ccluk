<?php

/**
 * Pro Sites settings for permission.
 *
 * @var bool       $network   Network flag.
 * @var array|bool $ps_levels Pro Sites levels.
 */

defined( 'WPINC' ) || die();

use Beehive\Core\Helpers\Template;

?>

<div class="sui-box">

	<div class="sui-box-banner" role="banner" aria-hidden="true">
		<img src="<?php echo Template::asset_url( 'images/onboarding/prosites.png' ); ?>" srcset="<?php echo Template::asset_url( 'images/onboarding/prosites.png' ); ?> 1x, <?php echo Template::asset_url( 'images/onboarding/prosites@2x.png' ); ?> 2x">
	</div>

	<div class="sui-box-header sui-lg sui-block-content-center">

		<h2 id="beehive-onboarding-setup-title" class="sui-box-title"><?php esc_html_e( 'Pro Site Permissions', 'ga_trans' ); ?></h2>

		<span id="beehive-onboarding-setup-description" class="sui-description">
			<?php esc_html_e( 'We see you have Pro Sites active. Choose which levels you want to access analytics.', 'ga_trans' ); ?>
		</span>

		<button data-a11y-dialog-hide="beehive-onboarding-setup" class="sui-dialog-close beehive-onboarding-skip" aria-label="<?php esc_html_e( 'Close this dialog window', 'ga_trans' ); ?>"></button>

	</div>

	<div class="sui-box-body sui-lg">

		<div class="beehive-box-border-bottom">

			<span class="sui-settings-label"><?php esc_html_e( 'Google Analytics Settings', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Choose which Pro Site levels can configure analytics settings.', 'ga_trans' ); ?></span>
			<?php foreach ( $ps_levels as $level => $data ) : ?>
				<label for="beehive-onboarding-settings-ps-level-<?php echo esc_attr( $level ); ?>" class="sui-checkbox">
					<input type="checkbox" id="beehive-onboarding-settings-ps-level-<?php echo esc_attr( $level ); ?>" class="onboarding-ps-settings-level" value="<?php echo esc_attr( $level ); ?>" <?php checked( in_array( $level, (array) beehive_analytics()->settings->get( 'prosites_settings_level', 'general', $network, [] ), true ) ); ?> />
					<span aria-hidden="true"></span>
					<span><?php echo esc_html( $data['name'] ); ?></span>
				</label>
			<?php endforeach; ?>

		</div>

		<div class="beehive-box-border-bottom">

			<span class="sui-settings-label"><?php esc_html_e( 'Dashboard Analytics', 'ga_trans' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Choose which Pro Site levels can view analytics in their WP Admin Dashboard\'s.', 'ga_trans' ); ?></span>
			<?php foreach ( $ps_levels as $level => $data ) : ?>
				<label for="beehive-onboarding-dashboard-ps-level-<?php echo esc_attr( $level ); ?>" class="sui-checkbox">
					<input type="checkbox" id="beehive-onboarding-dashboard-ps-level-<?php echo esc_attr( $level ); ?>" class="onboarding-ps-analytics-level" value="<?php echo esc_attr( $level ); ?>" <?php checked( in_array( $level, (array) beehive_analytics()->settings->get( 'prosites_analytics_level', 'general', $network, [] ), true ) ); ?> />
					<span aria-hidden="true"></span>
					<span><?php echo esc_html( $data['name'] ); ?></span>
				</label>
			<?php endforeach; ?>

		</div>

		<div class="sui-form-field sui-block-content-center">
			<button class="sui-button" data-a11y-dialog-tour-next><?php esc_html_e( 'Continue', 'ga_trans' ); ?></button>
		</div>

	</div>

</div>