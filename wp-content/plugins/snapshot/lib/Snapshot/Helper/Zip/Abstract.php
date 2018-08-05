<?php // phpcs:ignore

abstract class Snapshot_Helper_Zip_Abstract {

	protected $_zip;
	protected $_path;

	private $_root_path;

	public function __construct () {
		$this->_root_path = trailingslashit(wp_normalize_path(apply_filters('snapshot_home_path', get_home_path())));
	}

	public function set_root ($root) {
		if (empty($root)) return false;
		$this->_root_path = trailingslashit(wp_normalize_path($root));
	}

	abstract public function initialize ();

	/**
	 * Add files to initialized archive
	 *
	 * @param array $files List of full paths of files to be added
	 * @param string $relative_path Optional relative path to be prepended
	 *
	 * @return bool
	 */
	abstract public function add ($files = array(), $relative_path = false);

	/**
	 * Extract files from prepared archive
	 *
	 * @param string $destination Destination path to extract to
	 *
	 * @return bool
	 */
	abstract public function extract ($destination);

	/**
	 * Extract specific files from prepared archive
	 *
	 * @param string $destination Destination path to extract to
	 * @param array $files Specific list of files
	 *
	 * @return bool
	 */
	abstract public function extract_specific ($destination, $files);

	/**
	 * Whether or not a file is in the archive
	 *
	 * @param string $file File path, full or relative (will be converted)
	 *
	 * @return bool
	 */
	abstract public function has ($file);

	public function prepare ($path) {
		$this->_path = $path;
		$this->initialize();
	}

	protected function _to_root_relative ($file, $relative_path = false) {
		$file = wp_normalize_path($file);
		$root = $this->_get_root_path();

		$rel = !empty($relative_path)
			? trailingslashit(wp_normalize_path($relative_path))
			: ''
		;

		return preg_replace('/^' . preg_quote($root, '/') . '/i', $rel, $file);
	}

	protected function _get_root_path () {
		return $this->_root_path;
	}
}