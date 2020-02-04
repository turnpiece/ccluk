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
$header_class    = "";
$title           = 'fail' !== $modal
	? __( 'Building Package', 'shipper' )
	: __( 'Package Failed', 'shipper' );
if ( 'cancel' === $modal ) {
	$title        = __( 'Cancel Package', 'shipper' );
	$header_class = "sui-dialog-sm";
}
?>
<div class="sui-dialog sui-dialog-alt <?php echo $header_class ?> <?php echo esc_attr( "{$main_id}-{$modal}" ); ?>"
     aria-hidden="true" tabindex="-1" id="<?php echo esc_attr( $main_id ); ?>">

    <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

    <div class="sui-dialog-content" aria-labelledby="<?php echo esc_attr( $main_id ); ?>-title"
         aria-describedby="<?php echo esc_attr( $main_id ); ?>-description" role="dialog">
        <div class="sui-box" role="document">

            <div class="sui-box-header sui-block-content-center">
				<?php if ( 'fail' === $modal ) { ?>
                    <div class="shipper-package-fail">
                        <i class="sui-icon-warning-alert" aria-hidden="true"></i>
                    </div>
				<?php } ?>
                <h3 class="sui-box-title" id="<?php echo esc_attr( $main_id ); ?>-title">
					<?php echo esc_html( $title ); ?>
                </h3>
                <div class="sui-actions-right">
                    <button data-a11y-dialog-hide="<?php echo esc_attr( $main_id ); ?>" class="sui-dialog-close"
                            aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
                </div><!-- .sui-actions-right -->
            </div><!-- .sui-box-header -->

            <div class="sui-box-body sui-box-body-slim">
				<?php $this->render( 'modals/packages/build/' . $modal, $args ); ?>
            </div> <!-- .sui-box-body -->
	        <?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
        </div><!-- .sui-box-->
    </div><!-- .sui-dialog-content -->
</div>