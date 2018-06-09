<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_CForm_Page
 *
 * @since 1.0
 */
class Forminator_CForm_Page extends Forminator_Admin_Page {

	/**
	 * Page number
	 *
	 * @var int
	 */
	protected $page_number = 1;

	/**
	 * Initialize
	 *
	 * @since 1.0
	 */
	public function init() {
		$pagenum				= isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
		$this->page_number 		= max( 1, $pagenum );
		$this->processRequest();
	}

	/**
	 * Process request
	 *
	 * @since 1.0
	 */
	public function processRequest() {
		if ( ! isset( $_POST['forminatorNonce'] ) ) {
			return;
		}

		$nonce = $_POST['forminatorNonce'];
		if ( ! wp_verify_nonce( $nonce, 'forminatorCustomFormRequest' ) ) {
			return;
		}

		$action = $_POST['formninator_action'];
		switch ( $action ) {
			case 'delete':
				$id = $_POST['id'];
				//check if this id is valid and the record is exists
				$model = Forminator_Custom_Form_Model::model()->load( $id );
				if ( is_object( $model ) ) {
					wp_delete_post( $id );
					Forminator_Form_Entry_Model::delete_by_form( $id );
					$form_view 	= Forminator_Form_Views_Model::get_instance();
					$form_view->delete_by_form( $id );
					forminator_update_form_submissions_retention( $id, null, null );
				}
				break;
			case 'clone':
				$id = $_POST['id'];
				//check if this id is valid and the record is exists
				$model = Forminator_Custom_Form_Model::model()->load( $id );
				if ( is_object( $model ) ) {
					//create one
					//reset id
					$model->id = null;

					//update title
					if( isset( $model->settings['formName'] ) ) {
						$model->settings['formName'] = $model->settings['formName'] . ' - ' . __( "Clone", Forminator::DOMAIN );
					}

					//save it to create new record
					$new_id = $model->save( true );
					forminator_clone_form_submissions_retention($id, $new_id);
				}
				break;

			case 'reset-views' :
				$ids = $_POST['ids'];
				if ( !empty( $ids ) ) {
					$form_ids 	= explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						$form_view 	= Forminator_Form_Views_Model::get_instance();
						foreach ( $form_ids as $id ) {
							$model = Forminator_Custom_Form_Model::model()->load( $id );
							if ( is_object( $model ) ) {
								$form_view->delete_by_form( $id );
							}
						}
					}
				}
				break;

			case 'delete-entries' :
				$ids = $_POST['ids'];
				if ( !empty( $ids ) ) {
					$form_ids 	= explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						$form_view 	= Forminator_Form_Views_Model::get_instance();
						foreach ( $form_ids as $id ) {
							$model = Forminator_Custom_Form_Model::model()->load( $id );
							if ( is_object( $model ) ) {
								Forminator_Form_Entry_Model::delete_by_form( $id );
							}
						}
					}
				}
				break;

			case 'delete-forms' :
				$ids = $_POST['ids'];
				if ( !empty( $ids ) ) {
					$form_ids 	= explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						$form_view 	= Forminator_Form_Views_Model::get_instance();
						foreach ( $form_ids as $id ) {
							$model = Forminator_Custom_Form_Model::model()->load( $id );
							if ( is_object( $model ) ) {
								wp_delete_post( $id );
								Forminator_Form_Entry_Model::delete_by_form( $id );
								$form_view->delete_by_form( $id );
								forminator_update_form_submissions_retention( $id, null, null );
							}
						}
					}
				}
				break;
		}
		//todo add messaging as flash
		wp_redirect( admin_url( 'admin.php?page=forminator-cform' ) );
		exit;
	}

	/**
	 * Bulk actions
	 *
	 * @since 1.0
	 * @return array
	 */
	public function bulk_actions() {
		return apply_filters( 'forminator_cform_bulk_actions', array(
			'reset-views' 		=> __( "Reset Views", Forminator::DOMAIN ),
			'delete-entries' 	=> __( "Permanently Delete Entries", Forminator::DOMAIN ),
			'delete-forms' 		=> __( "Delete Forms", Forminator::DOMAIN )
		) );
	}

	/**
	 * Count modules
	 *
	 * @since 1.0
	 * @return int
	 */
	public function countModules() {
		return Forminator_Custom_Form_Model::model()->countAll();
	}


	/**
	 * Return modules
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getModules() {
		$modules 	= array();
		$data    	= $this->getModels();
		$form_view 	= Forminator_Form_Views_Model::get_instance();

		// Fallback
		if( !isset( $data['models'] ) || empty( $data['models'] ) ) return $modules;

		foreach ( $data['models'] as $model ) {
			$modules[] = array(
				"id"      => $model->id,
				"title"   => $model->name,
				"entries" => Forminator_Form_Entry_Model::count_entries( $model->id ),
				"views"   => $form_view->count_views( $model->id ),
				"date"    => date( get_option( 'date_format' ), strtotime( $model->raw->post_date ) )
			);
		}

		return $modules;
	}

	/**
	 * Calculate rate
	 *
	 * @since 1.0
	 * @param $module
	 *
	 * @return float|int
	 */
	public function getRate( $module ) {
		if ( $module["views"] == 0 ) {
			$rate = 0;
		} else {
			$rate = round( ( $module["entries"] * 100 ) / $module["views"], 1 );
		}

		return $rate;
	}

	/**
	 * Return models
	 *
	 * @since 1.0
	 * @return Forminator_Base_Form_Model[]
	 */
	public function getModels() {
		$data = Forminator_Custom_Form_Model::model()->getAll( $this->page_number );

		return $data;
	}

	/**
	 * Pagination
	 *
	 * @since 1.0
	 */
	public function pagination() {
		$count = $this->countModules();
		forminator_list_pagination( $count );
	}
}