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
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Only show notices to users who can do something about it (update, for example).
		$cap = is_multisite() ? 'manage_network_plugins' : 'update_plugins';
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		// This will show notice on both multisite and single site.
		add_action( 'admin_notices', array( $this, 'clear_cache' ) );

		if ( is_multisite() ) {
			add_action( 'network_admin_notices', array( $this, 'upgrade_to_pro' ) );
			add_action( 'network_admin_notices', array( $this, 'free_version_deactivated' ) );
			add_action( 'network_admin_notices', array( $this, 'free_version_rate' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'upgrade_to_pro' ) );
			add_action( 'admin_notices', array( $this, 'free_version_deactivated' ) );
			add_action( 'admin_notices', array( $this, 'free_version_rate' ) );
		}

		add_action( 'upgrader_process_complete', array( $this, 'plugin_changed' ) );
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
		$detection = WP_Hummingbird_Settings::get_setting( 'detection', 'page_cache' );

		// Do nothing selected in settings.
		if ( 'none' === $detection ) {
			return;
		}

		// Show notice.
		if ( 'manual' === $detection ) {
			update_option( 'wphb-notice-cache-cleaned-show', 'yes' );
			return;
		}

		// Auto clear cache, don't show any notice.
		if ( 'auto' === $detection ) {
			$modules = array( 'page_cache', 'minify' );
			foreach ( $modules as $mod ) {
				/* @var WP_Hummingbird_Module_Page_Cache|WP_Hummingbird_Module_Minify $module */
				$module = WP_Hummingbird_Utils::get_module( $mod );
				if ( ! $module->is_active() ) {
					continue;
				}

				// Make sure no settings are cleared during auto page cache purge.
				if ( 'minify' === $mod ) {
					$module->clear_cache( false );
				} else {
					$module->clear_cache();
				}
			}
		}
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
		if ( $only_hb_pages && ! preg_match( '/^(toplevel|hummingbird)(-pro)*_page_wphb/', get_current_screen()->id ) ) {
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
	 *              'site' for sub site options.
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
		}

		if ( 'option' === $mode ) {
			return 'yes' !== get_option( 'wphb-notice-' . $notice . '-show' );
		}
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
			delete_option( 'wphb-notice-' . $notice . '-show' );
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
	 * @param bool   $can_dismiss  If is dissmisable or not.
	 * @param bool   $notice_top   Show notice on top.
	 */
	public function show( $id, $message, $class = 'error', $can_dismiss = false, $notice_top = true ) {
		// Is already dismissed ?
		if ( $can_dismiss && $this->is_dismissed( $id, 'option' ) ) {
			return;
		}

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			return;
		}

		if ( in_array( $id, self::$displayed_notices, true ) ) {
			return;
		}

		self::$displayed_notices[] = $id;

		?>
		<div class="<?php echo $notice_top ? 'sui-notice-top ' : 'sui-notice '; ?>sui-notice-<?php echo esc_attr( $class ); ?> sui-can-dismiss" <?php if ( $can_dismiss ) : ?>
			id="wphb-dismissable"
			data-id="<?php echo esc_attr( $id ); ?>"<?php endif; ?>>
			<div class="sui-notice-content">
				<p><?php echo $message; ?></p>
			</div>
			<span class="sui-notice-dismiss">
				<?php if ( $notice_top ) : ?>
					<a role="button" href="#" aria-label="<?php esc_html_e( 'Dismiss', 'wphb' ); ?>" class="sui-icon-check"></a>
				<?php else : ?>
					<a role="button" href="#"><?php esc_html_e( 'Dismiss', 'wphb' ); ?></a>
				<?php endif; ?>
			</span>
		</div>

		<?php
	}

	/**
	 * *************************
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

		if ( defined( 'WPHB_WPORG' ) && WPHB_WPORG && WP_Hummingbird_Utils::is_member() ) {
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
		if ( WP_Hummingbird_Utils::is_member() ) {
			return;
		}

		if ( $this->is_dismissed( 'free-rated', 'option' ) ) {
			return;
		}

		// Show only if at least 7 days have past after installation of the free version.
		$now               = current_time( 'timestamp' );
		$free_installation = get_site_option( 'wphb-free-install-date' );
		if ( ( $now - (int) $free_installation ) < 604800 ) {
			return;
		}

		$this->show_notice(
			'free-rated',
			__( "We've spent countless hours developing Hummingbird and making it free for you to use. We would really appreciate it if you dropped us a quick rating!", 'wphb' ),
			'<a href="https://wordpress.org/support/plugin/hummingbird-performance/reviews/" class="sui-button sui-button-blue" target="_blank">' . __( 'Rate Hummingbird', 'wphb' ) . '</a>'
		);
	}

	/**
	 * Show clear cache notice.
	 *
	 * @since 1.7.0
	 */
	public function clear_cache() {
		if ( $this->is_dismissed( 'cache-cleaned', 'option' ) ) {
			return;
		}

		// Only show if minification or page cache is enabled.
		$minify_active = false;
		if ( WP_Hummingbird_Utils::can_execute_php() ) {
			$minify        = WP_Hummingbird_Utils::get_module( 'minify' );
			$minify_active = $minify->is_active();
		}
		$caching        = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$caching_active = $caching->is_active();

		// If both modules disabled - don't show notice.
		if ( ! $minify_active && ! $caching_active ) {
			return;
		}

		$text       = __( "We've noticed you've made changes to your website. We recommend you clear Hummingbird's page cache to avoid any issues.", 'wphb' );
		$additional = '';

		if ( $minify_active ) {
			// Add new files link.
			$recheck_file_url = add_query_arg(
				array(
					'recheck-files' => 'true',
				),
				WP_Hummingbird_Utils::get_admin_menu_url( 'minification' )
			);

			$text = __(
				"We've noticed you've made changes to your website. If you’ve installed new plugins or themes,
			we recommend you re-check Hummingbird's Asset Optimization configuration to ensure those new files are added
			correctly.",
				'wphb'
			);

			$additional .= '<a href="' . esc_url( $recheck_file_url ) . '" class="button button-primary" style="margin-right:10px">' . __( 'Re-check Asset Optimization', 'wphb' ) . '</a>';
		}

		$additional .= '<a href="#" id="wp-admin-notice-wphb-clear-cache" class="button">' . __( 'Clear Cache', 'wphb' ) . '</a>';
		if ( $caching_active ) {
			$adjust_settings_url = WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) . '&view=settings';
			if ( ! is_multisite() || ( is_multisite() && is_network_admin() ) ) {
				$additional .= '<a href="' . esc_url( $adjust_settings_url ) . '" style="color:#888;margin-left:10px;text-decoration:none">' . __( 'Adjust notification settings', 'wphb' ) . '</a>';
			}
		}

		$this->show_notice(
			'cache-cleaned',
			$text,
			$additional
		);
	}

}
