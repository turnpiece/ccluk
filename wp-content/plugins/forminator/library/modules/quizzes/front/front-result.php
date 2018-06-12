<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front render class for custom forms
 */
class Forminator_QForm_Result extends Forminator_Result {
  /**
	 * Forminator_Quizz_Front_Action constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'wp_ajax_forminator_result', array( $this, 'forminator_result' ) );
		add_action( 'wp_ajax_nopriv_forminator_result', array( $this, 'forminator_result' ) );

		// quizzes for default, use it for other post type
		$this->set_post_type( 'quizzes' );
	}

  public function forminator_result() {
    echo 'here';
  }

	public function get_description() {

			$right = 0;
	    $total = 0;

	  	if ( 0 !== $this->entry_id ) {
	      $entry = new Forminator_Form_Entry_Model( $this->entry_id );
	      $data = $entry->get( $this->entry_id );

	      $total = count( $data->meta_data['entry']['value'] );

	      if ( $this->get_post_type() === $data->entry_type ) {
	        foreach ($data->meta_data['entry']['value'] as $key => $value) {
	            if ( true === $value['isCorrect'] ) {
	              $right++;
	            }
	        }
	      }

			}

			$description = sprintf( __( 'I got %1$s/%2$s on %3$s quiz!', Forminator::DOMAIN ) , esc_html($right) , esc_html($total) , get_the_title() );

			return $description;

	}
}

register_deactivation_hook( __FILE__, array( 'Forminator_QForm_Result', 'flush_rewrites' ) );
register_activation_hook( __FILE__, array( 'Forminator_QForm_Result', 'flush_rewrites' ) );