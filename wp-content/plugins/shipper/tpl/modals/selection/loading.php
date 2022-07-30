<?php
/**
 * Shipper modal templates: site selection, loading template
 *
 * @since v1.0.3
 * @package shipper
 */

?>

	<h3 class="sui-box-title sui-lg">
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		<?php esc_html_e( 'Checking sites…', 'shipper' ); ?>
	</h3>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
	<input
		type="hidden"
		name="_wpnonce"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper_list_hub_sites' ) ); ?>"
	/>
	<p><?php esc_html_e( 'We\'re quickly fetching the up to date list of websites connected to the Hub.', 'shipper' ); ?></p>
</div>