<?php // phpcs:ignore

/**
 * Remote storage handling model helper
 */
class Snapshot_Model_Full_Remote_Storage extends Snapshot_Model_Full {

	const CACHE_EXPIRATION = 86400;

	/**
	 * Singleton instance
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type() {
 		return 'remote';
 	}

	private function __construct() { }

	private function __clone() { }

	/**
	 * Gets the singleton instance
	 *
	 * @return Snapshot_Model_Full_Remote_Storage
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Gets default maximum backups limit
	 *
	 * @return int
	 */
	public function get_max_backups_default() {
		return 3;
	}

	/**
	 * Gets currently set maximum backups limit
	 *
	 * @return int
	 */
	public function get_max_backups_limit() {
		$default = $this->get_max_backups_default();
		return (int) $this->get_config( 'full_backups_limit', $default );
	}

	/**
	 * Gets maximum number of automate-initiated backups to keep around
	 *
	 * @return int
	 */
	public function get_max_automate_backups_limit () {
		return 3;
	}

	/**
	 * Sets the current maximum backups limit
	 *
	 * @param int $limit Limit to set
	 *
	 * @return int
	 */
	public function set_max_backups_limit( $limit ) {
		$limit = intval( $limit ) > 99 ? 99 : intval( $limit );
		return $this->set_config( 'full_backups_limit', max( 0, $limit ) );
	}

	/**
	 * Checks if we have enougn room for the file storage on API end.
	 *
	 * @param string $path Full path to file to check for size constraints
	 *
	 * @return bool
	 */
	public function has_enough_space_for( $path ) {
		if ( empty( $path ) || ! file_exists( $path ) ) {
			return false;
		}

		$free = $this->get_free_remote_space();
		if ( false === $free ) {
			return false;
		} // There has been an error - default to safe response

		$filesize = filesize( $path );

		return $filesize < (float) $free;
	}

	/**
	 * Fetches the used space size in bytes.
	 *
	 * @return mixed Integer number of bytes on success, or (bool)false on failure
	 */
	public function get_used_remote_space() {
		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			return false;
		}

		$size = $this->_get_used_remote_space();

