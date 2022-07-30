<?php
/**
 * Shipper helpers: remote FS abstraction
 *
 * Abstracts the gory details of remote filesystem comms.
 *
 * @package shipper
 */

/**
 * Remote FS class
 */
class Shipper_Helper_Fs_Remote {

	/**
	 * Holds creds model instance
	 *
	 * @var object Shipper_Model_Stored_Creds instance
	 */
	private $creds;

	/**
	 * Gets creds model and attempts auto-update
	 *
	 * @return object Shipper_Model_Stored_Creds instance
	 */
	public function get_creds() {
		$creds = $this->get_creds_model();

		if ( $creds->is_expired() ) {
			$this->update_creds();

			return $this->get_creds_model();
		}

		return $creds;
	}

	/**
	 * Gets creds model
	 *
	 * @return object Shipper_Model_Stored_Creds instance
	 */
	public function get_creds_model() {
		if ( ! empty( $this->creds ) ) {
			return $this->creds;
		}
		$this->creds = new Shipper_Model_Stored_Creds();

		return $this->creds;
	}

	/**
	 * Attemtps updating the credentials info
	 *
	 * Re-initializes creds model on success as a side-effect.
	 *
	 * @return bool
	 * @uses Shipper_Task_Api_Info_Creds
	 */
	public function update_creds() {
		$task   = new Shipper_Task_Api_Info_Creds();
		$result = $task->apply();
		$status = false;

		if ( ! empty( $result ) ) {
			$creds = new Shipper_Model_Stored_Creds();
			$creds->set_data( $result );
			$creds->set_timestamp( time() );
			$creds->save();

			$this->creds = $creds;

			// We're good, reset the error count.
			update_site_option( 'shipper-storage-creds-errcount', 0 );
		} else {
			if ( $task->has_errors() ) {
				foreach ( $task->get_errors() as $error ) {
					Shipper_Helper_Log::write(
						sprintf(
							/* translators: %s: error message .*/
							__( 'Credentials update failed: %s', 'shipper' ),
							$error->get_error_message()
						)
					);
				}
			} else {
				Shipper_Helper_Log::write(
					__( 'Credentials updating task silently failed.', 'shipper' )
				);
			}

			$errcount = get_site_option( 'shipper-storage-creds-errcount', 0 );
			update_site_option( 'shipper-storage-creds-errcount', $errcount + 1 );
			if ( $errcount > 3 ) {
				// So we had more than we can take.
				// Drop now, and let one of continuation mechanisms pick it up later.
				Shipper_Helper_Log::write( 'Postponing further attempts until later.' );
				die;
			}
		}

		return $status;
	}

