<?php
/**
 * Defines everything for the core plugin.
 *
 * @note    Only hooks fired after the `plugins_loaded` hook will work here.
 *       You need to register earlier hooks separately.
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

use Beehive\Core\Modules;
use Beehive\Core\Endpoints;
use Beehive\Core\Controllers;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Views\Admin as Admin_View;

/**
 * Class Core
 *
 * @package Beehive\Core
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
		add_action( 'init', array( $this, 'init_modules' ), -1 );

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
		Controllers\I18n::instance()->init();
		Admin_View::instance()->init();
		Controllers\Capability::instance()->init();
		Controllers\Admin::instance()->init();
		Controllers\Menu::instance()->init();
		Controllers\Assets::instance()->init();
		Controllers\Common::instance()->init();
		Controllers\GDPR::instance()->init();
		Controllers\Compatibility::instance()->init();

		// Setup endpoints.
		Endpoints\Settings::instance();
		Endpoints\Actions::instance();
		Endpoints\Data::instance();
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

		// Google Tag Manager.
		Modules\Google_Tag_Manager\Tag_Manager::instance()->init();

		/**
		 * Action hook to execute after free modules initialization.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_core_modules_init' );
	}
}