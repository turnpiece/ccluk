<?php
/**
 * Shipper preflight templates: source section of the wizard
 *
 * @package shipper
 */

$checks    = $result['checks']['local'];
$migration = new Shipper_Model_Stored_Migration();
?>

<div class="shipper-wizard-tab">
	<?php $this->render( 'msgs/wizard-source-errors', array( 'result' => $result ) ); ?>
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
		<?php
		foreach ( $checks['checks'] as $check ) {
			if ( 'ok' === $check['status'] ) {
				continue;
			}
			?>
			<tr class="sui-accordion-item">
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
					?>
					<i aria-hidden="true" class="sui-icon-<?php echo esc_attr( $icon_type ); ?> sui-<?php echo esc_attr( $icon_kind ); ?>"></i>
				</td>
				<td>
					<div class="shipper-check-message">
					<?php
					$message = __( 'No issues found', 'shipper' );
					$raw     = '';
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
							<?php echo esc_html( $check['message'] ); ?>
							<?php if ( 'ok' !== $check['status'] ) { ?>
								<p>
									<a href="#reload" class="sui-button">
										<i class="sui-icon-update" aria-hidden="true"></i>
										<?php esc_html_e( 'Re-check', 'shipper' ); ?>
									</a>
								</p>
							<?php } ?>
						</div>
					</div>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>
<?php if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type() && ! empty( $result['checks']['files'] ) ) { ?>
	<div>
		<?php
		$this->render(
			'pages/preflight/wizard-files',
			array(
				'result'       => $result,
				'has_issues'   => $has_issues,
				'has_errors'   => $has_errors,
				'issues_count' => $issues_count,
				'shipper_url'  => $shipper_url,
			)
		);
		?>
	</div>
<?php } ?>
</div>
