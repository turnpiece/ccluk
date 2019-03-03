<?php
/**
 * Shipper templates: view documentation menu action
 *
 * @package shipper
 */

?>
<div class="sui-actions-right">
	<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/shipper/" target="_blank" class="sui-button sui-button-ghost">
		<i class="sui-icon-academy" aria-hidden="true"></i>
		<?php esc_html_e( 'View documentation', 'shipper' ); ?>
	</a>
</div>
<?php $this->render( 'modals/destination' ); ?>