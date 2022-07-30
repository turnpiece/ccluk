<?php
/**
 * Shipper modal templates: site selection, site preparation
 *
 * @since v1.0.3
 * @package shipper
 */

?>

	<h3 class="sui-box-title sui-lg">
		<?php esc_html_e( 'Preparing Site', 'shipper' ); ?>
	</h3>

	<div class="sui-button-icon sui-button-float--right shipper-cancel">
		<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper' ) ); ?>" class="shipper-go-back">
			<i class="sui-icon-close sui-md" aria-hidden="true"></i>
			<span class="sui-screen-reader-text">
				<?php esc_attr_e( 'Close the modal', 'shipper' ); ?>
			</span>
		</a>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %1$s: website url. */
				__( 'It appears %1$s isn\'t connected to Shipper (even though it may have been previously).', 'shipper' ),
				'<span class="shipper-site-domain">{{SITE_URL}}</span>'
			)
		);
		?>
		<?php esc_html_e( 'We\'re enabling it for migrations, please wait a few moments.', 'shipper' ); ?>
	</p>

	<div class="shipper-progress">

		<div class="sui-progress-block">
			<div class="sui-progress">
				<span class="sui-progress-icon" aria-hidden="true">
					<i class="sui-icon-loader sui-loading"></i>
				</span>

				<span class="sui-progress-text">
					<span>50%</span>
				</span>

				<div class="sui-progress-bar" aria-hidden="true">
					<span style="width: 50%"></span>
				</div>
			</div>

			<button class="sui-button-icon sui-tooltip" data-tooltip="Cancel">
				<i class="sui-icon-close" aria-hidden="true"></i>
			</button>
		</div>

		<div class="sui-progress-state">
			<?php esc_html_e( 'Preparing destination...', 'shipper' ); ?>
		</div>

		<div class="sui-progress-state shipper-progress-steps">
			<div class="shipper-progress-step shipper-step-active" data-step="install">
				<?php esc_html_e( 'Installing Shipper', 'shipper' ); ?>
				<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
			</div>
			<div class="shipper-progress-step" data-step="activate">
				<?php esc_html_e( 'Activating Shipper', 'shipper' ); ?>
			</div>
			<div class="shipper-progress-step" data-step="add-to-api">
				<?php esc_html_e( 'Adding site to Shipper API', 'shipper' ); ?>
			</div>
		</div>

	</div> <?php // .shipper-progress ?>

</div>