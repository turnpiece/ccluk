<?php
/**
 * Shipper templates: preflight wizard, files tab template
 *
 * @package shipper
 */

$checks = $result['checks']['files'];
$has_issues = (bool) $checks['errors_count'];
?>

<div class="shipper-wizard-tab shipper-wizard-files">
	<p>
		<?php if ( $has_issues ) { ?>
			<?php esc_html_e( 'Files check is complete and you have a few warnings with your files.', 'shipper' ); ?>
			<?php esc_html_e( 'You can try to resolve these warnings before the migration process begins.', 'shipper' ); ?>
		<?php } else { //has issues ?>
			<?php esc_html_e( 'Your source files check is complete.', 'shipper' ); ?>
		<?php } ?>
	</p>

	<table class="sui-table sui-table-flushed sui-accordion">
		<colgroup>
			<col class="shipper-result-col-1" />
			<col class="shipper-result-col-2" />
			<col class="shipper-result-col-3" />
		</colgroup>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Issue', 'shipper' ); ?></th>
				<th><?php esc_html_e( 'Status', 'shipper' ); ?></th>
				<th><?php esc_html_e( 'Details', 'shipper' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $checks['checks'] as $check_type => $check ) { ?>
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
						class="sui-icon-<?php echo $icon_type; ?> sui-<?php echo $icon_kind; ?>"></i>
				</td>

				<td>
				<?php
					$message = __( 'No issues found', 'shipper' );
					if ( 'ok' !== $check['status'] ) {
						$message = ! empty( $check['short_message'] )
							? $check['short_message']
							: sprintf( __( 'We detected some issues with %s', 'shipper' ), $check['title'] )
							;
					}
					echo $message;
				?>
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
						<div class="sui-box-body shipper-wizard-result-files">
							<?php
								$check_type = ! empty( $check['check_type'] )
									? $check['check_type']
									: false
								;
								echo preg_replace(
									'/' . preg_quote( '{{', '/' ) .
									'shipper-nonce-placeholder' .
									preg_quote( '}}', '/' ) . '/',
									wp_create_nonce( 'shipper_path_toggle' ),
									$this->get(
										'pages/preflight/wizard-files-result-wrapper',
										array(
											'html' => $check['message'],
											'check_type' => $check_type,
										)
									)
								);
							?>
						</div>
					</div>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>

	<div class="sui-notice-top sui-notice-success sui-can-dismiss shipper-toggle-success" style="display:none">
		<div class="sui-notice-content shipper-exclude-success" style="display:none">
			<p>
				<span class="shipper-toggle-count">0</span>
				<?php esc_html_e('files excluded from migration successfully.'); ?>
			</p>
		</div>
		<div class="sui-notice-content shipper-include-success" style="display:none">
			<p>
				<span class="shipper-toggle-count">0</span>
				<?php esc_html_e('files included to migration successfully.'); ?>
			</p>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>

</div>