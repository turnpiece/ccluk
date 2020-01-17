<?php
/**
 * Shipper package migration templates: existing migration, additional info template
 *
 * @since v1.1
 * @package shipper
 */

$model = new Shipper_Model_Stored_Package;
?>

<div class="sui-accordion shipper-additional-info">

    <div class="sui-accordion-item">

        <div class="sui-accordion-item-header">
            <div><b><?php esc_html_e( 'Additional Details', 'shipper' ); ?></b></div>
            <div>
				<?php esc_html_e( 'This section contains complete details about the package', 'shipper' ); ?>
                <button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><i
                            class="sui-icon-chevron-down" aria-hidden="true"></i></button>
            </div>
        </div><!-- sui-accordion-item-header -->

        <div class="sui-accordion-item-body">

            <div class="sui-box">
                <div class="sui-box-body">

                    <div class="shipper-info-group">

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'Package Name', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php
								echo esc_html(
									$model->get( Shipper_Model_Stored_Package::KEY_NAME )
								);
								?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'Created on', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php
								$date = $model->get(
									Shipper_Model_Stored_Package::KEY_DATE
								);
								echo esc_html( date( 'Y-m-d H:i:s', $date ) );
								$created = $model->get(
									Shipper_Model_Stored_Package::KEY_CREATED
								);
								if ( ! empty( $created ) ) {
									echo '(' .
									     human_time_diff( $created, $date ) .
									     ')';
								}
								?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'Size', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php echo size_format( $model->get_size() ); ?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                    </div> <!-- shipper-info-group -->

                    <div class="shipper-info-group">

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'File Exclusions', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php
								$items = $model->get(
									Shipper_Model_Stored_Package::KEY_EXCLUSIONS_FS,
									array()
								);
								echo join(
									'<br />',
									array_map( 'sanitize_text_field', $items )
								);
								?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'Database Exclusions', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php
								$items = $model->get(
									Shipper_Model_Stored_Package::KEY_EXCLUSIONS_DB,
									array()
								);
								echo join(
									'<br />',
									array_map( 'sanitize_text_field', $items )
								);
								?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'Advanced Exclusions', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php
								$items = $model->get(
									Shipper_Model_Stored_Package::KEY_EXCLUSIONS_XX,
									array()
								);
								echo join(
									'<br />',
									array_map( 'sanitize_text_field', $items )
								);
								?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                    </div> <!-- shipper-info-group -->

                    <div class="shipper-info-group">

                        <div class="shipper-info-item">
                            <div class="shipper-info-item-title">
								<?php esc_html_e( 'Password Protection', 'shipper' ); ?>
                            </div>
                            <div class="shipper-info-item-body">
								<?php
								echo esc_html(
									$model->get( Shipper_Model_Stored_Package::KEY_PWD )
								);
								?>
                            </div><!-- shipper-info-item-body -->
                        </div><!-- shipper-info-item -->

                    </div> <!-- shipper-info-group -->

                </div><!-- sui-box-body -->

                <div class="sui-box-footer">
                    <div class="sui-actions-left">
                        <button type="button" class="sui-button sui-button-red"
                                data-a11y-dialog-show="shipper-delete-modal">
                            <i class="sui-icon-trash" aria-hidden="true"></i>
							<?php esc_html_e( 'Delete', 'shipper' ); ?>
                        </button>
                    </div>
                    <div class="sui-actions-right">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=shipper-tools' ) ); ?>" type="button"
                           class="sui-button shipper-logs">
                            <i class="sui-icon-eye" aria-hidden="true"></i>
							<?php esc_html_e( 'View Logs', 'shipper' ); ?>
                        </a>
                    </div>
                </div><!-- sui-box-footer -->

            </div><!-- sui-box -->

        </div><!-- sui-accordion-item-body -->

    </div><!-- sui-accordion-item -->

</div><!-- sui-accordion -->
<div class="sui-dialog sui-dialog-alt sui-dialog-sm" aria-hidden="true" tabindex="-1" id="shipper-delete-modal">
    <div class="sui-dialog-overlay" data-a11y-dialog-hide="shipper-delete-modal"></div>
    <div class="sui-dialog-content" aria-labelledby="Shipper delete package modal"
         aria-describedby="Delete current package" role="dialog">
        <div class="sui-box" role="document">
            <div class="sui-box-header sui-block-content-center">
                <h3 class="sui-box-title"><?php esc_attr_e( "Delete Package", "shipper" ) ?></h3>

                <div class="sui-actions-right">
                    <button data-a11y-dialog-hide="shipper-delete-modal" class="sui-dialog-close"
                            aria-label="Close this dialog window"></button>
                </div>

            </div>
            <div class="sui-box-body sui-box-body-slim sui-block-content-center">
                <p class="sui-description"><?php esc_html_e( "Are you sure you wish to permanently delete this package?", "shipper" ) ?></p>
            </div>

            <div class="sui-box-footer sui-box-footer-center">
                <button class="sui-button sui-button-ghost" data-a11y-dialog-hide="shipper-delete-modal">
					<?php esc_html_e( "Cancel", "shipper" ) ?>
                </button>

                <button class="sui-modal-close sui-button sui-button-red shipper-delete sui-button-red">
                    <i class="sui-icon-trash" aria-hidden="true"></i>
					<?php esc_html_e( "Delete", "shipper" ) ?>
                </button>
            </div>
        </div>
    </div>
</div>