<?php
/**
 * Shipper template tags: individual check reset
 *
 * @since v1.0.3
 * @package shipper
 */

$status   = ! empty( $check['status'] ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global
	? 'ok' === $check['status']
	: true;
$check_id = ! empty( $check['check_id'] )
	? $check['check_id']
	: ( ! empty( $check['title'] ) ? md5( $check['title'] ) : '' );
?>
<?php if ( ! $status ) { ?>
	<p class="shipper-recheck">
		<a href="#reload" class="sui-button sui-button-ghost"
			data-check="<?php echo esc_attr( $check_id ); ?>">
			<i class="sui-icon-update" aria-hidden="true"></i>
			<?php esc_html_e( 'Re-check', 'shipper' ); ?>
		</a>
	</p>
<?php } ?>