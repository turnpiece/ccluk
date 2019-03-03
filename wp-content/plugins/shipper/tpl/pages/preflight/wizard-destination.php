<?php
/**
 * Shipper preflight templates: destination section of the wizard
 *
 * @package shipper
 */

$checks = $result['checks']['remote'];
$has_issues = (bool) $checks['errors_count'];
$has_breaking_issues = (bool) $checks['breaking_errors_count'];
?>

<div class="shipper-wizard-tab">
	<p>
		<?php if ( $has_issues ) { ?>
			<?php if ( $has_breaking_issues ) { ?>
				<?php esc_html_e( 'Your destination server configuration check is complete, but you have an error which prevents the migration.', 'shipper' ); ?>
			<?php } else { ?>
				<?php esc_html_e( 'Your destination server configuration check is complete and you have got a few warnings which you might wanna check before migration.', 'shipper' ); ?>
			<?php } ?>
		<?php } else { // has issues. ?>
			<?php esc_html_e( 'Your source server configuration check is complete.', 'shipper' ); ?>
		<?php } ?>
	</p>

<?php if ( $has_breaking_issues ) { ?>
	<p>
		<?php esc_html_e( 'Please, try re-running the preflight.', 'shipper' ); ?>
	</p>
	<?php if ( ! empty( $checks['errors'] ) ) { ?>
	<div class="sui-notice sui-notice-error">
		<?php foreach ( $checks['errors'] as $error ) { ?>
			<p><?php echo esc_html( $error ); ?></p>
		<?php } ?>
	</div>
	<?php } ?>
<?php } ?>

	<table class="sui-table sui-table-flushed sui-accordion">
		<colgroup>
			<col class="shipper-result-col-1" />
			<col class="shipper-result-col-2" />
			<col class="shipper-result-col-3" />
		</colgroup>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Configuration', 'shipper' ); ?></th>
				<th><?php esc_html_e( 'Status', 'shipper' ); ?></th>
				<th><?php esc_html_e( 'Details', 'shipper' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $checks['checks'] as $check ) { ?>
			<tr class="sui-accordion-item">
				<td class="sui-table-item-title">
					<?php echo esc_html( $check['title'] ); ?>
				</td>

				<td class="shipper-check-status">
				<?php
					$icon_type = 'ok' === $check['status']
						? 'check-tick'
						: 'warning-alert'
					;
					$icon_kind = 'warning-alert' === $icon_type
						? $check['status']
						: 'success'
					;
				?>
					<i aria-hidden="true"
						class="sui-icon-<?php
							echo esc_attr( $icon_type );
						?> sui-<?php echo esc_attr( $icon_kind ); ?>"></i>
				</td>

				<td>
					<div class="shipper-check-message">
					<?php
						$message = __( 'No issues found', 'shipper' );
					if ( ! empty( $check['message'] ) ) {
						$message = trim( wp_strip_all_tags( $check['message'] ) );
					}
						echo esc_html( $message );
					?>
					</div>
				<?php if ( ! empty( $check['message'] ) ) { ?>
					<button class="sui-button-icon sui-accordion-open-indicator">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				<?php } ?>
				</td>
			</tr>
			<?php if ( ! empty( $check['message'] ) ) { ?>
			<tr class="sui-accordion-item-content">
				<td colspan="3">
					<div class="sui-box">
						<div class="sui-box-body">
							<?php echo wp_kses_post( $check['message'] ); ?>
						</div>
					</div>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>
</div>
