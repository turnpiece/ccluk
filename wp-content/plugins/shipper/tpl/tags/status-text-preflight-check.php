<?php
/**
 * Shipper tag templates: preflight check status, with text
 *
 * @since v1.0.3
 * @package shipper
 */

$issues = array(
	'warning' => 0,
	'error'   => 0,
);
$items  = ! empty( $items ) && is_array( $items )
	? $items
	: array();

foreach ( $items as $item ) {
	if ( 'ok' === $item['status'] ) {
		continue;
	}

	$issues[ $item['status'] ] += 1;
}

$status = ! empty( $issues['error'] ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global
	? 'error'
	: ( ! empty( $issues['warning'] ) ? 'warning' : 'success' );

$text = ! empty( $issues['error'] )
	? (int) $issues['error']
	: ( ! empty( $issues['warning'] ) ? (int) $issues['warning'] : false );

if ( 'success' === $status && empty( $text ) ) {
	$this->render( 'tags/status-icon', array( 'hide' => false ) );
	$this->render( 'tags/status-text', array( 'hide' => true ) );
} else {
	$this->render( 'tags/status-icon', array( 'hide' => true ) );
	$this->render(
		'tags/status-text',
		array(
			'status' => $status,
			'text'   => $text,
			'hide'   => false,
		)
	);
}