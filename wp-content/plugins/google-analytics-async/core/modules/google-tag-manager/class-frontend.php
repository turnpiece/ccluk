<?php
/**
 * The Google Tag Manager frontend class.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */

namespace Beehive\Core\Modules\Google_Tag_Manager;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Frontend
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */
class Frontend extends Base {

	/**
	 * Register all the hooks related to module.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function init() {
		// Enqueue dummy scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Get the scripts and enqueue them.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function scripts() {
		// For network sites.
		if ( Helper::can_output_network_script() ) {
			$this->frontend_inline( true );
		}

		// For single/subsites.
		$this->frontend_inline();
	}

	/**
	 * Setup inline script for the GTM integrations.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function frontend_inline( $network = false ) {
		// Make sure we are ready to go.
		if ( ! Helper::is_ready( $network ) ) {
			return;
		}

		/**
		 * Filter hook to add header inline scripts for GTM.
		 *
		 * @param array $scripts Script content.
		 * @param bool  $network Network flag.
		 *
		 * @since 3.3.0
		 */
		$header_scripts = apply_filters( 'beehive_gtm_frontend_inline_scripts_header', array(), $network );

		/**
		 * Filter hook to add footer inline scripts for GTM.
		 *
		 * @param array $scripts Script content.
		 * @param bool  $network Network flag.
		 *
		 * @since 3.3.0
		 */
		$footer_scripts = apply_filters( 'beehive_gtm_frontend_inline_scripts_footer', array(), $network );

		// Setup scripts.
		$this->setup_scripts( $header_scripts, 'header', $network );
		$this->setup_scripts( $footer_scripts, 'footer', $network );
	}

	/**
	 * Setup inline script for the GTM integrations.
	 *
	 * @param array  $scripts  Network flag.
	 * @param string $position Position (footer/header).
	 * @param bool   $network  Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function setup_scripts( $scripts = array(), $position = 'footer', $network = false ) {
		if ( ! empty( $scripts ) ) {
			// Get the script name.
			$name = $network ? 'beehive-gtm-network-frontend-' . $position : 'beehive-gtm-frontend-' . $position;

			// Register dummy script.
			wp_register_script(
				$name,
				'',
				array( 'jquery' ),
				BEEHIVE_VERSION,
				'footer' === $position
			);

			// Enqueue dummy script.
			wp_enqueue_script( $name );

			foreach ( $scripts as $script ) {
				// Set inline script.
				wp_add_inline_script( $name, $script );
			}
		}
	}
}