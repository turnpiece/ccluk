<?php
/**
 * Shipper modals: something's up with WPMU DEV Dash template
 *
 * @package shipper
 */

$message = ! empty( $message ) ? $message : '';
$button = ! empty( $button ) ? $button : '';
$action = ! empty( $action ) ? $action : '';

$this->render('modals/welcome', array(
	'message' => __( 'This plugin works by migrating websites using the WPMU DEV API & Hub.', 'shipper' ) . " {$message}",
	'button' => $button,
	'button_class' => 'sui-button-primary',
	'skippable' => true,
	'action' => $action,
));