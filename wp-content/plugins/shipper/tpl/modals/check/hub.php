<?php
/**
 * Shipper modal dialogs: Hub connectivity check
 *
 * @package shipper
 */

$task  = new Shipper_Task_Check_Hub();
$model = new Shipper_Model_Stored_Modals();

if ( ! $task->apply() ) {

	// We have errors - we automatically reset the modal state.
	$model->set( 'welcome', Shipper_Model_Stored_Modals::STATE_OPEN );
	$model->save();

	$errors = $task->get_errors(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
	if ( shipper_has_error( Shipper_Task_Check_Hub::ERR_DASH_PRESENT, $errors ) ) {
		$this->render(
			'modals/welcome-activation',
			array(
				'message' => __( 'To connect this website, you need to install the WPMU DEV Dashboard plugin.', 'shipper' ),
				'button'  => __( 'Download', 'shipper' ),
				'action'  => 'https://wpmudev.com/project/wpmu-dev-dashboard/',
			)
		);
	} elseif ( shipper_has_error( Shipper_Task_Check_Hub::ERR_DASH_ACTIVE, $errors ) ) {
		$this->render(
			'modals/welcome-activation',
			array(
				'message' => __( 'To connect this website, you need to activate and log in to the WPMU DEV Dashboard plugin.', 'shipper' ),
				'button'  => __( 'Activate', 'shipper' ),
				'action'  => network_admin_url( 'plugins.php' ),
			)
		);
	} elseif ( shipper_has_error( Shipper_Task_Check_Hub::ERR_DASH_APIKEY, $errors ) ) {
		$this->render(
			'modals/welcome-activation',
			array(
				'message' => __( 'To use it, you need to log in to the WPMU DEV Dashboard.', 'shipper' ),
				'button'  => __( 'Log in', 'shipper' ),
				'action'  => network_admin_url( 'admin.php?page=wpmudev' ),
			)
		);
	}
} else {
	if ( Shipper_Model_Stored_Modals::STATE_CLOSED !== $model->get( 'welcome', Shipper_Model_Stored_Modals::STATE_OPEN ) ) {
		$this->render( 'modals/welcome' );
	}
}
