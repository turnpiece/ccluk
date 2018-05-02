<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Return custom form
 *
 * @since 1.0
 * @return mixed
 */
function forminator_form( $id, $ajax = false ) {
	$view = Forminator_CForm_Front::get_instance();

	return $view->display( $id, $ajax );
}

/**
 * Return custom form
 *
 * @since 1.0
 * @return mixed
 */
function forminator_poll( $id, $ajax = false ) {
	$view = Forminator_Poll_Front::get_instance();

	return $view->display( $id, $ajax );
}

/**
 * Return custom form
 *
 * @since 1.0
 * @return mixed
 */
function forminator_quiz( $id, $ajax = false ) {
	$view = Forminator_QForm_Front::get_instance();

	return $view->display( $id, $ajax );
}

/**
 * Return custom form
 *
 * @since 1.0
 * @return mixed
 */
function forminator_form_preview( $id, $ajax = false, $data = false ) {
	$view = Forminator_CForm_Front::get_instance();
	$data = forminator_stripslashes_deep($data);

	return $view->display( $id, $ajax, $data );
}

/**
 * Return custom form
 *
 * @since 1.0
 * @return mixed
 */
function forminator_poll_preview( $id, $ajax = false, $data = false ) {
	$view = Forminator_Poll_Front::get_instance();
	$data = forminator_stripslashes_deep($data);

	return $view->display( $id, $ajax, $data );
}

/**
 * Return custom form
 *
 * @since 1.0
 * @return mixed
 */
function forminator_quiz_preview( $id, $ajax = false, $data = false ) {
	$view = Forminator_QForm_Front::get_instance();
	$data = forminator_stripslashes_deep($data);

	return $view->display( $id, $ajax, $data );
}

/**
 * Return stripslashed string or array
 *
 * @since 1.0
 * @return mixed
 */
function forminator_stripslashes_deep($val){
    $val = is_array($val) ? array_map('stripslashes_deep', $val) : stripslashes($val);

    return $val;
}

/**
 * Sanitize field
 *
 * @since 1.0.2
 * @param $field
 *
 * @return array
 */
function forminator_sanitize_field( $field ) {
	// If array map all fields
	if( is_array( $field ) ) {
		return array_map( 'forminator_sanitize_field', $field );
	}

	return sanitize_text_field( $field );
}

/**
 * Return the array of fields objects
 *
 * @since 1.0
 * @return mixed
 */
function forminator_get_fields() {
	$forminator = Forminator_Core::get_instance();

	return $forminator->fields;
}

/**
 * Return field objects as array
 *
 * @since 1.0
 * @return mixed
 */
function forminator_fields_toArray() {
	$fields = array();
	$fields_array = forminator_get_fields();

	if( !empty( $fields_array ) ) {
		foreach ( $fields_array as $key => $field ) {
			$fields[ $field->type ] = $field;
		}
	}

	return apply_filters( 'forminator_fields_to_array', $fields, $fields_array );
}

/**
 * Return specific field by ID
 *
 * @since 1.0
 * @param $id
 *
 * @return bool
 */
function forminator_get_field( $id ) {
	$fields = forminator_fields_toArray();

	return isset( $fields[ $id ] ) && ! empty( $fields[ $id ] ) ? $fields[ $id ] : false;
}

/**
 * Return all existing custom fields
 *
 * @since 1.0
 * @return mixed
 */
function forminator_get_existing_cfields() {
	global $wpdb;

	$field_keys = $wpdb->get_col("SELECT meta_key
     FROM $wpdb->postmeta
	  WHERE meta_key NOT LIKE '\_%'
     GROUP BY meta_key
     ORDER BY meta_id DESC"
	);

	if ( $field_keys ) {
		natcasesort( $field_keys );
	}

	return $field_keys;
}

/**
 * Convert array to array compatible with field values
 *
 * @since 1.0
 * @param $array
 * @param bool $replace_value
 *
 * @return array
 */
function forminator_to_field_array( $array, $replace_value = false ) {
	$field_array = array();

	if( !empty( $array ) ) {
		foreach ( $array as $key => $value ) {
			// Use value instead of key
			if( $replace_value ) {
				$field_array[] = array(
					'value' => $value,
					'label' => $value
				);
			} else {
				$field_array[] = array(
					'value' => $key,
					'label' => $value
				);
			}
		}
	}

	return $field_array;
}

