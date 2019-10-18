<div id="wps-upload-progress">

	<p><?php echo wp_kses_post( __( 'Your backup is being uploaded. <strong>You need to keep this page open for the backup to complete.</strong> If your site is small, this will only take a few minutes, but could take a couple of hours for larger sites', SNAPSHOT_I18N_DOMAIN ) ); ?></p>

	<div class="wpmud-box-gray">
		<div class="wps-loading-status wps-total-status wps-spinner">
			<p class="wps-loading-number">0%</p>
			<div class="wps-loading-bar">
				<div class="wps-loader">
					<span style="width: 0%"></span>
				</div>
			</div>
		</div>
	</div>

	<p><a id="wps-cancel"
	      class="button button-outline button-gray">
			<?php esc_html_e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?></a>
	</p>

</div>