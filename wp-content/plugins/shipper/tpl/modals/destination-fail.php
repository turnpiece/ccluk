<?php
/**
 * Shipper modal templates: destination connection check failed partial
 *
 * @package shipper
 */

?>

<div class="sui-box-header">
	<a href="#back" class="shipper-dialog-back">
		<i class="sui-icon-chevron-left" aria-hidden="true"></i>
		<span><?php esc_attr_e( 'Go back', 'shipper' ); ?></span>
	</a>
	<h3 class="sui-dialog-title"><?php esc_html_e( 'Add destination', 'shipper' ); ?></h3>
	<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
</div>
<div class="sui-box-body">
	<div class="sui-notice sui-notice-warning">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php esc_html_e( 'Shipper couldn\'t find a new website.', 'shipper' ); ?>
					<?php esc_html_e( 'Double check you\'ve connected your website to the Hub and installed Shipper.', 'shipper' ); ?>
				</p>
			</div>
		</div>
	</div>
	<p>
		<a href="#recheck"
			class="sui-button sui-button-ghost shipper-connection-check">
			<?php esc_html_e( 'Re-check', 'shipper' ); ?>
		</a>
		<a href="#connect"
			class="sui-button shipper-connect">
			<?php esc_html_e( 'Connect new website', 'shipper' ); ?>
		</a>
	</p>
</div>