		return apply_filters(
			$this->get_filter( 'api_space_used' ),
			$size
		);
	}

	/**
	 * Used remote space request helper
	 *
	 * Circumvents filters and caches, and goes for the API
	 * response directly.
	 *
	 * @return float|bool Used remote space, or false on failure
	 */
	private function _get_used_remote_space() {
		$api = Snapshot_Model_Full_Remote_Api::get();
		$api->connect(); // Make sure caches are populated, if here

		// Negative default, so we have proper type coercion
		// and spare extra request on no remote space taken
		$used = (float) $api->get_api_meta( 'current_bytes', - 1 );
		if ( $used < 0 ) {
			$response = $api->get_dev_api_response( 'backups-size' );
			if ( is_wp_error( $response ) ) {
				return false;
			}
			if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			if ( ! empty( $body ) ) {
				$body = json_decode( $body, true );
				if ( isset( $body['current_bytes'] ) && is_numeric( $body['current_bytes'] ) ) {
					$body = (int) $body['current_bytes'];
				}
			}
			$used = is_numeric( $body )
				? (float) $body
				: false;
		}
		return $used;
	}

	/**
	 * Gets totall allocated remote space, in bytes
	 *
	 * @return int Number of total allocated bytes
	 */
	public function get_total_remote_space() {
		$hardcoded = 10 * 1024 * 1024 * 1024;

		$total = $this->_get_total_remote_space();
		if ( empty( $total ) || ! is_numeric( $total ) ) {
			$total = $hardcoded;
		}

		return (float) apply_filters(
			$this->get_filter( 'api_space_total' ),
			$total
		);
	}

	/**
	 * Total remote space request helper
	 *
	 * Circumvents filters and caches, and goes for the API
	 * response directly.
	 *
	 * @return float|bool Total remote space, or false on failure
	 */
	private function _get_total_remote_space() {
		$api = Snapshot_Model_Full_Remote_Api::get();
		$api->connect(); // Make sure caches are populated, if here

		$total = (float) $api->get_api_meta( 'user_limit', false );
		if ( empty( $total ) ) {
			$response = $api->get_dev_api_response( 'backups-size' );
			if ( is_wp_error( $response ) ) {
				return false;
			}
			if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			if ( ! empty( $body ) ) {
				$body = json_decode( $body, true );
				if ( isset( $body['user_limit'] ) && is_numeric( $body['user_limit'] ) ) {
					$body = (int) $body['user_limit'];
				}
			}
			$total = is_numeric( $body )
				? (float) $body
				: false;
		}
		return $total;
	}

	/**
	 * Get the free space left on the remote storage end, in bytes
	 *
	 * @return mixed (int)Number of total bytes left free, or (bool)false on failure
	 */
	public function get_free_remote_space() {
		$total = $this->get_total_remote_space();
		$free = false;

		if ( false === $total || ! is_numeric( $total ) ) {
			return apply_filters(
				$this->get_filter( 'api_space_free' ),
				$free
			);
		}

		$used = $this->get_used_remote_space();
		if ( false === $used || ! is_numeric( $used ) ) {
			return apply_filters(
				$this->get_filter( 'api_space_free' ),
				$free
			);
		}

		$free = (float) $total - (float) $used;

		return apply_filters(
			$this->get_filter( 'api_space_free' ),
			$free
		);
	}

	/**
	 * Check if the user ran out of space
	 *
	 * Compares the *total* and *used* remote space from the
	 * API response, and deduces whether the user's quota has
	 * been exceeded or not.
	 *
	 * @return bool
	 */
	public function is_out_of_space() {
		$total = (float) $this->_get_total_remote_space();
		$used = (float) $this->_get_used_remote_space();

		if ( empty( $total ) ) {
			$this->_set_error( __( "We encountered an issue communicating with the API", SNAPSHOT_I18N_DOMAIN ) );
			return false;
		}

		return (bool) ( ( $total - $used ) < 0 );
	}

	/**
	 * Checks existing remote backups presence
	 *
	 * Just quickly checks the used space state
	 *
	 * @return bool
	 */
	public function has_previous_backups() {
		return (float) $this->_get_used_remote_space() > 0;
	}


	/**
	 * Get backup rotation strategy
	 *
	 * @param string $path Full path to current file to rotate around
	 *
	 * @return array List of files to remove from remote storage
	 */
	public function get_backup_rotation_list( $path ) {
		$candidate_list = $this->get_remote_list();
		$raw_list = array();
		$automated_list = $raw_list;

		// First, separate automated from regular full backups
		foreach ($candidate_list as $item) {
			if (Snapshot_Helper_Backup::is_automated_backup($item['name'])) {
				$automated_list[] = $item;
			} else {
				$raw_list[] = $item;
			}
		}

		// Now, if we have more than N automated backups:
		// Drop oldest ones
		$max_automated = $this->get_max_automate_backups_limit();
		if (Snapshot_Controller_Full_Hub::get()->is_doing_automated_backup()) $max_automated--; // Drop one more to make room for automate
		$remove_automated = array();
		if (count($automated_list) > $max_automated) {
			$oldest = $this->_get_oldest_filename($automated_list);
			if (!empty($oldest))
				$remove_automated[] = $oldest;
			$safety = 0;
			$difference = count($automated_list) - count($remove_automated);
			while ( $difference > $max_automated ) {
				$safety++;
				if ($safety > 20) break;
				$oldest = $this->_get_newer_filename($automated_list, $oldest);
				if (empty($oldest)) break;
				$remove_automated[] = $oldest;

				$difference = count($automated_list) - count($remove_automated);
			}
		}

		$to_remove = array();

		$count = count( $raw_list );
		$max_limit = $this->get_max_backups_limit();
		if (Snapshot_Controller_Full_Hub::get()->is_doing_automated_backup()) $max_limit++; // We're not particularly interested in user ones if doing automated

		// No other remote backups - all good
		if ( ! $count ) {
			return $remove_automated; // There might be automate ones though
		}

		// We're under limit, nothing to clean up
		if ( $max_limit > $count ) {
			return $remove_automated; // We may still need to clean up automated ones though
		}

		// Keep dropping oldest ones until we're good to go
		$oldest = $this->_get_oldest_filename( $raw_list, false );
		if ( ! empty( $oldest ) ) {
			$to_remove[] = $oldest;
			if ( $max_limit > $count - count( $to_remove ) ) {
				return array_merge($to_remove, $remove_automated);
			}
		}

		for ( $i = 0; $i < 50; $i ++ ) {
			$oldest = $this->_get_newer_filename( $raw_list, $oldest );
			if ( empty( $oldest ) ) {
				Snapshot_Helper_Log::info( "No more oldest files, breaking", "Remote" );
				break; // No more oldest files
			}

			$to_remove[] = $oldest;
			if ( $max_limit > $count - count( $to_remove ) ) {
				break;
			} // We're good to go
		}

		return array_merge($to_remove, $remove_automated);
	}

	/**
	 * Rotate backups
	 *
	 * @param string $path Full path to current file to rotate around
	 *
	 * @uses $this->get_backup_rotation_list() to get the rotation strategy
	 *
	 * @return bool
	 */
	public function rotate_backups( $path ) {
		Snapshot_Helper_Log::info( "Enter backup rotation", "Remote" );

		$error = __( 'Error rotating backups', SNAPSHOT_I18N_DOMAIN );
		$to_remove = $this->get_backup_rotation_list( $path );

		if ( empty( $to_remove ) ) {
			return ! $this->has_errors();
		} // Nothing to drop, we're all good

		Snapshot_Helper_Log::info( sprintf( "Clean up remote storage, removing %d files", count( $to_remove ) ), "Remote" );

		// Actually remove backups that are to be rotated
		$status = true;
		foreach ( $to_remove as $filename ) {
			if ( empty( $filename ) ) {
				continue;
			}
			Snapshot_Helper_Log::info( "Cleaning up remote file: {$filename}", "Remote" );
			$status = $this->delete_remote_file( $filename );
			if ( ! $status ) {
				$this->_set_error( $error );
				Snapshot_Helper_Log::warn( "Error cleaning up remote file: {$filename}", "Remote" );
				break;
			}
		}

		if ( $status ) {
			// Purge caches, since they're no longer accurate
			Snapshot_Model_Transient::delete( $this->get_filter( "backups" ) );
		}

		return $status;
	}

	/**
	 * Spawn and return the S3 request handler.
	 *
	 * @return Aws\S3\S3Client|bool Remote storage handling object, or (bool) false on failure
	 */
	public function get_remote_storage_handler() {
		if ( version_compare(PHP_VERSION, '5.5', '<') ) {
			// Too old PHP, can't do anything about it.
			return false;
		}
		$plugin_path = WPMUDEVSnapshot::instance()->get_plugin_path();
		$lib_path = wp_normalize_path(
			trailingslashit( $plugin_path ) .
			'lib/Snapshot/Model/Destination/aws/vendor/autoload.php'
		);
		if ( ! file_exists( $lib_path ) ) {
			return false;
		}

		// Before loading the AWS SDK, we check the setup to see if there are older versions of the libs used in the SDK.
		if ( class_exists( 'GuzzleHttp\Client' ) ) {
			if ( ! class_exists( 'GuzzleHttp\Psr7\Response' ) ) {
				return false;
			}
		}

		if ( class_exists( 'Aws\S3\S3Client' ) ) {
			if ( ! class_exists( 'Aws\Sdk' ) ) {
				return false;
			}
		} else {
			require_once $lib_path ;
			if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
				return false;
			}
		}

		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			return false;
		}

		static $s3_handler;
		if ( empty( $s3_handler ) ) {
			$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
			if ( empty( $nfo ) ) {
				return false;
			} // Error getting the API info, bail out

			// Initiate client handler.
			require dirname( __FILE__ ) . '/Handler.php' ;

		}
		return $s3_handler;
	}

	/**
	 * Initializes the upload transfer
	 *
	 * @param string $path Local path to the file to be uploaded.
	 *
	 * @return object Initialized Snapshot_Model_Transfer_Upload instance
	 */
	public function get_initialized_upload( $path ) {
		$upload = new Snapshot_Model_Transfer_Upload( $path );
		if ( $upload->is_initialized() ) {
			return $upload;
		}

		$s3 = $this->get_remote_storage_handler();
		Snapshot_Helper_Utility::spawned_S3_handler( $s3 );

		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

		if ( ! empty( $nfo ) ) {
			try {
				$response = $s3->createMultipartUpload(array(
					'Bucket' => $nfo['Bucket'],
					'Key' => trailingslashit( $nfo['Prefix'] ) . basename( $path ),
					'ACL' => 'private',
					'ServerSideEncryption' => 'AES256',
				));
			} catch ( Exception $e ) {
				return $upload;
			}

			$upload->initialize( $response['UploadId'] );
		}

		return $upload;
	}

	/**
	 * Finalizes the multipart upload to S3
	 *
	 * @param object $upload Snapshot_Model_Transfer_Upload instance.
	 *
	 * @return bool
	 */
	public function finalize_upload( $upload ) {
		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			return false;
		}

		$s3 = $this->get_remote_storage_handler();
		if ( ! Snapshot_Helper_Utility::spawned_S3_handler( $s3 ) ) {
			return false;
		}
		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
		if ( empty( $nfo ) ) {
			return false;
		}

		$path = $upload->get_path();
		$parts = $s3->listParts(array(
			'Bucket' => $nfo['Bucket'],
			'Key' => trailingslashit( $nfo['Prefix'] ) . basename( $path ),
			'UploadId' => $upload->get_transfer_id(),
		));
		$complete = $s3->completeMultipartUpload(array(
			'Bucket' => $nfo['Bucket'],
			'Key' => trailingslashit( $nfo['Prefix'] ) . basename( $path ),
			'UploadId' => $upload->get_transfer_id(),
			'MultipartUpload' => array(
				'Parts' => $parts['Parts'],
			),
		));

		if ( $complete ) {
			$upload->complete();

			// Drop the local file!
			if ( is_writable( $path ) )
				unlink( $path );

			$this->purge_backups_cache();
			Snapshot_Helper_Log::info( "File successfully uploaded", "Remote" );

			return true;
		} else {
			Snapshot_Helper_Log::error( "Error uploading file", "Remote" );
		}

		return false;
	}

	/**
	 * Aborts the multipart upload to S3
	 *
	 * @param string $path Path to file being uploaded.
	 *
	 * @return bool
	 */
	public function abort_file_upload( $path ) {
		if ( ! file_exists( $path ) ) {
			Snapshot_Helper_Log::error( 'Local file not found. The upload may have completed successfully before an abort request could be sent.' );
			return false;
		}
		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			Snapshot_Helper_Log::error( 'Could not connect to remote API.' );
			return false;
		}

		$s3 = $this->get_remote_storage_handler();
		if ( ! Snapshot_Helper_Utility::spawned_S3_handler( $s3 ) ) {
			return false;
		}

		$info = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
		$upload = new Snapshot_Model_Transfer_Upload( $path );

		if ( ! $upload->get_transfer_id() ) {
			Snapshot_Helper_Log::error( 'No valid upload ID found for abort.' );
			return false;
		}

		$response = false;
		try {
			$response = $s3->abortMultipartUpload(array(
				'Bucket' => $info['Bucket'],
				'Key' => trailingslashit( $info['Prefix'] ) . basename( $path ),
				'UploadId' => $upload->get_transfer_id()
			));
		} catch( Exception $e ) {
			$response = false;
		}
		if ( ! $response ) {
			Snapshot_Helper_Log::error( "Abort request sent, but apparently something went wrong." );
			return false;
		}
		$upload->complete();

		return true;
	}

	/**
	 * Deletes cached backups list
	 */
	public function purge_backups_cache() {
		Snapshot_Model_Transient::delete( $this->get_filter( "backups" ) );
	}

	/**
	 * Attempts remote storage cleanup
	 *
	 * Triggered if there isn't enough space for the backup,
	 * it then tries to make some.
	 *
	 * @param string $path Full path to local backup file
	 *
	 * @return bool
	 */
	public function attempt_remote_cleanup( $path ) {
		Snapshot_Helper_Log::info( "Not enough space for upload, attempting cleanup", "Remote" );

		// First up, are we out of space?
		if ( $this->is_out_of_space() ) {
			Snapshot_Helper_Log::warn(
				"Out of remote space, currently used storage over quota. Aborting upload",
				"Remote"
			);

			// Also clean up API info cache and force re-sync
			Snapshot_Model_Full_Remote_Api::get()->clean_up_api();
			Snapshot_Model_Full_Remote_Api::get()->connect();

			return true; // Error condition, stop right here
		}

		// Quick sanity check - will we have enough room after rotation?
		$filesize = filesize( $path );
		$total = (float) $this->get_total_remote_space(); // Cast to number, as it can return false.

		if ( $filesize > $total ) {
			$this->_set_error( __( 'Backup too large for storage quota.', SNAPSHOT_I18N_DOMAIN ) );
			Snapshot_Helper_Log::warn( "Backup too large for storage quota", "Remote" );
			return false; // We don't have enough room to store this anyway.
		}

		$status = $this->rotate_backups( $path );
		return $status
			? false // Not done in this pass.
			: true // We had an error, clean up and rely on error set in removal.
		;
	}

	/**
	 * Whether we should do a backup rotation on upload
	 *
	 * @return bool
	 */
	public function should_rotate_on_upload() {
		// Fresh upload. Check backups rotation first.
		// We *do* seem to have enough space, *but* do we also have 3+ backups?
		// If we do, we need to clean them up.
		Snapshot_Helper_Log::info( 'Checking space/backups kept requirements', 'Remote' );

		// We work with cache, because it's quicker.
		$backups = Snapshot_Model_Transient::get_any( $this->get_filter( "backups" ), false );
		if ( false === $backups ) {
			// So apparently cache has been purged recently, let's rebuild.
			Snapshot_Helper_Log::info(
				'No cached backups to count and rotate, requesting fresh list',
				'Remote'
			);
			$this->refresh_backups_list();
			$backups = Snapshot_Model_Transient::get_any( $this->get_filter( "backups" ), false );
		}

		// Separate backups by initiator.
		$user_initiated = 0;
		$automate_initiated = 0;

		if (Snapshot_Controller_Full_Hub::get()->is_doing_automated_backup()) {
			// Drop one more to make room for automate.
			$automate_initiated++;
		} else {
			$user_initiated++;
		}

		if (!empty($backups) && is_array($backups)) {
			foreach ($backups as $idx => $bkp) {
				if (Snapshot_Helper_Backup::is_automated_backup($bkp['name'])) {
					$automate_initiated++;
				} else {
					$user_initiated++;
				}
			}
		}

		if (
			$automate_initiated > $this->get_max_automate_backups_limit()
			||
			$user_initiated > $this->get_max_backups_limit()
		) {
			Snapshot_Helper_Log::info(
				sprintf(
					'More than upper limit backups u(%d/%d) -- a(%d/%d) -- removing some.',
					$user_initiated, $this->get_max_backups_limit(),
					$automate_initiated, $this->get_max_automate_backups_limit()
				), 'Remote'
			);

			return true;
		}

		if ( empty( $backups ) && is_array( $backups ) ) {
			Snapshot_Helper_Log::info(
				'Apparently no remote backups, no need to rotate',
				'Remote'
			);
		} else if ( empty( $backups ) && ! is_array( $backups ) ) {
			Snapshot_Helper_Log::info(
				'Skip rotate, cache needs update',
				'Remote'
			);
		} else if ( ! empty( $backups ) ) {
			Snapshot_Helper_Log::info(
				sprintf(
					'Skip rotate, backups count in check: u%d + a%d',
					$user_initiated, $automate_initiated
				),
				'Remote'
			);
		}

		return false;
	}

	/**
	 * Actually send the finished local backup file to remote storage
	 *
	 * @param string $path Full path to local backup file
	 *
	 * @return bool
	 */
	public function send_backup_file( $path ) {
		if ( ! file_exists( $path ) ) {
			return false;
		}
		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			return false;
		}

		if ( ! $this->has_enough_space_for( $path ) ) {
			return $this->attempt_remote_cleanup( $path );
		}

		$upload = $this->get_initialized_upload( $path );

		// Determine if we're continuing this upload,
		// or sending the fresh one
		$is_continued_upload = false;
		$continuation_purge = defined( 'SNAPSHOT_FORCE_CONTINUATION_PURGE' ) &&
			constant( 'SNAPSHOT_FORCE_CONTINUATION_PURGE' )
		;
		if ( empty( $continuation_purge ) ) {
			$is_continued_upload = $upload->has_completed_parts();
		}

		if ( ! $is_continued_upload ) {
			if ( $this->should_rotate_on_upload() ) {
				$status = $this->rotate_backups( $path );
				return ! empty( $status )
					? false // We're okay, but not done yet.
					: true // We encountered an error rotating.
				;
			}
		} else {
			Snapshot_Helper_Log::info( 'A continued upload, we will not be re-rotating', 'Remote' );
		}

		$s3 = $this->get_remote_storage_handler();
		if ( ! Snapshot_Helper_Utility::spawned_S3_handler( $s3 ) ) {
			return false;
		}
		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
		$is_done = true;

		Snapshot_Helper_Log::info( "Ready to send file", "Remote" );

		$part = $upload->get_next_part();
		if ( ! empty( $part ) ) {
			$is_done = false;
			$idx = $part->get_index();
			try {
				$response = $s3->uploadPart(array(
					'Bucket' => $nfo['Bucket'],
					'Key' => trailingslashit( $nfo['Prefix'] ) . basename( $path ),
					'UploadId' => $upload->get_transfer_id(),
					'PartNumber' => $part->get_part_number(),
					'Body' => $upload->get_payload( $part ),
				));
				$upload->complete_part( $idx );
				$upload->save();

				$is_done = $upload->is_done();
			} catch( Exception $e ) {
				Snapshot_Model_Full_Remote_Api::get()->clean_up_api();
				Snapshot_Helper_Log::warn( "Error uploading the file, part [{$idx}]", "Remote" );
			}
		}

		if ( $is_done ) {
			return $this->finalize_upload( $upload );
		}

		return $is_done;
	}

	/**
	 * Gets initialized download transfer object
	 *
	 * @param string $path Local path to download to.
	 *
	 * @return object Initialized Snapshot_Model_Transfer_Download instance.
	 */
	public function get_initialized_download( $path ) {
		$download = new Snapshot_Model_Transfer_Download( $path );
		if ( $download->is_initialized() ) {
			return $download;
		}

		$s3 = $this->get_remote_storage_handler();
		$spawned_S3_handler = Snapshot_Helper_Utility::spawned_S3_handler( $s3 );

		if ( ! is_object( $s3 ) || false === $spawned_S3_handler ) {
			$this->_set_error( __( 'Error spawning the S3 request handler, most probably due to a plugin conflict.', SNAPSHOT_I18N_DOMAIN ) );
			return $download;
		}

		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

		try {
			$response = $s3->headObject(array(
				'Bucket' => $nfo['Bucket'],
				'Key' => trailingslashit( $nfo['Prefix'] ) . basename( $path ),
			));
			$download->initialize( $response['ContentLength'] );
		} catch( Exception $e ) {
			Snapshot_Helper_Log::warn( 'Unable to query download size', 'Remote' );
		}

		return $download;
	}

	/**
	 * Downloads the requested backup file from remote storage
	 *
	 * @param string $backup Backup item name
	 *
	 * @return string Local path
	 */
	public function fetch_backup_file( $backup ) {
		if ( empty( $backup ) ) {
			return false;
		}

		$destination = false;
		$local_path = trailingslashit( wp_normalize_path(
			WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' )
		) );

		if ( Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			Snapshot_Helper_Log::info( "Starting remote backup file download", 'Remote' );

			$destination = $local_path . basename( $backup );
			$download = $this->get_initialized_download( $destination );

			if ( false === $download) {
				$this->_set_error( __( 'Could not initialize the backup file download', SNAPSHOT_I18N_DOMAIN ) );
				Snapshot_Helper_Log::warn( "Could not initialize the backup file download", "Remote" );
				return false;
			}

			if ( ! $download->is_done() ) {
				$s3 = $this->get_remote_storage_handler();
				$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
				$part = $download->get_next_part();
				$from_bytes = $part->get_seek();
				$to_bytes = ($from_bytes + $part->get_length()) - 1;

				$response = false;
				try {
					$response = $s3->getObject(array(
						'Bucket' => $nfo['Bucket'],
						'Key' => trailingslashit( $nfo['Prefix'] ) . $backup,
						'SaveAs' => $download->get_part_file_path( $part ),
						'Range' => "bytes={$from_bytes}-{$to_bytes}",
					));
					$download->complete_part( $part->get_index() );
					$download->save();
				} catch ( Exception $e ) {
					$response = false;
				}

				if ( ! $response ) {
					$this->_set_error( __( 'Error fetching file part', SNAPSHOT_I18N_DOMAIN ) );
					Snapshot_Helper_Log::warn( "Error fetching file part", "Remote" );
					$download->complete();
					return false; // Error fetching the file
				}
				Snapshot_Helper_Log::info( "Remote backup part successfully downloaded", 'Remote' );

				if ( $download->is_done() ) {
					if ( ! $download->complete() ) {
						$this->_set_error(
							__( 'Error finalizing download', SNAPSHOT_I18N_DOMAIN )
						);
						// Whoops, we couldn't finalize the download.
						return false;
					}
					Snapshot_Helper_Log::info(
						"Remote backup file successfully downloaded", 'Remote'
					);
					return $destination;
				} else {
					// We are not done yet, try again.
					$destination = false;
				}
			}
		}

		return $destination;
	}

	/**
	 * Returns the storage download link for the file
	 *
	 * @param string $backup Backup item name
	 *
	 * @return string Remote storage link or (bool)false on failure
	 */
	public function get_backup_link( $backup ) {
		if ( empty( $backup ) ) {
			return false;
		}

		$destination = false;
		if ( Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			$s3 = $this->get_remote_storage_handler();
			if ( ! Snapshot_Helper_Utility::spawned_S3_handler( $s3 ) ) {
				return false;
			}
			$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

			$cmd = $s3->getCommand( 'getObject', array(
				'Bucket' => $nfo['Bucket'],
				'Key' => trailingslashit( $nfo['Prefix'] ) . $backup,
			));
			$request = $s3->createPresignedRequest( $cmd, '+1 hours' );
			$destination = (string) $request->getUri();
		}

		return $destination;
	}

	/**
	 * Actually removes the resolved remote file
	 *
	 * @param string $remote_file Remote file name to remove
	 *
	 * @return bool
	 */
	public function delete_remote_file( $remote_file ) {
		$status = false;
		$remote_file = basename( $remote_file );

		if ( empty( $remote_file ) ) {
			Snapshot_Helper_Log::warn( "No remote file to delete", 'Remote' );
			return $status;
		}

		if ( Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			$s3 = $this->get_remote_storage_handler();
			if ( ! Snapshot_Helper_Utility::spawned_S3_handler( $s3 ) ) {
				return false;
			}
			$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

			$resp = $s3->deleteObject(array(
				'Bucket' => $nfo['Bucket'],
				'Key' => trailingslashit( $nfo['Prefix'] ) . basename( $remote_file ),
			));

			if ( ! $resp ) {
				$this->_set_error(
					sprintf( __( 'Error deleting file: %s', SNAPSHOT_I18N_DOMAIN ), $remote_file )
				);
				Snapshot_Helper_Log::warn( "Error deleting remote file: [{$remote_file}]", "Remote" );
			} else {
				// Refresh backups upon successful removal.
				$this->refresh_backups_list();
				$status = true;
			}
		}

		return $status;
	}

	/**
	 * Fetches a fresh list of existing backups from remote storage
	 *
	 * Uses http://docs.aws.amazon.com/AWSSDKforPHP/latest/index.html#m=AmazonS3/list_objects
	 *
	 * @return array
	 */
	public function get_remote_list() {
		$raw = array();
		$error = Snapshot_View_Full_Backup::get_message( 'backup_list_fetch_error' );

		$s3 = $this->get_remote_storage_handler();
		if ( ! Snapshot_Helper_Utility::spawned_S3_handler( $s3 ) ) {
			return $raw;
		}
		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

		if ( $s3 ) {
			try {
				$objects = $s3->listObjects(array(
					'Bucket' => $nfo['Bucket'],
					'Prefix' => $nfo['Prefix'],
				));
				if ( ! empty( $objects ) ) {
					$objects_list = ! empty( $objects['Contents'] )
						? $objects['Contents']
						: array()
					;
					foreach ( $objects_list as $object ) {
						$raw[] = array(
							'name' => basename( $object['Key'] ),
							'size' => (int) $object['Size'],
						);
					}
				} else {
					$this->_set_error( $error );
					Snapshot_Helper_Log::warn( "Remote list fetching error", "Remote" );
				}
			} catch ( Exception $e ) {
				Snapshot_Model_Full_Remote_Api::get()->clean_up_api();
				$this->_set_error( $error );
				Snapshot_Helper_Log::warn( "Remote list fetching error, storage exception", "Remote" );
			}
		}

		// Okay, so even if we errored out, proceed to filter whatever we have left
		$raw = apply_filters(
			$this->get_filter( "backups_get" ),
			$raw
		);

		// Okay, so suppose all we get is a list of file names. Let's parse them into something reasonable
		$backups = array();
		foreach ( $raw as $file_info ) {
			if ( empty( $file_info['name'] ) || empty( $file_info['size'] ) ) {
				continue;
			}
			$time = $this->_get_file_timestamp_from_name( $file_info['name'] );

			if ( empty( $time ) ) {
				continue;
			}
			$backups[] = array(
				'name' => $file_info['name'],
				'size' => $file_info['size'],
				'timestamp' => $time,
				'local' => false,
			);
		}

		return $backups;
	}

	/**
	 * Connect to API and update local cache with fresh backups list
	 *
	 * @return bool
	 */
	public function refresh_backups_list() {
		$backups = array();

		// Connect to API and get the list
		if ( Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			$backups = $this->get_remote_list();
		}

		$backups = apply_filters(
			$this->get_filter( "backups_refresh" ),
			$backups // API-obtained backup list
		);

		return Snapshot_Model_Transient::set(
			$this->get_filter( "backups" ),
			$backups,
			$this->get_cache_expiration()
		);
	}

	/**
	 * Get local cache expiry timeframe.
	 *
	 * Filtered, defaults to constant (1 day in seconds)
	 *
	 * @return int Number of seconds to keep cache around
	 */
	public function get_cache_expiration() {
		return apply_filters(
			$this->get_filter( 'cache_expiration' ),
			self::CACHE_EXPIRATION
		);
	}

}