/**
 * Return vars
 *
 * @since 1.0
 * @return mixed
 */
function forminator_get_vars() {
	return apply_filters( 'forminator_vars_list', array(
			'user_ip'     => esc_html__( 'User IP Address', Forminator::DOMAIN ),
			'date_mdy'    => esc_html__( 'Date (mm/dd/yyyy)', Forminator::DOMAIN ),
			'date_dmy'    => esc_html__( 'Date (dd/mm/yyyy)', Forminator::DOMAIN ),
			'embed_id'    => esc_html__( 'Embed Post/Page ID', Forminator::DOMAIN ),
			'embed_title' => esc_html__( 'Embed Post/Page Title', Forminator::DOMAIN ),
			'embed_url'   => esc_html__( 'Embed URL', Forminator::DOMAIN ),
			'user_agent'  => esc_html__( 'HTTP User Agent', Forminator::DOMAIN ),
			'refer_url'   => esc_html__( 'HTTP Refer URL', Forminator::DOMAIN ),
			'user_name'   => esc_html__( 'User Display Name', Forminator::DOMAIN ),
			'user_email'  => esc_html__( 'User Email', Forminator::DOMAIN ),
			'user_login'  => esc_html__( 'User Login', Forminator::DOMAIN ),
		)
	);
}

/**
 * Return required icon
 *
 * @since 1.0
 * @return string
 */
function forminator_get_required_icon() {
	return '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
}

/**
 * Return week days
 *
 * @since 1.0
 * @return array
 */
function forminator_week_days() {
	return apply_filters( 'forminator_week_days', array(
			'sunday'    => __( "Sunday", Forminator::DOMAIN ),
			'monday'    => __( "Monday", Forminator::DOMAIN ),
			'tuesday'   => __( "Tuesday", Forminator::DOMAIN ),
			'wednesday' => __( "Wednesday", Forminator::DOMAIN ),
			'thursday'  => __( "Thursday", Forminator::DOMAIN ),
			'friday'    => __( "Friday", Forminator::DOMAIN ),
			'saturday'  => __( "Saturday", Forminator::DOMAIN ),
		)
	);
}

/**
 * Return name prefixes
 *
 * @since 1.0
 * @return array
 */
function forminator_get_name_prefixes() {
	return apply_filters( 'forminator_name_prefixes', array(
			'Mr'      => __( 'Mr.', Forminator::DOMAIN ),
			'Mrs'     => __( 'Mrs.', Forminator::DOMAIN ),
			'Ms'      => __( 'Ms.', Forminator::DOMAIN ),
			'Miss'    => __( 'Miss', Forminator::DOMAIN ),
			'Dr'      => __( 'Dr.', Forminator::DOMAIN ),
			'Prof'    => __( 'Prof.', Forminator::DOMAIN ),
		)
	);
}

/**
 * Return field id by string
 *
 * @since 1.0
 * @param $string
 *
 * @return mixed
 */
function forminator_clear_field_id( $string ) {
	$string = str_replace( '{', '', $string );
	$string = str_replace( '}', '', $string );

	return $string;
}

/**
 * Return filtered editor content with form data
 *
 * @since 1.0
 * @return mixed
 */
function forminator_replace_form_data( $content, $data, Forminator_Custom_Form_Model $custom_form = null, Forminator_Form_Entry_Model $entry = null ) {
	$matches = array();

	$fields      = forminator_fields_toArray();
	$field_types = array_keys( $fields );

	$randomed_field_pattern  = 'field-\d+-\d+';
	$increment_field_pattern = sprintf( '(%s)-\d+', implode( '|', $field_types ) );
	$pattern                 = '/\{((' . $randomed_field_pattern . ')|(' . $increment_field_pattern . '))(\-[A-Za-z-_]+)?\}/';


	// Find all field ID's
	if ( preg_match_all( $pattern, $content, $matches ) ) {
		if( !isset( $matches[0] ) || !is_array( $matches[0] ) ) {
			return $content;
		}
		foreach( $matches[0] as $match ) {
			$element_id = forminator_clear_field_id( $match );

			// Check if field exist, if not we replace the ID with empty string
			if( isset( $data[ $element_id ] ) && !empty( $data[ $element_id ] ) ) {
				$value = $data[ $element_id ];
			} else if( (strpos($element_id, 'postdata') !== false || strpos($element_id, 'upload') !== false) && $custom_form && $entry ) {
				$value = forminator_get_field_from_form_entry( $element_id, $custom_form, $data, $entry );
			} else {
				$value = '';
			}

			// If array, convert it to string
			if( is_array( $value ) ) {
				$value = implode( ", ",$value );
			}

			$content = str_replace( $match, $value, $content );
		}
	}

	return apply_filters( 'forminator_replace_form_data', $content, $data, $fields );
}

