<div id="wps-upload-abort" class="hidden">

	<p><?php echo wp_kses_post( __( 'Your backup is being uploaded. <strong>You need to keep this page open for the backup to complete.</strong> If your site is small, this will only take a few minutes, but could take a couple of hours for larger sites', SNAPSHOT_I18N_DOMAIN ) ); ?></p>

	<div class="wpmud-box-gray">
		<div class="wps-loading-status wps-total-status wps-spinner wps-error">
			<p class="wps-loading-number">0%</p>
			<div class="wps-loading-bar">
				<div class="wps-loader">
					<span style="width: 0%"></span>
				</div>
			</div>
		</div>
	</div>

	<div class="wps-auth-message warning">
		<p><?php esc_html_e( 'Please don\'t close this page while we remove your partially uploaded backup from the Hub. This should only take a few moments.', SNAPSHOT_I18N_DOMAIN ); ?></p>
	</div>

	<p>
		<button class="disabled button button-outline button-gray">
			<?php esc_html_e( 'Cancel In Progress …', SNAPSHOT_I18N_DOMAIN ); ?>
		</button>
	</p>

</div>