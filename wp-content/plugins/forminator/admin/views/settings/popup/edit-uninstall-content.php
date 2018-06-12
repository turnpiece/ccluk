<?php
$uninstall = get_option( "forminator_uninstall_clear_data", false );
$nonce     = wp_create_nonce( 'forminator_save_popup_uninstall_settings' );
?>


<div class="sui-box-body wpmudev-popup-form">
	<div class="sui-form-field">

		<label for="forminator-delete_uninstall-entries" class="sui-label"><?php esc_html_e( "Delete all data  on uninstall", Forminator::DOMAIN ); ?></label>

		<select name="delete_uninstall" id="delete_uninstall">
			<option value="true" <?php echo esc_attr(selected( $uninstall, true )); ?>><?php esc_html_e( "Yes", Forminator::DOMAIN ); ?></option>
			<option value="false" <?php echo esc_attr(selected( $uninstall, false )); ?>><?php esc_html_e( "No", Forminator::DOMAIN ); ?></option>
		</select>

	</div>
</div>
<div class="sui-box-footer">
	<div class="sui-flex-child-right">
		<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></button>
		<button class="sui-button sui-button-primary wpmudev-action-done" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<?php esc_html_e( "Save", Forminator::DOMAIN ); ?>
		</button>
	</div>
</div>