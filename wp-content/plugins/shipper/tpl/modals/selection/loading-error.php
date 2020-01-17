<?php
/**
 * Shipper modal templates: site selection, Hub error
 *
 * @since v1.0.3
 * @package shipper
 */

?>

<div class="sui-box-header sui-block-content-center">
	<h3 class="sui-box-title">
		<?php esc_html_e( 'Error Fetching Sites', 'shipper' ); ?>
	</h3>
	<div class="sui-actions-right">
		<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper' ) ); ?>"
			class="shipper-go-back">
			<i class="sui-icon-close" aria-hidden="true"></i>
			<span><?php esc_html_e( 'Cancel', 'shipper' ); ?></span>
		</a>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">

	<div class="sui-notice sui-notice-error">
	<p><?php echo wp_kses_post( sprintf(
		__( 'There was an error fetching the list of sites connected to the Hub. Please retry or contact <a href="%s" target="_blank">support</a> if the issue persists.', 'shipper' ),
		'https://premium.wpmudev.org/hub/support/'
	) ); ?></p>
	</div>

	<p><a href="#reload" class="sui-button" onclick="window.location.reload()">
		<i class="sui-icon-update" aria-hidden="true"></i>
		<?php esc_html_e( 'Retry Fetching Sites', 'shipper' ); ?>
	</a></p>
</div>