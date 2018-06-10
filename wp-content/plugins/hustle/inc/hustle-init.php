<?php
if( !class_exists("Hustle_Init") ):

/**
 * Class Hustle_Init
 */
class Hustle_Init {

	function __construct( Opt_In $hustle ){

		$hustle_db = new Hustle_Db();
		$email_services = new Hustle_Email_Services();
		$hustle->set_email_services( $email_services );

		// Hustle Migration from Wordpress Popup and Hustle 2.x
		$hustle_migration = new Hustle_Migration( $hustle );

		// Hubspot
		$hustle_hubpost = new Opt_In_HubSpot_Api();

		if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
			//Constant Contact
			$hustle_constantcontact = new Opt_In_ConstantContact_Api();
		}

		// Admin
		if( is_admin() ) {
			$module_admin = new Hustle_Module_Admin( $hustle );

			$popup_admin = new Hustle_Popup_Admin( $hustle, $email_services  );
			new Hustle_Popup_Admin_Ajax( $hustle, $popup_admin );

			$hustle_dashboard_admin = new Hustle_Dashboard_Admin( $email_services );

			$hustle_settings_admin = new Hustle_Settings_Admin( $hustle, $email_services );
			new Hustle_Settings_Admin_Ajax($hustle, $hustle_settings_admin );

			$slidein_admin = new Hustle_Slidein_Admin( $hustle, $email_services );
			new Hustle_Slidein_Admin_Ajax( $hustle, $slidein_admin );

			$embedded_admin = new Hustle_Embedded_Admin( $hustle, $email_services );
			new Hustle_Embedded_Admin_Ajax( $hustle, $embedded_admin );

			$social_sharing_admin = new Hustle_SShare_Admin();
			new Hustle_SShare_Admin_Ajax( $hustle, $social_sharing_admin );

			$upgrade_page = new Hustle_Upgrade_Free_Admin($hustle);
		}

		// Front
		$module_front = new Hustle_Module_Front($hustle);
		$module_front_ajax = new Hustle_Module_Front_Ajax($hustle);
	}
}

endif;