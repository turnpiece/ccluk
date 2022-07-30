<?php
/**
 * The GDPR class of the plugin.
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

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class GDPR
 *
 * @package Beehive\Core\Controllers
 */
class GDPR extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Add GDPR content to privacy page.
		add_action( 'admin_init', array( $this, 'privacy_content' ) );
	}

	/**
	 * Add privacy policy content for Beehive.
	 *
	 * @since 3.1.7
	 *
	 * @return void
	 */
	public function privacy_content() {
		// Make sure we don't break things for old versions.
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {

			// Privacy content.
			$content = __( 'This website uses Google Analytics to track website traffic. Collected data is processed in such a way that visitors cannot be identified.', 'ga_trans' );

			/**
			 * Filter to modify privacy policy content for Beehive.
			 *
			 * @param string $content Content.
			 *
			 * @since 3.2.0
			 */
			$content = apply_filters( 'beehive_privacy_content', $content );

			// Add to privacy policy page.
			wp_add_privacy_policy_content(
				General::plugin_name(),
				wp_kses_post( wpautop( $content, false ) )
			);
		}
	}
}