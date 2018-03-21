<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_Admin
 *
 * @since 1.0
 */
class Forminator_Poll_Admin extends Forminator_Admin_Module {

	/**
	 * Init
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module       = Forminator_Polls::get_instance();
		$this->page         = 'forminator-poll';
		$this->page_edit    = 'forminator-poll-wizard';
		$this->page_entries = 'forminator-poll-view';
	}

	/**
	 * Include files
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
		new Forminator_Poll_Page( 'forminator-poll', 'poll/list', __( 'Polls', Forminator::DOMAIN ), __( 'Polls', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Poll_New_Page( 'forminator-poll-wizard', 'poll/wizard', __( 'New Poll', Forminator::DOMAIN ), __( 'New Poll', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Poll_View_Page( 'forminator-poll-view', 'poll/entries', __( 'Entries:', Forminator::DOMAIN ), __( 'View Poll', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Remove necessary pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {
		remove_submenu_page( 'forminator', 'forminator-poll-wizard' );
		remove_submenu_page( 'forminator', 'forminator-poll-view' );
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
			$data['application'] = 'poll';
			if ( ! self::is_edit() ) {
				$data['currentForm'] = array();
			} else {
				$id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				if ( ! is_null( $id ) ) {
					$model = Forminator_Poll_Form_Model::model()->load( $id );
				}
				$answers = array();
				if ( is_object( $model ) ) {
					foreach ( (array) $model->getFields() as $field ) {
						$a = array(
							'title' => $field->title,
						);
						if ( filter_var( $field->use_extra, FILTER_VALIDATE_BOOLEAN ) == true ) {
							$a['use_extra'] = true;
							$a['extra']     = $field->extra;
						}
						$answers[] = $a;
					}
				}

				// Load stored record
				$settings = apply_filters( 'forminator_poll_settings', $model->settings, $model, $data, $this );
				$data['currentForm'] = array_merge( array(
					'answers'  => $answers,
					'formName' => $model->name,
					'formID'   => $model->id
				), $settings );
			}
		}

		$data['modules']['polls'] = array(
			'new_form_url'  => menu_page_url( $this->page_edit, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_polls' )
		);

		return apply_filters( 'forminator_poll_admin_data', $data, $model, $this );
	}

	/**
	 * Localize modules
	 *
	 * @since 1.0
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {
		$data['polls'] = array(
			'poll'                           => __( 'Poll', Forminator::DOMAIN ),
			"add_answer"                     => __( "Add Answers", Forminator::DOMAIN ),
			"answer_placeholder"             => __( "Enter poll answer", Forminator::DOMAIN ),
			"custom_input_placeholder_label" => __( "Custom input placeholder", Forminator::DOMAIN ),
			"custom_input_placeholder"       => __( "Type placeholder here...", Forminator::DOMAIN ),
			"add_custom_field"               => __( "Add custom input field", Forminator::DOMAIN ),
			"remove_custom_field"            => __( "Remove custom input field", Forminator::DOMAIN ),
			"delete_answer"                  => __( "Delete answer", Forminator::DOMAIN ),
			"details"                        => __( "Details", Forminator::DOMAIN ),
			"appearance"                     => __( "Appearance", Forminator::DOMAIN ),
			"preview"                        => __( "Preview", Forminator::DOMAIN ),
			"details_title"                  => __( "Poll Details", Forminator::DOMAIN ),
			"poll_title"                     => __( "Title", Forminator::DOMAIN ),
			"poll_desc"                      => __( "Description (optional)", Forminator::DOMAIN ),
			"poll_question"                  => __( "Poll question", Forminator::DOMAIN ),
			"poll_answers"                   => __( "Poll answers", Forminator::DOMAIN ),
			"poll_button"                    => __( "Button label", Forminator::DOMAIN ),
			"poll_title_placeholder"         => __( "Enter title", Forminator::DOMAIN ),
			"poll_desc_placeholder"          => __( "Enter description", Forminator::DOMAIN ),
			"poll_question_placeholder"      => __( "Enter question title", Forminator::DOMAIN ),
			"poll_button_placeholder"        => __( "E.g. Vote", Forminator::DOMAIN ),
			"appearance_title"               => __( "Poll Appearance", Forminator::DOMAIN ),
			"results_behav"                  => __( "Poll results behaviour", Forminator::DOMAIN ),
			"results_style"                  => __( "Poll results style", Forminator::DOMAIN ),
			"votes_count"                    => __( "Poll votes count", Forminator::DOMAIN ),
			"poll_colors"                    => __( "Colors", Forminator::DOMAIN ),
			"votes_limit"                    => __( "Vote number limit", Forminator::DOMAIN ),
			"link_on"                        => __( "Link on poll", Forminator::DOMAIN ),
			"show_after"                     => __( "Show after voted", Forminator::DOMAIN ),
			"not_show"                       => __( "Do not show", Forminator::DOMAIN ),
			"chart_bar"                      => __( "Bar chart", Forminator::DOMAIN ),
			"chart_pie"                      => __( "Pie chart", Forminator::DOMAIN ),
			"show_votes"                     => __( "Show number of votes", Forminator::DOMAIN ),
			"enable_limit"                   => __( "Allow same visitor to vote more than once", Forminator::DOMAIN ),
			"how_long"                       => __( "How long before user can vote again", Forminator::DOMAIN ),
			"poll_description"               => __( "Poll description", Forminator::DOMAIN ),
			"submission"                     => __( "Submission", Forminator::DOMAIN ),
			"enable_ajax"                    => __( "Enable AJAX", Forminator::DOMAIN ),
			"validate_form_name"             => __( "Form name cannot be empty! Please pick a name for your poll.", Forminator::DOMAIN ),
			"validate_form_question"         => __( "Poll question cannot be empty! Please add questions for your poll.", Forminator::DOMAIN ),
			"validate_form_answers"          => __( "Poll answers cannot be empty! Please add answers to your poll.", Forminator::DOMAIN ),
			"back"                           => __( "Back", Forminator::DOMAIN ),
			"cancel"                         => __( "Cancel", Forminator::DOMAIN ),
			"continue"                       => __( "Continue", Forminator::DOMAIN ),
			"finish"                         => __( "Finish", Forminator::DOMAIN ),
			"submission_explain"			 => __( "Enable AJAX to prevent refresh while submitting poll data.", Forminator::DOMAIN ),
			"votes_explain"					 => __( "Enable this option to display number of votes on Bar Chart results.")
		);

		return $data;
	}
}