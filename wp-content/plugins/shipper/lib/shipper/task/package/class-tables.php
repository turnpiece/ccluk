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
class Shipper_Task_Package_Tables extends Shipper_Task_Export_Tables {

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
		$dumper            = Shipper_Helper_Dumper::get_provider( 'php' );
		$pre_dump_sql_path = Shipper_Helper_Fs_Path::get_temp_dir() . Shipper_Helper_Dumper::PRE_DUMP_SQL;

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

		do_action( 'shipper_before_dump_table_for_package_migration', $dumper );

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
		Shipper_Helper_Log::debug( 'dump.sql file size => ' . size_format( filesize( $dump_to_this_path ) ) );

		$existing_row_count = static::$model->get( 'row_count', 0 );
		static::$model->set( 'row_count', $existing_row_count + static::$row_count );
		static::$model->save();

		if ( ! $is_done ) {
			return false;
		}

		do_action( 'shipper_after_dump_table_for_package_migration', $dumper );

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
	 * @return Shipper_Model_Stored_PackageMeta
	 */
	protected function get_migration_meta() {
		return new Shipper_Model_Stored_PackageMeta();
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
		$destination = $this->get_destination_path( $dump_file_name );
		$zip         = Shipper_Task_Package::get_zip();

		if ( ! $zip->add_file( $exported_file, $destination ) ) {
			/* translators: %1$s %2$s: file and destination path. */
			Shipper_Helper_Log::write( sprintf( __( 'Shipper couldn\'t archive exported table %1$s as %2$s', 'shipper' ), $exported_file, $destination ) );
			return false;
		}
		$zip->close();

		return true;
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
}