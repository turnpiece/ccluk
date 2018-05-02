<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_l10n
 *
 * @since 1.0
 */
class Forminator_Admin_l10n {

	public $forminator = null;

	public function __construct() {
	}

	public function get_l10n_strings() {
		$l10n = $this->admin_l10n();

		return apply_filters( 'forminator_l10n', $l10n );
	}

	/**
	 * Default Admin properties
	 *
	 * @return array
	 */
	public function admin_l10n() {
		return array(
			"popup"         => array(
				"form_name_label"       => __( "Name your form", Forminator::DOMAIN ),
				"form_name_placeholder" => __( "E.g. Contact Form", Forminator::DOMAIN ),
				"name"                  => __( "Name", Forminator::DOMAIN ),
				"fields"                => __( "Fields", Forminator::DOMAIN ),
				"date"                  => __( "Date", Forminator::DOMAIN ),
				"clear_all"             => __( "Clear All", Forminator::DOMAIN ),
				"your_exports"          => __( "Your exports", Forminator::DOMAIN ),
				"quiz_type"             => __( "Choose Quiz Type", Forminator::DOMAIN ),
				"edit_login_form"       => __( "Edit Login or Register form", Forminator::DOMAIN ),
				"edit_scheduled_export" => __( "Edit Scheduled Export", Forminator::DOMAIN ),
				"frequency"             => __( "Frequency", Forminator::DOMAIN ),
				"daily"                 => __( "Daily", Forminator::DOMAIN ),
				"weekly"                => __( "Weekly", Forminator::DOMAIN ),
				"monthly"               => __( "Monthly", Forminator::DOMAIN ),
				"week_day"              => __( "Day of the week", Forminator::DOMAIN ),
				"monday"                => __( "Monday", Forminator::DOMAIN ),
				"tuesday"               => __( "Tuesday", Forminator::DOMAIN ),
				"wednesday"             => __( "Wednesday", Forminator::DOMAIN ),
				"thursday"              => __( "Thursday", Forminator::DOMAIN ),
				"friday"                => __( "Friday", Forminator::DOMAIN ),
				"saturday"              => __( "Saturday", Forminator::DOMAIN ),
				"sunday"                => __( "Sunday", Forminator::DOMAIN ),
				"day_time"              => __( "Time of the day", Forminator::DOMAIN ),
				"email_to"              => __( "Email export data to", Forminator::DOMAIN ),
				"email_placeholder"     => __( "E.g. john@doe.com", Forminator::DOMAIN ),
				"schedule_help"         => __( "Leave blank if you don't want to receive exports via email.", Forminator::DOMAIN ),
				"congratulations"       => __( "Congratulations!", Forminator::DOMAIN ),
				"is_ready"              => __( "is ready!", Forminator::DOMAIN ),
				"new_form_desc"         => __( "Add it to any post / page by clicking Forminator button, or set it up as a Widget.", Forminator::DOMAIN ),
				"paypal_settings"       => __( "Edit PayPal credentials", Forminator::DOMAIN ),
				"preview_cforms"        => __( "Preview Custom Form", Forminator::DOMAIN ),
				"preview_polls"         => __( "Preview Poll", Forminator::DOMAIN ),
				"preview_quizzes"       => __( "Preview Quiz", Forminator::DOMAIN ),
				"captcha_settings"      => __( "Edit reCaptcha credentials", Forminator::DOMAIN ),
				"currency_settings"     => __( "Edit default currency", Forminator::DOMAIN ),
				"pagination_entries"    => __( "Number of entries per page", Forminator::DOMAIN ),
				"pagination_listings"   => __( "Pagination Settings", Forminator::DOMAIN ),
				"uninstall_settings"   	=> __( "Uninstall Settings", Forminator::DOMAIN ),
				"validate_form_name"    => __( "Form name cannot be empty! Please pick a name for your form.", Forminator::DOMAIN ),
				"close"                 => __( "Close", Forminator::DOMAIN ),
				'records'               => __( "Records", Forminator::DOMAIN ),
				"delete"                => __( "Delete", Forminator::DOMAIN ),
				"confirm"               => __( "Confirm", Forminator::DOMAIN ),
				"are_you_sure"          => __( "Are you sure?", Forminator::DOMAIN ),
				"cannot_be_reverted"    => __( "Have in mind this action cannot be reverted.", Forminator::DOMAIN ),
				"confirm_action"        => __( "Please confirm that you want to do this action.", Forminator::DOMAIN ),
				"confirm_title"         => __( "Confirm Action", Forminator::DOMAIN ),
				"confirm_field_delete"  => __( "Please confirm that you want to delete this field", Forminator::DOMAIN ),
				"cancel"                => __( "Cancel", Forminator::DOMAIN ),
				"save_alert"            => __( "The changes you made may be lost if you navigate away from this page.", Forminator::DOMAIN ),
				"save_changes"          => __( "Save Changes", Forminator::DOMAIN ),
			),
			"sidebar"       => array(
				"label"         => __( "Label", Forminator::DOMAIN ),
				"value"         => __( "Value", Forminator::DOMAIN ),
				"add_option"    => __( "Add Option", Forminator::DOMAIN ),
				"delete"        => __( "Delete", Forminator::DOMAIN ),
				"pick_field"    => __( "Pick a field", Forminator::DOMAIN ),
				"field_will_be" => __( "This field will be", Forminator::DOMAIN ),
				"if"            => __( "if", Forminator::DOMAIN ),
				"shown"         => __( "Shown", Forminator::DOMAIN ),
				"hidden"        => __( "Hidden", Forminator::DOMAIN )
			),
			"colors"        => array(
				"poll_background"	=> __( "Poll BG", Forminator::DOMAIN ),
				"poll_border"		=> __( "Poll border", Forminator::DOMAIN ),
				"poll_shadow"		=> __( "Poll shadow", Forminator::DOMAIN ),
				"title"             => __( "Title text", Forminator::DOMAIN ),
				"question"          => __( "Question text", Forminator::DOMAIN ),
				"answer"            => __( "Answer text", Forminator::DOMAIN ),
				"input_background"  => __( "Input field bg", Forminator::DOMAIN ),
				"input_border"      => __( "Input field border", Forminator::DOMAIN ),
				"input_placeholder" => __( "Input field placeholder", Forminator::DOMAIN ),
				"input_text"        => __( "Input field text", Forminator::DOMAIN ),
				"btn_background"    => __( "Button background", Forminator::DOMAIN ),
				"btn_text"          => __( "Button text", Forminator::DOMAIN ),
				"link_res"          => __( "Results link", Forminator::DOMAIN )
			),
			"options"       => array(
				"browse"                => __( "Browse", Forminator::DOMAIN ),
				"clear"                 => __( "Clear", Forminator::DOMAIN ),
				"no_results"            => __( "You don't have any results yet.", Forminator::DOMAIN ),
				"select_result"         => __( "Select result", Forminator::DOMAIN ),
				"no_answers"            => __( "You don't have any answer yet.", Forminator::DOMAIN ),
				"placeholder_image"     => __( "Click browse to add image...", Forminator::DOMAIN ),
				"placeholder_image_alt" => __( "Click on image container (at left) to add one...", Forminator::DOMAIN ),
				"multiqs_empty"         => __( "You don't have any questions yet.", Forminator::DOMAIN ),
				"add_question"          => __( "Add Question", Forminator::DOMAIN ),
				"question_title"        => __( "Question title", Forminator::DOMAIN ),
				"answers"               => __( "Answers", Forminator::DOMAIN ),
				"add_answer"            => __( "Add Answer", Forminator::DOMAIN ),
				"add_result"            => __( "Add Result", Forminator::DOMAIN ),
				"delete_result"         => __( "Delete Result", Forminator::DOMAIN ),
				"title"                 => __( "Title", Forminator::DOMAIN ),
				"image"                 => __( "Image (optional)", Forminator::DOMAIN ),
				"description"           => __( "Description", Forminator::DOMAIN ),
				"trash_answer"          => __( "Delete Answer", Forminator::DOMAIN ),
				"correct"               => __( "Correct answer", Forminator::DOMAIN ),
				"no_options"            => __( "You don't have any options yet.", Forminator::DOMAIN ),
				"delete"                => __( "Delete", Forminator::DOMAIN ),
				"restricted_dates"      => __( "Restricted dates:", Forminator::DOMAIN ),
				"add"                   => __( "Add", Forminator::DOMAIN ),
				"custom_date"           => __( "Pick custom date(s) to restrict:", Forminator::DOMAIN ),
				"form_data"             => __( "Form Data", Forminator::DOMAIN ),
				"required_form_fields"  => __( "Required Fields", Forminator::DOMAIN ),
				"optional_form_fields"  => __( "Optional Fields", Forminator::DOMAIN ),
				"all_fields"            => __( "All Submitted Fields", Forminator::DOMAIN ),
				"form_name"             => __( "Form Name", Forminator::DOMAIN ),
				"misc_data"             => __( "Misc Data", Forminator::DOMAIN ),
				"form_based_data"       => __( "Add form data", Forminator::DOMAIN ),
				"been_saved"            => __( "has been saved.", Forminator::DOMAIN ),
				"been_published"        => __( "has been published.", Forminator::DOMAIN ),
				"error_saving"          => __( "Error! Form cannot be saved."),
				"admin_email"           => get_option('admin_email'),
			),
			"exporter"      => array(
				"export_nonce" => wp_create_nonce( 'formninator_exporter' ),
				"form_id"      => forminator_get_form_id_helper(),
				"form_type"    => forminator_get_form_type_helper(),
				"enabled"      => ( forminator_get_exporter_info( 'enabled', forminator_get_form_id_helper() . forminator_get_form_type_helper() ) === 'true' ),
				"interval"     => forminator_get_exporter_info( 'interval', forminator_get_form_id_helper() . forminator_get_form_type_helper() ),
				"month_day"    => forminator_get_exporter_info( 'month_day', forminator_get_form_id_helper() . forminator_get_form_type_helper() ),
				"day"          => forminator_get_exporter_info( 'day', forminator_get_form_id_helper() . forminator_get_form_type_helper() ),
				"hour"         => forminator_get_exporter_info( 'hour', forminator_get_form_id_helper() . forminator_get_form_type_helper() ),
				"email"        => forminator_get_exporter_info( 'email', forminator_get_form_id_helper() . forminator_get_form_type_helper() ),
			),
			"exporter_logs" => forminator_get_export_logs( forminator_get_form_id_helper() )
		);
	}

}