<?php // phpcs:ignore
/*
Snapshots Locker Class
Dexcription: This locker class is used during process of Snapshot archive creation or restore. This utility creates a process lock file with information about the running process.
*/

if ( ! class_exists( 'Snapshot_Helper_Locker' ) ) {
	class Snapshot_Helper_Locker {

		private $lockFolder;
		private $item_key;
		private $data_item_key;
		private $lock_fp;
		private $has_lock;
		private $locker_info = array();

		public function __construct( $backupLockFolderFull, $item_key ) {
			$this->lockFolder = trailingslashit( $backupLockFolderFull );
			$this->item_key   = $item_key;

			$this->lockFileFull = $this->lockFolder . $this->item_key . ".lock";

			$this->has_lock = false;

			/*
				c+ mode do not exist on php < 5.2.6
				https://stackoverflow.com/questions/5682616/fopen-fread-and-flock
			*/

	        /*
	          if file exists, open in read+ plus mode so we can try to lock it
	          -- opening in w+ would truncate the file *before* we could get a lock!
	        */

	        if( version_compare( PHP_VERSION, '5.2.6' ) >= 0 ) {
	            $mode = 'c+';
	        } else {
	            //'c+' would be the ideal $mode to use, but that's only
	            //available in PHP >=5.2.6

	            $mode = file_exists( $this->lockFileFull ) ? 'r+' : 'w+';
	            //there's a small chance of a race condition here
	            // -- two processes could end up opening the file with 'w+'
	        }

	        // phpcs:ignore
			$this->lock_fp = fopen( $this->lockFileFull, $mode );
			$this->is_locked();
		}

		public function Snapshot_Controller_Locker( $backupLockFolderFull, $item_key ) {
			$this->__construct( $backupLockFolderFull, $item_key );
		}


		public function __destruct() {
			$this->unlock();
		}

		/**
		 * Sets lock on internal file pointer
		 *
		 * @return bool
		 */
		public function lock () {
			return flock( $this->lock_fp, LOCK_EX | LOCK_NB );
		}

		/**
		 * Unsets internal pointer lock
		 *
		 * @return bool
		 */
		public function unlock () {
			// See this bug on PHP flock third argument https://bugs.php.net/bug.php?id=31189&edit=2
			if ( $this->lock_fp ) {
				flock( $this->lock_fp, LOCK_UN );
				// phpcs:ignore
				fclose( $this->lock_fp );

				unset( $this->lock_fp );
				$this->lock_fp = false;

				return true;
			}
			return false;
		}

		public function is_locked() {
			if ( $this->lock_fp ) {
				if ( $this->lock() ) {
					$this->has_lock = true;
				} else {
					$this->has_lock = false;
				}
			}

			return $this->has_lock;
		}

		public function set_locker_info( $locker_info = array() ) {
			// Only the locking process can write to the file.
			if ( $this->is_locked() ) {
				rewind( $this->lock_fp );
				$locker_info['time_start'] = time();
				$locker_info['pid']        = mt_rand(0, 32000);
				$write_ret                 = fwrite( $this->lock_fp, wp_json_encode( $locker_info, JSON_FORCE_OBJECT ) . "\r\n" ); // phpcs:ignore
				fflush( $this->lock_fp );
			}
		}

		public function get_locker_info( $info_key = '' ) {
			if ( $this->lock_fp ) {
				rewind( $this->lock_fp );
				$locker_info = fgets( $this->lock_fp, 4096 );
				if ( $locker_info ) {
					$locker_info             = json_decode( $locker_info, true );
					$locker_info['has_lock'] = $this->is_locked();
					if ( strlen( $info_key ) ) {
						if ( isset( $locker_info[ $info_key ] ) ) {
							return $locker_info[ $info_key ];
						} else {
							return false;
						}
					}

					return $locker_info;
				}

				return false;
			}

			return false;
		}
	}
}