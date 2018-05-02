<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front action for quizzes
 *
 * @since 1.0
 */
class Forminator_Quizz_Front_Action extends Forminator_Front_Action {

	/**
	 * Forminator_Quizz_Front_Action constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'wp_ajax_forminator_submit_quizzes', array( &$this, 'submit_quizzes' ) );
		add_action( 'wp_ajax_nopriv_forminator_submit_quizzes', array( &$this, 'submit_quizzes' ) );
	}

	/**
	 * Handle quiz submit
	 *
	 * @since 1.0
	 */
	public function submit_quizzes() {
		$id    = isset( $_POST['form_id'] ) ? $_POST['form_id'] : null;
		$model = Forminator_Quiz_Form_Model::model()->load( $id );

		if ( ! is_object( $model ) ) {
			wp_send_json_error( array(
				'error' => apply_filters( 'forminator_submit_quiz_error_not_found', __( "Form not found", Forminator::DOMAIN ) )
			) );
		}

		/**
		 * Action called before submit quizzes
		 *
		 * @param Forminator_Quiz_Form_Model $model - the quiz model
		 */
		do_action( 'forminator_before_submit_quizzes', $model );

		if ( $model->quiz_type == 'nowrong' ) {
			$this->_process_nowrong_submit( $model );
		} else {
			$this->_process_knowledge_submit( $model );
		}
	}

	/**
	 * Process No wrong quiz
	 *
	 * @since 1.0
	 * @param $model
	 */
	private function _process_nowrong_submit( $model ) {
		//counting the result
		$results     = array();
		$result_data = array();

		if( isset( $_POST['answers'] ) ) {
			foreach ( $_POST['answers'] as $id => $answer ) {
				$results[]                = $model->getResultFromAnswer( $id, $answer );
				$question                 = $model->getQuestion( $id );
				$a                        = $model->getAnswer( $id, $answer );
				$result_data['answers'][] = array(
					'question' => $question['title'],
					'answer'   => $a['title']
				);
			}
		}
		$results = array_count_values( $results );
		asort( $results );
		$results  = array_reverse( $results );
		$finalRes = null;
		$clone    = array_values( $results );
		if ( ( count( $clone ) >= 2 && $clone[0] > $clone[1] ) || ( count( $clone ) < 2 ) ) {
			//this clearly we only have 1 result or the top over the lower
            $resultsKeys = array_keys( $results );
			$finalRes = array_shift( $resultsKeys );
		} elseif ( count( $clone ) >= 2 ) {
			//comare
			$priority = $model->getPriority();
			//get priority value
			$priorityValue = $results[ $priority ];
			$topOne        = $clone[0];
			if ( $topOne <= $priorityValue ) {
				$finalRes = $priority;
			} else {
				$finalRes = array_shift( array_keys( $results ) );
			}
		}

		$result_data['result'] = $model->getResult( $finalRes );;
		$this->_save_entry( $model->id, array(
			array(
				'name'  => 'entry',
				'value' => $result_data
			)
		) );
		wp_send_json_success( array(
			'result' => $this->_render_nowrong_result( $model, $finalRes ),
			'type'   => 'nowrong'
		) );
	}

