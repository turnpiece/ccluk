<dialog id="wphb-basic-minification-modal" class="small wphb-modal no-close wphb-basic-minification-modal">
	<div class="wphb-dialog-content dialog-upgrade">
		<h1><?php esc_html_e( 'Are you sure?', 'wphb' ); ?></h1>

		<p><?php esc_html_e( 'Switching back to Basic mode will keep your basic compression settings, but you’ll lose any advanced configuration you have set up.', 'wphb' ); ?></p>

		<div class="wphb-block-content-center">
			<a href="#" class="close button button-ghost"><?php esc_html_e( 'Go back', 'wphb' ); ?></a>
			<a href="#" onclick="WPHB_Admin.minification.switchView()" class="button button-grey"><?php esc_html_e( 'Switch to basic mode', 'wphb' ); ?></a>
		</div>

		<div class="wphb-modal-image wphb-modal-image-bottom dev-man">
			<img class="wphb-image wphb-image-center"
				 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@1x.png'; ?>"
				 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@2x.png'; ?> 2x"
				 alt="<?php esc_attr_e( 'Hummingbird','wphb' ); ?>">
		</div>

	</div>
</dialog><!-- end wphb-basic-minification-modal -->