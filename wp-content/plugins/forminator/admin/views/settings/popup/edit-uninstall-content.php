<?php
$uninstall  = get_option( "forminator_uninstall_clear_data", false );
?>
<div class="wpmudev-hidden-popup wpmudev-popup-form" style="display: none">

		<div class="wpmudev-row">

			<div class="wpmudev-col col-12">

				<label><?php _e( "Delete all data  on uninstall", Forminator::DOMAIN ); ?></label>

				<select class="wpmudev-select" name="delete_uninstall">
					<option value="true" <?php selected( $uninstall, true ); ?>><?php _e( "Yes", Forminator::DOMAIN ); ?></option>
					<option value="false" <?php selected( $uninstall, false ); ?>><?php _e( "No", Forminator::DOMAIN ); ?></option>
				</select>

			</div>
		</div>

    <div class="wpmudev-row">
        <div class="wpmudev-col col-12">
            <button class="wpmudev-button wpmudev-action-done wpmudev-button-blue" data-nonce="<?php echo wp_create_nonce( 'forminator_save_popup_uninstall_settings' ) ?>"><?php _e( "Apply Changes", Forminator::DOMAIN ); ?></button>
        </div>
    </div>

</div>