	/**
	 * Render No wrong result
	 *
	 * @since 1.0
	 * @param $model
	 * @param $finalRes
	 *
	 * @return string
	 */
	private function _render_nowrong_result( $model, $finalRes ) {
		$result = $model->getResult( $finalRes );
		ob_start();
		?>
        <div class="forminator-quiz--footer">

            <div class="forminator-result">

                <div class="forminator-result--header">

                    <p><?php echo forminator_get_form_name( $model->id, 'quiz' ) ?></p>

                    <button type="button"><i class="wpdui-icon wpdui-icon-refresh" aria-hidden="true"></i> <?php _e( "Retake Quiz", Forminator::DOMAIN ) ?></button>

                </div>

                <div class="forminator-result--content">

                    <div class="forminator-result--text">

                        <p class="forminator-result--title"><?php echo $result['title'] ?></p>

				        <?php if ( isset( $result['description'] ) && ! empty( $result['description'] ) ): ?>
                            <p class="forminator-result--description"><?php echo $result['description'] ?></p>
				        <?php endif; ?>

                    </div>
			        <?php if ( isset( $result['image'] ) && ! empty( $result['image'] ) ): ?>
                        <figure class="forminator-result--image">
                            <img src="<?php echo $result['image'] ?>"/>
                        </figure>
			        <?php endif; ?>
                </div>

            </div>
        </div>
		<?php

		$nowrong_result_html = ob_get_clean();

		/**
		 * Filter to modify nowrong results
		 *
		 * @since 1.0.2
		 *
		 * @param string $nowrong_result_html - the return html
		 * @param Forminator_Quiz_Form_Model $model - the model
		 * @param string $finalRes - the final result
		 *
		 * @return string $nowrong_result_html
		 */
		return apply_filters( 'forminator_quizzes_render_nowrong_result', $nowrong_result_html, $model, $finalRes );
	}

	/**
	 * Process knowledge quiz
	 *
	 * @since 1.0
	 * @param $model
	 */
	private function _process_knowledge_submit( $model ) {
		$answers = isset( $_POST['answers'] ) ? $_POST['answers'] : null;
		if ( ! is_array( $answers ) || count( $answers ) == 0 ) {
			wp_send_json_error(
				array(
					'error' => apply_filters( 'forminator_quizzes_process_knowledge_submit_no_answer_error', __( "You haven't answered any questions", Forminator::DOMAIN ) )
				)
			);
		}
		$results  = array();
		$isFinish = true;
		/** @var Forminator_Quiz_Form_Model $model */
		if ( count( $model->questions ) != count( $answers ) ) {
			if ( $model->settings['results_behav'] == 'end' ) {
				//need to check if all the questions are answered
				wp_send_json_error(
					array(
						'error' => apply_filters( 'forminator_quizzes_process_knowledge_submit_answer_all_error', __( "Please answer all the questions", Forminator::DOMAIN ) )
					)
				);
			} else {
				$isFinish = false;
			}
		}
		//todo need to have a filter for answers if we use the result when chose
		$rightCounter = 0;
		$result_data  = array();
		foreach ( $answers as $id => $pick ) {
			$question = $model->getQuestion( $id );
			$meta     = array(
				'question' => $question['title']
			);
			list( $index, $right ) = $model->getRightAnswerForQuestion( $id );

			// no correct answer set on this quesion
			if ( is_null( $right ) || $index == - 1 ) {
				$right = array(
					'title' => __( 'none above', Forminator::DOMAIN ),
				);
			}
			$userPicked    = $model->getAnswer( $id, $pick );
			$correctText   = isset( $model->settings['msg_correct'] ) ? $model->settings['msg_correct'] : '';
			$inCorrectText = isset( $model->settings['msg_incorrect'] ) ? $model->settings['msg_incorrect'] : '';
			$finalText     = isset( $model->settings['msg_count'] ) ? $model->settings['msg_count'] : '';
			if ( $pick == $index ) {
				$results[ $id ]['message'] = str_replace( '%CorrectAnswer%', $right['title'],
					str_replace( '%UserAnswer%', $userPicked['title'], $correctText ) );
				$results[ $id ]['isCorrect'] = true;
				$results[ $id ]['answer'] = $id . '-' . $pick;
				$rightCounter ++;
				$meta['answer']    = $userPicked['title'];
				$meta['isCorrect'] = true;
			} else {
				$results[ $id ]['message']    = str_replace( '%CorrectAnswer%', $right['title'],
					str_replace( '%UserAnswer%', $userPicked['title'], $inCorrectText ) );
				$results[ $id ]['isCorrect'] = false;
				$results[ $id ]['answer'] = $id . '-' . $pick;
				$meta['answer']    = $userPicked['title'];
				$meta['isCorrect'] = false;
			}
			$result_data[] = $meta;
		}
		$this->_save_entry( $model->id, $result_data );
		//store the
		wp_send_json_success( array(
			'result'    => $results,
			'type'      => 'knowledge',
			'finalText' => $isFinish == true ? $this->_render_knowledge_result( str_replace( '%YourNum%', $rightCounter, str_replace( '%Total%', count( $results ), $finalText ) ), $model, $rightCounter, count( $results ) ) : ''
		) );
	}

