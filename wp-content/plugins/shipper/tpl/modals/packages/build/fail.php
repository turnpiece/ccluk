<?php
/**
 * Shipper package migration modals: package building failure template
 *
 * @since v1.1
 * @package shipper
 */

?>

	<div
		id="<?php echo esc_attr( $main_id ); ?>"
		class="sui-notice sui-notice-error"
	>
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %1$s %2$s: settings and api url. */
							__( 'There was an error while trying to build your package! For better chances at succeeding, we suggest that you enable the <strong>Safe Mode</strong> on the <a href="%1$s" target="_blank">settings page</a> and try creating the package again. If you are unable to solve the issue, you can also try the <a href="%2$s" target="_blank">API Migration</a>, our alternative migration method.', 'shipper' ),
							esc_url( network_admin_url( 'admin.php?page=shipper-packages&tool=settings' ) ),
							esc_url( network_admin_url( 'admin.php?page=shipper-api' ) )
						)
					);
					?>
				</p>

				<p class="shipper-error-message-wrapper" style="display:none">
					<?php esc_html_e( 'Encountered error:', 'shipper' ); ?>
					<span class="shipper-error-message"></span>
				</p>
			</div>
		</div>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim">
	<div class="shipper-modal-bottom-actions">
		<div class="shipper-modal-bottom-action-left">
			<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-tools' ) ); ?>" type="button" class="sui-button sui-button-ghost shipper-logs">
				<i aria-hidden="true" class="sui-icon-eye"></i>
				<?php esc_html_e( 'View Logs', 'shipper' ); ?>
			</a>
		</div><!-- shipper-modal-bottom-action-left -->
		<div class="shipper-modal-bottom-action-right">
		<button type="button" class="sui-button shipper-restart">
				<i aria-hidden="true" class="sui-icon-update"></i>
				<?php esc_html_e( 'Retry Package', 'shipper' ); ?>
		</div><!-- shipper-modal-bottom-action-right -->
	</div><!-- shipper-modal-bottom-actions -->
</div>