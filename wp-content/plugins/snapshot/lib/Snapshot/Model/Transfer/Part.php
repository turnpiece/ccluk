<?php // phpcs:ignore
/**
 * Snapshot models: multipart transfer part
 *
 * @package snapshot
 */

/**
 * Multipart transfer part model class
 */
class Snapshot_Model_Transfer_Part {

	/**
	 * Holds seekTo reference
	 *
	 * @var int seekTo (offset), in bytes
	 */
	private $_seek;

	/**
	 * Holds length reference
	 *
	 * @var int part length, in bytes
	 */
	private $_length;

	/**
	 * Holds part index reference
	 *
	 * @var int
	 */
	private $_index;

	/**
	 * Done flag
	 *
	 * @var bool
	 */
	private $_is_done;

	/**
	 * Constructor
	 *
	 * @param int  $index Part index.
	 * @param bool $is_done Whether the part is done.
	 */
	public function __construct( $index, $is_done = false ) {
		$this->_index = (int) $index;
		$this->_is_done = (bool) $is_done;
	}

	/**
	 * Restores part from its serialized form
	 *
	 * @return object Snapshot_Model_Transfer_Part
	 */
	public static function from_data( $data ) {
		$index = ! empty( $data['index'] )
			? (int) $data['index']
			: 0
		;
		$is_done = isset( $data['is_done'] )
			? (bool) $data['is_done']
			: false
		;
		$obj = new self( $index, $is_done );

		if ( isset( $data['seek'] ) ) {
			$obj->set_seek( $data['seek'] );
		}

		if ( isset( $data['length'] ) ) {
			$obj->set_length( $data['length'] );
		}

		return $obj;
	}

	/**
	 * Gets part in serialized form
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'index' => $this->get_index(),
			'is_done' => $this->is_done(),
			'seek' => $this->get_seek(),
			'length' => $this->get_length(),
		);
	}

	/**
	 * Gets part index
	 *
	 * @return int
	 */
	public function get_index() {
		return (int) $this->_index;
	}

	/**
	 * Gets transfer part number
	 *
	 * @return int
	 */
	public function get_part_number() {
		return $this->get_index() + 1;
	}

	/**
	 * Sets part offset
	 *
	 * @param int $offset Part offset, in bytes.
	 */
	public function set_seek( $offset ) {
		$this->_seek = (int) $offset;
	}

	/**
	 * Gets part offset, in bytes
	 *
	 * @return int
	 */
	public function get_seek() {
		return (int) $this->_seek;
	}

	/**
	 * Sets part length
	 *
	 * @param int $length Part length, in bytes.
	 */
	public function set_length( $length ) {
		$this->_length = (int) $length;
	}

	/**
	 * Gets part length, in bytes
	 *
	 * @return int
	 */
	public function get_length() {
		return (int) $this->_length;
	}

	/**
	 * Completes a part
	 */
	public function complete() {
		$this->_is_done = true;
	}

	/**
	 * Checks whether a part is done
	 *
	 * @return bool
	 */
	public function is_done() {
		return (bool) $this->_is_done;
	}
}