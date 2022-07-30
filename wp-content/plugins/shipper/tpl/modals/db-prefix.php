<?php
/**
 * Shipper modal db-prefix.
 *
 * @package shipper
 */

$modal_id    = 'shipper-db-prefix';
$modal_class = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-db-prefix-modal' ),
	sanitize_html_class( $modal_id )
);

$arguments['modal_id'] = $modal_id;
$size_class            = 'sui-modal-md';
$modal_class          .= " {$size_class}";

$input_get_type     = filter_input( INPUT_GET, 'type' );
$input_get_site     = filter_input( INPUT_GET, 'site' );
$input_get_excludes = filter_input( INPUT_GET, 'is_excludes_picked' );
$query_strings      = sprintf( 'admin.php?page=shipper-api&type=%s&site=%s&is_excludes_picked=%s', $input_get_type, $input_get_site, $input_get_excludes );
$back_button_url    = network_admin_url( $query_strings );
?>

<div class="sui-modal <?php echo esc_attr( $modal_class ); ?>">
	<div
		role="dialog"
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $modal_id . '-title' ); ?>"
		aria-describedby="<?php echo esc_attr( $modal_id . '-description' ); ?>"
	>
		<div class="sui-box" role="document">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<h3 class="sui-box-title">
					<?php esc_html_e( 'Destination Database Prefix', 'shipper' ); ?>
				</h3>
				<button class="sui-button-icon sui-button-float--right" data-modal-close="sui-modal-content">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>
					</span>
				</button>
			</div>
			<div class="sui-box-body sui-box-body-slim">
				<div class="shipper-content">
					<div class="shipper-content-inside">
						<p>
							<?php esc_html_e( 'Choose whether you want to use your source\'s database prefix, existing database prefix of your destination site or use a custom database prefix on your destination site after migration.', 'shipper' ); ?>
						</p>
						<label class="sui-label"><?php esc_html_e( 'Destination Database Prefix', 'shipper' ); ?></label>
						<div class="sui-side-tabs">
							<div class="sui-tabs-menu">
								<label for="source" class="sui-tab-item active">
									<input type="radio" value="source" name="migrate_dbprefix" id="source" data-tab-menu="">
									<?php esc_html_e( 'Source’s Prefix', 'shipper' ); ?>
								</label>
								<label for="destination" class="sui-tab-item">
									<input type="radio" value="destination" name="migrate_dbprefix" data-tab-menu="" id="destination">
									<?php esc_html_e( 'Existing Destination Prefix', 'shipper' ); ?>
								</label>
								<label for="custom" class="sui-tab-item">
									<input type="radio" value="custom" name="migrate_dbprefix" data-tab-menu="custom-box" id="custom">
									<?php esc_html_e( 'Custom', 'shipper' ); ?>
								</label>
							</div>
							<div class="sui-tabs-content">
								<div class="sui-tab-content sui-tab-boxed" id="custom-box" data-tab-content="custom-box">
									<label for="shipper-custom-prefix" class="sui-label"><?php esc_html_e( 'Choose custom database prefix', 'shipper' ); ?></label>
									<input type="text" name="migrate_dbprefix_value" class="sui-form-control" placeholder="wp_" id="shipper-custom-prefix"/>
								</div>
							</div>
						</div>

						<a class="sui-button sui-button-ghost pull-left" href="<?php echo esc_url( $back_button_url ); ?>">
							<span class="sui-icon-arrow-left" aria-hidden="true"></span>
							<?php esc_html_e( 'Back', 'shipper' ); ?>
						</a>

						<button type="button" class="sui-button shipper-update-prefix pull-right">
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