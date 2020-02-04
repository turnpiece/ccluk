<?php
/**
 * Shipper export models: individual table export model
 *
 * Handles individual table export.
 *
 * @package shipper
 */

/**
 * Table export class
 */
class Shipper_Model_Database_Table_Export extends Shipper_Model_Database_Table {

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
	 * Gets destination SQL file name
	 *
	 * @return string
	 */
	public function get_file_path() {
		$name = $this->get_table_name();
		if ( empty( $name ) ) {
			return '';
		}

		return Shipper_Helper_Fs_Path::get_temp_dir() .
			preg_replace( '/[^-_a-z0-9]/i', '', $name )
			. '.sql'
		;
	}

	/**
	 * Gets export file header for a table
	 *
	 * A header contains some table info, export info and,
	 * most importantly, a table creation statement.
	 *
	 * Ends with Shipper export statement delimiter.
	 *
	 * @return string
	 */
	public function get_header() {
		$table = $this->get_table_name();
		$create = $this->get_dbh()->get_var( "SHOW CREATE TABLE {$table}", 1 );
		$create = $this->get_processed_create( $create, $table );
		$rows = $this->get_dbh()->get_var( "SELECT COUNT(*) FROM {$table}" );
		if ( empty( $create ) ) {
			return '';
		}

		// @codingStandardsIgnoreStart More readable broken into multiline concats.
		$result = '' .
			'# ' . sprintf( __( 'Shipper table export for %s', 'shipper' ), $table ) . "\n" .
			'# ' . join( '', array_fill( 0, 30, '-' ) ) . "\n" .
			'# ' . sprintf(
				__( 'Export started: on %1$s (%2$d)', 'shipper' ), date( 'r' ), time()
			) . "\n" .
			'# ' . sprintf( __( 'Total rows: %d', 'shipper' ), $rows ) . "\n" .
			"\n" .
			"DROP TABLE IF EXISTS {$table};" .
			$this->get_statement_delimiter() .
			$this->get_escaped_special_columns( $create ) .
			$this->get_statement_delimiter();
		// @codingStandardsIgnoreEnd

		return $result;
	}

	/**
	 * Finds and escapes specially named columns in create query
	 *
	 * @param string $create_statement Query to process.
	 *
	 * @return string Escaped query
	 */
	public function get_escaped_special_columns( $create_statement ) {
		$reserved_names = $this->get_reserved_words();
		$rpl_tpl = '__shipper_%s_reserved_name__';

		foreach ( $reserved_names as $rn ) {
			$rpl = sprintf( $rpl_tpl, md5( $rn ) );
			$create_statement = preg_replace(
				'/`' . preg_quote( $rn, '/' ) . '`/',
				$rpl,
				$create_statement
			);
		}

		$create_statement = str_replace( '`', '', $create_statement );

		foreach ( $reserved_names as $rn ) {
			$rpl = sprintf( $rpl_tpl, md5( $rn ) );
			$create_statement = preg_replace(
				'/' . preg_quote( $rpl, '/' ) . '/',
				"`{$rn}`",
				$create_statement
			);
		}

		return $create_statement;
	}

	/**
	 * Postprocesses SQL CREATE statement
	 *
	 * @param string $stmt SQL CREATE statement.
	 *
	 * @return string
	 */
	public function get_processed_create( $stmt ) {
		$table = $this->get_table_name();
		$postprocess = array(
			'charset',
			'collate',
		);
		foreach ( $postprocess as $part ) {
			$expected = apply_filters(
				'shipper_export_tables_create_' . $part,
				false,
				$table
			);
			if ( empty( $expected ) ) {
				// Passthrough value, keep what we have.
				continue;
			}

			$current = $this->get_table_create_value( $part, $stmt );
			if ( empty( $current ) ) {
				// Nothing to work with.
				continue;
			}
			Shipper_Helper_Log::debug(sprintf(
				__( 'Changing table %1$s %2$s from %3$s to %4$s', 'shipper' ),
				$table, $part, $current, $expected
			));
			if ( $current === $expected ) {
				// We already have what we should, continue.
				continue;
			}

			$stmt = $this->get_updated_table_create( $part, $current, $expected, $stmt );
		}

		return $stmt;
	}

