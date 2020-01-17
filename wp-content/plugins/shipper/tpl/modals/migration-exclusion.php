<?php
$modal_id              = "shipper-migration-exclusion";
$modal_class           = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-migration-exclusion' ),
	sanitize_html_class( $modal_id )
);
$arguments['modal_id'] = $modal_id;

//$size_class  = 'sui-dialog-reduced';
//$modal_class .= " {$size_class}";
?>

<div
        class="sui-dialog sui-dialog-alt <?php echo esc_attr( $modal_class ); ?>"
        tabindex="-1" id="<?php echo esc_attr( $modal_id ); ?>" aria-hidden="true">
    <div class="sui-dialog-overlay" data-a11y-dialog-hide="<?php echo esc_attr( $modal_id ); ?>"></div>

    <div class="sui-dialog-content sui-fade-in" role="dialog">
        <div class="sui-box" role="document">
            <div class="sui-box-header sui-block-content-center">
                <h3 class="sui-box-title">
					<?php esc_html_e( 'Migration Filters', 'shipper' ); ?>
                </h3>
                <div class="sui-actions-right">
                    <div class="sui-actions-left">

                    </div>
                    <div class="sui-actions-right">
                        <button data-a11y-dialog-hide="<?php echo esc_attr( $modal_id ); ?>" class="sui-dialog-close"
                                aria-label="<?php echo esc_attr( 'Close this dialog window', 'shipper' ); ?>"></button>
                    </div>
                </div>
            </div>
            <div class="sui-box-body sui-box-body-slim">
	            <div class="shipper-content">
		            <div class="shipper-content-inside">
			            <?php
			            $this->render( 'modals/migration-exclusion/settings', [
				            'main_id' => $modal_id
			            ] );
			            ?>
			            <button class="sui-button shipper-cancel sui-button-ghost pull-left"><?php esc_html_e( 'Cancel', 'shipper' ) ?></button>
			            <button type="button" data-url="<?php echo esc_attr( add_query_arg( 'is_excludes_picked', true ) ) ?>"
			                    class="sui-button shipper-update-exclusion pull-right">
				            <?php esc_html_e( 'Next', 'shipper' ) ?>
			            </button>
			            <div class="clearfix"></div>
		            </div>
		            <?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
	            </div>
            </div>
        </div>
    </div>
</div>