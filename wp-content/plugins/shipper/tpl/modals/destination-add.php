<?php
/**
 * Shipper modal templates: destination add partial
 *
 * @package shipper
 */

?>

<div class="sui-box-header">
	<h3 class="sui-dialog-title"><?php esc_html_e( 'Add Destination', 'shipper' ); ?></h3>
	<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
</div>
<div class="sui-box-body">

	<p>
		<?php esc_html_e( 'Get started by connecting a second website to the Hub.', 'shipper' ); ?>
	</p>

	<a href="#connect" class="sui-button shipper-connect">
		<?php esc_html_e( 'Connect a website', 'shipper' ); ?>
	</a>

	<p class="shipper-note">
		<?php esc_html_e( 'Done this step?', 'shipper' ); ?>
		<a href="#recheck" class="shipper-connection-check"><?php esc_html_e( 'Check Connection', 'shipper' ); ?></a>
	</p>
</div>