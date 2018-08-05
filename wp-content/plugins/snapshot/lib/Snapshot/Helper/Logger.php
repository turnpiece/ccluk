<?php // phpcs:ignore
/*
Snapshots Logger Class
Dexcription: This logger class is used from various parts of the Snapshots plugin to write messages to the log or archive items.
*/

if ( ! class_exists( 'Snapshot_Helper_Logger' ) ) {
	class Snapshot_Helper_Logger {

		private $DEBUG;
		private $logFolder;
		private $logFileFull;
		private $item_key;
		private $data_item_key;
		private $log_fp;

		public function __construct( $backupLogFolderFull, $item_key, $data_item_key ) {
			$this->logFolder     = trailingslashit( $backupLogFolderFull );
			$this->item_key      = $item_key;
			$this->data_item_key = $data_item_key;

			$this->start_logger();
		}

		public function Snapshot_Helper_Logger( $backupLogFolderFull, $item_key, $data_item_key ) {
			$this->__construct( $backupLogFolderFull, $item_key, $data_item_key );
		}

		public function __destruct() {}

		public function start_logger() {
			$this->logFileFull = $this->logFolder . "/" . $this->item_key . "_" . $this->data_item_key . ".log";
		}

		public function get_log_filename() {
			return $this->logFileFull;
		}

		public function log_message( $message ) {
			global $wp_filesystem;

			if( Snapshot_Helper_Utility::connect_fs() ) {
				if ( $this->logFileFull ) {
					$wp_filesystem->put_contents($this->logFileFull, $wp_filesystem->get_contents( $this->logFileFull ) . Snapshot_Helper_Utility::show_date_time( time(), 'Y-m-d H:i:s' ) . ": " . $message . "\r\n", FS_CHMOD_FILE);
				}
			} else {
				return new WP_Error("filesystem_error", "Cannot initialize filesystem");
			}
		}

	}
}