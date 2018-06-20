<?php
/**
 * Quick setup modal
 *
 * @package Hummingbird
 */

?>
<div class="dialog sui-dialog" aria-hidden="true" id="wphb-quick-setup-modal">

	<div class="sui-dialog-overlay" tabindex="-1"></div>

	<div class="sui-dialog-content" aria-labelledby="quickSetup" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="quickSetup"><?php esc_html_e( 'Quick Setup', 'wphb' ); ?></h3>
				<div class="sui-actions-right">
					<input type="button" class="sui-button sui-button-ghost" id="skip-quick-setup" data-a11y-dialog-hide value="<?php esc_attr_e( 'Skip', 'wphb' ); ?>" onclick="window.WPHB_Admin.dashboard.skipSetup()">
				</div>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'Welcome to Hummingbird, the hottest Performance plugin for WordPress! We recommend running a quick performance test before you start tweaking things. Alternatively you can skip this step if you’d prefer to start customizing.', 'wphb' ); ?></p>
				<div class="sui-border-frame">
					<div class="sui-row">
						<div class="sui-col-md-7">
							<?php esc_html_e( 'This is only a performance test. Once you know what to fix you can get started in the next steps.', 'wphb' ); ?>
						</div>
						<div class="sui-col-md-5 textright">
							<input type="button" class="sui-button sui-button-primary" value="<?php esc_attr_e( 'Run Performance Test', 'wphb' ); ?>" onclick="window.WPHB_Admin.dashboard.runPerformanceTest()">
						</div>
					</div>
				</div>
			</div>

			<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
				 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup.png' ); ?>"
				 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup@2x.png' ); ?> 2x"
				 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
		</div>

	</div>

</div>