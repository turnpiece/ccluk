<?php // phpcs:ignore
/*
Snapshots Session Class
Description: This session class is used in place of PHP _SESSION variable since many sites don't see to have _SESSIONS setup properly or not at all
*/

if ( ! class_exists( 'Snapshot_Helper_Session' ) ) {
	class Snapshot_Helper_Session {

		private $DEBUG;
		private $sessionFileFull;
		private $force_clear = false;
		public $data = array();

		public function __construct( $backupLogFolderFull, $item_key, $force_clear = false ) {
			$backupLogFolderFull = trailingslashit( $backupLogFolderFull );
			$item_key            = esc_attr( $item_key );

			$this->force_clear = $force_clear;

			$this->sessionFileFull = $backupLogFolderFull . $item_key . "_session.php";

			$this->load_session();

			return $this->data;
		}

		public function Snapshot_Helper_Session( $backupLogFolderFull, $item_key, $force_clear = false ) {
			$force_clear = false;
			$this->__construct( $backupLogFolderFull, $item_key, $force_clear );
		}

		public function __destruct() {
			$this->save_session();
		}

		public function load_session() {

			if ( ( file_exists( $this->sessionFileFull ) ) && ( false === $this->force_clear ) ) {
				$data = file_get_contents( $this->sessionFileFull ); // phpcs:ignore

				if ( $data ) {
					if (defined('SNAPSHOT_SESSION_PROTECT_DATA') && SNAPSHOT_SESSION_PROTECT_DATA) {
						$data = Snapshot_Helper_String::reveal_string($data);
					}
					$this->data = json_decode( $data, true );
				} else {
					$this->data = array();
				}
			} else {
				$this->data = array();
			}
		}

		/**
		 * Session data saving
		 *
		 * Enables session data to persist over requests
		 *
		 * @return bool
		 */
		public function save_session() {
			if ( ! isset( $this->data ) ) {
				$this->data = array();
			}
			$data = wp_json_encode( $this->data, JSON_FORCE_OBJECT );
			if (defined('SNAPSHOT_SESSION_PROTECT_DATA') && SNAPSHOT_SESSION_PROTECT_DATA) {
				$data = Snapshot_Helper_String::conceal_string($data);
			}

			return (bool)file_put_contents( $this->sessionFileFull, $data ); // phpcs:ignore

		}

		public function update_data( $data ) {
			$this->data = $data;

			if ( is_array( $this->data ) ) {
				$this->data = wp_json_encode( $this->data, JSON_FORCE_OBJECT );
			}

		}

	}
}