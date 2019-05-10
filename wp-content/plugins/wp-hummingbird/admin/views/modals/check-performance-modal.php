<?php
/**
 * Performance test modal.
 *
 * @package Hummingbird
 */

?>

<div class="dialog sui-dialog run-performance-test-modal" aria-hidden="true" id="run-performance-test-modal">

	<div class="sui-dialog-overlay" tabindex="-1"></div>

	<div class="sui-dialog-content" aria-labelledby="runPerformanceScan" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="runPerformanceScan">
					<?php esc_html_e( 'Test in progress', 'wphb' ); ?>
				</h3>
			</div>

			<div class="sui-box-body">

				<p><?php esc_html_e( 'Hummingbird is running a test to measure your website performance, please wait.', 'wphb' ); ?></p>

				<div class="sui-progress-block">
					<div class="sui-progress">
						<span class="sui-progress-icon" aria-hidden="true">
							<i class="sui-icon-loader sui-loading"></i>
						</span>
						<div class="sui-progress-text">
							<span>0%</span>
						</div>
						<div class="sui-progress-bar" aria-hidden="true">
							<span style="width: 0;"></span>
						</div>
					</div>
				</div>

				<div class="sui-progress-state">
					<span class="sui-progress-state-text"><?php esc_html_e( 'Initializing engines...', 'wphb' ); ?></span>
				</div>
			</div>
			<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
				<img class="sui-image"
					src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup.png' ); ?>"
					srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-modal-quicksetup@2x.png' ); ?> 2x"
					alt="<?php esc_attr_e( 'Reduce your page load time!', 'wphb' ); ?>">
			<?php endif; ?>
		</div>

	</div>

</div>
