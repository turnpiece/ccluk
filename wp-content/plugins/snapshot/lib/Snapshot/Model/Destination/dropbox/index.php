<?php
/*
Snapshots Plugin Destinations Dropbox
Author: Paul Menard (Incsub)
*/

if ( ! class_exists( 'SnapshotDestinationDropbox' )
	&& stristr( WPMUDEV_SNAPSHOT_DESTINATIONS_EXCLUDE, 'SnapshotDestinationDropbox' ) === false ) {

	class SnapshotDestinationDropbox extends Snapshot_Model_Destination {

		// The slug and name are used to identify the Destination Class
		public $name_slug;
		public $name_display;

		// Do not change this! This is set from Dropbox and is the KEY/SECRET for this Dropbox App.
		const DROPBOX_APP_KEY = 'g1j0k3ob0fwcgnc';
		const DROPBOX_APP_SECRET = 'di1vr3xgf86f4fl';

		public $tokens = array();

		public $excluded_files = array();
		public $excluded_file_chars = array();

		public $dropbox_connection;
		public $oauth;

		public $snapshot_logger;
		public $snapshot_locker;

		// These vars are used when connecting and sending file to the destination. There is an
		// inteface function which populates these from the destination data.
		public $destination_info;
		public $error_array;
		public $form_errors;

		public function load_library() {
			require_once  dirname( __FILE__ ) . '/vendor/autoload.php' ;
		}

		public function on_creation() {
			//private destination slug. Lowercase alpha (a-z) and dashes (-) only please!
			$this->name_slug = 'dropbox';

			// The display name for listing on admin panels
			$this->name_display = __( 'Dropbox', SNAPSHOT_I18N_DOMAIN );

			$this->sync_excluded_files = array(
				'.desktop.ini',
				'thumbs.db',
				'.ds_store',
				'icon\r',
				'.dropbox',
				'.dropbox.attr',
				'.git',
				'.gitignore',
				'.gitmodules',
				'.svn',
				'.sass-cache',
			);

			$this->sync_excluded_file_chars = array( '<', '>', ':', '/', '\\', '|', '?', '*' );

			//add_action('wp_ajax_snapshot_destination_dropbox', array(&$this, 'destination_ajax_proc' ));

			// When returning from Dropbox Authorize the URL Query String contains the parameter 'oauth_token'. On this indicator
			// we load the stored item option and grab the new access token. Then store the options and redirect the user to
			// the Destination Dropbox form where they will finally save the destination info.

			if ( ! isset( $_REQUEST['destination-noonce-field']  ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ) {
				return;
			}

			if ( ! isset( $_GET['page'] ) || ! in_array( sanitize_text_field( $_GET['page'] ), array( 'snapshots_destinations_panel', 'snapshot_pro_destinations' ), true ) ) {
				return;
			}

			$this->load_library();

			if ( ! isset( $_REQUEST['oauth_token'] ) ) {
				return;
			}

		}

		public function init() {

			if ( isset( $this->destination_info ) ) {
				unset( $this->destination_info );
			}
			$this->destination_info = array();

			if ( isset( $this->error_array ) ) {
				unset( $this->error_array );
			}
			$this->error_array = array();

			$this->error_array['errorStatus'] = false;
			$this->error_array['sendFileStatus'] = false;
			$this->error_array['errorArray'] = array();
			$this->error_array['responseArray'] = array();

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			set_error_handler( array( &$this, 'ErrorHandler' ) );
		}

		public function validate_form_data( $d_info ) {

			check_admin_referer( 'add_dropbox_destination' );

			if ( isset( $d_info['force-authorize'] ) && 'on' === $d_info['force-authorize']  ) {
				unset( $d_info['force-authorize'] );

				if ( isset( $d_info['tokens']['access'] ) ) {
					unset( $d_info['tokens']['access'] );
				}

				if ( isset( $_POST['item'] ) ) {
					$d_info['item'] = sanitize_text_field( $_POST['item'] );
				}

				$this->load_library();

				$this->oauth = new Kunnu\Dropbox\DropboxApp( self::DROPBOX_APP_KEY, self::DROPBOX_APP_SECRET );
				$this->dropbox = new Kunnu\Dropbox\Dropbox( $this->oauth );
				$auth_helper = $this->dropbox->getAuthHelper();

				update_option( 'snapshot-dropbox-tokens', $d_info );

				$dropbox_url = $auth_helper->getAuthUrl();
				wp_redirect( $dropbox_url );
				die;
			}

			// Will contain the filtered fields from the form (d_info).
			$destination_info = array();
			$this->form_errors = array();

			if ( isset( $d_info['type'] ) ) {
				$destination_info['type'] = esc_attr( $d_info['type'] );
			}

			if ( empty( $d_info['name'] ) ) {
				$this->form_errors['name'] = esc_html__( 'Please provide a name for the destination.', SNAPSHOT_I18N_DOMAIN );
			} else {
				$destination_info['name'] = stripslashes( esc_attr( $d_info['name'] ) );
			}

			if ( empty( $d_info['directory'] ) ) {
				$this->form_errors['directory'] = __( 'Please provide a valid subdirectory to store the snapshots.', SNAPSHOT_I18N_DOMAIN );
			} else {
				$destination_info['directory'] = trim( stripslashes( esc_attr( $d_info['directory'] ) ) );
				$destination_info['directory'] = str_replace( '\\', '/', stripslashes( $destination_info['directory'] ) );
				$destination_info['directory'] = trim( $destination_info['directory'], '/' );
			}

			if ( isset( $d_info['tokens']['request']['token'] ) ) {
				$destination_info['tokens']['request']['token'] = esc_attr( $d_info['tokens']['request']['token'] );
			}

			if ( isset( $d_info['tokens']['request']['token_secret'] ) ) {
				$destination_info['tokens']['request']['token_secret'] = esc_attr( $d_info['tokens']['request']['token_secret'] );
			}

			if ( isset( $d_info['tokens']['access']['token'] ) ) {
				$destination_info['tokens']['access']['token'] = esc_attr( $d_info['tokens']['access']['token'] );
			}

			if ( isset( $d_info['tokens']['access']['token_secret'] ) ) {
				$destination_info['tokens']['access']['token_secret'] = esc_attr( $d_info['tokens']['access']['token_secret'] );
			}

			if ( isset( $d_info['tokens']['access']['access_token'] ) ) {
				$destination_info['tokens']['access']['access_token'] = esc_attr( $d_info['tokens']['access']['access_token'] );
			}

			if ( isset( $d_info['tokens']['access']['authorization_token'] ) ) {
				$destination_info['tokens']['access']['authorization_token'] = esc_attr( $d_info['tokens']['access']['authorization_token'] );
			}

			if ( isset( $destination_info['tokens']['access']['authorization_token'] ) && ! empty( $destination_info['tokens']['access']['authorization_token'] ) ) {
				$destination_classes = WPMUDEVSnapshot::instance()->get_setting( 'destinationClasses' );
				$destination_class = $destination_classes[ $destination_info['type'] ];
				try {
					$oauth = new Kunnu\Dropbox\DropboxApp( $destination_class->get_app_key(), $destination_class->get_app_secret() );
					$dropbox = new Kunnu\Dropbox\Dropbox( $oauth );
					$oauth2_token = $dropbox->getOAuth2Client()->getAccessToken( $destination_info['tokens']['access']['authorization_token'] );
					$destination_info['tokens']['access']['access_token'] = $oauth2_token['access_token'];
				} catch ( Exception $e ) {
					$message = '';
					$error_message = $e->getMessage();
					if ( ! empty( $error_message ) ) {
						$message = json_decode( $error_message );
						if ( $message && isset( $message->error_description ) ) {
							$message = $message->error_description;
						} else {
							$message = $error_message;
						}
					}
					$this->form_errors['authorization_token'] = esc_html__( 'An error occurred when attempting to connect to Dropbox: ', SNAPSHOT_I18N_DOMAIN ) . ' ' . $message;

				}
				$destination_info['tokens']['access']['authorization_token'] = '';
				$destination_info['tokens']['access']['token'] = '';
				$destination_info['tokens']['access']['token_secret'] = '';
			}

			if ( isset( $d_info['account_info'] ) ) {
				$destination_info['account_info'] = $d_info['account_info'];
			}

			return $destination_info;
		}

		/**
		 * Sets up destination info and prepares connection
		 *
		 * @since v3.1.6-beta.1
		 *
		 * @param array $destination_info Destination info to set up
		 *
		 * @return bool
		 */
		public function set_up_destination ($destination_info) {
			$this->init();
			$this->load_class_destination( $destination_info );
			$this->load_library();

			$this->oauth = new Kunnu\Dropbox\DropboxApp( $this->get_app_key(), $this->get_app_secret() );
			$this->dropbox = new Kunnu\Dropbox\Dropbox( $this->oauth );
			if (
				empty( $this->destination_info['tokens']['access']['access_token'] )
				&&
				isset( $this->destination_info['tokens']['access']['token_secret'] )
				&&
				!empty( $this->destination_info['tokens']['access']['token_secret'] )
			) {
				$oauth2_token = $this->dropbox->getOAuth2Client()->getAccessTokenFromOauth1( $this->destination_info['tokens']['access']['token'], $this->destination_info['tokens']['access']['token_secret'] );
				$oauth2_token = $oauth2_token['oauth2_token'];

				$this->destination_info['tokens']['access']['access_token'] = $oauth2_token;
			}
			try {
				$this->dropbox->setAccessToken( $this->destination_info['tokens']['access']['access_token'] );
				$this->error_array['errorArray'] = array();

			} catch ( Exception $e ) {
				$this->error_array['errorStatus'] = true;
				$this->error_array['errorArray'][] = $e->getMessage();

				return false;
			}
			return true;
		}

		/**
		 * Obtains remote items list
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param string $root Filename prefix to match.
		 *
		 * @return array A list of remote items
		 */
		public function list_remote_items ($root) {
			$items = array();

			$directory_file = '/';
			if ( strlen( $this->destination_info['directory'] ) ) {
				$directory_file = '/' . ltrim( trailingslashit( $this->destination_info['directory'] ), '/');
			}

			try {
				$response = $this->dropbox->search($directory_file, $root, array('mode' => 'filename'));
				$items = $response->getData();
				$items = isset($items['matches']) ? $items['matches'] : array();
			} catch (Exception $e) {
				$this->handle_exception($e, 'list');
			}

			return $items;
		}

		/**
		 * Parses response items into shared format
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param array $items Raw remote items
		 *
		 * @return array
		 */
		public function get_prepared_items ($items) {
			$prepared = array();
			foreach ($items as $item) {
				if (!isset($item['metadata'])) continue;
				$data = wp_parse_args(
                    $item['metadata'], array(
						'client_modified' => time(),
						'server_modified' => time(),
						'path_lower' => '',
						'name' => '',
					)
                );
				if (empty($data['path_lower'])) continue;

				$client = strtotime($data['client_modified']);
				$server = strtotime($data['server_modified']);
				$ts = min($client, $server);
				$prepared[$ts] = array(
					'created' => date('r', $ts),
					'title' => $data['name'],
					'id' => $data['path_lower'],
				);
			}
			return $prepared;
		}

		/**
		 * Removes remote file
		 *
		 * Assumes remote connection has been established already.
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param string $file_id Destination-dependent file ID.
		 *
		 * @return bool
		 */
		public function remove_file ($file_id) {
			$this->dropbox->delete($file_id);
			return true;
		}

		public function sendfile_to_remote( $destination_info, $filename ) {

			$this->init();

			$this->load_class_destination( $destination_info );

			$this->load_library();

			$this->oauth = new Kunnu\Dropbox\DropboxApp( $this->get_app_key(), $this->get_app_secret() );
			$this->dropbox = new Kunnu\Dropbox\Dropbox( $this->oauth );
			if ( empty( $this->destination_info['tokens']['access']['access_token'] ) && isset( $this->destination_info['tokens']['access']['token_secret'] ) && ! empty( $this->destination_info['tokens']['access']['token_secret'] ) ) {
				$oauth2_token = $this->dropbox->getOAuth2Client()->getAccessTokenFromOauth1( $this->destination_info['tokens']['access']['token'], $this->destination_info['tokens']['access']['token_secret'] );
				$oauth2_token = $oauth2_token['oauth2_token'];

				$this->destination_info['tokens']['access']['access_token'] = $oauth2_token;
			}
			try {
				$this->dropbox->setAccessToken( $this->destination_info['tokens']['access']['access_token'] );
				$this->error_array['errorArray'] = array();

			} catch ( Exception $e ) {
				$this->error_array['errorStatus'] = true;
				$this->error_array['errorArray'][] = $e->getMessage();

				return $this->error_array;
			}

			//$this->dropbox->setLogger($this->snapshot_logger);
			if ( ! file_exists( $filename ) ) {
				$this->error_array['errorStatus'] = true;
				$this->error_array['errorArray'][] = "File does not exists: " . basename( $filename );

				return $this->error_array;
			}

			$directory_file = '/';
			if ( strlen( $this->destination_info['directory'] ) ) {
				$directory_file = trailingslashit( $this->destination_info['directory'] );
			}
			$this->error_array['responseArray'][] = 'Sending to Dropbox Directory: ' . $directory_file;
			$this->snapshot_logger->log_message( 'Sending to Dropbox Directory: ' . $directory_file );

			$directory_file .= basename( $filename );
			if ( substr( $directory_file, 0, 1 ) !== '/' ) {
				$directory_file = '/' . $directory_file;
			}

			try {
				$result = $this->dropbox->upload( $filename, $directory_file );
				$result_name = $result->getName();
				if ( ! empty( $result_name ) ) {
					$this->error_array['responseArray'][] = 'Send file success: ' . basename( $filename );
					$this->snapshot_logger->log_message( 'Send file success: ' . basename( $filename ) );
					$this->error_array['sendFileStatus'] = true;

				} else {
					$this->error_array['errorArray'][] = $result->error_summary;
					$this->snapshot_logger->log_message( 'Send file error: ' . basename( $filename ) . ' ' . $result->error_summary );
				}

				return $this->error_array;

			} catch ( Exception $e ) {
				$this->error_array['errorStatus'] = true;
				$this->error_array['errorArray'][] = $e->getMessage();
				$this->snapshot_logger->log_message( 'Send file error: ' . basename( $filename ) . ' ' . $e->getMessage() );

				return $this->error_array;
			}
		}

		public function progress_of_files( $file_array ) {

			$locker_info = $this->snapshot_locker->get_locker_info();
			foreach ( $file_array as $_key => $_val ) {
				$locker_info[ $_key ] = $_val;
			}
			$this->snapshot_locker->set_locker_info( $locker_info );
		}

		public function syncfiles_to_remote( $destination_info, $sync_files, $sync_files_option = '' ) {

			$this->init();

			$this->load_class_destination( $destination_info );

			$this->load_library();

			$this->oauth = new Kunnu\Dropbox\DropboxApp( $this->get_app_key(), $this->get_app_secret() );
			$this->dropbox = new Kunnu\Dropbox\Dropbox( $this->oauth );
			if ( empty( $this->destination_info['tokens']['access']['access_token'] ) && isset( $this->destination_info['tokens']['access']['token_secret'] ) && ! empty( $this->destination_info['tokens']['access']['token_secret'] ) ) {
				$oauth2_token = $this->dropbox->getOAuth2Client()->getAccessTokenFromOauth1( $this->destination_info['tokens']['access']['token'], $this->destination_info['tokens']['access']['token_secret'] );
				$oauth2_token = $oauth2_token['oauth2_token'];

				$this->destination_info['tokens']['access']['access_token'] = $oauth2_token;
			}
			try {
				$this->dropbox->setAccessToken( $this->destination_info['tokens']['access']['access_token'] );
				$this->error_array['errorArray'] = array();

			} catch ( Exception $e ) {
				$this->error_array['errorStatus'] = true;
				$this->error_array['errorArray'][] = $e->getMessage();

				return $this->error_array;
			}

			$_ABSPATH = str_replace( '\\', '/', ABSPATH );

			$directory_file = '';
			if ( strlen( $this->destination_info['directory'] ) ) {
				$directory_file = trailingslashit( $this->destination_info['directory'] );
			}

			$file_counter_total = 0;
			$file_counter_item = 0;
			foreach ( $sync_files['included'] as $section => $section_files ) {
				$file_counter_total += count( $section_files );
			}
			$this->progress_of_files(
                 array(
					'files_count' => $file_counter_item,
					'files_total' => $file_counter_total,
				)
            );

			foreach ( $sync_files['included'] as $section => $section_files ) {
				$file_counter_section = count( $section_files );
				$this->snapshot_logger->log_message( "Files sync start for section: " . $section . " " . $file_counter_section . " files ---------------------" );

				$file_consecutive_errors = 0;
				$file_send_success_count = 0;

				foreach ( $section_files as $file_idx => $filename ) {
					$file_counter_item++;
					$file_send_ratio = $file_counter_item . "/" . $file_counter_total;

					$_r_filename = str_replace( $_ABSPATH, '', $filename );
					if ( ! file_exists( $filename ) ) {
						$this->snapshot_logger->log_message( "[" . $file_send_ratio . "] File does not exists: " . $_r_filename . " removed" );
						unset( $sync_files['included'][ $section ][ $file_idx ] );
						update_option( $sync_files_option, $sync_files );
						continue;
					}

					//if (filesize($filename) >= 157286400) {
					//	$this->snapshot_logger->log_message("[". $file_send_ratio ."] File is over 150Mb. Too large for Dropbox sync. ". $_r_filename);
					//	unset($sync_files['included'][$section][$file_idx]);
					//	$sync_files['excluded']['dropbox'][$section][] = $filename;
					//	update_option($sync_files_option, $sync_files);
					//	continue;
					//}

					$_filename = str_replace( '\\', '/', $filename );
					$_filename = str_replace( $_ABSPATH, '', $filename );
					$_directory_file = $directory_file . $_filename;

					$_file = strtolower( basename( $_filename ) );
					if ( array_search( $_file, $this->sync_excluded_files, true ) !== false ) {

						$this->snapshot_logger->log_message( "[" . $file_send_ratio . "] File not allowed by Dropbox." . $_r_filename );
						unset( $sync_files['included'][ $section ][ $file_idx ] );
						$sync_files['excluded']['dropbox'][ $section ][] = $filename;
						update_option( $sync_files_option, $sync_files );
						continue;

					}

					if ( strstr( $_file, $this->sync_excluded_file_chars ) !== false ) {
						//echo "File contains an invalid character not allowed by Dropbox. ". $_r_filename;
						$this->snapshot_logger->log_message( "[" . $file_send_ratio . "] File contains an invalid character not allowed by Dropbox. " . $_r_filename );
						unset( $sync_files['included'][ $section ][ $file_idx ] );
						$sync_files['excluded']['dropbox'][ $section ][] = $filename;
						update_option( $sync_files_option, $sync_files );
						continue;
					}


					if ( substr( $_directory_file, 0, 1 ) !== '/' ) {
						$_directory_file = '/' . $_directory_file;
					}


					try {
						$result = $this->dropbox->upload( $filename, $_directory_file );

						$this->snapshot_logger->log_message( "[" . $file_send_ratio . "] Sync file: " . $_filename . " -> " . $_directory_file . " success" );

						unset( $sync_files['included'][ $section ][ $file_idx ] );
						$file_send_success_count++;

						// Update our option on every 10th file to keep things updated in case of abort or failure.
						if ( $file_send_success_count > 10 ) {
							update_option( $sync_files_option, $sync_files );
							$file_send_success_count = 0;
							$this->progress_of_files( array( 'files_count' => $file_counter_item ) );
						}

						$file_consecutive_errors = 0;

					} catch ( Exception $e ) {
						//$this->error_array['errorStatus'] 	= true;
						$this->snapshot_logger->log_message( "[" . $file_send_ratio . "] Sync file: " . $_filename . " -> " . $_directory . " FAILED" );
						$this->snapshot_logger->log_message( $e->getMessage() );

						update_option( $sync_files_option, $sync_files );
						$file_send_success_count = 0;
						$this->progress_of_files( array( 'files_count' => $file_counter_item ) );

						$file_consecutive_errors++;
						if ( $file_consecutive_errors >= 10 ) {
							break;
						}

						$this->snapshot_logger->log_message( "Sleeping after error 15 seconds" );
						sleep( 15 );
					}
				}
				$this->progress_of_files( array( 'files_count' => $file_counter_item ) );

				$this->snapshot_logger->log_message( "Files sync end for section: " . $section . " ---------------------" );

			}

			update_option( $sync_files_option, $sync_files );

			if ( true !== $this->error_array['errorStatus'] ) {
				$this->error_array['sendFileStatus'] = true;
				$this->error_array['syncFilesLast'] = time();
				$this->error_array['syncFilesTotal'] = $file_counter_total;
			}

			return $this->error_array;
		}

		public function load_class_destination( $d_info ) {

			if ( isset( $d_info['type'] ) ) {
				$this->destination_info['type'] = esc_attr( $d_info['type'] );
			}

			if ( isset( $d_info['name'] ) ) {
				$this->destination_info['name'] = esc_attr( $d_info['name'] );
			}

			if ( ( isset( $d_info['directory'] ) ) && ( strlen( $d_info['directory'] ) ) ) {
				$this->destination_info['directory'] = esc_attr( $d_info['directory'] );
			} else {
				$this->destination_info['directory'] = "";
			}

			if ( isset( $d_info['tokens']['request']['token'] ) ) {
				$this->destination_info['tokens']['request']['token'] = html_entity_decode( $d_info['tokens']['request']['token'] );
			}

			if ( isset( $d_info['tokens']['request']['token_secret'] ) ) {
				$this->destination_info['tokens']['request']['token_secret'] = html_entity_decode( $d_info['tokens']['request']['token_secret'] );
			}

			if ( isset( $d_info['tokens']['access']['token'] ) ) {
				$this->destination_info['tokens']['access']['token'] = html_entity_decode( $d_info['tokens']['access']['token'] );
			}

			if ( isset( $d_info['tokens']['access']['token_secret'] ) ) {
				$this->destination_info['tokens']['access']['token_secret'] = html_entity_decode( $d_info['tokens']['access']['token_secret'] );
			}

			if ( isset( $d_info['tokens']['access']['access_token'] ) ) {
				$this->destination_info['tokens']['access']['access_token'] = esc_attr( $d_info['tokens']['access']['access_token'] );
			}

		}

		public function ErrorHandler( $errno, $errstr, $errfile, $errline ) {
			// phpcs:ignore
			if ( ! error_reporting() ) {
				return;
			}

			$errType = '';
			switch ( $errno ) {
				case E_USER_ERROR:
					$errType = "Error";
					//echo "errno=[". $errno ."]<br />";
					//echo "errstr=[". $errstr ."]<br />";
					//echo "errfile=[". $errfile ."]<br />";
					//echo "errline=[". $errline ."]<br />";

					break;

				case E_USER_WARNING:
					return;

				case E_USER_NOTICE:
					return;

				default:
					return;
			}

			$error_string = $errType . ": errno:" . $errno . " " . $errstr . " " . $errfile . " on line " . $errline;

			$this->error_array['errorStatus'] = true;
			$this->error_array['errorArray'][] = $error_string;

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				echo wp_json_encode( $this->error_array );
				die;
			}
		}

		public function get_app_key() {
			return self::DROPBOX_APP_KEY;
		}

		public function get_app_secret() {
			return self::DROPBOX_APP_SECRET;
		}

	}

	do_action( 'snapshot_register_destination', 'SnapshotDestinationDropbox' );
}