	/**
	 * Uploads a local path to remote storage
	 *
	 * @param string $path Local path to upload.
	 * @param string $dest Remote path.
	 *
	 * @return object Shipper_Model_Progress instance
	 * @throws Exception Thrown on S3 upload error.
	 */
	public function upload( $path, $dest ) {
		if ( apply_filters( 'shipper_api_mock_local', false ) ) {
			return $this->upload_mock( $path, $dest );
		}

		$progress = new Shipper_Model_Progress();

		$upload = $this->get_upload( $path, $dest );

		if ( ! is_object( $upload ) ) {
			if ( $upload ) {
				$progress->set(
					Shipper_Model_Progress::KEY_STATUS,
					Shipper_Model_Progress::STATUS_DONE
				);
			} else {
				$progress->set_error(
					sprintf(
						/* translators: %s: error message .*/
						__( 'Initialization error on %s', 'shipper' ),
						$path
					)
				);
			}

			return $progress;
		}

		if ( ! $upload->has_transfer() ) {
			$progress->set_error(
				sprintf(
					/* translators: %s: error message .*/
					__( 'Initialization error on %s', 'shipper' ),
					$path
				)
			);

			return $progress;
		}

		$progress->set_total( $upload->get_parts_count() + 1 );
		$progress->set_current( $upload->get_transfered_count() );

		$next  = $upload->get_next();
		$creds = $this->get_creds();
		$s3    = $this->get_remote_storage_handler();

		if ( ! empty( $next ) ) {
			$fs = Shipper_Helper_Fs_File::open( $path );

			if ( ! $fs ) {
				return false;
			}

			if ( $fs->ftell() !== $next['seekTo'] ) {
				$fs->fseek( $next['seekTo'] );
			}

			$body = $fs->fread( $next['length'] );

			// We have a part to upload.
			// Let's do so.
			$response = $s3->uploadPart(
				array(
					'Bucket'     => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
					'Key'        => $this->get_remote_path( $dest ),
					'UploadId'   => $upload->get_transfer_id(),
					'PartNumber' => $next[ Shipper_Model_Stored_Multipart_Uploads::KEY_PART_IDX ] + 1,
					'Body'       => $body,
				)
			);

			if ( $response ) {
				$upload->complete_part( $next );
				$progress->update();
			} else {
				$progress->set_error(
					sprintf(
						/* translators: %1%s %2$s: file name and part. */
						__( 'Error uploading file %1$s, part %2$s', 'shipper' ),
						$dest,
						$next[ Shipper_Model_Stored_Multipart_Uploads::KEY_PART_IDX ]
					)
				);
			}
		} else {
			// We're all done, let's finalize.
			try {
				$parts = $s3->listParts(
					array(
						'Bucket'   => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
						'Key'      => $this->get_remote_path( $dest ),
						'UploadId' => $upload->get_transfer_id(),
					)
				);

				$response = $s3->completeMultipartUpload(
					array(
						'Bucket'          => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
						'Key'             => $this->get_remote_path( $dest ),
						'UploadId'        => $upload->get_transfer_id(),
						'MultipartUpload' => array(
							'Parts' => $parts['Parts'],
						),
					)
				);
			} catch ( Exception $e ) {
				Shipper_Helper_Log::write( wp_json_encode( $e->getMessage() ) );
				$response = false;
			}

			if ( $response ) {
				$progress->set(
					Shipper_Model_Progress::KEY_STATUS,
					Shipper_Model_Progress::STATUS_DONE
				);
			} else {
				$progress->set_error(
					sprintf(
						/* translators: %s: error message file name. .*/
						__( 'Error finalizing upload %s', 'shipper' ),
						$path
					)
				);
			}
			$upload->clear();
			$upload->save();
		}

		return $progress;
	}

	/**
	 * Mock upload
	 *
	 * @param string $path Local path to upload.
	 * @param string $dest Remote path.
	 *
	 * @return object Shipper_Model_Progress instance
	 */
	public function upload_mock( $path, $dest ) {
		$progress    = new Shipper_Model_Progress();
		$destination = trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) . basename( $dest );

		if ( ! file_exists( dirname( $destination ) ) ) {
			wp_mkdir_p( dirname( $destination ) );
		}

		if ( copy( $path, $destination ) ) {
			$progress->update();
		} else {
			$progress->set_error(
				sprintf(
					/* translators: %s: error message file name. */
					__( 'Unable to upload %s', 'shipper' ),
					$path
				)
			);
		}

