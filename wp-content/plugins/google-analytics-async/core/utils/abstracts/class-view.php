<?php
/**
 * The view base class of the plugin.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Utils\Abstracts
 */

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class View
 *
 * @package Beehive\Core\Utils\Abstracts
 */
class View extends Base {

	/**
	 * Render an admin view template.
	 *
	 * @param string $view File name.
	 * @param array  $args Arguments.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function view( $view, $args = array() ) {
		// Default views.
		$file_name = BEEHIVE_DIR . 'app/templates/' . $view . '.php';

		// If file exist, set all arguments are variables.
		if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
			if ( ! empty( $args ) ) {
				$args = (array) $args;
				// phpcs:ignore
				extract( $args );
			}

			/* @noinspection PhpIncludeInspection */
			include $file_name;
		}
	}
}