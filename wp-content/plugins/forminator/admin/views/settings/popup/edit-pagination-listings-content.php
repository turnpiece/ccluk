<?php
$per_page     	= get_option( "forminator_pagination_listings", 10 );
?>
<div class="wpmudev-hidden-popup wpmudev-popup-form" style="display: none">

		<div class="wpmudev-row">

			<div class="wpmudev-col col-12">

				<label class="wpmudev-label"><?php _e( "Limit modules per page", Forminator::DOMAIN ); ?></label>

				<input class="wpmudev-input" type="number" min="1" name="pagination_listings" value="<?php echo $per_page; ?>">

				<button class="wpmudev-button wpmudev-action-done wpmudev-button-blue" data-nonce="<?php echo wp_create_nonce( 'forminator_save_popup_pagination_listings' ) ?>"><?php _e( "Apply Changes", Forminator::DOMAIN ); ?></button>

			</div>

		</div>

</div>