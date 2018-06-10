<?php
/**
 * A class to serve static data
 *
 * Class Opt_In_Static
 */
if ( !class_exists ('Opt_In_Static', false ) ) {
	class Opt_In_Static {
		/**
		 * Returns animations
		 * Returns Popup Pro animations if it's installed and active
		 *
		 *
		 * @return object
		 */
		public function get_animations(){

			$animations_in = array(
				'' => array(
					'' => __( 'No Animation', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Bouncing Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'bounceIn' => __( 'Bounce In', Opt_In::TEXT_DOMAIN ),
					'bounceInUp' => __( 'Bounce In Up', Opt_In::TEXT_DOMAIN ),
					'bounceInRight' => __( 'Bounce In Right', Opt_In::TEXT_DOMAIN ),
					'bounceInDown' => __( 'Bounce In Down', Opt_In::TEXT_DOMAIN ),
					'bounceInLeft' => __( 'Bounce In Left', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Fading Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'fadeIn' => __( 'Fade In', Opt_In::TEXT_DOMAIN ),
					'fadeInUp' => __( 'Fade In Up', Opt_In::TEXT_DOMAIN ),
					'fadeInRight' => __( 'Fade In Right', Opt_In::TEXT_DOMAIN ),
					'fadeInDown' => __( 'Fade In Down', Opt_In::TEXT_DOMAIN ),
					'fadeInLeft' => __( 'Fade In Left', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Falling Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'fall' => __( 'Fall In', Opt_In::TEXT_DOMAIN ), // MISSING
					'sidefall' => __( 'Fade In Side', Opt_In::TEXT_DOMAIN ), // MISSING
				),
				__( 'Rotating Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'rotateIn' => __( 'Rotate In', Opt_In::TEXT_DOMAIN ),
					'rotateInDownLeft' => __( 'Rotate In Down Left', Opt_In::TEXT_DOMAIN ),
					'rotateInDownRight' => __( 'Rotate In Down Right', Opt_In::TEXT_DOMAIN ),
					'rotateInUpLeft' => __( 'Rotate In Up Left', Opt_In::TEXT_DOMAIN ),
					'rotateInUpRight' => __( 'Rotate In Up Right', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Sliding Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'slideInUp' => __( 'Slide In Up', Opt_In::TEXT_DOMAIN ),
					'slideInRight' => __( 'Slide In Right', Opt_In::TEXT_DOMAIN ),
					'slideInDown' => __( 'Slide In Down', Opt_In::TEXT_DOMAIN ),
					'slideInLeft' => __( 'Slide In Left', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Zoom Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'zoomIn' => __( 'Zoom In', Opt_In::TEXT_DOMAIN ),
					'zoomInUp' => __( 'Zoom In Up', Opt_In::TEXT_DOMAIN ),
					'zoomInRight' => __( 'Zoom In Right', Opt_In::TEXT_DOMAIN ),
					'zoomInDown' => __( 'Zoom In Down', Opt_In::TEXT_DOMAIN ),
					'zoomInLeft' => __( 'Zoom In Left', Opt_In::TEXT_DOMAIN ),
					'scaled' => __( 'Super Scaled', Opt_In::TEXT_DOMAIN ), // MISSING
				),
				__( '3D Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'sign wpoi-modal' => __( '3D Sign', Opt_In::TEXT_DOMAIN ), // MISSING
					'slit wpoi-modal' => __( '3D Slit', Opt_In::TEXT_DOMAIN ), // MISSING
					'flipx wpoi-modal' => __( '3D Flip (Horizontal)', Opt_In::TEXT_DOMAIN ), // MISSING
					'flipy wpoi-modal' => __( '3D Flip (Vertical)', Opt_In::TEXT_DOMAIN ), // MISSING
					'rotatex wpoi-modal' => __( '3D Rotate (Left)', Opt_In::TEXT_DOMAIN ), // MISSING
					'rotatey wpoi-modal' => __( '3D Rotate (Bottom)', Opt_In::TEXT_DOMAIN ), // MISSING
				),
				__( 'Special Entrances', Opt_In::TEXT_DOMAIN ) => array(
					'rollIn' => __( 'Roll In', Opt_In::TEXT_DOMAIN ),
					'lightSpeedIn' => __( 'Light Speed In', Opt_In::TEXT_DOMAIN ),
					'newspaperIn' => __( 'Newspaper In', Opt_In::TEXT_DOMAIN ),
				),
			);

			$animations_out = array(
				'' => array(
					'' => __( 'No Animation', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Bouncing Exits', Opt_In::TEXT_DOMAIN ) => array(
					'bounceOut' => __( 'Bounce Out', Opt_In::TEXT_DOMAIN ),
					'bounceOutUp' => __( 'Bounce Out Up', Opt_In::TEXT_DOMAIN ),
					'bounceOutRight' => __( 'Bounce Out Right', Opt_In::TEXT_DOMAIN ),
					'bounceOutDown' => __( 'Bounce Out Down', Opt_In::TEXT_DOMAIN ),
					'bounceOutLeft' => __( 'Bounce Out Left', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Fading Exits', Opt_In::TEXT_DOMAIN ) => array(
					'fadeOut' => __( 'Fade Out', Opt_In::TEXT_DOMAIN ),
					'fadeOutUp' => __( 'Fade Out Up', Opt_In::TEXT_DOMAIN ),
					'fadeOutRight' => __( 'Fade Out Right', Opt_In::TEXT_DOMAIN ),
					'fadeOutDown' => __( 'Fade Out Down', Opt_In::TEXT_DOMAIN ),
					'fadeOutLeft' => __( 'Fade Out Left', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Rotating Exits', Opt_In::TEXT_DOMAIN ) => array(
					'rotateOut' => __( 'Rotate In', Opt_In::TEXT_DOMAIN ),
					'rotateOutUp' => __( 'Rotate In Up', Opt_In::TEXT_DOMAIN ),
					'rotateOutRight' => __( 'Rotate In Right', Opt_In::TEXT_DOMAIN ),
					'rotateOutDown' => __( 'Rotate In Down', Opt_In::TEXT_DOMAIN ),
					'rotateOutLeft' => __( 'Rotate In Left', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Sliding Exits', Opt_In::TEXT_DOMAIN ) => array(
					'slideOutUp' => __( 'Slide Out Up', Opt_In::TEXT_DOMAIN ),
					'slideOutRight' => __( 'Slide Out Left', Opt_In::TEXT_DOMAIN ),
					'slideOutDown' => __( 'Slide Out Down', Opt_In::TEXT_DOMAIN ),
					'slideOutLeft' => __( 'Slide Out Right', Opt_In::TEXT_DOMAIN ),
				),
				__( 'Zoom Exits', Opt_In::TEXT_DOMAIN ) => array(
					'zoomOut' => __( 'Zoom Out', Opt_In::TEXT_DOMAIN ),
					'zoomOutUp' => __( 'Zoom Out Up', Opt_In::TEXT_DOMAIN ),
					'zoomOutRight' => __( 'Zoom Out Right', Opt_In::TEXT_DOMAIN ),
					'zoomOutDown' => __( 'Slide Out Down', Opt_In::TEXT_DOMAIN ),
					'zoomOutLeft' => __( 'Slide Out Left', Opt_In::TEXT_DOMAIN ),
					'scaled' => __( 'Super Scaled', Opt_In::TEXT_DOMAIN ), // MISSING
				),
				__( '3D Effects', Opt_In::TEXT_DOMAIN ) => array(
					'sign wpoi-modal' => __( '3D Sign', Opt_In::TEXT_DOMAIN ), // MISSING
					'flipx wpoi-modal' => __( '3D Flip (Horizontal)', Opt_In::TEXT_DOMAIN ), // MISSING
					'flipy wpoi-modal' => __( '3D Flip (Vertical)', Opt_In::TEXT_DOMAIN ), // MISSING
					'rotatex wpoi-modal' => __( '3D Rotate (Left)', Opt_In::TEXT_DOMAIN ), // MISSING
					'rotatey wpoi-modal' => __( '3D Rotate (Bottom)', Opt_In::TEXT_DOMAIN ), // MISSING
				),
				__( 'Special Exits', Opt_In::TEXT_DOMAIN ) => array(
					'rollOut' => __( 'Roll Out', Opt_In::TEXT_DOMAIN ),
					'lightSpeedOut' => __( 'Light Speed Out', Opt_In::TEXT_DOMAIN ),
					'newspaperOut' => __( 'Newspaper Out', Opt_In::TEXT_DOMAIN ),
				),
			);

			return (object) array(
				'in' => $animations_in,
				'out' => $animations_out,
			);

		}


		/**
		 * Returns palettes used to color optins
		 *
		 * @return array
		 */
		public function get_palettes(){
			return array(
				'Gray Slate' => array(
					'main_bg_color' => '#38454E',
					'image_container_bg' => '#35414A',
					'form_area_bg' => '#5D7380',

					'title_color' => '#FDFDFD',
					'subtitle_color' => '#FDFDFD',
					'content_color' => '#ADB5B7',

					'link_static_color' => '#38C5B5',
					'link_hover_color' => '#49E2D1',
					'link_active_color' => '#49E2D1',

					'cta_button_static_bg' => '#38C5B5',
					'cta_button_hover_bg' => '#49E2D1',
					'cta_button_active_bg' => '#49E2D1',

					'cta_button_static_color' => '#FFFFFF',
					'cta_button_hover_color' => '#FFFFFF',
					'cta_button_active_color' => '#FFFFFF',

					'optin_input_static_bg' => '#FDFDFD',
					'optin_input_hover_bg' => '#FDFDFD',
					'optin_input_active_bg' => '#FDFDFD',

					'optin_input_icon' => '#ADB5B7',

					'optin_placeholder_color' => '#ADB5B7',

					'optin_form_field_text_static_color' => '#363B3F',
					'optin_form_field_text_hover_color' => '#363B3F',
					'optin_form_field_text_active_color' => '#363B3F',

					'optin_submit_button_static_bg' => '#38C5B5',
					'optin_submit_button_hover_bg' => '#38C5B5',
					'optin_submit_button_active_bg' => '#38C5B5',

					'optin_submit_button_static_color' => '#FDFDFD',
					'optin_submit_button_hover_color' => '#FDFDFD',
					'optin_submit_button_active_color' => '#FDFDFD',

					'optin_error_text_color' => '#F1F1F1',
					'optin_error_text_bg' => '#EA6464',

					'optin_mailchimp_title_color' => '#FDFDFD',
					'optin_mailchimp_labels_color' => '#ADB5B7',

					'optin_check_radio_bg' => '#FDFDFD',

					'optin_check_radio_tick_color' => '#38C5B5',

					'optin_success_tick_color' => '#38C5B5',

					'optin_success_content_color' => '#FDFDFD',

					'overlay_bg' => 'rgba(51,51,51,0.9)',

					'close_button_static_color' => '#38C5B5',
					'close_button_hover_color' => '#49E2D1',
					'close_button_active_color' => '#49E2D1',
				),
				'Coffee' => array(
					'main_bg_color' => '#46403B',
					'image_container_bg' => '#423D38',
					'form_area_bg' => '#59524B',

					'title_color' => '#FDFDFD',
					'subtitle_color' => '#FDFDFD',
					'content_color' => '#ADB5B7',

					'link_static_color' => '#C6A685',
					'link_hover_color' => '#C69767',
					'link_active_color' => '#C69767',

					'cta_button_static_bg' => '#C6A685',
					'cta_button_hover_bg' => '#C69767',
					'cta_button_active_bg' => '#C69767',

					'cta_button_static_color' => '#FFFFFF',
					'cta_button_hover_color' => '#FFFFFF',
					'cta_button_active_color' => '#FFFFFF',

					'optin_input_static_bg' => '#FDFDFD',
					'optin_input_hover_bg' => '#FDFDFD',
					'optin_input_active_bg' => '#FDFDFD',

					'optin_input_icon' => '#ADB5B7',

					'optin_placeholder_color' => '#ADB5B7',

					'optin_form_field_text_static_color' => '#363B3F',
					'optin_form_field_text_hover_color' => '#363B3F',
					'optin_form_field_text_active_color' => '#363B3F',

					'optin_submit_button_static_bg' => '#C6A685',
					'optin_submit_button_hover_bg' => '#C69767',
					'optin_submit_button_active_bg' => '#C69767',

					'optin_submit_button_static_color' => '#FDFDFD',
					'optin_submit_button_hover_color' => '#FDFDFD',
					'optin_submit_button_active_color' => '#FDFDFD',

					'optin_error_text_color' => '#F1F1F1',
					'optin_error_text_bg' => '#EA6464',

					'optin_mailchimp_title_color' => '#FDFDFD',
					'optin_mailchimp_labels_color' => '#ADB5B7',

					'optin_check_radio_bg' => '#FDFDFD',

					'optin_check_radio_tick_color' => '#38C5B5',

					'optin_success_tick_color' => '#38C5B5',

					'optin_success_content_color' => '#FDFDFD',

					'overlay_bg' => 'rgba(51,51,51,0.9)',

					'close_button_static_color' => '#FDFDFD',
					'close_button_hover_color' => '#FDFDFD',
					'close_button_active_color' => '#FDFDFD',
				),
				'Ectoplasm' => array(
					'main_bg_color' => '#403159',
					'image_container_bg' => '#3D2F54',
					'form_area_bg' => '#513E70',

					'title_color' => '#FDFDFD',
					'subtitle_color' => '#FDFDFD',
					'content_color' => '#ADB5B7',

					'link_static_color' => '#A4B824',
					'link_hover_color' => '#B9CE33',
					'link_active_color' => '#B9CE33',

					'cta_button_static_bg' => '#A4B824',
					'cta_button_hover_bg' => '#B9CE33',
					'cta_button_active_bg' => '#B9CE33',

					'cta_button_static_color' => '#FFFFFF',
					'cta_button_hover_color' => '#FFFFFF',
					'cta_button_active_color' => '#FFFFFF',

					'optin_input_static_bg' => '#FDFDFD',
					'optin_input_hover_bg' => '#FDFDFD',
					'optin_input_active_bg' => '#FDFDFD',

					'optin_input_icon' => '#ADB5B7',

					'optin_placeholder_color' => '#ADB5B7',

					'optin_form_field_text_static_color' => '#363B3F',
					'optin_form_field_text_hover_color' => '#363B3F',
					'optin_form_field_text_active_color' => '#363B3F',

					'optin_submit_button_static_bg' => '#A4B824',
					'optin_submit_button_hover_bg' => '#B9CE33',
					'optin_submit_button_active_bg' => '#B9CE33',

					'optin_submit_button_static_color' => '#FDFDFD',
					'optin_submit_button_hover_color' => '#FDFDFD',
					'optin_submit_button_active_color' => '#FDFDFD',

					'optin_error_text_color' => '#F1F1F1',
					'optin_error_text_bg' => '#EA6464',

					'optin_mailchimp_title_color' => '#FDFDFD',
					'optin_mailchimp_labels_color' => '#ADB5B7',

					'optin_check_radio_bg' => '#FDFDFD',

					'optin_check_radio_tick_color' => '#38C5B5',

					'optin_success_tick_color' => '#38C5B5',

					'optin_success_content_color' => '#FDFDFD',

					'overlay_bg' => 'rgba(51,51,51,0.9)',

					'close_button_static_color' => '#FDFDFD',
					'close_button_hover_color' => '#FDFDFD',
					'close_button_active_color' => '#FDFDFD',
				),
				'Blue' => array(
					'main_bg_color' => '#176387',
					'image_container_bg' => '#165E80',
					'form_area_bg' => '#78B5D1',

					'title_color' => '#FDFDFD',
					'subtitle_color' => '#FDFDFD',
					'content_color' => '#ADB5B7',

					'link_static_color' => '#78B5D1',
					'link_hover_color' => '#4D95B6',
					'link_active_color' => '#4D95B6',

					'cta_button_static_bg' => '#4D95B6',
					'cta_button_hover_bg' => '#78B5D1',
					'cta_button_active_bg' => '#78B5D1',

					'cta_button_static_color' => '#FFFFFF',
					'cta_button_hover_color' => '#FFFFFF',
					'cta_button_active_color' => '#FFFFFF',

					'optin_input_static_bg' => '#FDFDFD',
					'optin_input_hover_bg' => '#FDFDFD',
					'optin_input_active_bg' => '#FDFDFD',

					'optin_input_icon' => '#ADB5B7',

					'optin_placeholder_color' => '#ADB5B7',

					'optin_form_field_text_static_color' => '#363B3F',
					'optin_form_field_text_hover_color' => '#363B3F',
					'optin_form_field_text_active_color' => '#363B3F',

					'optin_submit_button_static_bg' => '#4D95B6',
					'optin_submit_button_hover_bg' => '#176387',
					'optin_submit_button_active_bg' => '#176387',

					'optin_submit_button_static_color' => '#FDFDFD',
					'optin_submit_button_hover_color' => '#FDFDFD',
					'optin_submit_button_active_color' => '#FDFDFD',

					'optin_error_text_color' => '#F1F1F1',
					'optin_error_text_bg' => '#EA6464',

					'optin_mailchimp_title_color' => '#FDFDFD',
					'optin_mailchimp_labels_color' => '#ADB5B7',

					'optin_check_radio_bg' => '#FDFDFD',

					'optin_check_radio_tick_color' => '#38C5B5',

					'optin_success_tick_color' => '#38C5B5',

					'optin_success_content_color' => '#FDFDFD',

					'overlay_bg' => 'rgba(51,51,51,0.9)',

					'close_button_static_color' => '#FDFDFD',
					'close_button_hover_color' => '#FDFDFD',
					'close_button_active_color' => '#FDFDFD',
				),
				'Sunrise' => array(
					'main_bg_color' => '#B03E34',
					'image_container_bg' => '#A73B31',
					'form_area_bg' => '#CB4B40',

					'title_color' => '#FDFDFD',
					'subtitle_color' => '#FDFDFD',
					'content_color' => '#ADB5B7',

					'link_static_color' => '#CBB000',
					'link_hover_color' => '#CCB83D',
					'link_active_color' => '#CCB83D',

					'cta_button_static_bg' => '#CBB000',
					'cta_button_hover_bg' => '#CCB83D',
					'cta_button_active_bg' => '#CCB83D',

					'cta_button_static_color' => '#FFFFFF',
					'cta_button_hover_color' => '#FFFFFF',
					'cta_button_active_color' => '#FFFFFF',

					'optin_input_static_bg' => '#FDFDFD',
					'optin_input_hover_bg' => '#FDFDFD',
					'optin_input_active_bg' => '#FDFDFD',

					'optin_input_icon' => '#ADB5B7',

					'optin_placeholder_color' => '#ADB5B7',

					'optin_form_field_text_static_color' => '#363B3F',
					'optin_form_field_text_hover_color' => '#363B3F',
					'optin_form_field_text_active_color' => '#363B3F',

					'optin_submit_button_static_bg' => '#CBB000',
					'optin_submit_button_hover_bg' => '#CCB83D',
					'optin_submit_button_active_bg' => '#CCB83D',

					'optin_submit_button_static_color' => '#FDFDFD',
					'optin_submit_button_hover_color' => '#FDFDFD',
					'optin_submit_button_active_color' => '#FDFDFD',

					'optin_error_text_color' => '#F1F1F1',
					'optin_error_text_bg' => '#EA6464',

					'optin_mailchimp_title_color' => '#FDFDFD',
					'optin_mailchimp_labels_color' => '#ADB5B7',

					'optin_check_radio_bg' => '#FDFDFD',

					'optin_check_radio_tick_color' => '#38C5B5',

					'optin_success_tick_color' => '#38C5B5',

					'optin_success_content_color' => '#FDFDFD',

					'overlay_bg' => 'rgba(51,51,51,0.9)',

					'close_button_static_color' => '#FDFDFD',
					'close_button_hover_color' => '#FDFDFD',
					'close_button_active_color' => '#FDFDFD',
				),
				'Midnight' => array(
					'main_bg_color' => '#25282B',
					'image_container_bg' => '#232629',
					'form_area_bg' => '#363B3F',

					'title_color' => '#FDFDFD',
					'subtitle_color' => '#FDFDFD',
					'content_color' => '#ADB5B7',

					'link_static_color' => '#DD4F3D',
					'link_hover_color' => '#C63D2B',
					'link_active_color' => '#C63D2B',

					'cta_button_static_bg' => '#DD4F3D',
					'cta_button_hover_bg' => '#C63D2B',
					'cta_button_active_bg' => '#C63D2B',

					'cta_button_static_color' => '#FFFFFF',
					'cta_button_hover_color' => '#FFFFFF',
					'cta_button_active_color' => '#FFFFFF',

					'optin_input_static_bg' => '#FDFDFD',
					'optin_input_hover_bg' => '#FDFDFD',
					'optin_input_active_bg' => '#FDFDFD',

					'optin_input_icon' => '#ADB5B7',

					'optin_placeholder_color' => '#ADB5B7',

					'optin_form_field_text_static_color' => '#363B3F',
					'optin_form_field_text_hover_color' => '#363B3F',
					'optin_form_field_text_active_color' => '#363B3F',

					'optin_submit_button_static_bg' => '#DD4F3D',
					'optin_submit_button_hover_bg' => '#C63D2B',
					'optin_submit_button_active_bg' => '#C63D2B',

					'optin_submit_button_static_color' => '#FDFDFD',
					'optin_submit_button_hover_color' => '#FDFDFD',
					'optin_submit_button_active_color' => '#FDFDFD',

					'optin_error_text_color' => '#F1F1F1',
					'optin_error_text_bg' => '#EA6464',

					'optin_mailchimp_title_color' => '#FDFDFD',
					'optin_mailchimp_labels_color' => '#ADB5B7',

					'optin_check_radio_bg' => '#FDFDFD',

					'optin_check_radio_tick_color' => '#38C5B5',

					'optin_success_tick_color' => '#38C5B5',

					'optin_success_content_color' => '#FDFDFD',

					'overlay_bg' => 'rgba(51,51,51,0.9)',

					'close_button_static_color' => '#FDFDFD',
					'close_button_hover_color' => '#FDFDFD',
					'close_button_active_color' => '#FDFDFD',
				)
			);
		}

		/**
		 * Default form filds for a new form
		 */
		public function default_form_fields() {
			return array(
				'email' => array(
					'required'      => true,
					'label'         => __( 'Your email', Opt_In::TEXT_DOMAIN ),
					'name'          => 'email',
					'type'          => 'email',
					'placeholder'   => 'johnsmith@example.com',
					'delete'        => false
				),
				'first_name' => array(
					'required'      => false,
					'label'         => __( 'First Name', Opt_In::TEXT_DOMAIN ),
					'name'          => 'first_name',
					'type'          => 'name',
					'placeholder'   => 'John',
					'delete'        => true
				),
				'last_name' => array(
					'required'      => false,
					'label'         => __( 'Last Name', Opt_In::TEXT_DOMAIN ),
					'name'          => 'last_name',
					'type'          => 'name',
					'placeholder'   => 'Smith',
					'delete'        => true
				),
				'submit' => array(
					'required'      => true,
					'label'         => __( 'Submit', Opt_In::TEXT_DOMAIN ),
					'name'          => 'submit',
					'type'          => 'submit',
					'placeholder'   => 'Subscribe',
					'delete'        => false
				)
			);
		}

		public function get_providers_with_args() {
			return array(
				'mailchimp'
			);
		}

		public static function get_client_ip() {

			$ip_addr = lib3()->net->current_ip()->ip;
			if ( $ip_addr )
				$ipaddress = $ip_addr;
			else
				$ipaddress = 'UNKNOWN';
			return $ipaddress;
		}

		/**
		 * Returns array of countries
		 *
		 * @return array|mixed|null|void
		 */
		public function get_countries() {

			return apply_filters( 'opt_in-country-list', array(
					'AU' => __( 'Australia', Opt_In::TEXT_DOMAIN ),
					'AF' => __( 'Afghanistan', Opt_In::TEXT_DOMAIN ),
					'AL' => __( 'Albania', Opt_In::TEXT_DOMAIN ),
					'DZ' => __( 'Algeria', Opt_In::TEXT_DOMAIN ),
					'AS' => __( 'American Samoa', Opt_In::TEXT_DOMAIN ),
					'AD' => __( 'Andorra', Opt_In::TEXT_DOMAIN ),
					'AO' => __( 'Angola', Opt_In::TEXT_DOMAIN ),
					'AI' => __( 'Anguilla', Opt_In::TEXT_DOMAIN ),
					'AQ' => __( 'Antarctica', Opt_In::TEXT_DOMAIN ),
					'AG' => __( 'Antigua & Barbuda', Opt_In::TEXT_DOMAIN ),
					'AR' => __( 'Argentina', Opt_In::TEXT_DOMAIN ),
					'AM' => __( 'Armenia', Opt_In::TEXT_DOMAIN ),
					'AW' => __( 'Aruba', Opt_In::TEXT_DOMAIN ),
					'AT' => __( 'Austria', Opt_In::TEXT_DOMAIN ),
					'AZ' => __( 'Azerbaijan', Opt_In::TEXT_DOMAIN ),
					'BS' => __( 'Bahamas', Opt_In::TEXT_DOMAIN ),
					'BH' => __( 'Bahrain', Opt_In::TEXT_DOMAIN ),
					'BD' => __( 'Bangladesh', Opt_In::TEXT_DOMAIN ),
					'BB' => __( 'Barbados', Opt_In::TEXT_DOMAIN ),
					'BY' => __( 'Belarus', Opt_In::TEXT_DOMAIN ),
					'BE' => __( 'Belgium', Opt_In::TEXT_DOMAIN ),
					'BZ' => __( 'Belize', Opt_In::TEXT_DOMAIN ),
					'BJ' => __( 'Benin', Opt_In::TEXT_DOMAIN ),
					'BM' => __( 'Bermuda', Opt_In::TEXT_DOMAIN ),
					'BT' => __( 'Bhutan', Opt_In::TEXT_DOMAIN ),
					'BO' => __( 'Bolivia', Opt_In::TEXT_DOMAIN ),
					'BA' => __( 'Bosnia/Hercegovina', Opt_In::TEXT_DOMAIN ),
					'BW' => __( 'Botswana', Opt_In::TEXT_DOMAIN ),
					'BV' => __( 'Bouvet Island', Opt_In::TEXT_DOMAIN ),
					'BR' => __( 'Brazil', Opt_In::TEXT_DOMAIN ),
					'IO' => __( 'British Indian Ocean Territory', Opt_In::TEXT_DOMAIN ),
					'BN' => __( 'Brunei Darussalam', Opt_In::TEXT_DOMAIN ),
					'BG' => __( 'Bulgaria', Opt_In::TEXT_DOMAIN ),
					'BF' => __( 'Burkina Faso', Opt_In::TEXT_DOMAIN ),
					'BI' => __( 'Burundi', Opt_In::TEXT_DOMAIN ),
					'KH' => __( 'Cambodia', Opt_In::TEXT_DOMAIN ),
					'CM' => __( 'Cameroon', Opt_In::TEXT_DOMAIN ),
					'CA' => __( 'Canada', Opt_In::TEXT_DOMAIN ),
					'CV' => __( 'Cape Verde', Opt_In::TEXT_DOMAIN ),
					'KY' => __( 'Cayman Is', Opt_In::TEXT_DOMAIN ),
					'CF' => __( 'Central African Republic', Opt_In::TEXT_DOMAIN ),
					'TD' => __( 'Chad', Opt_In::TEXT_DOMAIN ),
					'CL' => __( 'Chile', Opt_In::TEXT_DOMAIN ),
					'CN' => __( 'China, People\'s Republic of', Opt_In::TEXT_DOMAIN ),
					'CX' => __( 'Christmas Island', Opt_In::TEXT_DOMAIN ),
					'CC' => __( 'Cocos Islands', Opt_In::TEXT_DOMAIN ),
					'CO' => __( 'Colombia', Opt_In::TEXT_DOMAIN ),
					'KM' => __( 'Comoros', Opt_In::TEXT_DOMAIN ),
					'CG' => __( 'Congo', Opt_In::TEXT_DOMAIN ),
					'CD' => __( 'Congo, Democratic Republic', Opt_In::TEXT_DOMAIN ),
					'CK' => __( 'Cook Islands', Opt_In::TEXT_DOMAIN ),
					'CR' => __( 'Costa Rica', Opt_In::TEXT_DOMAIN ),
					'CI' => __( 'Cote d\'Ivoire', Opt_In::TEXT_DOMAIN ),
					'HR' => __( 'Croatia', Opt_In::TEXT_DOMAIN ),
					'CU' => __( 'Cuba', Opt_In::TEXT_DOMAIN ),
					'CY' => __( 'Cyprus', Opt_In::TEXT_DOMAIN ),
					'CZ' => __( 'Czech Republic', Opt_In::TEXT_DOMAIN ),
					'DK' => __( 'Denmark', Opt_In::TEXT_DOMAIN ),
					'DJ' => __( 'Djibouti', Opt_In::TEXT_DOMAIN ),
					'DM' => __( 'Dominica', Opt_In::TEXT_DOMAIN ),
					'DO' => __( 'Dominican Republic', Opt_In::TEXT_DOMAIN ),
					'TP' => __( 'East Timor', Opt_In::TEXT_DOMAIN ),
					'EC' => __( 'Ecuador', Opt_In::TEXT_DOMAIN ),
					'EG' => __( 'Egypt', Opt_In::TEXT_DOMAIN ),
					'SV' => __( 'El Salvador', Opt_In::TEXT_DOMAIN ),
					'GQ' => __( 'Equatorial Guinea', Opt_In::TEXT_DOMAIN ),
					'ER' => __( 'Eritrea', Opt_In::TEXT_DOMAIN ),
					'EE' => __( 'Estonia', Opt_In::TEXT_DOMAIN ),
					'ET' => __( 'Ethiopia', Opt_In::TEXT_DOMAIN ),
					'FK' => __( 'Falkland Islands', Opt_In::TEXT_DOMAIN ),
					'FO' => __( 'Faroe Islands', Opt_In::TEXT_DOMAIN ),
					'FJ' => __( 'Fiji', Opt_In::TEXT_DOMAIN ),
					'FI' => __( 'Finland', Opt_In::TEXT_DOMAIN ),
					'FR' => __( 'France', Opt_In::TEXT_DOMAIN ),
					'FX' => __( 'France, Metropolitan', Opt_In::TEXT_DOMAIN ),
					'GF' => __( 'French Guiana', Opt_In::TEXT_DOMAIN ),
					'PF' => __( 'French Polynesia', Opt_In::TEXT_DOMAIN ),
					'TF' => __( 'French South Territories', Opt_In::TEXT_DOMAIN ),
					'GA' => __( 'Gabon', Opt_In::TEXT_DOMAIN ),
					'GM' => __( 'Gambia', Opt_In::TEXT_DOMAIN ),
					'GE' => __( 'Georgia', Opt_In::TEXT_DOMAIN ),
					'DE' => __( 'Germany', Opt_In::TEXT_DOMAIN ),
					'GH' => __( 'Ghana', Opt_In::TEXT_DOMAIN ),
					'GI' => __( 'Gibraltar', Opt_In::TEXT_DOMAIN ),
					'GR' => __( 'Greece', Opt_In::TEXT_DOMAIN ),
					'GL' => __( 'Greenland', Opt_In::TEXT_DOMAIN ),
					'GD' => __( 'Grenada', Opt_In::TEXT_DOMAIN ),
					'GP' => __( 'Guadeloupe', Opt_In::TEXT_DOMAIN ),
					'GU' => __( 'Guam', Opt_In::TEXT_DOMAIN ),
					'GT' => __( 'Guatemala', Opt_In::TEXT_DOMAIN ),
					'GN' => __( 'Guinea', Opt_In::TEXT_DOMAIN ),
					'GW' => __( 'Guinea-Bissau', Opt_In::TEXT_DOMAIN ),
					'GY' => __( 'Guyana', Opt_In::TEXT_DOMAIN ),
					'HT' => __( 'Haiti', Opt_In::TEXT_DOMAIN ),
					'HM' => __( 'Heard Island And Mcdonald Island', Opt_In::TEXT_DOMAIN ),
					'HN' => __( 'Honduras', Opt_In::TEXT_DOMAIN ),
					'HK' => __( 'Hong Kong', Opt_In::TEXT_DOMAIN ),
					'HU' => __( 'Hungary', Opt_In::TEXT_DOMAIN ),
					'IS' => __( 'Iceland', Opt_In::TEXT_DOMAIN ),
					'IN' => __( 'India', Opt_In::TEXT_DOMAIN ),
					'ID' => __( 'Indonesia', Opt_In::TEXT_DOMAIN ),
					'IR' => __( 'Iran', Opt_In::TEXT_DOMAIN ),
					'IQ' => __( 'Iraq', Opt_In::TEXT_DOMAIN ),
					'IE' => __( 'Ireland', Opt_In::TEXT_DOMAIN ),
					'IL' => __( 'Israel', Opt_In::TEXT_DOMAIN ),
					'IT' => __( 'Italy', Opt_In::TEXT_DOMAIN ),
					'JM' => __( 'Jamaica', Opt_In::TEXT_DOMAIN ),
					'JP' => __( 'Japan', Opt_In::TEXT_DOMAIN ),
					'JT' => __( 'Johnston Island', Opt_In::TEXT_DOMAIN ),
					'JO' => __( 'Jordan', Opt_In::TEXT_DOMAIN ),
					'KZ' => __( 'Kazakhstan', Opt_In::TEXT_DOMAIN ),
					'KE' => __( 'Kenya', Opt_In::TEXT_DOMAIN ),
					'KI' => __( 'Kiribati', Opt_In::TEXT_DOMAIN ),
					'KP' => __( 'Korea, Democratic Peoples Republic', Opt_In::TEXT_DOMAIN ),
					'KR' => __( 'Korea, Republic of', Opt_In::TEXT_DOMAIN ),
					'KW' => __( 'Kuwait', Opt_In::TEXT_DOMAIN ),
					'KG' => __( 'Kyrgyzstan', Opt_In::TEXT_DOMAIN ),
					'LA' => __( 'Lao People\'s Democratic Republic', Opt_In::TEXT_DOMAIN ),
					'LV' => __( 'Latvia', Opt_In::TEXT_DOMAIN ),
					'LB' => __( 'Lebanon', Opt_In::TEXT_DOMAIN ),
					'LS' => __( 'Lesotho', Opt_In::TEXT_DOMAIN ),
					'LR' => __( 'Liberia', Opt_In::TEXT_DOMAIN ),
					'LY' => __( 'Libyan Arab Jamahiriya', Opt_In::TEXT_DOMAIN ),
					'LI' => __( 'Liechtenstein', Opt_In::TEXT_DOMAIN ),
					'LT' => __( 'Lithuania', Opt_In::TEXT_DOMAIN ),
					'LU' => __( 'Luxembourg', Opt_In::TEXT_DOMAIN ),
					'MO' => __( 'Macau', Opt_In::TEXT_DOMAIN ),
					'MK' => __( 'Macedonia', Opt_In::TEXT_DOMAIN ),
					'MG' => __( 'Madagascar', Opt_In::TEXT_DOMAIN ),
					'MW' => __( 'Malawi', Opt_In::TEXT_DOMAIN ),
					'MY' => __( 'Malaysia', Opt_In::TEXT_DOMAIN ),
					'MV' => __( 'Maldives', Opt_In::TEXT_DOMAIN ),
					'ML' => __( 'Mali', Opt_In::TEXT_DOMAIN ),
					'MT' => __( 'Malta', Opt_In::TEXT_DOMAIN ),
					'MH' => __( 'Marshall Islands', Opt_In::TEXT_DOMAIN ),
					'MQ' => __( 'Martinique', Opt_In::TEXT_DOMAIN ),
					'MR' => __( 'Mauritania', Opt_In::TEXT_DOMAIN ),
					'MU' => __( 'Mauritius', Opt_In::TEXT_DOMAIN ),
					'YT' => __( 'Mayotte', Opt_In::TEXT_DOMAIN ),
					'MX' => __( 'Mexico', Opt_In::TEXT_DOMAIN ),
					'FM' => __( 'Micronesia', Opt_In::TEXT_DOMAIN ),
					'MD' => __( 'Moldavia', Opt_In::TEXT_DOMAIN ),
					'MC' => __( 'Monaco', Opt_In::TEXT_DOMAIN ),
					'MN' => __( 'Mongolia', Opt_In::TEXT_DOMAIN ),
					'MS' => __( 'Montserrat', Opt_In::TEXT_DOMAIN ),
					'MA' => __( 'Morocco', Opt_In::TEXT_DOMAIN ),
					'MZ' => __( 'Mozambique', Opt_In::TEXT_DOMAIN ),
					'MM' => __( 'Union Of Myanmar', Opt_In::TEXT_DOMAIN ),
					'NA' => __( 'Namibia', Opt_In::TEXT_DOMAIN ),
					'NR' => __( 'Nauru Island', Opt_In::TEXT_DOMAIN ),
					'NP' => __( 'Nepal', Opt_In::TEXT_DOMAIN ),
					'NL' => __( 'Netherlands', Opt_In::TEXT_DOMAIN ),
					'AN' => __( 'Netherlands Antilles', Opt_In::TEXT_DOMAIN ),
					'NC' => __( 'New Caledonia', Opt_In::TEXT_DOMAIN ),
					'NZ' => __( 'New Zealand', Opt_In::TEXT_DOMAIN ),
					'NI' => __( 'Nicaragua', Opt_In::TEXT_DOMAIN ),
					'NE' => __( 'Niger', Opt_In::TEXT_DOMAIN ),
					'NG' => __( 'Nigeria', Opt_In::TEXT_DOMAIN ),
					'NU' => __( 'Niue', Opt_In::TEXT_DOMAIN ),
					'NF' => __( 'Norfolk Island', Opt_In::TEXT_DOMAIN ),
					'MP' => __( 'Mariana Islands, Northern', Opt_In::TEXT_DOMAIN ),
					'NO' => __( 'Norway', Opt_In::TEXT_DOMAIN ),
					'OM' => __( 'Oman', Opt_In::TEXT_DOMAIN ),
					'PK' => __( 'Pakistan', Opt_In::TEXT_DOMAIN ),
					'PW' => __( 'Palau Islands', Opt_In::TEXT_DOMAIN ),
					'PS' => __( 'Palestine', Opt_In::TEXT_DOMAIN ),
					'PA' => __( 'Panama', Opt_In::TEXT_DOMAIN ),
					'PG' => __( 'Papua New Guinea', Opt_In::TEXT_DOMAIN ),
					'PY' => __( 'Paraguay', Opt_In::TEXT_DOMAIN ),
					'PE' => __( 'Peru', Opt_In::TEXT_DOMAIN ),
					'PH' => __( 'Philippines', Opt_In::TEXT_DOMAIN ),
					'PN' => __( 'Pitcairn', Opt_In::TEXT_DOMAIN ),
					'PL' => __( 'Poland', Opt_In::TEXT_DOMAIN ),
					'PT' => __( 'Portugal', Opt_In::TEXT_DOMAIN ),
					'PR' => __( 'Puerto Rico', Opt_In::TEXT_DOMAIN ),
					'QA' => __( 'Qatar', Opt_In::TEXT_DOMAIN ),
					'RE' => __( 'Reunion Island', Opt_In::TEXT_DOMAIN ),
					'RO' => __( 'Romania', Opt_In::TEXT_DOMAIN ),
					'RU' => __( 'Russian Federation', Opt_In::TEXT_DOMAIN ),
					'RW' => __( 'Rwanda', Opt_In::TEXT_DOMAIN ),
					'WS' => __( 'Samoa', Opt_In::TEXT_DOMAIN ),
					'SH' => __( 'St Helena', Opt_In::TEXT_DOMAIN ),
					'KN' => __( 'St Kitts & Nevis', Opt_In::TEXT_DOMAIN ),
					'LC' => __( 'St Lucia', Opt_In::TEXT_DOMAIN ),
					'PM' => __( 'St Pierre & Miquelon', Opt_In::TEXT_DOMAIN ),
					'VC' => __( 'St Vincent', Opt_In::TEXT_DOMAIN ),
					'SM' => __( 'San Marino', Opt_In::TEXT_DOMAIN ),
					'ST' => __( 'Sao Tome & Principe', Opt_In::TEXT_DOMAIN ),
					'SA' => __( 'Saudi Arabia', Opt_In::TEXT_DOMAIN ),
					'SN' => __( 'Senegal', Opt_In::TEXT_DOMAIN ),
					'SC' => __( 'Seychelles', Opt_In::TEXT_DOMAIN ),
					'SL' => __( 'Sierra Leone', Opt_In::TEXT_DOMAIN ),
					'SG' => __( 'Singapore', Opt_In::TEXT_DOMAIN ),
					'SK' => __( 'Slovakia', Opt_In::TEXT_DOMAIN ),
					'SI' => __( 'Slovenia', Opt_In::TEXT_DOMAIN ),
					'SB' => __( 'Solomon Islands', Opt_In::TEXT_DOMAIN ),
					'SO' => __( 'Somalia', Opt_In::TEXT_DOMAIN ),
					'ZA' => __( 'South Africa', Opt_In::TEXT_DOMAIN ),
					'GS' => __( 'South Georgia and South Sandwich', Opt_In::TEXT_DOMAIN ),
					'ES' => __( 'Spain', Opt_In::TEXT_DOMAIN ),
					'LK' => __( 'Sri Lanka', Opt_In::TEXT_DOMAIN ),
					'XX' => __( 'Stateless Persons', Opt_In::TEXT_DOMAIN ),
					'SD' => __( 'Sudan', Opt_In::TEXT_DOMAIN ),
					'SR' => __( 'Suriname', Opt_In::TEXT_DOMAIN ),
					'SJ' => __( 'Svalbard and Jan Mayen', Opt_In::TEXT_DOMAIN ),
					'SZ' => __( 'Swaziland', Opt_In::TEXT_DOMAIN ),
					'SE' => __( 'Sweden', Opt_In::TEXT_DOMAIN ),
					'CH' => __( 'Switzerland', Opt_In::TEXT_DOMAIN ),
					'SY' => __( 'Syrian Arab Republic', Opt_In::TEXT_DOMAIN ),
					'TW' => __( 'Taiwan, Republic of China', Opt_In::TEXT_DOMAIN ),
					'TJ' => __( 'Tajikistan', Opt_In::TEXT_DOMAIN ),
					'TZ' => __( 'Tanzania', Opt_In::TEXT_DOMAIN ),
					'TH' => __( 'Thailand', Opt_In::TEXT_DOMAIN ),
					'TL' => __( 'Timor Leste', Opt_In::TEXT_DOMAIN ),
					'TG' => __( 'Togo', Opt_In::TEXT_DOMAIN ),
					'TK' => __( 'Tokelau', Opt_In::TEXT_DOMAIN ),
					'TO' => __( 'Tonga', Opt_In::TEXT_DOMAIN ),
					'TT' => __( 'Trinidad & Tobago', Opt_In::TEXT_DOMAIN ),
					'TN' => __( 'Tunisia', Opt_In::TEXT_DOMAIN ),
					'TR' => __( 'Turkey', Opt_In::TEXT_DOMAIN ),
					'TM' => __( 'Turkmenistan', Opt_In::TEXT_DOMAIN ),
					'TC' => __( 'Turks And Caicos Islands', Opt_In::TEXT_DOMAIN ),
					'TV' => __( 'Tuvalu', Opt_In::TEXT_DOMAIN ),
					'UG' => __( 'Uganda', Opt_In::TEXT_DOMAIN ),
					'UA' => __( 'Ukraine', Opt_In::TEXT_DOMAIN ),
					'AE' => __( 'United Arab Emirates', Opt_In::TEXT_DOMAIN ),
					'GB' => __( 'United Kingdom', Opt_In::TEXT_DOMAIN ),
					'UM' => __( 'US Minor Outlying Islands', Opt_In::TEXT_DOMAIN ),
					'US' => __( 'USA', Opt_In::TEXT_DOMAIN ),
					'HV' => __( 'Upper Volta', Opt_In::TEXT_DOMAIN ),
					'UY' => __( 'Uruguay', Opt_In::TEXT_DOMAIN ),
					'UZ' => __( 'Uzbekistan', Opt_In::TEXT_DOMAIN ),
					'VU' => __( 'Vanuatu', Opt_In::TEXT_DOMAIN ),
					'VA' => __( 'Vatican City State', Opt_In::TEXT_DOMAIN ),
					'VE' => __( 'Venezuela', Opt_In::TEXT_DOMAIN ),
					'VN' => __( 'Vietnam', Opt_In::TEXT_DOMAIN ),
					'VG' => __( 'Virgin Islands (British)', Opt_In::TEXT_DOMAIN ),
					'VI' => __( 'Virgin Islands (US)', Opt_In::TEXT_DOMAIN ),
					'WF' => __( 'Wallis And Futuna Islands', Opt_In::TEXT_DOMAIN ),
					'EH' => __( 'Western Sahara', Opt_In::TEXT_DOMAIN ),
					'YE' => __( 'Yemen Arab Rep.', Opt_In::TEXT_DOMAIN ),
					'YD' => __( 'Yemen Democratic', Opt_In::TEXT_DOMAIN ),
					'YU' => __( 'Yugoslavia', Opt_In::TEXT_DOMAIN ),
					'ZR' => __( 'Zaire', Opt_In::TEXT_DOMAIN ),
					'ZM' => __( 'Zambia', Opt_In::TEXT_DOMAIN ),
					'ZW' => __( 'Zimbabwe', Opt_In::TEXT_DOMAIN )
				));


		}
	}
}