<?php
add_action( 'wp_loaded', function () {
	$last_version = get_site_option( 'shipper_version', false );
	if ( $last_version == false ) {
		//treat it as 1.0.3
		$last_version = "1.0.3";
	}

	$version = SHIPPER_VERSION;
	if ( $last_version == '1.0.3' ) {
		//cancel
		$migration = new Shipper_Model_Stored_Migration();
		if ( $migration->is_active() ) {
			Shipper_Helper_Log::write( __( "Shipper version difference, please update both source and destination to latest version and try again.", 'shipper' ) );
			Shipper_Controller_Runner_Migration::get()->attempt_cancel();
		}
	}
	update_site_option( 'shipper_version', $version );
} );