<?php
/**
 * The assets controller class of the plugin.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Controllers
 */

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Assets
 *
 * @package Beehive\Core\Controllers
 */
class Assets extends Base {

	/**
	 * Initialize assets functionality.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		// Include clipboard JS.
		add_filter( 'beehive_assets_get_scripts', array( $this, 'register_clipboard' ) );
	}

	/**
	 * Assets for our front end functionality.
	 *
	 * Currently this function will not register anything.
	 * But this should be here for other modules to register
	 * public assets.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function public_assets() {
		$this->register_styles( false );
		$this->register_scripts( false );
	}

	/**
	 * Assets for our front end functionality.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function admin_assets() {
		$this->register_styles();
		$this->register_scripts();
	}

	/**
	 * Register available styles.
	 *
	 * We are just registering the assets with WP now.
	 * We will enqueue them when it's really required.
	 *
	 * @param bool $admin Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	private function register_styles( $admin = true ) {
		// Get all the assets.
		$styles = $this->get_styles( $admin );

		// Register all styles.
		foreach ( $styles as $handle => $data ) {
			// Get the source full url.
			$src = empty( $data['external'] ) ? BEEHIVE_URL . 'app/assets/css/' . $data['src'] : $data['src'];

			// Register custom videos scripts.
			wp_register_style(
				$handle,
				$src,
				empty( $data['deps'] ) ? array() : $data['deps'],
				empty( $data['version'] ) ? BEEHIVE_VERSION : $data['version'],
				empty( $data['media'] ) ? false : true
			);
		}
	}

	/**
	 * Register available scripts.
	 *
	 * We are just registering the assets with WP now.
	 * We will enqueue them when it's really required.
	 *
	 * @param bool $admin Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	private function register_scripts( $admin = true ) {
		// Get all the assets.
		$scripts = $this->get_scripts( $admin );

		// Register all available scripts.
		foreach ( $scripts as $handle => $data ) {
			// Get the source full url.
			$src = empty( $data['external'] ) ? BEEHIVE_URL . 'app/assets/js/' . $data['src'] : $data['src'];

			// Register custom videos scripts.
			wp_register_script(
				$handle,
				$src,
				empty( $data['deps'] ) ? array() : $data['deps'],
				empty( $data['version'] ) ? BEEHIVE_VERSION : $data['version'],
				isset( $data['footer'] ) ? $data['footer'] : true
			);
		}
	}

	/**
	 * Enqueue a script with localization.
	 *
	 * Always use this method to enqueue scripts. Then only
	 * we will get the required localized vars.
	 *
	 * @param string $script Script handle name.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function enqueue_script( $script ) {
		static $vars_printed = false;

		// Only if not enqueued already.
		if ( ! wp_script_is( $script ) ) {
			// Extra vars.
			wp_localize_script(
				$script,
				'beehiveModuleVars',
				/**
				 * Filter to add/remove vars in script.
				 *
				 * @since 3.2.4
				 */
				apply_filters( "beehive_assets_scripts_localize_vars_{$script}", array() )
			);

			if ( ! $vars_printed ) {
				wp_localize_script(
					$script,
					'beehiveVars',
					/**
					 * Filter to add/remove vars in script.
					 *
					 * @param array $common_vars Common vars.
					 * @param array $handle      Script handle name.
					 *
					 * @since 3.2.4
					 */
					apply_filters( 'beehive_assets_scripts_common_localize_vars', array(), $script )
				);

				// Localized vars for the locale.
				wp_localize_script( $script, 'beehiveI18n', I18n::instance()->get_strings( $script ) );
			}

			// Enqueue.
			wp_enqueue_script( $script );

			$vars_printed = true;
		}
	}

	/**
	 * Enqueue a style with WordPress.
	 *
	 * This is just an alias function.
	 *
	 * @param string $style Style handle name.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function enqueue_style( $style ) {
		// Only if not enqueued already.
		if ( ! wp_style_is( $style ) ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * Get the scripts list to register.
	 *
	 * @param bool $admin Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function get_scripts( $admin = true ) {
		if ( $admin ) {
			$scripts = array(
				'beehive-sui-common' => array(
					'src'  => 'sui-common.min.js',
					'deps' => array( 'jquery' ),
				),
				'beehive-dashboard'  => array(
					'src'  => 'dashboard.min.js',
					'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common' ),
				),
				'beehive-settings'   => array(
					'src'  => 'settings.min.js',
					'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common' ),
				),
				'beehive-accounts'   => array(
					'src'  => 'accounts.min.js',
					'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common', 'clipboard' ),
				),
				'beehive-tutorials'  => array(
					'src'  => 'tutorials.min.js',
					'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common' ),
				),
				'beehive-common'     => array(
					'src'  => 'chunk-common.min.js',
					'deps' => array( 'jquery' ),
				),
				'beehive-vendors'    => array(
					'src'  => 'chunk-vendors.min.js',
					'deps' => array( 'jquery' ),
				),
			);
		} else {
			$scripts = array();
		}

		/**
		 * Filter to include/exclude new script.
		 *
		 * Modules should use this filter to that common localized
		 * vars will be available.
		 *
		 * @param array $scripts Scripts list.
		 * @param bool  $admin   Is admin assets?.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_assets_get_scripts', $scripts, $admin );
	}

	/**
	 * Get the styles list to register.
	 *
	 * @param bool $admin Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function get_styles( $admin = true ) {
		if ( $admin ) {
			$styles = array(
				'beehive-dashboard' => array(
					'src' => 'dashboard.min.css',
				),
				'beehive-settings'  => array(
					'src' => 'settings.min.css',
				),
				'beehive-accounts'  => array(
					'src' => 'accounts.min.css',
				),
				'beehive-tutorials' => array(
					'src' => 'tutorials.min.css',
				),
			);
		} else {
			$styles = array();
		}

		/**
		 * Filter to include/exclude new style.
		 *
		 * Modules should use this filter to include styles.
		 *
		 * @param array $styles Styles list.
		 * @param bool  $admin  Is admin assets?.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_assets_get_styles', $styles, $admin );
	}

	/**
	 * Add clipboard JS to the scripts list if required.
	 *
	 * @param array $scripts Scripts list.
	 *
	 * @since 3.3.1
	 *
	 * @return array
	 */
	public function register_clipboard( $scripts ) {
		global $wp_version;

		// We need to include the lib manually for WP below 5.2.
		if ( version_compare( $wp_version, '5.2', '<' ) ) {
			$scripts['clipboard'] = array(
				'src'  => 'clipboard.min.js',
				'deps' => array( 'jquery' ),
			);
		}

		return $scripts;
	}
}