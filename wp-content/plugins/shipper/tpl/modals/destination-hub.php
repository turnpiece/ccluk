<?php
/**
 * Shipper modal templates: destination add new site to hub partial
 *
 * @package shipper
 */

?>

<div class="sui-box-header">
	<a href="#back" class="shipper-dialog-back">
		<i class="sui-icon-chevron-left" aria-hidden="true"></i>
		<span><?php esc_attr_e( 'Go back', 'shipper' ); ?></span>
	</a>
	<h3 class="sui-dialog-title"><?php esc_html_e( 'New Destination', 'shipper' ); ?></h3>
	<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
</div>
<div class="sui-box-body">

	<p>
		<?php esc_html_e( 'To set up a new destination, first you\'ll need to connect it to the Hub.', 'shipper' ); ?>
		<?php esc_html_e( 'Let\'s jump over to the Hub and add a new website.', 'shipper' ); ?>
	</p>

	<a target="_blank" href="https://wpmudev.com/hub2/connect/choose-method/" class="sui-button sui-button-primary shipper-connect">
		<i class="sui-icon-hub" aria-hidden="true"></i>
		<?php esc_html_e( 'Go to the Hub', 'shipper' ); ?>
	</a>

	<p class="shipper-note">
		<?php esc_html_e( 'Prefer to install Shipper manually?', 'shipper' ); ?>
		<a href="https://wpmudev.com/project/shipper-pro/"><?php esc_html_e( 'Download Shipper', 'shipper' ); ?></a>
	</p>
</div>