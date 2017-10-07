<dialog class="wphb-modal small wphb-progress-modal no-close" id="check-files-modal" title="<?php esc_attr_e( 'Checking files', 'wphb' ); ?>">
	<div class="title-action">
		<input type="button" class="button button-ghost" id="cancel-minification-check" value="<?php esc_attr_e( 'Cancel', 'wphb' ); ?>">
	</div>
	<div class="wphb-dialog-content">
		<p><?php esc_html_e( 'Hummingbird is currently checking which files your website is outputting. She will give you the controls to compress, combine, move and defer loading of files to help reduce your page loads times.', 'wphb' ); ?></p>

		<div class="wphb-block-test" id="check-files-modal-content">
			<div class="wphb-scan-progress">
				<div class="wphb-scan-progress-text">
					<span>0%</span>
				</div><!-- end wphb-scan-progress-text -->
				<div class="wphb-scan-progress-bar">
					<span style="width: 0%"></span>
				</div><!-- end wphb-scan-progress-bar -->
			</div><!-- end wphb-scan-progress -->
		</div><!-- end wphb-block-test -->

		<div class="wphb-progress-state">
			<span class="wphb-progress-state-text"><?php esc_html_e( 'Check Files is running in the background, you can check back anytime to see progress...', 'wphb' ); ?></span>
		</div><!-- end wphb-progress-state -->

		<div class="wphb-notice wphb-notice-warning wphb-notice-box">
			<p><?php esc_html_e( 'Note: Moving files between the header and footer of your page can break your website. We recommend tweaking and checking each file as you go and if a setting causes errors then revert the setting here.', 'wphb' ); ?></p>
		</div>

		<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
			 src="<?php echo wphb_plugin_url() . 'admin/assets/image/graphic-hb-minify-summary.png'; ?>"
			 srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/graphic-hb-minify-summary@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
	</div><!-- end wphb-dialog-content -->
</dialog><!-- end check-files-modal -->