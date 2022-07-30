<?php
/**
 * Shipper package migration modals: new package confirmation template
 *
 * @since v1.1
 * @package shipper
 */

?>
	<p class="shipper-description shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
		<?php esc_html_e( 'Are you sure you wish to create a new package? This will override your existing package since you can only have one package at a time to avoid any security risks.', 'shipper' ); ?>
	</p>
</div>

<div class="sui-box-body sui-box-body-slim">
	<input type="hidden"
		name="shipper-reset-package"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper-reset-package' ) ); ?>"
	/>

	<div class="shipper-modal-bottom-actions">
		<div class="shipper-modal-bottom-action-left">
			<button type="button" class="sui-button sui-button-ghost shipper-cancel">
				<?php esc_html_e( 'Cancel', 'shipper' ); ?>
			</button>
		</div><!-- shipper-modal-bottom-action-left -->
		<div class="shipper-modal-bottom-action-right">
			<button type="button" class="sui-button shipper-next">
				<?php esc_html_e( 'Continue', 'shipper' ); ?>
		</div><!-- shipper-modal-bottom-action-right -->
	</div><!-- shipper-modal-bottom-actions -->
</div>