	/**
	 * Gets SQL CREATE value part
	 *
	 * Part is a table meta value, such as charset or collate.
	 *
	 * @param string $part Part to extract.
	 * @param string $stmt SQL CREATE statement.
	 *
	 * @return string
	 */
	public function get_table_create_value( $part, $stmt ) {
		$rx = '\).*?\b' . preg_quote( $part, '/' ) . '=(\S+)\s';
		$value = '';
		// Add a space at the end of the statement so the regex matches.
		if ( preg_match( "/{$rx}/i", "{$stmt} ", $matches ) ) {
			$value = ! empty( $matches[1] )
				? $matches[1]
				: ''
			;
		}
		return $value;
	}

	/**
	 * Updates SQL CREATE statement field
	 *
	 * @param string $part Part to update.
	 * @param string $old Old value to replace.
	 * @param string $new New value to replace with.
	 * @param string $stmt SQL CREATE statemet.
	 *
	 * @return string
	 */
	public function get_updated_table_create( $part, $old, $new, $stmt ) {
		$rx1 = '\b' . preg_quote( $part, '/' ) . '=' . preg_quote( $old, '/' ) . '\b';
		$rpl1 = "{$part}={$new}";

		$rx2 = '\b' . preg_quote( $part, '/' ) . ' ' . preg_quote( $old, '/' ) . '\b';
		$rpl2 = "{$part} {$new}";

		$stmt = preg_replace( "/{$rx1}/i", $rpl1, $stmt );
		$stmt = preg_replace( "/{$rx2}/i", $rpl2, $stmt );

		return $stmt;
	}

	/**
	 * Gets columns type description for a table.
	 *
	 * @return array Table description, as column name => data type hash.
	 */
	public function get_description() {
		$table = $this->get_table_name();
		$description = array();

		$raw_description = $this->get_dbh()->get_results( "SHOW COLUMNS FROM {$table}", ARRAY_A );
		if ( empty( $raw_description ) ) {
			return $description;
		}

		foreach ( $raw_description as $d ) {
			$type = 'string';
			if ( preg_match( '/^(big|small|medium|tiny)?int/i', $d['Type'] ) ) {
				$type = 'int';
			} elseif ( preg_match( '/^(decimal|double|float)/i', $d['Type'] ) ) {
				$type = 'float';
			}
			$description[ $d['Field'] ] = $type;
		}

		return $description;
	}

	/**
	 * Gets row limit, in bytes
	 *
	 * Rows larger than this could be serialized. Processing them could take
	 * _a while_ and block request for a long time.
	 *
	 * By default, let's go with 8Kb.
	 *
	 * @return int Row size limit, in bytes
	 */
	public function get_bytes_limit() {
		$table = $this->get_table_name();
		$limit = preg_match( '/(posts|options|meta|agm_maps)$/', $table )
			? 8 * 1024
			: 16 * 1024
		;

		/**
		 * Row size limit, in bytes
		 *
		 * @param int $limit Size, in bytes.
		 * @param string $table Table name.
		 *
		 * @return int
		 */
		return (int) apply_filters(
			'shipper_export_tables_row_bytes',
			$limit,
			$table
		);
	}

