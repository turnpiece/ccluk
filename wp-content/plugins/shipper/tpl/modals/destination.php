<?php
/**
 * Shipper modal dialogs: Add new destination
 *
 * @package shipper
 */

?>
<div class="sui-dialog shipper-destination-add" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="shipper-destination-state shipper-destination-state-add">
				<?php $this->render( 'modals/destination-add' ); ?>
			</div>
			<div class="shipper-destination-state shipper-destination-state-check" style="display:none">
				<?php $this->render( 'modals/destination-check' ); ?>
			</div>
			<div class="shipper-destination-state shipper-destination-state-connect" style="display:none">
				<?php $this->render( 'modals/destination-connect' ); ?>
			</div>
			<div class="shipper-destination-state shipper-destination-state-refresh" style="display:none">
				<?php $this->render( 'modals/destination-refresh' ); ?>
			</div>
			<div class="shipper-destination-state shipper-destination-state-hub" style="display:none">
				<?php $this->render( 'modals/destination-hub' ); ?>
			</div>
			<div class="shipper-destination-state shipper-destination-state-prepare" style="display:none">
				<?php $this->render( 'modals/destination-prepare' ); ?>
			</div>
			<div class="shipper-destination-state shipper-destination-state-fail" style="display:none">
				<?php $this->render( 'modals/destination-fail' ); ?>
			</div>
		</div>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>

<div class="shipper-destination-added-message">
	<div class="status-success sui-notice sui-notice-success sui-notice-top sui-can-dismiss"
		style="display:none">
		<div class="sui-notice-content">
			<p>
				<?php esc_html_e( 'Site successfully added as destination.', 'shipper'); ?>
				<?php esc_html_e( 'You can now migrate to and from it using Shipper!', 'shipper'); ?>
			</p>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
	<div class="status-refresh sui-notice sui-notice-success sui-notice-top sui-can-dismiss"
		style="display:none">
		<div class="sui-notice-content">
			<p>
				<?php esc_html_e( 'Refresh complete.', 'shipper'); ?>
				<?php esc_html_e( 'You should now see your website in the list below.', 'shipper'); ?>
				<?php esc_html_e( 'Select it and click the button to proceed.', 'shipper'); ?>
			</p>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
	<div class="status-failure sui-notice sui-notice-error sui-notice-top sui-can-dismiss"
		style="display:none">
		<div class="sui-notice-content">
			<p>
				<?php esc_html_e( 'We encountered an error adding site as destination.', 'shipper'); ?>
			</p>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
</div>