<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
class Forminator_Poll_Form_Model extends Forminator_Base_Form_Model {
	protected $post_type = 'forminator_polls';

	/**
	 * Whether to check form access
	 *
	 * @since 1.0.5
	 *
	 * @var bool
	 */
	protected $check_access = true;

	/**
	 * @param int|string $class_name
	 *
	 * @since 1.0
	 * @return self
	 */
	public static function model( $class_name = __CLASS__ ) {
		return parent::model( $class_name );
	}

	/**
	 * Load preview
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return bool|Forminator_Base_Form_Model
	 */
	public function load_preview( $id, $data ) {
		$formModel = $this->load( $id, true );

		// If bool, abort
		if ( is_bool( $formModel ) ) {
			return false;
		}

		$formModel->clearFields();
		$formModel->setVarInArray( 'name', 'formName', $data );

		//build the field
		$fields = array();
		if ( isset( $data['answers'] ) ) {
			$fields = $data['answers'];
			unset( $data['answers'] );
		}

		//build the settings
		if ( isset( $data['settings'] ) ) {
			$settings            = $data['settings'];
			$formModel->settings = $settings;
		}

		// Set fields
		foreach ( $fields as $f ) {
			$field         = new Forminator_Form_Field_Model();
			$field->formID = isset( $f['wrapper_id'] ) ? $f['wrapper_id'] : $f['title'];
			$field->slug   = isset( $f['element_id'] ) ? $f['element_id'] : $f['title'];
			$field->import( $f );
			$formModel->addField( $field );
		}

		$formModel->check_access = false;

		return $formModel;
	}

	/**
	 * Check if the vote clause is set up and if a user can vote again
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function current_user_can_vote() {
		/**
		 * Added condition for poll access.
		 *
		 * @since 1.0.5
		 */
		if ( $this->check_access ) {
			$settings = $this->settings;
			$user_ip  = Forminator_Geo::get_user_ip();
			if ( isset( $settings['enable-votes-limit'] ) ) {
				if ( ! empty( $settings['enable-votes-limit'] ) && $settings['enable-votes-limit'] == 'true' ) {
					if ( isset( $settings['vote_limit_input'] ) ) {
						$duration           = $settings['vote_limit_input'];
						$vote_limit_options = 'm';
						if ( isset( $settings['vote_limit_options'] ) ) {
							$vote_limit_options = $settings['vote_limit_options'];
						}

						switch ( $vote_limit_options ) {
							case 'h':
								$interval = "INTERVAL $duration HOUR";
								break;
							case 'd':
								$interval = "INTERVAL $duration DAY";
								break;
							case 'W':
								$interval = "INTERVAL $duration WEEK";
								break;
							case 'M':
								$interval = "INTERVAL $duration MONTH";
								break;
							case 'Y':
								$interval = "INTERVAL $duration YEAR";
								break;
							default:
								$interval = "INTERVAL $duration MINUTE";
								break;
						}
						$last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $this->id, $user_ip );
						if ( $last_entry ) {
							$can_vote = Forminator_Form_Entry_Model::check_entry_date_by_ip_and_form( $this->id, $user_ip, $last_entry, $interval );
							if ( $can_vote ) {
								return true;
							} else {
								return false;
							}
						} else {
							return true;
						}
					}
				} else {
					$last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $this->id, $user_ip );
					if ( $last_entry ) {
						return false;
					}
				}
			} else {
				$last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $this->id, $user_ip );
				if ( $last_entry ) {
					return false;
				}
			}

		}
		return true;
	}

	/**
	 * Overridden load function to check element_id of answers for older poll
	 * Backward compat for <= 1.0.4
	 * which is forminator poll doesnt have element_id on poll answers
	 *
	 * @since 1.0.5
	 *
	 * @param      $id
	 * @param bool $callback
	 *
	 * @return bool|Forminator_Poll_Form_Model
	 */
	public function load( $id, $callback = false ) {
		$model = parent::load( $id, $callback );

		// callback means load latest post and replace data,
		// so we dont need to add element_id since its must be try to loading preview
		if ( ! $callback ) {
			if ( $model instanceof Forminator_Poll_Form_Model ) {
				// patch for backward compat
				return $this->maybe_add_element_id_on_answers( $model );
			}
		}

		return $model;
	}

	/**
	 * Add Element id on answers that doesnt have it
	 *
	 * @since 1.0.5
	 *
	 * @param Forminator_Poll_Form_Model $model
	 *
	 * @return Forminator_Poll_Form_Model
	 */
	private function maybe_add_element_id_on_answers( Forminator_Poll_Form_Model $model ) {
		$answers                   = $model->getFieldsAsArray();
		$is_need_to_add_element_id = false;

		foreach ( $answers as $key => $answer ) {
			if ( ! isset( $answer['element_id'] ) || ! $answer['element_id'] ) {
				$is_need_to_add_element_id = true;
				break;
			}
		}

		if ( $is_need_to_add_element_id ) {

			// get max element id here
			$max_element_id = 0;
			foreach ( $answers as $answer ) {
				if ( isset( $answer['element_id'] ) && $answer['element_id'] ) {
					$element_id = trim( str_replace( 'answer-', '', $answer['element_id'] ) );
					if ( $element_id > $max_element_id ) {
						$max_element_id = $element_id;
					}
				}
			}
			foreach ( $answers as $key => $answer ) {
				if ( ! isset( $answer['element_id'] ) || ! $answer['element_id'] ) {
					$max_element_id ++;
					$answers[ $key ]['element_id'] = 'answer-' . $max_element_id; // start from 1
					$answers[ $key ]['id']         = 'answer-' . $max_element_id; // start from 1
				}
			}

			$model->clearFields();
			foreach ( $answers as $answer ) {
				$field         = new Forminator_Form_Field_Model();
				$field->formID = $model->id;
				$field->slug   = $answer['id'];
				unset( $answer['id'] );
				$field->import( $answer );
				$model->addField( $field );
			}

			return $this->resave_and_reload( $model );
		}

		return $model;
	}

	/**
	 * Resave model and then load to return new model
	 *
	 * @since 1.0.5
	 *
	 * @param Forminator_Poll_Form_Model $model
	 *
	 * @return Forminator_Poll_Form_Model
	 */
	private function resave_and_reload( Forminator_Poll_Form_Model $model ) {
		$model->save();
		return $model;

	}

	/**
	 * Get Fields as array with `$key` as key of array and `$pluck_key` as $value with `$default` as fallback
	 *
	 * @since 1.0.5
	 *
	 * @param  string      $pluck_key
	 * @param  string|null $key
	 * @param null         $default
	 *
	 * @return array
	 */
	public function pluck_fields_array( $pluck_key, $key = null, $default = null ) {
		$fields_with_key = array();
		$fields          = $this->getFieldsAsArray();

		foreach ( $fields as $field ) {
			if ( '*' === $pluck_key ) {
				$field_value = $field;
			} else {
				if ( isset( $field[ $pluck_key ] ) ) {
					$field_value = $field[ $pluck_key ];
				} else {
					$field_value = $default;
				}
			}

			if ( ! is_null( $key ) ) {
				if ( isset( $field[ $key ] ) ) {
					$fields_with_key[ $field[ $key ] ] = $field_value;
				} else {
					$fields_with_key[] = $field_value;
				}
			} else {
				$fields_with_key[] = $field_value;
			}
		}

		return $fields_with_key;
	}
}