/**
 * Format custom form data variables to html formatted
 *
 * @since 1.0.3
 *
 * @param string                       $content
 * @param Forminator_Custom_Form_Model $custom_form
 * @param array                        $data - submitted `_POST` data
 * @param Forminator_Form_Entry_Model  $entry
 * @param array                        $excluded
 *
 * @return mixed
 */
function forminator_replace_custom_form_data( $content, Forminator_Custom_Form_Model $custom_form, $data, Forminator_Form_Entry_Model $entry, $excluded = array() ) {
	$custom_form_datas = array(
		'{all_fields}' => 'forminator_get_formatted_form_entry',
		'{form_name}'  => 'forminator_get_formatted_form_name',
	);

	foreach ( $custom_form_datas as $custom_form_data => $function ) {
		if ( in_array( $custom_form_data, $excluded ) ) {
			continue;
		}
		if ( strpos( $content, $custom_form_data ) !== false ) {
			if ( is_callable( $function ) ) {
				$replacer = call_user_func( $function, $custom_form, $data, $entry );
				$content  = str_replace( $custom_form_data, $replacer, $content );
			}
		}
	}

	return apply_filters( 'forminator_replace_custom_form_data', $content, $custom_form, $data, $entry, $excluded, $custom_form_datas );
}

/**
 * Get Html Formatted of form entry
 *
 * @since 1.0.3
 *
 * @param Forminator_Custom_Form_Model $custom_form
 * @param                              $data
 * @param Forminator_Form_Entry_Model  $entry
 *
 * @return string
 */
function forminator_get_formatted_form_entry( Forminator_Custom_Form_Model $custom_form, $data, Forminator_Form_Entry_Model $entry ) {
	$ignored_field_types = Forminator_Form_Entry_Model::ignored_fields();
	$form_fields         = $custom_form->getFields();
	if ( is_null( $form_fields ) ) {
		$form_fields = array();
	}

	$html = '<br/><ol>';
	foreach ( $form_fields as $form_field ) {
		/** @var  Forminator_Form_Field_Model $form_field */
		$field_type = $form_field->__get( 'type' );
		if ( in_array( $field_type, $ignored_field_types ) ) {
			continue;
		}
		$html  .= '<li>';
		$label = $form_field->get_label_for_entry();
		$value = render_entry( $entry, $form_field->slug );
		if ( ! empty( $label ) ) {
			$html .= '<b>' . $label . '</b><br/>';
		}
		$html .= $value . '<br/>';
		$html .= '</li>';
	}
	$html .= '</ol><br/>';

	return apply_filters( 'forminator_get_formatted_form_entry', $html, $custom_form, $data, $entry, $ignored_field_types );
}

/**
 * Get field from registered entries
 *
 * @since 1.0.5
 *
 * @param 							   $element_id
 * @param Forminator_Custom_Form_Model $custom_form
 * @param                              $data
 * @param Forminator_Form_Entry_Model  $entry
 *
 * @return string
 */
function forminator_get_field_from_form_entry( $element_id, Forminator_Custom_Form_Model $custom_form, $data, Forminator_Form_Entry_Model $entry ) {
	$form_fields         = $custom_form->getFields();
	if ( is_null( $form_fields ) ) {
		$form_fields = array();
	}
	foreach ( $form_fields as $form_field ) {
		/** @var  Forminator_Form_Field_Model $form_field */
		if ( $form_field->slug !== $element_id ) {
			continue;
		}
		$value = render_entry( $entry, $form_field->slug );
		return $value;
	}
}

/**
 * Get Html Formatted of form name
 *
 * @since 1.0.3
 *
 * @param Forminator_Custom_Form_Model $custom_form
 * @param                              $data
 * @param Forminator_Form_Entry_Model  $entry
 *
 * @return string
 */
function forminator_get_formatted_form_name( Forminator_Custom_Form_Model $custom_form, $data, Forminator_Form_Entry_Model $entry ) {
	return esc_html( forminator_get_form_name( $custom_form->id, 'custom_form' ) );
}

