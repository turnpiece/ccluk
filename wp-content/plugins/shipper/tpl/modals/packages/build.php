<?php
/**
 * Shipper package migration modals: main package building template
 *
 * @since v1.1
 * @package shipper
 */

$main_id         = 'shipper-package-build';
$modal           = ! empty( $modal ) ? $modal : '';
$args            = ! empty( $arguments ) && is_array( $arguments ) ? $arguments : array();
$args['main_id'] = $main_id;
$header_class    = '';
$title           = 'fail' !== $modal // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
	? __( 'Building Package', 'shipper' )
	: __( 'Package Failed', 'shipper' );

if ( 'cancel' === $modal ) {
	$title        = __( 'Cancel Package', 'shipper' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
	$header_class = 'sui-modal-sm';
}
?>

<div class="sui-modal sui-modal-lg <?php echo esc_attr( $header_class ); ?>" id="<?php echo esc_attr( $main_id ); ?>">
	<div
		role="dialog"
		id="<?php echo esc_attr( "{$main_id}-{$modal}" ); ?>"
		class="sui-modal-content sui-content-fade-in <?php echo esc_attr( "{$main_id}-{$modal}" ); ?>"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $main_id ); ?>-title"
		aria-describedby="<?php echo esc_attr( $main_id ); ?>-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<?php if ( 'fail' === $modal ) : ?>
					<div class="shipper-package-fail">
						<i class="sui-icon-warning-alert" aria-hidden="true"></i>
					</div>
				<?php endif; ?>

				<button class="sui-button-icon sui-button-float--right shipper-cancel">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'shipper' ); ?></span>
				</button>

				<h3 id="<?php echo esc_attr( $main_id ); ?>-title" class="sui-box-title sui-lg">
					<?php echo esc_html( $title ); ?>
				</h3>

				<?php $this->render( 'modals/packages/build/' . $modal, $args ); ?>
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div>
		</div>
	</div>