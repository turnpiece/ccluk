<?php
/**
 * Shipper model abstractions: database model class
 *
 * @package shipper
 */

/**
 * Database model abstraction
 */
class Shipper_Model_Database {

	const QUERY_GET_COL = 'get_col';
	const QUERY_GET_RESULTS = 'get_results';
	const QUERY_GET_VAR = 'get_var';

	const MAX_SQL_TABLE_NAME_LENGTH = 64;

	/**
	 * WPDB reference
	 *
	 * @var object
	 */
	private $_wpdb;

	/**
	 * Cached primary key column names
	 *
	 * @var array
	 */
	private $_primary_keys = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->_wpdb = $wpdb;
	}

	/**
	 * Gets database handler
	 *
	 * @return object
	 */
	public function get_dbh() {
		return $this->_wpdb;
	}

	/**
	 * Gets reserved SQL names list
	 *
	 * @return array
	 */
	public function get_reserved_words() {
		$reserved_names = array( 'key', 'group', 'show', 'order' );
		return $reserved_names;
	}

	/**
	 * Gets a list of all DB tables
	 *
	 * @return array
	 */
	public function get_tables_list() {
		$query = 'SHOW TABLES FROM `' . DB_NAME . '`';
		$tables = $this->run(
			$query,
			self::QUERY_GET_COL
		);
		return (array) $tables;
	}

	/**
	 * Returns global tables list
	 *
	 * Tables are without prefixes.
	 *
	 * @return array
	 */
	public function get_global_tables_list() {
		$tables = $this->get_dbh()->global_tables;
		if ( ! empty( $this->get_dbh()->ms_global_tables ) ) {
			$tables = array_merge(
				$tables,
				$this->get_dbh()->ms_global_tables
			);
		}

		return $tables;
	}

	/**
	 * Clears the last error
	 */
	public function clear_error() {
		$this->get_dbh()->last_error = '';
	}

	/**
	 * Checks if we have a DB error
	 *
	 * @return bool
	 */
	public function has_error() {
		return ! empty(
			$this->get_error()
		);
	}

	/**
	 * Gets current DB error
	 *
	 * @return string
	 */
	public function get_error() {
		return (string) $this->get_dbh()->last_error;
	}

	/**
	 * Runs the query and returns result
	 *
	 * @param string $query SQL query to run.
	 * @param string $callback WPDB method to use.
	 * @param mixed  $param Additional parameter to use.
	 *
	 * @return mixed Query result
	 */
	public function run( $query, $callback = false, $param = false ) {
		if ( empty( $callback ) ) {
			$callback = self::QUERY_GET_RESULTS;
		}
		$args = array( $query );

		if ( false !== $param ) {
			$args[] = $param;
		}

		$this->clear_error();
		return call_user_func_array(
			array( $this->get_dbh(), $callback ),
			$args
		);
	}

	/**
	 * Runs query with ignored foreign key constraints
	 *
	 * @param string $statement Prepared SQL statement to run.
	 *
	 * @return mixed
	 */
	public function query_ignore( $statement ) {
		$this->get_dbh()->query( 'SET foreign_key_checks = 0' );
		$result = $this->get_dbh()->query( $statement );
		$error = $this->get_error();
		$this->get_dbh()->query( 'SET foreign_key_checks = 1' );

		$this->get_dbh()->last_error = $error;

		return $result;
	}

	/**
	 * Gets table primary key column name
	 *
	 * Discovered keys are cached for subsequent use.
	 *
	 * @param string $table Table to get the primary key for.
	 *
	 * @return string|bool Primary key, or false on failure
	 */
	public function get_primary_key( $table ) {
		if ( ! empty( $this->_primary_keys[ $table ] ) ) { return $this->_primary_keys[ $table ]; }

		$keys = $this->get_dbh()->get_row(
			"SHOW KEYS FROM {$table} WHERE key_name = 'PRIMARY' or key_name = 'ID'",
			ARRAY_A
		);
		$primary_key = ! empty( $keys['Column_name'] )
			? $keys['Column_name']
			: false
		;

		if ( ! empty( $primary_key ) ) {
			$this->_primary_keys[ $table ] = $primary_key;
		}

		return $primary_key;
	}

	/**
	 * Gets total row count for multiple tables
	 *
	 * @param array $tables Tables to process.
	 *
	 * @return array Array of total rows, by table
	 */
	public function get_tables_rows_count( $tables ) {
		$sqls = array();
		foreach ( $tables as $table ) {
			$sqls[] = "SELECT '{$table}', COUNT(*) AS cnt FROM {$table}";
		}
		if ( empty( $sqls ) ) { return array(); }

		$raw = (array) $this->run(
			join( ' UNION ALL ', $sqls ),
			self::QUERY_GET_RESULTS,
			ARRAY_A
		);

		if ( count( $tables ) !== count( $raw ) ) {
			// Wow, something went terribly wrong here.
			return array();
		}

		return array_combine(
			$tables,
			wp_list_pluck( $raw, 'cnt' )
		);
	}

	/**
	 * Gets total rows count for a table
	 *
	 * @param string $table Table name to count.
	 *
	 * @return int
	 */
	public function get_table_rows_count( $table ) {
		return (int) $this->run(
			"SELECT COUNT(*) FROM {$table}",
			self::QUERY_GET_VAR
		);
	}

	/**
	 * Gets a temporary table name, used in renaming
	 *
	 * @param string $src Source table name.
	 *
	 * @return string
	 */
	public function get_temporary_table_name( $src ) {
		$table_name_length = strlen( $src ) + 5;
		$max_base = $table_name_length >= self::MAX_SQL_TABLE_NAME_LENGTH
			? self::MAX_SQL_TABLE_NAME_LENGTH
			: self::MAX_SQL_TABLE_NAME_LENGTH - ( strlen( $src ) + 5 );
		$max = $max_base > ( PHP_INT_MAX / 1000 )
			? PHP_INT_MAX
			: $max_base * 1000
		;
		if ( $table_name_length >= self::MAX_SQL_TABLE_NAME_LENGTH ) {
			// Ensure table name uniqueness for very long tables.
			$maxlen = strlen( $max ) + 5;
			$src = substr( $src, 0, self::MAX_SQL_TABLE_NAME_LENGTH - $maxlen );
		}
		return "{$src}_tmp_" . rand(0, $max);
	}

	/**
	 * Checks whether we're dealing with an options row raw data
	 *
	 * @param array $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return bool
	 */
	public function is_options_table_row( $raw, $table ) {
		return shipper_array_keys_exist( array(
			'option_id',
			'option_name',
			'option_value',
			'autoload',
		), $raw ) && strrpos( $table, 'options' );
	}

	/**
	 * Checks whether we're dealing with an options row raw data
	 *
	 * @param array $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return bool
	 */
	public function is_sitemeta_table_row( $raw, $table ) {
		return shipper_array_keys_exist( array(
			'meta_id',
			'site_id',
			'meta_key',
			'meta_value',
		), $raw ) && strrpos( $table, 'sitemeta' );
	}

	/**
	 * Checks whether we're dealing with an users row raw data
	 *
	 * @param array $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return bool
	 */
	public function is_users_table_row( $raw, $table ) {
		return shipper_array_keys_exist( array(
			'user_login',
			'user_pass',
			'user_nicename',
			'user_email',
			'user_activation_key',
			'user_status',
			'display_name',
		), $raw ) && strrpos( $table, 'users' );
	}

	/**
	 * Gets row name - option_name for options table, meta_key for sitemeta.
	 *
	 * @param array $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return string
	 */
	public function get_table_row_name( $raw, $table ) {
		if ( $this->is_options_table_row( $raw, $table ) ) {
			return $raw['option_name'];
		}
		if ( $this->is_sitemeta_table_row( $raw, $table ) ) {
			return $raw['meta_key'];
		}
		return '';
	}

	/**
	 * Renames a table
	 *
	 * @param string $src_table Source table name.
	 * @param string $dest_table New table name.
	 *
	 * @return bool
	 */
	public function rename_table( $src_table, $dest_table ) {
		$have_dest = $this->get_dbh()->get_var(
			$this->get_dbh()->prepare( 'SHOW TABLES LIKE %s', $dest_table )
		);

		$dest_tmp_table = false;
		if ( ! empty( $have_dest ) ) {
			$dest_tmp_table = $this->get_temporary_table_name( $dest_table );
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Destination table %s exists, moving it to %s first', 'shipper' ),
					$dest_table, $dest_tmp_table
				)
			);
			$status = $this->get_dbh()->query(
				"RENAME TABLE {$dest_table} TO {$dest_tmp_table}"
			);
			if ( false === $status ) {
				Shipper_Helper_Log::write(
					sprintf(
						"Error moving destination table to temporary destination: %s",
						$this->get_error()
					)
				);
				return false;
			}
		}

		// Now we can move source table.
		Shipper_Helper_Log::write(
			sprintf(
				__( 'Move source table %1$s to destination: %2$s', 'shipper' ),
				$src_table, $dest_table
			)
		);
		$status = $this->get_dbh()->query(
			"RENAME TABLE {$src_table} TO {$dest_table}"
		);
		if ( false === $status ) {
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Error renaming table %1$s to %2$s. The DB said: %3$s', 'shipper' ),
					$src_table, $dest_table, $this->get_error()
				)
			);
			return false;
		}

		if ( ! empty( $dest_tmp_table ) ) {
			// We still need to clean up!
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Clean up destination table: %s', 'shipper' ),
					$dest_tmp_table
				)
			);
			$status = $this->query_ignore( "DROP TABLE {$dest_tmp_table}" );
			if ( false === $status ) {
				Shipper_Helper_Log::write(
					sprintf(
						__( 'Error removing intermediate backup table %1$s: %2$s', 'shipper' ),
						$dest_tmp_table, $this->get_error()
					)
				);
			}
		}

		// This is so cached stuff doesn't cause stuckages.
		// That can happen on e.g. WPEngine when importing a non-WPE site.
		// Particularly, with users table.
		shipper_flush_cache();

		return true;
	}
}
