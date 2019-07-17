<?php
/**
 * Shipper modal templates: site selection, site preparation
 *
 * @since v1.0.3
 * @package shipper
 */

$migration = new Shipper_Model_Stored_Migration;
$local = $migration->get_source();
$remote = $migration->get_destination();
?>

<div class="sui-box-header sui-block-content-center">
	<h3 class="sui-box-title">
		<?php esc_html_e( 'Pre-flight Check', 'shipper' ); ?>
	</h3>
	<div class="sui-actions-right">
		<button data-a11y-dialog-hide="<?php echo esc_attr( $modal_id ); ?>" class="sui-dialog-close" aria-label="<?php echo esc_attr( 'Close this dialog window', 'shipper' ); ?>"></button>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
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

			<button class="sui-button-icon sui-tooltip" data-tooltip="Cancel">
				<i class="sui-icon-close" aria-hidden="true"></i>
			</button>
		</div>

		<div class="sui-progress-state">
			<?php echo wp_kses_post( sprintf(
				__( 'Checking %s...', 'shipper' ),
				'<b class="shipper-preflight-target">' . esc_html( $local ) . '</b>'
			) ); ?>
		</div>

		<div class="sui-progress-state shipper-progress-steps">
			<div class="shipper-progress-step shipper-step-active"
				data-domain="<?php echo esc_attr( $local ); ?>"
				data-step="local">
				<?php echo esc_html( $local ); ?>
				<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
			</div>
			<div class="shipper-progress-step"
				data-domain="<?php echo esc_attr( $remote ); ?>"
				data-step="remote">
				<?php echo esc_html( $remote ); ?>
			</div>
			<div class="shipper-progress-step"
				data-domain="<?php echo esc_attr( "{$local} vs {$remote}" ); ?>"
				data-step="sysdiff">
				<?php esc_html_e( 'Server Differences', 'shipper' ); ?>
			</div>
		</div>

	</div> <?php // .shipper-progress ?>

</div>
