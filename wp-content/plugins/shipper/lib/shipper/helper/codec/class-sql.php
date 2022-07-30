<?php
/**
 * Shipper codec: replace stuff in SQL exports
 *
 * @package shipper
 */

/**
 * SQL replacer class
 */
class Shipper_Helper_Codec_Sql extends Shipper_Helper_Codec {

	/**
	 * Model instance holder
	 *
	 * @var \Shipper_Model_Stored_Migration
	 */
	private $model;

	/**
	 * Optional intermediate prefix
	 *
	 * @var string
	 */
	private $intermediate_prefix = '';

	/**
	 * Shipper_Helper_Codec_Sql constructor.
	 *
	 * @param null $model Shipper_Model_Stored_Migration instance holder.
	 */
	public function __construct( $model = null ) {
		if ( null === $model ) {
			$this->model = new Shipper_Model_Stored_Migration();
		} else {
			$this->model = $model;
		}
	}

	/**
	 * Gets intermediate codec expansion
	 *
	 * Used in imports
	 *
	 * @param string $prefix Intermediate prefix to use in expansion.
	 *
	 * @return object Shipper_Helper_Codec_Sql instance.
	 */
	public static function get_intermediate( $prefix = '' ) {
		$me                      = new self();
		$me->intermediate_prefix = $prefix;

		return $me;
	}

	/**
	 * Gets a list of replacement pairs
	 *
	 * A replacement pair is represented like so:
	 * Context-dependent table name as a key, macro-prefixed table name as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		global $wpdb;
		$like   = $wpdb->esc_like( $wpdb->base_prefix ) . '%';
		$tables = $wpdb->get_col(
			$wpdb->prepare(
				'SHOW TABLES FROM `' . DB_NAME . '` LIKE %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$like
			)
		); // db call ok,cache ok.

		// Also add whatever are the WP defaults.
		foreach ( $wpdb->tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		foreach ( $wpdb->old_tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		foreach ( $wpdb->global_tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		foreach ( $wpdb->ms_global_tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		$tables = array_values( array_unique( $tables ) );

		// End defaults stuffing.
		$result = array();

		$rx = preg_quote( $wpdb->base_prefix, '/' );
		foreach ( $tables as $table ) {
			$key = $table;
			if ( ! empty( $this->intermediate_prefix ) ) {
				$key = "{$this->intermediate_prefix}_{$table}";
			}
			$result[ $key ] = preg_replace( "/^{$rx}/", '{{SHIPPER_TABLE_PREFIX}}', $table );
		}

		// Catch-all clause.
		if ( ! empty( $this->intermediate_prefix ) ) {
			$result[ "{$this->intermediate_prefix}_{$wpdb->base_prefix}" ] = '{{SHIPPER_TABLE_PREFIX}}';
		}

		return $result;
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Purposefully single-task oriented - just process the subset of SQL
	 * statements actually used by the export process (drop|create|insert).
	 *
	 * Will match an entire line (one line per statement).
	 *
	 * @param string $string Original table name.
	 * @param string $value Optional table name with prefix replaced with a macro.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: preg_quote( $string, '/' );

		// phpcs:disable
		return '^' .
		       '(' .
		       'DROP TABLE IF EXISTS' .
		       '|' .
		       'CREATE TABLE IF NOT EXISTS' .
		       '|' .
		       'CREATE TABLE' .
		       '|' .
		       'LOCK TABLES' .
		       '|' .
		       'INSERT INTO' .
		       '|' .
		       'ALTER TABLE' .
		       ')' .
		       '\s*' .
		       '(' .
		       '`?' . $value . '`?' .
		       ')' .
		       '\s' .
		       '(.*)' .
		       '$';
		// phpcs:enable
	}

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Original table name.
	 * @param string $value Process-dependent table name representation
	 *                      (macro-prefixed on export, original on import).
	 *
	 * @return string
	 */
	public function get_replacement( $name, $value ) {
		return '\1 ' . $value . ' \3';
	}
}