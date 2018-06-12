<?php
$per_page = get_option( "forminator_pagination_listings", 10 );
$nonce    = wp_create_nonce( 'forminator_save_popup_pagination_listings' );
?>

<div class="sui-box-body wpmudev-popup-form">
	<div class="sui-form-field">
		<label for="forminator-limit-modules-pp" class="sui-label"><?php esc_html_e( "Limit modules per page", Forminator::DOMAIN ); ?></label>
		<input id="forminator-limit-modules-pp" class="sui-form-control" name="pagination_listings" value="<?php echo esc_attr( $per_page ); ?>">
	</div>
</div>
<div class="sui-box-footer">
	<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></button>
	<div class="sui-actions-right">
		<button class="sui-button sui-button-primary wpmudev-action-done" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<?php esc_html_e( "Save", Forminator::DOMAIN ); ?>
		</button>
	</div>
</div>