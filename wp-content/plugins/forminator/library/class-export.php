<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Export
 *
 * Handle data exports
 *
 * @since 1.0
 */
class Forminator_Export {

	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Holds fields to be exported
	 *
	 * @since 1.0.5
	 *
	 * @var array
	 */
	private $global_fields_to_export = array();

	/**
	 * Return the plugin instance
	 *
	 * @return Forminator_Export
	 *
	 * @since 1.0
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Main constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( &$this, 'listen_for_csv_export' ) );
		add_action( 'wp_loaded', array( &$this, 'listen_for_saving_export_schedule' ) );
		//schedule for check and send export
		add_action( 'init', array( &$this, 'schedule_entries_exporter' ) );

		add_action( 'forminator_send_export', array( &$this, 'maybe_send_export' ) );
	}

	/**
	 * Set up the schedule
	 *
	 * @since 1.0
	 */
	public function schedule_entries_exporter() {
		if ( ! wp_next_scheduled( 'forminator_send_export' ) ) {
			wp_schedule_single_event( time(), 'forminator_send_export' );
		}
	}

	/**
	 * Listen for export action
	 *
	 * @since 1.0
	 */
	public function listen_for_csv_export() {
		if ( ! isset( $_POST['forminator_export'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_forminator_nonce'], 'forminator_export' ) ) {
			return;
		}

		$form_id     = isset( $_POST['form_id'] ) ? $_POST['form_id'] : 0;
		$type        = isset( $_POST['form_type'] ) ? $_POST['form_type'] : null;
		$form_id     = intval( $form_id );
		$export_data = $this->_prepare_export_data( $form_id, $type );
		if ( ! is_array( $export_data ) ) {
			return;
		}

		$data  = $export_data[0];
		$model = $export_data[1];
		$count = $export_data[2];
		//save the time for later uses
		$logs = get_option( 'forminator_exporter_log', array() );
		if ( ! isset( $logs[ $model->id ] ) ) {
			$logs[ $model->id ] = array();
		}
		$logs[ $model->id ][] = array(
			'time'  => current_time( 'timestamp' ),
			'count' => $count
		);
		update_option( 'forminator_exporter_log', $logs );

		$fp = fopen( 'php://memory', 'w' );
		foreach ( $data as $fields ) {
			fputcsv( $fp, $fields );
		}
		$filename = 'forminator-' . sanitize_title( $model->name ) . '-' . date( 'ymdHis' ) . '.csv';
		fseek( $fp, 0 );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		// make php send the generated csv lines to the browser
		fpassthru( $fp );
		exit();
	}

	/**
	 * Listen for the POST request to store schedule data
	 *
	 * @since 1.0
	 */
	public function listen_for_saving_export_schedule() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'forminator_export_entries' ) {
			$data         = get_option( 'forminator_entries_export_schedule', array() );

			//check for invalid setting
			foreach ($data as $k => $d) {
				if (!$d['form_id'] || !$d['form_type']) {
					//unschedule
					unset($data[$k]);
				}
			}
			$key          = $_POST['form_id'] . $_POST['form_type'];
			$last_sent    = current_time( 'timestamp' );

			if( $_POST['interval'] == 'daily' ) {
				$last_sent = strtotime( '-24 hours', current_time( 'timestamp' ) );
			}

			$data[ $key ] = array(
				'enabled'   => isset( $_POST['enabled'] ) ? $_POST['enabled'] : 'false',
				'form_id'   => $_POST['form_id'],
				'form_type' => $_POST['form_type'],
				'email'     => $_POST['email'],
				'interval'  => $_POST['interval'],
				'month_day' => $_POST['month_day'],
				'day'       => $_POST['day'],
				'hour'      => $_POST['hour'],
				'last_sent' => $last_sent
			);
			update_option( 'forminator_entries_export_schedule', $data );

			$referer = wp_get_referer();
			if ( ! empty( $referer ) ) {
				$referer_query = wp_parse_url( $referer, PHP_URL_QUERY );
				if ( ! empty( $referer_query ) ) {
					wp_parse_str( $referer_query, $query );
					if ( ! empty( $query ) && isset( $query['page'] ) && 'forminator-entries' === $query['page'] ) {

						// additional redirect parameter on global entries page
						$redirect = add_query_arg(
							array(
								'form_id' => $_POST['form_id'],
							)
						);

						wp_redirect( $redirect );
						exit;
					}
				}

			}
		}
	}

