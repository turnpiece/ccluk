<?php // phpcs:ignore

abstract class Snapshot_Model_Full_Abstract extends Snapshot_Model_Full {

	/**
	 * Gets a list of backups
	 *
	 * @return array A list of full backup items
	 */
	abstract public function get_backups ();

	/**
	 * Gets a backup file instance path
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return mixed Path to backup if local file exists, (bool)false otherwise
	 */
	abstract public function get_backup ($timestamp);

	/**
	 * Deletes a backup instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	abstract public function delete_backup ($timestamp);

}