		return $progress;
	}

	/**
	 * Downloads a remote file to local path
	 *
	 * @param string $fname Remote filename to download.
	 * @param string $path Local path to download to.
	 *
	 * @return object Shipper_Model_Progress instance
	 * @throws Exception Thrown on S3 error.
	 */
	public function download( $fname, $path ) {
		if ( apply_filters( 'shipper_api_mock_local', false ) ) {
			return $this->download_mock( $fname, $path );
		}

		$progress = new Shipper_Model_Progress();

		$download = $this->get_download( $fname );
		if ( ! $download->has_transfer() ) {
			$progress->set_error(
				sprintf(
					/* translators: %s: error message file name. */
					__( 'Initialization error on %s', 'shipper' ),
					$fname
				)
			);

			return $progress;
		}

		$progress->set_total( $download->get_parts_count() + 1 );
		$progress->set_current( $download->get_transfered_count() );

		$next = $download->get_next();

		if ( ! empty( $next ) ) {
			if ( $this->download_part( $fname, $path, $next ) ) {
				$progress->update();
				$download->complete_part( $next );
			} else {
				$progress->set_error(
					sprintf(
						/* translators: %s: error message file name. */
						__( 'Error downloading file %s', 'shipper' ),
						$fname
					)
				);
				$download->clear();
				$download->save();
			}
		} else {
			if ( $this->stitch_download_parts( $path, $download->get_parts() ) ) {
				$progress->set(
					Shipper_Model_Progress::KEY_STATUS,
					Shipper_Model_Progress::STATUS_DONE
				);
			} else {
				$progress->set_error(
					sprintf(
						/* translators: %s: error message file name. */
						__( 'Error finalizing download %s', 'shipper' ),
						$path
					)
				);
			}
			$download->clear();
			$download->save();
		}

		return $progress;
	}

	/**
	 * Mock-downloads a remote file to local path
	 *
	 * @param string $fname Remote filename to download.
	 * @param string $path Local path to download to.
	 *
	 * @return object Shipper_Model_Progress instance.
	 */
	public function download_mock( $fname, $path ) {
		$progress = new Shipper_Model_Progress();

		$fname = trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) . $fname;
		if ( ! file_exists( $fname ) || ! is_readable( $fname ) ) {
			$progress->set_error(
				/* translators: %s: error message file name. */
				sprintf( __( 'Unable to locate the source file: %s', 'shipper' ), $fname )
			);

			return $progress;
		}

		if ( copy( $fname, $path ) ) {
			$progress->update();
		} else {
			$progress->set_error(
				sprintf(
					/* translators: %s: error message file name. */
					__( 'Unable to download %s', 'shipper' ),
					$fname
				)
			);
		}

		return $progress;
	}

	/**
	 * Downloads a file part from S3
	 *
	 * @param string $fname Remote filename to download.
	 * @param string $path Local path to download to.
	 * @param array  $part Part hash to download.
	 *
	 * @return bool
	 */
	public function download_part( $fname, $path, $part ) {
		if ( ! isset( $part['seekTo'] ) || ! isset( $part['length'] ) ) {
			return false;
		}

		$creds = $this->get_creds();
		$s3    = $this->get_remote_storage_handler();

		$response   = false;
		$from_bytes = $part['seekTo'];
		$to_bytes   = ( $from_bytes + (int) $part['length'] ) - 1;
		try {
			$response = $s3->getObject(
				array(
					'Bucket' => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
					'Key'    => $this->get_remote_path( $fname ),
					'SaveAs' => $this->get_download_part_name( $path, $part['seekTo'] ),
					'Range'  => "bytes={$from_bytes}-{$to_bytes}",
				)
			);
		} catch ( Exception $e ) {
			$response = false;
		}

		return ! ! $response;
	}

	/**
	 * Stitches downloaded parts together
	 *
	 * @param string $path Final destination path.
	 * @param array  $parts Parts array to stich.
	 *
	 * @return bool
	 */
	public function stitch_download_parts( $path, $parts ) {
		Shipper_Helper_Log::write( 'Stitching downloaded parts together' );
		$status = true;
		foreach ( $parts as $part ) {
			$idx  = $part['seekTo'];
			$file = $this->get_download_part_name( $path, $idx );
			if ( ! file_exists( $file ) ) {
				$status = false;
				continue;
			}

			$file_reader = Shipper_Helper_Fs_File::open( $file );
			$file_writer = Shipper_Helper_Fs_File::open( $path, 'a' );

			if ( ! $file_reader || ! $file_writer ) {
				return false;
			}

			$status = ! ! $file_writer->fwrite( $file_reader->fread( $file_reader->getSize() ) );

			if ( empty( $status ) ) {
				Shipper_Helper_Log::write( "Stitching failed for part {$idx}" );

				return false;
			}
			@unlink( $file );
		}

		return $status;
	}

	/**
	 * Gets download part name
	 *
	 * @param string $path Final destination path.
	 * @param int    $part_seek Part seek offset.
	 *
	 * @return string
	 */
	public function get_download_part_name( $path, $part_seek ) {
		return "{$path}-part-{$part_seek}";
	}

	/**
	 * Checks if a remote file exists
	 *
	 * @param string $domain Remote filename to check.
	 *
	 * @return bool
	 */
	public function exists( $domain ) {
		$dirname = trailingslashit(
			Shipper_Helper_Fs_Path::clean_fname( $domain )
		) . trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_META );
		$files   = new Shipper_Model_Dumped_Filelist();
		$size    = $this->get_remote_file_size( $dirname . $files->get_file_name() );

		return false !== $size;
	}

	/**
	 * Gets remote file size
	 *
	 * @param string $fname Remote filename to check.
	 *
	 * @return int|bool Size in bytes, or false if no such file
	 */
	public function get_remote_file_size( $fname ) {
		if ( apply_filters( 'shipper_api_mock_local', false ) ) {
			return $this->get_remote_file_size_mock( $fname );
		}

		$creds = $this->get_creds();
		$s3    = $this->get_remote_storage_handler();
		try {
			$response = $s3->headObject(
				array(
					'Bucket' => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
					'Key'    => $this->get_remote_path( $fname ),
				)
			);
		} catch ( Exception $e ) {
			return false;
		}

		return ! ! $response
			? $response['ContentLength']
			: false;
	}

	/**
	 * Gets mocked remote file size
	 *
	 * @param string $fname Remote filename to check.
	 *
	 * @return int|bool Size in bytes, or false if no such file
	 */
	public function get_remote_file_size_mock( $fname ) {
		$fname = trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) . $fname;

		return file_exists( $fname ) && is_readable( $fname )
			? filesize( $fname )
			: false;
	}

	/**
	 * Gets upload parts array for a file
	 *
	 * @param string $path Path to local file.
	 *
	 * @return array
	 */
	public function get_file_upload_parts( $path ) {
		$model = new Shipper_Model_Stored_Multipart_Uploads();
		$parts = array();

		if ( file_exists( $path ) && is_readable( $path ) ) {
			$parts = $model->get_calculated_transfer_parts(
				filesize( $path )
			);
		}

		return $parts;
	}

	/**
	 * Gets current upload model
	 *
	 * Initializes the upload model for the path if it doesn't already exist as
	 * a side-effect.
	 * If the file is smaller than the multipart upload size threshold, it will
	 * upload the file directly as a side-effect.
	 *
	 * @param string $path Path to the file to be uploaded.
	 * @param string $dest Remote destination path.
	 *
	 * @return object|bool Shipper_Model_Stored_Multipart_Uploads instance for large files.
	 *                     (bool)true if the file has been uploaded directly.
	 *                     (bool)false on error.
	 */
	public function get_upload( $path, $dest ) {
		$model = new Shipper_Model_Stored_Multipart_Uploads();
		if ( $model->has_transfer() ) {
			// Upload already created, let's just return that.
			return $model;
		}

		// Okay, so let's initialize upload now.
		$s3    = $this->get_remote_storage_handler();
		$creds = $this->get_creds();
		$parts = $this->get_file_upload_parts( $path );

		$filesize = filesize( $path );

		if ( $filesize < 5 * 1024 * 1024 ) {
			$fs = Shipper_Helper_Fs_File::open( $path );

			if ( ! $fs ) {
				return false;
			}

			// Not multipart candidate - upload straight up.
			try {
				$s3->putObject(
					array(
						'Bucket'               => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
						'Key'                  => $this->get_remote_path( $dest ),
						'ACL'                  => 'private',
						'ServerSideEncryption' => 'AES256',
						'Body'                 => $fs->fread( $fs->getSize() ),
					)
				);

				return true;
			} catch ( Exception $e ) {
				Shipper_Helper_Log::write(
					"Error uploading {$path} to {$dest}: " . $e->getMessage()
				);

				return false;
			}
		} else {
			// Multipart.
			try {
				$upload = $s3->createMultipartUpload(
					array(
						'Bucket'               => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
						'Key'                  => $this->get_remote_path( $dest ),
						'ACL'                  => 'private',
						'ServerSideEncryption' => 'AES256',
					)
				);

				$model->create(
					(string) $upload['UploadId'],
					$parts
				);
			} catch ( Exception $e ) {
				Shipper_Helper_Log::write(
					sprintf(
						/* translators: %s: error message. */
						__( 'Error initializing upload: %s', 'shipper' ),
						$e->getMessage()
					)
				);
			}
		}

		return $model;
	}

	/**
	 * Gets current download model
	 *
	 * Initializes the download model for the path if it doesn't already exist
	 * as a side-effect.
	 *
	 * @param string $fname Remote filename to download.
	 *
	 * @return object Shipper_Model_Stored_Multipart_Downloads instance
	 */
	public function get_download( $fname ) {
		$model = new Shipper_Model_Stored_Multipart_Downloads();
		if ( $model->has_transfer() ) {
			// Upload already created, let's just return that.
			return $model;
		}

		$size = $this->get_remote_file_size( $fname );

		if ( $size ) {
			$parts = $model->get_calculated_transfer_parts( $size );

			$model->create( $fname, $parts );
		}

		return $model;
	}

	/**
	 * Gets upload handler object
	 *
	 * @return object S3 SDK instance
	 */
	public function get_remote_storage_handler() {
		static $storage_handler;
		if ( empty( $storage_handler ) ) {
			if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
				// Require external SDK just in time for this.
				require_once dirname( SHIPPER_PLUGIN_FILE ) . '/vendor/autoload.php';
			}
			$creds = $this->get_creds();
			$ca    = trailingslashit( ABSPATH . WPINC ) . 'certificates/ca-bundle.crt';

			$storage_handler = new Aws\S3\S3Client(
				array(
					'version'     => '2006-03-01',
					'region'      => 'us-east-1',
					'credentials' => array(
						'key'    => $creds->get( Shipper_Model_Stored_Creds::KEY_ID ),
						'secret' => $creds->get( Shipper_Model_Stored_Creds::KEY_SECRET ),
						'token'  => $creds->get( Shipper_Model_Stored_Creds::KEY_TOKEN ),
					),
					'http'        => array(
						'verify' => $ca,
					),
				)
			);
		}

		return $storage_handler;
	}

	/**
	 * Returns remote FS path
	 *
	 * @param string $path Local path.
	 *
	 * @return string
	 */
	public function get_remote_path( $path ) {
		$creds = $this->get_creds();

		$upload_path = trailingslashit(
			$creds->get( Shipper_Model_Stored_Creds::KEY_PREFIX )
		) . $path;

		return $upload_path;
	}

	/**
	 * Gets S3 upload command
	 *
	 * @param string $source Source file absolute path (local fs).
	 * @param string $dest_relpath Destination relative path (on S3).
	 *
	 * @return array|false
	 */
	public function get_upload_command( $source, $dest_relpath ) {
		$s3    = $this->get_remote_storage_handler();
		$creds = $this->get_creds();

		if ( ! is_readable( $source ) ) {
			// We won't be able to upload this.
			return false;
		}

		$cmd             = $s3->getCommand(
			'PutObject',
			array(
				'Bucket'               => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
				'Key'                  => $this->get_remote_path( $dest_relpath ),
				'ACL'                  => 'private',
				'ServerSideEncryption' => 'AES256',
				'SourceFile'           => $source,
			)
		);
		$cmd['@retries'] = 5;

		return $cmd;
	}

	/**
	 * Gets S3 download command
	 *
	 * @param string $source_relpath Relative source file path (on S3).
	 * @param string $destination Destination absolute path (local FS).
	 *
	 * @return array
	 */
	public function get_download_command( $source_relpath, $destination ) {
		$s3    = $this->get_remote_storage_handler();
		$creds = $this->get_creds();

		$cmd             = $s3->getCommand(
			'getObject',
			array(
				'Bucket' => $creds->get( Shipper_Model_Stored_Creds::KEY_BUCKET ),
				'Key'    => $this->get_remote_path( $source_relpath ),
				'SaveAs' => $destination,
			)
		);
		$cmd['@retries'] = 5;

		return $cmd;
	}

	/**
	 * Executes remote batch commands queue
	 *
	 * @param array $batch A list of S3 commands to execute.
	 *
	 * @return bool
	 */
	public function execute_batch_queue( $batch ) {
		if ( Shipper_Model_Env::is_phpunit_test() ) {
			return false;
		}

		$s3                = $this->get_remote_storage_handler();
		$this->batch_error = false;

		try {
			\Aws\CommandPool::batch(
				$s3,
				$batch,
				array(
					'concurrency' => 5,
					'rejected'    => function ( $reason, $key, $aggregate = false ) {
						Shipper_Helper_Log::write(
							sprintf(
								'[FAIL] Transfer failed for [%s]: [%s]',
								$key,
								$reason->getMessage()
							)
						);
						$this->batch_error = true;
						if ( $aggregate && is_callable( array( $aggregate, 'reject' ) ) ) {
							Shipper_Helper_Log::write( 'Rejecting aggregate' );
							$aggregate->reject( 'Reject all' );
						}
					},
				)
			);
		} catch ( Exception $e ) {
			Shipper_Helper_Log::write(
				sprintf(
					'Batch upload unexpected exception: %s',
					$e->getMessage()
				)
			);
			$this->batch_error = true;
		}

		return ! $this->batch_error;
	}
}