/**
 * Return filtered editor content with replaced variables
 *
 * @since 1.0
 *
 * @param $content
 * @param $id
 *
 * @return string
 */
function forminator_replace_variables( $content, $id = false, $data_current_url = false ) {
	$content_before_replacement = $content;

	// If we have no variables, skip
	if ( strpos( $content, '{' ) !== false ) {
		// Handle User IP Address variable
		$user_ip = forminator_user_ip();
		$content = str_replace( '{user_ip}', $user_ip, $content );

		// Handle Date (mm/dd/yyyy) variable
		$date_mdy = date_i18n( 'm/d/Y', forminator_local_timestamp(), true );
		$content = str_replace( '{date_mdy}', $date_mdy, $content );

		// Handle Date (dd/mm/yyyy) variable
		$date_dmy = date_i18n( 'd/m/Y', forminator_local_timestamp(), true );
		$content = str_replace( '{date_dmy}', $date_dmy, $content );

		// Handle Embed Post/Page ID variable
		$embed_post_id = forminator_get_post_data( 'ID' );
		$content = str_replace( '{embed_id}', $embed_post_id, $content );

		// Handle Embed Post/Page Title variable
		$embed_title = forminator_get_post_data( 'post_title' );
		$content = str_replace( '{embed_title}', $embed_title, $content );

		// Handle Embed URL variable
		$embed_url = $data_current_url ? $data_current_url : forminator_get_current_url();
		$content = str_replace( '{embed_url}', $embed_url, $content );

		// Handle HTTP User Agent variable
		$user_agent = $_SERVER[ 'HTTP_USER_AGENT' ];
		$content = str_replace( '{user_agent}', $user_agent, $content );

		// Handle site url variable
		$site_url = site_url();
		$content = str_replace( '{site_url}', $site_url, $content );

		// Handle HTTP Refer URL variable
		$refer_url = isset ( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : $embed_url;
		$content = str_replace( '{refer_url}', $refer_url, $content );
		$content = str_replace( '{http_refer}', $refer_url, $content);

		// Handle User Display Name variable
		$user_name = forminator_get_user_data( 'display_name' );
		$content = str_replace( '{user_name}', $user_name, $content );

		// Handle User Email variable
		$user_email = forminator_get_user_data( 'user_email' );
		$content = str_replace( '{user_email}', $user_email, $content );

		// Handle User Login variable
		$user_login = forminator_get_user_data( 'user_login' );
		$content = str_replace( '{user_login}', $user_login, $content );

		// Handle form_name data
		$form_name = ( $id != false ) ? esc_html( forminator_get_form_name( $id, 'custom_form' ) ) : '';
		$content = str_replace( '{form_name}', $form_name, $content );
	}

	return apply_filters( 'forminator_replace_variables', $content, $content_before_replacement );
}

/**
 * Render entry
 *
 * @since 1.0
 *
 * @param object $item        - the entry
 * @param string $column_name - the column name
 *
 * @param null   $field @since 1.0.5, optional Forminator_Form_Field_Model
 *
 * @return string
 */
function render_entry( $item , $column_name, $field = null ) {
	$data =  $item->get_meta( $column_name, '' );
	if ( $data ) {
		$currency_symbol 	= forminator_get_currency_symbol();
		if ( is_array( $data ) ) {
			$output 		= '';
			$product_cost 	= 0;
			$is_product 	= false;
			$countries 		= forminator_get_countries_list();
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( $key == 'file' && isset( $value['file_url'] ) ) {
						$file_name 	= basename( $value['file_url'] );
						$file_name 	= "<a href='" . esc_url( $value['file_url'] ) . "' target='_blank' rel='noreferrer' title='". __( 'View File', Forminator::DOMAIN ) ."'>$file_name</a> ,";
						$output 	.= $file_name;
					}

				} else {
					if ( !is_int( $key ) ) {
						if ( $key == 'postdata' ) {
							// possible empty when postdata not required
							if (! empty($value)) {
								$url 	= get_edit_post_link( $value );
								$title  = get_the_title( $value );
								$name 	= ! empty( $title ) ? $title : '(no title)' ;
								$output .= "<a href='" .$url . "' target='_blank' rel='noreferrer' title='". __( 'Edit Post', Forminator::DOMAIN ) ."'>$name</a> ,";
							}
						} else {
							if ( is_string( $key ) ) {
								if ( $key == 'product-id' || $key == 'product-quantity' ) {
									if ( $product_cost == 0 ) {
										$product_cost = $value;
									} else {
										$product_cost = $product_cost * $value;
									}
									$is_product = true;
								} else {
									if ( $key  == 'country' ) {
										if ( isset( $countries[$value] ) ) {
											$output .=  sprintf( __( '<strong>Country : </strong> %s', Forminator::DOMAIN ), $countries[$value] ) . "<br/> ";
										} else {
											$output .=  sprintf( __( '<strong>Country : </strong> %s', Forminator::DOMAIN ), $value ) . "<br/> ";
										}
									} else {
										if ( in_array( $key, Forminator_Form_Entry_Model::field_suffix() ) ) {
											$key = Forminator_Form_Entry_Model::translate_suffix( $key );
										} else {
											$key = strtolower( $key );
											$key = ucfirst( str_replace( array( '-', '_' ), ' ', $key ) );
										}
										$value  = esc_html( $value );
										$output .= sprintf( __( '<strong>%s : </strong> %s', Forminator::DOMAIN ), $key, $value ) . "<br/> ";
									}
								}
							}
						}
					}
				}
			}
			if ( $is_product ) {
				$output = sprintf( __( '<strong>Total</strong> %s', Forminator::DOMAIN ), $currency_symbol . '' .$product_cost );
			} else {
				if ( !empty( $output ) ) {
					$output = substr( trim( $output ), 0, -1 );
				} else {
					$output = implode( ",", $data );
				}
			}
			return $output;
		} else {
			return $data;
		}
	}

	return '';
}

