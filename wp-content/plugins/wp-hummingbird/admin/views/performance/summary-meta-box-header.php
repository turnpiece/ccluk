<h3 class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( ! $dismissed ) : ?>
	<div class="ignore-report sui-actions-right">
		<a class="sui-button sui-button-ghost" id="dismiss-report" data-a11y-dialog-show="dismiss-report-modal">
			<?php esc_html_e( 'Ignore Warnings', 'wphb' ); ?>
		</a>
	</div><!-- end test-results -->
<?php endif; ?>