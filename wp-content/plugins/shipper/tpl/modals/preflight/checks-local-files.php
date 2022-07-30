<?php
/**
 * Shipper templates: preflight modal, local checks, files report template
 *
 * @since v1.0.3
 * @package shipper
 */

$checks     = $result['checks']['files'];
$has_issues = (bool) $checks['errors_count'];
?>

<div class="sui-accordion sui-accordion-block" data-section="files">

	<?php
	foreach ( $checks['checks'] as $check_type => $check ) {
		if ( isset( $check['status'] ) && 'ok' === $check['status'] && 'package_size' !== $check['check_type'] ) {
			continue;
		}

		if ( 'is_done' === $check_type ) {
			continue;
		}


		$check_id = ! empty( $check['check_id'] )
			? $check['check_id']
			: ( ! empty( $check['title'] ) ? md5( $check['title'] ) : '' );
		?>
		<div class="sui-accordion-item shipper-<?php echo esc_attr( $check['check_type'] ); ?>" data-check_item="<?php echo esc_attr( $check_id ); ?>">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<?php
						$this->render(
							'tags/status-icon-preflight-check',
							array( 'item' => $check )
						);
					?>
					<?php echo esc_html( $check['title'] ); ?>
				</div>
				<div class="shipper-check-status">
					<?php
					$content = ! empty( $check['count'] ) ? $check['count'] : 0;
					$type    = $content > 0 ? 'warning' : 'success'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable

					if ( 'package_size' === $check['check_type'] ) {
						$estimate = new Shipper_Model_Stored_Estimate();
						$content  = size_format( $estimate->get( 'package_size' ) );
						$type     = 'ok' === $check['status'] ? 'success' : 'warning'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
					}

					$this->render(
						'tags/status-text',
						array(
							'status' => $type,
							'text'   => $content,
						)
					);
					?>
				</div>
				<div>
					<button class="sui-button-icon sui-accordion-open-indicator">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
			<?php if ( ! empty( $check['message'] ) ) { ?>
				<div class="shipper-wizard-result-files">
					<?php
						$check_type = ! empty( $check['check_type'] ) ? $check['check_type'] : $check_type;

						echo preg_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
						);
					?>
				</div>
			<?php } ?>
			</div>
		</div>
	<?php } ?>


	<div class="sui-notice-top sui-notice-success sui-can-dismiss shipper-toggle-success" style="display:none">
		<div class="sui-notice-content shipper-exclude-success" style="display:none">
			<p>
				<span class="shipper-toggle-count">0</span>
				<?php esc_html_e( 'files excluded from migration successfully.', 'shipper' ); ?>
			</p>
		</div>
		<div class="sui-notice-content shipper-include-success" style="display:none">
			<p>
				<span class="shipper-toggle-count">0</span>
				<?php esc_html_e( 'files included to migration successfully.', 'shipper' ); ?>
			</p>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
</div>