	/**
	 * Gets a number of table rows as a list of SQL INSERTs
	 *
	 * Important: the number of returned statements has to be
	 * the same as the number of results. This will be used
	 * by the calling method to update migration pointer.
	 *
	 * @param int $position Position to start from.
	 * @param int $limit Maximum number of rows to process.
	 *
	 * @return array
	 */
	public function get_rows_sql( $position, $limit ) {
		$table = $this->get_table_name();
		$description = $this->get_description();
		$sqls = array();
		$overall = 0;
		$strlimit = $this->get_bytes_limit();

		$results = $this->run(
			"SELECT * FROM {$table} LIMIT {$position},{$limit}",
			self::QUERY_GET_RESULTS,
			ARRAY_A
		);
		if ( empty( $results ) && $this->has_error() ) {
			Shipper_Helper_Log::write(
				sprintf(
					__(
						'Unable to select results from %1$s:%2$d,%3$d - the database said: [%4$s]',
						'shipper'
					),
					$table, $position, $limit, $this->get_error()
				)
			);
			return $sqls;
		}

		// Process results...
		$replacer = new Shipper_Helper_Replacer_Serialized( Shipper_Helper_Codec::ENCODE, $table );
		$full_string_rpl = new Shipper_Helper_Replacer_String( Shipper_Helper_Codec::ENCODE );
		$sql_only_string_rpl = new Shipper_Helper_Replacer_String( Shipper_Helper_Codec::ENCODE );
		$sql_only_string_rpl->set_codec_list( array(
			new Shipper_Helper_Codec_Sql
		) );
		$skip_emails = $this->get_options_model()
			->get( Shipper_Model_Stored_Options::KEY_SKIPEMAILS );
		foreach ( $results as $src ) {
			if ( ! $this->is_migrated_row( $src ) ) {
				$sqls[] = $this->get_skipped_row_delimiter( 'skipped row' );
				continue;
			}

			// This is where we check the row size.
			// This is just against raw data, before we actually process it.
			$overall += strlen( join( '', $src ) );
			if ( $overall > $strlimit && count( $sqls ) ) {
				break;
			}

			// Serialized replacer is not a passthrough by default.
			$replacer->set_passthrough( false );

			$string_rpl = $full_string_rpl;
			$is_full_replacement_table_row = $this->is_full_replacement_table_row( $src, $table );
			if ( ! $is_full_replacement_table_row ) {
				$string_rpl = $sql_only_string_rpl;

				if ( $skip_emails ) {
					// If there's stuff we need to keep, set passthrough mode.
					// This affects serialized replacer, which will leave entry verbatim.
					$replacer->set_passthrough( true );
				}
			}

			$sqls[] = $string_rpl->transform(
				$this->get_row_sql( $src, $description, $replacer )
			);
		}

		return $sqls;
	}

	/**
	 * Check whether to perform full replacement on a table row
	 *
	 * We can either do full replacement, which will go into the values
	 * and replace everything, then do overall string replacement again.
	 * Alternatively, we can do short replacement, which will just do
	 * the minimum SQL-related transformations.
	 *
	 * @param array  $src Source row.
	 * @param string $table Table name.
	 *
	 * @return bool
	 */
	public function is_full_replacement_table_row( $src, $table ) {
		if ( $this->is_users_table_row( $src, $table ) ) {
			return false;
		}

		if ( $this->is_options_table_row( $src, $table ) ) {
			if ( 'admin_email' !== $this->get_table_row_name( $src, $table ) ) {
				return true;
			}

			return ! $this->get_options_model()
				->get( Shipper_Model_Stored_Options::KEY_SKIPEMAILS );
		}

		return true;
	}

