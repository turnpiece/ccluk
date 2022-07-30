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
	 * Holds constants instance
	 *
	 * @var Shipper_Model_Constants_Shipper
	 */
	private $constants;

	/**
	 * Sets internal constants instance
	 *
	 * Used in tests.
	 *
	 * @since v1.0.3
	 *
	 * @param Shipper_Model_Constants $obj Shipper_Model_Constants instance.
	 */
	public function set_constants( Shipper_Model_Constants $obj ) {
		$this->constants = $obj;
	}

	/**
	 * Gets internal constants instance.
	 *
	 * Instantiates one if there's not one already set.
	 *
	 * @since v1.0.3
	 *
	 * @return object Shipper_Model_Constants instance
	 */
	public function get_constants() {
		if ( ! empty( $this->constants ) ) {
			return $this->constants;
		}
		return new Shipper_Model_Constants_Shipper();
	}

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
			: '';
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
		if ( empty( $template ) ) {
			return false; }

		$constants = $this->get_constants();
		if ( $constants->get( 'DEBUG_TEMPLATE' ) ) {
			echo '<div class="shipper-debug shipper-debug-template">';
			echo '<span class="shipper-debug-template-name"><code>' .
				esc_html( $relpath ) . '</code></span>';
		}

		// @codingStandardsIgnoreLine Using extract for templating
		if ( ! empty( $args ) ) { extract( $args, EXTR_PREFIX_SAME, 'view_' ); }
		include $template;

		if ( $constants->get( 'DEBUG_TEMPLATE' ) ) {
			echo '</div>';
		}

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