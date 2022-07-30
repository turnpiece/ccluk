<?php
/**
 * Shipper package migration modals: package build migration template
 *
 * @since v1.1
 * @package shipper
 */

?>
	<p class="shipper-description shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
		<?php esc_html_e( 'Great! We\'re building your package. Keep this modal open during the build process. This may take anywhere from a few seconds to a couple of minutes depending upon the size of your site.', 'shipper' ); ?>
	</p>
</div>

<div class="sui-box-body sui-box-body-slim">
	<div class="sui-progress-block">
		<div class="sui-progress">

			<span class="sui-progress-icon" aria-hidden="true">
				<i class="sui-icon-loader sui-loading"></i>
			</span>

			<span class="sui-progress-text">
				<span class="shipper-progress-label">0%</span>
			</span>

			<div class="sui-progress-bar" aria-hidden="true">
				<span class="shipper-progress-bar" style="width: 0"></span>
			</div>

		</div><!-- sui-progress -->
	</div><!-- sui-progress-block -->

	<div class="shipper-package-progress-logs">
		<span class="title"><?php esc_html_e( 'Logs' ); ?></span>

		<div class="sui-notice sui-notice-success">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
					<p><?php esc_html_e( 'Package migration start', 'shipper' ); ?></p>
				</div>

				<span class="sui-progress-icon" aria-hidden="true">
					<span class="sui-icon-loader sui-loading"></span>
				</span>
			</div>
		</div>
	</div>

	<div class="shipper-package-logs-button">
		<a role="button" href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-tools' ) ); ?>" target="_blank" class="sui-button sui-button-ghost">
			<span class="sui-icon-eye" aria-hidden="true"></span>
			<?php esc_html_e( 'View Logs', 'shipper' ); ?>
		</a>
	</div>
</div>