<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
class Forminator_Custom_Form_Model extends Forminator_Base_Form_Model {

	protected $post_type = 'forminator_forms';

	/**
	 * @param int|string $class_name
	 *
	 * @since 1.0
	 * @return Forminator_Custom_Form_Model
	 */
	public static function model( $class_name = __CLASS__ ) {
		return parent::model( $class_name );
	}

	/**
	 * Get field
	 *
	 * @since 1.0
	 * @param $id
	 *
	 * @return array|Forminator_Form_Field|null
	 */
	public function getField( $id ) {
		foreach ( $this->getFields() as $field ) {
			if ( $field->slug == $id ) {
				return $field->toArray();
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
	 * @return bool|Forminator_Custom_Form_Model
	 */
	public function load_preview( $id, $data ) {
		$formModel = $this->load( $id, true );

		// If bool, abort
		if( is_bool( $formModel ) ) return false;

		$formModel->clearFields();
		$formModel->setVarInArray( 'name', 'formName', $data );

		//build the field
		$fields = array();
		if( isset( $data['wrappers'] ) ) {
			$fields = $data['wrappers'];
			unset( $data['wrappers'] );
		}

		//build the settings
		if( isset( $data['settings'] ) ) {
			$settings            = $data['settings'];
			$formModel->settings = $settings;
		}

		if( !empty( $fields ) ) {
			foreach ( $fields as $row ) {
				foreach ( $row['fields'] as $f ) {
					$field         = new Forminator_Form_Field_Model();
					$field->formID = $row['wrapper_id'];
					$field->slug   = $f['element_id'];
					unset( $f['element_id'] );
					$field->import( $f );
					$formModel->addField( $field );
				}
			}
		}

		return $formModel;
	}

	/**
	 * Check if can show the form
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function form_is_visible() {
		$form_settings 	= $this->settings;
		$can_show		= true;

		if( isset( $form_settings[ 'logged-users' ] )  && !empty( $form_settings[ 'logged-users' ] ) ) {
			if ( filter_var( $form_settings[ 'logged-users' ], FILTER_VALIDATE_BOOLEAN ) && !is_user_logged_in() ) {
				$can_show = false;
			}
		}
		if ( $can_show ) {
			if( isset( $form_settings[ 'form-expire' ] ) ) {
				if ( $form_settings[ 'form-expire' ] == 'submits' ) {
					if ( isset( $form_settings[ 'expire_submits' ] ) && !empty( $form_settings[ 'expire_submits' ] ) ) {
						$submits 		= intval( $form_settings[ 'expire_submits' ] );
						$total_entries 	= Forminator_Form_Entry_Model::count_entries( $this->id );
						if ( $total_entries >= $submits ) {
							$can_show = false;
						}
					}
				} else if ( $form_settings[ 'form-expire' ] == 'date' ) {
					if ( isset( $form_settings[ 'expire_date' ] ) && !empty( $form_settings[ 'expire_date' ] ) ) {
						$expire_date 	=  strtotime( $form_settings[ 'expire_date' ] );
						$current_date 	=  strtotime( "now" );
						if ( $current_date > $expire_date ) {
							$can_show = false;
						}
					}
				}
			}
		}

		return apply_filters( 'forminator_cform_form_is_visible', $can_show, $this->id, $form_settings );
	}
}