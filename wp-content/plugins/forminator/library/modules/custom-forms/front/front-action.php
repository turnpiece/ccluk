<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front ajax for custom forms
 *
 * @since 1.0
 */
class Forminator_CForm_Front_Action extends Forminator_Front_Action {

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = 'custom-forms';

	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Response message
	 *
	 * @var array
	 */
	private static $response = array();

	/**
	 * Render ID of Form
	 *
	 * @var array
	 */
	private static $render_id = '';

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return Forminator_Front_Action
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Do PayPal backend check
	 *
	 * @since 1.0
	 * @return array
	 */
	public function handle_paypal( $payment_id ) {
		$payment_total 	= isset( $_POST['payment_total'] ) ? sanitize_text_field( $_POST['payment_total'] ) : false;
		$paypal        	= new Forminator_Paypal_Express();

		$result 		= $paypal->paypal_check( $payment_id, $payment_total );

		$response 		= array(
			'success' => $result ? true : false,
		);
		return $response;
	}

	/**
	 * Handle submit
	 *
	 * @since 1.0
	 */
	public function handle_submit() {
		$form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;

		if ( $form_id ) {
			/**
			 * Action called before full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 */
			do_action( 'forminator_custom_form_before_handle_submit', $form_id );

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
			$response = apply_filters( 'forminator_custom_form_submit_response', $response, $form_id  );

			/**
			 * Action called after full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 * @param array $response - the post response
			 */
			do_action( 'forminator_custom_form_after_handle_submit', $form_id, $response );

			if ( $response && is_array( $response ) ) {
				if ( ! empty( $response ) ) {
					self::$response = $response;
					add_action( 'forminator_cform_post_message', array( $this, 'form_response_message' ), 10, 2 );
					if ( ! $response['success'] && isset( $response['errors'] ) ) {
						add_action( 'wp_footer', array( $this, 'footer_message' ) );
					}
					if ( $response['success'] ) {
						if ( isset( $response['url'] ) ) {
							if ( wp_redirect( $response['url'] ) ) {
								exit;
							}
						}
					}
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
			$form_id    = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;
			$payment_id = isset( $_POST['payment_id'] ) ? sanitize_text_field( $_POST['payment_id'] ) : false;

			if ( $form_id ) {

				/**
				 * Action called before form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param string $type - the submit type. In this case submit
				 */
				do_action( 'forminator_custom_form_before_save_entry', $form_id, 'submit'  );

				$response = $this->handle_form( $form_id );

				/**
				 * Filter ajax response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $form_id - the form id
				 * @param string $type - the submit type. In this case submit
				 *
				 * @return array $response
				 */
				$response = apply_filters( 'forminator_custom_form_ajax_submit_response', $response, $form_id, 'submit' );


				/**
				 * Action called after form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param array $response - the post response
				 * @param string $type - the submit type. In this case submit
				 */
				do_action( 'forminator_custom_form_after_save_entry', $form_id, $response, 'submit'  );

				if ( ! $response['success'] && isset( $response['errors'] ) ) {
					wp_send_json_error( $response );
				} else {
					wp_send_json_success( $response );
				}
			} elseif ( $payment_id ) {

				/**
				 * Action called before form payment
				 *
				 * @since 1.0.2
				 *
				 * @param int $payment_id - the payment id
				 * @param string $type - the submit type. In this case payment
				 */
				do_action( 'forminator_custom_form_before_save_entry', $payment_id, 'payment' );

				$response = $this->handle_paypal( $payment_id );

				/**
				 * Filter ajax payment response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $payment_id - the payment id
				 * @param string $type - the submit type. In this case payment
				 *
				 * @return array $response
				 */
				$response = apply_filters( 'forminator_custom_form_ajax_submit_response', $response, $payment_id, 'payment' );

				/**
				 * Action called after form payment
				 *
				 * @since 1.0.2
				 *
				 * @param int $payment_id - the payment id
				 * @param array $response - the post response
				 * @param string $type - the submit type. In this case payment
				 */
				do_action( 'forminator_custom_form_after_save_entry', $payment_id, $response, 'payment' );

				if ( ! $response['success'] && isset( $response['errors'] ) ) {
					wp_send_json_error( $response );
				} else {
					wp_send_json_success( $response );
				}
			}
		}
	}

	/**
	 * Handle form
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 *
	 * @return array|bool
	 */
	public function handle_form( $form_id ) {
		$custom_form = Forminator_Custom_Form_Model::model()->load( $form_id );
		if ( is_object( $custom_form ) ) {
			$setting    = $custom_form->settings;
			$can_submit = $custom_form->form_is_visible();

			if ( isset( $setting['logged-users'] ) && $setting['logged-users'] ) {
				$can_submit = is_user_logged_in();
			}

			/**
			 * Filter to check if current user can submit the form
			 *
			 * @since 1.0.2
			 *
			 * @param bool $can_submit - if can submit depending on above conditions
			 * @param int $form_id - the form id
			 *
			 * @return bool $can_submit - true|false
			 */
			$can_submit = apply_filters( 'forminator_custom_form_handle_form_user_can_submit', $can_submit, $form_id );

			if ( $can_submit ) {
				$submit_errors     = array();
				$entry             = new Forminator_Form_Entry_Model();
				$entry->entry_type = $this->entry_type;
				$entry->form_id    = $form_id;
				$field_data_array  = array();
				$fields            = $custom_form->getFields();
				$field_suffix      = Forminator_Form_Entry_Model::field_suffix();
				$field_forms       = forminator_fields_toArray();
				$product_fields    = array();
				if ( ! is_null( $fields ) ) {
					$response = array(
						'message' => __( "Error saving form", Forminator::DOMAIN ),
						'errors'  => array(),
						'success' => false,
					);
					$ignored_field_types 	= Forminator_Form_Entry_Model::ignored_fields();
					foreach ( $fields as $field ) {
						$field_array 	= $field->toFormattedArray();
						$field_type 	= $field_array[ "type" ];
						if ( in_array( $field_type, $ignored_field_types ) ) {
							continue;
						}
						if ( isset( $field->slug ) ) {
							$field_id     = Forminator_Field::get_property( 'element_id', $field_array );
							$mod_field_id = $field_id;
							$field_data   = array();
							$field_type   = $field_array["type"];
							$post_file    = false;
							if ( ! isset( $_POST[ $field_id ] ) ) {
								foreach ( $field_suffix as $suffix ) {
									$mod_field_id = $field_id . '-' . $suffix;
									if ( isset( $_POST[ $mod_field_id ] ) ) {
										$field_data[ $suffix ] = $_POST[ $mod_field_id ];
									} elseif ( isset( $_FILES[ $mod_field_id ] ) ) {
										if ( $field_type == "postdata" && $suffix == 'post-image' ) {
											$post_file = $mod_field_id;
										}
									}
								}
								if ( $field_type == "postdata" ) {
									$custom_vars = Forminator_Field::get_property( 'custom_vars', $field_array );
									if ( ! empty( $custom_vars ) ) {
										foreach ( $custom_vars as $variable ) {
											$value    = ! empty( $variable['value'] ) ? $variable['value'] : sanitize_title( $variable['label'] );
											$input_id = $field_id . '-post_meta-' . $value;
											$label    = $variable['label'];
											if ( isset( $_POST[ $input_id ] ) ) {
												$field_data['post-custom'][] = array(
													'label' => $label,
													'value' => $_POST[ $input_id ],
													'key'   => $value,
												);
											}
										}
									}
								}
							} else {
								$field_data = $_POST[ $field_id ];
							}

							if ( isset( $field_forms[ $field_type ] ) && ! empty( $field_forms[ $field_type ] ) ) {
								$form_field_obj = $field_forms[ $field_type ];
								if ( $field_type == "upload" ) {
									$upload_data = $this->handle_file_upload( $field_id );
									if ( $upload_data ) {
										$field_data['file'] = $upload_data;
									} else {
										$field_data = '';
									}
								}
								if ( $field_type == "postdata" ) {
									if ( $post_file ) {
										$post_image = $form_field_obj->upload_post_image( $field_array, $post_file );
										if ( is_array( $post_image ) && $post_image['attachment_id'] > 0 ) {
											$field_data['post-image'] = $post_image;
										} else {
											$field_data['post-image'] = '';
										}
									}

								}
								if ( ! empty( $field_data ) ) {
									//Validate data
									if ( ! $form_field_obj->is_hidden( $field_array, $_POST ) ) {
										$form_field_obj->validate( $field_array, $field_data );
									}

									$valid_response = $form_field_obj->is_valid_entry();
									if ( ! is_array( $valid_response ) ) {

										/**
										 * Sanitize data
										 *
										 * @since 1.0.2
										 *
										 * @param array $field
										 * @param array|string $data - the data to be sanitized
										*/
										$field_data = $form_field_obj->sanitize( $field_array, $field_data );


										if ( $field_type == "postdata" && ! $form_field_obj->is_hidden( $field_array, $_POST ) ) {
											$post_id = $form_field_obj->save_post( $field_array, $field_data );
											if ( $post_id ) {
												$field_data             = array();
												$field_data['postdata'] = $post_id;
											} else {
												$submit_errors[][ $field->slug ] = __( 'There was an error saving the post data. Please try again', Forminator::DOMAIN );
											}
										}
										if ( $field_type == "product" ) {
											$product_fields[] = array(
												'name'  => $field_id,
												'value' => $field_data,
											);
										}
										$field_data_array[] = array(
											'name'  => $field_id,
											'value' => $field_data,
										);
									} else {
										if ( is_array( $valid_response ) ) {
											foreach ( $valid_response as $error_field => $error_response ) {
												$submit_errors[][ $error_field ] = $error_response;
											}
										}
									}

								} else {
									if ( ! $form_field_obj->is_hidden( $field_array, $_POST ) ) {
										$form_field_obj->validate( $field_array, '' );
									}
									$valid_response = $form_field_obj->is_valid_entry();
									if ( is_array( $valid_response ) && isset( $valid_response[ $field_id ] ) ) {
										$submit_errors[][ $field->slug ] = $valid_response[ $field_id ];
									}
								}
							}
						}
					}

				}

				/**
				 * Filter submission errors
				 *
				 * @since 1.0.2
				 *
				 * @param array $submit_errors - the submission errors
				 * @param int $form_id - the form id
				 *
				 * @return array $submit_errors
				 */
				$submit_errors = apply_filters( 'forminator_custom_form_submit_errors', $submit_errors, $form_id );

				if ( empty( $submit_errors ) ) {
					if ( isset( $setting['honeypot'] ) && filter_var( $setting['honeypot'], FILTER_VALIDATE_BOOLEAN ) ) {
						$total_fields = count( $fields ) + 1;
						if ( isset( $_POST["input_$total_fields"] ) && empty( $_POST["input_$total_fields"] ) ) {
							$can_submit = true;
						} else {
							$can_submit = false;
							//show success but dont save form
							$response = array(
								'message' => __( "Form entry saved", Forminator::DOMAIN ),
								'success' => true,
							);
						}
					}
				}
				if ( $can_submit ) {
					if ( ! empty( $field_data_array ) && empty( $submit_errors ) ) {

						/**
						 * Handle spam protection
						 * Add-ons use this filter to check if content has spam data
						 *
						 * @since 1.0.2
						 *
						 * @param bool false - defauls to false
						 * @param array $field_data_array - the entry data
						 * @param int $form_id - the form id
						 * @param string $form_type - the form type. In this case defaults to 'custom_form'
						 *
						 * @return bool true|false
						 */
						$is_spam        = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'custom_form' );

						$entry->is_spam = $is_spam;
						if ( $entry->save() ) {
							$response = array(
								'message' => __( "Form entry saved", Forminator::DOMAIN ),
								'success' => true,
							);
							if ( isset( $_POST['product-shipping'] ) && intval( $_POST['product-shipping'] > 0 ) ) {
								$field_data_array[] = array(
									'name'  => 'product_shipping',
									'value' => $_POST['product-shipping'],
								);
							}
							$field_data_array[] =  array(
								'name' 	=> '_forminator_user_ip',
								'value' => Forminator_Geo::get_user_ip()
							);

							/**
							 * Filter saved data before persisted into the database
							 *
							 * @since 1.0.2
							 *
							 * @param array $field_data_array - the entry data
							 * @param int $form_id - the form id
							 *
							 * @return array $field_data_array
							 */
							$field_data_array = apply_filters( 'forminator_custom_form_submit_field_data', $field_data_array, $form_id );

							/**
							 * Action called before setting fields to database
							 *
							 * @since 1.0.2
							 *
							 * @param Forminator_Form_Entry_Model $entry - the entry model
							 * @param int $form_id - the form id
							 * @param array $field_data_array - the entry data
							 *
							 */
							do_action( 'forminator_custom_form_submit_before_set_fields', $entry, $form_id, $field_data_array );

							$entry->set_fields( $field_data_array );
							$forminator_mail_sender = new Forminator_CForm_Front_Mail();
							$forminator_mail_sender->process_mail( $custom_form, $_POST );
							if ( isset( $setting['redirect'] ) ) {
								// Convert to bool
								$redirect = filter_var( $setting['redirect'], FILTER_VALIDATE_BOOLEAN );
								if( $redirect ) {
									if ( isset( $setting['redirect-url'] ) && ! empty( $setting['redirect-url'] ) ) {
										$response['redirect'] = true;
										$response['url']      = $setting['redirect-url'];
									}
								}
							}
							if ( isset( $setting['thankyou'] ) && $setting['thankyou'] ) {
								if ( isset( $setting['thankyou-message'] ) && ! empty( $setting['thankyou-message'] ) ) {
									//replace form data vars with value
									$thankyou_message = forminator_replace_form_data( wp_strip_all_tags( $setting['thankyou-message'] ), $_POST );
									//replace misc data vars with value
									$thankyou_message    = forminator_replace_variables( $thankyou_message );
									$response['message'] = $thankyou_message;
								}
							}

							if ( ! empty( $product_fields ) ) {
								//Process purchase

								$page_id  = $_POST['page_id']; //use page id to get permalink for redirect
								$entry_id = $entry->entry_id;
								$shipping = 0;

								if ( isset( $_POST['product-shipping'] ) ) {
									$shipping = $_POST['product-shipping'];
								}

								/**
								 * Process purchase
								 *
								 * @since 1.0.0
								 *
								 * @param array $response       - the response array
								 * @param array $product_fields - the product fields
								 * @param int   $entry_id       - the entry id ( reference for callback)
								 * @param int   $page_id        - the page id. Used to generate a return url
								 * @param int   $shipping       - the shipping cost
								 */
								$response = apply_filters( 'forminator_cform_process_purchase', $response, $product_fields, $field_data_array, $entry_id, $page_id, $shipping );
							}
						}
					}
					if ( ! empty( $submit_errors ) ) {
						$response = array(
							'message' => $this->get_invalid_form_message( $setting, $form_id ),
							'success' => false,
							'errors'  => $submit_errors,
						);
					}
				}
			} else {
				$response = array(
					'message' => __( "Only logged in users can submit this form", Forminator::DOMAIN ),
					'success' => false,
					'errors'  => array(),
				);
			}

			return $response;
		}

		return false;
	}

	/**
	 * Response message
	 *
	 * @since 1.0
	 */
	public function form_response_message( $form_id, $render_id ) {
		$post_form_id   = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;
		$post_render_id = isset( $_POST['render_id'] ) ? sanitize_text_field( $_POST['render_id'] ) : '';
		$response       = self::$response;
		//only show to related form
		if ( ! empty( $response ) && is_array( $response ) && $form_id == $post_form_id && $render_id == $post_render_id ) {
			$label_class = $response['success'] ? 'success' : 'error';
			?>
            <label class="forminator-label--<?php echo $label_class; ?>"><span><?php echo $response['message']; ?></span></label>
			<?php
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param array $setting - the form settings
	 * @param int $form_id - the form id
	 *
	 * @return mixed
	 */
	public function get_invalid_form_message( $setting, $form_id ) {
		if ( isset( $setting['use-custom-invalid-form'] ) && $setting['use-custom-invalid-form'] ) {
			if ( isset( $setting['custom-invalid-form-message'] ) && ! empty( $setting['custom-invalid-form-message'] ) ) {
				return apply_filters( 'forminator_custom_form_invalid_form_message', $setting['custom-invalid-form-message'], $form_id );
			}
		}

		return apply_filters( 'forminator_custom_form_invalid_form_message', __( "Error: Your form is not valid, please fix the errors!", Forminator::DOMAIN ), $form_id );
	}


	/**
	 * @since 1.0
	 */
	public function footer_message() {
		$response  = self::$response;
		$form_id   = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;
		$render_id = isset( $_POST['render_id'] ) ? sanitize_text_field( $_POST['render_id'] ) : '';
		$selector  = '#forminator-module-' . $form_id . '[data-forminator-render="' . $render_id . '"]';
		if ( ! empty( $response ) && is_array( $response ) ) {
			?>
            <script type="text/javascript">var ForminatorValidationErrors = <?php echo wp_json_encode( array( 'selector' => $selector, 'errors' => $response['errors'] ) ); ?></script>
			<?php
		}

	}
}