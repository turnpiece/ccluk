<?php
/**
 * Shipper tasks: files export
 *
 * Will export DB tables to a ZIP archive, ready for migration.
 *
 * @package shipper
 */

/**
 * Files export task class
 */
class Shipper_Task_Export_Tables extends Shipper_Task_Export {

	/**
	 * Holds name of the table currently being processed
	 *
	 * @var string
	 */
	private $_current_table;

	/**
	 * Task runner method
	 *
	 * Returns (bool)true when the export is done, and
	 * (bool)false otherwise.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration;
		$tablelist = new Shipper_Model_Stored_Tablelist;
		$remote = new Shipper_Helper_Fs_Remote;

		$tables = $this->get_tables_list( $tablelist );
		$is_done = true;
		$processed = $tablelist->get(
			Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES,
			array()
		);
		$dest_root = Shipper_Helper_Fs_Path::clean_fname( $migration->get_destination() );

		foreach ( $tables as $table ) {
			if ( in_array( $table, $processed, true ) ) {
				// We have already done this one.
				continue;
			}

			/**
			 * Whether to actually include this table in migration processing
			 *
			 * @param bool $do_process_item Include item.
			 * @param string $table Table name.
			 *
			 * @return bool
			 */
			$do_process_item = apply_filters(
				'shipper_path_include_table',
				true,
				$table
			);
			if ( ! $do_process_item ) { continue; }

			// Update status flag first.
			$this->_has_done_anything = true;

			$this->_current_table = $table;

			$source = $this->get_source_path( $table, $migration );
			if ( empty( $source ) ) {
				// Incomplete export, pick it up in the next step.
				return false;
			}

			$is_done = false;

			if ( ! is_readable( $source ) ) {
				/*
				$this->add_error(
					self::ERR_ACCESS,
					sprintf( __( 'Shipper couldn\'t read file: %s', 'shipper' ), $source )
				);
				 */
				// Could be a competeing access.
				Shipper_Helper_Log::write(
					sprintf( __( 'Shipper couldn\'t read file: %s', 'shipper' ), $source )
				);
				return false;
			}

			$destination = $this->get_destination_path( $table . '.sql' );
			$s3_dest = trailingslashit( $dest_root ) . $destination;
			$progress = $remote->upload( $source, $s3_dest );
			$upload_is_done = $progress->is_done();

			if ( $upload_is_done && $progress->has_error() ) {
				Shipper_Helper_Log::write(
					sprintf( 'Uploading %s failed, will re-try', $table )
				);
				$upload_is_done = false;
			}

			if ( $upload_is_done ) {
				// Update filelist manifest.
				$dumped = new Shipper_Model_Dumped_Filelist;
				$target_line = array(
					'source' => $source,
					'destination' => $destination,
					'size' => filesize( $source ),
				);
				$dumped->add_statement( $target_line );
				$dumped->close();
				shipper_delete_file( $source );

				Shipper_Helper_Log::debug(
					sprintf( __( 'Exported and archived table %s', 'shipper' ), $table )
				);

				// Update processed tables migration state.
				$processed[] = $table;
				$tablelist->set( Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES, $processed );
			}

