<?php
/**
 * Shipper package migration modals: package building cancel template
 *
 * @since v1.1
 * @package shipper
 */

?>

	<p class="shipper-description shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
		<?php esc_html_e( 'Are you sure you want to cancel the package build?', 'shipper' ); ?>
	</p>
</div>

<div class="sui-box-body sui-box-body-slim">
	<div class="shipper-modal-bottom-actions">
		<div class="shipper-modal-bottom-action-left">
			<button type="button" class="sui-button sui-button-ghost shipper-goback">
				<?php esc_html_e( 'Go Back', 'shipper' ); ?>
			</button>
		</div><!-- shipper-modal-bottom-action-left -->
		<div class="shipper-modal-bottom-action-right">
			<button type="button" class="sui-button sui-button-ghost shipper-package-cancel shipper-cancel">
				<?php esc_html_e( 'Cancel Package', 'shipper' ); ?>
			</button>
		</div><!-- shipper-modal-bottom-action-right -->
	</div><!-- shipper-modal-bottom-actions -->
</div>