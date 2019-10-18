<?php

?>
<div id="wps-upload-success" class="hidden">
	<div class="wps-auth-message success">
		<p>
			<?php echo esc_html__( 'Your backup has been successfully uploaded to the Hub.', SNAPSHOT_I18N_DOMAIN ); ?>
		</p>
	</div>

	<div class="wps-button-container">
		<a href="<?php echo esc_url( $managed_backups_url ); ?>"
		   class="button button-gray button-outline">

			<?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?>
		</a>

		<a href="<?php echo esc_url( $restore_link ); ?>"
		   class="button button-gray">

			<?php esc_html_e( 'Restore', SNAPSHOT_I18N_DOMAIN ); ?>
		</a>
	</div>
</div>