			// And break while going is good.
			break;
		}
		$tablelist->save();

		return $is_done;
	}

	/**
	 * Gets currently processed table
	 *
	 * @return string
	 */
	public function get_current_table() {
		return isset( $this->_current_table )
			? $this->_current_table
			: ''
		;
	}

	/**
	 * Sets current table
	 *
	 * Used in tests
	 *
	 * @param string $table Table to set.
	 */
	public function set_current_table( $table ) {
		$this->_current_table = $table;
	}

	/**
	 * Gets a cached list of tables.
	 *
	 * Updates cache if needed as a side-effect.
	 *
	 * @param object $storage Shipper_Model_Stored_Tablelist instance.
	 *
	 * @return array
	 */
	public function get_tables_list( $storage ) {
		$tables = $storage->get( Shipper_Model_Stored_Tablelist::KEY_TABLES_LIST, array() );
		if ( ! empty( $tables ) ) {
			// Already have a list of cached tables.
			return $tables;
		}

		$db = new Shipper_Model_Database;
		$raw_tables = $db->get_tables_list();
		$tables = array();
		$rx = preg_quote( Shipper_Task_Import::PREFIX, '/' );

		foreach ( $raw_tables as $table ) {
			if ( preg_match( "/^{$rx}/", $table ) ) { continue; }
			$tables[] = $table;
		}

		if ( empty( $tables ) && $db->has_error() ) {
			$this->add_error(
				self::ERR_SQL,
				sprintf(
					__( 'Unable to list tables on %1$s - the database said [%2$s]', 'shipper' ),
					DB_NAME, $db->get_error()
				)
			);
			return array();
		}
		$storage->set(
			Shipper_Model_Stored_Tablelist::KEY_TABLES_LIST,
			$tables
		);

		// Analyze tables.
		$totals = $db->get_tables_rows_count( $tables );
		foreach ( $totals as $table => $rows ) {
			$state = $storage->get( $table, array() );
			if ( isset( $state['total'] ) ) {
				// Already have this!
				continue;
			}
			$state['total'] = $rows;
			$storage->set( $table, $state );
		}
		$storage->save();

		return $tables;
	}

	/**
	 * Gets readable source path for a table.
	 *
	 * Will export table values to the intermediate file.
	 * Will update migration state as a side-effect.
	 * Will return (bool)false if the file is incomplete.
	 *
	 * @param string $table Table name to export.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string|bool
	 */
	public function get_source_path( $table, $migration ) {
		$storage = new Shipper_Model_Stored_Tablelist;
		$model = new Shipper_Model_Database_Table_Export( $table );

		$state = $storage->get( $table, array() );
		// @TODO make this reasonable
		$limit = apply_filters(
			'shipper_export_tables_row_limit',
			250
		);

		$done = isset( $state['done'] ) ? ! ! $state['done'] : false;
		$position = isset( $state['position'] ) ? (int) $state['position'] : 0;
		$total = isset( $state['total'] ) ? (int) $state['total'] : 0;

		$destination = $model->get_file_path();

		if ( ! ! $done ) {
			// Okay, so we're done here - file is there.
			return $destination;
		}

		if ( 0 === $position ) {
			// First iteration on a table, write the header.
			$res = file_put_contents( $destination, $model->get_header() );
			if ( false === $res ) {
				$this->add_error(
					self::ERR_ACCESS,
					sprintf( __( 'Shipper couldn\'t write to file: %s', 'shipper' ), $destination )
				);
				return false;
			}
		}

		$sqls = $model->get_rows_sql( $position, $limit );
		if ( empty( $sqls ) && $model->has_error() ) {
			$this->add_error(
				self::ERR_SQL,
				sprintf( __( 'Error selecting results for %s', 'shipper' ), $table )
			);
		}

		if ( ! empty( $sqls ) ) {
			$res = file_put_contents( $destination, join( "\n", $sqls ), FILE_APPEND );
			if ( false === $res ) {
				$this->add_error(
					self::ERR_ACCESS,
					sprintf( __( 'Shipper couldn\'t write to file: %s', 'shipper' ), $destination )
				);
				return false;
			}
		}

		//$done = count( $sqls ) < $limit; // If we have less than limit results, we should be good.
		$done = empty( $sqls ); // We're done when we got nothing in results.
		$position += count( $sqls ); // Record new position.

		$storage->set($table, array(
			'done' => $done,
			'position' => $position,
			'total' => $total,
		));
		$storage->save();

		// @TODO: perhaps get rid of this entirely? #cleanup #performance
		if ( ! empty( $done ) ) {
			// If we're done now with table exporting, let's process the entire file too.
			$replacer = new Shipper_Helper_Replacer_File( Shipper_Helper_Codec::ENCODE );
			// Just the SQL codec though!
			$replacer->set_codec_list( array(
				new Shipper_Helper_Codec_Sql
			) );
			$destination = $replacer->transform( $destination );
		}

		return ! empty( $done )
			? $destination
			: false
		;
	}

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
	 * Returns total number of tables that will be processed
	 *
	 * @return int
	 */
	public function get_total_tables_count() {
		$tablelist = new Shipper_Model_Stored_Tablelist;
		$tables = $this->get_tables_list( $tablelist );

		return count( $tables );
	}

	/**
	 * Returns count of tables processed this far
	 *
	 * @return int
	 */
	public function get_processed_tables_count() {
		$tablelist = new Shipper_Model_Stored_Tablelist;
		$processed = $tablelist->get(
			Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES,
			array()
		);

		return count( $processed );
	}

	/**
	 * Gets total rows to process in the database
	 *
	 * @return int
	 */
	public function get_total_rows_count() {
		$tablelist = new Shipper_Model_Stored_Tablelist;
		$tables = $this->get_tables_list( $tablelist );
		$total = 0;

		foreach ( $tables as $table ) {
			$state = $tablelist->get( $table, array() );
			if ( ! empty( $state['total'] ) ) {
				$total += (int) $state['total'];
			}
		}

		return $total;
	}

	/**
	 * Returns total number of rows processed this far
	 *
	 * @return int
	 */
	public function get_processed_rows_count() {
		$tablelist = new Shipper_Model_Stored_Tablelist;
		$current = $this->get_current_table();
		$state = $tablelist->get( $current, array() );
		$processed = $tablelist->get(
			Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES,
			array()
		);
		$total = isset( $state['position'] ) ? (int) $state['position'] : 0;

		foreach ( $processed as $table ) {
			if ( $table === $current ) {
				continue;
			}
			$state = $tablelist->get( $table, array() );
			if ( ! empty( $state['total'] ) ) {
				$total += (int) $state['total'];
			}
		}

		return $total;
	}

	/**
	 * Gets the number of steps required to finalize this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return $this->get_total_rows_count() + $this->get_total_tables_count();
	}

	/**
	 * Gets the current position in current task finalization
	 *
	 * @return int
	 */
	public function get_current_step() {
		return $this->get_processed_rows_count() + $this->get_processed_tables_count();
	}

	/**
	 * Gets export task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		$current = $this->get_current_table();
		$position = false;
		$done = false;
		if ( ! empty( $current ) ) {
			$storage = new Shipper_Model_Stored_Tablelist;
			$state = $storage->get( $current, array() );
			$position = isset( $state['position'] ) ? (int) $state['position'] : 0;
			$done = isset( $state['done'] ) ? ! ! $state['done'] : false;
			$rows = isset( $state['total'] ) ? (int) $state['total'] : 0;
		}

		$details = __( 'uploaded, analyzing next', 'shipper' );
		if ( ! empty( $position ) && empty( $done ) ) {
			$details = sprintf(
				__( 'row %1$d of %2$d', 'shipper' ),
				(int) $position, (int) $rows
			);
		}

		$detailed_description = '';
		if ( ! empty( $current ) ) {
			$detailed_description = sprintf(
				__( '( table %1$d of %2$d: %3$s, %4$s )', 'shipper' ),
				$this->get_processed_tables_count(),
				$this->get_total_tables_count(),
				$current, $details
			);
		}
		return sprintf(
			__( 'Process tables %s', 'shipper' ),
			$detailed_description
		);
	}
}
