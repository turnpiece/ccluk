<?php
/**
 * Shipper package migration modals: package preflight check template
 *
 * @since v1.1
 * @package shipper
 */

?>
	<p class="shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
		<?php esc_html_e( 'Hold tight! We’re running a pre-flight check for any issues that might prevent a package build. This will only take a few seconds.', 'shipper' ); ?>
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
				<span class="shipper-progress-bar" style="width: 0%"></span>
			</div>

		</div><!-- sui-progress -->
	</div><!-- sui-progress-block -->

	<div class="sui-progress-state">
		<span class="shipper-progress-status"><?php esc_html_e( 'Connecting...', 'shipper' ); ?></span>
	</div>

	<div class="shipper-progress-checks">

		<div class="shipper-progress-check active">
			<div class="shipper-progress-title"
				data-active="<?php esc_attr_e( 'Checking system...', 'shipper' ); ?>">
				<?php esc_html_e( 'System', 'shipper' ); ?>
			</div>
			<div class="shipper-progress-icon">
				<i class="sui-icon-loader sui-loading"></i>
			</div>
		</div><!-- shipper-progress-check -->

		<div class="shipper-progress-check">
			<div class="shipper-progress-title"
				data-active="<?php esc_attr_e( 'Checking files...', 'shipper' ); ?>">
				<?php esc_html_e( 'Files', 'shipper' ); ?>
			</div>
			<div class="shipper-progress-icon">
				<i class="sui-icon-loader sui-loading"></i>
			</div>
		</div><!-- shipper-progress-check -->

		<div class="shipper-progress-check">
			<div class="shipper-progress-title"
				data-active="<?php esc_attr_e( 'Checking database...', 'shipper' ); ?>">
				<?php esc_html_e( 'Database', 'shipper' ); ?>
			</div>
			<div class="shipper-progress-icon">
				<i class="sui-icon-loader sui-loading"></i>
			</div>
		</div><!-- shipper-progress-check -->

	</div><!-- shipper-progress-checks -->
</div>