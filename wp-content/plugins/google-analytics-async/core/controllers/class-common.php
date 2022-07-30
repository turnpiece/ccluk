<?php
/**
 * The common class of the plugin.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Controllers
 */

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Common
 *
 * @package Beehive\Core\Controllers
 */
class Common extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Process upgrade.
		add_action( 'init', array( $this, 'upgrade' ) );

		// Deactivate free version.
		add_action( 'init', array( $this, 'deactivate_free' ) );
	}

	/**
	 * Run upgrade process if required.
	 *
	 * We need to make sure we have upgraded all old settings
	 * to our new version without fail. Run upgrade script only
	 * within admin page.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function upgrade() {
		Installer::instance()->upgrade();
	}

	/**
	 * Make sure both Pro and Free versions are not active at a time.
	 *
	 * If Pro version is already active, deactivate the free version if
	 * it is activated.
	 *
	 * @since 3.2.0
	 * @deprecated 3.3.15 Used only if old plugin structure.
	 */
	public function deactivate_free() {
		// Only when both constants found.
		if ( defined( 'BEEHIVE_FREE' ) && BEEHIVE_FREE && defined( 'BEEHIVE_PRO' ) && BEEHIVE_PRO ) {
			// Make sure the function exist.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Check if the Pro version exists and is activated.
			if ( is_plugin_active( 'google-analytics-async/google-analytics-async.php' ) && is_plugin_active( 'beehive-analytics/beehive-analytics.php' ) ) {
				// Pro is activated, so deactivate the free one.
				deactivate_plugins( 'beehive-analytics/beehive-analytics.php' );
			}
		}
	}
}