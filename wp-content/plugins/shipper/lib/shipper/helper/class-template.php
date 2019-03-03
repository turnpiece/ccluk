<?php
/**
 * Shipper templating: Template helper class
 *
 * @package shipper
 */

/**
 * Template class
 */
class Shipper_Helper_Template {

	/**
	 * Resolves relative template path to an actual absolute path
	 *
	 * @param string $relpath Relative template path.
	 *
	 * @return string
	 */
	public function get_template_path( $relpath ) {
		$root = wp_normalize_path( trailingslashit( dirname( SHIPPER_PLUGIN_FILE ) ) . 'tpl/' );
		$path = wp_normalize_path( realpath( "{$root}{$relpath}.php" ) );

		return $path && preg_match( '/' . preg_quote( $root, '/' ) . '/', $path )
			? $path
			: ''
		;
	}

	/**
	 * Renders the template with supplied arguments
	 *
	 * @param string $relpath Relative template path.
	 * @param array  $args Optional arguments.
	 *
	 * @return bool
	 */
	public function render( $relpath, $args = array() ) {
		$template = $this->get_template_path( $relpath );
		if ( empty( $template ) ) { return false; }

		// @codingStandardsIgnoreLine Using extract for templating
		if ( ! empty( $args ) ) { extract( $args, EXTR_PREFIX_SAME, 'view_' ); }
		include( $template );
		return true;
	}

	/**
	 * Gets rendered template with supplied arguments as a string
	 *
	 * @param string $relpath Relative template path.
	 * @param array  $args Optional arguments.
	 *
	 * @return string
	 */
	public function get( $relpath, $args = array() ) {
		ob_start();
		$this->render( $relpath, $args );
		return ob_get_clean();
	}
}