/**
 * Return countries list
 *
 * @since 1.0
 * @return array
 */
function forminator_get_countries_list() {
	return apply_filters( 'forminator_countries_list', array(
			esc_html__( 'Afghanistan', Forminator::DOMAIN ),
			esc_html__( 'Albania', Forminator::DOMAIN ),
			esc_html__( 'Algeria', Forminator::DOMAIN ),
			esc_html__( 'American Samoa', Forminator::DOMAIN ),
			esc_html__( 'Andorra', Forminator::DOMAIN ),
			esc_html__( 'Angola', Forminator::DOMAIN ),
			esc_html__( 'Antigua and Barbuda', Forminator::DOMAIN ),
			esc_html__( 'Argentina', Forminator::DOMAIN ),
			esc_html__( 'Armenia', Forminator::DOMAIN ),
			esc_html__( 'Australia', Forminator::DOMAIN ),
			esc_html__( 'Austria', Forminator::DOMAIN ),
			esc_html__( 'Azerbaijan', Forminator::DOMAIN ),
			esc_html__( 'Bahamas', Forminator::DOMAIN ),
			esc_html__( 'Bahrain', Forminator::DOMAIN ),
			esc_html__( 'Bangladesh', Forminator::DOMAIN ),
			esc_html__( 'Barbados', Forminator::DOMAIN ),
			esc_html__( 'Belarus', Forminator::DOMAIN ),
			esc_html__( 'Belgium', Forminator::DOMAIN ),
			esc_html__( 'Belize', Forminator::DOMAIN ),
			esc_html__( 'Benin', Forminator::DOMAIN ),
			esc_html__( 'Bermuda', Forminator::DOMAIN ),
			esc_html__( 'Bhutan', Forminator::DOMAIN ),
			esc_html__( 'Bolivia', Forminator::DOMAIN ),
			esc_html__( 'Bosnia and Herzegovina', Forminator::DOMAIN ),
			esc_html__( 'Botswana', Forminator::DOMAIN ),
			esc_html__( 'Brazil', Forminator::DOMAIN ),
			esc_html__( 'Brunei', Forminator::DOMAIN ),
			esc_html__( 'Bulgaria', Forminator::DOMAIN ),
			esc_html__( 'Burkina Faso', Forminator::DOMAIN ),
			esc_html__( 'Burundi', Forminator::DOMAIN ),
			esc_html__( 'Cambodia', Forminator::DOMAIN ),
			esc_html__( 'Cameroon', Forminator::DOMAIN ),
			esc_html__( 'Canada', Forminator::DOMAIN ),
			esc_html__( 'Cape Verde', Forminator::DOMAIN ),
			esc_html__( 'Cayman Islands', Forminator::DOMAIN ),
			esc_html__( 'Central African Republic', Forminator::DOMAIN ),
			esc_html__( 'Chad', Forminator::DOMAIN ),
			esc_html__( 'Chile', Forminator::DOMAIN ),
			esc_html__( 'China', Forminator::DOMAIN ),
			esc_html__( 'Colombia', Forminator::DOMAIN ),
			esc_html__( 'Comoros', Forminator::DOMAIN ),
			esc_html__( 'Congo, Democratic Republic of the', Forminator::DOMAIN ),
			esc_html__( 'Congo, Republic of the', Forminator::DOMAIN ),
			esc_html__( 'Costa Rica', Forminator::DOMAIN ),
			esc_html__( "Côte d'Ivoire", Forminator::DOMAIN ),
			esc_html__( 'Croatia', Forminator::DOMAIN ),
			esc_html__( 'Cuba', Forminator::DOMAIN ),
			esc_html__( 'Curaçao', Forminator::DOMAIN ),
			esc_html__( 'Cyprus', Forminator::DOMAIN ),
			esc_html__( 'Czech Republic', Forminator::DOMAIN ),
			esc_html__( 'Denmark', Forminator::DOMAIN ),
			esc_html__( 'Djibouti', Forminator::DOMAIN ),
			esc_html__( 'Dominica', Forminator::DOMAIN ),
			esc_html__( 'Dominican Republic', Forminator::DOMAIN ),
			esc_html__( 'East Timor', Forminator::DOMAIN ),
			esc_html__( 'Ecuador', Forminator::DOMAIN ),
			esc_html__( 'Egypt', Forminator::DOMAIN ),
			esc_html__( 'El Salvador', Forminator::DOMAIN ),
			esc_html__( 'Equatorial Guinea', Forminator::DOMAIN ),
			esc_html__( 'Eritrea', Forminator::DOMAIN ),
			esc_html__( 'Estonia', Forminator::DOMAIN ),
			esc_html__( 'Ethiopia', Forminator::DOMAIN ),
			esc_html__( 'Faroe Islands', Forminator::DOMAIN ),
			esc_html__( 'Fiji', Forminator::DOMAIN ),
			esc_html__( 'Finland', Forminator::DOMAIN ),
			esc_html__( 'France', Forminator::DOMAIN ),
			esc_html__( 'French Polynesia', Forminator::DOMAIN ),
			esc_html__( 'Gabon', Forminator::DOMAIN ),
			esc_html__( 'Gambia', Forminator::DOMAIN ),
			esc_html__( 'Georgia, Country', Forminator::DOMAIN ),
			esc_html__( 'Germany', Forminator::DOMAIN ),
			esc_html__( 'Ghana', Forminator::DOMAIN ),
			esc_html__( 'Greece', Forminator::DOMAIN ),
			esc_html__( 'Greenland', Forminator::DOMAIN ),
			esc_html__( 'Grenada', Forminator::DOMAIN ),
			esc_html__( 'Guam', Forminator::DOMAIN ),
			esc_html__( 'Guatemala', Forminator::DOMAIN ),
			esc_html__( 'Guinea', Forminator::DOMAIN ),
			esc_html__( 'Guinea-Bissau', Forminator::DOMAIN ),
			esc_html__( 'Guyana', Forminator::DOMAIN ),
			esc_html__( 'Haiti', Forminator::DOMAIN ),
			esc_html__( 'Honduras', Forminator::DOMAIN ),
			esc_html__( 'Hong Kong', Forminator::DOMAIN ),
			esc_html__( 'Hungary', Forminator::DOMAIN ),
			esc_html__( 'Iceland', Forminator::DOMAIN ),
			esc_html__( 'India', Forminator::DOMAIN ),
			esc_html__( 'Indonesia', Forminator::DOMAIN ),
			esc_html__( 'Iran', Forminator::DOMAIN ),
			esc_html__( 'Iraq', Forminator::DOMAIN ),
			esc_html__( 'Ireland', Forminator::DOMAIN ),
			esc_html__( 'Israel', Forminator::DOMAIN ),
			esc_html__( 'Italy', Forminator::DOMAIN ),
			esc_html__( 'Jamaica', Forminator::DOMAIN ),
			esc_html__( 'Japan', Forminator::DOMAIN ),
			esc_html__( 'Jordan', Forminator::DOMAIN ),
			esc_html__( 'Kazakhstan', Forminator::DOMAIN ),
			esc_html__( 'Kenya', Forminator::DOMAIN ),
			esc_html__( 'Kiribati', Forminator::DOMAIN ),
			esc_html__( 'North Korea', Forminator::DOMAIN ),
			esc_html__( 'South Korea', Forminator::DOMAIN ),
			esc_html__( 'Kenya', Forminator::DOMAIN ),
			esc_html__( 'Kosovo', Forminator::DOMAIN ),
			esc_html__( 'Kuwait', Forminator::DOMAIN ),
			esc_html__( 'Kyrgyzstan', Forminator::DOMAIN ),
			esc_html__( 'Laos', Forminator::DOMAIN ),
			esc_html__( 'Latvia', Forminator::DOMAIN ),
			esc_html__( 'Lebanon', Forminator::DOMAIN ),
			esc_html__( 'Lesotho', Forminator::DOMAIN ),
			esc_html__( 'Liberia', Forminator::DOMAIN ),
			esc_html__( 'Libya', Forminator::DOMAIN ),
			esc_html__( 'Liechtenstein', Forminator::DOMAIN ),
			esc_html__( 'Lithuania', Forminator::DOMAIN ),
			esc_html__( 'Luxembourg', Forminator::DOMAIN ),
			esc_html__( 'Macedonia', Forminator::DOMAIN ),
			esc_html__( 'Madagascar', Forminator::DOMAIN ),
			esc_html__( 'Malawi', Forminator::DOMAIN ),
			esc_html__( 'Malaysia', Forminator::DOMAIN ),
			esc_html__( 'Maldives', Forminator::DOMAIN ),
			esc_html__( 'Mali', Forminator::DOMAIN ),
			esc_html__( 'Malta', Forminator::DOMAIN ),
			esc_html__( 'Marshall Islands', Forminator::DOMAIN ),
			esc_html__( 'Mauritania', Forminator::DOMAIN ),
			esc_html__( 'Mauritius', Forminator::DOMAIN ),
			esc_html__( 'Mexico', Forminator::DOMAIN ),
			esc_html__( 'Micronesia', Forminator::DOMAIN ),
			esc_html__( 'Moldova', Forminator::DOMAIN ),
			esc_html__( 'Monaco', Forminator::DOMAIN ),
			esc_html__( 'Mongolia', Forminator::DOMAIN ),
			esc_html__( 'Montenegro', Forminator::DOMAIN ),
			esc_html__( 'Morocco', Forminator::DOMAIN ),
			esc_html__( 'Mozambique', Forminator::DOMAIN ),
			esc_html__( 'Myanmar', Forminator::DOMAIN ),
			esc_html__( 'Namibia', Forminator::DOMAIN ),
			esc_html__( 'Nauru', Forminator::DOMAIN ),
			esc_html__( 'Nepal', Forminator::DOMAIN ),
			esc_html__( 'Netherlands', Forminator::DOMAIN ),
			esc_html__( 'New Zealand', Forminator::DOMAIN ),
			esc_html__( 'Nicaragua', Forminator::DOMAIN ),
			esc_html__( 'Niger', Forminator::DOMAIN ),
			esc_html__( 'Nigeria', Forminator::DOMAIN ),
			esc_html__( 'Northern Mariana Islands', Forminator::DOMAIN ),
			esc_html__( 'Norway', Forminator::DOMAIN ),
			esc_html__( 'Oman', Forminator::DOMAIN ),
			esc_html__( 'Pakistan', Forminator::DOMAIN ),
			esc_html__( 'Palau', Forminator::DOMAIN ),
			esc_html__( 'Palestine, State of', Forminator::DOMAIN ),
			esc_html__( 'Panama', Forminator::DOMAIN ),
			esc_html__( 'Papua New Guinea', Forminator::DOMAIN ),
			esc_html__( 'Paraguay', Forminator::DOMAIN ),
			esc_html__( 'Peru', Forminator::DOMAIN ),
			esc_html__( 'Philippines', Forminator::DOMAIN ),
			esc_html__( 'Poland', Forminator::DOMAIN ),
			esc_html__( 'Portugal', Forminator::DOMAIN ),
			esc_html__( 'Puerto Rico', Forminator::DOMAIN ),
			esc_html__( 'Qatar', Forminator::DOMAIN ),
			esc_html__( 'Romania', Forminator::DOMAIN ),
			esc_html__( 'Russia', Forminator::DOMAIN ),
			esc_html__( 'Rwanda', Forminator::DOMAIN ),
			esc_html__( 'Saint Kitts and Nevis', Forminator::DOMAIN ),
			esc_html__( 'Saint Lucia', Forminator::DOMAIN ),
			esc_html__( 'Saint Vincent and the Grenadines', Forminator::DOMAIN ),
			esc_html__( 'Samoa', Forminator::DOMAIN ),
			esc_html__( 'San Marino', Forminator::DOMAIN ),
			esc_html__( 'Sao Tome and Principe', Forminator::DOMAIN ),
			esc_html__( 'Saudi Arabia', Forminator::DOMAIN ),
			esc_html__( 'Senegal', Forminator::DOMAIN ),
			esc_html__( 'Serbia', Forminator::DOMAIN ),
			esc_html__( 'Seychelles', Forminator::DOMAIN ),
			esc_html__( 'Sierra Leone', Forminator::DOMAIN ),
			esc_html__( 'Singapore', Forminator::DOMAIN ),
			esc_html__( 'Sint Maarten', Forminator::DOMAIN ),
			esc_html__( 'Slovakia', Forminator::DOMAIN ),
			esc_html__( 'Slovenia', Forminator::DOMAIN ),
			esc_html__( 'Solomon Islands', Forminator::DOMAIN ),
			esc_html__( 'Somalia', Forminator::DOMAIN ),
			esc_html__( 'South Africa', Forminator::DOMAIN ),
			esc_html__( 'Spain', Forminator::DOMAIN ),
			esc_html__( 'Sri Lanka', Forminator::DOMAIN ),
			esc_html__( 'Sudan', Forminator::DOMAIN ),
			esc_html__( 'Sudan, South', Forminator::DOMAIN ),
			esc_html__( 'Suriname', Forminator::DOMAIN ),
			esc_html__( 'Swaziland', Forminator::DOMAIN ),
			esc_html__( 'Sweden', Forminator::DOMAIN ),
			esc_html__( 'Switzerland', Forminator::DOMAIN ),
			esc_html__( 'Syria', Forminator::DOMAIN ),
			esc_html__( 'Taiwan', Forminator::DOMAIN ),
			esc_html__( 'Tajikistan', Forminator::DOMAIN ),
			esc_html__( 'Tanzania', Forminator::DOMAIN ),
			esc_html__( 'Thailand', Forminator::DOMAIN ),
			esc_html__( 'Togo', Forminator::DOMAIN ),
			esc_html__( 'Tonga', Forminator::DOMAIN ),
			esc_html__( 'Trinidad and Tobago', Forminator::DOMAIN ),
			esc_html__( 'Tunisia', Forminator::DOMAIN ),
			esc_html__( 'Turkey', Forminator::DOMAIN ),
			esc_html__( 'Turkmenistan', Forminator::DOMAIN ),
			esc_html__( 'Tuvalu', Forminator::DOMAIN ),
			esc_html__( 'Uganda', Forminator::DOMAIN ),
			esc_html__( 'Ukraine', Forminator::DOMAIN ),
			esc_html__( 'United Arab Emirates', Forminator::DOMAIN ),
			esc_html__( 'United Kingdom', Forminator::DOMAIN ),
			esc_html__( 'United States of America (USA)', Forminator::DOMAIN ),
			esc_html__( 'Uruguay', Forminator::DOMAIN ),
			esc_html__( 'Uzbekistan', Forminator::DOMAIN ),
			esc_html__( 'Vanuatu', Forminator::DOMAIN ),
			esc_html__( 'Vatican City', Forminator::DOMAIN ),
			esc_html__( 'Venezuela', Forminator::DOMAIN ),
			esc_html__( 'Vietnam', Forminator::DOMAIN ),
			esc_html__( 'Virgin Islands, British', Forminator::DOMAIN ),
			esc_html__( 'Virgin Islands, U.S.', Forminator::DOMAIN ),
			esc_html__( 'Yemen', Forminator::DOMAIN ),
			esc_html__( 'Zambia', Forminator::DOMAIN ),
			esc_html__( 'Zimbabwe', Forminator::DOMAIN ),
		)
	);
}