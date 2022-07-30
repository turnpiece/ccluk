<?php
/**
 * Shipper dash notice templates: root template
 *
 * @package shipper
 */

$check = new Shipper_Task_Check_Hub();

if ( ! $check->has_dashboard_present() ) {
	$this->render( 'msgs/welcome-dash-not-present', array( 'action' => $action ) );
} elseif ( ! $check->is_dashboard_active() ) {
	$this->render( 'msgs/welcome-dash-not-active', array( 'action' => $action ) );
} elseif ( ! $check->has_api_key() ) {
	$this->render( 'msgs/welcome-dash-not-logged', array( 'action' => $action ) );
}