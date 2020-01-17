<?php
/**
 * Shipper email templates: import migration body
 *
 * @package shipper
 */

echo ! ! $status
	? esc_html( sprintf( __( 'Ahoy Captain!', 'shipper' ),$name ) )
	: esc_html( sprintf( __( 'Aye Matey,', 'shipper' ),$name ) )
;
?>


<?php
echo ! ! $status
	? esc_html(sprintf(
		__( 'Your site http://%1$s was successfully ported to http://%2$s.', 'shipper' ),
		$migration->get_destination(), $migration->get_source()
	))
	: esc_html(sprintf(
		__( 'Alas, Shipper was unable to transfer your data from http://%1$s to http://%2$s', 'shipper' ),
		$migration->get_destination(), $migration->get_source()
	));
?>


<?php
echo ! ! $status
	? esc_html( __( 'This calls for drink’s all around.', 'shipper' ) )
	: esc_html(
		__( 'Check the Shipper logs for details. Our support crew is standing by if you need assistance.', 'shipper' )
	);
?>


<?php
echo ! ! $status
	? esc_html( __( 'Raise your glass!', 'shipper' ) )
	: esc_html( __( 'Happy Sailing!', 'shipper' ) )
?>

<?php _e( 'Shipper & WPMU DEV Crew', 'shipper' );