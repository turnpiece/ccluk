<?php

namespace Beehive\Core;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Modules;
use Beehive\Core\Views\Settings;
use Beehive\Core\Controllers\GDPR;
use Beehive\Core\Controllers\Ajax;
use Beehive\Core\Controllers\Admin;
use Beehive\Core\Controllers\Assets;
use Beehive\Core\Controllers\Common;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Controllers\Capability;
use Beehive\Core\Controllers\Compatibility;

/**
 * Defines everything for the core plugin.
 *
 * @note   Only hooks fired after the `plugins_loaded` hook will work here.
 *       You need to register earlier hooks separately.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Core extends Base {

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
		// Register all actions and filters.
		$this->init();

		/**
		 * Important: Do not change the priority.
		 *
		 * We need to initialize the modules as early as possible
		 * but using `init` hook. Then only other hooks will work.
		 */
		add_action( 'init', [ $this, 'init_modules' ], -1 );

		/**
		 * Action hook to trigger after initializing all core actions.
		 *
		 * You still need to check if it Pro version or Free.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_core_init' );
	}

	/**
	 * Register all the actions and filters for the plugin free features.
	 *
	 * Note: Module features are registered within the module class.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function init() {
		// Initialize sub classes.
		Settings::instance()->init();
		Capability::instance()->init();
		Admin::instance()->init();
		Assets::instance()->init();
		Common::instance()->init();
		Ajax::instance()->init();
		GDPR::instance()->init();
		Compatibility::instance()->init();
	}

	/**
	 * Initialize modules for the core plugin.
	 *
	 * Note: Hooks that execute after init hook with priority 1 or higher
	 * will only work from this method. You need to handle the earlier hooks separately.
	 * Hook into `beehive_after_core_modules_init` to add new
	 * module.
	 *
	 * @since 3.2.0
	 */
	public function init_modules() {
		// Google authentication.
		Modules\Google_Auth\Auth::instance()->init();

		// Google Analytics.
		Modules\Google_Analytics\Analytics::instance()->init();

		/**
		 * Action hook to execute after free modules initialization.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_core_modules_init' );
	}
}