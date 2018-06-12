<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizz_Page
 *
 * @since 1.0
 */
class Forminator_Quizz_Page extends Forminator_Admin_Page {

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
		$this->processRequest();
	}

	/**
	 * Count modules
	 *
	 * @since 1.0
	 * @return int
	 */
	public function countModules() {
		$pagenum		   = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0; // WPCS: CSRF OK
		$this->page_number = max( 1, $pagenum );

		return Forminator_Quiz_Form_Model::model()->countAll();
	}

	/**
	 * Return models
	 *
	 * @return Forminator_Base_Form_Model[]
	 *
	 * @since 1.0
	 */
	public function getModels() {
		$data = Forminator_Quiz_Form_Model::model()->getAll( $this->page_number );

		return $data;
	}

	/**
	 * Return admin edit url
	 *
	 * @since 1.0
	 * @param $type
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getAdminEditUrl( $type, $id ) {
		if ( 'nowrong' === $type ) {
			return admin_url( 'admin.php?page=forminator-nowrong-wizard&id=' . $id );
		} else {
			return admin_url( 'admin.php?page=forminator-knowledge-wizard&id=' . $id );
		}
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

		$nonce = $_POST['forminatorNonce']; // WPCS: CSRF OK
		if ( ! wp_verify_nonce( $nonce, 'forminatorQuizFormRequest' ) ) {
			return;
		}

		$action = $_POST['formninator_action'];
		switch ( $action ) {
			case 'delete':
				$id = $_POST['id'];
				//check if this id is valid and the record is exists
				$model = Forminator_Quiz_Form_Model::model()->load( $id );
				if ( is_object( $model ) ) {
					wp_delete_post( $id );
					Forminator_Form_Entry_Model::delete_by_form( $id );
				}
				break;
			case 'clone':
				$id = $_POST['id'];
				//check if this id is valid and the record is exists
				$model = Forminator_Quiz_Form_Model::model()->load( $id );

				if ( is_object( $model ) ) {
					//create one
					//reset id
					$model->id = null;

					//update title
					if( isset( $model->settings['formName'] ) ) {
						$model->settings['formName'] = $model->settings['formName'] . ' - ' . __( "Clone", Forminator::DOMAIN );
					}

					//save it to create new record
					$model->save( true );
				}
				break;
			case 'reset-views' :
				$ids = $_POST['ids'];
				if ( !empty( $ids ) ) {
					$form_ids 	= explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						$form_view 	= Forminator_Form_Views_Model::get_instance();
						foreach ( $form_ids as $id ) {
							$model = Forminator_Quiz_Form_Model::model()->load( $id );
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
							$model = Forminator_Quiz_Form_Model::model()->load( $id );
							if ( is_object( $model ) ) {
								Forminator_Form_Entry_Model::delete_by_form( $id );
							}
						}
					}
				}
				break;

			case 'delete-quizzes' :
				$ids = $_POST['ids'];
				if ( !empty( $ids ) ) {
					$form_ids 	= explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						$form_view 	= Forminator_Form_Views_Model::get_instance();
						foreach ( $form_ids as $id ) {
							$model = Forminator_Quiz_Form_Model::model()->load( $id );
							if ( is_object( $model ) ) {
								wp_delete_post( $id );
								Forminator_Form_Entry_Model::delete_by_form( $id );
								$form_view->delete_by_form( $id );
							}
						}
					}
				}
				break;
		}
		//todo add messaging as flash
		wp_redirect( admin_url( 'admin.php?page=forminator-quiz' ) );
		exit;
	}

	/**
	 * Get modules
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getModules() {
		$modules 	= array();
		$data    	= $this->getModels();
		$form_view 	= Forminator_Form_Views_Model::get_instance();

		if( !isset( $data['models'] ) || empty( $data['models'] ) ) return $modules;

		foreach ( $data['models'] as $model ) {
			$modules[] = array(
				"id"              => $model->id,
				"title"           => $model->name,
				"entries"         => Forminator_Form_Entry_Model::count_entries( $model->id ),
				"last_entry_time" => forminator_get_latest_entry_time_by_form_id( $model->id ),
				"views"           => $form_view->count_views( $model->id ),
				'type'            => $model->quiz_type,
				"date"            => date( get_option( 'date_format' ), strtotime( $model->raw->post_date ) ),
			);
		}

		return $modules;
	}

	/**
	 * Return rate
	 *
	 * @since 1.0
	 * @param $module
	 *
	 * @return float|int
	 */
	public function getRate( $module ) {
		if ( $module['views'] > 0 ) {
			$rate = round( ( $module["entries"] * 100 ) / $module["views"], 1 );
		} else {
			$rate = 0;
		}

		return $rate;
	}

	/**
	 * Bulk actions
	 *
	 * @since 1.0
	 * @return array
	 */
	public function bulk_actions() {
		return apply_filters( 'forminator_quizzes_bulk_actions', array(
			'reset-views' 		=> __( "Reset Views", Forminator::DOMAIN ),
			'delete-entries' 	=> __( "Permanently Delete Entries", Forminator::DOMAIN ),
			'delete-quizzes' 	=> __( "Delete Quizzes", Forminator::DOMAIN )
		) );
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