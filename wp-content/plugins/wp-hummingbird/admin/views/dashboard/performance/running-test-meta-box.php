<?php
/**
 * Performance running test on dashboard page.
 *
 * @package Hummingbird
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-content">

		<p><?php esc_html_e( 'Hummingbird is running a test to measure your website performance, please wait.', 'wphb' ); ?></p>

		<div class="wphb-block-test wphb-block-test-standalone">
			<div class="wphb-scan-progress">
				<div class="wphb-scan-progress-text">
					<span>0%</span>
				</div><!-- end wphb-scan-progress-text -->
				<div class="wphb-scan-progress-bar">
					<span style="width: 0"></span>
				</div><!-- end wphb-scan-progress-bar -->
			</div><!-- end wphb-scan-progress -->
		</div><!-- end wphb-block-test -->

		<div class="wphb-progress-state">
			<span class="wphb-progress-state-text">
				<?php esc_html_e( 'Analyzing your site...', 'wphb' ); ?>
			</span>
		</div><!-- end wphb-progress-state -->

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->