	/**
	 * Render knowledge result
	 *
	 * @since 1.0
	 * @param $text
	 * @param $model
	 *
	 * @return string
	 */
	private function _render_knowledge_result( $text, $model, $right_answers, $total_answers ) {
		ob_start();
		?>
        <div class="forminator-quiz--footer">
            <div class="forminator-quiz--summary"><?php echo wpautop( $text, true ); ?></div>
			<?php
			$is_fb 	= isset( $model->settings['facebook'] ) && $model->settings['facebook'] == true ? true : false;
			$is_tw 	= isset( $model->settings['twitter'] ) && $model->settings['twitter'] == true ? true : false;
			$is_g  	= isset( $model->settings['google'] ) && $model->settings['google'] == true ? true : false;
			$is_li  = isset( $model->settings['linkedin'] ) && $model->settings['linkedin'] == true ? true : false;

			if ( $is_fb || $is_g || $is_tw ):
			?>
				<div class="forminator-quiz--share">
					<p><?php _e( "Share your results", Forminator::DOMAIN ) ?></p>
					<ul class="forminator-share--icons" data-message="<?php printf( __( "I got %s/%s on %s quiz! ", Forminator::DOMAIN ), $right_answers, $total_answers, $model->settings['formName'] ); ?>">
						<?php if ( $is_fb ): ?>
							<li class="forminator-share--icon">
								<a href="#" data-social="facebook" class="wpdui-icon wpdui-icon-social-facebook"></a>
							</li>
						<?php endif; ?>
						<?php if ( $is_tw ): ?>
							<li class="forminator-share--icon">
								<a href="#"  data-social="twitter" class="wpdui-icon wpdui-icon-social-twitter"></a>
							</li>
						<?php endif; ?>
						<?php if ( $is_g ): ?>
							<li class="forminator-share--icon">
								<a href="#" data-social="google" class="wpdui-icon wpdui-icon-social-google-plus"></a>
							</li>
						<?php endif; ?>
						<?php if ( $is_li ): ?>
							<li class="forminator-share--icon">
								<a href="#" data-social="linkedin" class="wpdui-icon wpdui-icon-social-linkedin"></a>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>
        </div>
		<?php
		$knowledge_result_html = ob_get_clean();

		/**
		 * Filter to modify knowledge results
		 *
		 * @since 1.0.2
		 *
		 * @param string $knowledge_result_html - the return html
		 * @param string $text - the summary text
		 * @param Forminator_Quiz_Form_Model $model - the model
		 *
		 * @return string $knowledge_result_html
		 */
		return apply_filters( 'forminator_quizzes_render_knowledge_result', $knowledge_result_html, $text, $model );
	}

	/**
	 * Entry type
	 *
	 * @since 1.0
	 * @var string
	 */
	public $entry_type = 'quizzes';

	/**
	 * Save entry
	 *
	 * @since 1.0
	 * @return application/json Json response
	 */
	function save_entry() {}

	/**
	 * @since 1.0
	 * @param $form_id
	 * @param $field_data
	 *
	 * @return bool
	 */
	private function _save_entry( $form_id, $field_data ) {
		$entry             = new Forminator_Form_Entry_Model();
		$entry->entry_type = $this->entry_type;
		$entry->form_id    = $form_id;
		if ( $entry->save() ) {

			/**
			 * Action called before setting fields to database
			 *
			 * @since 1.0.2
			 *
			 * @param Forminator_Form_Entry_Model $entry - the entry model
			 * @param int $form_id - the form id
			 * @param array $field_data - the entry data
			 *
			 */
			do_action( 'forminator_quizzes_submit_before_set_fields', $entry, $form_id, $field_data );
			$entry->set_fields( array(
				array(
					'name'  => 'entry',
					'value' => $field_data
				)
			) );

			return true;
		}

		return false;
	}

	public function handle_submit() {}
}