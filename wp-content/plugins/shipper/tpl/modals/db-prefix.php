<?php
$modal_id              = "shipper-db-prefix";
$modal_class           = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-db-prefix-modal' ),
	sanitize_html_class( $modal_id )
);
$arguments['modal_id'] = $modal_id;

$size_class  = 'sui-dialog-reduced';
$modal_class .= " {$size_class}";
?>

<div
		class="sui-dialog sui-dialog-alt <?php echo esc_attr( $modal_class ); ?>"
		tabindex="-1" id="<?php echo esc_attr( $modal_id ); ?>" aria-hidden="true">
	<div class="sui-dialog-overlay" data-a11y-dialog-hide="<?php echo esc_attr( $modal_id ); ?>"></div>

	<div class="sui-dialog-content sui-fade-in" role="dialog">
		<div class="sui-box" role="document">
			<div class="sui-box-header sui-block-content-center">
				<h3 class="sui-box-title">
					<?php esc_html_e( 'Destination Database Prefix', 'shipper' ); ?>
				</h3>
				<div class="sui-actions-right">
					<!--                    <a href="--><?php //echo esc_url( admin_url( 'admin.php?page=shipper' ) ); ?><!--"-->
					<!--                       class="shipper-go-back">-->
					<!--                        <i class="sui-icon-close" aria-hidden="true"></i>-->
					<!--                        <span>--><?php //esc_html_e( 'Cancel', 'shipper' ); ?><!--</span>-->
					<!--                    </a>-->
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
						<p>
							<?php esc_html_e( 'Choose whether you want to use your source\'s database prefix, existing database prefix of your destination site or use a custom database prefix on your destination site after migration.', 'shipper' ) ?>
						</p>
						<label class="sui-label"><?php esc_html_e( 'Destination Database Prefix', 'shipper' ) ?></label>
						<div class="sui-side-tabs">
							<div class="sui-tabs-menu">
								<label for="source"
								       class="sui-tab-item active">
									<input type="radio" value="source" name="migrate_dbprefix" id="source" data-tab-menu="">
									<?php esc_html_e( 'Source’s Prefix', 'shipper' ) ?>
								</label>
								<label for="destination"
								       class="sui-tab-item">
									<input type="radio" value="destination" name="migrate_dbprefix" data-tab-menu=""
									       id="destination">
									<?php esc_html_e( 'Existing Destination Prefix', 'shipper' ) ?>
								</label>
								<label for="custom"
								       class="sui-tab-item">
									<input type="radio" value="custom" name="migrate_dbprefix" data-tab-menu="custom-box"
									       id="custom">
									<?php esc_html_e( 'Custom', 'shipper' ) ?>
								</label>
							</div>
							<div class="sui-tabs-content">
								<div class="sui-tab-content sui-tab-boxed"
								     id="custom-box" data-tab-content="custom-box">
									<label for="shipper-custom-prefix"
									       class="sui-label"><?php esc_html_e( 'Choose custom database prefix', 'shipper' ) ?></label>
									<input type="text" name="migrate_dbprefix_value" class="sui-form-control"
									       placeholder="wp_" id="shipper-custom-prefix"/>
								</div>
							</div>
						</div>
						<button class="sui-button shipper-cancel sui-button-ghost pull-left"><?php esc_html_e( 'Cancel', 'shipper' ) ?></button>
						<button type="button" class="sui-button shipper-update-prefix pull-right">
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