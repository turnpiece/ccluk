<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Custom_Form_Admin
 *
 * @property Forminator_Custom_Form module
 * @since 1.0
 */
class Forminator_Custom_Form_Admin extends Forminator_Admin_Module {

	/**
	 * Init module admin
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module       = Forminator_Custom_Form::get_instance();
		$this->page         = 'forminator-cform';
		$this->page_edit    = 'forminator-cform-wizard';
		$this->page_entries = 'forminator-cform-view';
	}

	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/admin-page-new.php';
		include_once dirname( __FILE__ ) . '/admin-page-view.php';
		include_once dirname( __FILE__ ) . '/admin-page-entries.php';
		include_once dirname( __FILE__ ) . '/admin-renderer-entries.php';
	}

	/**
	 * Add module pages to Admin
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {
		new Forminator_CForm_Page( $this->page, 'custom-form/list', __( 'Forms', Forminator::DOMAIN ), __( 'Forms', Forminator::DOMAIN ), 'forminator' );
		new Forminator_CForm_New_Page( $this->page_edit, 'custom-form/wizard', __( 'Edit Form', Forminator::DOMAIN ), __( 'New Custom Form', Forminator::DOMAIN ), 'forminator' );
		new Forminator_CForm_View_Page( $this->page_entries, 'custom-form/entries', __( 'Submissions:', Forminator::DOMAIN ), __( 'View Custom Form', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Remove necessary pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {
		remove_submenu_page( 'forminator', $this->page_edit );
		remove_submenu_page( 'forminator', $this->page_entries );
	}

	/**
	 * Pass module defaults to JS
	 *
	 * @since 1.0
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_js_defaults( $data ) {
		$model = null;
		if ( $this->is_admin_wizard() ) {
			$data['application'] = 'builder';

			if ( ! self::is_edit() ) {
				// Load settings from template
				$template = $this->get_template();
				$name     = '';
				if( isset( $_GET['name'] ) ) { // WPCS: CSRF ok.
					$name = sanitize_text_field( $_GET['name'] );
				}

				if ( $template ) {
					$data['currentForm'] = array_merge( array(
						'wrappers' => $template->fields,
						'formName' => $name
					), $template->settings );
				} else {
					$data['currentForm'] = array(
						'fields'   => array(),
						'formName' => $name
					);
				}
			} else {
				$id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; // WPCS: CSRF ok.
				if ( ! is_null( $id ) ) {
					$model = Forminator_Custom_Form_Model::model()->load( $id );
				}

				$wrappers = array();
				if ( is_object( $model ) ) {
					$wrappers = $model->getFieldsGrouped();
				}

				// Load stored record
				$settings = apply_filters( 'forminator_form_settings', $this->get_form_settings( $model ), $model, $data, $this );
				$data['currentForm'] = array_merge( array(
					'wrappers' => $wrappers,
					'formName' => $model->name,
					'formID'   => $model->id
				), $settings );
			}
		}

		$data['modules']['custom_form'] = array(
			'templates'     => $this->module->get_templates(),
			'new_form_url'  => menu_page_url( $this->page_edit, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_cforms' )
		);

		return apply_filters( 'forminator_form_admin_data', $data, $model, $this );
	}

	/**
	 * Localize module
	 *
	 * @since 1.0
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {
		$data['custom_form'] = array(
			'popup_label' => __( 'Choose Form Type', Forminator::DOMAIN ),
		);

		$data['builder'] = array(
			"builder_edit_title"    => __( "Edit Form", Forminator::DOMAIN ),
			"builder_new_title"    => __( "New Form", Forminator::DOMAIN ),
			"cancel"           => __( "Cancel", Forminator::DOMAIN ),
			"save"             => __( "Save", Forminator::DOMAIN ),
			"continue"         => __( "Continue", Forminator::DOMAIN ),
			"form_builder"     => __( "Form Builder", Forminator::DOMAIN ),
			"finish"           => __( "Finish", Forminator::DOMAIN ),
			"publish"          => __( "Publish", Forminator::DOMAIN ),
			"form_settings"    => __( "Form Settings", Forminator::DOMAIN ),
			"form_name"        => __( "Enter form name", Forminator::DOMAIN ),
			"next"             => __( "Next", Forminator::DOMAIN ),
			"click_drag_label" => __( "Click & Drag fields", Forminator::DOMAIN ),
			"standard"         => __( "Standard", Forminator::DOMAIN ),
			"posts"            => __( "Posts", Forminator::DOMAIN ),
			"pricing"          => __( "Pricing", Forminator::DOMAIN ),
			"clone"            => __( "Clone", Forminator::DOMAIN ),
			"general"          => __( "General", Forminator::DOMAIN ),
			"advanced"         => __( "Advanced", Forminator::DOMAIN ),
			"required_filed"   => __( "Required field: %s", Forminator::DOMAIN ),
			"no_conditions"    => __( "You have not yet created any conditions.", Forminator::DOMAIN ),
			"no_fields"        => __( "Drag and drop fields from the sidebar to add them to your form.", Forminator::DOMAIN ),
			"use_custom_class" => __( "Custom class", Forminator::DOMAIN ),
			"custom_class"     => __( "Custom class", Forminator::DOMAIN ),


			"build_your_form"					=> __( "Build your form", Forminator::DOMAIN ),

			// Form name
			"form_name"							=> __( "Form name", Forminator::DOMAIN ),
			"form_name_field"					=> __( "Name your form *", Forminator::DOMAIN ),
			"form_name_description"				=> __( "Pick a name for your form.", Forminator::DOMAIN ),
			"form_name_field_validation"		=> __( "This field cannot be empty! Please pick a name for your form.", Forminator::DOMAIN ),
			"form_name_field_description"		=> __( "This name will help you to identify your form and will not be displayed on your site.", Forminator::DOMAIN ),

			// Buttons
			"add_new_field"						=> __( "Click to add new field", Forminator::DOMAIN ),

			// Sidebar
			"back"								=> __( "Back", Forminator::DOMAIN ),
			"delete"							=> __( "Delete field", Forminator::DOMAIN ),
		);

		$data['conditions'] = array(
			"conditional_logic" => __( "Conditional logic", Forminator::DOMAIN ),
			"setup_conditions"  => __( "Set Up Conditions", Forminator::DOMAIN ),
			"edit_conditions"   => __( "Edit Conditions", Forminator::DOMAIN ),
			"show"              => __( "Show", Forminator::DOMAIN ),
			"hide"              => __( "Hide", Forminator::DOMAIN ),
			"show_field_if"     => __( "this field if", Forminator::DOMAIN ),
			"any"               => __( "Any", Forminator::DOMAIN ),
			"is"                => __( "is", Forminator::DOMAIN ),
			"is_not"            => __( "is not", Forminator::DOMAIN ),
			"is_great"          => __( "is greater than", Forminator::DOMAIN ),
			"is_less"           => __( "is less than", Forminator::DOMAIN ),
			"contains"          => __( "contains", Forminator::DOMAIN ),
			"starts"            => __( "starts with", Forminator::DOMAIN ),
			"ends"              => __( "ends with", Forminator::DOMAIN ),
			"following_match"   => __( "of the following match", Forminator::DOMAIN ),
			"add_condition"     => __( "Add a condition", Forminator::DOMAIN ),
			"done"              => __( "Done", Forminator::DOMAIN ),
			"all"               => __( "All", Forminator::DOMAIN ),
			"select_option"     => __( "Select option...", Forminator::DOMAIN ),
			"more_condition"    => __( "more condition", Forminator::DOMAIN ),
			"more_conditions"   => __( "more conditions", Forminator::DOMAIN ),
			"delete_condition"	=> __( "Delete condition", Forminator::DOMAIN ),
		);

		$data['product'] = array(
			"add_variations"   => __( "Add some variations of your product.", Forminator::DOMAIN ),
			"use_list"         => __( "Display in list?", Forminator::DOMAIN ),
			"add_variation"    => __( "Add Variation", Forminator::DOMAIN ),
			"image"            => __( "Image", Forminator::DOMAIN ),
			"name"             => __( "Name", Forminator::DOMAIN ),
			"price"            => __( "Price", Forminator::DOMAIN ),
		);

		$data['name'] = array(
			"prefix_label" => __( "Prefix", Forminator::DOMAIN ),
			"fname_label"  => __( "First Name", Forminator::DOMAIN ),
			"mname_label"  => __( "Middle Name", Forminator::DOMAIN ),
			"lname_label"  => __( "Last Name", Forminator::DOMAIN ),
		);

		$data['address'] = array(
			"street_address_label" => __( 'Street address', Forminator::DOMAIN ),
			"address_line_label"   => __( 'Address Line 2', Forminator::DOMAIN ),
			"address_city_label"   => __( 'City', Forminator::DOMAIN ),
			"address_state_label"  => __( 'State/Province', Forminator::DOMAIN ),
			"address_zip_label"    => __( 'ZIP / Postal Code', Forminator::DOMAIN ),
		);

		$data['time'] = array(
			"hh_label" => __( 'Hours', Forminator::DOMAIN ),
			"mm_label" => __( 'Minutes', Forminator::DOMAIN ),
		);

		$data['appearance'] = array(
			"settings_title"              => __( "SETTINGS & BEHAVIOURS", Forminator::DOMAIN ),
			"preview_form"                => __( "Preview Form", Forminator::DOMAIN ),
			"appearance"                  => __( "Appearance", Forminator::DOMAIN ),
			"asterisk"                	  => __( "Asterisk", Forminator::DOMAIN ),
			"form_behaviour"              => __( "Form behaviour", Forminator::DOMAIN ),
			"form_emails"                 => __( "Form emails", Forminator::DOMAIN ),
			"form_pagination"             => __( "Pagination", Forminator::DOMAIN ),
			"advanced"                    => __( "Advanced", Forminator::DOMAIN ),
			"filled_elements"             => __( "Filled elements", Forminator::DOMAIN ),
			"extras"                	  => __( "Extras", Forminator::DOMAIN ),
			"error_elements"              => __( "Error elements", Forminator::DOMAIN ),
			"design_colors"	              => __( "Choose a theme", Forminator::DOMAIN ),
			"design_colors_desc"          => __( "Choose a pre-made style for your form and further customize it's appearance.", Forminator::DOMAIN ),
			"custom"                      => __( "Custom design", Forminator::DOMAIN ),
			"customize_typography"        => __( "Customize typography", Forminator::DOMAIN ),
			"custom_font_family"          => __( "Enter custom font family name", Forminator::DOMAIN ),
			"custom_font_placeholder"     => __( "E.g. 'Arial', sans-serif", Forminator::DOMAIN ),
			"custom_font_description"     => __( "Type the font family name, as you would in CSS", Forminator::DOMAIN ),
			"font_family"                 => __( "Font family", Forminator::DOMAIN ),
			"font_size"                   => __( "Font size", Forminator::DOMAIN ),
			"font_weight"                 => __( "Font weight", Forminator::DOMAIN ),
			"css_selectors"               => __( "Available CSS Selectors (click to add)", Forminator::DOMAIN ),
			"after_form_submit"           => __( "After form submit", Forminator::DOMAIN ),
			"submission"                  => __( "Submission", Forminator::DOMAIN ),
			"validation"                  => __( "Validation", Forminator::DOMAIN ),
			"validation_method"           => __( "Validation Method", Forminator::DOMAIN ),
			"security"                    => __( "Security", Forminator::DOMAIN ),
			"privacy"                     => __( "Privacy", Forminator::DOMAIN ),
			"privacy_desc"                => __( "When its enabled, privacy settings here will be used instead of default privacy settings.", Forminator::DOMAIN ),
			"form_doesnt_expire"          => __( "Form does not expire", Forminator::DOMAIN ),
			"on_certain_date"             => __( "Expires on certain date", Forminator::DOMAIN ),
			"expires_submits"             => __( "Expires after x-submits", Forminator::DOMAIN ),
			"show_thank_you_message"      => __( "Show a Thank you message", Forminator::DOMAIN ),
			"redirect_to_url"             => __( "Re-direct user to URL", Forminator::DOMAIN ),
			"server_only"                 => __( "Server only", Forminator::DOMAIN ),
			"form_submit"                 => __( "On form submit", Forminator::DOMAIN ),
			"inline"                      => __( "Enable inline validation (as user types)", Forminator::DOMAIN ),
			"enable_honeypot"             => __( "Enable honeypot protection", Forminator::DOMAIN ),
			"enable_privacy"              => __( "Enable privacy", Forminator::DOMAIN ),
			"only_logged"                 => __( "Only logged-in users can submit", Forminator::DOMAIN ),
			"select_font"                 => __( "Select font", Forminator::DOMAIN ),
			"subtitle"        		      => __( "Subtitle", Forminator::DOMAIN ),
			"title"			              => __( "Title", Forminator::DOMAIN ),
			"custom_font"                 => __( "Custom user font", Forminator::DOMAIN ),
			"label"                       => __( "Label", Forminator::DOMAIN ),
			"border_line"                 => __( "Border line", Forminator::DOMAIN ),
			"value"                       => __( "Value", Forminator::DOMAIN ),
			"description"                 => __( "Description", Forminator::DOMAIN ),
			"admin_email_search_noresult" => __( "No Result Found", Forminator::DOMAIN ),
			"admin_email_searching"       => __( "Searching", Forminator::DOMAIN ),
			"add_form_data"               => __( "Add form based data", Forminator::DOMAIN ),
			"subject"                     => __( "Subject", Forminator::DOMAIN ),
			"generate_pdf"                => __( "Generate PDF using data", Forminator::DOMAIN ),
			"integrations"                => __( "Various service integrations (send to Mailchimp etc)", Forminator::DOMAIN ),
			"static"                      => __( "Static", Forminator::DOMAIN ),
			"hover"                       => __( "Hover", Forminator::DOMAIN ),
			"active"                      => __( "Active", Forminator::DOMAIN ),
			"btn_bg"                      => __( "Button background", Forminator::DOMAIN ),
			"btn_txt"                     => __( "Button text", Forminator::DOMAIN ),
			"links"                       => __( "Results link", Forminator::DOMAIN ),
			"minutes"						=> __( "minute(s)", Forminator::DOMAIN ),
			"hours"							=> __( "hour(s)", Forminator::DOMAIN ),
			"days"							=> __( "day(s)", Forminator::DOMAIN ),
			"weeks"							=> __( "week(s)", Forminator::DOMAIN ),
			"months"						=> __( "month(s)", Forminator::DOMAIN ),
			"years"							=> __( "year(s)", Forminator::DOMAIN ),
			"form_name_main"				=> __( "Form name", Forminator::DOMAIN ),
			"form_name_main_desc"			=> __( "Pick a name for your form module.", Forminator::DOMAIN ),
			"form_name_field_desc"			=> __( "This is for you to be able to identify forms and will not be displayed on your site.", Forminator::DOMAIN ),
			"submit"						=> __( "Submit Button", Forminator::DOMAIN ),
			"background"					=> __( "Background", Forminator::DOMAIN ),
			"pagination_prev"				=> __( "Back", Forminator::DOMAIN ),
			"pagination_next"				=> __( "Next", Forminator::DOMAIN ),
			"inputs"						=> __( "Inputs", Forminator::DOMAIN ),
			"input"							=> __( "Input", Forminator::DOMAIN ),
			"border"						=> __( "Border", Forminator::DOMAIN ),
			"admin_subject"					=> __( "New Form Entry for {form_name}", Forminator::DOMAIN ),
			"admin_message"					=> __( "You have a new website form submission: \n {all_fields} \n This message was sent from {site_url}", Forminator::DOMAIN ),
			"main"							=> __( "Main", Forminator::DOMAIN ),
			"section"						=> __( "Section", Forminator::DOMAIN ),
			"form"							=> __( "Form", Forminator::DOMAIN ),
			"placeholder"					=> __( "Placeholder", Forminator::DOMAIN ),
			"error"							=> __( "Error", Forminator::DOMAIN ),
			"filled"						=> __( "Filled", Forminator::DOMAIN ),
			"input_textarea_typography"		=> __( "Input and textarea typography", Forminator::DOMAIN ),
			"option_color"					=> __( "Option color", Forminator::DOMAIN ),
			"option_bg"						=> __( "Option BG", Forminator::DOMAIN ),
			"search_value"					=> __( "Search | Font color", Forminator::DOMAIN ),
			"search_background"				=> __( "Search | Background", Forminator::DOMAIN ),
			"search_border"					=> __( "Search | Border color", Forminator::DOMAIN ),
			"icon"							=> __( "Icon", Forminator::DOMAIN ),
			"header_bg"						=> __( "Header BG", Forminator::DOMAIN ),
			"content_bg"					=> __( "Content BG", Forminator::DOMAIN ),
			"nav_arrows"					=> __( "Nav arrows", Forminator::DOMAIN ),
			"dweek"							=> __( "Day of week", Forminator::DOMAIN ),
			"days_color"					=> __( "Days color", Forminator::DOMAIN ),
			"days_color_hover"				=> __( "Days color (hover)", Forminator::DOMAIN ),
			"days_color_active"				=> __( "Days color (active)", Forminator::DOMAIN ),
			"days_background"				=> __( "Days background", Forminator::DOMAIN ),
			"days_background_hover"			=> __( "Days background (hover)", Forminator::DOMAIN ),
			"days_background_active"		=> __( "Days background (active)", Forminator::DOMAIN ),
			"radio_checkbox_typography"		=> __( "Radio and checkbox label typography", Forminator::DOMAIN ),
			"check_icon"					=> __( "Check / Icon", Forminator::DOMAIN ),
			"item_color"					=> __( "Item color", Forminator::DOMAIN ),
			"item_bg"						=> __( "Item BG", Forminator::DOMAIN ),
			"item_border"					=> __( "Item border", Forminator::DOMAIN ),
			"open_settings"					=> __( "Open settings", Forminator::DOMAIN ),
			"timeline_text"					=> __( "Text", Forminator::DOMAIN ),
			"timeline_text_current"			=> __( "Text", Forminator::DOMAIN ),
			"timeline_step"					=> __( "Step", Forminator::DOMAIN ),
			"timeline_step_current"			=> __( "Step (Current)", Forminator::DOMAIN ),
			"dot_border"					=> __( "Dot border", Forminator::DOMAIN ),
			"dot_border_current"			=> __( "Dot border (Current)", Forminator::DOMAIN ),
			"dot_background"				=> __( "Dot BG", Forminator::DOMAIN ),
			"dot_background_current"		=> __( "Dot BG (Current)", Forminator::DOMAIN ),
			"timeline_border"				=> __( "Timeline border", Forminator::DOMAIN ),
			"prev_background"				=> __( "Left button | Background", Forminator::DOMAIN ),
			"prev_background_hover"			=> __( "Left button | Background (hover)", Forminator::DOMAIN ),
			"prev_background_active"		=> __( "Left button | Background (active)", Forminator::DOMAIN ),
			"prev_value"					=> __( "Left button | Font color", Forminator::DOMAIN ),
			"prev_value_hover"				=> __( "Left button | Font color (hover)", Forminator::DOMAIN ),
			"prev_value_active"				=> __( "Left button | Font color (active)", Forminator::DOMAIN ),
			"next_background"				=> __( "Right button | Background", Forminator::DOMAIN ),
			"next_background_hover"			=> __( "Right button | Background (hover)", Forminator::DOMAIN ),
			"next_background_active"		=> __( "Right button | Background (active)", Forminator::DOMAIN ),
			"next_value"					=> __( "Right button | Font color", Forminator::DOMAIN ),
			"next_value_hover"				=> __( "Right button | Font color (hover)", Forminator::DOMAIN ),
			"next_value_active"				=> __( "Right button | Font color (active)", Forminator::DOMAIN ),
			"form_padding"					=> __( "Form padding", Forminator::DOMAIN ),
			"form_border"					=> __( "Form border", Forminator::DOMAIN ),
			"none"							=> __( "None", Forminator::DOMAIN ),
			"solid"							=> __( "Solid", Forminator::DOMAIN ),
			"dashed"						=> __( "Dashed", Forminator::DOMAIN ),
			"dotted"						=> __( "Dotted", Forminator::DOMAIN ),
			"integrations_label"            => __( "Integrations", Forminator::DOMAIN ),
			"shadow"						=> __( "Box shadow", Forminator::DOMAIN ),
			"icon_cal"						=> __( "Calendar icon", Forminator::DOMAIN ),
			"error_bg"						=> __( "Error BG", Forminator::DOMAIN ),
			"error_color"					=> __( "Error color", Forminator::DOMAIN ),
			"left_button_title"				=> __( "Left button", Forminator::DOMAIN ),
			"left_button_desc"				=> __( "Here you can customize your left (previous) button that's located at the bottom of the form.", Forminator::DOMAIN ),
			"right_button_title"			=> __( "Right button", Forminator::DOMAIN ),
			"right_button_desc"				=> __( "Here you can customize your right (next and submit) button that's' located at the bottom of the form.", Forminator::DOMAIN ),
			"notifications"					=> __( "Notifications", Forminator::DOMAIN ),
			"select_date"					=> __( "Select date", Forminator::DOMAIN ),
			"behaviour"						=> __( "Behaviour", Forminator::DOMAIN ),

			"customize_colors"					=> __( "Customize colors", Forminator::DOMAIN ),
			"typography_description"			=> __( "Change how the form labels and other elements typography looks like.", Forminator::DOMAIN ),
		);

		$data['tab_appearance'] = array(
			"appearance"						=> __( "Appearance", Forminator::DOMAIN ),

			// Form name
			"form_name"							=> __( "Form name", Forminator::DOMAIN ),
			"form_name_field"					=> __( "Name your form *", Forminator::DOMAIN ),
			"form_name_description"				=> __( "Pick a name for your form.", Forminator::DOMAIN ),
			"form_name_field_validation"		=> __( "This field cannot be empty! Please pick a name for your form.", Forminator::DOMAIN ),
			"form_name_field_description"		=> __( "This name will help you to identify your form and will not be displayed on your site.", Forminator::DOMAIN ),

			// Form design
			"form"								=> __( "Form", Forminator::DOMAIN ),
			"section"							=> __( "Section", Forminator::DOMAIN ),
			"fields_label"						=> __( "Fields label", Forminator::DOMAIN ),
			"form_design"						=> __( "Form design", Forminator::DOMAIN ),
			"forminator_ui"						=> __( "Forminator UI", Forminator::DOMAIN ),
			"upload_button"						=> __( "Upload button", Forminator::DOMAIN ),
			"submit_button"						=> __( "Submit button", Forminator::DOMAIN ),
			"vanilla_theme"						=> __( "Vanilla Theme", Forminator::DOMAIN ),
			"forminator_bold"					=> __( "Forminator Bold", Forminator::DOMAIN ),
			"forminator_flat"					=> __( "Forminator Flat", Forminator::DOMAIN ),
			"material_design"					=> __( "Material Design", Forminator::DOMAIN ),
			"vanilla_message"					=> __( "Vanilla Theme will provide you a clean design (with no styles) and simple markup.", Forminator::DOMAIN ),
			"form_design_field"					=> __( "Choose a theme", Forminator::DOMAIN ),
			"form_design_description"			=> __( "Assign a theme to your form and customize its appearance.", Forminator::DOMAIN ),

			// Typography
			"typography"						=> __( "Typography", Forminator::DOMAIN ),
			"typography_description"			=> __( "Here you can edit form elements font family, size, and weight.", Forminator::DOMAIN ),

			// Typography – Settings
			"select"							=> __( "Select", Forminator::DOMAIN ),
			"buttons"							=> __( "Buttons", Forminator::DOMAIN ),
			"field_label"						=> __( "Field Label", Forminator::DOMAIN ),
			"multi_select"						=> __( "Multi Select", Forminator::DOMAIN ),
			"date_calendar"						=> __( "Date Calendar", Forminator::DOMAIN ),
			"section_title"						=> __( "Section Title", Forminator::DOMAIN ),
			"input_textarea"					=> __( "Input & Textarea", Forminator::DOMAIN ),
			"radio_checkbox"					=> __( "Radio & Checkbox", Forminator::DOMAIN ),
			"select_dropdown"					=> __( "Select Dropdown", Forminator::DOMAIN ),
			"section_subtitle"					=> __( "Section Subtitle", Forminator::DOMAIN ),
			"pagination_footer"					=> __( "Pagination Footer", Forminator::DOMAIN ),
			"pagination_timeline"				=> __( "Pagination Timeline", Forminator::DOMAIN ),

			// Form style
			"form_style"						=> __( "Form style", Forminator::DOMAIN ),
			"form_style_description"			=> __( "Further customize the appearance of the form main container", Forminator::DOMAIN ),

			// Fields style
			"open_fields"						=> __( "Open fields", Forminator::DOMAIN ),
			"fields_style"						=> __( "Fields style", Forminator::DOMAIN ),
			"enclosed_fields"					=> __( "Enclosed fields", Forminator::DOMAIN ),
			"fields_style_field"				=> __( "Edit separation", Forminator::DOMAIN ),
			"fields_style_description"			=> __( "This will help you edit separation between fields.", Forminator::DOMAIN ),
			"fields_style_field_description"	=> __( 'Choose "open" to increase or "enclosed" to decrease fields separation.', Forminator::DOMAIN ),

			// Custom text
			"custom_text"						=> __( "Custom text", Forminator::DOMAIN ),
			"submit_custom_text"				=> __( "Use custom submit button text", Forminator::DOMAIN ),
			"custom_text_description"			=> __( "To customize content of some available form elements.", Forminator::DOMAIN ),
			"invalid_form_custom_text"			=> __( "Use custom invalid form message", Forminator::DOMAIN ),
			"invalid_form_placeholder"			=> __( "Enter your invalid form message", Forminator::DOMAIN ),
			"submit_custom_text_placeholder"	=> __( "Enter your submit button text", Forminator::DOMAIN ),

			// Custom CSS
			"custom_css"						=> __( "Custom CSS", Forminator::DOMAIN ),
			"enable_custom_css"					=> __( "Enable custom CSS for this module", Forminator::DOMAIN ),
			"custom_css_description"			=> __( "For more advanced customization options use custom CSS.", Forminator::DOMAIN ),
		);

		$data['tab_pagination'] = array(
			"header"						=> __( "Header", Forminator::DOMAIN ),
			"footer"						=> __( "Footer", Forminator::DOMAIN ),
			"no_header"						=> __( "No header", Forminator::DOMAIN ),
			"pagination"					=> __( "Pagination", Forminator::DOMAIN ),
			"progress_bar"					=> __( "Progress bar", Forminator::DOMAIN ),
			"last_step_label"				=> __( "Label for last step", Forminator::DOMAIN ),
			"edit_left_button"				=> __( "Edit left button text", Forminator::DOMAIN ),
			"navigation_steps"				=> __( "Navigation steps", Forminator::DOMAIN ),
			"notice_no_header"				=> __( "When this option is selected no header with pagination will show up on your form.", Forminator::DOMAIN ),
			"edit_right_button"				=> __( "Edit right button text", Forminator::DOMAIN ),
			"footer_description"			=> __( "Customize pagination footer buttons text.", Forminator::DOMAIN ),
			"header_description"			=> __( "Choose a design for pagination header, or do not show it at all.", Forminator::DOMAIN ),
			"notice_progress_bar"			=> __( "When this option is selected you will see a progress bar going from 0% on first page to 100% on last page.", Forminator::DOMAIN ),
			"last_step_placeholder"			=> __( "Finish", Forminator::DOMAIN ),
			"left_button_placeholder"		=> __( "« Back", Forminator::DOMAIN ),
			"right_button_placeholder"		=> __( "Next »", Forminator::DOMAIN ),
		);

		$data['tab_behaviour'] = array(
			"behaviour"								=> __( "Behaviour", Forminator::DOMAIN ),

			// Form lifespan
			"form_lifespan"							=> __( "Form lifespan", Forminator::DOMAIN ),
			"form_expires_date"						=> __( "Expires on certain date", Forminator::DOMAIN ),
			"form_doesnt_expire"					=> __( "Form does not expire", Forminator::DOMAIN ),
			"form_expires_submits"					=> __( "Expires after x-submits", Forminator::DOMAIN ),
			"form_lifespan_description"				=> __( "Choose when your form will stop appearing for users.", Forminator::DOMAIN ),

			// Autofill
			"autofill"								=> __( "Autofill", Forminator::DOMAIN ),
			"enable_autofill"						=> __( "Enable autofill", Forminator::DOMAIN ),
			"autofill_description"					=> __( "Choose which field should be autofilled.", Forminator::DOMAIN ),

			// Submission behaviour
			"redirect_to_url"						=> __( "Re-direct user to URL", Forminator::DOMAIN ),
			"submission_behaviour"					=> __( "Submission behaviour", Forminator::DOMAIN ),
			"show_thank_you_message"				=> __( "Show thank you message", Forminator::DOMAIN ),
			"redirect_to_url_placeholder"			=> __( "E.g. /thank-you", Forminator::DOMAIN ),
			"submission_behaviour_description"		=> __( "Choose what you want to happen after your visitor has successfully submitted their form.", Forminator::DOMAIN ),

			// Database storage
			"disable_storage"						=> __( "Disable storage", Forminator::DOMAIN ),
			"database_storage"						=> __( "Database storage", Forminator::DOMAIN ),
			"disable_storage_description"			=> __( "Prevent your form from storing entries in Database.", Forminator::DOMAIN ),

			// Submission method
			"enable_ajax"							=> __( "Enable AJAX", Forminator::DOMAIN ),
			"ajax_description"						=> __( "Turn on this setting to prevent page refresh while submitting a form.", Forminator::DOMAIN ),
			"submission_method"						=> __( "Submission method", Forminator::DOMAIN ),

			// Validation method
			"form_submit"							=> __( "On form submit", Forminator::DOMAIN ),
			"server_only"							=> __( "Server only", Forminator::DOMAIN ),
			"enable_honeypot"						=> __( "Enable honeypot protection", Forminator::DOMAIN ),
			"validation_method"						=> __( "Validation method", Forminator::DOMAIN ),
			"only_logged_users"						=> __( "Only logged-in users can submit", Forminator::DOMAIN ),
			"enable_inline_validation"				=> __( "Enable inline validation (as user types)", Forminator::DOMAIN ),

			// Security
			"security"								=> __( "Security", Forminator::DOMAIN ),

			// Privacy
			"privacy"                               => __( "Privacy", Forminator::DOMAIN ),
			"privacy_description"                   => __( "When its enabled, privacy settings here will be used instead of default privacy settings.", Forminator::DOMAIN ),
			"enable_submissions_retention"	        => __( "Enable submissions retention", Forminator::DOMAIN ),
		);

		$data['tab_emails'] = array(
			"emails"						=> __( "Emails", Forminator::DOMAIN ),
			"to_user"						=> __( "To user", Forminator::DOMAIN ),
			"subject"						=> __( "Subject", Forminator::DOMAIN ),
			"to_admins"						=> __( "To admin(s)", Forminator::DOMAIN ),
			"hello_user"					=> __( "Hello user, thank you for filling this form.", Forminator::DOMAIN ),
			"recipients"					=> __( "Recipient(s)", Forminator::DOMAIN ),
			"hello_admin"					=> __( "Hello admin, someone filled out your form.", Forminator::DOMAIN ),
			"email_subject"					=> __( "Email subject", Forminator::DOMAIN ),
			"send_user_email"				=> __( "Send email to user", Forminator::DOMAIN ),
			"send_admins_email"				=> __( "Send email to admin(s)", Forminator::DOMAIN ),
			"choose_recipients"				=> __( "Add email recipient(s)", Forminator::DOMAIN ),
		);

		$data['tab_integrations'] = array(
			"api"							=> __( "API", Forminator::DOMAIN ),
			"pro"							=> __( "PRO", Forminator::DOMAIN ),
			"api_message"					=> __( "This is currently in development and will be made available to Pro members soon.", Forminator::DOMAIN ),
			"applications"					=> __( "Applications", Forminator::DOMAIN ),
			"integrations"					=> __( "Integrations", Forminator::DOMAIN ),
			"api_description"				=> __( "Connect Forminator to your custom built apps using our full featured API", Forminator::DOMAIN ),
			"applications_empty"			=> __( "There are no active applications.", Forminator::DOMAIN ),
			"applications_description"		=> __( "Connect your third-party app accounts and send data to your favourite apps.", Forminator::DOMAIN ),
		);

		$data['css_tags'] = array(
			"form"							=> __( "Form", Forminator::DOMAIN ),
			"select"						=> __( "Select", Forminator::DOMAIN ),
			"textarea"						=> __( "Textarea", Forminator::DOMAIN ),
			"main_label"					=> __( "Main label", Forminator::DOMAIN ),
			"text_input"					=> __( "Text input", Forminator::DOMAIN ),
			"helper_label"					=> __( "Helper label", Forminator::DOMAIN ),
		);

		$data['autofill'] = array(
			'form_field'                   => __( 'Form Field', Forminator::DOMAIN ),
			'source'                       => __( 'Autofill Source', Forminator::DOMAIN ),
			'editable'                     => __( 'Editable', Forminator::DOMAIN ),
			'no_autofill_fields_available' => __( 'Form does not have fields that can be autofilled', Forminator::DOMAIN ),
			'disable_autofill'             => __( 'Disable Autofill', Forminator::DOMAIN ),
			'editable_yes'                 => __( 'Yes', Forminator::DOMAIN ),
			'editable_no'                  => __( 'No', Forminator::DOMAIN ),
			'autofill_feature'             => __( 'Autofill', Forminator::DOMAIN ),
			'autofill_desc'                => __( 'Choose which field should be autofilled', Forminator::DOMAIN ),
		);

		return $data;
	}

	/**
	 * Return template
	 *
	 * @since 1.0
	 * @return Forminator_Template|false
	 */
	private function get_template() {
		//$id = $_GET['template']; // TODO: if enabled use sanitize and trim
		$id = 'contact_form';

		foreach ( $this->module->templates as $key => $template ) {
			if ( $template->options['id'] === $id ) {
				return $template;
			}
		}

		return false;
	}

	/**
	 * Return Form Settins
	 *
	 * @since 1.1
	 * @param Forminator_Custom_Form_Model $form
	 * @return mixed
	 */
	public function get_form_settings( $form ) {
		// If not using the new "submission-behaviour" setting, set it according to the previous settings
		if ( ! isset( $form->settings['submission-behaviour'] ) ) {
			$redirect = ( isset( $form->settings['redirect'] ) && 'true' === $form->settings['redirect'] );
			$thankyou = ( isset( $form->settings['thankyou'] ) && 'true' === $form->settings['thankyou'] );

			if( $thankyou || ( ! $thankyou && ! $redirect ) ){
				$form->settings['submission-behaviour'] = 'behaviour-thankyou';
			} elseif( $redirect ){
				$form->settings['submission-behaviour'] = 'behaviour-redirect';
			}
		}
		return $form->settings;
	}
}