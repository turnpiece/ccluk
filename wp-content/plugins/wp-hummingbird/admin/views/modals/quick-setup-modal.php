<dialog id="wphb-quick-setup-modal" class="small wphb-modal wphb-quick-setup-modal no-close" title="<?php esc_attr_e( 'Quick Setup', 'wphb' ); ?>">
	<div class="title-action">
		<input type="button" class="button button-ghost" id="skip-quick-setup" value="<?php esc_attr_e( 'Skip', 'wphb' ); ?>" onclick="window.WPHB_Admin.dashboard.skipSetup()">
	</div>
	<div class="wphb-dialog-content">
		<p><?php esc_html_e( 'Welcome to Hummingbird, the hottest Performance plugin for WordPress! We recommend running a quick performance test before you start tweaking things. Alternatively you can skip this step if you’d prefer to start customizing.', 'wphb' ); ?></p>
		<div class="wphb-block-test" id="check-files-modal-content">
			<p><?php esc_html_e( 'This is only a performance test. Once you know what to fix you can get started in the next steps.', 'wphb' ); ?></p>
			<input type="button" class="button button-large" value="<?php esc_attr_e( 'Test my website', 'wphb' ); ?>" onclick="window.WPHB_Admin.dashboard.runPerformanceTest()">
		</div>
		<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
	</div>
</dialog><!-- end wphb-upgrade-membership-modal -->