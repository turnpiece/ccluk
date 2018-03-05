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
	 * @param int|string $class_name
	 *
	 * @since 1.0
	 * @return Forminator_Base_Form_Model
	 */
	public static function model( $class_name = __CLASS__ ) {
		return parent::model( $class_name );
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
		$fields = array();
		if( isset( $data['answers'] ) ) {
			$fields = $data['answers'];
			unset( $data['answers'] );
		}

		//build the settings
		if( isset( $data['settings'] ) ) {
			$settings = $data['settings'];
			$formModel->settings = $settings;
		}

		// Set fields
		foreach ( $fields as $f ) {
			$field         = new Forminator_Form_Field_Model();
			$field->formID = isset($f['wrapper_id']) ? $f['wrapper_id'] : $f['title'];
			$field->slug   = isset($f['element_id']) ? $f['element_id'] : $f['title'];
			unset( $f['element_id'] );
			$field->import( $f );
			$formModel->addField( $field );
		}

		return $formModel;
	}

	/**
	 * Check if the vote clause is set up and if a user can vote again
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function current_user_can_vote() {
		$settings 	= $this->settings;
		$user_ip 	= Forminator_Geo::get_user_ip();
		if ( isset( $settings['enable-votes-limit'] ) ) {
			if ( !empty( $settings['enable-votes-limit'] ) && $settings['enable-votes-limit'] == 'true' ) {
				if ( isset( $settings['vote_limit_input'] ) ) {
					$duration 			= $settings['vote_limit_input'];
					$vote_limit_options = 'm';
					if ( isset( $settings['vote_limit_options'] ) ) {
						$vote_limit_options = $settings['vote_limit_options'];
					}

					switch ( $vote_limit_options ) {
						case 'h':
							$interval =  "INTERVAL $duration HOUR";
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
						$can_vote 	= Forminator_Form_Entry_Model::check_entry_date_by_ip_and_form( $this->id, $user_ip, $last_entry, $interval );
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
		return true;
	}
}