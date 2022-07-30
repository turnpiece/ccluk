<?php
/**
 * Shipper messages: wizard source tab top area notice template
 *
 * @package shipper
 */

$checks              = $result['checks']['local'];
$has_issues          = (bool) $checks['errors_count'];
$has_breaking_issues = (bool) $checks['breaking_errors_count'];
?>

<?php if ( $has_issues ) { ?>
	<?php if ( $has_breaking_issues ) { ?>
		<div class="sui-notice sui-notice-error">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<p>
						<?php esc_html_e( 'The errors and warnings highlighted below are likely to cause migrations to fail. We highly recommend fixing the issues and re-running the pre-flight check before continuing.', 'shipper' ); ?>
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
	<?php esc_html_e( 'Your source server configuration check is complete.', 'shipper' ); ?>
<?php } ?>