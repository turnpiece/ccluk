<?php
/**
 * Shipper messages: wizard destination tab top area notice template
 *
 * @package shipper
 */

$checks              = $result['checks']['remote'];
$has_issues          = (bool) $checks['errors_count'];
$has_breaking_issues = (bool) $checks['breaking_errors_count'];
$has_service_errors  = ! empty( $checks['errors'] );
?>

<?php if ( $has_issues ) { ?>
	<?php if ( $has_breaking_issues ) { ?>
		<div class="sui-notice sui-notice-error shipper-service-error">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<?php if ( $has_service_errors ) { ?>
						<?php foreach ( $checks['errors'] as $error ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global ?>
							<?php /* translators: %s: error message. */ ?>
							<p><?php echo esc_html( sprintf( __( 'Error: %s', 'shipper' ), $error ) ); ?></p>
						<?php } ?>
						<p><?php esc_html_e( 'Shipper was unable to receive or process your destination’s system info.', 'shipper' ); ?>
						<p><?php esc_html_e( 'You can try the following to troubleshoot this:', 'shipper' ); ?>
						<p>
							<?php esc_html_e( '1. Make sure the Shipper plugin is installed on your destination website, and you\'re logged in to the WPMU DEV Dashboard.', 'shipper' ); ?><br />
							<?php esc_html_e( '2. Make sure that both your source and destination sites are using the same version of Shipper and preferably the latest version.', 'shipper' ); ?><br />
							<?php esc_html_e( '3. Try to deactivate and activate the Shipper plugin on your destination. This will force the Shipper API to fetch system info.', 'shipper' ); ?><br />
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: wpmudev support link. */
									__( '4. Still not able to resolve the issue? <a href="%s" target="_blank">Contact support.</a>', 'shipper' ),
									'https://wpmudev.com/get-support/'
								)
							);
							?>
						</p>

					<?php } else { ?>
						<p>
							<?php esc_html_e( 'The errors and warnings highlighted below are likely to cause migrations to fail. We highly recommend fixing the issues and re-running the pre-flight check before continuing.', 'shipper' ); ?>
						</p>
					<?php } ?>
					<p>
						<a href="#reload" class="sui-button sui-button-ghost">
							<i class="sui-icon-update" aria-hidden="true"></i>
							<?php esc_html_e( 'Re-check', 'shipper' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
	<?php } else { ?>
		<div class="sui-notice sui-notice-warning">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<p>
						<?php esc_html_e( 'You have got a few warnings which you may want to fix before starting the migration.', 'shipper' ); ?>
					</p>
					<p>
						<a href="#reload" class="sui-button sui-button-ghost">
							<i class="sui-icon-update" aria-hidden="true"></i>
							<?php esc_html_e( 'Re-check', 'shipper' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } else { // has issues. ?>
	<?php esc_html_e( 'Your destination server configuration check is complete.', 'shipper' ); ?>
<?php } ?>