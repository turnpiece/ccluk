<dialog class="wphb-modal small wphb-progress-modal no-close" id="enable-cdn-modal" title="<?php _e( 'Activate CDN', 'wphb' ); ?>">
	<script type="text/javascript">
		jQuery('label[for="enable_cdn"]').on('click', function(e) {
			e.preventDefault();
			var checkbox = jQuery('input[name="enable_cdn"]');
			checkbox.prop('checked', !checkbox.prop('checked') );
		});
	</script>
	<div class="title-action">
		<?php _e( 'File check complete.' , 'wphb' ); ?>
	</div>
	<div class="wphb-dialog-content">
		<p><?php _e( 'Do you want to store your minified files on WPMU DEV’s super-fast CDN? It’s absolutely free for WPMU DEV members!', 'wphb' ); ?></p>

		<form method="post">
			<div class="wphb-block-test" id="check-files-modal-content">
				<span class="toggle">
					<input type="hidden" name="enable_cdn" value="0">
					<input type="checkbox" class="toggle-checkbox" name="enable_cdn" id="enable_cdn" value="1" checked="checked">
					<label class="toggle-label" for="enable_cdn"></label>
				</span>
				<label><?php _e( 'Host my files on the WPMU DEV CDN', 'wphb' ); ?></label>
			</div>
			<div>
				<a href="<?php echo wphb_cdn_link(); ?>" target="_blank"><?php _e( 'What is a CDN?', 'wphb' ); ?></a>
				<button type="submit" class="button button-large alignright"><?php _e( 'Continue', 'wphb' ); ?></button>
			</div>
		</form>

		<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
			 src="<?php echo wphb_plugin_url() . 'admin/assets/image/graphic-hb-minify-summary.png'; ?>"
			 srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/graphic-hb-minify-summary@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
	</div>
</dialog>