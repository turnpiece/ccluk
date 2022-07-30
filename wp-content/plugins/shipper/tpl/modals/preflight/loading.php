<?php
/**
 * Shipper modal templates: site selection, site preparation
 *
 * @since v1.0.3
 * @package shipper
 */

$migration            = new Shipper_Model_Stored_Migration();
$local                = $migration->get_source( true );
$remote               = $migration->get_destination( true );
$shipper_package_link = network_admin_url( 'admin.php?page=shipper-packages' );
?>

<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
	<h3 class="sui-box-title sui-lg">
		<?php esc_html_e( 'Pre-flight Check', 'shipper' ); ?>
	</h3>

	<button class="sui-button-icon sui-button-float--right sui-modal-close">
		<i class="sui-icon-close sui-md" aria-hidden="true"></i>
		<span class="sui-screen-reader-text">
			<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>
		</span>
	</button>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center sui-description">
	<p>
		<?php esc_html_e( 'Hold tight!', 'shipper' ); ?>
		<?php esc_html_e( 'We’re running a pre-flight check for any issues that might prevent a successful migration.', 'shipper' ); ?>
		<?php esc_html_e( 'This will only take a few seconds.', 'shipper' ); ?>
	</p>

	<div class="shipper-progress">

		<div class="sui-progress-block">
			<div class="sui-progress">
				<span class="sui-progress-icon" aria-hidden="true">
					<i class="sui-icon-loader sui-loading"></i>
				</span>

				<span class="sui-progress-text">
					<span>0%</span>
				</span>

				<div class="sui-progress-bar" aria-hidden="true">
					<span style="width: 0%"></span>
				</div>
			</div>

			<button class="sui-button-icon sui-tooltip sui-modal-close" data-tooltip="Cancel">
				<i class="sui-icon-close" aria-hidden="true"></i>
			</button>
		</div>

		<div class="sui-progress-state">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: website title. */
					__( 'Checking %s...', 'shipper' ),
					'<b class="shipper-preflight-target">' . esc_html( $local ) . '</b>'
				)
			);
			?>
		</div>

		<div class="sui-progress-state shipper-progress-steps">
			<div
				class="shipper-progress-step shipper-step-active"
				data-domain="<?php echo esc_attr( $local ); ?>"
				data-step="local"
			>
				<?php echo esc_html( $local ); ?>
				<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
			</div>
			<div
				class="shipper-progress-step"
				data-domain="<?php echo esc_attr( $remote ); ?>"
				data-step="remote">
				<?php echo esc_html( $remote ); ?>
			</div>
			<div
				class="shipper-progress-step"
				data-domain="<?php echo esc_attr( "{$local} vs {$remote}" ); ?>"
				data-step="sysdiff">
				<?php esc_html_e( 'Server Differences', 'shipper' ); ?>
			</div>
		</div>

	</div> <?php // .shipper-progress ?>

	<div class="shipper-progress-stuck hidden">
		<div class="sui-notice sui-notice-error">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
					<p class="sui-description">
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: package migration link */
								__( 'Oh no! Looks like the Pre-flight process is stuck for the API Migration method. Please cancel this process and try the <a href="%s" target="_blank">Package Migration</a> method instead.', 'shipper' ),
								esc_url( $shipper_package_link )
							)
						);
						?>
					</p>

					<div class="shipper-progress-stuck-buttons">
						<button type="button" class="sui-button sui-button-ghost shipper-cancel">
							<?php esc_html_e( 'Cancel', 'shipper' ); ?>
						</button>

						<a class="sui-button shipper-try-package" href="<?php echo esc_url( $shipper_package_link ); ?>">
							<?php esc_html_e( 'Try Package Migration', 'shipper' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div> <?php // .shipper-progress-stuck ?>

</div>