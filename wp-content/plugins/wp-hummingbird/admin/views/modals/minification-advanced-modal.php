<dialog id="wphb-advanced-minification-modal" class="small wphb-modal no-close wphb-advanced-minification-modal">
	<div class="wphb-dialog-content dialog-upgrade">
		<h1><?php esc_html_e( 'Just be careful!', 'wphb' ); ?></h1>

		<p><?php esc_html_e( 'Advanced mode gives you full control over your files but can easily break your website if configured incorrectly.', 'wphb' ); ?></p>

		<p><?php _e( '<strong>We recommend you make one tweak at a time</strong> and check the frontend of your website each change to avoid any mishaps. ', 'wphb' ); ?></p>

		<div class="wphb-block-content-center">
			<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_admin_menu_url( 'minification' ) ); ?>" class="button button-grey"><?php esc_html_e( 'Got It', 'wphb' ); ?></a>
		</div>

		<div class="wphb-modal-image wphb-modal-image-bottom dev-man">
			<img class="wphb-image wphb-image-center"
				 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@1x.png'; ?>"
				 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@2x.png'; ?> 2x"
				 alt="<?php esc_attr_e( 'Hummingbird','wphb' ); ?>">
		</div>

	</div>
</dialog><!-- end wphb-advanced-minification-modal -->