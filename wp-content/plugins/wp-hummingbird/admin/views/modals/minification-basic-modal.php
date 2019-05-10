<?php
/**
 * Asset optimization: switch to basic mode modal.
 *
 * @package Hummingbird
 */

?>

<div class="dialog sui-dialog sui-dialog-sm" aria-hidden="true" id="wphb-basic-minification-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="switchBasic" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="switchBasic">
					<?php esc_html_e( 'Are you sure?', 'wphb' ); ?>
				</h3>
				<button data-a11y-dialog-hide class="sui-dialog-close" aria-label="Close this dialog window"></button>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'Switching back to Basic mode will keep your basic compression settings, but you’ll lose any advanced configuration you have set up.', 'wphb' ); ?></p>

				<div class="sui-block-content-center">
					<button class="close sui-button sui-button-ghost" data-a11y-dialog-hide="wphb-basic-minification-modal"><?php esc_html_e( 'Go back', 'wphb' ); ?></button>
					<a onclick="WPHB_Admin.minification.switchView( 'basic' )" class="sui-button">
						<?php esc_html_e( 'Switch to basic mode', 'wphb' ); ?>
					</a>
				</div>
			</div>

			<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
				<img class="sui-image"
					src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@1x.png' ); ?>"
					srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-minify-modal-warning@2x.png' ); ?> 2x"
					alt="<?php esc_attr_e( 'Hummingbird', 'wphb' ); ?>">
			<?php endif; ?>

		</div>

	</div>

</div>
