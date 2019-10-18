<?php
$args = array(
	'role'  => 'alert'
);

// Set unique identifier to notice.
if ( isset( $id ) && '' !== $id ) {
	$args['id'] = esc_attr( $id );
}

// Set default class.
$args['class'] = 'beehive-notice sui-notice';

// Set type of notice.
if ( isset( $type ) && '' !== $type ) {
	$args['class'] .= ' sui-notice-' . esc_attr( $type );
}

// Make notice dismissable.
if ( isset( $dismiss ) && true === $dismiss ) {
	$args['class'] .= ' sui-can-dismiss';
}

// Hide notice on load.
if ( isset( $hidden ) && true === $hidden ) {
	$args['style']  = 'display: none;';
	$args['hidden'] = '';
}

foreach ( $args as $key => $value ) {
	$attrs[] = $key . '="' . $value . '"';
}
?>

<div <?php echo implode( ' ', wp_kses_post( $attrs ) ); ?>>

	<?php if ( isset( $message ) && '' !== $message ) { ?>
		<p><?php echo $message; // phpcs:disable ?></p>
	<?php } ?>

	<?php if ( isset( $dismiss ) && true === $dismiss ) { ?>

		<button class="sui-button-icon beehive-notice-dismiss">
			<i class="sui-icon-cross-close" aria-hidden="true"></i>
			<span class="sui-screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'ga_trans' ); ?></span>
		</button>

	<?php } ?>

</div>