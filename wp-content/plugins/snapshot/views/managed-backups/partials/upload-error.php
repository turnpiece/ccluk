<div id="wps-upload-error" class="hidden">
	<div class="wps-auth-message error">
		<p></p>
	</div>

	<div class="wps-button-container">
		<a href="<?php echo esc_url( $managed_backups_url ); ?>"
		   class="button button-gray button-outline">

			<?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?>
		</a>

		<a href="#retry-upload-after-error"
		   class="button button-gray">

			<?php esc_html_e( 'Retry', SNAPSHOT_I18N_DOMAIN ); ?>
		</a>
	</div>
</div>