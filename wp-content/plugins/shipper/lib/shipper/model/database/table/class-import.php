<?php
/**
 * Shipper export models: individual table import model
 *
 * Handles individual table import.
 *
 * @package shipper
 */

/**
 * Table import class
 */
class Shipper_Model_Database_Table_Import extends Shipper_Model_Database_Table {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $_table;

	/**
	 * Constructor
	 *
	 * @param string $table Table name.
	 */
	public function __construct( $table ) {
		parent::__construct();
		$this->_table = (string) $table;
	}

	/**
	 * Gets the table name
	 *
	 * @return string
	 */
	public function get_table_name() {
		return (string) $this->_table;
	}

	/**
	 * Gets source SQL file name
	 *
	 * @return string
	 */
	public function get_file_path() {
		$name = $this->get_table_name();
		if ( empty( $name ) ) {
			return '';
		}

		return trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) .
			trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_DB ) .
			preg_replace( '/[^-_a-z0-9]/i', '', $name ) .
		'.sql';
	}

	/**
	 * Opens up a reading file pointer to a table file.
	 *
	 * @return resource|bool File pointer on success, (bool)false otherwise
	 */
	public function get_file_pointer() {
		$table = $this->get_table_name();
		$source = $this->get_file_path( $table );

		if ( ! file_exists( $source ) || ! is_readable( $source ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Unable to read table %1$s data from %2$s', 'shipper' ),
					$table, $source
				)
			);
			return false;
		}

		$fp = fopen( $source, 'r' );
		if ( false === $fp ) { return false; }

		fseek( $fp, 0 );

		return $fp;
	}

	/**
	 * Counts statements total in a table source
	 *
	 * @return int
	 */
	public function get_statements_count() {
		$table = $this->get_table_name();
		$fp = $this->get_file_pointer();
		$count = 0;
		if ( false === $fp ) {
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Unable to get the number of statements to import for %s', 'shipper' ),
					$table
				)
			);
			return $count;
		}

		$delimiter_rx = preg_quote( Shipper_Model_Database_Table::STATEMENT_DELIMITER, '/' );
		while ( ($line = fgets( $fp )) !== false ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				// Nothing to do, empty line.
				continue;
			}
			if ( preg_match( "/{$delimiter_rx}/", $line ) ) {
				$count++;
			}
		}

		// Clean up after ourselves.
		fclose( $fp );

		return $count;
	}

	/**
	 * Extracts a number of statements to import from source
	 *
	 * @param int $position Offset to start from.
	 * @param int $limit Import at most this many statements.
	 *
	 * @return array Statements to import, as SQL strings
	 */
	public function get_statements( $position, $limit ) {
		$statements = array();
		$table = $this->get_table_name();
		$fp = $this->get_file_pointer();
		if ( false === $fp ) {
			Shipper_Helper_Log::write(
				sprintf(
					__(
						'Unable to extract the statements to import for %1$s from position %2$d',
						'shipper'
					),
					$table, $position
				)
			);
			return $statements;
		}

		$delimiter_rx = preg_quote( Shipper_Model_Database_Table::STATEMENT_DELIMITER, '/' );
		$count = 0;
		while ( ($line = fgets( $fp )) !== false ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				// Nothing to do, empty line.
				continue;
			}
			if ( preg_match( "/{$delimiter_rx}/", $line ) ) {
				// We have the delimiter - increase the count, but don't include.
				$count++;
				// Had enough? We're done for now.
				if ( count( $statements ) >= $limit ) { break; }
				continue;
			}
			if ( preg_match( '/^#/', $line ) ) {
				// Comment line - don't include.
				continue;
			}

			// The meat part - add statement to the queue.
			if ( $count >= $position ) {
				if ( ! isset( $statements[ $count ] ) ) { $statements[ $count ] = ''; }
				$statements[ $count ] .= $line;
			}
		}

		// At this point, we have somewhat randonly indexes statements.
		$statements = array_values( $statements );

		// Clean up after ourselves.
		fclose( $fp );

		return $statements;
	}

	/**
	 * Gets a list of table names from SQL files list
	 *
	 * @return array
	 */
	static public function get_table_names_from_files() {
		// static $tables;
		$tables = array();

		if ( empty( $tables ) ) {
			$tables = array();
			$source = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) .
				trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_DB );
			$raw = glob( "{$source}*.sql" );
			foreach ( $raw as $file ) {
				$tables[] = pathinfo( $file, PATHINFO_FILENAME );
			}

			// Resolve table dependencies and options tables.
			$dependencies = array();
			$options = array();
			$matcher = '(' . join( '|', $tables ) . ')';
			foreach ( $tables as $tidx => $table ) {

				// Check if we're dealing with an options-like table first.
				if ( preg_match( '/(options|sitemeta)$/', $table ) ) {
					// We do.
					$options[] = $table;
					unset( $tables[ $tidx ] );
					// We'll be pushing that last anyway, so carry on.
					continue;
				}

				$model = new Shipper_Model_Database_Table_Import( $table );
				$statements = $model->get_statements( 1, 1 );
				if ( empty( $statements ) ) { continue; }
				if ( ! preg_match( '/create table/i', $statements[0] ) ) { continue; }

				if ( preg_match( "/{$matcher}/", $statements[0] ) ) {
					$dependencies[] = $table;
					unset( $tables[ $tidx ] );
				}
			}
			if ( ! empty( $dependencies ) ) {
				Shipper_Helper_Log::debug( sprintf(
					'Detected table dependencies. Re-queueing dependent tables: %s',
					join( ', ', $dependencies )
				) );
			}
			foreach ( $dependencies as $dep ) {
				$tables[] = $dep;
			}

			if ( ! empty( $options ) ) {
				foreach( $options as $options_like_table ) {
					$tables[] = $options_like_table;
				}
			}
		}

		/**
		 * Resolved table names, according to unpacked files
		 *
		 * Used in tests.
		 *
		 * @param array $tables Resolved table names.
		 *
		 * @return array
		 */
		return (array) apply_filters(
			'shipper_import_tables_source_names',
			$tables
		);
	}

	/**
	 * Gets source prefix from migration data, or determined one
	 *
	 * Predetermined one takes precedence.
	 *
	 * @return string
	 */
	public function get_source_prefix() {
		$migration = new Shipper_Model_Stored_Migration;
		$source_prefix = $migration->get( 'source_prefix' );
		return ! empty( $source_prefix )
			? $source_prefix
			: $this->get_determined_source_prefix()
		;
	}

	/**
	 * Determine source prefix for the imported tables
	 *
	 * @return string
	 */
	public function get_determined_source_prefix() {
		$source_prefix = '';

		$tables = self::get_table_names_from_files();
		if ( empty( $tables ) ) { return $source_prefix; }

		$tbl_pfx = join( '|', $this->get_global_tables_list() );
		$prefixes = array();
		foreach ( $tables as $table ) {
			if ( ! preg_match( '/(' . $tbl_pfx . ')$/', $table ) ) { continue; }
			$pfx = preg_replace( '/(' . $tbl_pfx . ')$/', '', $table );
			if ( empty( $pfx ) ) { continue; }
			$prefixes[ $pfx ] = strlen( $pfx );
		}

		if ( empty( $prefixes ) ) {
			// We came up empty looking for prefix candidates.
			return $source_prefix;
		}
		// Shortest non-empty candidate is the proper candidate.
		$min_pfx_len = min( array_values( $prefixes ) );
		$source_prefix = array_search( $min_pfx_len, $prefixes );

		return $source_prefix;
	}

	/**
	 * Figures out local table name for a given source table name
	 *
	 * Useful when source and destination table prefixes are different.
	 *
	 * @param string $prefix Optional prefix to use.
	 *
	 * @return string Local table name
	 */
	public function get_destination_table( $prefix = '' ) {
		$source_table = $this->get_table_name();

		$source_prefix = $this->get_source_prefix();
		if ( empty( $source_prefix ) ) { return $source_table; }

		$destination_prefix = (
			! empty( $prefix ) ? $prefix . '_' : ''
		) . $this->get_dbh()->base_prefix;

		return preg_replace(
			'/^' . preg_quote( $source_prefix, '/' ) . '/',
			$destination_prefix,
			$source_table
		);
	}

	/**
	 * Preprocesses the import file
	 *
	 * First step in importing a table.
	 *
	 * @return bool
	 */
	public function preprocess_import_file() {
// @TODO implement this properly - deprecate method or implement switching #cleanup
return true; // Temporary - we try preprocessing each statement.
		$table = $this->get_table_name();

		// First up, let's pre-process the file.
		$decoder = new Shipper_Helper_Replacer_File( Shipper_Helper_Codec::DECODE );

		// We'll only be using the SQL query codec.
		$decoder->set_codec_list(array(
			Shipper_Helper_Codec_Sql::get_intermediate( Shipper_Task_Import::PREFIX )
		));

		$path = $this->get_file_path( $table );
		$tmp_path = $decoder->transform( $path );

		if ( file_exists( $tmp_path ) ) {
			rename( $tmp_path, $path );
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Preprocesses the import statement
	 *
	 * @param string $statement Statement to preprocess.
	 *
	 * @return string
	 */
	public function preprocess_import_statement( $statement ) {
		// First up, let's pre-process the file.
		$decoder = new Shipper_Helper_Replacer_String( Shipper_Helper_Codec::DECODE );

		// We'll only be using the SQL query codec.
		$decoder->set_codec_list(array(
			Shipper_Helper_Codec_Sql::get_intermediate( Shipper_Task_Import::PREFIX )
		));

		return $decoder->transform( $statement );
	}

	/**
	 * Actually imports statements from the file
	 *
	 * @param int $position Start importing statements at this position.
	 * @param int $limit Import this many statements.
	 *
	 * @return int|WP_Error Number of imported statements, or WP_Error instance
	 */
	public function import_statements( $position, $limit ) {
		$table = $this->get_table_name();

		$statements = $this->get_statements( $position, $limit );
		// Prepare row decoder for domain name expansion.
		$row_decoder = new Shipper_Helper_Replacer_Serialized(
			Shipper_Helper_Codec::DECODE,
			$this->get_destination_table()
		);
		$row_decoder->set_codec_list(array(
			new Shipper_Helper_Codec_Domain
		));
		foreach ( $statements as $idx => $statement ) {
			$status = $this->import_statement( $statement, $row_decoder );
			if ( is_wp_error( $status ) ) {
				// Something went wrong, let's log.
				$is_fatal = Shipper_Task_Import_Tables::ERR_RECOVERABLE !== $status->get_error_code();
				$msg = $is_fatal
					? __( 'Fatal error processing %1$s at position [%2$d][%3$d]: %4$s', 'shipper' )
					: __( 'Ignored issue processing %1$s at position [%2$d][%3$d]: %4$s', 'shipper' );
				Shipper_Helper_Log::write(sprintf($msg,
					$table, $position, $idx, $status->get_error_message()
				));
				if ( ! empty( $is_fatal ) ) {
					// Issue is serious - let's bail out from processing this table.
					return $status;
				}
				// Otherwise, we did encounter an issue but it wasn't serious enough to panic.
				// Let's just keep chugging along.
			}
		}

		return count( $statements );
	}

	/**
	 * Actually imports a single statement.
	 *
	 * @param string $statement SQL string to import.
	 * @param object $replacer Shipper_Helper_Replacer_Serialized instance.
	 *
	 * @return bool|WP_Error true on success, WP_Error with what went wrong otherwise.
	 */
	public function import_statement( $statement, $replacer ) {
		$statement = $this->preprocess_import_statement( $statement );
		$row_id = $this->import_statement_insert( $statement );
		if ( is_wp_error( $row_id ) ) {
			return $row_id;
		}
		if ( empty( $row_id ) ) {
			// CREATE statement, nothing to postprocess.
			return true;
		}
		/*
		if ( preg_match( '/(_site)_transient/', $statement ) ) { return true; }
		if ( preg_match( '/wdp_un_updates_(data|available)/', $statement ) ) { return true; }
		if ( preg_match( '/wdp_un_profile_data/', $statement ) ) { return true; }
		*/

		return $this->import_statement_postprocess( $row_id, $replacer );
	}

	/**
	 * Inserts an import statement
	 *
	 * @param string $statement Statement to import.
	 *
	 * @return bool|WP_Error
	 */
	public function import_statement_insert( $statement ) {
		// Force the insert ID to empty value.
		$this->get_dbh()->insert_id = false;

		if ( (bool) preg_match( '/^create table/i', $statement ) ) {
			$this->import_statement_create( $statement );
			// No row ID on table creation.
			return false;
		}

		// 1) Do SQL query
		$result = $this->query_ignore( $statement );
		if ( false === $result ) {
			// Break early in the process.
			return new WP_Error(
				Shipper_Task_Import_Tables::ERR_RECOVERABLE,
				sprintf(
					__( 'Error in statement %1$s: The database said: %2$s', 'shipper' ),
					$statement, $this->get_error()
				)
			);
		}
		// 2) Obtain insert ID, if any.
		$row_id = $this->get_dbh()->insert_id;

		return $row_id;
	}

	/**
	 * Special-case handling for CREATE statements
	 *
	 * Tables can have dependencies as foreign keys, which is why
	 * we're processing them as special case.
	 *
	 * @param string $statement SQL CREATE statement.
	 *
	 * @return bool|WP_Error
	 */
	public function import_statement_create( $statement ) {
		$statement = $this->prepare_create_statement( $statement );

		$this->get_dbh()->query( 'SET foreign_key_checks = 0' );
		$result = $this->get_dbh()->query( $statement );
		if ( false === $result ) {
			// Failed on create statement, this could be down to FK checks.
			$has_source = $this->get_dbh()->get_var(
				$this->get_dbh()->prepare( 'SHOW TABLES LIKE %s', $this->get_destination_table() )
			);
			if ( ! empty( $has_source ) ) {
				Shipper_Helper_Log::write( sprintf(
					'Table creation issue with %s - attempting to drop the original %s first',
					$statement, $this->get_destination_table()
				) );
				$this->get_dbh()->query(
					'DROP TABLE ' . $this->get_destination_table()
				);
				// Re-try this.
				$result = $this->get_dbh()->query( $statement );
			}
		}
		$this->get_dbh()->query( 'SET foreign_key_checks = 1' );
		if ( false === $result ) {
			$msg = sprintf(
				__( 'Error in statement %1$s: The database said: %2$s', 'shipper' ),
				$statement, $this->get_error()
			);
			Shipper_Helper_Log::write( $msg );
			// Break early in the process.
			return new WP_Error( Shipper_Task_Import_Tables::ERR_BREAKING, $msg );
		}
		return $result;
	}

	/**
	 * Pre-processes CREATE statement for dependencies
	 *
	 * @param string $statement SQL CREATE statement.
	 *
	 * @return string
	 */
	public function prepare_create_statement( $statement ) {
		// We want the _original_ create statement.
		// We will yank out the prefixless table name from that.
		$old_create = $this->get_statements( 1, 1 );
		$old_create = ! empty( $old_create[0] ) ? $old_create[0] : '';
		preg_match_all( '/\{\{SHIPPER_TABLE_PREFIX\}\}(\S+)/', $old_create, $tn );
		$prefixless_table_name = ! empty( $tn[1][0] )
			? $tn[1][0]
			: ''
		;

		if ( ! empty( $prefixless_table_name ) ) {
			$table = $this->get_table_name();

			// Now we check for table dependencies.
			$prefix = preg_replace(
				'/' . preg_quote( $prefixless_table_name, '/' ) . '/',
				'',
				$table
			);
			$statements = $this->get_statements( 1, 1 );
			$tables = Shipper_Model_Database_Table_Import::get_table_names_from_files();
			$matcher = '(' . join( '|', $tables ) . ')';
			preg_match_all( "/{$matcher}/", $statements[0], $matches );
			if ( ! empty( $matches[1] ) ) {
				// We have some table dependencies listed in the create statement.
				// Let's process that.
				// @TODO also exclude current table from matcher!
				foreach ( $matches[1] as $mtch ) {
					$have_dest = $this->get_dbh()->get_var(
						$this->get_dbh()->prepare( 'SHOW TABLES LIKE %s', $mtch )
					);
					if ( ! empty( $have_dest ) ) {
						Shipper_Helper_Log::write(
							"Dependency table {$mtch} already exists, not processing."
						);
						continue;
					}

					Shipper_Helper_Log::write( sprintf(
						'Found table dependency for %s: %s. Processing.',
						$table, $mtch
					) );
					$mtbl = $this->get_dbh()->base_prefix . preg_replace(
						'/^' . preg_quote( $prefix, '/' ) . '/',
						'',
						$mtch
					);
					$statement = preg_replace( '/' . $mtch . '/', $mtbl, $statement );
				}
			}
		}

		return $statement;
	}

	/**
	 * Postprocesses the imported statement
	 *
	 * @param int    $row_id Imported statement row ID.
	 * @param object $replacer Replacer instance.
	 *
	 * @return bool|WP_Error
	 */
	public function import_statement_postprocess( $row_id, $replacer ) {
		$table = $this->get_table_name();
		$local_table = $this->get_destination_table( Shipper_Task_Import::PREFIX );
		$primary_key = $this->get_primary_key( $local_table );
		if ( empty( $primary_key ) ) {
			// We won't continue post-processing this statement.
			return new WP_Error(
				Shipper_Task_Import_Tables::ERR_RECOVERABLE,
				sprintf(
					__( 'Unable to find primary key for %1$s (%2$s).', 'shipper' ),
					$table, $local_table
				)
			);
		}

		// SELECT that entire row.
		$row = $this->get_dbh()->get_row(
			$this->get_dbh()->prepare(
				"SELECT * FROM {$local_table} WHERE {$primary_key} = %d", $row_id
			),
			ARRAY_A
		);
		if ( empty( $row ) ) {
			// We won't continue post-processing this statement.
			return new WP_Error(
				Shipper_Task_Import_Tables::ERR_RECOVERABLE,
				sprintf(
					__( 'Error processing statement in %1$s - empty resultset for %2$d', 'shipper' ),
					$table, $row_id
				)
			);
		}

		$source_hash = md5( serialize( $row ) );

		// Run serialized replacer on raw source row data.
		$dest_row = array();
		foreach ( $row as $index => $value ) {
			$dest_row[ $index ] = $replacer->set_key( $index )->transform( $value );
		}

		/**
		 * The row values to be updated in the destination table.
		 *
		 * @since v1.0.0-beta.4
		 *
		 * @param array $dest_row Processed row values.
		 * @param array $row Source (unprocessed) row.
		 * @param string $table Table that the row belongs to.
		 *
		 * @return array Row values to be used.
		 */
		$dest_row = (array) apply_filters(
			'shipper_import_table_row_values',
			$dest_row,
			$row,
			$table
		);

		// If hashes differ, UPDATE the row.
		if ( md5( serialize( $dest_row ) ) !== $source_hash ) {
			$result = $this->get_dbh()->update($local_table, $dest_row, array(
				$primary_key => $row_id,
			));
			if ( false === $result ) {
				// We won't continue post-processing this statement.
				return new WP_Error(
					Shipper_Task_Import_Tables::ERR_RECOVERABLE,
					sprintf(
						__( 'Error updating row %1$d - %2$s', 'shipper' ),
						$row_id, $this->get_error()
					)
				);
			}
		}

		return true;
	}

	/**
	 * Finalizes table import
	 *
	 * Moves table to the required position.
	 *
	 * @return bool
	 */
	public function import_table_finish() {
		$src_table = $this->get_destination_table( Shipper_Task_Import::PREFIX );
		$dest_table = apply_filters(
			'shipper_destination_table_name',
			$this->get_destination_table()
		);

		$status = $this->rename_table( $src_table, $dest_table );
		if ( empty( $status ) ) {
			Shipper_Helper_Log::write( sprintf(
				'Error finalizing %s ( %s )',
				$src_table, $dest_table
			) );
		}

		return true;
	}
}
