<div class="dialog sui-dialog sui-dialog-sm" aria-hidden="true" id="wphb-advanced-minification-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="switchAdvanced" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="switchAdvanced">
					<?php esc_html_e( 'Just be careful!', 'wphb' ); ?>
				</h3>
				<button data-a11y-dialog-hide class="sui-dialog-close" aria-label="Close this dialog window"></button>
			</div>

			<div class="sui-box-body">

				<p><?php esc_html_e( 'Advanced mode gives you full control over your files but can easily break your website if configured incorrectly.', 'wphb' ); ?></p>

				<p><?php _e( '<strong>We recommend you make one tweak at a time</strong> and check the frontend of your website each change to avoid any mishaps. ', 'wphb' ); ?></p>

				<div class="sui-block-content-center">
					<a onclick="WPHB_Admin.minification.switchView( 'advanced' )"  class="sui-button">
						<?php esc_html_e( 'Got It', 'wphb' ); ?>
					</a>
				</div>
			</div>

			<div class="wphb-modal-image wphb-modal-image-bottom dev-man">
				<img class="wphb-image"
					 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@1x.png' ); ?>"
					 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@2x.png' ); ?> 2x"
					 alt="<?php esc_attr_e( 'Hummingbird','wphb' ); ?>">
			</div>
		</div>

	</div>

</div>