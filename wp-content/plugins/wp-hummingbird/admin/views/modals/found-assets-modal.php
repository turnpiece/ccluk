<?php
/**
 * Modal window that is shown right after the asset optimization scan is finished.
 *
 * @since 1.9.2
 * @package Hummingbird
 */

?>
<div class="sui-dialog sui-dialog-sm wphb-assets-modal" aria-hidden="true" id="wphb-assets-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="assetsFound" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="assetsFound">
					<?php
					printf(
						/* translators: %s - number of assets */
						esc_html__( '%s assets found', 'wphb' ),
						0
					);
					?>
				</h3>
			</div>

			<div class="sui-box-body">
				<p>
					<?php
					esc_html_e(
						'Next, optimize your file structure by turning on compression, and moving
				files in order to speed up your page load times.',
						'wphb'
					);
					?>
				</p>

				<div class="sui-notice sui-notice-warning">
					<p>
						<?php
						esc_html_e(
							'This is an advanced feature and can break themes easily. We
						recommend modifying each file individually and checking your frontend regularly
						for issues.',
							'wphb'
						);
						?>
					</p>
				</div>

				<div class="sui-block-content-center">
					<a class="sui-button" onclick="WPHB_Admin.minification.goToSettings()">
						<?php esc_html_e( 'Got It', 'wphb' ); ?>
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
