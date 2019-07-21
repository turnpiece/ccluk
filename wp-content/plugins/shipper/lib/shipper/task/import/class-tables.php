<?php
/**
 * Shipper tasks: import database tables
 *
 * This task restores database tables from the cold storage.
 *
 * @package shipper
 */

/**
 * Tables import class
 */
class Shipper_Task_Import_Tables extends Shipper_Task_Import {

	const ERR_RECOVERABLE = 'recoverable_error';
	const ERR_BREAKING = 'offending_error';

	const PHASE_INIT = 'init';
	const PHASE_PROCESS = 'processing';
	const PHASE_POSTPROCESS = 'postprocessing';
	const PHASE_FINISHED = 'finished';

	/**
	 * Holds name of the table currently being processed
	 *
	 * @var string
	 */
	private $_current_table;

	/**
	 * Holds storage instance
	 *
	 * @var object Shipper_Model_Stored_Tablelist instance
	 */
	private $_storage;

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
	 * Gets a storage instance
	 *
	 * Spawns one if necessary
	 *
	 * @return object Shipper_Model_Stored_Tablelist instance
	 */
	public function get_storage() {
		if ( ! isset( $this->_storage ) ) {
			$this->_storage = new Shipper_Model_Stored_Tablelist;
		}
		return $this->_storage;
	}

	/**
	 * Gets a cached list of tables.
	 *
	 * Updates cache if needed as a side-effect.
	 *
	 * @return array
	 */
	public function get_tables_list() {
		$list = $this->get_storage();
		$tables = $list->get( Shipper_Model_Stored_Tablelist::KEY_TABLES_LIST, array() );
		if ( empty( $tables ) ) {
			$tables = Shipper_Model_Database_Table_Import::get_table_names_from_files();

			if ( ! empty( $tables ) ) {
				// Update cached list for future reference.
				$list->set( Shipper_Model_Stored_Tablelist::KEY_TABLES_LIST, $tables );
				$list->save();
			}
		}

		/**
		 * Source tables processing list
		 *
		 * Used in tests suite.
		 *
		 * @param array $tables List of extracted table names (from SQL files).
		 *
		 * @return array
		 */
		return apply_filters(
			'shipper_task_import_tables_list',
			$tables
		);
	}

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		$table = $this->get_current_table();
		$current = '';
		if ( ! empty( $table ) ) {
			$nfo = $this->get_storage()->get( $table, array() );
			$pos = ! empty( $nfo['position'] ) ? (int) $nfo['position'] : 0;
			$phase = ! empty( $nfo['phase'] ) ? $nfo['phase'] : '';
			$current = sprintf( __( '(table %1$s, restoring, at position %2$d)', 'shipper' ), $table, $pos );

			if ( self::PHASE_INIT === $phase ) {
				$current = sprintf( __( '(table %s, estimate work)', 'shipper' ), $table );
			} elseif ( self::PHASE_FINISHED === $phase ) {
				$current = sprintf( __( '(table %s, finalize)', 'shipper' ), $table );
			}
		}
		return sprintf(
			__( 'Restore the database %s', 'shipper' ),
			$current
		);
	}

	public function get_total_steps() {
		return count( $this->get_tables_list() );
	}

	public function get_current_step() {
		$processed = $this->get_storage()->get(
			Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES,
			array()
		);
		return count( $processed );
	}

	/**
	 * Actually imports the table
	 *
	 * @param string $table Table to import.
	 *
	 * @return bool True if all done, false otherwise
	 */
	public function import_table( $table ) {
		$nfo = $this->get_storage()->get( $table, array() );
		$total = ! empty( $nfo['total'] ) ? (int) $nfo['total'] : 0;
		$position = ! empty( $nfo['position'] ) ? (int) $nfo['position'] : 0;
		$phase = ! empty( $nfo['phase'] ) ? $nfo['phase'] : self::PHASE_INIT;

		// @TODO make this reasonable
		$limit = apply_filters(
			'shipper_import_tables_row_limit',
			25 // Reduce this limit by quite a bit in comparison to export.
		);

		$model = new Shipper_Model_Database_Table_Import( $table );

		if ( empty( $total ) ) {
			$phase = self::PHASE_INIT;
			$status = $model->preprocess_import_file();

			if ( empty( $status ) ) {
				$this->add_error(
					self::ERR_BREAKING,
					sprintf(
						__( 'Error working with intermediate representation: %s', 'shipper' ),
						basename( $table )
					)
				);
				// Serious enough to not go further with this table.
				return true;
			}

			// Also, let's see how many statements there is.
			$total = $model->get_statements_count();
			$position = 0;

			Shipper_Helper_Log::write(sprintf(
				__( 'Found %1$d statements for %2$s', 'shipper' ),
				$total, $table
			));

			if ( 0 === $total ) {
				// Warn about this - there should be at least *some* inside.
				$phase = self::PHASE_FINISHED;
				Shipper_Helper_Log::write(sprintf(
					__( 'Empty statement count found for %s, skipping.', 'shipper' ),
					$table
				));
			}
		} elseif ( self::PHASE_POSTPROCESS !== $phase ) {
			$phase = self::PHASE_PROCESS;

			$status = $model->import_statements( $position, $limit );
			if ( is_wp_error( $status ) ) {
				return true;
			}

			$position += $status;

			if ( $position >= $total ) {
				$phase = self::PHASE_POSTPROCESS;
			}
		} else {
			// Finishing phase - rename intermediate table to what it should be named as.
			$phase = self::PHASE_FINISHED;

			/**
			 * Whether we're in import mocking mode, defaults to false.
			 *
			 * In tables import mocking mode, intermediate tables will be created.
			 * However, they won't be ported over to the existing tables.
			 * As a side-effect of this, the database size _will_ double.
			 *
			 * @param bool $is_mock_import Whether we're in mock import mode.
			 *
			 * @return bool
			 */
			$is_mock_import = apply_filters(
				'shipper_import_mock_tables',
				false
			);
			if ( ! $is_mock_import ) {
				$status = $model->import_table_finish();
				if ( empty( $status ) ) {
					$this->add_error(
						self::ERR_SQL,
						sprintf( __( 'Error finalizing table %1$s', 'shipper' ), $table )
					);
					return true; // Short-out, we're done here.
				}
			}
		}

		$this->get_storage()->set($table, array(
			'total' => $total,
			'position' => $position,
			'phase' => $phase,
		));
		$this->get_storage()->save();

		return $position >= $total && self::PHASE_FINISHED === $phase;
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$tables = $this->get_tables_list();
		$processed = $this->get_storage()->get(
			Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES,
			array()
		);

		foreach ( $tables as $table ) {
			if ( in_array( $table, $processed, true ) ) {
				// Already done this one.
				continue;
			}
			$this->_current_table = $table;
			$status = $this->import_table( $table );

			if ( ! empty( $status ) ) {
				$processed[] = $table;
			}

			// Do one step at the time.
			break;
		}

		// Update list.
		$this->get_storage()->set( Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES, $processed );
		$this->get_storage()->save();

		return count( $tables ) === count( $processed );
	}
}
