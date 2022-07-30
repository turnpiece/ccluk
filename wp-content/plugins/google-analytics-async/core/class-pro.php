<?php
/**
 * Defines everything for the Pro version of the plugin.
 *
 * @note    Only hooks fired after the `plugins_loaded` hook will work here.
 *          You need to register earlier hooks separately.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core
 */

namespace Beehive\Core;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Pro
 *
 * @package Beehive\Core
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
		/**
		 * Important: Do not change the priority.
		 *
		 * We need to initialize the modules as early as possible
		 * but using `init` hook. Then only other hooks will work.
		 */
		add_action( 'init', array( $this, 'init_modules' ), - 1 );

		// Initialize dash notification.
		add_action( 'init', array( $this, 'init_dash' ), 1 );

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
				'screens' => General::get_plugin_admin_pages(),
			);

			/* @noinspection PhpIncludeInspection */
			include_once $file;
		}
	}

	/**
	 * Initialize modules for the Pro version of the plugin.
	 *
	 * Note: Hooks that execute after init hook with priority 1 or higher
	 * will only work from this method. You need to handle the earlier hooks separately.
	 * Hook into `beehive_after_core_modules_init` to add new
	 * module.
	 *
	 * @since 3.2.4
	 */
	public function init_modules() {
		/**
		 * Action hook to execute after Pro modules initialization.
		 *
		 * @since 3.2.4
		 */
		do_action( 'beehive_after_pro_modules_init' );
	}
}