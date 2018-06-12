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
	 * @since 1.1 Refactor $_POST to use `get_post_data`
	 */
	public function handle_submit() {
		$post_data = $this->get_post_data();
		$form_id = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false;
		if ( $form_id ) {

			/**
			 * Action called before full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 */
			do_action( 'forminator_polls_before_handle_submit', $form_id );

			$response = $this->handle_form( $form_id );

			/**
			 * Filter submit response
			 *
			 * @since 1.0.2
			 *
			 * @param array $response - the post response
			 * @param int $form_id - the form id
			 *
			 * @return array $response
			 */
			$response = apply_filters( 'forminator_polls_submit_response', $response, $form_id  );

			/**
			 * Action called after full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 * @param array $response - the post response
			 */
			do_action( 'forminator_polls_after_handle_submit', $form_id, $response );

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
	 * @since 1.1 refactor $_POST to `get_post_data`
	 * @return void /json Json response
	 */
	public function save_entry() {
		if ( $this->validate_ajax( 'forminator_submit_form', 'POST', 'forminator_nonce' ) ) {
			$post_data = $this->get_post_data();
			$form_id = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false;
			if ( $form_id ) {

				/**
				 * Action called before poll ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 */
				do_action( 'forminator_polls_before_save_entry', $form_id );

				$response = $this->handle_form( $form_id );

				/**
				 * Filter ajax response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $form_id - the form id
				 *
				 * @return array $response
				 */
				$response = apply_filters( 'forminator_polls_ajax_submit_response', $response, $form_id );


				/**
				 * Action called after form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param array $response - the post response
				 */
				do_action( 'forminator_polls_after_save_entry', $form_id, $response );

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
			$user_can_vote = $poll->current_user_can_vote();

			/**
			 * Filter to check if current user can vote
			 *
			 * @since 1.0.2
			 *
			 * @param bool $user_can_vote - if can vote depending on above conditions
			 * @param int  $form_id       - the form id
			 *
			 * @return bool $user_can_vote - true|false
			 */
			$user_can_vote = apply_filters( 'forminator_poll_handle_form_user_can_vote', $user_can_vote, $form_id );

			$post_data = $this->get_post_data();

			if ( $user_can_vote ) {
				$field_data  = isset( $post_data[ $form_id ] ) ? $post_data[ $form_id ] : false;
				$extra_field = isset( $post_data[ $form_id . '-extra' ] ) ? $post_data[ $form_id . '-extra' ] : false;
				if ( $field_data && ! empty( $field_data ) ) {
					$entry             = new Forminator_Form_Entry_Model();
					$entry->entry_type = $this->entry_type;
					$entry->form_id    = $form_id;
					// get fields labels
					$fields_labels    = $poll->pluck_fields_array( 'title', 'element_id', '1' );
					$field_data_array = array(
						array(
							'name'  => $field_data,
							'value' => isset( $fields_labels[ $field_data ] ) ? $fields_labels[ $field_data ] : '1',
						),
						array(
							'name'  => '_forminator_user_ip',
							'value' => Forminator_Geo::get_user_ip(),
						),
					);
					if ( $extra_field && ! empty( $extra_field ) ) {
						$field_data_array[] = array(
							'name'  => 'extra',
							'value' => $extra_field,
						);

						/**
						 * Handle spam protection
						 * Add-ons use this filter to check if content has spam data
						 *
						 * @since 1.0.2
						 *
						 * @param bool false - defauls to false
						 * @param array  $field_data_array - the entry data
						 * @param int    $form_id          - the form id
						 * @param string $form_type        - the form type. In this case defaults to 'poll'
						 *
						 * @return bool true|false
						 */
						$is_spam = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'poll' );

						$entry->is_spam = $is_spam;
					}

					if ( $entry->save() ) {

						/**
						 * Filter saved data before persisted into the database
						 *
						 * @since 1.0.2
						 *
						 * @param array $field_data_array - the entry data
						 * @param int   $form_id          - the form id
						 *
						 * @return array $field_data_array
						 */
						$field_data_array = apply_filters( 'forminator_polls_submit_field_data', $field_data_array, $form_id );

						/**
						 * Action called before setting fields to database
						 *
						 * @since 1.0.2
						 *
						 * @param Forminator_Form_Entry_Model $entry            - the entry model
						 * @param int                         $form_id          - the form id
						 * @param array                       $field_data_array - the entry data
						 *
						 */
						do_action( 'forminator_polls_submit_before_set_fields', $entry, $form_id, $field_data_array );

						$entry->set_fields( $field_data_array );
						$setting = $poll->settings;

						if ( isset( $setting['results-behav'] ) && ( 'show_after' === $setting['results-behav'] || 'link_on' === $setting['results-behav'] ) ) {
							$url       = $post_data['_wp_http_referer'];
							$render_id = $post_data['render_id'];
							$url       = add_query_arg(
								array(
									'saved'     => 'true',
									'form_id'   => $form_id,
									'render_id' => $render_id,
								),
								$url
							);
							$url       = apply_filters( 'forminator_poll_submit_url', $url, $form_id );
							$response  = array(
								'message' => __( 'Your vote has been saved', Forminator::DOMAIN ),
								'notice'  => 'success',
								'success' => true,
							);

							if ( ! isset( $setting['enable-ajax'] ) || empty( $setting['enable-ajax'] ) ) {
								$is_ajax_enabled = false;
							} else {
								$is_ajax_enabled = filter_var( $setting['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
							}

							if ( $is_ajax_enabled ) {
								// ajax enabled send result data to front end
								$response['chart_data']  = $this->get_chart_data( $poll );
								$response['back_button'] = '<button class="forminator-button" type="button">' . __( 'Back To poll', Forminator::DOMAIN ) . '</button>';
							} else {
								// its not ajax enabled, send url result to front end
								$response['url'] = $url;
							}

							return $response;
						}

						return array(
							'message' => __( 'Your vote has been saved', Forminator::DOMAIN ),
							'notice'  => 'success',
							'success' => true,
						);
					}
				} else {
					return array(
						'message' => __( "You need to select a poll option", Forminator::DOMAIN ),
						'notice'  => 'error',
						'success' => false,
					);
				}
			} else {
				return array(
					'message' => __( "You have already submitted a vote to this poll", Forminator::DOMAIN ),
					'notice'  => 'notice',
					'success' => false,
				);
			}
		}

		return false;
	}

	/**
	 * Get Chart data of Poll
	 *
	 * @param Forminator_Poll_Form_Model $poll
	 *
	 * @return array
	 */
	private function get_chart_data( Forminator_Poll_Form_Model $poll ) {
		$chart_colors         = apply_filters( 'forminator_poll_chart_color', Forminator_Poll_Front::$default_chart_colors );
		$default_chart_colors = $chart_colors;
		$chart_datas          = array();
		$chart_datas[]        = array(
			__( 'Question', Forminator::DOMAIN ),
			__( 'Results', Forminator::DOMAIN ),
			array( 'role' => 'style' ),
			array( 'role' => 'annotation' ),
		);

		$form_settings        = $poll->settings;
		$number_votes_enabled = false;
		if ( isset( $settings['show-votes-count'] ) && $form_settings['show-votes-count'] ) {
			$number_votes_enabled = true;
		}

		$fields_array = $poll->getFieldsAsArray();
		$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $poll->id, $fields_array );
		$fields       = $poll->getFields();
		if ( ! is_null( $fields ) ) {
			foreach ( $fields as $field ) {
				$annotation = '';
				$label = addslashes( $field->title );

				if ( empty( $chart_colors ) ) {
					$chart_colors = $default_chart_colors;
				}
				$color   = array_shift( $chart_colors );
				$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
				$entries = 0;
				if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $slug ];
				}
				if ( $number_votes_enabled ) {
					$annotation = $entries . __( ' vote(s)', Forminator::DOMAIN );
				}
				$style = 'color: ' . $color;

				$chart_datas[] = array(
					(string) $label,
					(int) $entries,
					(string) $style,
					(string) $annotation,
				);
			}

		}

		return $chart_datas;
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
			<label class="forminator-label--<?php echo esc_attr( $response['notice'] ); ?>"><span><?php echo $response['message']; // WPCS: XSS ok. ?></span></label>
			<?php
		}
	}
}