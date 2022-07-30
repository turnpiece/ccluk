<?php
/**
 * Shipper autoloader
 *
 * @package shipper
 */

/**
 * Class name to file mapping procedure
 *
 * @param string $class Class name.
 */
function shipper_resolve_class( $class ) {
	if ( ! preg_match( '/^shipper_/i', $class ) ) {
		return false;
	}

	$raw  = explode( '_', preg_replace( '/^shipper_/i', '', strtolower( $class ) ) );
	$file = 'class-' . array_pop( $raw ) . '.php';
	$path = dirname( __FILE__ ) . '/shipper/' . join( DIRECTORY_SEPARATOR, $raw ) . "/{$file}";

	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

spl_autoload_register( 'shipper_resolve_class' );