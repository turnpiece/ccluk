<?php
/**
 * Shipper modal dialogs: Add new destination
 *
 * @package shipper
 */

$destinations = new Shipper_Model_Stored_Destinations();

// Only show 'add destination' initial state if we don't have other sites around.
$initial_state = count( $destinations->get_data() ) > 1
	? 'connect'
	: 'add';
?>

<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="shipper-site_selection-destination-tmp"
		class="sui-modal-content sui-content-fade-in shipper-site_selection shipper-site_selection-destination"
		aria-modal="true"
		aria-labelledby="shipper-site_selection-destination-title"
		aria-describedby="shipper-site_selection-destination-description"
	>
		<div class="sui-box" role="document">

			<div class="shipper-destination-state shipper-destination-state-add"
				style="display:none">
				<?php $this->render( 'modals/destination-add' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

			<div class="shipper-destination-state shipper-destination-state-check"
				style="display:none">
				<?php $this->render( 'modals/destination-check' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

			<div class="shipper-destination-state shipper-destination-state-connect"
				style="display:none">
				<?php $this->render( 'modals/destination-connect' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

			<div class="shipper-destination-state shipper-destination-state-refresh"
				style="display:none">
				<?php $this->render( 'modals/destination-refresh' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

			<div class="shipper-destination-state shipper-destination-state-hub"
				style="display:none">
				<?php $this->render( 'modals/destination-hub' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

			<div class="shipper-destination-state shipper-destination-state-prepare"
				style="display:none">
				<?php $this->render( 'modals/destination-prepare' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

			<div class="shipper-destination-state shipper-destination-state-fail"
				style="display:none">
				<?php $this->render( 'modals/destination-fail' ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>

		</div>

	</div><?php // .sui-modal-content ?>
</div><?php // .sui-modal ?>

<div class="shipper-destination-added-message">
	<div class="sui-notice status-success sui-notice sui-notice-success sui-notice-top sui-can-dismiss"
		style="display:none">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php esc_html_e( 'Site successfully added as destination.', 'shipper' ); ?>
					<?php esc_html_e( 'You can now migrate to and from it using Shipper!', 'shipper' ); ?>
				</p>
			</div>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
	<div class="status-refresh sui-notice sui-notice-success sui-notice-top sui-can-dismiss"
		style="display:none">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php esc_html_e( 'Refresh complete.', 'shipper' ); ?>
					<?php esc_html_e( 'You should now see your website in the list below.', 'shipper' ); ?>
					<?php esc_html_e( 'Select it and click the button to proceed.', 'shipper' ); ?>
				</p>
			</div>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
	<div class="status-failure sui-notice sui-notice-error sui-notice-top sui-can-dismiss"
		style="display:none">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php esc_html_e( 'We encountered an error adding site as destination.', 'shipper' ); ?>
				</p>
			</div>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
		</span>
	</div>
</div>