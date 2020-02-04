<?php
/**
 * Shipper templates: view documentation menu action
 *
 * @package shipper
 */

?>
<div class="sui-actions-right">
<?php /*
	<a href="#" target="_blank" class="shipper-add-website sui-button sui-button-primary">
		<i class="sui-icon-plus" aria-hidden="true"></i>
		<?php esc_html_e( 'Add destination', 'shipper' ); ?>
	</a>
 */ ?>
<?php if ( Shipper_Helper_Assets::has_docs_links() ) { ?>
	<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/shipper/" target="_blank" class="sui-button sui-button-ghost">
		<i class="sui-icon-academy" aria-hidden="true"></i>
		<?php esc_html_e( 'View documentation', 'shipper' ); ?>
	</a>
<?php } ?>
</div>