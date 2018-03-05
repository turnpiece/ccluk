<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Front_Action
 *
 * Abstract class for front functions
 *
 * @since 1.0
 */
abstract class Forminator_Front_Action {

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = '';

	public function __construct() {
		//Save entries
		if ( !empty( $this->entry_type ) ) {
			add_action( 'wp', array( $this, 'maybe_handle_submit' ), 9 );
			add_action( "wp_ajax_forminator_submit_form_" . $this->entry_type , array( $this, "save_entry" ) );
			add_action( "wp_ajax_nopriv_forminator_submit_form_" . $this->entry_type , array( $this, "save_entry" ) );
		}
	}

	/**
	 * Maybe handle form submit
	 *
	 * @since 1.0
	 */
	public function maybe_handle_submit() {
		if (
			isset( $_POST['forminator_nonce'] )
			&& wp_verify_nonce( $_POST['forminator_nonce'], 'forminator_submit_form' )
		) {
			$this->handle_submit();
		}
	}

	/**
	 * Handle submit
	 *
	 * @since 1.0
	 */
	abstract public function handle_submit();

	/**
	 * Validate ajax
	 *
	 * @since 1.0
	 * @param null $action - the HTTP action
	 * @param string $request_method
	 * @param string $nonce_field
	 *
	 * @return bool
	 */
	public function validate_ajax( $action = null, $request_method = 'POST', $nonce_field = '_wpnonce' ) {
		switch ( $request_method ) {
			case 'GET':
				$request_fields = $_GET;
				break;

			case 'REQUEST':
			case 'any':
				$request_fields = $_REQUEST;
				break;

			case 'POST':
			default:
				$request_fields = $_POST;
				break;
		}

		if ( empty( $action ) ) {
			$action = ! empty( $request_fields['action'] ) ? $request_fields['action'] : '';
		}

		if ( ! empty( $request_fields[ $nonce_field ] )
				&& wp_verify_nonce( $request_fields[ $nonce_field ], $action )
			) {
			return true;
		}
	}

	/**
	 * Save Entry
	 *
	 * @since 1.0
	 */
	public abstract function save_entry();

	/**
	 * Handle file uplload
	 *
	 * @since 1.0
	 * @param string $field_name - the input file name
	 *
	 * @return bool|array
	 */
	public function handle_file_upload( $field_name ) {
		if ( isset( $_FILES[$field_name] ) ) {
			if ( isset( $_FILES[$field_name]['name'] ) && !empty( $_FILES[$field_name]['name'] ) ) {
				$file_name 		= $_FILES[$field_name]['name'];
				$valid 			= wp_check_filetype( $file_name );

				if ( false === $valid["ext"] ) {
					return false;
				}

				$file_data 			= file_get_contents( $_FILES[$field_name]['tmp_name'] );
				$upload_dir       	= wp_upload_dir(); // Set upload folder
				$unique_file_name 	= wp_unique_filename( $upload_dir['path'], $file_name );
				$filename         	= basename( $unique_file_name ); // Create base file name

				if ( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file_path 	= $upload_dir['path'] . '/' . $filename;
					$file_url 	= $upload_dir['url'] . '/' . $filename;
				} else {
					$file_path = $upload_dir['basedir'] . '/' . $filename;
					$file_url  = $upload_dir['baseurl'] . '/' . $filename;
				}

				$file_handler = @fopen( $file_path, 'wb' );
				if ( $file_handler ) {
					@fwrite( $file_handler, $file_data );
					fclose( $file_handler );
				}
				return array(
					'file_url' 	=> $file_url,
					'file_path' => $file_path
				);
			}
		}
		return false;
	}
}