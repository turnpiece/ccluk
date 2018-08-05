<?php // phpcs:ignore
/**
 * Storage implementation in Snapshot Session
 *
 * @package snapshot
 */

/**
 * Snapshot Session storage implementation class
 */
class Snapshot_Model_Storage_Session extends Snapshot_Model_Storage {

	/**
	 * Holds session instance
	 *
	 * @var Snapshot_Helper_Session
	 */
	private $_session;

	public function __construct( $namespace = '' ) {
		parent::__construct( $namespace );

		$loc = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupSessionFolderFull' ) );
		$this->_session = new Snapshot_Helper_Session( $loc, $this->get_namespace() );
	}

	public function load() {
		if ( empty( $this->_session ) ) {
			return false;
		}

		if ( empty( $this->_session->data ) ) {
			$this->_session->load_session();
		}

		$this->_data = ! empty( $this->_session->data[ $this->get_namespace() ] )
			? (array) $this->_session->data[ $this->get_namespace() ]
			: array()
		;

		return is_array( $this->_session->data );
	}

	public function save() {
		if ( empty( $this->_session ) ) {
			return false;
		}

		$this->_session->data[ $this->get_namespace() ] = $this->_data;

		return $this->_session->save_session();
	}
}
