<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
class Forminator_Quiz_Form_Model extends Forminator_Base_Form_Model {

	/**
	 * @var string
	 */
	protected $post_type = 'forminator_quizzes';

	/**
	 * @var array
	 */
	public $results = array();

	/**
	 * @var array
	 */
	public $questions = array();

	/**
	 * @var string
	 */
	public $quiz_type = '';

	/**
	 * @since 1.0
	 * @return array
	 */
	public function getMaps() {
		return array(
			array(
				'type'     => 'meta',
				'property' => 'questions',
				'field'    => 'questions'
			),
			array(
				'type'     => 'meta',
				'property' => 'results',
				'field'    => 'results'
			),
			array(
				'type'     => 'meta',
				'property' => 'quiz_type',
				'field'    => 'quiz_type'
			),
		);
	}

	/**
	 * @since 1.0
	 * @param $slug
	 *
	 * @return array|bool
	 */
	public function getRightAnswerForQuestion( $slug ) {
		if( !empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( $question['slug'] == $slug ) {
					$answers = $question['answers'];
					$picked  = null;
					$index   = - 1;
					foreach ( $answers as $k => $answer ) {
						if ( isset( $answer['toggle'] ) && filter_var( $answer['toggle'], FILTER_VALIDATE_BOOLEAN ) == true ) {
							$picked = $answer;
							$index  = $k;
							break;
						}
					}

					return array( $index, $picked );
				}
			}
		}

		return array( false, false );
	}

	/**
	 * Return questions
	 *
	 * @since 1.0
	 * @param $slug
	 *
	 * @return mixed
	 */
	public function getQuestion( $slug ) {
		if( !empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( $question['slug'] == $slug ) {
					return $question;
				}
			}
		}

		return false;
	}

	/**
	 * Return answer
	 *
	 * @since 1.0
	 * @param $slug
	 * @param $index
	 *
	 * @return bool
	 */
	public function getAnswer( $slug, $index ) {
		if( !empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( $question['slug'] == $slug ) {
					$answers = $question['answers'];

					return $answers[ $index ];
				}
			}
		}

		return false;
	}

	/**
	 * Get result from answer
	 *
	 * @since 1.0
	 * @param $slug
	 * @param $index
	 *
	 * @return mixed
	 */
	public function getResultFromAnswer( $slug, $index ) {
		$this->getAnswer( $slug, $index );
		$answer = $this->getAnswer( $slug, $index );

		if( isset( $answer['result'] ) ) {
			return $answer['result'];
		}

		return false;
	}

	/**
	 * @since 1.0
	 * @return mixed
	 */
	public function getPriority() {
		if( isset( $this->settings['priority_order'][0]['slug'] ) ) {
			return $this->settings['priority_order'][0]['slug'];
		}

		return false;
	}

	/**
	 * Return results
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getResults() {
		$results = array();

		if( empty( $this->results ) ) return $results;

		foreach ( $this->results as $slug => $result ) {
			$results[] = $result;
		}

		return $results;
	}

	/**
	 * Get result
	 *
	 * @since 1.0
	 * @param $slug
	 *
	 * @return mixed|null
	 */
	public function getResult( $slug ) {
		if( !empty( $this->results ) ) {
			foreach ( $this->results as $result ) {
				if ( $result['slug'] == $slug ) {
					return $result;
				}
			}
		}

		return null;
	}

	/**
	 * Load preview
	 *
	 * @since 1.0
	 * @param $id
	 * @param $data
	 *
	 * @return bool|Forminator_Base_Form_Model
	 */
	public function load_preview( $id, $data ) {
		$formModel = $this->load( $id, true );

		// If bool, abort
		if( is_bool( $formModel ) ) return false;

		$formModel->clearFields();
		$formModel->setVarInArray( 'name', 'formName', $data );

		//build the field
		$questions = array();
		if( isset( $data['questions'] ) ) {
			$questions = $data['questions'];
			unset( $data['questions'] );
		}

		$formModel->questions = $questions;

		//build the settings
		if( isset( $data['settings'] ) ) {
			$settings            = $data['settings'];
			$formModel->settings = $settings;
		}

		return $formModel;
	}

	/**
	 * @since 1.0
	 * @param int|string $class_name
	 *
	 * @return Forminator_Base_Form_Model
	 */
	public static function model( $class_name = __CLASS__ ) {
		return parent::model( $class_name );
	}
}