	/**
	 * Gets individual row SQL from results
	 *
	 * @param array  $raw Raw row hash.
	 * @param array  $table_description Table fields description list.
	 * @param object $replacer Shipper_Helper_Replacer_Serialized instance.
	 *
	 * @return string
	 */
	public function get_row_sql( $raw, $table_description, $replacer ) {
		$table = $this->get_table_name();
		$keys = array();
		$values = array();
		$placeholders = array();

		$raw = $this->get_preprocessed_raw_row( $raw );

		$is_shipper_internal = false; // Shipper internal stuff detection flag.
		$shipper_rx = '/^' . preg_quote( Shipper_Helper_Storage::DEFAULT_NAMESPACE, '/' ) . '/';

		foreach ( $raw as $key => $value ) {
			if ( preg_match( $shipper_rx, $value ) ) {
				$is_shipper_internal = true; // Don't export shipper internals.
				break; // No need to further process this row.
			}
			$keys[] = in_array( $key, $this->get_reserved_words(), true )
				? "`{$key}`"
				: $key;

			$placeholder = '%s';
			if ( 'int' === $table_description[ $key ] ) {
				$placeholder = '%d';
				$value = (int) $value;
			} elseif ( 'float' === $table_description[ $key ] ) {
				$placeholder = '%f';
				$value = (float) $value;
			} else {
				$placeholder = '%s';
				$value = $replacer->set_key( $key )->transform( "{$value}" );
			}

			$placeholders[] = $placeholder;
			$values[] = $value;
		}

		$sql = '';
		if ( $is_shipper_internal ) {
			$sql = $this->get_skipped_row_delimiter( 'shipper internal' );
		} else {
			$key_placeholders = join( ', ', $keys );
			$value_placeholders = join( ', ', $placeholders );
			$sql = '' .
				"INSERT INTO {$table} ({$key_placeholders})" .
				' ' .
				$this->get_dbh()->prepare( "VALUES ({$value_placeholders})", $values ) .
				';' .
				$this->get_statement_delimiter();
		}

		// Remove any placeholder escapes added by the `prepare` call.
		// This is because they won't be properly re-expanded on destination.
		return $this->get_dbh()->remove_placeholder_escape( $sql );
	}

	/**
	 * Preprocesses raw row hash before prepping it for export
	 *
	 * Targets setups where home/site URLs are different in DB and defined.
	 *
	 * @param array  $raw Raw row hash.
	 * @param object $constants Shipper_Model_Constants instance with overrides, used in tests.
	 *
	 * @return array
	 */
	public function get_preprocessed_raw_row( $raw, $constants = false ) {
		$table = $this->get_table_name();
		if ( ! is_object( $constants ) ) {
			$constants = new Shipper_Model_Constants_General;
		}
		if ( is_multisite() || ! $this->is_options_table_row( $raw, $table ) ) {
			// Only do this on single sites.
			// And only do this for options rows.
			return $raw;
		}

		$siteurl = $constants->get( 'WP_SITEURL' );
		if ( ! empty( $siteurl ) && 'siteurl' === $this->get_table_row_name( $raw, $table ) ) {
			$raw['option_value'] = $siteurl;
		}

		$home = $constants->get( 'WP_HOME' );
		if ( ! empty( $home ) && 'home' === $this->get_table_row_name( $raw, $table ) ) {
			$raw['option_value'] = $home;
		}

		return $raw;
	}

	/**
	 * Whether to include this row in migration
	 *
	 * @param array $raw Raw row hash.
	 *
	 * @return bool
	 */
	public function is_migrated_row( $raw ) {
		$table = $this->get_table_name();
		$include = true;

		$name = $this->get_table_row_name( $raw, $table );
		if ( $name && preg_match( '/^(_site)?_transient/', $name ) ) {
			/**
			 * Whether to include transients in migration
			 *
			 * @param bool   $include Whether to include transients in export.
			 * @param array  $raw Raw row hash.
			 * @param string $table Table name.
			 *
			 * @return bool
			 */
			$include = (bool) apply_filters(
				'shipper_export_table_include_transients',
				true,
				$raw,
				$table
			);
		}

		/**
		 * Whether to include this row in migration
		 *
		 * @param bool   $include Whether to include the row in export.
		 * @param array  $raw Raw row hash.
		 * @param string $table Table name.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_export_table_include_row',
			$include,
			$raw,
			$table
		);
	}

	/**
	 * Gets options model instance
	 *
	 * @return object Shipper_Model_Stored_Options instance
	 */
	public function get_options_model() {
		if ( ! isset( $options ) ) {
			$options = new Shipper_Model_Stored_Options;
		}
		return $options;
	}
}