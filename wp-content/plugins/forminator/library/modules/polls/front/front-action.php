<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front action for polls
 *
 * @since 1.0
 */
class Forminator_Poll_Front_Action extends Forminator_Front_Action {

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = 'poll';

	/**
	 * Response message
	 *
	 * @var array
	 */
	private static $response = array();

	/**
	 * Handle submit
	 *
	 * @since 1.0
	 */
	public function handle_submit() {
		$form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;
		if ( $form_id ) {
			$response = $this->handle_form( $form_id );
			if ( $response && is_array( $response ) ) {
				if ( isset( $response['url'] ) ) {
					$url = apply_filters( 'forminator_poll_submit_url', $response['url'], $form_id );
					wp_safe_redirect( $url );
					exit;
				} else {
					self::$response = $response;
					add_action( 'forminator_poll_post_message', array( $this, 'form_response_message' ) );
				}

			}
		}
	}

	/**
	 * Save entry
	 *
	 * @since 1.0
	 * @return application/json Json response
	 */
	function save_entry() {
		if ( $this->validate_ajax( 'forminator_submit_form', 'POST', 'forminator_nonce' ) ) {
			$form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;
			if ( $form_id ) {
				$response = $this->handle_form( $form_id );
				if ( $response && is_array( $response ) ) {
					if ( ! $response['success'] ) {
						wp_send_json_error( $response );
					} else {
						wp_send_json_success( $response );
					}
				}
			}
		}
	}

	/**
	 * Handle form action
	 *
	 * @since 1.0
	 * @param int $form_id
	 *
	 * @return bool|array
	 */
	private function handle_form( $form_id ) {
		$poll = Forminator_Poll_Form_Model::model()->load( $form_id );
		if ( is_object( $poll ) ) {
			if ( $poll->current_user_can_vote() ) {
				$field_data 	= isset( $_POST[$form_id] ) ? $_POST[$form_id] : false;
				$extra_field 	= isset( $_POST[$form_id .'-extra'] ) ? $_POST[$form_id .'-extra'] : false;
				if ( $field_data && !empty( $field_data ) ) {
					$entry 				= new Forminator_Form_Entry_Model();
					$entry->entry_type 	= $this->entry_type;
					$entry->form_id 	= $form_id;
					$field_data_array = array(
						array(
							'name' 	=> $field_data,
							'value' => '1'
						),
						array(
							'name' 	=> '_forminator_user_ip',
							'value' => Forminator_Geo::get_user_ip()
						)
					);
					if ( $extra_field && !empty( $extra_field ) ) {
						$field_data_array[] = array(
							'name' 	=> 'extra',
							'value' => $extra_field
						);

						$is_spam = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'poll' );

						$entry->is_spam = $is_spam;
					}

					if ( $entry->save() ) {
						$entry->set_fields( $field_data_array );
						$setting   = $poll->settings;

						if ( isset( $setting[ 'results-behav' ] ) && ( $setting[ 'results-behav' ] == 'show_after' ) ) {
							$url       	= $_POST['_wp_http_referer'];
							$render_id 	= $_POST['render_id'];
							$url       	= add_query_arg( array( 'saved' => 'true', 'form_id' => $form_id, 'render_id' => $render_id ), $url );
							$url 	 	= apply_filters( 'forminator_poll_submit_url', $url, $form_id );
							return array(
								'message' => __( "Your vote has been saved", Forminator::DOMAIN ),
								'url'	  => $url,
								'notice'  => 'success',
								'success' => true
							);
						}
						return array(
							'message' => __( "Your vote has been saved", Forminator::DOMAIN ),
							'notice'  => 'success',
							'success' => true
						);
					}
				} else {
					return array(
						'message' => __( "You need to select a poll option", Forminator::DOMAIN ),
						'notice'  => 'error',
						'success' => false
					);
				}
			} else {
				return array(
					'message' => __( "You have already submitted a vote to this poll", Forminator::DOMAIN ),
					'notice'  => 'notice',
					'success' => false
				);
			}
		}
		return false;
	}

	/**
	 * Response message
	 *
	 * @since 1.0
	 */
	public function form_response_message() {
		$response 		= self::$response;
		if ( !empty( $response ) && is_array( $response ) ) {
			?>
			<label class="forminator-label--<?php echo $response['notice']; ?>"><span><?php echo $response['message']; ?></span></label>
			<?php
		}
	}
}