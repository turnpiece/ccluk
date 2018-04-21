<div class="dialog sui-dialog" aria-hidden="true" id="check-files-modal">

	<div class="sui-dialog-overlay" tabindex="-1"></div>

	<div class="sui-dialog-content" aria-labelledby="checkingFiles" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="checkingFiles"><?php esc_html_e( 'Checking files', 'wphb' ); ?></h3>
				<div class="sui-actions-right title-action">
					<span><?php esc_html_e( 'File check in progress...', 'wphb' ); ?></span>
				</div>
			</div>

			<div class="sui-box-body">
				<script type="text/javascript">
                    jQuery('label[for="enable_cdn"]').on('click', function(e) {
                        e.preventDefault();
                        var checkbox = jQuery('input[name="enable_cdn"]');
                        checkbox.prop('checked', !checkbox.prop('checked') );
                    });
				</script>
				<p><?php esc_html_e( 'Hummingbird is running a test to measure your website performance, please wait.', 'wphb' ); ?></p>

				<div class="sui-progress-block sui-progress-can-close">
					<div class="sui-progress">
						<div class="sui-progress-text sui-icon-loader sui-loading">
							<span>0%</span>
						</div>
						<div class="sui-progress-bar">
							<span style="width: 0"></span>
						</div>
					</div>
					<button class="sui-progress-close sui-tooltip" id="cancel-minification-check" type="button" data-a11y-dialog-hide data-tooltip="Cancel Test">
						<i class="sui-icon-close"></i>
					</button>
				</div>

				<div class="sui-progress-state">
					<span class="sui-progress-state-text"></span>
				</div>
			<?php
			/* @var WP_Hummingbird_Module_Minify $minify */
			$minify = WP_Hummingbird_Utils::get_module( 'minify' );

			if ( ! is_multisite() && WP_Hummingbird_Utils::is_member() && ! $minify->get_cdn_status() ) : ?>
				<form method="post" class="sui-border-frame" id="enable-cdn-form">
					<label class="sui-toggle">
						<input type="checkbox" name="enable_cdn" id="enable_cdn" checked="checked">
						<span class="sui-toggle-slider"></span>
					</label>
					<label><?php esc_html_e( 'Store my files on the WPMU DEV CDN', 'wphb' ); ?></label>
					<span class="sui-description sui-toggle-description">
							<?php esc_html_e( 'By default your files are hosted on your own server. With this pro setting enabled we will host your files on WPMU DEV’s secure and hyper fast CDN.', 'wphb' ); ?>
						</span>
				</form>
			<?php elseif ( ! is_multisite() || $minify->get_cdn_status() ) : ?>
				<label class="sui-toggle sui-hidden">
					<input type="checkbox" name="enable_cdn" id="enable_cdn" checked="checked">
					<span class="sui-toggle-slider"></span>
				</label>
			<?php endif; ?>

			</div>
			<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
				 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-minify-summary.png'; ?>"
				 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-minify-summary@2x.png'; ?> 2x"
				 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
		</div>

	</div>

</div>