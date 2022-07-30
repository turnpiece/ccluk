<?php
/**
 * Shipper templates: view documentation menu action
 *
 * @package shipper
 */

?>
<div class="sui-actions-right">

<?php if ( Shipper_Helper_Assets::has_docs_links() ) { ?>
	<a href="https://wpmudev.com/docs/wpmu-dev-plugins/shipper/?utm_source=shipper&utm_medium=plugin&utm_campaign=shipper_api_docs#api-migration" target="_blank" class="sui-button sui-button-ghost">
		<i class="sui-icon-academy" aria-hidden="true"></i>
		<?php esc_html_e( 'View documentation', 'shipper' ); ?>
	</a>
<?php } ?>
</div>