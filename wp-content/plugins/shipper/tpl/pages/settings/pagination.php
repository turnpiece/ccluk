<?php
/**
 * Shipper settings: Pagination subpage template
 *
 * @package shipper
 */

$model    = new Shipper_Model_Stored_Options();
$per_page = $model->get( Shipper_Model_Stored_Options::KEY_PER_PAGE, 10 ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
?>

<div class="sui-box shipper-page-settings-pagination">
	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Pagination', 'shipper' ); ?></h2>
	</div>

	<form method="POST">
		<input
			type="hidden"
			name="pagination[shipper-nonce]"
			value="<?php echo esc_attr( wp_create_nonce( 'shipper-pagination' ) ); ?>"
		>
		<div class="sui-box-body">
			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<label class="sui-settings-label"><?php esc_html_e( 'Pre-flight check', 'shipper' ); ?></label>
					<p class="shipper-description">
						<?php esc_html_e( 'Choose the number of entries per page for the pre-flight results such as large files and files with large path listings.', 'shipper' ); ?>
					</p>
				</div>

				<div class="sui-box-settings-col-2">
					<div class="sui-form-field">
						<label for="shipper-pagination" class="sui-label"><?php esc_html_e( 'Entries per page', 'shipper' ); ?></label>
						<input
							type="number"
							id="shipper-pagination"
							value="<?php echo esc_attr( $per_page ); ?>"
							name="pagination[<?php echo esc_attr( Shipper_Model_Stored_Options::KEY_PER_PAGE ); ?>]"
							class="sui-form-control sui-input-sm sui-field-has-suffix"
							min="0"
							max="20"
							step="1"
						>
					</div>
				</div>
			</div>
		</div>

		<div class="sui-box-footer shipper-settings-footer">
			<div class="sui-col shipper-actions">
				<button class="sui-button sui-button-primary shipper-pagination-save">
					<?php esc_html_e( 'Save changes', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>