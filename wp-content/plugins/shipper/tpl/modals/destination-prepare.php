<?php
/**
 * Shipper modal templates: Hub-connected site migrate preparation partial
 *
 * @package shipper
 */

?>

<div class="sui-box-header">
	<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'shipper_prepare_hub_site' ) ); ?>" />
	<a href="#back" class="shipper-dialog-back">
		<i class="sui-icon-chevron-left" aria-hidden="true"></i>
		<span><?php esc_attr_e( 'Go back', 'shipper' ); ?></span>
	</a>
	<h3 class="sui-dialog-title">
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		<?php esc_html_e( 'Setting up destination...', 'shipper' ); ?>
	</h3>
	<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
</div>
<div class="sui-box-body">
	<p><?php esc_html_e( 'We are installing Shipper on your website, please wait...', 'shipper' ); ?></p>
</div>