	/**
	 * Try send export
	 *
	 * @since 1.0
	 */
	public function maybe_send_export() {
		$data     = get_option( 'forminator_entries_export_schedule', array() );
		$receipts = array();
		foreach ( $data as $row ) {
			if ( ! isset( $row['enabled'] ) || ( isset( $row['enabled'] ) && $row['enabled'] === 'false' ) || ( isset( $row['email'] ) && empty( $row['email'] ) ) ) {
				continue;
			}
			$last_sent = $row['last_sent'];
			//check the next sent
			$next_sent = null;
			switch ( $row['interval'] ) {
				case 'daily':
					$next_sent = strtotime( '+24 hours', $last_sent );
					$next_sent = date( 'Y-m-d', $next_sent ) . ' ' . $row['hour'];
					break;
				case 'weekly':
					$next_sent = strtotime( '+7 days', $last_sent );
					$next_sent = date( 'Y-m-d', $next_sent ) . ' ' . $row['hour'];
					break;
				case 'monthly':
					$month_date = isset( $row['month_day'] ) ? $row['month_day'] : 1;
					$next_sent = $this->get_monthly_export_date($last_sent, $month_date);
					$next_sent = date( 'Y-m-d', $next_sent ) . ' ' . $row['hour'];
					break;
			}

			if ( current_time( 'timestamp' ) > strtotime( $next_sent ) ) {
				//queue to prevent spam
				$info = $this->_prepare_attachment( $row['form_id'], $row['form_type'], $row['email'] );
				if ( ! is_array( $info ) ) {
					continue;
				}
				$info[]                      = $row['form_type'];
				$receipts[ $row['email'] ][] = $info;
			}
		}
		//now start to send
		foreach ( $receipts as $email => $info ) {
			$ids    = array();
			$files  = array();
			$titles = array();
			foreach ( $info as $row ) {
				$ids[]    = $row[1]->id . $row[2];
				$files[]  = $row[0];
				if( isset( $row[1]->settings['formName'] ) ) {
					$titles[] = $row[1]->settings['formName'];
				} else {
					$titles[] = $row[1]->name;
				}
			}
			$subject = sprintf( __( "Entries data for %s", Forminator::DOMAIN ), implode( ',', $titles ) );
			wp_mail( $email, $subject, 'Your scheduled results have arrived! Forminator has tabulated the responses and packaged the results.', array(), $files );
			foreach ( $files as $file ) {
				@unlink( $file );
			}
			//update last sent
			foreach ( $ids as $id ) {
				$data[ $id ]['last_sent'] = current_time( 'timestamp' );
			}
		}
		update_option( 'forminator_entries_export_schedule', $data );
	}

