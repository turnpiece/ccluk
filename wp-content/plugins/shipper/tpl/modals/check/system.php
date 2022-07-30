<?php
/**
 * Shipper modal dialogs: local system check
 *
 * @package shipper
 */

$model = new Shipper_Model_Stored_Modals();
if ( Shipper_Model_Stored_Modals::STATE_CLOSED === $model->get( 'system', Shipper_Model_Stored_Modals::STATE_OPEN ) ) {
	// User dismissed this modal - don't even bother.
	return false;
}

$task  = new Shipper_Task_Check_System();
$model = new Shipper_Model_System();

$status = $task->apply( $model->get_data() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$errors = count( $task->get_errors() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$checks = $task->get_checks();

foreach ( $checks as $check ) {
	if ( $check->is_fatal() ) {
		$errors++;
	}
}

if ( ! empty( $status ) && empty( $errors ) ) {
	return false;
}

?>
<div class="sui-dialog shipper-system-check shipper-check-result" data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'shipper_modal_close' ) ); ?>" aria-hidden="true">
	<div class="sui-dialog-overlay sui-fade-in" tabindex="-1" data-a11y-dialog-hide=""></div>

	<div class="sui-dialog-content sui-bounce-in" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-body shipper-system-check-body">

				<div class="shipper-system-title">
					<div class="shipper-actions-left">
						<a href="#recheck">
							<i class="sui-icon-update" aria-hidden="true"></i>
						</a>
					</div>
					<h3 class="shipper-dialog-title"><?php esc_html_e( 'Requirements failed', 'shipper' ); ?></h3>

					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: admin username. */
								__( '%s, we’ve uncovered a few potential issues that may cause the migration to fail. Please fix the following errors to continue using the plugin.', 'shipper' ),
								shipper_get_user_name()
							)
						);
						?>
					</p>
				</div>

				<table class="sui-table sui-accordion shipper-system-checks-list">
				<?php foreach ( $checks as $check ) { ?>
					<?php
					if ( ! $check->is_fatal() ) {
						continue;
					}
					?>
					<tr class="sui-accordion-item sui-error">
						<td class="sui-accordion-item-title">
							<i class="sui-icon-warning-alert sui-error"></i>
							<b><?php echo esc_html( $check->get( 'title' ) ); ?></b>
						</td>
						<td>
							<span class="sui-accordion-open-indicator">
								<i class="sui-icon-chevron-down"></i>
							</span>
						</td>
					</tr>
					<tr class="sui-accordion-item-content">
						<td colspan=2>
							<div class="sui-box">
								<div class="sui-box-body">
									<?php echo wp_kses_post( $check->get( 'message' ) ); ?>
									<div class="sui-row shipper-item-actions">
										<div class="sui-col">
											<a href="#recheck" type="button" class="sui-button sui-button-ghost">
												<i class="sui-icon-update" aria-hidden="true"></i>
												<?php esc_html_e( 'Re-check', 'shipper' ); ?>
											</a>
										</div>
									</div><?php // .shipper-item-actions ?>
								</div>
							</div>
						</td>
					</tr>
				<?php } ?>
				</table>

				<div class="shipper-system-footer">
					<a href="#recheck" type="button" class="sui-button">
						<i class="sui-icon-update" aria-hidden="true"></i>
						<?php esc_attr_e( 'Re-check', 'shipper' ); ?>
					</a>
				</div>

			</div><?php // .sui-box-body ?>

		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>

</div><?php // .sui-dialog ?>