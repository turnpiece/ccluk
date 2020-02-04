<?php
/**
 * Shipper package migration modals: package build migration template
 *
 * @since v1.1
 * @package shipper
 */

?>
<p class="shipper-description" id="<?php echo esc_attr( $main_id ); ?>-description">
	<?php esc_html_e( 'Great! We\'re building your package. Keep this modal open during the build process. This may take anywhere from a few seconds to a couple of minutes depending upon the size of your site.', 'shipper' ); ?>
</p>

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

<div class="sui-progress-state">
	<span class="shipper-progress-status">
		<?php esc_html_e( 'Building Package Archive...', 'shipper' ); ?>
	</span>
</div>

<div class="shipper-progress-checks">

	<div class="shipper-progress-check active">
		<div class="shipper-progress-title">
			<?php esc_html_e( 'Package Archive', 'shipper' ); ?>
		</div>
		<div class="shipper-progress-icon">
			<i class="sui-icon-loader sui-loading"></i>
		</div>
	</div><!-- shipper-progress-check -->

	<div class="shipper-progress-check">
		<div class="shipper-progress-title">
			<?php esc_html_e( 'Installer', 'shipper' ); ?>
		</div>
		<div class="shipper-progress-icon">
			<i class="sui-icon-loader sui-loading"></i>
		</div>
	</div><!-- shipper-progress-check -->

</div><!-- shipper-progress-checks -->