<?php
/**
 * Shipper package migration modals: package preflight individual issue template
 *
 * @since v1.1
 * @package shipper
 */

$issue_type_class = 'shipper-issue-' . esc_attr( $task_type );
if ( ! empty( $check_type ) ) {
	$issue_type_class .= ' shipper-issue-' . esc_attr( $check_type );
}
if ( 'ok' === $status ) {
	$status = 'success'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
}
?>
<div class="shipper-issue <?php echo esc_attr( $issue_type_class ); ?>">
	<div class="shipper-issue-title">
		<div class="shipper-issue-severity shipper-severity-<?php echo esc_attr( $status ); ?>">
			<i aria-hidden="true" class="sui-icon-warning-alert"></i>
		</div>

		<div class="shipper-issue-summary">
			<?php echo esc_html( $title ); ?>
		</div>

		<div class="shipper-issue-item-state">
			<i class="sui-icon-chevron-down" aria-hidden="true"></i>
		</div>
	</div><!-- shipper-issue-title -->

	<div class="shipper-issue-body">
		<div class="shipper-issue-body-content">
		<?php
		$this->render(
			'modals/packages/preflight/issue-message',
			array(
				'message'    => $message,
				'status'     => $status,
				'check_type' => ! empty( $check_type ) ? $check_type : false,
			)
		);
		?>
		<div class="shipper-issue-body-footer">
			<button type="button" class="sui-button sui-button-ghost shipper-recheck">
				<i class="sui-icon-update" aria-hidden="true"></i>
				<?php esc_html_e( 'Re-check', 'shipper' ); ?>
			</button>
		</div><!-- shipper-issue-body-footer -->
	</div><!-- shipper-issue-body -->
</div>