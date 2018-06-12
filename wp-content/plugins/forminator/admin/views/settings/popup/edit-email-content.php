<?php
$sender_email_address = get_global_sender_email_address();
$sender_name = get_global_sender_name();
?>

<div class="sui-box-body wpmudev-popup-form">

	<div class="sui-form-field">

		<label class="sui-label"><?php esc_html_e( "Sender email address", Forminator::DOMAIN ); ?></label>

		<input class="sui-form-control" name="sender_email" value="<?php echo esc_attr( $sender_email_address ); ?>">

	</div>

	<div class="sui-form-field">

		<label class="sui-label"><?php esc_html_e( "Sender name", Forminator::DOMAIN ); ?></label>

		<input class="sui-form-control" name="sender_name" value="<?php echo esc_attr( $sender_name ); ?>">

	</div>

</div>

<div class="sui-box-footer">

	<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></button>

	<div class="sui-actions-right">

		<button class="sui-button sui-button-primary wpmudev-action-done" data-nonce="<?php echo wp_create_nonce( 'forminator_save_popup_email_settings' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Apply Changes", Forminator::DOMAIN ); ?></button>

	</div>

</div>