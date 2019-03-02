<?php
/*
Snapshots Plugin Destinations Dropbox
Author: Paul Menard (Incsub)
*/

if ( ( ! class_exists( 'Snapshot_Model_Destination_AWS' ) ) && ( version_compare( PHP_VERSION, "5.5.0", ">=" ) )
     && ( stristr( WPMUDEV_SNAPSHOT_DESTINATIONS_EXCLUDE, 'Snapshot_Model_Destination_AWS' ) === false )
) {
	// //require_once( dirname( __FILE__ ) . '/amazon-s3-php-class/S3.php' );
	// if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
	// 	require_once  dirname( __FILE__ ) . '/vendor/autoload.php' ;
	// }

	class Snapshot_Model_Destination_AWS extends Snapshot_Model_Destination {

		const REGION_US_E1 = 'us-east-1';
		const REGION_US_W2 = 'us-west-2';
		const REGION_US_W1 = 'us-west-1';
		const REGION_EU_W1 = 'eu-west-1';
		const REGION_APAC_SE1 = 'ap-southeast-1';
		const REGION_APAC_SE2 = 'ap-southeast-2';
		const REGION_APAC_NE1 = 'ap-northeast-1';
		const REGION_SA_E1 = 'sa-east-1';

		const STORAGE_STANDARD = 'STANDARD';
		const STORAGE_REDUCED = 'REDUCED_REDUNDANCY';

		const ACL_PRIVATE = 'private';
		const ACL_PUBLIC = 'public-read';
		const ACL_OPEN = 'public-read-write';
		const ACL_AUTH_READ = 'authenticated-read';

		// The slug and name are used to identify the Destination Class
		public $name_slug;
		public $name_display;

		public $aws_connection;

		// These vars are used when connecting and sending file to the destination. There is an
		// inteface function which populates these from the destination data.
		public $destination_info;
		public $error_array;
		public $form_errors;

		private $_regions = array();
		private $_ssl = array();
		private $_storage = array();
		private $_acl = array();

		public function load_library() {
			if ( ( ! class_exists( 'Aws\S3\S3Client' ) || class_exists( 'Aws\Sdk' ) ) && ( ! class_exists( 'GuzzleHttp\Client' ) || class_exists( 'GuzzleHttp\Psr7\Response' ) ) ) {
				require_once  dirname( __FILE__ ) . '/vendor/autoload.php' ;
			}
		}

		public function get_regions(){
			return $this->_regions;
		}

		public function get_storage(){
			return $this->_storage;
		}

		public function get_acl(){
			return $this->_acl;
		}

		public function on_creation() {
			//private destination slug. Lowercase alpha (a-z) and dashes (-) only please!
			$this->name_slug = 'aws';

			// The display name for listing on admin panels
			$this->name_display = __( 'Amazon S3', SNAPSHOT_I18N_DOMAIN );

			add_action( 'wp_ajax_snapshot_destination_aws', array( &$this, 'destination_ajax_proc' ) );
			$this->load_scripts();
		}

		public function load_scripts() {
			if ( ! isset( $_REQUEST['destination-noonce-field']  ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ) {
				return;
			}

			if ( ( ! isset( $_GET['page'] ) ) || ( ! in_array( sanitize_text_field( $_GET['page'] ), array( "snapshots_destinations_panel", "snapshot_pro_destinations" ), true ) ) ) {
				return;
			}

			if ( ( ! isset( $_GET['type'] ) ) || ( sanitize_text_field( $_GET['type'] ) !== $this->name_slug ) ) {
				return;
			}


			if( sanitize_text_field( $_GET['page'] ) === "snapshots_destinations_panel" ){
				wp_enqueue_script( 'snapshot-destination-aws-js', plugins_url( '/js/snapshot_destination_aws.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_style( 'snapshot-destination-aws-css', plugins_url( '/css/snapshot_destination_aws.css', __FILE__ ) );
			} else {
				wp_enqueue_script( 'snapshot-destination-aws-js', plugins_url( '/js/new_snapshot_destination_aws.js', __FILE__ ), array( 'jquery' ) );
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

			$this->error_array['errorStatus']    = false;
			$this->error_array['sendFileStatus'] = false;
			$this->error_array['errorArray']     = array();
			$this->error_array['responseArray']  = array();

			// Kill our instance of the AWS connection
			if ( isset( $this->aws_connection ) ) {
				unset( $this->aws_connection );
			}

			// We use set_error_handler() as logging code and not debug code.
			// phpcs:ignore
			set_error_handler( array( &$this, 'ErrorHandler' ) );

			$this->_ssl = array(
				'yes' => 'Yes',
				'no'  => 'No'
			);

			$this->_regions = array(
				self::REGION_US_E1            => __( 'US Standard', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_US_W2            => __( 'US West (Oregon) Region', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_US_W1            => __( 'US West (Northern California) Region', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_EU_W1            => __( 'EU (Ireland) Region', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_APAC_SE1         => __( 'Asia Pacific (Singapore) Region', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_APAC_SE2         => __( 'Asia Pacific (Sydney) Region', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_APAC_NE1         => __( 'Asia Pacific (Tokyo) Region', SNAPSHOT_I18N_DOMAIN ),
				self::REGION_SA_E1            => __( 'South America (Sao Paulo) Region', SNAPSHOT_I18N_DOMAIN ),
				'other'                           => __( 'other', SNAPSHOT_I18N_DOMAIN )
			);

			$this->_storage = array(
				self::STORAGE_STANDARD => __( 'Standard', SNAPSHOT_I18N_DOMAIN ),
				self::STORAGE_REDUCED  => __( 'Reduced Redundancy', SNAPSHOT_I18N_DOMAIN )
			);

			$this->_acl = array(
				self::ACL_PRIVATE   => __( 'Private', SNAPSHOT_I18N_DOMAIN ),
				self::ACL_PUBLIC    => __( 'Public Read', SNAPSHOT_I18N_DOMAIN ),
				self::ACL_OPEN      => __( 'Public Read/Write', SNAPSHOT_I18N_DOMAIN ),
				self::ACL_AUTH_READ => __( 'Authenticated Read', SNAPSHOT_I18N_DOMAIN )
			);

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
					break;

				case E_USER_WARNING:
					$errType = "Warning";
					break;

				case E_USER_NOTICE:
					$errType = "Notice";
					break;

				default:
					$errType = "Unknown";
					break;
			}

			// phpcs:ignore
			if ( ! ( error_reporting() & $errno ) ) {
				return;
			}

			$error_string = $errType . ": errno:" . $errno . " " . $errstr . " " . $errfile . " on line " . $errline;

			$this->error_array['errorStatus']  = true;
			$this->error_array['errorArray'][] = $error_string;

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				echo wp_json_encode( $this->error_array );
				die();
			}
		}

		public function sendfile_to_remote( $destination_info, $filename ) {
			$this->init();

			$this->load_class_destination( $destination_info );

			if ( ! $this->login() ) {
				return $this->error_array;
			}

			$test_connection = false;
			if ( ! $this->send_file( $filename, $test_connection ) ) {
				return $this->error_array;
			}

			return $this->error_array;
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

			$prefix = array();
			if (!empty($this->destination_info['directory'])) {
				$dirs = explode(',', $this->destination_info['directory']);
				$prefix = !empty($dirs[0]) ? array($dirs[0]) : array();
			}
			$prefix[] = $root;

			if ( ! isset( $this->aws_connection ) || ! $this->aws_connection ) {
				return 0;
			}

			$resp = false;
			try {
				$resp = $this->aws_connection->listObjects(
					array(
						'Bucket' => $this->destination_info['bucket'],
						'Prefix' => join('/', $prefix),
					)
				);
			} catch (Exception $e) {
				$this->handle_exception($e, 'listing');
			}

			if ( $resp ) {
				$resp_array = $resp->toArray();
				foreach ($resp_array['Contents'] as $item) {
					$items[] = $item;
				}

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
				$ts = strtotime((string)$item['LastModified']);
				$path = (string)$item['Key'];

				$prepared[$ts] = array(
					'created' => date('r', $ts),
					'title' => basename($path),
					'id' => $path,
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
			$resp = $this->aws_connection->deleteObject(array(
				'Bucket' => $this->destination_info['bucket'],
				'Key' => $file_id,
			));
			return (bool)$resp;
		}

		public function destination_ajax_proc() {
			$this->init();
			check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

			if ( ! isset( $_POST['snapshot_action'] ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Missing 'snapshot_action' value.";
				echo wp_json_encode( $this->error_array );
				die();
			}

			if ( ! isset( $_POST['destination_info'] ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Missing 'destination_info' values.";
				echo wp_json_encode( $this->error_array );
				die();
			}
			$destination_info = $_POST['destination_info'];

			if ( ! $this->validate_form_data( $destination_info ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = implode( ', ', $this->form_errors );
				echo wp_json_encode( $this->error_array );
				die();
			}

			$this->load_class_destination( $destination_info );

			if ( "connection-test" === $_POST['snapshot_action'] ) {

				if ( ! $this->login() ) {
					echo wp_json_encode( $this->error_array );
					die();
				}

				$tmpfname = tempnam( sys_get_temp_dir(), 'Snapshot_' );
				global $wp_filesystem;

				if( Snapshot_Helper_Utility::connect_fs() ) {
					$wp_filesystem->put_contents($tmpfname, "WPMU DEV Snapshot Test connection file.", FS_CHMOD_FILE);
				} else {
					$this->error_array['responseArray'][] = "Cannot initialize filesystem ";
					echo wp_json_encode( $this->error_array );
					die();
				}

				$test_connection = true;
				$this->send_file( $tmpfname, $test_connection );
				echo wp_json_encode( $this->error_array );
				die();

			} else if ( "aws-get-bucket-list" === $_POST['snapshot_action'] ) {

				if ( ! $this->login() ) {
					echo wp_json_encode( $this->error_array );
					die();
				}

				if ( ! $this->get_buckets() ) {
					echo wp_json_encode( $this->error_array );
					die();
				}

				echo wp_json_encode( $this->error_array );
				die();
			}

			echo wp_json_encode( $this->error_array );
			die();
		}

		public function login() {
			$this->load_library();
			//echo "destination_info<pre>"; print_r($this->destination_info); echo "</pre>";
			$this->error_array['responseArray'][] = "Connecting to AWS ";
			if ( version_compare(PHP_VERSION, '5.5', '<') ) {
				// Too old PHP, can't do anything about it.
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Could not connect to AWS due to old PHP version.";
				return false;
			}

			// Before instantiate the S3Client, we check the setup to see if there are older versions of the libs used in the SDK.
			if ( class_exists( 'GuzzleHttp\Client' ) ) {
				if ( ! class_exists( 'GuzzleHttp\Psr7\Response' ) ) {
					$this->error_array['errorStatus']  = true;
					$this->error_array['errorArray'][] = "Error: Could not connect to AWS most probably due to a plugin conflict.";
					return false;
				}
			}

			if ( class_exists( 'Aws\S3\S3Client' ) ) {
				if ( ! class_exists( 'Aws\Sdk' ) ) {
					$this->error_array['errorStatus']  = true;
					$this->error_array['errorArray'][] = "Error: Could not connect to AWS most probably due to a plugin conflict.";
					return false;
				}
			}

			if ( "yes" === $this->destination_info['ssl'] ) {
				$use_ssl                              = true;
				$this->error_array['responseArray'][] = "Using SSL: Yes";
			} else {
				$use_ssl                              = false;
				$this->error_array['responseArray'][] = "Using SSL: No";
			}

			if ( 'other' === $this->destination_info['region'] ) {
				$connection_region = $this->destination_info['region-other'];
			} else {
				$connection_region = $this->destination_info['region'];
			}
			require  dirname( __FILE__ ) . '/login.php' ;

			return $status;
		}

		public function get_buckets() {
			try {
				$buckets = $this->aws_connection->listBuckets();
			} catch ( Exception $e ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Could not list buckets :" . $e->getMessage();

				return false;
			}

			if ( ( $buckets['@metadata']['statusCode'] < 200 ) || ( $buckets['@metadata']['statusCode'] >= 300 ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Could not list buckets :" . $buckets->body->Message;

				return false;
			}

			if ( ( ! isset( $buckets['Buckets'] ) ) || ( count( $buckets['Buckets'] ) < 1 ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: No Buckets found";

				return false;
			}

			$this->error_array['responseArray'][0] = '';
			foreach ($buckets['Buckets'] as $bucket) {
				$this->error_array['responseArray'][0] .= '<option value="' . $bucket['Name'] . '" ';

				if ( $this->destination_info['bucket'] === $bucket['Name'] ) {
					$this->error_array['responseArray'][0] .= ' selected="selected" ';
				}
				$this->error_array['responseArray'][0] .= '>' . $bucket['Name'] . '</option>';
			}

			return true;
		}

		public function send_file( $filename, $test_connection ) {
			if ( ! empty( $this->destination_info['directory'] ) ) {
				if ( "/" === $this->destination_info['directory'][0] ) {
					$this->destination_info['directory'] = substr( $this->destination_info['directory'], 1 );
				}

				$this->destination_info['directory'] = trailingslashit( $this->destination_info['directory'] );
			}
			$remote_filename = $this->destination_info['directory'] . basename( $filename );

			if ( ! $test_connection ) {
				if ( ! file_exists( $filename ) ) {
					$this->error_array['errorStatus']  = true;
					$this->error_array['errorArray'][] = "Error: Could not find file to upload";

					return false;
				}

				$upload = $this->get_initialized_upload( $filename, $remote_filename );

				if ( ! $upload ) {
					$this->error_array['errorStatus']  = true;
					$this->error_array['errorArray'][] = "Error: Could not initialize the backup upload";

					return false;
				}

				$this->error_array['responseArray'][] = "Using Storage: " . $this->_storage[ $this->destination_info['storage'] ];
				$this->error_array['responseArray'][] = "Using ACL: " . $this->destination_info['acl'];
				$this->error_array['responseArray'][] = "Sending file to: Bucket: " . $this->destination_info['bucket'] .
														": Directory: " . $this->destination_info['directory'];

				$is_done = true;

				$part = $upload->get_next_part();
				if ( ! empty( $part ) ) {
					$is_done = false;
					$idx = $part->get_index();
					try {
						$response = $this->aws_connection->uploadPart(array(
							'Bucket' => $this->destination_info['bucket'],
							'Key' => $remote_filename,
							'UploadId' => $upload->get_transfer_id(),
							'PartNumber' => $part->get_part_number(),
							'Body' => $upload->get_payload( $part ),
						));
						$upload->complete_part( $idx );
						$upload->save();

						$is_done = $upload->is_done();
					} catch( Exception $e ) {
						$this->error_array['errorStatus']  = true;
						$this->error_array['errorArray'][] = "Error: Send file failed :" . $e->getMessage();

						return false;
					}
				}

				// Check if upload is finished and if it did, finalize it and log success message.
				if ( $is_done ) {
					$finalized_upload = $this->finalize_upload( $upload, $remote_filename );
					if ( $finalized_upload ) {
						$this->error_array['responseArray'][] = "Send file success: " . basename( $filename );
						$this->error_array['sendFileStatus']  = true;
					} else {
						$this->error_array['errorStatus']  = true;
						$this->error_array['errorArray'][] = "Unable to finalize the upload";

						return false;
					}

				} else {
					// Check if the non-finished upload has already tried to send all of its parts.
					if ( ( $idx + 1 ) === count($upload->get_parts() ) ) {
						// If so, we can not recover, so log failed message.
						$this->error_array['errorStatus']  = true;
						$this->error_array['errorArray'][] = "Unable to finalize the upload";

						return false;
					}
				}

			} else {
				try {
					$bucket_exists = $this->aws_connection->headBucket(array(
						'Bucket' => $this->destination_info['bucket'],
					));
				} catch ( Exception $e ) {
					// Display error message
					$this->error_array['errorStatus']  = true;
					$this->error_array['errorArray'][] = "Error: " . $e->getMessage();
					echo wp_json_encode( $this->error_array );
					die();
				}

				$this->error_array['responseArray'][] = "Using Storage: " . $this->_storage[ $this->destination_info['storage'] ];
				$this->error_array['responseArray'][] = "Using ACL: " . $this->destination_info['acl'];
				$this->error_array['responseArray'][] = "Sending file to: Bucket: " . $this->destination_info['bucket'] .
														": Directory: " . $this->destination_info['directory'];

				try {
					$s3Client = $this->aws_connection;
					$result = $s3Client->putObject(array(
						'ACL'        => $this->destination_info['acl'],
						'Bucket' => $this->destination_info['bucket'],
						'Key' => $remote_filename,
						'SourceFile' => $filename,
						'StorageClass'    => $this->destination_info['storage'],
					));

					$result = $s3Client->getObject(array(
						'Bucket' => $this->destination_info['bucket'],
						'Key' => $remote_filename,
					));
					$result_array = $result->toArray();

					if ( ( $result_array["@metadata"]["statusCode"] >= 200 ) && ( $result_array["@metadata"]["statusCode"] < 300 ) ) {
						$this->error_array['responseArray'][] = "Send file success: " . basename( $filename );
						$this->error_array['sendFileStatus']  = true;

						return true;

					} else {
						$this->error_array['errorStatus']  = true;
						$this->error_array['errorArray'][] = 'Error: Send file failed :' . $result_array['status'] . ' : ' . $message;

						return false;
					}
				} catch ( Exception $e ) {
					$this->error_array['errorStatus']  = true;
					$this->error_array['errorArray'][] = "Error: Send file failed :" . $e->getMessage();

					return false;
				}
			}

		}

		public function load_class_destination( $d_info ) {

			if ( isset( $d_info['type'] ) ) {
				$this->destination_info['type'] = esc_attr( $d_info['type'] );
			}

			if ( isset( $d_info['name'] ) ) {
				$this->destination_info['name'] = esc_attr( $d_info['name'] );
			}

			if ( isset( $d_info['awskey'] ) ) {
				$this->destination_info['awskey'] = html_entity_decode( $d_info['awskey'] );
			}

			if ( ( isset( $d_info['secretkey'] ) ) && ( strlen( $d_info['secretkey'] ) ) ) {
				$this->destination_info['secretkey'] = html_entity_decode( $d_info['secretkey'] );
			}

			if ( ( isset( $d_info['ssl'] ) ) && ( strlen( $d_info['ssl'] ) ) ) {
				if ( isset( $this->_ssl[ esc_attr( $d_info['ssl'] ) ] ) ) {
					$this->destination_info['ssl'] = esc_attr( $d_info['ssl'] );
				} else {
					$this->destination_info['ssl'] = "no";
				}
			} else {
				$this->destination_info['ssl'] = "no";
			}

			if ( ( isset( $d_info['region'] ) ) && ( strlen( $d_info['region'] ) ) ) {
				if ( isset( $this->_regions[ esc_attr( $d_info['region'] ) ] ) ) {
					$this->destination_info['region'] = esc_attr( $d_info['region'] );
				} else {
					$this->destination_info['region'] = 's3.amazonaws.com';
				}
			} else {
				$this->destination_info['region'] = self::REGION_US_E1;
			}

			if ( ( isset( $d_info['region-other'] ) ) && ( strlen( $d_info['region-other'] ) ) ) {
				$this->destination_info['region-other'] = $d_info['region-other'];
			}

			if ( ( isset( $d_info['storage'] ) ) && ( strlen( $d_info['storage'] ) ) ) {
				if ( isset( $this->_storage[ esc_attr( $d_info['storage'] ) ] ) ) {
					$this->destination_info['storage'] = esc_attr( $d_info['storage'] );
				} else {
					$this->destination_info['storage'] = self::STORAGE_STANDARD;
				}
			} else {
				$this->destination_info['storage'] = self::STORAGE_STANDARD;
			}

			if ( ( isset( $d_info['acl'] ) ) && ( strlen( $d_info['acl'] ) ) ) {
				if ( isset( $this->_acl[ $d_info['acl'] ] ) ) {
					$this->destination_info['acl'] = $d_info['acl'];
				} else {
					$this->destination_info['acl'] = self::ACL_PRIVATE;
				}
			} else {
				$this->destination_info['acl'] = self::ACL_PRIVATE;
			}

			if ( ( isset( $d_info['bucket'] ) ) && ( strlen( $d_info['bucket'] ) ) ) {
				$this->destination_info['bucket'] = esc_attr( $d_info['bucket'] );
			} else {
				$this->destination_info['bucket'] = "";
			}

			if ( ( isset( $d_info['directory'] ) ) && ( strlen( $d_info['directory'] ) ) ) {
				$this->destination_info['directory'] = esc_attr( $d_info['directory'] );
			} else {
				$this->destination_info['directory'] = "";
			}
		}

		public function validate_form_data( $d_info ) {
			$this->init();

			// Will contain the filtered fields from the form (d_info).
			$destination_info = array();
			$this->form_errors = array();

			$text_fields = array( 'type', 'name', 'awskey', 'secretkey', 'ssl', 'bucket', 'directory' );

			$required_fields = array(
				'name' => __( 'Name is required', SNAPSHOT_I18N_DOMAIN ),
				'awskey' => __( 'AWS Key is required', SNAPSHOT_I18N_DOMAIN ),
				'secretkey' => __( 'AWS Secret Key is required', SNAPSHOT_I18N_DOMAIN ),
			);

			$destination_info = $this->validate_text_fields( $text_fields, $d_info, $destination_info, $required_fields );

			$destination_info['ssl'] = ( 'yes' === $destination_info['ssl'] ) ? 'yes' : 'no';

			if ( empty( $d_info['region'] ) ) {
				$destination_info['region'] = self::REGION_US_E1;
			} else {
				$region = esc_attr( $d_info['region'] );
				$destination_info['region'] = isset( $this->_regions[ $region ] ) ? $region : self::REGION_US_E1;
			}

			if ( ! empty( $d_info['region-other'] ) ) {
				$destination_info['region-other'] = $d_info['region-other'];
			}


			if ( empty( $d_info['storage'] ) ) {
				$destination_info['storage'] = self::STORAGE_STANDARD;
			} else {
				$storage = esc_attr( $d_info['storage'] );
				$destination_info['storage'] = isset( $this->_storage[ $storage ] ) ? $storage : self::STORAGE_STANDARD;
			}

			if ( empty( $d_info['acl'] ) ) {
				$destination_info['acl'] = self::ACL_PRIVATE;
			} else {
				$acl = esc_attr( $d_info['acl'] );
				$destination_info['acl'] = isset( $this->_acl[ $acl ] ) ? $acl : self::ACL_PRIVATE;
			}

			//var_dump( $destination_info ); exit;

			return $destination_info;
		}

		public function display_listing_table( $destinations, $edit_url, $delete_url ) {

			?>
			<table class="widefat">
				<thead>
				<tr class="form-field">
					<th class="snapshot-col-delete"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-access-key"><?php esc_html_e( 'AWS Access Key ID', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-bucket"><?php esc_html_e( 'Bucket', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-directory"><?php esc_html_e( 'Directory', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-used"><?php esc_html_e( 'Used', SNAPSHOT_I18N_DOMAIN ); ?></th>
				</tr>
				<thead>
				<tbody>
				<?php
				if ( ( isset( $destinations ) ) && ( count( $destinations ) ) ) {

					foreach ( $destinations as $idx => $item ) {

						if ( ! isset( $row_class ) ) {
							$row_class = "";
						}
						$row_class = ( '' === $row_class ? 'alternate' : '' );

						?>
						<tr class="<?php echo esc_attr( $row_class ); ?>
						<?php
						if ( isset( $item['type'] ) ) {
							echo esc_attr( ' snapshot-row-filter-type-' . $item['type'] );
						}
						?>
						">
							<td class="snapshot-col-delete"><input type="checkbox"
																	name="delete-bulk-destination[<?php echo esc_attr( $idx ); ?>]"
																	id="delete-bulk-destination-<?php echo esc_attr( $idx ); ?>">
							</td>

							<td class="snapshot-col-name"><a
									href="<?php echo esc_url( $edit_url ); ?>item=<?php echo esc_attr( $idx ); ?>"><?php echo esc_html( stripslashes( $item['name'] ) ); ?></a>

								<div class="row-actions" style="margin:0; padding:0;">
									<span class="edit"><a
											href="<?php echo esc_url( $edit_url ); ?>item=<?php echo esc_attr( $idx ); ?>"><?php esc_html_e( 'edit', SNAPSHOT_I18N_DOMAIN ); ?></a></span>
									| <span class="delete"><a
											href="<?php echo esc_url( $delete_url ); ?>item=<?php echo esc_attr( $idx ); ?>&amp;destination-noonce-field=<?php echo esc_attr( wp_create_nonce( 'snapshot-destination' ) ); ?>"><?php esc_html_e( 'delete', SNAPSHOT_I18N_DOMAIN ); ?></a></span>
								</div>
							</td>
							<td class="snapshot-col-server">
								<?php
								if ( isset( $item['awskey'] ) ) {
									echo esc_html( $item['awskey'] );
								}
								?>
							</td>
							<td class="snapshot-col-bucket">
								<?php
								if ( isset( $item['bucket'] ) ) {
									echo esc_html( $item['bucket'] );
								}
								?>
							</td>
							<td class="snapshot-col-directory">
								<?php
								if ( isset( $item['directory'] ) ) {
									echo esc_html( $item['directory'] );
								}
								?>
							</td>
							<td class="snapshot-col-used"><?php Snapshot_Model_Destination::show_destination_item_count( $idx ); ?></td>
						</tr>
					<?php
					}
				} else {
					?>
					<tr class="form-field">
					<td colspan="4">
					<?php
						esc_html_e( 'No Amazon S3 Destinations', SNAPSHOT_I18N_DOMAIN );
					?>
					</td></tr>
						<?php
				}
				?>
				</tbody>
			</table>
			<?php
			if ( ( isset( $destinations ) ) && ( count( $destinations ) ) ) {
				?>
				<div class="tablenav">
					<div class="alignleft actions">
						<input class="button-secondary" type="submit"
								value="<?php esc_attr_e( 'Delete Destination', SNAPSHOT_I18N_DOMAIN ); ?>"/>
					</div>
				</div>
			<?php
			}
			?>
		<?php
		}

		public function display_details_form( $item = 0 ) {

			$this->init();
			//echo "item<pre>"; print_r($item); echo "</pre>";
			?>
			<p><?php esc_html_e( 'Define an Amazon AWS destination connection. You can define multiple destinations which use Amazon AWS. Each destination can use different security keys and/or buckets.', SNAPSHOT_I18N_DOMAIN ); ?></p>
			<div id="poststuff" class="metabox-holder">
			<div style="display: none" id="snapshot-destination-test-result"></div>
			<div class="postbox" id="snapshot-destination-item">

				<h3 class="hndle"><span><?php esc_html_e( 'Amazon S3 Destination', SNAPSHOT_I18N_DOMAIN ); ?></span></h3>

				<div class="inside">
					<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type"
							value="<?php echo esc_attr( $this->name_slug ); ?>"/>

					<table class="form-table">
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-name"><?php esc_html_e( 'Destination Name', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="text" name="snapshot-destination[name]" id="snapshot-destination-name"
										value="
										<?php
										if ( isset( $item['name'] ) ) {
											echo esc_attr( stripslashes( sanitize_text_field( $item['name'] ) ) );
										}
										?>
										"/></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-awskey"><?php esc_html_e( 'AWS Access Key ID', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="text" name="snapshot-destination[awskey]"
										id="snapshot-destination-awskey"
										value="
										<?php
										if ( isset( $item['awskey'] ) ) {
											echo esc_attr( sanitize_text_field( $item['awskey'] ) );
										}
										?>
										"/><br/><a
									href="https://aws-portal.amazon.com/gp/aws/securityCredentials" target="_blank">Access
									AWS Console</a></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-secretkey"><?php esc_html_e( 'AWS Secret Access Key', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="password" name="snapshot-destination[secretkey]"
										id="snapshot-destination-secretkey"
										value="
										<?php
										if ( isset( $item['secretkey'] ) ) {
											echo esc_attr( sanitize_text_field( $item['secretkey'] ) );
										}
										?>
										"/></td>
						</tr>

						<?php
						if ( ! isset( $item['ssl'] ) ) {
							$item['ssl'] = "yes";
						}
						?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-ssl"><?php esc_html_e( 'Use SSL Connection', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td>
								<select name="snapshot-destination[ssl]" id="snapshot-destination-ssl">
									<?php
									foreach ( $this->_ssl as $_key => $_name ) {
										?>
										<option value="<?php echo esc_attr( $_key ); ?>"
										<?php
										if ( $item['region'] === $_key ) {
											echo ' selected="selected" ';
										}
										?>
											><?php echo esc_html( $_name ); ?></option>
										<?php

									}
									?>
								</select>
							</td>
						</tr>


						<?php
						if ( ! isset( $item['region'] ) ) {
							$item['region'] = self::REGION_US_E1;
						}
						?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-region"><?php esc_html_e( 'AWS Region', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td>
								<select name="snapshot-destination[region]" id="snapshot-destination-region">
									<?php
									foreach ( $this->_regions as $_key => $_name ) {
										?>
										<option value="<?php echo esc_attr( $_key ); ?>"
										<?php
										if ( $item['region'] === $_key ) {
											echo ' selected="selected" ';
										}
										?>
											>
										<?php
										echo esc_html( $_name );
										?>
											(<?php echo esc_html( $_key ); ?>)</option>
										<?php

									}
									?>
								</select>

								<div id="snapshot-destination-region-other-container"
								<?php
								if ( 'other' !== $item['region'] ) {
									echo ' style="display:none;" ';
								}
								?>
								>
									<br/><label
										id="snapshot-destination-region-other"><?php esc_html_e( 'Alternate Region host', SNAPSHOT_I18N_DOMAIN ); ?></label><br/>
									<input name="snapshot-destination[region-other]"
											id="snapshot-destination-region-other"
											value="<?php echo esc_attr( $item['region-other'] ); ?>"/>
								</div>
							</td>
						</tr>

						<?php
						if ( ! isset( $item['storage'] ) ) {
							$item['storage'] = self::STORAGE_STANDARD;
						}
						?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-storage"><?php esc_html_e( 'Storage Type', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td>
								<select name="snapshot-destination[storage]" id="snapshot-destination-storage">
									<?php
									foreach ( $this->_storage as $_key => $_name ) {
										?>
										<option value="<?php echo esc_attr( $_key ); ?>"
										<?php
										if ( $item['storage'] === $_key ) {
											echo ' selected="selected" ';
										}
										?>
											><?php echo esc_html( $_name ); ?></option>
										<?php

									}
									?>
								</select>
							</td>
						</tr>


						<tr class="form-field" id="snapshot-destination-bucket-container">
							<th scope="row"><label
									for="snapshot-destination-bucket"><?php esc_html_e( 'Bucket Name', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td>
								<select name="snapshot-destination[bucket]" id="snapshot-destination-bucket-list"
										style="display: none">
										<?php if ( isset( $item['bucket'] ) ) { ?>
										<option value="<?php echo esc_attr( $item['bucket'] ); ?>" selected="selected"><?php echo esc_html( $item['bucket'] ); ?></option>
										<?php } ?>
								</select>
								<button id="snapshot-destination-aws-get-bucket-list" class="button-seconary"
										name="">
										<?php
										esc_html_e( 'Select Bucket', SNAPSHOT_I18N_DOMAIN );
										?>
								</button>
								<div id="snapshot-ajax-destination-bucket-error" style="display:none"></div>
							</td>
						</tr>

						<?php
						if ( ! isset( $item['acl'] ) ) {
							$item['acl'] = self::ACL_PRIVATE;
						}
						?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-acl">
									<?php
									esc_html_e(
										'File permissions for uploaded files',
										SNAPSHOT_I18N_DOMAIN
									);
									?>
									</label></th>
							<td>
								<select name="snapshot-destination[acl]" id="snapshot-destination-acl">
									<option
										value="<?php echo esc_attr( self::ACL_PRIVATE ); ?>"
										<?php
										if (  self::ACL_PRIVATE === $item['acl'] ) {
											echo ' selected="selected" ';
										}
										?>
										><?php esc_html_e( 'Private', SNAPSHOT_I18N_DOMAIN ); ?></option>
									<option
										value="<?php echo esc_attr( self::ACL_PUBLIC ); ?>"
										<?php
										if ( self::ACL_PUBLIC === $item['acl'] ) {
											echo ' selected="selected" ';
										}
										?>
										><?php esc_html_e( 'Public Read', SNAPSHOT_I18N_DOMAIN ); ?></option>
									<option
										value="<?php echo esc_attr( self::ACL_OPEN ); ?>"
										<?php
										if ( self::ACL_OPEN === $item['acl'] ) {
											echo ' selected="selected" ';
										}
										?>
										><?php esc_html_e( 'Public Read/Write', SNAPSHOT_I18N_DOMAIN ); ?></option>
									<option
										value="<?php echo esc_attr( self::ACL_AUTH_READ ); ?>"
										<?php
										if ( self::ACL_AUTH_READ === $item['acl'] ) {
											echo ' selected="selected" ';
										}
										?>
										><?php esc_html_e( 'Authenticated Read', SNAPSHOT_I18N_DOMAIN ); ?></option>
								</select>
							</td>
						</tr>

						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-directory"><?php esc_html_e( 'Directory (optional)', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="text" name="snapshot-destination[directory]"
										id="snapshot-destination-directory"
										value="
										<?php
										if ( isset( $item['directory'] ) ) {
											echo esc_attr( $item['directory'] );
										}
										?>
										"/>

								<p class="description"><?php esc_html_e( 'If directory is blank the snapshot file will be stored at the bucket root. If the directory is provided it will be created inside the bucket. This is a global setting and will be used by all snapshot configurations using this destination. You can also defined a directory used by a specific snapshot.', SNAPSHOT_I18N_DOMAIN ); ?></p>

							</td>
						</tr>
						<tr class="form-field" id="snapshot-destination-test-connection-container">
							<th scope="row">&nbsp;</th>
							<td>
								<button id="snapshot-destination-test-connection" class="button-seconary" name="">
									<?php
									esc_html_e( 'Test Connection', SNAPSHOT_I18N_DOMAIN );
									?>
								</button>
								<div id="snapshot-ajax-destination-test-result" style="display:none"></div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		<?php
		}

		/**
		 * Finalizes the multipart upload to S3
		 *
		 * @param object $upload Snapshot_Model_Transfer_Upload instance
		 * @param string $remote_path Path to remote file including potential subdirectory
		 *
		 * @return bool
		 */
		public function finalize_upload( $upload, $remote_path ) {
			if ( ! $this->login() ) {
				return false;
			}

			$path = $upload->get_path();
			$parts = $this->aws_connection->listParts(array(
				'Bucket' => $this->destination_info['bucket'],
				'Key' => $remote_path,
				'UploadId' => $upload->get_transfer_id(),
			));
			$complete = $this->aws_connection->completeMultipartUpload(array(
				'Bucket' => $this->destination_info['bucket'],
				'Key' => $remote_path,
				'UploadId' => $upload->get_transfer_id(),
				'MultipartUpload' => array(
					'Parts' => $parts['Parts'],
				),
			));

			if ( $complete ) {
				$upload->complete();
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Initializes the upload transfer
		 *
		 * @param string $path Local path to the file to be uploaded
		 * @param string $remote_path Path ro remote file including potential subdirectory
		 *
		 * @return object Initialized Snapshot_Model_Transfer_Upload instance
		 */
		public function get_initialized_upload( $path, $remote_path ) {
			$upload = new Snapshot_Model_Transfer_Upload( $path );
			if ( $upload->is_initialized() ) {
				return $upload;
			}

			if ( $this->login() ) {

				try {
					$response = $this->aws_connection->createMultipartUpload(array(
						'Bucket' => $this->destination_info['bucket'],
						'Key' => $remote_path,
						'ACL' => $this->destination_info['acl'],
						'StorageClass'    => $this->destination_info['storage'],
					));
				} catch ( Exception $e ) {
					return false;
				}

				$upload->initialize( $response['UploadId'] );
			}

			return $upload;
		}

		/**
		 * Gets the region of the bucket of a configured aws destination
		 *
		 * @param array $destination_info Info of the destination.
		 *
		 * @return string
		 */
		public function get_updated_region( $destination_info ) {
			$this->load_library();
			$this->init();

			$this->load_class_destination( $destination_info );

			require  dirname( __FILE__ ) . '/updated-region.php' ;

			if ( $status ) {
				return $resp['LocationConstraint'];
			} else {
				return false;
			}
		}
	}

	do_action( 'snapshot_register_destination', 'Snapshot_Model_Destination_AWS' );
}
?>