<h3><?php echo esc_html( $title ); ?></h3>

<?php if ( ! $dismissed ) : ?>
	<div class="ignore-report buttons">
		<a href="#dismiss-report-modal" class="button button-ghost" id="dismiss-report" rel="dialog">
			<?php esc_html_e( 'Ignore Warnings', 'wphb' ); ?>
		</a>
	</div><!-- end test-results -->
<?php endif; ?>