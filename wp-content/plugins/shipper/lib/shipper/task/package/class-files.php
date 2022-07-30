<?php
/**
 * Shipper tasks: files packaging task
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Class Shipper_Task_Package_Files
 *
 * @since 1.2.2
 */
class Shipper_Task_Package_Files extends Shipper_Task_Package {

	/**
	 * Current pointer position
	 *
	 * @var int
	 */
	private $current_position;

	/**
	 * Max files to be added at once in ZIP (in the safe mode).
	 *
	 * @var int
	 */
	private $max_counter = 10;

	/**
	 * Number of files added into ZIP so far.
	 *
	 * @var int
	 */
	private $counter = 0;

	/**
	 * Max file size limit (in the safe mode).
	 *
	 * @var float|int
	 */
	private $max_file_size = 50 * 1024 * 1024;

	/**
	 * Total file size count (in the safe mode).
	 *
	 * @var int
	 */
	private $file_size = 0;

	/**
	 * Run the task
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return bool|mixed
	 */
	public function apply( $args = array() ) {
		$zip       = self::get_zip();
		$file_list = new Shipper_Model_Stored_Filelist();
		$fs        = Shipper_Helper_Fs_File::open( $file_list->get_path() );

		if ( ! $fs ) {
			/* translators: %s: file path */
			Shipper_Helper_Log::write( sprintf( __( 'Unable to open %s', 'shipper' ), $file_list->get_path() ) );
			return false;
		}

		$settings       = new Shipper_Model_Stored_Options();
		$limit          = $settings->get( $settings::KEY_PACKAGE_ZIP_LIMIT, 5000 );
		$pointer        = $file_list->get( $file_list::KEY_CURSOR, 0 );
		$max_line       = $file_list->get( $file_list::MAX_LINE, $limit );
		$total_line     = $file_list->get( $file_list::KEY_TOTAL, 0 );
		$media_replacer = $this->get_media_replacement();
		$is_safe_mode   = apply_filters( 'shipper_is_safe_mode', $settings->get( $settings::KEY_PACKAGE_SAFE_MODE, false ) );
		$starting_time  = microtime( true );

		if ( empty( $total_line ) ) {
			$fs->seek( PHP_INT_MAX );
			$file_list->set( $file_list::KEY_TOTAL, $fs->key() )->save();
			$fs->rewind();
		}

		$fs->seek( $pointer );
		$this->current_position = $pointer;

		while ( ! $fs->eof() ) {
			$line = trim( $fs->current() );
			$fs->next();

			if ( empty( $line ) ) {
				continue;
			}

			list( $src, $dest ) = explode( $file_list->get_separator(), $line );

			if ( ! is_readable( $src ) ) {
				/* translators: %s: file to be added in zip. */
				Shipper_Helper_Log::debug( sprintf( __( 'Unable to zip %s', 'shipper' ), $src ) );
				continue;
			}

			if ( ! empty( $media_replacer ) ) {
				$dest = str_replace( $media_replacer, '', $dest );
			}

			$src = shipper_get_transformed_config_file( $src );

			$current_file_size = filesize( $src );

			if ( $is_safe_mode && $current_file_size >= $this->max_file_size ) {
				// This file is too big, so skip it when safe mode is enabled.
				// Log these file, so admin can import these file manually.
				Shipper_Helper_Log::write(
					sprintf(
						/* translators: %1$s %2$s: file path and size */
						__( '%1$s is too big to zip - %2$s', 'shipper' ),
						$src,
						size_format( $current_file_size )
					)
				);

				continue;
			}

			try {
				$this->file_size += $current_file_size;
				$this->counter ++;
				$zip->add_file( $src, $dest );

				/* translators: %s: file to be added in zip. */
				Shipper_Helper_Log::debug( sprintf( __( 'Zipping %s', 'shipper' ), $src ) );

				if ( $is_safe_mode && $this->counter >= $this->max_counter ) {
					$zip->close();
					$this->counter = 0;
				}
			} catch ( Exception $e ) {
				Shipper_Helper_Log::debug( $e->getMessage() );
			}

			/**
			 * Lets play safe. Some cheap hosts can't handle large files and we can't even manipulate max_execution_time.
			 * That's why lets make sure, we don't cross the max execution time set by the hosting provider.
			 * Yeah, we know it's slow though but better than failing :D
			 *
			 * @since 1.2.4
			 */
			$time_spent         = microtime( true ) - $starting_time;
			$max_execution_time = Shipper_Helper_System::get_safe_max_execution_time();
			$met_safe_mode      = $is_safe_mode && ( $time_spent >= $max_execution_time || $this->file_size >= $this->max_file_size );
			$line_number        = $fs->key();

			if ( $line_number >= $max_line || $met_safe_mode ) {
				$file_list->set( $file_list::KEY_CURSOR, $line_number );
				$file_list->set( $file_list::MAX_LINE, $line_number + $limit );
				$file_list->save();

				return false;
			}
		}

		return true;
	}

	/**
	 * Get total steps
	 *
	 * @return false|int|mixed
	 */
	public function get_total_steps() {
		return ( new Shipper_Model_Stored_Filelist() )->get( Shipper_Model_Stored_Filelist::KEY_TOTAL, 0 );
	}

	/**
	 * Get current step
	 *
	 * @return int|mixed
	 */
	public function get_current_step() {
		return max( $this->current_position, 1 );
	}

	/**
	 * Get media directory replacement string for sub-site
	 * We have to check if this is sub-site extractor, as the media path will be something like files/uploads/sites/id/...
	 *
	 * @return string
	 */
	private function get_media_replacement() {
		$meta           = new Shipper_Model_Stored_PackageMeta();
		$media_replacer = '';

		if ( $meta->get_mode() === 'subsite' && $meta->get_site_id() !== 1 ) {
			$media_replacer = "sites/{$meta->get_site_id()}/";
		}

		return $media_replacer;
	}
}