<div class="dialog sui-dialog sui-dialog-sm" aria-hidden="true" id="dismiss-report-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="dismissReport" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="dismissReport">
					<?php esc_html_e( 'Are you sure?', 'wphb' ); ?>
				</h3>
				<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'wphb' ); ?>"></button>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'Are you sure you wish to ignore the current performance test results? You can re-run the test anytime to check your performance score again.', 'wphb' ); ?></p>
			</div>
			<div class="sui-box-footer">
				<form method="post">
					<a class="sui-button sui-button-ghost" data-a11y-dialog-hide><?php esc_html_e( 'Cancel', 'wphb' ); ?></a>
					<button type="submit" name="dismiss_report" class="sui-button sui-button-primary"><?php esc_html_e( 'Confirm', 'wphb' ); ?></button>
					<?php wp_nonce_field( 'wphb-dismiss-performance-report' ); ?>
				</form>
			</div>

		</div>

	</div>

</div>