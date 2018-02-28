<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Custom_Form_Admin
 *
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
		include_once( dirname(__FILE__) . '/admin-page-new.php' );
		include_once( dirname(__FILE__) . '/admin-page-view.php' );
		include_once( dirname(__FILE__) . '/admin-page-entries.php' );
	}

	/**
	 * Add module pages to Admin
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {
		new Forminator_CForm_Page( $this->page, 'custom-form/list', __( 'Forms', Forminator::DOMAIN ), __( 'Forms', Forminator::DOMAIN ), 'forminator' );
		new Forminator_CForm_New_Page( $this->page_edit, 'custom-form/wizard', __( 'Edit Form', Forminator::DOMAIN ), __( 'New Custom Form', Forminator::DOMAIN ), 'forminator' );
		new Forminator_CForm_View_Page( $this->page_entries, 'custom-form/entries', __( 'Entries:', Forminator::DOMAIN ), __( 'View Custom Form', Forminator::DOMAIN ), 'forminator' );
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
		if ( $this->is_admin_wizard() ) {
			$data['application'] = 'builder';

			if ( ! self::is_edit() ) {
				// Load settings from template
				$template = $this->get_template();
				if( isset( $_GET['name'] ) ) {
					$name = sanitize_text_field( $_GET['name'] );
				}

				if ( $template ) {
					$data['currentForm'] = array_merge( array(
						'wrappers' => $template->fields,
						'formName' => ''
					), $template->settings );
				} else {
					$data['currentForm'] = array(
						'fields'   => array(),
						'formName' => $name
					);
				}
			} else {
				$id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				$model = null;
				if ( ! is_null( $id ) ) {
					$model = Forminator_Custom_Form_Model::model()->load( $id );
				}

				$wrappers = array();
				if ( is_object( $model ) ) {
					$wrappers = $model->getFieldsGrouped();
				}

				// Load stored record
				$data['currentForm'] = array_merge( array(
					'wrappers' => $wrappers,
					'formName' => $model->name,
					'formID'   => $model->id
				), $model->settings );
			}
		}

		$data['modules']['custom_form'] = array(
			'templates'     => $this->module->get_templates(),
			'new_form_url'  => menu_page_url( $this->page_edit, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_cforms' )
		);

		return $data;
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
			"new_field"        => __( "Add New Field", Forminator::DOMAIN ),
			"save"             => __( "Save", Forminator::DOMAIN ),
			"continue"         => __( "Continue", Forminator::DOMAIN ),
			"finish"           => __( "Finish", Forminator::DOMAIN ),
			"publish"          => __( "Publish", Forminator::DOMAIN ),
			"form_settings"    => __( "Form Settings", Forminator::DOMAIN ),
			"form_name"        => __( "Enter form name", Forminator::DOMAIN ),
			"next"             => __( "Next", Forminator::DOMAIN ),
			"back"             => __( "Back", Forminator::DOMAIN ),
			"click_drag_label" => __( "Click & Drag fields", Forminator::DOMAIN ),
			"standard"         => __( "Standard", Forminator::DOMAIN ),
			"posts"            => __( "Posts", Forminator::DOMAIN ),
			"pricing"          => __( "Pricing", Forminator::DOMAIN ),
			"back_to_fields"   => __( "Back to Fields", Forminator::DOMAIN ),
			"clone"            => __( "Clone", Forminator::DOMAIN ),
			"general"          => __( "General", Forminator::DOMAIN ),
			"advanced"         => __( "Advanced", Forminator::DOMAIN ),
			"required_filed"   => __( "Required field: %s", Forminator::DOMAIN ),
			"no_conditions"    => __( "You have not yet created any conditions.", Forminator::DOMAIN ),
			"no_fields"        => __( "Drag and drop fields from the sidebar to add them to your form.", Forminator::DOMAIN ),
			"use_custom_class" => __( "Custom class", Forminator::DOMAIN ),
			"custom_class"     => __( "Custom class", Forminator::DOMAIN ),
			"delete"           => __( "Delete field", Forminator::DOMAIN ),
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
			"more_conditions"   => __( "more conditions", Forminator::DOMAIN )
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

		$data['appearance'] = array(
			"settings_title"              => __( "SETTINGS & BEHAVIOURS", Forminator::DOMAIN ),
			"preview_form"                => __( "Preview Form", Forminator::DOMAIN ),
			"appearance"                  => __( "Appearance", Forminator::DOMAIN ),
			"form_behaviour"              => __( "Form behaviour", Forminator::DOMAIN ),
			"form_emails"                 => __( "Form emails", Forminator::DOMAIN ),
			"form_pagination"             => __( "Pagination", Forminator::DOMAIN ),
			"advanced"                    => __( "Advanced", Forminator::DOMAIN ),
			"form_style"                  => __( "Form style", Forminator::DOMAIN ),
			"fields_style"                => __( "Fields style", Forminator::DOMAIN ),
			"default"                     => __( "Default design", Forminator::DOMAIN ),
			"flat"                        => __( "Flat design", Forminator::DOMAIN ),
			"bold"                        => __( "Bold design", Forminator::DOMAIN ),
			"material"                    => __( "Material design", Forminator::DOMAIN ),
			"custom"                      => __( "Custom design", Forminator::DOMAIN ),
			"open_fields"                 => __( "Open fields", Forminator::DOMAIN ),
			"enclosed_fields"             => __( "Enclosed fields", Forminator::DOMAIN ),
			"customize_typography"        => __( "Customize typography", Forminator::DOMAIN ),
			"custom_font_family"          => __( "Enter custom font family name", Forminator::DOMAIN ),
			"custom_font_placeholder"     => __( "E.g. 'Arial', sans-serif", Forminator::DOMAIN ),
			"custom_font_description"     => __( "Type the font family name, as you would in CSS", Forminator::DOMAIN ),
			"customize_colors"            => __( "Customize colors", Forminator::DOMAIN ),
			"font_family"                 => __( "Font family", Forminator::DOMAIN ),
			"font_size"                   => __( "Font size", Forminator::DOMAIN ),
			"font_weight"                 => __( "Font weight", Forminator::DOMAIN ),
			"custom_text"                 => __( "Custom text", Forminator::DOMAIN ),
			"custom_submit_text"          => __( "Enter your submit button text", Forminator::DOMAIN ),
			"use_custom_submit"           => __( "Use custom submit button text", Forminator::DOMAIN ),
			"use_custom_invalid_form"     => __( "Use custom invalid form message", Forminator::DOMAIN ),
			"custom_invalid_form_message" => __( "Enter your invalid form message", Forminator::DOMAIN ),
			"use_custom_css"              => __( "Use custom CSS for this module", Forminator::DOMAIN ),
			"css_selectors"               => __( "Available CSS Selectors (click to add)", Forminator::DOMAIN ),
			"form_lifespan"               => __( "Form lifespan", Forminator::DOMAIN ),
			"after_form_submit"           => __( "After form submit", Forminator::DOMAIN ),
			"submission"                  => __( "Submission", Forminator::DOMAIN ),
			"enable_ajax"                 => __( "Enable AJAX", Forminator::DOMAIN ),
			"validation"                  => __( "Validation", Forminator::DOMAIN ),
			"security"                    => __( "Security", Forminator::DOMAIN ),
			"form_doesnt_expire"          => __( "Form does not expire", Forminator::DOMAIN ),
			"on_certain_date"             => __( "Expires on certain date", Forminator::DOMAIN ),
			"expires_submits"             => __( "Expires after x-submits", Forminator::DOMAIN ),
			"show_thank_you_message"      => __( "Show a Thank you message", Forminator::DOMAIN ),
			"redirect_to_url"             => __( "Re-direct user to URL", Forminator::DOMAIN ),
			"server_only"                 => __( "Server only", Forminator::DOMAIN ),
			"form_submit"                 => __( "On form submit", Forminator::DOMAIN ),
			"inline"                      => __( "Enable inline validation (as user types)", Forminator::DOMAIN ),
			"enable_honeypot"             => __( "Enable honeypot protection", Forminator::DOMAIN ),
			"only_logged"                 => __( "Only logged-in users can submit", Forminator::DOMAIN ),
			"select_font"                 => __( "Select font", Forminator::DOMAIN ),
			"custom_font"                 => __( "Custom user font", Forminator::DOMAIN ),
			"label"                       => __( "Label", Forminator::DOMAIN ),
			"labels"                      => __( "Labels", Forminator::DOMAIN ),
			"border_line"                 => __( "Border line", Forminator::DOMAIN ),
			"value"                       => __( "Value", Forminator::DOMAIN ),
			"description"                 => __( "Description", Forminator::DOMAIN ),
			"send_user_email"             => __( "Send user email", Forminator::DOMAIN ),
			"send_admin_email"            => __( "Send me (admin) email", Forminator::DOMAIN ),
			"add_form_data"               => __( "Add form based data", Forminator::DOMAIN ),
			"subject"                     => __( "Subject", Forminator::DOMAIN ),
			"body"                        => __( "Body", Forminator::DOMAIN ),
			"generate_pdf"                => __( "Generate PDF using data", Forminator::DOMAIN ),
			"integrations"                => __( "Various service integrations (send to Mailchimp etc)", Forminator::DOMAIN ),
			"static"                      => __( "Static", Forminator::DOMAIN ),
			"hover"                       => __( "Hover", Forminator::DOMAIN ),
			"active"                      => __( "Active", Forminator::DOMAIN ),
			"btn_bg"                      => __( "Button background", Forminator::DOMAIN ),
			"btn_txt"                     => __( "Button text", Forminator::DOMAIN ),
			"links"                       => __( "Results link", Forminator::DOMAIN ),
			"minutes"                     => __( "minute(s)", Forminator::DOMAIN ),
			"hours"                       => __( "hour(s)", Forminator::DOMAIN ),
			"days"                        => __( "day(s)", Forminator::DOMAIN ),
			"weeks"                       => __( "week(s)", Forminator::DOMAIN ),
			"months"                      => __( "month(s)", Forminator::DOMAIN ),
			"years"                       => __( "year(s)", Forminator::DOMAIN ),
			"form_name"                   => __( "Name your form", Forminator::DOMAIN ),
			"form_name_validation"        => __( "Form name cannot be empty! Please pick a name for your form.", Forminator::DOMAIN ),
			"pagination_none"             => __( "None", Forminator::DOMAIN ),
			"pagination_bar"              => __( "Progress bar", Forminator::DOMAIN ),
			"pagination_nav"              => __( "Navigation Steps", Forminator::DOMAIN ),
			"pagination_off_label"        => __( "When this option is selected no header with pagination will show up on your form.", Forminator::DOMAIN ),
			"pagination_bar_label"        => __( "When this option is selected you will see a progress bar going from 0% on first page to 100% on last page.", Forminator::DOMAIN ),
			"last_step_label"             => __( "Last step label", Forminator::DOMAIN ),
			"last_step_placeholder"       => __( "Finish", Forminator::DOMAIN ),
			"last_page_button"            => __( "Edit last page 'back' button text", Forminator::DOMAIN ),
			"last_page_placeholder"       => __( "« Previous", Forminator::DOMAIN ),
			"submit"                      => __( "Submit Button", Forminator::DOMAIN ),
			"background"                  => __( "Background", Forminator::DOMAIN ),
			"upload"                      => __( "Upload Button", Forminator::DOMAIN ),
			"pagination_prev"             => __( "Back", Forminator::DOMAIN ),
			"pagination_next"             => __( "Next", Forminator::DOMAIN ),
			"inputs"                      => __( "Inputs", Forminator::DOMAIN ),
			"border"                      => __( "Border", Forminator::DOMAIN ),
		);

		return $data;
	}

	/**
	 * Return template
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function get_template() {
		//$id = $_GET['template'];
		$id = 'contact_form';

		foreach ( $this->module->templates as $key => $template ) {
			if ( $template->options['id'] == $id ) {
				return $template;
			}
		}

		return false;
	}
}