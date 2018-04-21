<div class="dialog sui-dialog" aria-hidden="true" id="run-performance-test-modal">

	<div class="sui-dialog-overlay" tabindex="-1"></div>

	<div class="sui-dialog-content" aria-labelledby="runPerformanceScan" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="runPerformanceScan"><?php esc_html_e( 'Test in progress', 'wphb' ); ?></h3>
			</div>

			<div class="sui-box-body">

				<p><?php esc_html_e( 'Hummingbird is running a test to measure your website performance, please wait.', 'wphb' ); ?></p>

				<div class="sui-progress-block">
					<div class="sui-progress">
						<div class="sui-progress-text sui-icon-loader sui-loading">
							<span>0%</span>
						</div>
						<div aria-hidden="true" class="sui-progress-bar">
							<span style="width: 0"></span>
						</div>
					</div>
				</div>

				<div class="sui-progress-state">
					<span class="sui-progress-state-text"><?php esc_html_e( 'Performance test in progress...', 'wphb' ); ?></span>
				</div>
			</div>
			<img class="wphb-image wphb-image-center wphb-modal-image-bottom"
				 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup.png'; ?>"
				 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup@2x.png'; ?> 2x"
				 alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
		</div>

	</div>

</div>