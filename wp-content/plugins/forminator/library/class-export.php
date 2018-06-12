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
	 * @var array
	 */
	private static $connected_addons = array();

	/**
	 * Return the plugin instance
	 *
	 * @return Forminator_Export
	 *
	 * @since 1.0
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
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
			'count' => $count,
		);
		update_option( 'forminator_exporter_log', $logs );

		$fp = fopen( 'php://memory', 'w' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen -- disable phpcs because it writes memory
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
		// todo verify nonce
		$post_data = $_POST;
		if ( isset( $post_data['action'] ) && 'forminator_export_entries' === $post_data['action'] ) {
			$data = $this->get_entries_export_schedule();

			if ( ! isset( $post_data['form_id'] ) || empty( $post_data['form_id'] ) ) {
				exit;
			}
			if ( ! isset( $post_data['form_type'] ) || empty( $post_data['form_type'] ) ) {
				exit;
			}

			$key       = $post_data['form_id'] . $post_data['form_type'];
			$last_sent = current_time( 'timestamp' );

			if ( 'daily' === $post_data['interval'] ) {
				$last_sent = strtotime( '-24 hours', current_time( 'timestamp' ) );
			}

			$data[ $key ] = array(
				'enabled'   => isset( $post_data['enabled'] ) ? $post_data['enabled'] : 'false',
				'form_id'   => $post_data['form_id'],
				'form_type' => $post_data['form_type'],
				'email'     => $post_data['email'],
				'interval'  => $post_data['interval'],
				'month_day' => $post_data['month_day'],
				'day'       => $post_data['day'],
				'hour'      => $post_data['hour'],
				'last_sent' => $last_sent,
			);

			update_option( 'forminator_entries_export_schedule', $data );

			$referer = wp_get_referer();
			if ( empty( $referer ) ) {
				// on same request uri `wp_get_referer` return false
				$referer = wp_get_raw_referer();
			}
			if ( ! empty( $referer ) && ! headers_sent() ) {
				// probably header sent so skip this logic to avoid erro
				$referer_query = wp_parse_url( $referer, PHP_URL_QUERY );
				if ( ! empty( $referer_query ) ) {
					wp_parse_str( $referer_query, $query_strings );
					if ( ! empty( $query_strings ) && isset( $query_strings['page'] ) && 'forminator-entries' === $query_strings['page'] ) {

						// additional redirect parameter on global entries page

						$redirect = add_query_arg(
							array(
								'form_id' => $post_data['form_id'],
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
		$data     = $this->get_entries_export_schedule();
		$receipts = array();
		foreach ( $data as $row ) {
			if ( ! isset( $row['enabled'] ) || ( isset( $row['enabled'] ) && 'false' === $row['enabled'] ) || ( isset( $row['email'] ) && empty( $row['email'] ) ) ) {
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
					$next_sent  = $this->get_monthly_export_date( $last_sent, $month_date );
					$next_sent  = date( 'Y-m-d', $next_sent ) . ' ' . $row['hour'];
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
			$ids           = array();
			$files         = array();
			$total_entries = array();
			$titles        = array();
			foreach ( $info as $row ) {
				$ids[]   = $row[1]->id . $row[3];
				$files[] = $row[0];
				if ( isset( $row[1]->settings['formName'] ) ) {
					$titles[] = $row[1]->settings['formName'];
				} else {
					$titles[] = $row[1]->name;
				}
				$total_entries[] = $row[2];
			}
			$subject        = sprintf( __( "Entries data for %s", Forminator::DOMAIN ), implode( ', ', $titles ) );
			$message_header = sprintf( __( 'Your scheduled results have arrived! Forminator has tabulated the responses and packaged %1$d of total entries from %2$s.' ),
			                           array_sum( $total_entries ),
			                           implode( ', ', $titles ) );
			wp_mail( $email, $subject, $message_header, array(), $files );
			foreach ( $files as $file ) {
				@unlink( $file ); // phpcs:ignore
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
		$date    = '';
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
					__( 'Date', Forminator::DOMAIN ),
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
								$row[]  = $entry->date_created_sql;
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
							$row[]  = $entry->date_created_sql;
							$row[]  = $answer['question'];
							$row[]  = $answer['answer'];
							$row[]  = 1 === $answer['isCorrect'] ? __( 'Correct', Forminator::DOMAIN ) : __( 'Incorrect', Forminator::DOMAIN );
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
					array( __( 'Date', Forminator::DOMAIN ), __( 'Answer', Forminator::DOMAIN ), __( 'Total', Forminator::DOMAIN ) ),
				);
				if ( ! is_null( $fields ) ) {
					foreach ( $fields as $field ) {
						$label = $field->__get( 'field_label' );
						if ( ! $label ) {
							$label = $field->title;
						}
						if ( isset($entries[0]) ) {
							$date = $entries[0]->date_created_sql;
						}
						$slug          = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
						$count_entries = 0;
						if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
							$count_entries = $map_entries[ $slug ];
						}
						$data[] = array( $date, $label, $count_entries );
					}
				}
				break;
			case 'cform':
				$entries = Forminator_Form_Entry_Model::get_entries( $form_id );
				$model   = Forminator_Custom_Form_Model::model()->load( $form_id );
				if ( ! is_object( $model ) ) {
					return null;
				}

				$mappers       = $this->get_custom_form_export_mappers( $model );
				$addon_mappers = $this->attach_addons_on_export_render_title_row( $form_id );

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

					// Addon columns
					$addon_data = $this->attach_addons_on_export_render_entry_row( $form_id, $entry );

					foreach ( $addon_mappers as $mapper_id => $mapper ) {
						if ( isset( $addon_data[ $mapper_id ] ) ) {
							$data[] = $addon_data[ $mapper_id ];
						}
					}

					$addon_datas = '';

//					foreach ( $addon_mappers as $title_id => $title ) {
//						$data[] = '';
//					}

					//Add additional data from addon

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

				//additional addon headers
				foreach ( $addon_mappers as $mapper ) {
					$headers[] = $mapper;
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
		$model         = $data[1];
		$count_entries = $data[2];
		$data          = $data[0];
		$upload_dirs   = wp_upload_dir();
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
		// todo refactor saving file to wp_filesystem
		fclose( $fp );

		return array( $tmp_path, $model, $count_entries );
	}

	private function get_monthly_export_date( $last_sent, $month_day ) {
		// Array [0] = year. [1] = month. [2] = day.
		$last_sent_array = explode( '-', date( 'Y-m-d', $last_sent ) );

		$next_sent_array    = array();
		$next_sent_array[0] = 12 === $last_sent_array[1] ? $last_sent_array[0] + 1 : $last_sent_array[0];
		$next_sent_array[1] = 12 === $last_sent_array[1] ? 1 : $last_sent_array[1] + 1;
		$next_sent_array[2] = $month_day;

		$is_valid_date = checkdate( $next_sent_array[1], $next_sent_array[2], $next_sent_array[0] );

		while ( ! $is_valid_date ) {
			$next_sent_array[2] --;
			$is_valid_date = checkdate( $next_sent_array[1], $next_sent_array[2], $next_sent_array[0] );
		}

		$next_sent = strtotime( implode( '-', $next_sent_array ) );

		return $next_sent;
	}


	/**
	 * Get data mappers for retrieving entries meta
	 *
	 * @example {
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
	 *  ]...
	 * }
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

			if ( in_array( $field_type, $ignored_field_types, true ) ) {
				continue;
			}

			// base mapper for every field
			$mapper             = array();
			$mapper['meta_key'] = $field->slug;
			$mapper['label']    = $field->get_label_for_entry();
			$mapper['type']     = $field_type;


			// fields that should be displayed as multi column (sub_metas)
			if ( 'name' === $field_type ) {
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

			} elseif ( 'address' === $field_type ) {
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

	/**
	 * Additional Column on Title(first) Row of Export data from Addon
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::on_export_render_title_row()
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	private function attach_addons_on_export_render_title_row( $form_id ) {
		$additional_headers = array();
		//find is_form_connected
		$connected_addons = $this->get_connected_addons( $form_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks         = $connected_addon->get_addon_form_hooks( $form_id );
				$addon_headers      = $form_hooks->on_export_render_title_row();
				$addon_headers      = $this->format_addon_additional_headers( $connected_addon, $addon_headers );
				$additional_headers = array_merge( $additional_headers, $addon_headers );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_export_render_title_row', $e->getMessage() );
			}

		}

		return $additional_headers;
	}

	/**
	 * Format additional header given by addon
	 * Format used is `forminator_addon_export_title_{$addon_slug}_{$title_id_data_from_addon}`
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $addon_headers
	 *
	 * @return array
	 */
	private function format_addon_additional_headers( Forminator_Addon_Abstract $addon, $addon_headers ) {
		$formatted_headers = array();
		if ( ! is_array( $addon_headers ) || empty( $addon_headers ) ) {
			return $formatted_headers;
		}

		foreach ( $addon_headers as $title_id => $title ) {
			if ( ! is_scalar( $title ) || empty( $title ) ) {
				continue; // skip on empty title
			}

			// avoid collistion with other addon ids
			$title_id = 'forminator_addon_export_title_' . $addon->get_slug() . '_' . $title_id;

			$formatted_headers[ $title_id ] = $title;
		}

		return $formatted_headers;
	}

	/**
	 * Add addons export render entry row
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::on_export_render_entry()
	 * @since 1.1
	 *
	 * @param                             $form_id
	 * @param Forminator_Form_Entry_Model $entry_model
	 *
	 * @return array
	 */
	private function attach_addons_on_export_render_entry_row( $form_id, Forminator_Form_Entry_Model $entry_model ) {
		$additional_data = array();
		//find is_form_connected
		$connected_addons = $this->get_connected_addons( $form_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks      = $connected_addon->get_addon_form_hooks( $form_id );
				$meta_data       = forminator_find_addon_meta_data_from_entry_model( $connected_addon, $entry_model );
				$addon_data      = $form_hooks->on_export_render_entry( $entry_model, $meta_data );
				$addon_data      = $this->format_addon_additional_data( $connected_addon, $addon_data );
				$additional_data = array_merge( $additional_data, $addon_data );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_export_render_entry', $e->getMessage() );
			}

		}

		return $additional_data;
	}

	/**
	 * Format addional data form addons to match requirement of export
	 * Format used is `forminator_addon_export_title_{$addon_slug}_{$title_id_data_from_addon}`
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $addon_data
	 *
	 * @return array
	 */
	private function format_addon_additional_data( Forminator_Addon_Abstract $addon, $addon_data ) {
		$formatted_data = array();
		if ( ! is_array( $addon_data ) || empty( $addon_data ) ) {
			return $formatted_data;
		}

		foreach ( $addon_data as $title_id => $value ) {
			$value = Forminator_Form_Entry_Model::meta_value_to_string( 'addon_' . $addon->get_slug(), $value );

			// avoid collistion with other addon ids
			$title_id = 'forminator_addon_export_title_' . $addon->get_slug() . '_' . $title_id;

			$formatted_data[ $title_id ] = $value;
		}

		return $formatted_data;
	}

	/**
	 * Get Connected Addons for form_id, avoid overhead for checking connected addons many times
	 *
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return array|Forminator_Addon_Abstract[]
	 */
	public function get_connected_addons( $form_id ) {
		if ( ! isset( self::$connected_addons[ $form_id ] ) ) {
			self::$connected_addons[ $form_id ] = array();

			$connected_addons = forminator_get_addons_instance_connected_with_form( $form_id );

			foreach ( $connected_addons as $connected_addon ) {
				try {
					$form_hooks = $connected_addon->get_addon_form_hooks( $form_id );
					if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
						self::$connected_addons[ $form_id ][] = $connected_addon;
					}
				} catch ( Exception $e ) {
					forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to get_addon_form_hooks', $e->getMessage() );
				}
			}
		}

		return self::$connected_addons[ $form_id ];
	}

	/**
	 * Get Entries Export Schedule
	 *
	 * Basic checking for export schedule
	 *
	 * @since 1.1
	 * @return array
	 */
	public function get_entries_export_schedule() {
		$opt           = get_option( 'forminator_entries_export_schedule', array() );
		$validated_opt = $opt;

		foreach ( $validated_opt as $key => $value ) {
			if ( ! $value['form_id'] || ! $value['form_type'] ) {
				// unschedule no form id exist
				unset( $validated_opt[ $key ] );
			}
		}

		if ( $validated_opt !== $opt ) {
			update_option( 'forminator_entries_export_schedule', $validated_opt );
		}

		return $validated_opt;
	}
}