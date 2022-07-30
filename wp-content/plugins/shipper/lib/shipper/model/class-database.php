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

	const QUERY_GET_COL     = 'get_col';
	const QUERY_GET_RESULTS = 'get_results';
	const QUERY_GET_VAR     = 'get_var';

	const MAX_SQL_TABLE_NAME_LENGTH = 64;

	/**
	 * Database connection reference
	 *
	 * @var object
	 */
	private static $dbh;

	/**
	 * DSN holder
	 *
	 * @var string
	 */
	private $dsn;

	/**
	 * Username holder
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password holder
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Cached primary key column names
	 *
	 * @var array
	 */
	private $primary_keys = array();

	/**
	 * Shipper_Model_Database constructor.
	 *
	 * @param null $host host name.
	 * @param null $db_name db_name.
	 * @param null $user username.
	 * @param null $password password.
	 * @param null $port db port.
	 */
	public function __construct( $host = null, $db_name = null, $user = null, $password = null, $port = null ) {
		$this->set_credentials();
		$this->set_dbh();
	}

	/**
	 * Get port from wp-config.php; If not found, get mysql default port.
	 *
	 * @since 1.2.2
	 *
	 * @return int|mixed|string
	 */
	private function get_port() {
		$default_port = (int) ini_get( 'mysqli.default_port' );

		if ( empty( $default_port ) ) {
			$default_port = 3306;
		}

		$port = explode( ':', DB_HOST );
		$port = ! empty( $port[1] ) ? $port[1] : $default_port;

		return $port;
	}

	/**
	 * Set DB credentials
	 *
	 * @param null $host host name.
	 * @param null $db_name db name.
	 * @param null $user db username.
	 * @param null $password db password.
	 * @param null $port db port.
	 *
	 * @return void
	 */
	public function set_credentials( $host = null, $db_name = null, $user = null, $password = null, $port = null ) {
		if ( ! $host ) {
			$host = DB_HOST;
		}

		if ( ! $db_name ) {
			$db_name = DB_NAME;
		}

		if ( ! $user ) {
			$user = DB_USER;
		}

		if ( ! $password ) {
			// @RIPS\Annotation\Ignore
			$password = DB_PASSWORD;
		}

		if ( ! $port ) {
			$port = $this->get_port();
		}

		$this->dsn      = 'mysql:host=' . $host . ';dbname=' . $db_name . ';port=' . $port;
		$this->username = $user;
		$this->password = $password;
	}

	/**
	 * Set Database connection
	 *
	 * @return void
	 */
	private function set_dbh() {
		if ( ! self::$dbh ) {
			try {
				// phpcs:disable
				self::$dbh = new PDO( $this->dsn, $this->username, $this->password );
				self::$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				// phpcs:enable
			} catch ( Exception $e ) {
				Shipper_Helper_Log::debug( __( 'Unable to connect to database.', 'shipper' ) );
			}
		}
	}

	/**
	 * Close the connection
	 *
	 * @return void
	 */
	public function close() {
		self::$dbh = null;
	}

	/**
	 * Get database handler
	 *
	 * @param bool $pdo whether to use PDO or not.
	 *
	 * @return object|wpdb
	 */
	public function get_dbh( $pdo = false ) {
		if ( ! $pdo ) {
			global $wpdb;
			self::$dbh = $wpdb;
		}

		return self::$dbh;
	}

	/**
	 * Gets reserved SQL names list
	 *
	 * @return array
	 */
	public function get_reserved_words() {
		$reserved_names = array(
			'key',
			'group',
			'show',
			'order',
			'require',
			'virtual',
			'select',
			'delete',
			'bigint',
			'begin',
			'end',
			'interval',
			'count',
			'(',
			'_filename',
			'group',
			'accessible',
			'account',
			'action',
			'add',
			'after',
			'against',
			'aggregate',
			'algorithm',
			'all',
			'alter',
			'always',
			'analyse',
			'analyze',
			'and',
			'any',
			'as',
			'asc',
			'ascii',
			'asensitive',
			'at',
			'autoextend_size',
			'auto_increment',
			'avg',
			'avg_row_length',
			'backup',
			'before',
			'begin',
			'between',
			'bigint',
			'binary',
			'binlog',
			'bit',
			'blob',
			'block',
			'bool',
			'boolean',
			'both',
			'btree',
			'by',
			'byte',
			'cache',
			'call',
			'cascade',
			'cascaded',
			'case',
			'catalog_name',
			'chain',
			'change',
			'changed',
			'channel',
			'char',
			'character',
			'charset',
			'check',
			'checksum',
			'cipher',
			'class_origin',
			'client',
			'close',
			'coalesce',
			'code',
			'collate',
			'collation',
			'column',
			'columns',
			'column_format',
			'column_name',
			'comment',
			'commit',
			'committed',
			'compact',
			'completion',
			'compressed',
			'compression',
			'concurrent',
			'condition',
			'connection',
			'consistent',
			'constraint',
			'constraint_catalog',
			'constraint_name',
			'constraint_schema',
			'contains',
			'context',
			'continue',
			'convert',
			'cpu',
			'create',
			'cross',
			'cube',
			'current',
			'current_date',
			'current_time',
			'current_timestamp',
			'current_user',
			'cursor',
			'cursor_name',
			'data',
			'database',
			'databases',
			'datafile',
			'date',
			'datetime',
			'day',
			'day_hour',
			'day_microsecond',
			'day_minute',
			'day_second',
			'deallocate',
			'dec',
			'decimal',
			'declare',
			'default',
			'default_auth',
			'definer',
			'delayed',
			'delay_key_write',
			'delete',
			'desc',
			'describe',
			'des_key_file',
			'deterministic',
			'diagnostics',
			'directory',
			'disable',
			'discard',
			'disk',
			'distinct',
			'distinctrow',
			'div',
			'do',
			'double',
			'drop',
			'dual',
			'dumpfile',
			'duplicate',
			'dynamic',
			'each',
			'else',
			'elseif',
			'enable',
			'enclosed',
			'encryption',
			'end',
			'ends',
			'engine',
			'engines',
			'enum',
			'error',
			'errors',
			'escape',
			'escaped',
			'event',
			'events',
			'every',
			'exchange',
			'execute',
			'exists',
			'exit',
			'expansion',
			'expire',
			'explain',
			'export',
			'extended',
			'extent_size',
			'false',
			'fast',
			'faults',
			'fetch',
			'fields',
			'file',
			'file_block_size',
			'filter',
			'first',
			'fixed',
			'float',
			'float4',
			'float8',
			'flush',
			'follows',
			'for',
			'force',
			'foreign',
			'format',
			'found',
			'from',
			'full',
			'fulltext',
			'function',
			'general',
			'generated',
			'geometry',
			'geometrycollection',
			'get',
			'get_format',
			'global',
			'grant',
			'grants',
			'group',
			'group_replication',
			'handler',
			'hash',
			'having',
			'help',
			'high_priority',
			'host',
			'hosts',
			'hour',
			'hour_microsecond',
			'hour_minute',
			'hour_second',
			'identified',
			'if',
			'ignore',
			'ignore_server_ids',
			'import',
			'in',
			'index',
			'indexes',
			'infile',
			'initial_size',
			'inner',
			'inout',
			'insensitive',
			'insert',
			'insert_method',
			'install',
			'instance',
			'int',
			'int1',
			'int2',
			'int3',
			'int4',
			'int8',
			'integer',
			'interval',
			'into',
			'invoker',
			'io',
			'io_after_gtids',
			'io_before_gtids',
			'io_thread',
			'ipc',
			'is',
			'isolation',
			'issuer',
			'iterate',
			'join',
			'json',
			'key',
			'keys',
			'key_block_size',
			'kill',
			'language',
			'last',
			'leading',
			'leave',
			'leaves',
			'left',
			'less',
			'level',
			'like',
			'limit',
			'linear',
			'lines',
			'linestring',
			'list',
			'load',
			'local',
			'localtime',
			'localtimestamp',
			'lock',
			'locks',
			'logfile',
			'logs',
			'long',
			'longblob',
			'longtext',
			'loop',
			'low_priority',
			'master',
			'master_auto_position',
			'master_bind',
			'master_connect_retry',
			'master_delay',
			'master_heartbeat_period',
			'master_host',
			'master_log_file',
			'master_log_pos',
			'master_password',
			'master_port',
			'master_retry_count',
			'master_server_id',
			'master_ssl',
			'master_ssl_ca',
			'master_ssl_capath',
			'master_ssl_cert',
			'master_ssl_cipher',
			'master_ssl_crl',
			'master_ssl_crlpath',
			'master_ssl_key',
			'master_ssl_verify_server_cert',
			'master_tls_version',
			'master_user',
			'match',
			'maxvalue',
			'max_connections_per_hour',
			'max_queries_per_hour',
			'max_rows',
			'max_size',
			'max_statement_time',
			'max_updates_per_hour',
			'max_user_connections',
			'medium',
			'mediumblob',
			'mediumint',
			'mediumtext',
			'memory',
			'merge',
			'message_text',
			'microsecond',
			'middleint',
			'migrate',
			'minute',
			'minute_microsecond',
			'minute_second',
			'min_rows',
			'mod',
			'mode',
			'modifies',
			'modify',
			'month',
			'multilinestring',
			'multipoint',
			'multipolygon',
			'mutex',
			'mysql_errno',
			'name',
			'names',
			'national',
			'natural',
			'nchar',
			'ndb',
			'ndbcluster',
			'never',
			'new',
			'next',
			'no',
			'nodegroup',
			'nonblocking',
			'none',
			'not',
			'no_wait',
			'no_write_to_binlog',
			'null',
			'number',
			'numeric',
			'nvarchar',
			'offset',
			'old_password',
			'on',
			'one',
			'only',
			'open',
			'optimize',
			'optimizer_costs',
			'option',
			'optionally',
			'options',
			'or',
			'order',
			'out',
			'outer',
			'outfile',
			'owner',
			'pack_keys',
			'page',
			'parser',
			'parse_gcol_expr',
			'partial',
			'partition',
			'partitioning',
			'partitions',
			'password',
			'phase',
			'plugin',
			'plugins',
			'plugin_dir',
			'point',
			'polygon',
			'port',
			'precedes',
			'precision',
			'prepare',
			'preserve',
			'prev',
			'primary',
			'privileges',
			'procedure',
			'processlist',
			'profile',
			'profiles',
			'proxy',
			'purge',
			'quarter',
			'query',
			'quick',
			'range',
			'read',
			'reads',
			'read_only',
			'read_write',
			'real',
			'rebuild',
			'recover',
			'redofile',
			'redo_buffer_size',
			'redundant',
			'references',
			'regexp',
			'relay',
			'relaylog',
			'relay_log_file',
			'relay_log_pos',
			'relay_thread',
			'release',
			'reload',
			'remove',
			'rename',
			'reorganize',
			'repair',
			'repeat',
			'repeatable',
			'replace',
			'replicate_do_db',
			'replicate_do_table',
			'replicate_ignore_db',
			'replicate_ignore_table',
			'replicate_rewrite_db',
			'replicate_wild_do_table',
			'replicate_wild_ignore_table',
			'replication',
			'require',
			'reset',
			'resignal',
			'restore',
			'restrict',
			'resume',
			'return',
			'returned_sqlstate',
			'returns',
			'reverse',
			'revoke',
			'right',
			'rlike',
			'rollback',
			'rollup',
			'rotate',
			'routine',
			'row',
			'rows',
			'row_count',
			'row_format',
			'rtree',
			'savepoint',
			'schedule',
			'schema',
			'schemas',
			'schema_name',
			'second',
			'second_microsecond',
			'security',
			'select',
			'sensitive',
			'separator',
			'serial',
			'serializable',
			'server',
			'session',
			'set',
			'share',
			'show',
			'shutdown',
			'signal',
			'signed',
			'simple',
			'slave',
			'slow',
			'smallint',
			'snapshot',
			'socket',
			'some',
			'soname',
			'sounds',
			'source',
			'spatial',
			'specific',
			'sql',
			'sqlexception',
			'sqlstate',
			'sqlwarning',
			'sql_after_gtids',
			'sql_after_mts_gaps',
			'sql_before_gtids',
			'sql_big_result',
			'sql_buffer_result',
			'sql_cache',
			'sql_calc_found_rows',
			'sql_no_cache',
			'sql_small_result',
			'sql_thread',
			'sql_tsi_day',
			'sql_tsi_hour',
			'sql_tsi_minute',
			'sql_tsi_month',
			'sql_tsi_quarter',
			'sql_tsi_second',
			'sql_tsi_week',
			'sql_tsi_year',
			'ssl',
			'stacked',
			'start',
			'starting',
			'starts',
			'stats_auto_recalc',
			'stats_persistent',
			'stats_sample_pages',
			'status',
			'stop',
			'storage',
			'stored',
			'straight_join',
			'string',
			'subclass_origin',
			'subject',
			'subpartition',
			'subpartitions',
			'super',
			'suspend',
			'swaps',
			'switches',
			'table',
			'tables',
			'tablespace',
			'table_checksum',
			'table_name',
			'temporary',
			'temptable',
			'terminated',
			'text',
			'than',
			'then',
			'time',
			'timestamp',
			'timestampadd',
			'timestampdiff',
			'tinyblob',
			'tinyint',
			'tinytext',
			'to',
			'trailing',
			'transaction',
			'trigger',
			'triggers',
			'true',
			'truncate',
			'type',
			'types',
			'uncommitted',
			'undefined',
			'undo',
			'undofile',
			'undo_buffer_size',
			'unicode',
			'uninstall',
			'union',
			'unique',
			'unknown',
			'unlock',
			'unsigned',
			'until',
			'update',
			'upgrade',
			'usage',
			'use',
			'user',
			'user_resources',
			'use_frm',
			'using',
			'utc_date',
			'utc_time',
			'utc_timestamp',
			'validation',
			'value',
			'values',
			'varbinary',
			'varchar',
			'varcharacter',
			'variables',
			'varying',
			'view',
			'virtual',
			'wait',
			'warnings',
			'week',
			'weight_string',
			'when',
			'where',
			'while',
			'with',
			'without',
			'work',
			'wrapper',
			'write',
			'x509',
			'xa',
			'xid',
			'xml',
			'xor',
			'year',
			'year_month',
			'zerofill',
			'account',
			'always',
			'channel',
			'compression',
			'encryption',
			'file_block_size',
			'filter',
			'follows',
			'generated',
			'group_replication',
			'instance',
			'json',
			'master_tls_version',
			'never',
			'optimizer_costs',
			'parse_gcol_expr',
			'precedes',
			'replicate_do_db',
			'replicate_do_table',
			'replicate_ignore_db',
			'replicate_ignore_table',
			'replicate_rewrite_db',
			'replicate_wild_do_table',
			'replicate_wild_ignore_table',
			'rotate',
			'stacked',
			'stored',
			'validation',
			'virtual',
			'without',
			'xid',
			'old_password',
		);

		return $reserved_names;
	}

	/**
	 * Gets a list of all DB tables
	 *
	 * @return array
	 */
	public function get_tables_list() {
		$query  = 'SHOW TABLES FROM `' . DB_NAME . '`';
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
		$error  = $this->get_error();
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
		if ( ! empty( $this->primary_keys[ $table ] ) ) {
			return $this->primary_keys[ $table ];
		}

		$keys        = $this->get_dbh()->get_row(
			"SHOW KEYS FROM {$table} WHERE key_name = 'PRIMARY' or key_name = 'ID'",
			ARRAY_A
		);
		$primary_key = ! empty( $keys['Column_name'] )
			? $keys['Column_name']
			: false;

		if ( ! empty( $primary_key ) ) {
			$this->primary_keys[ $table ] = $primary_key;
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
		if ( empty( $sqls ) ) {
			return array();
		}

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
		$max_base          = $table_name_length >= self::MAX_SQL_TABLE_NAME_LENGTH
			? self::MAX_SQL_TABLE_NAME_LENGTH
			: self::MAX_SQL_TABLE_NAME_LENGTH - ( strlen( $src ) + 5 );
		$max               = $max_base > ( PHP_INT_MAX / 1000 )
			? PHP_INT_MAX
			: $max_base * 1000;
		if ( $table_name_length >= self::MAX_SQL_TABLE_NAME_LENGTH ) {
			// Ensure table name uniqueness for very long tables.
			$maxlen = strlen( $max ) + 5;
			$src    = substr( $src, 0, self::MAX_SQL_TABLE_NAME_LENGTH - $maxlen );
		}

		return "{$src}_tmp_" . wp_rand( 0, $max );
	}

	/**
	 * Checks whether we're dealing with an options row raw data
	 *
	 * @param array  $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return bool
	 */
	public function is_options_table_row( $raw, $table ) {
		return shipper_array_keys_exist(
			array(
				'option_id',
				'option_name',
				'option_value',
				'autoload',
			),
			$raw
		) && strrpos( $table, 'options' );
	}

	/**
	 * Checks whether we're dealing with an options row raw data
	 *
	 * @param array  $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return bool
	 */
	public function is_sitemeta_table_row( $raw, $table ) {
		return shipper_array_keys_exist(
			array(
				'meta_id',
				'site_id',
				'meta_key',
				'meta_value',
			),
			$raw
		) && strrpos( $table, 'sitemeta' );
	}

	/**
	 * Checks whether we're dealing with an users row raw data
	 *
	 * @param array  $raw Raw row data array.
	 * @param string $table Raw row table name.
	 *
	 * @return bool
	 */
	public function is_users_table_row( $raw, $table ) {
		return shipper_array_keys_exist(
			array(
				'user_login',
				'user_pass',
				'user_nicename',
				'user_email',
				'user_activation_key',
				'user_status',
				'display_name',
			),
			$raw
		) && strrpos( $table, 'users' );
	}

	/**
	 * Gets row name - option_name for options table, meta_key for sitemeta.
	 *
	 * @param array  $raw Raw row data array.
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
				/* translators: %1$s %2$s: dest and temp dest table. */
					__( 'Destination table %1$s exists, moving it to %2$s first', 'shipper' ),
					$dest_table,
					$dest_tmp_table
				)
			);
			$status = $this->get_dbh()->query(
				"RENAME TABLE {$dest_table} TO {$dest_tmp_table}"
			);

			if ( false === $status ) {
				Shipper_Helper_Log::write(
					sprintf(
					/* translators: %s: error message. */
						'Error moving destination table to temporary destination: %s',
						$this->get_error()
					)
				);

				return false;
			}
		}

		// Now we can move source table.
		Shipper_Helper_Log::write(
			sprintf(
			/* translators: %1$s %2$s: source and dest table. */
				__( 'Move source table %1$s to destination: %2$s', 'shipper' ),
				$src_table,
				$dest_table
			)
		);
		$status = $this->get_dbh()->query(
			"RENAME TABLE {$src_table} TO {$dest_table}"
		);
		if ( false === $status ) {
			Shipper_Helper_Log::write(
				sprintf(
				/* translators: %1$s %2$s %3$s: source, dest table and error message. */
					__( 'Error renaming table %1$s to %2$s. The DB said: %3$s', 'shipper' ),
					$src_table,
					$dest_table,
					$this->get_error()
				)
			);

			return false;
		}

		if ( ! empty( $dest_tmp_table ) ) {
			// We still need to clean up!
			Shipper_Helper_Log::write(
				sprintf(
				/* translators: %s: dest table. */
					__( 'Clean up destination table: %s', 'shipper' ),
					$dest_tmp_table
				)
			);
			$status = $this->query_ignore( "DROP TABLE {$dest_tmp_table}" );
			if ( false === $status ) {
				Shipper_Helper_Log::write(
					sprintf(
					/* translators: %1$s %2$s: dest table and error message. */
						__( 'Error removing intermediate backup table %1$s: %2$s', 'shipper' ),
						$dest_tmp_table,
						$this->get_error()
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

	/**
	 * Get total rows count for all tables (excluding ignored tables)
	 *
	 * @since 1.2.4
	 *
	 * @return int
	 */
	public function get_total_rows() {
		$migration = new Shipper_Model_Stored_Migration();
		$model     = $migration->is_package_migration()
			? new Shipper_Model_Stored_PackageMeta()
			: new Shipper_Model_Stored_MigrationMeta();

		$excluded_tables = $model->get( $model::KEY_EXCLUSIONS_DB, array() );
		$all_tables      = $this->get_tables_list();
		$tables          = array_values( array_diff( $all_tables, array_values( $excluded_tables ) ) );

		$count = 0;
		foreach ( $tables as $table ) {
			$count += $this->get_table_rows_count( $table );
		}

		return $count;
	}
}