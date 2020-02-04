<?php
/**
 * Shipper modal templates: destination connection check partial
 *
 * @package shipper
 */

?>

<div class="sui-box-header">
	<a href="#back" class="shipper-dialog-back">
		<i class="sui-icon-chevron-left" aria-hidden="true"></i>
		<span><?php esc_attr_e( 'Go back', 'shipper' ); ?></span>
	</a>
	<h3 class="sui-dialog-title">
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		<?php esc_html_e( 'Checking...', 'shipper' ); ?>
	</h3>
	<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
</div>
<div class="sui-box-body">
	<p><?php esc_html_e( 'Looking for your new website, please wait...', 'shipper' ); ?></p>
</div>