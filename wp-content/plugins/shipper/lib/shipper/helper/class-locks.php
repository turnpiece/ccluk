<?php
/**
 * Shipper helpers: process locks
 *
 * @package shipper
 */

/**
 * Stored locks class
 */
class Shipper_Helper_Locks {

	const LOCK_MIGRATION = 'migration';
	const LOCK_CANCEL    = 'cancel';
	const LOCK_PREFLIGHT = 'preflight';

	const LOCKED = 'locked';

	/**
	 * Checks whether we have the lock on this process
	 *
	 * @param string $process Process to check the lock for.
	 *
	 * @return bool
	 */
	public function has_lock( $process ) {
		$lockfile = $this->get_lock_file( $process );
		if ( ! file_exists( $lockfile ) ) {
			return false;
		}

		$fs = Shipper_Helper_Fs_File::open( $lockfile );

		if ( ! $fs ) {
			return false;
		}

		$lock = $fs->isReadable() ?
			$fs->fread( $fs->getSize() )
			: false;

		if ( false === $lock ) {
			// OK, so it _does_ exist, but can't be read.
			// Probably just cleared. Let's go with that.
			return true;
		}
		return self::LOCKED === $lock;
	}

	/**
	 * Checks whether the lock is overdue for a cleanup
	 *
	 * @param string $process Process to check the lock for.
	 *
	 * @return bool
	 */
	public function is_old_lock( $process ) {

		/**
		 * Stale locks cleanup check
		 *
		 * This is how we decide whether to clear stale locks.
		 * We are doing this because clearing "stale" locks messes up the
		 * import process if set_time_limit *actually* does what it's supposed
		 * to do, which might not always be the case :(
		 *
		 * @param bool $forbid_cleanup If true, stale locks will not be cleared.
		 *
		 * @return bool
		 */
		$forbid_stale_locks_check = apply_filters(
			'shipper_locks_forbid_stale_checks',
			false
		);
		if ( $forbid_stale_locks_check ) {
			return false;
		}

		$exec_time = Shipper_Helper_System::get_max_exec_time_capped();
		if ( empty( $exec_time ) ) {
			return false;
		}
		$lockfile = $this->get_lock_file( $process );
		clearstatcache( true, $lockfile );
		$locktime = filemtime( $lockfile );
		if ( empty( $locktime ) ) {
			return false;
		}

		$lock_expiry = $locktime + $exec_time;
		return time() > $lock_expiry;
	}

	/**
	 * Sets running lock on a process
	 *
	 * @param string $process Process to set the lock for.
	 *
	 * @return bool
	 */
	public function set_lock( $process ) {
		$fs = Shipper_Helper_Fs_File::open( $this->get_lock_file( $process ), 'w' );

		if ( ! $fs ) {
			return false;
		}

		return ! ! $fs->fwrite( self::LOCKED );
	}

	/**
	 * Releases the lock on a process
	 *
	 * @param string $process Process to unlock.
	 *
	 * @return bool
	 */
	public function release_lock( $process ) {
		$lockfile = $this->get_lock_file( $process );
		$fs       = Shipper_Helper_Fs_File::open( $lockfile, 'w' );

		if ( ! $fs ) {
			return false;
		}

		$fs->fwrite( '' );

		return shipper_delete_file( $lockfile );
	}

	/**
	 * Clears all process locks
	 *
	 * @return bool
	 */
	public function clear_locks() {
		$locks = glob( trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) . '*.lock' );
		if ( empty( $locks ) ) {
			return true; }

		$status = true;
		foreach ( $locks as $lock ) {
			$fs = Shipper_Helper_Fs_File::open( $lock, 'w' );

			if ( ! $fs ) {
				continue;
			}

			$fs->fwrite( '' );
			if ( ! shipper_delete_file( $lock ) ) {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Gets lock storage file path
	 *
	 * @param string $process Process key.
	 *
	 * @return string
	 */
	public function get_lock_file( $process ) {
		return trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) . sprintf(
			'shipper-locks-%s.lock',
			preg_replace( '/[^-_a-z0-9]/i', '', $process )
		);
	}
}