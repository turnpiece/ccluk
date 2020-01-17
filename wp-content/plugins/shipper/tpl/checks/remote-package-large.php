<?php
/**
 * Shipper checks body copies: remote package size too large
 *
 * @since v1.0.3
 * @package shipper
 */

$estimate = Shipper_Model_Stored_Estimate::get_estimated_migration_time_span( $size );
$estimate_msg = '';
if ( ! empty( $estimate['high'] ) && ! empty( $estimate['low'] ) ) {
	$estimate_msg = sprintf(
		__( 'Your source site is %1$s in size which <b>could take %2$d to %3$d hours to import</b> as we are using our advanced API to make sure the process is as stable as possible. We recommend that you export your site, rather than importing it, because the export method allows you to exclude large files from the migration, thus speeding up the process.', 'shipper' ),
		size_format( $size ), $estimate['low'], $estimate['high']
	);
} else {
	$estimate_msg = sprintf(
		__( 'Your source site is %1$s in size, but we were not able to estimate how long it might take to migrate it fully. Nevertheless, we still recommend that you export your site, rather than importing it, because the export method allows you to exclude large files from the migration, thus speeding up the process.', 'shipper' ),
		size_format( $size )
	);
}
?>
<p>
	<?php echo wp_kses_post( $estimate_msg ); ?>
</p>
<p>
	<?php esc_html_e( 'Also, please note that the time your site takes to migrate may vary considerably depending on many other factors (such as the speed of your current host!).', 'shipper' ); ?>
</p>