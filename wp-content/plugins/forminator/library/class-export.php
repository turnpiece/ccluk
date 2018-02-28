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
		$this->maybe_send_export();
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
			$key          = $_POST['form_id'] . $_POST['form_type'];
			$data[ $key ] = array(
				'form_id'   => $_POST['form_id'],
				'form_type' => $_POST['form_type'],
				'email'     => $_POST['email'],
				'interval'  => $_POST['interval'],
				'day'       => $_POST['day'],
				'hour'      => $_POST['hour'],
				'last_sent' => current_time( 'timestamp' )
			);
			update_option( 'forminator_entries_export_schedule', $data );
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
			if ( empty( $row['email'] ) ) {
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
					$next_sent = strtotime( '+30 days', $last_sent );
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
				$titles[] = $row[1]->name;
			}
			$subject = sprintf( __( "Forminator entires data for form %s", Forminator::DOMAIN ), implode( ',', $titles ) );
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
					'Question',
					'Answer',
					'Result',
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
							$row[]  = $answer['isCorrect'] == 1 ? 'Correct' : 'Incorrect';
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
				$entries = Forminator_Form_Entry_Model::get_entries( $form_id );
				$fields  = $model->getFields();
				$data    = array(
					array( 'Answer', 'Total' )
				);
				if ( ! is_null( $fields ) ) {
					foreach ( $fields as $field ) {
						$label = $field->__get( 'field_label' );
						if ( ! $label ) {
							$label = $field->title;
						}
						$slug         = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
						$countEntries = Forminator_Form_Entry_Model::count_entries_by_form_and_field( $model->id, $slug );
						$data[]       = array( $label, $countEntries );
					}
				}
				break;
			case 'cform':
				$entries = Forminator_Form_Entry_Model::get_entries( $form_id );
				$model   = Forminator_Custom_Form_Model::model()->load( $form_id );
				if ( ! is_object( $model ) ) {
					return null;
				}
				$fields = $model->getFields();
				$data   = array();
				foreach ( $entries as $entry ) {
					$row = array(
						'Date' => $entry->date_created
					);
					foreach ( $entry->meta_data as $fslug => $meta_datum ) {
						$entry_value = $meta_datum['value'];
						if ( is_array( $entry_value ) ) {
							foreach ( $entry_value as $h => $value ) {
								$label = ucfirst( str_replace( '_', ' ', $h ) );
								if ( empty( $value ) ) {
									$value = "";
								}
								$row[ $label ] = $value;
							}
						} else {
							//find the field name
							foreach ( $fields as $field ) {
								if ( $field->slug == $fslug ) {
									$label = $field->__get( 'field_label' );
									if ( ! $label ) {
										$label = $field->title;
									}
									if ( ! $label ) {
										$label = $field->__get( 'main_label' );
									}
									$entry_value = trim( $entry_value );
									if ( strlen( $entry_value ) == 0 ) {
										$entry_value = "";
									}
									$row[ $label ] = $entry_value;
								}
							}
						}
					}
					$data[] = $row;
				}
				//flatten array for csv
				$tmp = $data;
				array_multisort( array_map( 'count', $tmp ), SORT_DESC, $tmp );
				$headers = array_shift( $tmp );
				$headers = array_keys( $headers );
				$csv     = array();
				foreach ( $data as $key => $value ) {
					if ( count( $value ) == count( $headers ) ) {
						$csv[] = array_values( $value );
					} else {
						//find what key is missing
						$missing = array_diff( $headers, array_keys( $value ) );
						foreach ( $missing as $m ) {
							$pos = array_search( $m, $headers );
							array_splice( $value, $pos, 0, array( $m => '' ) );
						}
						$csv[] = array_values( $value );
					}
				}

				$data = array_merge( array( $headers ), $csv );
				break;
		}
		if ( ! is_object( $model ) ) {
			return null;
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
}