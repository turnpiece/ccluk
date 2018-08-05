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
	 * @return mixed Remote storage handling object, or (bool)false on failure
	 */
	public function get_remote_storage_handler() {
		if ( ! class_exists( 'AmazonS3' ) ) {
			return false;
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
			$s3_handler = new AmazonS3(
                 array(
					'key' => $nfo['AccessKeyId'],
					'secret' => $nfo['SecretAccessKey'],
					'token' => $nfo['SessionToken'],
					'certificate_authority' => trailingslashit( ABSPATH . WPINC ) . 'certificates/ca-bundle.crt',
				)
            );
		}
		return $s3_handler;
	}

	/**
	 * Fetch a list of calculated upload parts for the file's S3 upload
	 *
	 * If the list doesn't exist, calculate it.
	 * Uses http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/get_multipart_counts
	 * Uses http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/initiate_multipart_upload [<description>]
	 *
	 * @param string $path Path to file
	 *
	 * @return array A list of parts to deal with
	 */
	public function get_upload_parts( $path ) {
		$key = md5( $path );
		$parts = get_site_option( $key, array() );
		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			return false;
		}
		$s3 = $this->get_remote_storage_handler();

		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

		if ( ! empty( $nfo ) && empty( $parts ) ) {
// http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/get_multipart_counts
			$parts = $s3->get_multipart_counts( filesize( $path ), 50 * 1024 * 1024 );
// http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/initiate_multipart_upload
			$upload = $s3->initiate_multipart_upload(
				$nfo['Bucket'],
				trailingslashit( $nfo['Prefix'] ) . basename( $path ),
				array(
					'acl' => 'private',
					'encryption' => 'AES256',
				)
			);
			$upload_id = (string) $upload->body->UploadId;
			foreach ( $parts as $idx => $part ) {
				$part['done'] = false;
				$part['upload_id'] = $upload_id;
				$parts[ $idx ] = $part;
			}
			add_site_option( $key, $parts );
		}

		return $parts;
	}

	/**
	 * Finalizes the multipart upload to S3
	 *
	 * Uses http://docs.aws.amazon.com/AWSSDKforPHP/latest/index.html#m=AmazonS3/list_parts
	 * Uses http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/complete_multipart_upload
	 *
	 * @param string $upload_id Upload ID initiated earlier
	 * @param string $path File path to finalize
	 *
	 * @return bool
	 */
	public function finalize_upload( $upload_id, $path ) {
		if ( empty( $upload_id ) || empty( $path ) ) {
			return false;
		}
		if ( ! Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			return false;
		}

		$key = md5( $path );

		$s3 = $this->get_remote_storage_handler();
		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
		if ( empty( $nfo ) ) {
			return false;
		}

// http://docs.aws.amazon.com/AWSSDKforPHP/latest/index.html#m=AmazonS3/list_parts
		$parts = $s3->list_parts(
			$nfo['Bucket'],
			trailingslashit( $nfo['Prefix'] ) . basename( $path ),
			$upload_id
		);
// http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/complete_multipart_upload
		$complete = $s3->complete_multipart_upload(
			$nfo['Bucket'],
			trailingslashit( $nfo['Prefix'] ) . basename( $path ),
			$upload_id,
			$parts
		);

		if ( $complete->isOk() ) {
			delete_site_option( $key ); // Clean up the temp storage

			// Drop the local file!
			if ( is_writable( $path ) )
				unlink( $path );

			// Delete cache
			Snapshot_Model_Transient::delete( $this->get_filter( "backups" ) );

			Snapshot_Helper_Log::info( "File successfully uploaded", "Remote" );
			return true;
		}

		return false;
	}

	/**
	 * Actually send the finished local backup file to remote storage
	 *
	 * Uses http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/upload_part
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
			Snapshot_Helper_Log::info( "Not enough space for upload, attempting cleanup", "Remote" );

			// First up, are we out of space?
			if ( $this->is_out_of_space() ) {
				Snapshot_Helper_Log::warn( "Out of remote space, currently used storage over quota. Aborting upload", "Remote" );

				// Also clean up API info cache and force re-sync
				Snapshot_Model_Full_Remote_Api::get()->clean_up_api();
				Snapshot_Model_Full_Remote_Api::get()->connect();

				return true; // Error condition, stop right here
			}

			// Quick sanity check - will we have enough room after rotation?
			$filesize = filesize( $path );
			$total = (float) $this->get_total_remote_space(); // Cast to int, as it can return false

			if ( $filesize > $total ) {
				$this->_set_error( __( 'Backup too large for storage quota.', SNAPSHOT_I18N_DOMAIN ) );
				Snapshot_Helper_Log::warn( "Backup too large for storage quota", "Remote" );
				return false; // We don't have enough room to store this anyway
			}

			$status = $this->rotate_backups( $path );
			return $status
				? false // Not done in this pass
				: true // We had an error, clean up and rely on error set in removal
				;
		}

		$parts = $this->get_upload_parts( $path );
		$key = md5( $path );
		$upload_id = false;

		$part_keys = array_keys( $parts );
		$last_key = end( $part_keys );

		// Determine if we're continuing this upload,
		// or sending the fresh one
		$is_continued_upload = false;
		if ( ! ( defined( 'SNAPSHOT_FORCE_CONTINUATION_PURGE' ) && SNAPSHOT_FORCE_CONTINUATION_PURGE ) ) {
			foreach ( $parts as $part ) {
				if ( empty( $part['done'] ) ) {
					continue;
				}
				$is_continued_upload = true;
				break;
			}
		}

		if ( ! $is_continued_upload ) {
			// Fresh one. Check backups rotation first.
			// We *do* seem to have enough space, *but* do we also have 3+ backups?
			// If we do, we need to clean them up
			Snapshot_Helper_Log::info( 'Checking space/backups kept requirements', 'Remote' );

			// We work with cache, because it's quicker
			$backups = Snapshot_Model_Transient::get_any( $this->get_filter( "backups" ), false );
			if ( false === $backups ) {
				// So apparently cache has been purged recently, let's rebuild
				Snapshot_Helper_Log::info( 'No cached backups to count and rotate, requesting fresh list', 'Remote' );
				$this->refresh_backups_list();
				$backups = Snapshot_Model_Transient::get_any( $this->get_filter( "backups" ), false );
			}

			// Separate backups by initiator
			$user_initiated = 0;
			$automate_initiated = 0;

			if (Snapshot_Controller_Full_Hub::get()->is_doing_automated_backup()) $automate_initiated++; // Drop one more to make room for automate
			else $user_initiated++;

			if (!empty($backups) && is_array($backups)) foreach ($backups as $idx => $bkp) {
				if (Snapshot_Helper_Backup::is_automated_backup($bkp['name'])) $automate_initiated++;
				else $user_initiated++;
			}

			if ($automate_initiated > $this->get_max_automate_backups_limit() || $user_initiated > $this->get_max_backups_limit()) {
				Snapshot_Helper_Log::info(
                    sprintf(
						'More than upper limit backups u(%d/%d) -- a(%d/%d) -- removing some.',
						$user_initiated, $this->get_max_backups_limit(),
						$automate_initiated, $this->get_max_automate_backups_limit()
					), 'Remote'
                );
				$status = $this->rotate_backups( $path );
				return $status
					? false // Not done in this pass
					: true // We had an error, clean up and rely on error set in removal
				;
			} else {
				if ( empty( $backups ) && is_array( $backups ) ) {
					Snapshot_Helper_Log::info( 'Apparently no remote backups, no need to rotate', 'Remote' );
				} else if ( empty( $backups ) && ! is_array( $backups ) ) {
					Snapshot_Helper_Log::info( 'Skip rotate, cache needs update', 'Remote' );
				} else if ( ! empty( $backups ) ) {
					Snapshot_Helper_Log::info( sprintf( 'Skip rotate, backups count in check: u%d + a%d', $user_initiated, $automate_initiated ), 'Remote' );
				}
			}
		} else {
			Snapshot_Helper_Log::info( 'A continued upload, we will not be re-rotating', 'Remote' );
		}

		$s3 = $this->get_remote_storage_handler();
		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();
		$is_done = true;

		Snapshot_Helper_Log::info( "Ready to send file", "Remote" );

		foreach ( $parts as $idx => $part ) {
			$upload_id = $part['upload_id'];
			if ( ! empty( $part['done'] ) ) {
				continue;
			}
			// We have a part to upload
			$is_done = false;
			try {
// http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/upload_part
				$response = $s3->upload_part(
					$nfo['Bucket'], trailingslashit( $nfo['Prefix'] ) . basename( $path ), $upload_id, array(
						'expect' => '100-continue',
						'fileUpload' => $path,
						'partNumber' => $idx + 1,
						'seekTo' => $part['seekTo'],
						'length' => $part['length'],
					)
                );
				$part['done'] = $response->isOk();
				$parts[ $idx ] = $part;
				update_site_option( $key, $parts );

				if ( $idx === $last_key ) {
					$is_done = true;
				} // If this is the last one, let's process right away

				break;
			} catch ( Exception $e ) {
				Snapshot_Model_Full_Remote_Api::get()->clean_up_api();
				Snapshot_Helper_Log::warn( "Error uploading the file, part [{$idx}]", "Remote" );
				break;
			}
		}

		if ( $is_done ) {
			if ( ! empty( $upload_id ) ) {
				return $this->finalize_upload( $upload_id, $path );
			} else {
				$this->_set_error( __( 'Unable to finalize the upload.', SNAPSHOT_I18N_DOMAIN ) );
				Snapshot_Helper_Log::warn( "Unable to finalize the upload", "Remote" );
				return false;
			}
		}

		return $is_done;
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
		$lock = new Snapshot_Helper_Locker(
			WPMUDEVSnapshot::instance()->get_setting( 'backupLockFolderFull' ),
			Snapshot_Helper_String::conceal( $backup )
		);
		$local_path = trailingslashit( wp_normalize_path( WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) ) );

		if ( $lock->is_locked() ) {
			if ( Snapshot_Model_Full_Remote_Api::get()->connect() ) {
				Snapshot_Helper_Log::info( "Starting remote backup file download", 'Remote' );

				$destination = $local_path . basename( $backup );
				$s3 = $this->get_remote_storage_handler();
				$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

// http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/get_object
				$resp = $s3->get_object(
					$nfo['Bucket'],
					trailingslashit( $nfo['Prefix'] ) . $backup,
					array(
						'fileDownload' => $destination,
					)
				);
				if ( ! $resp->isOk() ) {
					$this->_set_error( __( 'Error fetching file', SNAPSHOT_I18N_DOMAIN ) );
					Snapshot_Helper_Log::warn( "Error fetching file", "Remote" );
					return false; // Error fetching the file
				} else {
					Snapshot_Helper_Log::info( "Remote backup file successfully downloaded", 'Remote' );
				}
			}

			$lock->unlock();
			unset( $lock ); // Clear lock
		} else {
			Snapshot_Helper_Log::warn( "Unable to obtain lock on downloaded remote backup", 'Remote' );
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
			$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

//http://docs.aws.amazon.com/AWSSDKforPHP/latest/#m=AmazonS3/get_object_url
			$resp = $s3->get_object_url(
				$nfo['Bucket'],
				trailingslashit( $nfo['Prefix'] ) . $backup,
				'+1 hours',
				array(
					'https' => true,
				)
			);
			$destination = $resp;
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
			return $status;
		}

		if ( Snapshot_Model_Full_Remote_Api::get()->connect() ) {
			$s3 = $this->get_remote_storage_handler();
			$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

			$resp = $s3->delete_object(
				$nfo['Bucket'],
				trailingslashit( $nfo['Prefix'] ) . $remote_file
			);

			$status = $resp->isOk();
			if ( empty( $status ) ) {
				$this->_set_error( sprintf( __( 'Error deleting file: %s', SNAPSHOT_I18N_DOMAIN ), $remote_file ) );
				Snapshot_Helper_Log::warn( "Error deleting remote file: [{$remote_file}]", "Remote" );
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
		$nfo = Snapshot_Model_Full_Remote_Api::get()->get_api_info();

		if ( $s3 ) {
			try {
				// http://docs.aws.amazon.com/AWSSDKforPHP/latest/index.html#m=AmazonS3/list_objects
				$resp = $s3->list_objects(
					$nfo['Bucket'],
					array(
						'prefix' => $nfo['Prefix'],
					)
				);
				if ( $resp->isOk() ) {
					// Process the response and get the raw objects info
					foreach ( $resp->body->Contents as $item ) {
						$key = (string) $item->Key;
						if ( empty( $key ) ) {
							continue;
						}
						$raw[] = array(
							'name' => basename( $key ),
							'size' => (int) $item->Size,
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