	/**
	 * Prepare export data
	 *
	 * @since 1.0
	 */
	private function _prepare_export_data( $form_id, $type ) {
		$model   = null;
		$data    = array();
		$entries = array();
		switch ( $type ) {
			case 'quiz':
				$model = Forminator_Quiz_Form_Model::model()->load( $form_id );
				if ( ! is_object( $model ) ) {
					return null;
				}
				$entries = Forminator_Form_Entry_Model::get_entries( $form_id );
				$headers = array(
					__( 'Question', Forminator::DOMAIN ),
					__( 'Answer', Forminator::DOMAIN ),
					__( 'Result', Forminator::DOMAIN ),
				);
				foreach ( $entries as $entry ) {
					if ( isset( $entry->meta_data['entry']['value'][0]['value'] ) ) {
						$meta = $entry->meta_data['entry']['value'][0]['value'];

						if ( isset( $meta['answers'] ) ) {
							foreach ( $meta['answers'] as $answer ) {
								$row    = array();
								$row[]  = $answer['question'];
								$row[]  = $answer['answer'];
								$row[]  = $meta['result']['title'];
								$data[] = $row;
							}
						}
					} else {
						$meta = $entry->meta_data['entry']['value'];
						foreach ( $meta as $answer ) {
							$row    = array();
							$row[]  = $answer['question'];
							$row[]  = $answer['answer'];
							$row[]  = $answer['isCorrect'] == 1 ? __( 'Correct', Forminator::DOMAIN ) : __( 'Incorrect', Forminator::DOMAIN );
							$data[] = $row;
						}
					}
				}

				$data = array_merge( array( $headers ), $data );
				break;
			case 'poll':
				$model = Forminator_Poll_Form_Model::model()->load( $form_id );
				if ( ! is_object( $model ) ) {
					return null;
				}
				$entries      = Forminator_Form_Entry_Model::get_entries( $form_id );
				$fields_array = $model->getFieldsAsArray();
				$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $form_id, $fields_array );
				$fields       = $model->getFields();
				$data         = array(
					array( __( 'Answer', Forminator::DOMAIN ), __( 'Total', Forminator::DOMAIN ) ),
				);
				if ( ! is_null( $fields ) ) {
					foreach ( $fields as $field ) {
						$label = $field->__get( 'field_label' );
						if ( ! $label ) {
							$label = $field->title;
						}
						$slug         = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
						$countEntries = 0;
						if ( in_array( $slug, array_keys( $map_entries ) ) ) {
							$countEntries = $map_entries[ $slug ];
						}
						$data[] = array( $label, $countEntries );
					}
				}
				break;
			case 'cform':
				$entries = Forminator_Form_Entry_Model::get_entries( $form_id );
				$model   = Forminator_Custom_Form_Model::model()->load( $form_id );
				if ( ! is_object( $model ) ) {
					return null;
				}

				$mappers = $this->get_custom_form_export_mappers( $model );

				$result = array();
				foreach ( $entries as $entry ) {
					$data = array();
					// traverse from fields to be correctly mapped with updated form fields.
					foreach ( $mappers as $mapper ) {
						//its from model's property
						if ( isset( $mapper['property'] ) ) {
							if ( property_exists( $entry, $mapper['property'] ) ) {
								$property = $mapper['property'];
								// casting property to string
								$data[] = (string) $entry->$property;
							} else {
								$data[] = '';
							}
						} else {
							// meta_key based
							$meta_value = $entry->get_meta( $mapper['meta_key'], '' );
							if ( ! isset( $mapper['sub_metas'] ) ) {
								$data[] = Forminator_Form_Entry_Model::meta_value_to_string( $mapper['type'], $meta_value );
							} else {

								// sub_metas available
								foreach ( $mapper['sub_metas'] as $sub_meta ) {
									$sub_key = $sub_meta['key'];
									if ( isset( $meta_value[ $sub_key ] ) && ! empty( $meta_value[ $sub_key ] ) ) {
										$value      = $meta_value[ $sub_key ];
										$field_type = $mapper['type'] . '.' . $sub_key;
										$data[]     = Forminator_Form_Entry_Model::meta_value_to_string( $field_type, $value );
									} else {
										$data[] = '';
									}
								}
							}
						}

					}

					// use string as key, so it will not reindex when merge or shift/unshift
					$result[ (string) $entry->entry_id ] = $data;
				}

				//flatten mappers to headers
				$headers = array();
				foreach ( $mappers as $mapper ) {
					if ( ! isset( $mapper['sub_metas'] ) ) {
						$headers[] = $mapper['label'];
					} else {
						foreach ( $mapper['sub_metas'] as $sub_meta ) {
							$headers[] = $sub_meta['label'];
						}
					}
				}


				$data = array_merge( array( 'headers' => $headers ), $result );
				break;
		}

