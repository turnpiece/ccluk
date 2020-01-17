<?php
/**
 * Shipper checks body copies: remote package size unknown
 *
 * @since v1.0.3
 * @package shipper
 */

$message = array(
	__( 'We experienced issues checking the remote package size.', 'shipper' ),
);
if ( ! empty( $has_existing_export ) ) {
	$message[] = __( 'However, we were able to find an existing export that we can use for this migration, so that is what we will use.', 'shipper' );
	$message[] = __( 'If that is not what you intended, please initiate an export migration on your source site instead.', 'shipper' );
}
$message[] = '<i>' . __( 'Please, make sure you are using equal versions of the plugin on both migration ends.', 'shipper' ) . '</i>';
?>
<p><?php echo wp_kses_post( join( ' ', $message ) ); ?></p>
<p>
	<?php esc_html_e( 'Also, please note that the time your site takes to migrate may vary considerably depending on many other factors (such as the speed of your current host!).', 'shipper' ); ?>
</p>