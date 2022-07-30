<?php
/**
 * Shipper modal templates: site selection, Hub error
 *
 * @since v1.0.3
 * @package shipper
 */

?>

	<h3 class="sui-box-title sui-lg">
		<?php esc_html_e( 'Error Fetching Sites', 'shipper' ); ?>
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

	<div class="sui-notice sui-notice-error">
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %s: incsub support url.*/
				__( 'There was an error fetching the list of sites connected to the Hub. Please retry or contact <a href="%s" target="_blank">support</a> if the issue persists.', 'shipper' ),
				'https://wpmudev.com/hub2/support/'
			)
		);
		?>
	</p>
	</div>

	<p><a href="#reload" class="sui-button" onclick="window.location.reload()">
		<i class="sui-icon-update" aria-hidden="true"></i>
		<?php esc_html_e( 'Retry Fetching Sites', 'shipper' ); ?>
	</a></p>
</div>