		return array( $data, $model, count( $entries ) );
	}

	/**
	 * Prepare mail attachment
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 * @param $type
	 * @param $email
	 *
	 * @return array|void
	 */
	private function _prepare_attachment( $form_id, $type, $email ) {
		$data = $this->_prepare_export_data( $form_id, $type );
		if ( ! is_array( $data ) ) {
			return;
		}
		$model       = $data[1];
		$data        = $data[0];
		$upload_dirs = wp_upload_dir();
		//temp write to uploads
		$tmp_path = $upload_dirs['basedir'] . '/forminator/';
		if ( ! is_dir( $tmp_path ) ) {
			wp_mkdir_p( $tmp_path );
		}
		$filename = sanitize_title( $model->name ) . '-' . date( 'ymdHis' ) . '.csv';
		$tmp_path = $tmp_path . $filename;
		$fp       = fopen( $tmp_path, 'w' );
		foreach ( $data as $fields ) {
			fputcsv( $fp, $fields );
		}
		fclose( $fp );

		return array( $tmp_path, $model );
	}

	private function get_monthly_export_date( $last_sent, $month_day ) {
		// Array [0] = year. [1] = month. [2] = day.
		$last_sent_array = explode( '-', date('Y-m-d', $last_sent) );

		$next_sent_array = array();
		$next_sent_array[0] = $last_sent_array[1] === 12 ? $last_sent_array[0] + 1 : $last_sent_array[0];
		$next_sent_array[1] = $last_sent_array[1] === 12 ? 1 : $last_sent_array[1] + 1;
		$next_sent_array[2] = $month_day;

		$is_valid_date = checkdate( $next_sent_array[1], $next_sent_array[2], $next_sent_array[0] );

		while( ! $is_valid_date ) {
			$next_sent_array[2]--;
			$is_valid_date = checkdate( $next_sent_array[1], $next_sent_array[2], $next_sent_array[0] );
		}

		$next_sent = strtotime( implode('-', $next_sent_array) );
		return $next_sent;
	}


	/**
	 * Get data mappers for retrieving entries meta
	 *
	 * @example [
	 *  [
	 *      'meta_key'  => 'FIELD_ID',
	 *      'label'     => 'LABEL',
	 *      'type'      => 'TYPE',
	 *      'sub_metas'      => [
	 *          [
	 *              'key'   => 'SUFFIX',
	 *              'label'   => 'LABEL',
	 *          ]
	 *      ],
	 *  ]
	 * ]
	 *
	 * @since   1.0.5
	 *
	 * @param Forminator_Custom_Form_Model|Forminator_Base_Form_Model $model
	 *
	 * @return array
	 */
	private function get_custom_form_export_mappers( $model ) {
		/** @var  Forminator_Custom_Form_Model $model */
		$fields              = $model->getFields();
		$ignored_field_types = Forminator_Form_Entry_Model::ignored_fields();

		/** @var  Forminator_Form_Field_Model $fields */
		$mappers = array(
			array(
				// read form model's property
				'property' => 'date_created', // must be on export
				'label'    => __( 'Submission date', Forminator::DOMAIN ),
				'type'     => 'entry_date_created',
			),
		);

		foreach ( $fields as $field ) {
			$field_type = $field->__get( 'type' );

			if ( in_array( $field_type, $ignored_field_types ) ) {
				continue;
			}

			// base mapper for every field
			$mapper             = array();
			$mapper['meta_key'] = $field->slug;
			$mapper['label']    = $field->get_label_for_entry();
			$mapper['type']     = $field_type;


			// fields that should be displayed as multi column (sub_metas)
			if ( 'name' == $field_type ) {
				$is_multiple_name = filter_var( $field->__get( 'multiple_name' ), FILTER_VALIDATE_BOOLEAN );
				if ( $is_multiple_name ) {
					$prefix_enabled      = filter_var( $field->__get( 'prefix' ), FILTER_VALIDATE_BOOLEAN );
					$first_name_enabled  = filter_var( $field->__get( 'fname' ), FILTER_VALIDATE_BOOLEAN );
					$middle_name_enabled = filter_var( $field->__get( 'mname' ), FILTER_VALIDATE_BOOLEAN );
					$last_name_enabled   = filter_var( $field->__get( 'lname' ), FILTER_VALIDATE_BOOLEAN );
					// at least one sub field enabled
					if ( $prefix_enabled || $first_name_enabled || $middle_name_enabled || $last_name_enabled ) {
						// sub metas
						$mapper['sub_metas'] = array();
						if ( $prefix_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'prefix' );
							$label                 = $field->__get( 'prefix_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'prefix',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}

						if ( $first_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'first-name' );
							$label                 = $field->__get( 'fname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'first-name',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}

						if ( $middle_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'middle-name' );
							$label                 = $field->__get( 'mname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'middle-name',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}
						if ( $middle_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'last-name' );
							$label                 = $field->__get( 'lname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'last-name',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}

					} else {
						// if no subfield enabled when multiple name remove mapper (means dont show it on export)
						$mapper = array();
					}
				}

			} elseif ( 'address' == $field_type ) {
				$street_enabled  = filter_var( $field->__get( 'street_address' ), FILTER_VALIDATE_BOOLEAN );
				$line_enabled    = filter_var( $field->__get( 'address_line' ), FILTER_VALIDATE_BOOLEAN );
				$city_enabled    = filter_var( $field->__get( 'address_city' ), FILTER_VALIDATE_BOOLEAN );
				$state_enabled   = filter_var( $field->__get( 'address_state' ), FILTER_VALIDATE_BOOLEAN );
				$zip_enabled     = filter_var( $field->__get( 'address_zip' ), FILTER_VALIDATE_BOOLEAN );
				$country_enabled = filter_var( $field->__get( 'address_country' ), FILTER_VALIDATE_BOOLEAN );
				if ( $street_enabled || $line_enabled || $city_enabled || $state_enabled || $zip_enabled || $country_enabled ) {
					$mapper['sub_metas'] = array();
					if ( $street_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'street_address' );
						$label                 = $field->__get( 'street_address_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'street_address',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $line_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'address_line' );
						$label                 = $field->__get( 'address_line_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'address_line',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $city_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'city' );
						$label                 = $field->__get( 'address_city_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'city',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $state_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'state' );
						$label                 = $field->__get( 'address_state_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'state',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $zip_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'zip' );
						$label                 = $field->__get( 'address_zip_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'zip',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $country_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'country' );
						$label                 = $field->__get( 'address_country_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'country',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
				} else {
					// if no subfield enabled when multiple name remove mapper (means dont show it on export)
					$mapper = array();
				}
			}

			if ( ! empty( $mapper ) ) {
				$mappers[] = $mapper;
			}
		}

		return $mappers;
	}
}