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

	const GET_PDO    = true;
	const LINE_LIMIT = 10000;
	const MAX_LINE   = 'max_line';
	const TOTAL_LINE = 'total_line';
	const POINTER    = 'pointer';
	const SQL_PATH   = 'sqls/dump.sql';

	/**
	 * String replacer
	 *
	 * @var Shipper_Helper_Replacer_String
	 */
	private static $replacer;

	/**
	 * Holds name of the table currently being processed
	 *
	 * @var string
	 */
	private $current_table;

	/**
	 * Holds storage instance
	 *
	 * @var object Shipper_Model_Stored_Tablelist instance
	 */
	private $storage;

	/**
	 * Gets currently processed table
	 *
	 * @return string
	 */
	public function get_current_table() {
		return isset( $this->current_table )
			? $this->current_table
			: '';
	}

	/**
	 * Gets a storage instance
	 *
	 * Spawns one if necessary
	 *
	 * @return object Shipper_Model_Stored_Tablelist instance
	 */
	public function get_storage() {
		if ( ! isset( $this->storage ) ) {
			$this->storage = new Shipper_Model_Stored_Tablelist();
		}

		return $this->storage;
	}


	/**
	 * Get total steps.
	 *
	 * @return int|void
	 */
	public function get_total_steps() {
		return $this->get_storage()->get( self::TOTAL_LINE, 0 );
	}

	/**
	 * Get current step.
	 *
	 * @return int|void
	 */
	public function get_current_step() {
		$processed = $this->get_storage()->get(
			Shipper_Model_Stored_Tablelist::KEY_PROCESSED_TABLES,
			array()
		);

		return count( $processed );
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
		$this->has_done_anything = true;
		$db                      = new Shipper_Model_Database();
		$dbh                     = $db->get_dbh( self::GET_PDO );
		$query_string            = '';
		$replacer                = $this->get_replacer();
		$serialize_replacer      = new Shipper_Helper_Replacer_Serialize();
		$json_replacer           = new Shipper_Helper_Replacer_JSON();
		$reader                  = Shipper_Helper_Fs_File::open( $this->get_sql_file() );
		$storage                 = $this->get_storage();
		$total_line              = $storage->get( self::TOTAL_LINE, false );

		if ( false === $total_line ) {
			$reader->seek( PHP_INT_MAX );
			$total_line = $reader->key();
			$storage->set( self::TOTAL_LINE, $total_line );
			$storage->save();
			$reader->rewind();
		}

		$limit    = self::LINE_LIMIT;
		$max_line = $storage->get( self::MAX_LINE, $limit );
		$pointer  = $storage->get( self::POINTER, 0 );

		$reader->seek( $pointer );
		$is_done = true;

		while ( ! $reader->eof() ) {
			$line = $reader->current();
			$reader->next();

			$left_trimmed_line = ltrim( $line );
			$temp_query_string = trim( $query_string );

			if ( 1 === preg_match( '/^#|^--/', $left_trimmed_line ) && empty( $temp_query_string ) ) {
				continue; // skip one-line comments.
			}

			if ( preg_match( '/^\/\*!/m', $left_trimmed_line ) ) {
				continue;
			}

			$decoded_serialized = $serialize_replacer->transform( $left_trimmed_line );
			$decoded_string     = $json_replacer->transform( $replacer->transform( $decoded_serialized ) );
			$query_string      .= $decoded_string . PHP_EOL; // append the line to the current query.
			$trimmed_line       = rtrim( $decoded_string );

			if ( 1 !== preg_match( '/;$/', $trimmed_line ) ) {
				continue; // skip incomplete statement.
			}

			$query_string = trim( $query_string );
			$query_string = $this->maybe_randomize_constraint_sql( $query_string );
			$this->log_tables( $query_string );

			try {
				$dbh->exec( "SET SQL_MODE='ALLOW_INVALID_DATES'" );
				$dbh->exec( 'SET foreign_key_checks = 0' );
				$dbh->exec( 'SET autocommit = 0' );
				$dbh->exec( $query_string );
				$dbh->exec( 'SET autocommit = 1' );
				$dbh->exec( 'SET foreign_key_checks = 1' );
				$db->close();
				$this->hold_on( false );
			} catch ( Exception $e ) {
				Shipper_Helper_Log::debug( $e->getMessage() );
			}

			$line_number  = $reader->key();
			$query_string = '';

			if ( $line_number >= $max_line ) {
				$total_line = $storage->get( self::TOTAL_LINE );
				$storage->set( self::POINTER, $line_number );
				$storage->set( self::MAX_LINE, $max_line + $limit );
				$storage->save();

				$percentage = floor( ( $line_number / $total_line ) * 100 );
				$is_done    = $percentage >= 100;
				$this->hold_on();

				return $is_done;
			}
		}

		return $is_done;
	}

	/**
	 * Lets allow CPU to take a breath.
	 *
	 * @param bool $seconds Whether to hold in seconds or microseconds.
	 */
	private function hold_on( $seconds = true ) {
		if ( $seconds ) {
			// @RIPS\Annotation\Ignore
			sleep( 2 );
		} else {
			// @RIPS\Annotation\Ignore
			usleep( 1000 );
		}
	}

	/**
	 * Gets source SQL file name
	 *
	 * @return string
	 */
	public function get_sql_file() {
		return trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) . self::SQL_PATH;
	}

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Importing table.', 'shipper' );
	}

	/**
	 * Check whether the sql string is foreign key constraint or not
	 *
	 * @since 1.2.2
	 *
	 * @param string $string sql string.
	 *
	 * @return false|int
	 */
	public function is_constraint( $string ) {
		return preg_match( '/constraint\s([`_a-z]+)\sFOREIGN\sKEY/mi', $string );
	}

	/**
	 * If it's a foreign key constraint sql, add some random string and make it unique
	 *
	 * @since 1.2.2
	 *
	 * @param string $string sql string.
	 *
	 * @return string|string[]|null
	 */
	public function maybe_randomize_constraint_sql( $string ) {
		if ( ! $this->is_constraint( $string ) ) {
			return $string;
		}

		return preg_replace(
			'/constraint\s([`_a-z])/mi',
			'constraint $1' . shipper_get_random_string( 5 ) . '$3',
			$string
		);
	}

	/**
	 * Get table name from sql string.
	 * There will have `;` after the table name. So filter that too.
	 *
	 * @since 1.2.4
	 *
	 * @param string $sql sql string.
	 *
	 * @return string
	 */
	private function get_table_name( $sql ) {
		$search = 'drop table if exists';
		$table  = explode( $search, strtolower( $sql ) );

		return ! empty( $table[1] ) ? trim( substr( $table[1], 0, -1 ) ) : '';
	}

	/**
	 * Log currently importing table name
	 *
	 * @since 1.2.4
	 *
	 * @param string $sql sql string.
	 *
	 * @return void
	 */
	private function log_tables( $sql ) {
		$table_name = $this->get_table_name( $sql );

		if ( $table_name ) {
			/* translators: %s: table name */
			Shipper_Helper_Log::write( sprintf( __( 'Trying to import %s', 'shipper' ), $table_name ) );
		}
	}

	/**
	 * Get replacer instance
	 *
	 * @since 1.2.4
	 *
	 * @return Shipper_Helper_Replacer_String
	 */
	private function get_replacer() {
		if ( ! empty( self::$replacer ) ) {
			return self::$replacer;
		}

		self::$replacer = new Shipper_Helper_Replacer_String( Shipper_Helper_Codec::DECODE );
		self::$replacer->set_codec_list(
			array(
				new Shipper_Helper_Codec_Define(),
				new Shipper_Helper_Codec_Var(),
				new Shipper_Helper_Codec_Domain(),
				new Shipper_Helper_Codec_Preoptionname(),
			)
		);

		return self::$replacer;
	}
}