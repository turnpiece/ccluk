<?php
/**
 * Shipper model abstractions: database table model class
 *
 * @package shipper
 */

/**
 * Table model abstraction
 */
class Shipper_Model_Database_Table extends Shipper_Model_Database {

	const STATEMENT_DELIMITER = 'end_shipper_statement';

	/**
	 * Gets a statement delimiter string
	 *
	 * This delimiter is later used in import, to break up the
	 * importing work into smaller, more manageable chunks.
	 *
	 * @return string
	 */
	public function get_statement_delimiter() {
		return "\n" .
			'# ' .
			join( '', array_fill( 0, 10, '-' ) ) .
			' ' .
			self::STATEMENT_DELIMITER .
			' ' .
			join( '', array_fill( 0, 10, '-' ) ) .
		"\n";
	}

	/**
	 * Gets skipped row delimiter SQL comment
	 *
	 * @param string $reason Reason why the row is being skipped.
	 *
	 * @return string
	 */
	public function get_skipped_row_delimiter( $reason ) {
		$reason = trim( strtolower( preg_replace( '/[^-_ a-z0-9]/i', '', $reason ) ) );
		return "# --- skipped row: {$reason} ---\n";
	}
}