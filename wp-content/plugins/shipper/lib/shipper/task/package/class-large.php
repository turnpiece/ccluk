<?php
/**
 * Shipper tasks: large files packaging task
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Large files packaging class
 */
class Shipper_Task_Package_Large extends Shipper_Task_Package_Files {

	public function get_total_steps() {
		$dumped = new Shipper_Model_Dumped_Largelist;
		return $dumped->get_statements_count();
	}

	/**
	 * Gets the next chunk of file statements to process
	 *
	 * @param object $dumped Optional Shipper_Model_Dumped_Largelist instance (used in tests).
	 *
	 * @return array
	 */
	public function get_next_files( $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Largelist;
		}

		$pos = $this->get_initialized_position();
		$stmts = $dumped->get_statements( $pos, 1 );
		return $stmts;
	}
}