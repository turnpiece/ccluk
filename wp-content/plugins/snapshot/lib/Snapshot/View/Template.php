<?php // phpcs:ignore

class Snapshot_View_Template {

	private static $_instances = array();

	protected $_relative_path;

	private function __clone() { }

	/**
	 * Gets instance and wires it up for resolution.
	 *
	 * @param string|bool $relative Relative path fragment
	 *
	 * @return Snapshot_View_Template Instance
	 */
	public static function get( $relative = false ) {
		if ( ! empty( self::$_instances[ $relative ] ) ) {
			return self::$_instances[ $relative ];
		}

		$view = new self();
		$view->set_relative_path( $relative );

		self::$_instances[ $relative ] = $view;
		return $view;
	}

	/**
	 * Sets relative path fragment for template resolution
	 *
	 * @param string $relative Relative path fragment
	 */
	public function set_relative_path( $relative ) {
		$this->_relative_path = preg_replace( '/[^-_a-z0-9]/', '', $relative );
	}

	/**
	 * Get relative path fragment
	 *
	 * @return string Relative path fragment
	 */
	public function get_relative_path() {
		return $this->_relative_path;
	}

	/**
	 * The actual template inclusion
	 *
	 * @param string $template Template to use
	 * @param array  $vars Local vars cache
	 *
	 * @return bool
	 */
	public function load( $template = false, $vars = array() ) {
		$template = preg_replace( '/[^-_a-z0-9]/', '', $template );
		if ( empty( $template ) ) {
			return false;
		}

		$root = $this->_get_template_path();
		if ( empty( $root ) ) {
			return false;
		}

		$path = "{$root}/{$template}.php";

		if ( ! empty( $vars ) ) {
			extract( $vars ); // phpcs:ignore
		}

		return file_exists( $path ) ? include  $path  : false;
	}

	/**
	 * Converts raw markup into proper (error) message HTML
	 *
	 * Basically, only allows for a strictly limited subset of tags
	 * to be used in error messages.
	 *
	 * @param string $message Raw message
	 *
	 * @return string
	 */
	public function to_message_html( $message ) {
		return wp_kses(
			$message,
			array(
				'a' => array(
					'href' => array(),
					'target' => array(),
				),
				'code' => array(),
				'pre' => array(),
				'em' => array(),
				'i' => array(),
				'strong' => array(),
				'b' => array(),
				'br' => array(),
			),
			array( 'http', 'https' )
		);
	}

	/**
	 * Get full template path to views directory.
	 * No trailing slash
	 *
	 * @return string Full path to views
	 */
	protected function _get_template_path() {
		$root = wp_normalize_path( untrailingslashit( WPMUDEVSnapshot::instance()->get_plugin_path() ) );
		return untrailingslashit( $root . '/views/' . $this->get_relative_path() );
	}

}