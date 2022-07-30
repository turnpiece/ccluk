<?php
/**
 * Shipper packages task: DB tables export
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Tables package - extends from export for now, and facades the package task interface
 */
class Shipper_Task_Export_Tables extends Shipper_Task_Export {

	/**
	 * Number of rows
	 *
	 * @var int
	 */
	protected static $row_count = 0;

	/**
	 * Current work description
	 *
	 * @var string
	 */
	protected static $work_description = '';

	/**
	 * Model instance holder
	 *
	 * @var \Shipper_Model_Stored_Dump instance.
	 */
	protected static $model;

	/**
	 * Apply method.
	 *
	 * @param array $args array of arguments.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		// Update status flag first.
		$this->has_done_anything = true;
		$dumper                  = Shipper_Helper_Dumper::get_provider( 'php' );
		$pre_dump_sql_path       = Shipper_Helper_Fs_Path::get_temp_dir() . Shipper_Helper_Dumper::PRE_DUMP_SQL;

		static::$model = new Shipper_Model_Stored_Dump();

		$dumper->set_info_hook(
			function( $object, $info ) {
				if ( 'table' === $object ) {
					static::$row_count       += $info['row_count'];
					static::$work_description = sprintf(
						/* translators: %1$s %2$d %3$d: exported table name, current and total steps. */
						__( 'Trying to export %1$s - (%2$d of %3$d rows)', 'shipper' ),
						$info['name'],
						$this->get_current_step(),
						$this->get_total_steps()
					);

					Shipper_Helper_Log::write( $this->get_work_description() );
				}
			}
		);

		do_action( 'shipper_before_dump_table_for_api_migration', $dumper );

		$is_done = $dumper->start( $pre_dump_sql_path );
		$fs      = Shipper_Helper_Fs_File::open( $pre_dump_sql_path );
		$content = $fs->fread( $fs->getSize() );

		$replacer = new Shipper_Helper_Replacer_String( Shipper_Helper_Codec::ENCODE );
		$replacer->set_codec_list(
			array(
				new Shipper_Helper_Codec_Define(),
				new Shipper_Helper_Codec_Var(),
				new Shipper_Helper_Codec_Sql(),
				new Shipper_Helper_Codec_Domain(),
				new Shipper_Helper_Codec_Preoptionname(),
			)
		);

		if ( $this->is_extracting() ) {
			$replacer->add_codec( new Shipper_Helper_Codec_Subsite( $this->get_migration_meta() ) );
		}

		$transformed_content = $replacer->transform( $content );
		$dump_to_this_path   = Shipper_Helper_Fs_Path::get_temp_dir() . Shipper_Helper_Dumper::DUMP_SQL;

		$fs = Shipper_Helper_Fs_File::open( $dump_to_this_path, 'a+b' );
		$fs->fwrite( $transformed_content );

		Shipper_Helper_Log::debug(
			sprintf(
				/* translators: %s: File size. */
				__( 'Dumped SQL file size: %s', 'shipper' ),
				size_format( filesize( $dump_to_this_path ) )
			)
		);

		$existing_row_count = static::$model->get( 'row_count', 0 );
		static::$model->set( 'row_count', $existing_row_count + static::$row_count );
		static::$model->save();

		if ( ! $is_done ) {
			return false;
		}

		do_action( 'shipper_after_dump_table_for_api_migration', $dumper );

		return $this->table_to_final_destination( Shipper_Helper_Dumper::DUMP_SQL, $dump_to_this_path );
	}

	/**
	 * Whether is in extracting mode or not
	 *
	 * @return bool
	 */
	protected function is_extracting() {
		return $this->get_migration_meta()->is_extract_mode();
	}

	/**
	 * Get migration meta
	 *
	 * @return Shipper_Model_Stored_MigrationMeta
	 */
	protected function get_migration_meta() {
		return new Shipper_Model_Stored_MigrationMeta();
	}

	/**
	 * Table to final destination
	 *
	 * @param string $dump_file_name dump.sql file name.
	 * @param string $exported_file exported file name.
	 *
	 * @return bool
	 */
	public function table_to_final_destination( $dump_file_name, $exported_file ) {
		$migration = new Shipper_Model_Stored_Migration();
		$remote    = new Shipper_Helper_Fs_Remote();

		$destination    = $this->get_destination_path( $dump_file_name );
		$dest_root      = Shipper_Helper_Fs_Path::clean_fname( $migration->get_destination() );
		$s3_dest        = trailingslashit( $dest_root ) . $destination;
		$progress       = $remote->upload( $exported_file, $s3_dest );
		$upload_is_done = $progress->is_done();

		if ( $upload_is_done && $progress->has_error() ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %s: exporting file name. */
					__( 'Uploading %s failed, will re-try' ),
					$exported_file
				)
			);
			$upload_is_done = false;
		}

		if ( $upload_is_done ) {
			Shipper_Helper_Log::debug( __( 'Table upload is done.', 'shipper' ) );

			// Update filelist manifest.
			$dumped      = new Shipper_Model_Dumped_Filelist();
			$target_line = array(
				'source'      => $exported_file,
				'destination' => $destination,
				'size'        => filesize( $exported_file ),
			);
			$dumped->add_statement( $target_line );
			$dumped->close();
		}

		return $upload_is_done;
	}

	/**
	 * Gets the current position in current task finalization
	 *
	 * @since 1.2.1
	 *
	 * @return int
	 */
	public function get_current_step() {
		return static::$model->get( 'row_count', static::$row_count );
	}

	/**
	 * Gets export task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return static::$work_description;
	}

	/**
	 * Gets readable source path for a file.
	 *
	 * @param string $path Absolute file path.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return void
	 */
	public function get_source_path( $path, $migration ) {}

	/**
	 * Gets destination type
	 *
	 * Used for classifying output files in the ZIP structure.
	 *
	 * @return string
	 */
	public function get_destination_type() {
		return Shipper_Model_Stored_Migration::COMPONENT_DB;
	}

	/**
	 * Gets the number of steps required to finalize this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return ( new Shipper_Model_Database() )->get_total_rows();
	}
}