<?php
/**
 * Shipper modal migration exclusion.
 *
 * @package shipper
 */

$modal_id              = 'shipper-migration-exclusion';
$modal_class           = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-migration-exclusion' ),
	sanitize_html_class( $modal_id )
);
$arguments['modal_id'] = $modal_id;

$input_get_type  = filter_input( INPUT_GET, 'type' );
$back_button_url = network_admin_url( 'admin.php?page=shipper-api&type=' . $input_get_type );
?>

<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="sui-modal-content sui-fade-in <?php echo esc_attr( $modal_class ); ?>"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $modal_id . '-title' ); ?>"
		aria-describedby="<?php echo esc_attr( $modal_id . '-description' ); ?>"
	>
		<div class="sui-box" role="document">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<a class="sui-button-icon sui-button-float--left" href="<?php echo esc_url( $back_button_url ); ?>">
					<span class="sui-icon-arrow-left" aria-hidden="true"></span>
					<span class="sui-screen-reader-text">Go back to previous modal</span>
				</a>

				<h3 class="sui-box-title sui-lg">
					<?php esc_html_e( 'Migration Filters', 'shipper' ); ?>
				</h3>

				<button class="sui-button-icon sui-button-float--right shipper-cancel" data-modal-close="">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this modal window', 'shipper' ); ?></span>
				</button>
			</div>
			<div class="sui-box-body sui-box-body-slim">
				<div class="shipper-content">
					<div class="shipper-content-inside">
						<?php
						$this->render(
							'modals/migration-exclusion/settings',
							array(
								'main_id' => $modal_id,
							)
						);
						?>
						<a class="sui-button sui-button-ghost pull-left" href="<?php echo esc_url( $back_button_url ); ?>">
							<span class="sui-icon-arrow-left" aria-hidden="true"></span>
							<?php esc_html_e( 'Back', 'shipper' ); ?>
						</a>
						<button type="button" data-url="<?php echo esc_attr( add_query_arg( 'is_excludes_picked', true ) ); ?>"
								class="sui-button shipper-update-exclusion pull-right">
							<?php esc_html_e( 'Next', 'shipper' ); ?>
						</button>
						<div class="clearfix"></div>
					</div>
					<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
