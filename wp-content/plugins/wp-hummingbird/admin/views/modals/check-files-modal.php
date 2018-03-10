<dialog class="wphb-modal small wphb-progress-modal no-close" id="check-files-modal" title="<?php esc_attr_e( 'Checking files', 'wphb' ); ?>">
	<script type="text/javascript">
		jQuery('label[for="enable_cdn"]').on('click', function(e) {
			e.preventDefault();
			var checkbox = jQuery('input[name="enable_cdn"]');
			checkbox.prop('checked', !checkbox.prop('checked') );
		});
	</script>
	<div class="title-action">
		<span><?php esc_html_e( 'File check in progress...', 'wphb' ); ?></span>
	</div>
	<div class="wphb-dialog-content">
		<p><?php esc_html_e( 'Hummingbird is running a file check to see what files can be optimized.', 'wphb' ); ?></p>

		<div class="wphb-block-test" id="check-files-modal-content">
			<div class="wphb-scan-progress">
				<div class="wphb-scan-progress-text">
					<span>0%</span>
				</div><!-- end wphb-scan-progress-text -->
				<div class="wphb-scan-progress-bar">
					<span style="width: 0"></span>
				</div><!-- end wphb-scan-progress-bar -->
				<div class="wphb-scan-cancel-button">
					<a href="#" id="cancel-minification-check"><?php esc_html_e( 'Cancel', 'wphb' ); ?></a>
				</div>
			</div><!-- end wphb-scan-progress -->
		</div><!-- end wphb-block-test -->

		<?php
		/* @var WP_Hummingbird_Module_Minify $minify */
		$minify = WP_Hummingbird_Utils::get_module( 'minify' );

		if ( ! is_multisite() && WP_Hummingbird_Utils::is_member() && ! $minify->get_cdn_status() ) : ?>
			<form method="post" class="wphb-cdn-block wphb-notice-box" id="enable-cdn-form">
				<div>
					<span class="toggle">
						<input type="checkbox" class="toggle-checkbox" name="enable_cdn" id="enable_cdn" checked="checked">
						<label class="toggle-label small" for="enable_cdn"></label>
					</span>
					<label><?php _e( 'Store my files on the WPMU DEV CDN', 'wphb' ); ?></label>
				</div>
				<p>
					<?php esc_html_e( 'By default your files are hosted on your own server. With this pro setting enabled we will host your files on WPMU DEV’s secure and hyper fast CDN.', 'wphb' ); ?>
				</p>
			</form>
		<?php elseif ( ! is_multisite() || $minify->get_cdn_status() ) : ?>
			<input type="hidden" class="toggle-checkbox" name="enable_cdn" id="enable_cdn" checked="checked">
		<?php endif; ?>

		<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-minify-summary.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-minify-summary@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
	</div><!-- end wphb-dialog-content -->
</dialog><!-- end check-files-modal -->