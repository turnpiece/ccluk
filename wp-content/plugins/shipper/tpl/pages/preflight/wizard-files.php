<?php
/**
 * Shipper templates: preflight wizard, files tab template
 *
 * @package shipper
 */

$checks     = $result['checks']['files'];
$has_issues = (bool) $checks['errors_count'];
?>

<div class="shipper-wizard-tab shipper-wizard-files">
	<?php if ( $has_issues ) { ?>
	<div class="sui-notice sui-notice-warning">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<p>
					<?php esc_html_e( 'Files check is complete and you have a few warnings with your files.', 'shipper' ); ?>
					<?php esc_html_e( 'You can try to resolve these warnings before the migration process begins.', 'shipper' ); ?>
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
		<p><?php esc_html_e( 'Your source files check is complete.', 'shipper' ); ?></p>
	<?php } ?>

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
		<?php
		foreach ( $checks['checks'] as $check_type => $check ) {
			if ( 'is_done' === $check_type ) {
				continue;
			}
			?>
			<tr class="sui-accordion-item shipper-<?php echo esc_attr( $check['check_type'] ); ?>">
				<td class="sui-table-item-title">
					<?php echo esc_html( $check['title'] ); ?>
				</td>

				<td class="shipper-check-status">
				<?php
				$icon_type = 'ok' === $check['status']
					? 'check-tick'
					: 'warning-alert';
				$icon_kind = 'warning-alert' === $icon_type
					? $check['status']
					: 'success';
				$content   = 0;
				if ( 'ok' !== $check['status'] ) {
					if ( in_array(
						$check['check_type'],
						array( 'file_sizes', 'file_names' ),
						true
					) ) {
						$content    = ! empty( $check['count'] )
							? (int) $check['count']
							: $content;
						$exclusions = new Shipper_Model_Stored_Exclusions();
						if ( count( $exclusions->get_data() ) >= $check['count'] ) {
							$icon_kind = 'success';
						}
					}
					if ( 'package_size' === $check['check_type'] ) {
						$estimate = new Shipper_Model_Stored_Estimate();
						$content  = size_format( $estimate->get( 'package_size' ) );
					}
				}

				if ( 'package_size' === $check['check_type'] ) {
					$chk     = new Shipper_Task_Check_Files();
					$content = size_format( $chk->get_updated_package_size() );
				}

				$zero_class = '';
				if ( empty( $content ) && in_array( $check['check_type'], array( 'file_sizes', 'file_names' ), true ) ) {
					$zero_class = 'shipper-zero';
				}
				?>

					<span
						class="sui-tag sui-tag-<?php echo esc_attr( $icon_kind ); ?>
						<?php echo esc_attr( $zero_class ); ?>">
						<?php echo esc_html( $content ); ?>
					</span>
				</td>

				<td>
				<?php
				$message_ok      = __( 'No issues found', 'shipper' );
				$generic_warning = sprintf(
					/* translators: %s: error message. */
					__( 'We detected some issues with %s', 'shipper' ),
					$check['title']
				);
				?>
				<div
					data-shipper-success-msg="<?php echo esc_attr( $message_ok ); ?>"
					data-shipper-warning-msg="<?php echo esc_attr( $generic_warning ); ?>"
					class="shipper-check-message">
				<?php
				$message = $message_ok;
				if ( 'ok' !== $check['status'] ) {
					$message = ! empty( $check['short_message'] )
						? $check['short_message']
						: $generic_warning;
				}
				if ( 'package_size' === $check['check_type'] && 'ok' === $check['status'] ) {
					$message = '<span class="shipper-package-size-summary">' . $message . '</span>';
				}
				echo wp_kses_post( $message );
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
			<tr class="sui-accordion-item-content shipper-<?php echo esc_attr( $check['check_type'] ); ?>">
				<td colspan="3">
					<div class="sui-box">
						<div class="sui-box-body shipper-wizard-result-files">
							<?php
							$check_type = ! empty( $check['check_type'] ) ? $check['check_type'] : false;
							echo wp_kses_post(
								preg_replace(
									'/' . preg_quote( '{{', '/' ) .
									'shipper-nonce-placeholder' .
									preg_quote( '}}', '/' ) . '/',
									wp_create_nonce( 'shipper_path_toggle' ),
									$this->get(
										'pages/preflight/wizard-files-result-wrapper',
										array(
											'html'       => $check['message'],
											'check_type' => $check_type,
										)
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

	<div id="wizard-files-notice-inline-dismiss" class="sui-notice sui-notice-top sui-notice-success sui-can-dismiss shipper-toggle-success" style="display:none">
		<div class="sui-notice-content shipper-exclude-success" style="display:none">
			<div class="sui-notice-message">
				<p>
					<span class="shipper-toggle-count">0</span>
					<?php esc_html_e( 'files excluded from migration successfully.', 'shipper' ); ?>
				</p>
			</div>
		</div>
		<div class="sui-notice-content shipper-include-success" style="display:none">
			<div class="sui-notice-message">
				<p>
					<span class="shipper-toggle-count">0</span>
					<?php esc_html_e( 'files included to migration successfully.', 'shipper' ); ?>
				</p>
			</div>
		</div>

		<div class="sui-notice-actions">
			<button class="sui-button-icon" data-notice-close="wizard-files-notice-inline-dismiss">
				<i class="sui-icon-check" aria-hidden="true"></i>
				<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
			</button>
		</div>
	</div>
</div>