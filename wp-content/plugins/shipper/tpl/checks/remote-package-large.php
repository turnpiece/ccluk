<?php
/**
 * Shipper checks body copies: remote package size too large
 *
 * @since v1.0.3
 * @package shipper
 */

$estimate     = Shipper_Model_Stored_Estimate::get_estimated_migration_time_span( $size );
$estimate_msg = '';
if ( ! empty( $estimate['high'] ) ) {
	$estimate_msg = sprintf(
		// translators: %s %s: package size and ETA.
		__( 'Your source site is %1$s in size which <b>could take up to %2$s to import</b> as we are using our advanced API to make sure the process is as stable as possible. We recommend that you export your site, rather than importing it, because the export method allows you to exclude large files from the migration, thus speeding up the process.', 'shipper' ),
		size_format( $size ),
		$estimate['high']
	);
} else {
	$estimate_msg = sprintf(
		// translators: %s: package size.
		__( 'Your source site is %1$s in size, but we were not able to estimate how long it might take to migrate it fully. Nevertheless, we still recommend that you export your site, rather than importing it, because the export method allows you to exclude large files from the migration, thus speeding up the process.', 'shipper' ),
		size_format( $size )
	);
}
?>
<div class="shipper-wizard-result-files">
	<p>
		<?php echo wp_kses_post( $estimate_msg ); ?>
	</p>
	<p>
		<?php esc_html_e( 'Also, please note that the time your site takes to migrate may vary considerably depending on many other factors (such as the speed of your current host!).', 'shipper' ); ?>
	</p>

	<div class="sui-row shipper-package-size-full-captain-notice">
		<div class="sui-col-md-4">
			<div class="shipper-captain-image"></div>
			<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
		</div>
		<div class="sui-col-md-8">
			<div class="sui-notice sui-notice-info">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>

						<p>
							<?php
							echo wp_kses_post(
								sprintf(
									// translators: %s" website url.
									__( 'Looks like a long time? You can use the <a href="%s" target="_blank"> Package Migration </a> method on your source site to create a package and upload it on this server to migrate in a matter of minutes.', 'shipper' ),
									network_admin_url( 'admin.php?page=shipper-packages' )
								)
							);
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>