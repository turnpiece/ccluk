<?php

namespace Beehive\Core;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Defines everything for the Pro version of the plugin.
 *
 * @note   Only hooks fired after the `plugins_loaded` hook will work here.
 *       You need to register earlier hooks separately.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Pro extends Base {

	/**
	 * Setup the plugin and register all hooks.
	 *
	 * Pro version features and not initialized yet, so do not
	 * execute something on this hooks if you are checking for
	 * Beehive Pro.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup() {
		// Initialize dash notification.
		add_action( 'init', [ $this, 'init_dash' ] );

		/**
		 * Action hook to trigger after initializing all Pro features.
		 *
		 * Use this hook to execute anything for Pro version.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_pro_init' );
	}

	/**
	 * Setup WPMUDEV Dashboard notifications.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init_dash() {
		// Dashboard file.
		$file = BEEHIVE_DIR . '/core/external/dash-notice/wpmudev-dash-notification.php';

		// Load WPMUDEV dashboard.
		if ( file_exists( $file ) ) {
			global $wpmudev_notices;

			// Setup dashboard notice.
			$wpmudev_notices[] = array(
				'id'      => 51,
				// Plugin name.
				'name'    => General::plugin_name(),
				// Plugin screens.
				'screens' => General::$pages,
			);

			/* @noinspection PhpIncludeInspection */
			include_once $file;
		}
	}
}