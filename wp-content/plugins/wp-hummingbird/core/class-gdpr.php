<?php
/**
 * Class responsible for GDPR compliance: WP_Hummingbird_GDPR class
 *
 * Class WP_Hummingbird_GDPR
 *
 * @since 1.9.2
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_GDPR is responsible for GDPR compliance.
 *
 * @since 1.9.2
 */
class WP_Hummingbird_GDPR {

	/**
	 * Singleton class instance.
	 *
	 * @var WP_Hummingbird_GDPR|null
	 */
	private static $_instance = null;

	/**
	 * Get class instance.
	 *
	 * @return WP_Hummingbird_GDPR|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new WP_Hummingbird_GDPR();
		}

		return self::$_instance;
	}

	/**
	 * WP_Hummingbird_GDPR constructor.
	 */
	private function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register hooks and filters related to GDPR.
	 */
	private function register_hooks() {
		// Register private policy text.
		add_action( 'admin_init', array( $this, 'privacy_policy_content' ) );
	}

	/**
	 * Register private policy text.
	 */
	public function privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = sprintf(
			'<h3>%s</h3><p>%s</p>',
			__( 'Third parties', 'wphb' ),
			sprintf(
				/* translators: %s: start of a href tag, %s: end of a tag */
				__(
					'Hummingbird uses the Stackpath Content Delivery Network (CDN). Stackpath may store web log information
				of site visitors, including IPs, UA, referrer, Location and ISP info of site visitors for 7 days.
				Files and images served by the CDN may be stored and served from countries other than your own.
				Stackpath’s privacy policy can be found %1$shere%2$s.',
					'wphb'
				),
				'<a href="https://www.stackpath.com/legal/privacy-statement/" target="_blank">',
				'</a>'
			)
		);

		wp_add_privacy_policy_content(
			__( 'Hummingbird', 'wphb' ),
			wp_kses_post( wpautop( $content, false ) )
		);
	}

}
