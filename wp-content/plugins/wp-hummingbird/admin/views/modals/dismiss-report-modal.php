<dialog class="wphb-modal dismiss-report no-close box-content" id="dismiss-report-modal" title="<?php esc_attr_e( 'Ignore Warnings', 'wphb' ); ?>">
	<div class="wphb-dialog-content">
		<p><?php esc_html_e( 'Are you sure you wish to ignore the current performance test results? You can re-run the test anytime to check your performance score again.', 'wphb' ); ?></p>

	</div><!-- end wphb-dialog-content -->

	<div class="buttons buttons-on-right">
		<form method="post">
			<div class="close button button-ghost"><?php esc_html_e( 'Cancel', 'wphb' ); ?></div>
			<button type="submit" name="dismiss_report" class="button wph-button"><?php esc_html_e( 'Confirm', 'wphb' ); ?></button>
			<?php wp_nonce_field( 'wphb-dismiss-performance-report' ); ?>
		</form>
	</div>
</dialog><!-- end check-files-modal -->