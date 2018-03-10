<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Admin_Notices
 */
class WP_Hummingbird_Admin_Notices {

	/**
	 * In order to avoid duplicated notices,
	 * we save notices IDs here
	 *
	 * @var    array $displayed_notices
	 * @access protected
	 */
	protected static $displayed_notices = array();

	/**
	 * Instance of class.
	 *
	 * @since  1.7.0
	 * @access private
	 * @var    $instance
	 */
	private static $instance = null;

	/**
	 * Store list of installed plugins.
	 *
	 * @since  1.7.0
	 * @access private
	 * @var    array $plugins  List of installed plugins.
	 */
	private $plugins = array();

	/**
	 * Return the plugin instance.
	 *
	 * @since 1.7.0
	 * @return WP_Hummingbird_Admin_Notices
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WP_Hummingbird_Admin_Notices constructor.
	 */
	public function __construct() {
		$dismiss = isset( $_GET['wphb-dismiss'] ) ? sanitize_text_field( $_GET['wphb-dismiss'] ) : false;
		if ( $dismiss ) {
			$this->dismiss( $dismiss );
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->plugins = get_plugins();

		// Only show notices to users who can do something about it (update, for example).
		$cap = is_multisite() ? 'manage_network_plugins' : 'update_plugins';
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		if ( is_multisite() ) {
			add_action( 'network_admin_notices', array( $this, 'upgrade_to_pro' ) );
			add_action( 'network_admin_notices', array( $this, 'free_version_deactivated' ) );
			add_action( 'network_admin_notices', array( $this, 'free_version_rate' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'upgrade_to_pro' ) );
			add_action( 'admin_notices', array( $this, 'free_version_deactivated' ) );
			add_action( 'admin_notices', array( $this, 'free_version_rate' ) );
			add_action( 'admin_notices', array( $this, 'clear_cache' ) );
		}

		add_action( 'activated_plugin', array( $this, 'plugin_changed' ) );
		add_action( 'deactivated_plugin', array( $this, 'plugin_changed' ) );
		add_action( 'after_switch_theme', array( $this, 'plugin_changed' ) );
	}

	/**
	 * Clear the notice blocker on plugin activate/deactivate.
	 *
	 * @since 1.7.0
	 * @used-by activated_plugin action
	 * @used-by deactivated_plugin action
	 */
	public function plugin_changed() {
		update_site_option( 'wphb-notice-cache-cleaned-show', 'yes' );
	}

	/**
	 * Display notice HTML code.
	 *
	 * @since  1.7.0
	 * @access private
	 * @param  string $id             Accepted: upgrade-to-pro, free-deactivated, free-rated.
	 * @param  string $message        Notice message.
	 * @param  bool   $additional     Additional content that goes after the message text.
	 * @param  bool   $only_hb_pages  Show message only on Hummingbird pages.
	 */
	private function show_notice( $id = '', $message = '', $additional = false, $only_hb_pages = false ) {
		// Only run on HB pages.
		$hb_pages = array(
			'toplevel_page_wphb',
			'hummingbird_page_wphb-performance',
			'hummingbird_page_wphb-minification',
			'hummingbird_page_wphb-caching',
			'hummingbird_page_wphb-gzip',
			'hummingbird_page_wphb-uptime',
			'toplevel_page_wphb-network',
			'hummingbird_page_wphb-performance-network',
			'hummingbird_page_wphb-minification-network',
			'hummingbird_page_wphb-caching-network',
			'hummingbird_page_wphb-gzip-network',
			'hummingbird_page_wphb-uptime-network',
		);

		if ( $only_hb_pages && ! in_array( get_current_screen()->id, $hb_pages, true ) ) {
			return;
		}

		$dismiss_url = wp_nonce_url( add_query_arg( 'wphb-dismiss', $id ), 'wphb-dismiss-notice' );
		?>
		<div class="notice-info notice wphb-notice">
			<p>
				<?php echo $message; ?>
				<a class="wphb-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>">
					<span class="dashicons dashicons-dismiss"></span>
					<span class="screen-reader-text">
						<?php esc_html_e( 'Dismiss this notice.', 'wphb' ); ?>
					</span>
				</a>
			</p>
			<?php if ( $additional ) : ?>
				<p>
					<?php echo $additional; ?>
				</p>
			<?php endif; ?>
		</div>
		<style>
			.wphb-notice .wphb-dismiss {
				text-decoration: none;
				float:right;
				color: #333333;
			}
		</style>
		<?php
	}

	/**
	 * Check if a notice has been dismissed by the current user.
	 *
	 * Will accept: 'user' for user options, 'option' for site wide options and
	 * 				'site' for sub site options.
	 *
	 * @since  1.7.0 changed to private
	 * @access private
	 * @param  string $notice  Notice.
	 * @param  string $mode    Default: 'user'.
	 * @return mixed
	 */
	private function is_dismissed( $notice, $mode = 'user' ) {
		if ( 'user' === $mode ) {
			return get_user_meta( get_current_user_id(), 'wphb-' . $notice . '-dismissed' );
		} elseif ( 'option' === $mode ) {
			return 'yes' !== get_option( 'wphb-notice-' . $notice . '-show' );
			//return 'yes' !== get_site_option( 'wphb-notice-' . $notice . '-show' );
		} //elseif ( 'site' === $mode ) {
		//	return 'yes' !== get_option( 'wphb-notice-' . $notice . '-show' );
		//}
	}

	/**
	 * Dismiss a notice.
	 *
	 * @since  1.7.0 changed to private
	 * @access private
	 * @param  string $notice  Notice.
	 */
	private function dismiss( $notice ) {
		check_admin_referer( 'wphb-dismiss-notice' );

		$user_notices = array(
			'upgrade-to-pro',
		);

		$options_notices = array(
			'free-deactivated',
			'free-rated',
			'cache-cleaned',
		);

		if ( in_array( $notice, $user_notices, true ) ) {
			update_user_meta( get_current_user_id(), 'wphb-' . $notice . '-dismissed', true );
		} elseif ( in_array( $notice, $options_notices, true ) ) {
			delete_site_option( 'wphb-notice-' . $notice . '-show' );
		}

		$redirect = remove_query_arg( array( 'wphb-dismiss', '_wpnonce' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Show info notice (HB style, not WP).
	 *
	 * @param string $id           Unique identifier for the notice.
	 * @param string $message      The notice text.
	 * @param string $class        Class for the notice wrapper.
	 * @param bool   $auto_hide    Auto hide notice.
	 * @param bool   $dismissable  If is dissmisable or not.
	 */
	public function show( $id, $message, $class = 'error', $auto_hide = false, $dismissable = false ) {
		// Is already dismissed ?
		if ( $dismissable && $this->is_dismissed( $id, 'option' ) ) {
			return;
		}

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			return;
		}

		if ( in_array( $id, self::$displayed_notices ) ) {
			return;
		}

		self::$displayed_notices[] = $id;

		?>
		<div class="wphb-notice wphb-notice-<?php echo $class; ?> can-close" <?php if ( $dismissable ) : ?>
			id="wphb-dismissable"
			data-id="<?php echo esc_attr( $id ); ?>"<?php endif; ?>>

			<p><?php echo $message; ?></p>

			<span class="close">
				<?php esc_html_e( 'Dismiss', 'wphb' ); ?>
			</span>
		</div>

		<?php if ( $auto_hide ) : ?>
			<script type="text/javascript">
				jQuery('.wphb-notice:not(.notice)').delay(3000).slideUp('slow');
			</script>
		<?php endif;
	}

	/***************************
	 * NOTICES
	 ***************************/

	/**
	 * Available notices.
	 *
	 * @see WP_Hummingbird_Admin_Notices::upgrade_to_pro()
	 * @see WP_Hummingbird_Admin_Notices::free_version_deactivated()
	 * @see WP_Hummingbird_Admin_Notices::free_version_rate()
	 */

	/**
	 * Show Upgrade to Pro notice
	 *
	 * User is authenticated into WPMU DEV but it has free version installed
	 */
	public function upgrade_to_pro() {
		if ( $this->is_dismissed( 'upgrade-to-pro' ) ) {
			return;
		}

		if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			return;
		}

		$dashboard = WPMUDEV_Dashboard::instance();
		if ( ! is_object( $dashboard ) ) {
			return;
		}

		if ( ! get_site_option( 'wphb-pro' ) && WP_Hummingbird_Utils::is_member() ) {
			$url = WPMUDEV_Dashboard::$ui->page_urls->plugins_url;
			/* translators: %s: Upgrade URL */
			$message = sprintf( __( 'Awww yeah! You’ve got access to Hummingbird Pro! Let’s upgrade your free version so you can start using premium features. <a href="%s">Upgrade</a>', 'wphb' ), esc_url( $url ) );
			$this->show_notice( 'upgrade-to-pro', $message, false, true );
		}
	}

	/**
	 * Notice displayed when the free version is deactivated because the pro one was already active
	 */
	public function free_version_deactivated() {
		if ( ! file_exists( WP_PLUGIN_DIR . '/hummingbird-performance/wp-hummingbird.php' ) ) {
			return;
		}

		if ( $this->is_dismissed( 'free-deactivated', 'option' ) ) {
			return;
		}

		$this->show_notice(
			'free-deactivated',
			__( 'We noticed you’re running both the free and pro versions of Hummingbird. No biggie! We’ve deactivated the free version for you. Enjoy the pro features!', 'wphb' )
		);
	}

	/**
	 * Offer the user to submit a review for the free version of the plugin.
	 *
	 * @since 1.5.4
	 */
	public function free_version_rate() {
		if ( get_site_option( 'wphb-pro' ) && WP_Hummingbird_Utils::is_member() ) {
			return;
		}

		if ( $this->is_dismissed( 'free-rated', 'option' ) ) {
			return;
		}

		// Show only if at least 7 days have past after installation of the free version.
		$now = current_time( 'timestamp' );
		$free_installation = get_site_option( 'wphb-free-install-date' );
		if ( ( $now - (int) $free_installation ) < 604800 ) {
			return;
		}

		$this->show_notice(
			'free-rated',
			__( "We've spent countless hours developing Hummingbird and making it free for you to use. We would really appreciate it if you dropped us a quick rating!", 'wphb' ),
			'<a href="https://wordpress.org/support/plugin/hummingbird-performance/reviews/" class="button" target="_blank">' . __( 'Rate Hummingbird', 'wphb' ) . '</a>'
		);
	}

	/**
	 * Show clear cache notice.
	 *
	 * @since 1.7.0
	 */
	public function clear_cache() {
		if ( wphb_cache_is_multisite() || $this->is_dismissed( 'cache-cleaned', 'option' ) ) {
			return;
		}

		// Only show if minification or page cache is enabled.
		$minify_active = false;
		if ( WP_Hummingbird_Utils::can_execute_php() ) {
			$minify = WP_Hummingbird_Utils::get_module( 'minify' );
			$minify_active = $minify->is_active();
		}
		$caching = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$caching_active = $caching->is_active();

		// If both modules disabled - don't show notice
		if ( ! $minify_active && ! $caching_active ) {
			return;
		}

		if ( $minify_active ) {
			// Clear cache button link
			$clear_cache_url = add_query_arg(
				array(
					'clear-cache' => 'true',
					'clear-pc'    => $caching_active,
				),
				WP_Hummingbird_Utils::get_admin_menu_url( 'minification' )
			);

			$text = __( "We've noticed you've made changes to your website and have Hummingbird's Asset Optimization feature active. You might want to clear cache to avoid any issues.", 'wphb' );

			if ( $caching_active ) {
				$text = __( "We've noticed you've made changes to your website and have Hummingbird's Asset Optimization and Page Caching features active. You might want to clear cache to avoid any issues.", 'wphb' );
			}
		} elseif ( $caching_active ) {
			// Clear cache button link
			$clear_cache_url = add_query_arg(
				array(
					'type' => 'pc-purge',
					'run'  => 'true',
				),
				WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) . '&view=main'
			);
			$clear_cache_url = wp_nonce_url( $clear_cache_url, 'wphb-run-caching' );

			$text = __( "We've noticed you've made changes to your website and have Hummingbird's Page Caching feature active. You might want to clear cache to avoid any issues.", 'wphb' );
		}

		$this->show_notice(
			'cache-cleaned',
			$text,
			'<a href="' . esc_url( $clear_cache_url ) . '" class="button">' . __( 'Clear Cache', 'wphb' ) . '</a>'
		);
	}

}