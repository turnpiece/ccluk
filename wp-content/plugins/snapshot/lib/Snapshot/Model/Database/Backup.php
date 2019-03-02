<?php // phpcs:ignore
/**
 * Class for creating Snapshot backups
 *
 * @since 2.5
 *
 * @package Snapshot
 * @subpackage Model
 * @subpackage Database
 */

if ( ! class_exists( 'Snapshot_Model_Database_Backup' ) ) {
	class Snapshot_Model_Database_Backup {

		public $errors;

		private $fp;
		private $status_fp;
		private $filename;
		private $temp_ftell_after;

		public function __construct() {
			$this->errors = array();
		}

		public function Snapshot_Model_Database_Backup() {
			$this->__construct();
		}

		/**
		 * Sets the open file point to be used when writing out the
		 * table dumps. Not needed on the import step.
		 *
		 * @param string $args
		 *
		 * @return none
		 */
		public function set_fp( $fp ) {
			if ( $fp ) {
				$this->fp = $fp;
			}
		}

		public function set_status_fp( $status_fp ) {
			if ( $status_fp ) {
				$this->status_fp = $status_fp;
			}
		}

		/**
		 * Sets the filename where we'll write out the
		 * table dumps. Not needed on the import step.
		 *
		 * @param string $args
		 *
		 * @return none
		 */
		public function set_file( $filename ) {
			if ( $filename ) {
				$this->filename = $filename;
			}
		}

		/**
		 * Gets the temp_ftell_after to be used in retrieving the current position of the pointer.
		 *
		 * @return int/bool
		 */
		public function get_temp_ftell_after() {
			if ( $this->temp_ftell_after ) {
				return $this->temp_ftell_after;
			} else {
				return false;
			}
		}

		/**
		 * Logs any error messages
		 *
		 * @param string $args
		 *
		 * @return none
		 */
		public function error( $error ) {

			$this->errors[] = $error;
		}

		/**
		 * Write to the backup file
		 *
		 * @param string $query_line the line to write
		 *
		 * @return null
		 */
		public function stow( $query_line ) {
			//echo "query_line=[". $query_line ."]<br />";

			if ( false === @fwrite( $this->fp, $query_line ) ) { // phpcs:ignore
				$this->error( __( 'There was an error writing a line to the backup script:', SNAPSHOT_I18N_DOMAIN ) . '  ' . $query_line . '  ' . $php_errormsg );
			}
		}

		/**
		 * Better addslashes for SQL queries.
		 * Taken from phpMyAdmin.
		 */
		public function sql_addslashes( $a_string = '', $is_like = false ) {
			if ( $is_like ) {
				$a_string = str_replace( '\\', '\\\\\\\\', $a_string );
			} else {
				$a_string = str_replace( '\\', '\\\\', $a_string );
			}

			return str_replace( '\'', '\\\'', $a_string );
		}

		/**
		 * Add backquotes to tables and db-names in
		 * SQL queries. Taken from phpMyAdmin.
		 */
		public function backquote( $a_name ) {
			if ( ! empty( $a_name ) && '*' !== $a_name ) {
				// We removed the is_array check because we always use strings with that function.
				//
				// if ( is_array( $a_name ) ) {
				// 	$result = array();
				// 	reset( $a_name );
				// 	while ( list( $key, $val ) = each( $a_name ) ) {
				// 		$result[ $key ] = '`' . $val . '`';
				// 	}

				// 	return $result;
				// } else {
				// 	return '`' . $a_name . '`';
				// }
				return '`' . $a_name . '`';
			} else {
				return $a_name;
			}
		}

		/**
		 * Front-end function to the backup_table() function. This
		 * function just provides the foreach looping over the
		 * tables array provided.
		 *
		 * @since 1.0.0
		 * @uses non
		 *
		 * @param array $tables an array of table names to backup.
		 *
		 * @return none
		 */

		public function backup_tables( $tables ) {

			if ( is_array( $tables ) ) {
				foreach ( $tables as $table ) {
					$this->backup_table( $table );
				}
			}
		}

		/**
		 * Taken partially from phpMyAdmin and partially from
		 * Alain Wolf, Zurich - Switzerland
		 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
		 * Modified by Scott Merrill (http://www.skippy.net/)
		 * to use the WordPress $wpdb object
		 *
		 * @param string $table
		 * @param string $segment
		 *
		 * @return void
		 */
		public function backup_table( $table, $rows_start = 0, $rows_end = '', $rows_total = '', $sql = '' ) {

			global $wpdb;

			$total_rows = 0;
			// Use of esc_sql() instead of $wpdb->prepare() because of backticks in query.
			$table_structure = $wpdb->get_results( esc_sql( "DESCRIBE `{$table}`" ) );
			if ( ! $table_structure ) {
				$this->error( __( 'Error getting table details', SNAPSHOT_I18N_DOMAIN ) . ": $table" );

				return false;
			}

			if ( 0 === $rows_start ) {
				//$this->stow('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"' . ";\n");
				// Use of esc_sql() instead of $wpdb->prepare() because of backticks in query.
				$table_create = $wpdb->get_row( esc_sql( "SHOW CREATE TABLE `{$table}`" ), ARRAY_A );
				//echo "table_create<pre>"; print_r($table_create); echo "</pre>";
				//die();

				if ( isset( $table_create['Create Table'] ) ) {
					$create_table_str = str_replace(
						'CREATE TABLE ' . $this->backquote( $table ) . ' (',
						'CREATE TABLE IF NOT EXISTS ' . $this->backquote( $table ) . ' (',
						$table_create['Create Table'] );
					//echo "create_table_str=[". $create_table_str ."]<br />";
					$this->stow( $create_table_str . ";\n" );
				}
				$this->stow( "TRUNCATE TABLE " . $this->backquote( $table ) . ";\n" );
			}

			if ( ! empty( $sql ) ) {
				$table_data = $wpdb->get_results( esc_sql( $sql ), ARRAY_A );
			} else {
				$table_data = $wpdb->get_results( $wpdb->prepare( esc_sql( "SELECT * FROM `{$table}`") . " LIMIT %d, %d", $rows_start, $rows_end ), ARRAY_A );
			}

			//echo "table_data<pre>"; print_r($table_data); echo "</pre>";
			$entries = 'INSERT INTO ' . $this->backquote( $table ) . ' VALUES (';
			//    \x08\\x09, not required
			$search  = array( "\x00", "\x0a", "\x0d", "\x1a" );
			$replace = array( '\0', '\n', '\r', '\Z' );

			if ( $table_data ) {
				foreach ( $table_data as $row ) {

					$values = array();
					foreach ( $row as $key => $value ) {

						if ( isset( $ints[ strtolower( $key ) ] ) ) {
							// make sure there are no blank spots in the insert syntax,
							// yet try to avoid quotation marks around integers
							$value    = ( null === $value || '' === $value ) ? $defs[ strtolower( $key ) ] : $value;
							$values[] = ( '' === $value ) ? "''" : $value;
						} else {
							$values[] = "'" . str_replace( $search, $replace, $this->sql_addslashes( $value ) ) . "'";
						}
					}
					$this->stow( " \n" . $entries . implode( ', ', $values ) . ');' );
					$total_rows++;
				}
			}

			if ( $rows_end === $rows_total ) {

				// Create footer/closing comment in SQL-file
				$this->stow( "\n" );
				$this->stow( "# --------------------------------------------------------\n" );
				$this->stow( "\n" );
			}

			return $total_rows;
		}


		public function restore_databases( $buffer, $source_table_name = false, $subsite_migration = false ) {
			global $wpdb;

			$sql                         = '';
			$start_pos                   = 0;
			$i                           = 0;
			$len                         = 0;
			$big_value                   = 2147483647;
			$delimiter_keyword           = 'DELIMITER '; // include the space because it's mandatory
			$length_of_delimiter_keyword = strlen( $delimiter_keyword );
			$sql_delimiter               = ';';
			$finished                    = false;
			$log                         = array();

			$len = strlen( $buffer );

			//if (get_class($wpdb) === "wpdb") {
			//	$sql = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
			//	$wpdb->query($sql);
			//}

			// Grab some SQL queries out of it
			while ( $i < $len ) {
				//@set_time_limit( 300 );

				$found_delimiter = false;

				// Find first interesting character
				$old_i = $i;

				// this is about 7 times faster that looking for each sequence i
				// one by one with strpos()
				if ( preg_match( '/(\'|"|#|-- |\/\*|`|(?i)(?<![A-Z0-9_])' . $delimiter_keyword . ')/', $buffer, $matches, PREG_OFFSET_CAPTURE, $i ) ) {
					// in $matches, index 0 contains the match for the complete
					// expression but we don't use it

					$first_position = $matches[1][1];
				} else {
					$first_position = $big_value;
				}

				$first_sql_delimiter = strpos( $buffer, $sql_delimiter, $i );
				if ( false === $first_sql_delimiter ) {
					$first_sql_delimiter = $big_value;
				} else {
					$found_delimiter = true;
				}

				// set $i to the position of the first quote, comment.start or delimiter found
				$i = min( $first_position, $first_sql_delimiter );
				//echo "i=[". $i ."]<br />";

				if ( $i === $big_value ) {
					// none of the above was found in the string

					$i = $old_i;
					if ( ! $finished ) {
						break;
					}

					// at the end there might be some whitespace...
					if ( trim( $buffer ) === '' ) {
						$buffer = '';
						$len    = 0;
						break;
					}

					// We hit end of query, go there!
					$i = strlen( $buffer ) - 1;
				}

				// Grab current character
				$ch = $buffer[ $i ];

				// Quotes
				if ( strpos( '\'"`', $ch ) !== false ) {
					$quote = $ch;
					$endq  = false;

					while ( ! $endq ) {
						// Find next quote
						$pos = strpos( $buffer, $quote, $i + 1 );

						// No quote? Too short string
						if ( false === $pos ) {
							// We hit end of string => unclosed quote, but we handle it as end of query
							if ( $finished ) {
								$endq = true;
								$i    = $len - 1;
							}

							$found_delimiter = false;
							break;
						}

						// Was not the quote escaped?
						$j = $pos - 1;

						while ( '\\' === $buffer[ $j ] ) {
							$j --;
						}

						// Even count means it was not escaped
						$endq = ( ( ( ( $pos - 1 ) - $j ) % 2 ) === 0 );

						// Skip the string
						$i = $pos;

						if ( $first_sql_delimiter < $pos ) {
							$found_delimiter = false;
						}
					}

					if ( ! $endq ) {
						break;
					}

					$i ++;

					// Aren't we at the end?
					if ( $finished && $i === $len ) {
						$i --;
					} else {
						continue;
					}
				}

				// Not enough data to decide
				if ( ( ( ( $len - 1 ) === $i && ( '-' === $ch || '/' === $ch ) )
				       || ( ( $len - 2 ) === $i && ( ( '-' === $ch && '-' === $buffer[ $i + 1 ] )
				                                    || ( '/' === $ch && '*' === $buffer[ $i + 1 ] ) ) ) ) && ! $finished
				) {
					break;
				}


				// Comments
				if ( '#' === $ch
				     || ( $i < ( $len - 1 ) && '-' === $ch && '-' === $buffer[ $i + 1 ]
				          && ( ( $i < ( $len - 2 ) && $buffer[ $i + 2 ] <= ' ' )
				               || ( ( $len - 1 ) === $i && $finished ) ) )
				     || ( $i < ( $len - 1 ) && '/' === $ch && '*' === $buffer[ $i + 1 ] )
				) {
					// Copy current string to SQL
					if ( $start_pos !== $i ) {
						$sql .= substr( $buffer, $start_pos, $i - $start_pos );
					}

					// Skip the rest
					$start_of_comment = $i;

					// do not use PHP_EOL here instead of "\n", because the export
					// file might have been produced on a different system
					$i = strpos( $buffer, '/' === $ch ? '*/' : "\n", $i );

					// didn't we hit end of string?
					if ( false === $i ) {
						if ( $finished ) {
							$i = $len - 1;
						} else {
							break;
						}
					}

					// Skip *
					if ( '/' === $ch ) {
						$i ++;
					}

					// Skip last char
					$i ++;

					// We need to send the comment part in case we are defining
					// a procedure or function and comments in it are valuable
					$sql .= substr( $buffer, $start_of_comment, $i - $start_of_comment );

					// Next query part will start here
					$start_pos = $i;

					// Aren't we at the end?
					if ( $i === $len ) {
						$i --;
					} else {
						continue;
					}
				}

				// Change delimiter, if redefined, and skip it (don't send to server!)
				if ( strtoupper( substr( $buffer, $i, $length_of_delimiter_keyword ) ) === $delimiter_keyword
				     && ( $i + $length_of_delimiter_keyword < $len )
				) {
					// look for EOL on the character immediately after 'DELIMITER '
					// (see previous comment about PHP_EOL)
					$new_line_pos = strpos( $buffer, "\n", $i + $length_of_delimiter_keyword );

					// it might happen that there is no EOL
					if ( false === $new_line_pos ) {
						$new_line_pos = $len;
					}

					$sql_delimiter = substr( $buffer, $i + $length_of_delimiter_keyword, $new_line_pos - $i - $length_of_delimiter_keyword );
					$i             = $new_line_pos + 1;

					// Next query part will start here
					$start_pos = $i;
					continue;
				}

				if ( $found_delimiter || ( $finished && ( $i === $len - 1 ) ) ) {
					$tmp_sql = $sql;

					if ( $start_pos < $len ) {
						$length_to_grab = $i - $start_pos;

						if ( ! $found_delimiter ) {
							$length_to_grab ++;
						}

						$tmp_sql .= substr( $buffer, $start_pos, $length_to_grab );
						unset( $length_to_grab );
					}

					// Do not try to execute empty SQL
					if ( ! preg_match( '/^([\s]*;)*$/', trim( $tmp_sql ) ) ) {
						$sql = $tmp_sql;
						$wpdb->query( 'SET foreign_key_checks = 0' );

						$ret_db = $wpdb->query( $sql ); // phpcs:ignore

						if ( ( false === $ret_db ) && ( (bool) preg_match( '/^create table/i', $sql ) ) ) {
							$last_error = $wpdb->last_error;
							// Failed on create statement, this could be down to FK checks.
							$has_source = ! empty ( $source_table_name ) ? $wpdb->get_var(
								$wpdb->prepare( 'SHOW TABLES LIKE %s', $source_table_name )
							) : false;
							if ( ! empty( $has_source ) && ! empty ( $source_table_name ) && ( false === $subsite_migration )  ) {

								if ( ! empty( $last_error ) && ( false !== strpos( $last_error, 'errno: 121' ) || strpos( false !== $last_error, 'Duplicate key on write or update' ) ) ) {
									// It actually was down to FK checks, so we drop the original table.
									$log[] = 'Table creation issue for the ' . $source_table_name . ' table - attempting to drop the original table first';

									$wpdb->query(
										esc_sql( "DROP TABLE  `{$source_table_name}`;" )
									);
									// Retry the query please.
									$ret_db = $wpdb->query( $sql ); // phpcs:ignore
									if ( false !== $ret_db ) {
										$log[] = 'Table creation for the ' . $source_table_name . ' table succeeded after dropping the original table first';
									}

								}
							}

						}
						$wpdb->query( 'SET foreign_key_checks = 1' );

						$buffer = substr( $buffer, $i + strlen( $sql_delimiter ) );
						// Reset parser:

						$len       = strlen( $buffer );
						$sql       = '';
						$i         = 0;
						$start_pos = 0;

						// Any chance we will get a complete query?
						//if ((strpos($buffer, ';') === FALSE) && !$GLOBALS['finished']) {
						if ( ( strpos( $buffer, $sql_delimiter ) === false ) && ! $finished ) {
							break;
						}
					} else {
						$i ++;
						$start_pos = $i;
					}
				}

			}

			if ( ! empty( $log ) ) {
				return $log;
			}

		}
	}
}