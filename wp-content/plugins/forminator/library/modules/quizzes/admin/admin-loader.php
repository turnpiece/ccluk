<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizz_Admin
 *
 * @since 1.0
 */
class Forminator_Quizz_Admin extends Forminator_Admin_Module {

	/**
	 * Initialize
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module              = Forminator_Quizzes::get_instance();
		$this->page                = 'forminator-quiz';
		$this->page_edit_nowrong   = 'forminator-nowrong-wizard';
		$this->page_edit_knowledge = 'forminator-knowledge-wizard';
		$this->page_entries        = 'forminator-quiz-view';
	}

	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	public function includes() {
		include_once( dirname(__FILE__) . '/admin-page-new-nowrong.php' );
		include_once( dirname(__FILE__) . '/admin-page-new-knowledge.php' );
		include_once( dirname(__FILE__) . '/admin-page-view.php' );
		include_once( dirname(__FILE__) . '/admin-page-entries.php' );
	}

	/**
	 * Add module pages to Admin
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {
		new Forminator_Quizz_Page( $this->page, 'quiz/list', __( 'Quizzes', Forminator::DOMAIN ), __( 'Quizzes', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Quizz_New_NoWrong( $this->page_edit_nowrong, 'quiz/nowrong', __( 'New Quiz', Forminator::DOMAIN ), __( 'New Quiz', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Quizz_New_Knowledge( $this->page_edit_knowledge, 'quiz/knowledge', __( 'New Quiz', Forminator::DOMAIN ), __( 'New Quiz', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Quizz_View_Page( $this->page_entries, 'quiz/entries', __( 'Entries:', Forminator::DOMAIN ), __( 'View Quizzes', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Remove necessary pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {
		remove_submenu_page( 'forminator', $this->page_edit_nowrong );
		remove_submenu_page( 'forminator', $this->page_edit_knowledge );
		remove_submenu_page( 'forminator', $this->page_entries );
	}

	/**
	 * Is the type of the quiz "knowledge"
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_knowledge_wizard() {
		return (bool) isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_edit_knowledge );
	}

	/**
	 * Is the type of the quiz "no wrong answer"
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_nowrong_wizard() {
		return (bool) isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_edit_nowrong );
	}

	/**
	 * Highlight parent page in sidebar
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function highlight_admin_parent( $file ) {
		global $plugin_page;

		if ( $this->page_edit_nowrong == $plugin_page || $this->page_edit_knowledge == $plugin_page || $this->page_entries == $plugin_page ) {
			$plugin_page = $this->page;
		}

		return $file;
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
		$id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
		$model = null;

		if ( ! is_null( $id ) ) {
			$model = Forminator_Quiz_Form_Model::model()->load( $id );
		}

		if ( $this->is_knowledge_wizard() ) {
			$data['application'] = 'knowledge';

			if ( ! self::is_edit() ) {
				$data['currentForm'] = array();
			} else {
				// Load stored record
				if ( is_object( $model ) ) {
					$cForm               = array_merge( array(
						'formName'   => $model->name,
						'formID'     => $model->id,
						'questions'  => $model->questions,
						'results'    => array(),
						'quiz_title' => $model->name
					), $model->settings );
					$data['currentForm'] = $cForm;
				} else {
					$data['currentForm'] = array();
				}
			}
		}

		if ( $this->is_nowrong_wizard() ) {
			$data['application'] = 'nowrong';

			if ( ! self::is_edit() ) {
				$data['currentForm'] = array();
			} else {
				// Load stored record
				if ( is_object( $model ) ) {
					unset( $model->settings['priority_order'] );
					$settings = apply_filters( 'forminator_quiz_settings', $model->settings, $model, $data, $this );
					$cForm               = array_merge( array(
						'formName'   => $model->name,
						'formID'     => $model->id,
						'results'    => $model->getResults(),
						'questions'  => $model->questions,
						'quiz_title' => $model->name
					), $settings );

					$data['currentForm'] = $cForm;
				} else {
					$data['currentForm'] = array();
				}
			}
		}

		$data['modules']['quizzes'] = array(
			'nowrong_url'   => menu_page_url( $this->page_edit_nowrong, false ),
			'knowledge_url' => menu_page_url( $this->page_edit_knowledge, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_quizzes' )
		);

		return apply_filters( 'forminator_quiz_admin_data', $data, $model, $this );
	}

	/**
	 * Localize modules strings
	 *
	 * @since 1.0
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {
		$data['quizzes'] = array(
			'quizzes'                      => __( 'Quizzes', Forminator::DOMAIN ),
			"popup_label"                  => __( "Choose Quiz Type", Forminator::DOMAIN ),
			"nowrong_label"                => __( "No Wrong Answer", Forminator::DOMAIN ),
			"nowrong_description"          => __( "Similar to quizzes you see on Facebook. e.g. Answer these questions, and we will tell you what breed of dog you are at heart.", Forminator::DOMAIN ),
			"knowledge_label"              => __( "Knowledge", Forminator::DOMAIN ),
			"knowledge_description"        => __( "Quizzes that test your knowledge af things. e.g. Just how well exactly do you know your Seinfeld quotes.", Forminator::DOMAIN ),
			"results"                      => __( "Results", Forminator::DOMAIN ),
			"questions"                    => __( "Questions", Forminator::DOMAIN ),
			"details"                      => __( "Details", Forminator::DOMAIN ),
			"settings"                     => __( "Settings", Forminator::DOMAIN ),
			"appearance"				   => __( "Appearance", Forminator::DOMAIN ),
			"preview"                      => __( "Preview", Forminator::DOMAIN ),
			"preview_quiz"                 => __( "Preview Quiz", Forminator::DOMAIN ),
			"list"                         => __( "List", Forminator::DOMAIN ),
			"grid"                         => __( "Grid", Forminator::DOMAIN ),
			"visual_style"                 => __( "Visual style", Forminator::DOMAIN ),
			"quiz_title"                   => __( "Quiz Title", Forminator::DOMAIN ),
			"quiz_title_desc"			   => __( "Further customize the appearance for quiz title. It appears as result's header.", Forminator::DOMAIN ),
			"title"						   => __( "Title", Forminator::DOMAIN ),
			"title_desc"				   => __( "Further customize appearance for quiz title.", Forminator::DOMAIN ),
			"image_desc"				   => __( "Further customize appearance for quiz featured image.", Forminator::DOMAIN ),
			"enable_styles"				   => __( "Enable custom styles", Forminator::DOMAIN ),
			"desc_desc"				       => __( "Further customize appearance for quiz description / intro.", Forminator::DOMAIN ),
			"description"                  => __( "Description / Intro", Forminator::DOMAIN ),
			"feat_image"                   => __( "Feature image", Forminator::DOMAIN ),
			"font_color"	               => __( "Font Color", Forminator::DOMAIN ),
			"browse"                       => __( "Browse", Forminator::DOMAIN ),
			"clear"                        => __( "Clear", Forminator::DOMAIN ),
			"results_behav"                => __( "Results behaviour", Forminator::DOMAIN ),
			"rb_description"               => __( "Pick if you want to reveal the correct answer as user finishes question, or only after the whole quiz is completed.", Forminator::DOMAIN ),
			"reveal"                       => __( "When to reveal correct answer", Forminator::DOMAIN ),
			"after"                        => __( "After user picks answer", Forminator::DOMAIN ),
			"before"                       => __( "At the end of whole quiz", Forminator::DOMAIN ),
			"phrasing"                     => __( "Answer phrasing", Forminator::DOMAIN ),
			"phrasing_desc"                => __( "Pick how you want the correct & incorrect answers to read. Use <strong>%UserAnswer%</strong> to pull in the value user selected & <strong>%CorrectAnswer%</strong> to pull in the correct value.", Forminator::DOMAIN ),
			"phrasing_desc_alt"				=> __( "Further customize appearance for answer message.", Forminator::DOMAIN ),
			"msg_correct"                  => __( "Correct answer message", Forminator::DOMAIN ),
			"msg_incorrect"                => __( "Incorrect answer message", Forminator::DOMAIN ),
			"msg_count"                    => __( "Final count message", Forminator::DOMAIN ),
			"msg_count_desc"               => __( "Edit the copy of the final result count message that will appear after the quiz is complete. Use <strong>%YourNum%</strong> to display number of correct answers & <strong>%Total%</strong> for total number of questions.", Forminator::DOMAIN ),
			"msg_count_info"				=> __( "You can now add some html content here to personalize even more text displayed as Final Count Message. Try it now!", Forminator::DOMAIN ),
			"share"							=> __( "Share on social media", Forminator::DOMAIN ),
			"order"							=> __( "Results priority order", Forminator::DOMAIN ),
			"order_label"					=> __( "Pick priority for results", Forminator::DOMAIN ),
			"order_alt"						=> __( "Quizzes can have even number of scores for 2 or more results, in those scenarious, this order will help determine the result.", Forminator::DOMAIN ),
			"questions_title"				=> __( "Questions", Forminator::DOMAIN ),
			"question_desc"					=> __( "Further customize appearance for quiz questions.", Forminator::DOMAIN ),
			"result_title"					=> __( "Result title", Forminator::DOMAIN ),
			"result_description"			=> __( "Result description", Forminator::DOMAIN ),
			"result_description_desc"		=> __( "Further customize the appearance for result description typography.", Forminator::DOMAIN ),
			"result_title_desc"				=> __( "Further customize the appearance for result title typography.", Forminator::DOMAIN ),
			"retake_button"					=> __( "Retake button", Forminator::DOMAIN ),
			"retake_button_desc"			=> __( "Further customize the appearance for retake quiz button.", Forminator::DOMAIN ),
			"validate_form_name"			=> __( "Form name cannot be empty! Please pick a name for your quiz.", Forminator::DOMAIN ),
			"validate_form_question"		=> __( "Quiz question cannot be empty! Please add questions for your quiz.", Forminator::DOMAIN ),
			"validate_form_answers"			=> __( "Quiz answers cannot be empty! Please add some questions.", Forminator::DOMAIN ),
			"validate_form_answers_result"	=> __( "Result answer cannot be empty! Please select a result.", Forminator::DOMAIN ),
			"validate_form_correct_answer"	=> __( "Please select a correct answer for this question.", Forminator::DOMAIN ),
			"validate_form_no_answer"	    => __( "Please add an answer for this question.", Forminator::DOMAIN ),
			"answer"						=> __( "Answers", Forminator::DOMAIN ),
			"answer_desc"					=> __( "Further customize appearance for quiz answers.", Forminator::DOMAIN ),
			"back"							=> __( "Back", Forminator::DOMAIN ),
			"cancel"						=> __( "Cancel", Forminator::DOMAIN ),
			"continue"						=> __( "Continue", Forminator::DOMAIN ),
			"correct_answer"				=> __( "Correct answer", Forminator::DOMAIN ),
			"correct_answer_desc"			=> __( "Customize appearance for correct answers.", Forminator::DOMAIN ),
			"finish"						=> __( "Finish", Forminator::DOMAIN ),
			"smartcrawl"					=> __( "<strong>Want more control?</strong> <strong><a href='https://premium.wpmudev.org/project/smartcrawl-wordpress-seo/' target='_blank'>SmartCrawl</a></strong> OpenGraph and Twitter Card support lets you choose how your content looks when it’s shared on social media.", Forminator::DOMAIN ),
			"submit"						=> __( "Submit", Forminator::DOMAIN ),
			"submit_desc"					=> __( "Further customize appearance for quiz submit button.", Forminator::DOMAIN ),
			"main_styles"					=> __( "Main styles", Forminator::DOMAIN ),
			"border"						=> __( "Border", Forminator::DOMAIN ),
			"border_desc"					=> __( "Further customize border for result's main container.", Forminator::DOMAIN ),
			"padding"						=> __( "Padding", Forminator::DOMAIN ),
			"background"					=> __( "Background", Forminator::DOMAIN ),
			"background_desc"				=> __( "Further customize background color for result's main container, header and content.", Forminator::DOMAIN ),
			"bg_main"						=> __( "Main BG", Forminator::DOMAIN ),
			"bg_header"						=> __( "Header BG", Forminator::DOMAIN ),
			"bg_content"					=> __( "Content BG", Forminator::DOMAIN ),
			"color"							=> __( "Color", Forminator::DOMAIN ),
			"result_appearance"				=> __( "Quiz Result Appearance", Forminator::DOMAIN ),
			"margin"						=> __( "Margin", Forminator::DOMAIN ),
			"summary"						=> __( "Summary", Forminator::DOMAIN ),
			"summary_desc"					=> __( "Further customize appearance for quiz final count message", Forminator::DOMAIN ),
			"sshare"						=> __( "Sharing text", Forminator::DOMAIN ),
			"sshare_desc"					=> __( "Further customize appearance for share on social media text", Forminator::DOMAIN ),
			"social"						=> __( "Social icons", Forminator::DOMAIN ),
			"social_desc"					=> __( "Further customize appearance for social media icons", Forminator::DOMAIN ),
			"wrong_answer"					=> __( "Wrong answer", Forminator::DOMAIN ),
			"wrong_answer_desc"				=> __( "Customize appearance for wrong answers.", Forminator::DOMAIN )
		);

		return $data;
	}
}