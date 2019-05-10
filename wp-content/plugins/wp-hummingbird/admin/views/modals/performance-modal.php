<?php
/**
 * One-off performance reports modal.
 *
 * @since 2.0.0
 * @package Hummingbird
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<script type="text/template" id="wphb-performance">
	<div class="sui-box-header sui-dialog-with-image">
		<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
			<div class="sui-dialog-image" aria-hidden="true">
				<img src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/preformance-report-modal.png' ); ?>"
					srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/preformance-report-modal@2x.png' ); ?> 2x"
					alt="<?php esc_attr_e( 'Performance Report Modal', 'wphb' ); ?>" class="sui-image sui-image-center">
			</div>
		<?php endif; ?>

		<h3 class="sui-box-title <?php echo WP_Hummingbird_Utils::hide_wpmudev_branding() ? 'sui-padding-top' : ''; ?>" id="dialogTitle">
			<?php esc_html_e( 'Performance Report', 'wphb' ); ?>
		</h3>

		<div class="sui-actions-right">
			<button class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close', 'wphb' ); ?>" onclick="SUI.dialogs['wphb-performance-dialog'].hide()"></button>
		</div>
	</div>

	<form>
		<div class="sui-box-body">
			<p>
				<?php esc_html_e( 'Run a one-off performance test on this page and get the performance report in your email.', 'wphb' ); ?>
			</p>

			<# if ( ! data.scanning ) { #>
			<div class="sui-form-field">
				<label class="sui-label" for="name"><?php esc_html_e( 'First name', 'wphb' ); ?></label>
				<input class="sui-form-control" id="name" placeholder="<?php esc_attr_e( 'E.g. John', 'wphb' ); ?>" required>
			</div>

			<div class="sui-form-field">
				<label class="sui-label" for="email"><?php esc_html_e( 'Email address', 'wphb' ); ?></label>
				<input class="sui-form-control" id="email" placeholder="<?php esc_attr_e( 'E.g. john@doe.com', 'wphb' ); ?>" required>
			</div>
			<# } else if ( ! data.finished ) { #>
			<div class="sui-progress-block">
				<div class="sui-progress">
					<span class="sui-progress-icon" aria-hidden="true">
						<i class="sui-icon-loader sui-loading"></i>
					</span>
					<div class="sui-progress-text">
						<span>0%</span>
					</div>
					<div class="sui-progress-bar" aria-hidden="true">
						<span style="width: 0;"></span>
					</div>
				</div>

				<button class="sui-button-icon sui-tooltip" data-tooltip="<?php esc_attr_e( 'Cancel', 'wphb' ); ?>">
					<i class="sui-icon-close" aria-hidden="true"></i>
				</button>
			</div>

			<div class="sui-progress-state">
				<span class="sui-progress-state-text"><?php esc_html_e( 'Initializing engines...', 'wphb' ); ?></span>
			</div>
			<# } else { #>
			<p>
				<?php
				esc_html_e(
					"Performance test completed successfully on this page. We've emailed you the performance
					test report at",
					'wphb'
				);
				?>
				<strong>{{{ data.email }}}</strong>.
			</p>

			<button class="sui-button" onclick="SUI.dialogs['wphb-performance-dialog'].hide()">
				<?php esc_html_e( 'Done', 'wphb' ); ?>
			</button>
			<# } #>
		</div>

		<# if ( ! data.scanning && ! data.finished ) { #>
		<div class="sui-box-footer sui-space-between">
			<button type="button" class="sui-button sui-button-ghost" onclick="SUI.dialogs['wphb-performance-dialog'].hide()">
				<?php esc_html_e( 'Cancel', 'wphb' ); ?>
			</button>
			<button type="submit" class="sui-button sui-button-blue">
				<?php esc_html_e( 'Run test', 'wphb' ); ?>
			</button>
		</div>
		<# } #>
	</form>
</script>

<div class="sui-dialog sui-dialog-sm wphb-performance-dialog" aria-hidden="true" tabindex="-1" id="wphb-performance-dialog">
	<div class="sui-dialog-overlay sui-fade-in"></div>
	<div class="sui-dialog-content sui-bounce-in" aria-labelledby="dialogTitle" aria-describedby="dialogDescription" role="dialog">
		<div class="sui-box" role="document">
			<div id="wphb-performance-content"></div>
			<?php wp_nonce_field( 'wphb-fetch' ); ?>
		</div>
	</div>
</div>
