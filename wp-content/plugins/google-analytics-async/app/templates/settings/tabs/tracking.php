<?php

/**
 * Tracking settings page template.
 *
 * @var bool   $network            Is network settings page?.
 * @var string $tracking           Current site's tracking code.
 * @var string $settings_page      Settings page link.
 * @var string $network_tracking   Network tracking code.
 * @var bool   $auto_tracking      Is tracking code automatically found.
 * @var string $auto_tracking_code Auto tracking code.
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

?>

<input type="hidden" name="beehive_settings_group" value="tracking">

<div class="sui-box-settings-row">

	<?php
	// Network admin.
	if ( is_multisite() && $network ) {
		$label    = esc_html__( 'Network Tracking', 'ga_trans' );
		$desc     = esc_html__( 'Copy and paste your Google Analytics tracking ID to add it to your website. Note: This tracking code will track your whole network. To track subsites, you need to add the tracking code separately for each one.', 'ga_trans' );
		$sub_desc = sprintf( __( 'Having trouble finding your tracking code? You can grab it <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/1032385?rd=1' );
	} elseif ( is_multisite() ) { // Subsite
		$label = esc_html__( 'Subsite Tracking', 'ga_trans' );
		$desc  = esc_html__( 'Copy and paste your Google Analytics tracking ID to add it to your website.', 'ga_trans' );
		// If network admin is configured tracking code and current site's tracking is empty.
		if ( empty( $tracking ) && ! empty( $network_tracking ) ) {
			$sub_desc = sprintf( __( 'Note: Currently your statistics are provided from network wide tracking code. You can increase stats accuracy by <a href="%s">logging in</a> and configuring your own profile.', 'ga_trans' ), $settings_page );
		} else {
			$sub_desc = sprintf( __( 'Having trouble finding your tracking code? You can grab it <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/1032385?rd=1' );
		}
	} else { // Single site.
		$label    = esc_html__( 'Tracking Statistics', 'ga_trans' );
		$desc     = esc_html__( 'Copy and paste your Google Analytics tracking ID to add it to your website.', 'ga_trans' );
		$sub_desc = sprintf( __( 'Having trouble finding your tracking code? You can grab it <a href="%s" target="_blank">here</a>.', 'ga_trans' ), 'https://support.google.com/analytics/answer/1032385?rd=1' );
	}
	?>

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php echo esc_attr( $label ); ?></span>
		<span class="sui-description"><?php echo esc_html( $desc ); ?></span>
	</div>

	<div class="sui-box-settings-col-2">

		<?php $code = beehive_analytics()->settings->get( 'code', 'tracking', $network ); ?>
		<?php $auto_tracking_ready = ! empty( $auto_tracking ) && ! empty( $auto_tracking_code ); ?>

		<?php if ( $auto_tracking_ready ) : // Only when auto tracking enabled. ?>
			<div class="sui-form-field <?php echo empty( $code ) ? '' : 'sui-hidden'; ?>" id="beehive-tracking-code-auto">
				<label for="beehive-settings-tracking-code-auto" class="sui-label">
					<?php esc_html_e( 'Tracking ID', 'ga_trans' ); ?>
					<span class="beehive-icon-tooltip sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_html_e( 'Note: This will only feed data to Google, to view analytics in your Dashboard you\'ll need to authenticate your account on the Settings tab.', 'ga_trans' ); ?>"><i class="sui-icon-info" aria-hidden="true"></i></span>
					<a role="button" href="#" class="sui-label-link"><?php esc_html_e( 'Use a different tracking ID', 'ga_trans' ); ?></a>
				</label>
				<input type="text" id="beehive-settings-tracking-code-auto" class="sui-form-control" value="<?php echo esc_attr( $auto_tracking_code ); ?>" disabled/>
				<span class="sui-description"><?php esc_html_e( 'We have automatically got your tracking code from your connected profile.', 'ga_trans' ); ?></span>
			</div>
		<?php endif; ?>

		<div class="sui-form-field <?php echo empty( $code ) && $auto_tracking_ready ? 'sui-hidden' : ''; ?>" id="beehive-tracking-code-manual">
			<label for="beehive-settings-tracking-code-manual" class="sui-label">
				<?php esc_html_e( 'Tracking ID', 'ga_trans' ); ?>
				<span class="beehive-icon-tooltip sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_html_e( 'Note: This will only feed data to Google, to view analytics in your Dashboard you\'ll need to authenticate your account on the Settings tab.', 'ga_trans' ); ?>"><i class="sui-icon-info" aria-hidden="true"></i></span>
				<?php if ( $auto_tracking_ready ) : ?>
					<a role="button" href="#" class="sui-label-link"><?php esc_html_e( 'Add tracking ID connected to the account', 'ga_trans' ); ?></a>
				<?php endif; ?>
			</label>
			<input type="text" id="beehive-settings-tracking-code-manual" class="sui-form-control" name="tracking[code]" placeholder="<?php esc_html_e( 'E.g: UA-XXXXXXXXX-X', 'ga_trans' ); ?>" value="<?php echo esc_attr( $code ); ?>"/>
			<span class="sui-description"><?php echo $sub_desc; ?></span>
		</div>

		<div class="sui-form-field">
			<div id="beehive-tracking-code-notice"><!-- error notice --></div>
		</div>

	</div>

</div>