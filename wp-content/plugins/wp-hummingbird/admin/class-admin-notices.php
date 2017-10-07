<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Admin_Notices
 */
class WP_Hummingbird_Admin_Notices {

	/**
	 * Initialize admin notices
	 *
	 * @TODO: If we're going to add more notices we might want to use a better abstraction for this
	 */
	public function init() {
		$dismiss = isset( $_GET['wphb-dismiss'] ) ? sanitize_text_field( $_GET['wphb-dismiss'] ) : false;
		if ( $dismiss ) {
			$this->dismiss( $dismiss );
		}

		if ( is_multisite() ) {
			add_action( 'network_admin_notices', array( $this, 'upgrade_to_pro' ) );
			add_action( 'network_admin_notices', array( $this, 'free_version_deactivated' ) );
			add_action( 'network_admin_notices', array( $this, 'free_version_rate' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'upgrade_to_pro' ) );
			add_action( 'admin_notices', array( $this, 'free_version_deactivated' ) );
			add_action( 'admin_notices', array( $this, 'free_version_rate' ) );
		}
	}

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

		$url = WPMUDEV_Dashboard::$ui->page_urls->plugins_url;
		$cap = is_multisite() ? 'manage_network_plugins' : 'update_plugins';
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		$dismiss_url = wp_nonce_url( add_query_arg( 'wphb-dismiss', 'upgrade-to-pro' ), 'wphb-dismiss-notice' );

		if ( ! get_site_option( 'wphb-pro' ) && wphb_is_member() ) {
			?>
			<div class="notice-info notice wphb-notice">
				<p>
					<?php /* translators: %s: Upgrade URL */ ?>
					<?php printf( __( 'Awww yeah! You’ve got access to Hummingbird Pro! Let’s upgrade your free version so you can start using premium features. <a href="%s">Upgrade</a>', 'wphb' ), esc_url( $url ) ); ?>
					<a class="wphb-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-dismiss"></span></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Notice displayed when the free version is deactivated because the pro one was already active
	 */
	public function free_version_deactivated() {
		if ( ! array_key_exists( 'wp-hummingbird-wporg/wp-hummingbird.php', get_plugins() ) ) {
			return;
		}

		if ( $this->is_dismissed( 'free-deactivated', 'option' ) ) {
			return;
		}

		$cap = is_multisite() ? 'manage_network_plugins' : 'update_plugins';
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		$dismiss_url = wp_nonce_url( add_query_arg( 'wphb-dismiss', 'free-deactivated' ), 'wphb-dismiss-notice' );

		?>
		<div class="notice-info notice wphb-notice">
			<p>
				<?php esc_html_e( 'We noticed you’re running both the free and pro versions of Hummingbird. No biggie! We’ve deactivated the free version for you. Enjoy the pro features!', 'wphb' ); ?>
				<a class="wphb-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-dismiss"></span></a>
			</p>
		</div>
		<style>
			.wphb-notice .wphb-dismiss {
				text-decoration: none;
				float:right;
			}
		</style>
		<?php
	}

	/**
	 * Offer the user to submit a review for the free version of the plugin.
	 *
	 * @since 1.5.4
	 */
	public function free_version_rate() {
		if ( ! array_key_exists( 'wp-hummingbird-wporg/wp-hummingbird.php', get_plugins() ) ) {
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

		$cap = is_multisite() ? 'manage_network_plugins' : 'update_plugins';
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		$dismiss_url = wp_nonce_url( add_query_arg( 'wphb-dismiss', 'free-rated' ), 'wphb-dismiss-notice' );
		$review_url = 'https://wordpress.org/support/plugin/hummingbird-performance/reviews/';

		?>
		<div class="notice-info notice wphb-notice">
			<p>
				<?php esc_html_e( "We've spent countless hours developing this free plugin for you, and we would really appreciate it if you dropped us a quick rating!", 'wphb' ); ?>
				<a class="wphb-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-dismiss"></span></a>
			</p>
			<p>
				<a href="<?php echo esc_url( $review_url ); ?>" class="button" target="_blank"><?php esc_html_e( 'Rate Hummingbird', 'wphb' ); ?></a>
			</p>
		</div>
		<style>
			.wphb-notice .wphb-dismiss {
				text-decoration: none;
				float:right;
			}
		</style>
		<?php
	}

	/**
	 * Check if a notice has been dismissed by the current user.
	 *
	 * @param string $notice  Notice.
	 * @param string $mode    Default: user.
	 *
	 * @return mixed
	 */
	public function is_dismissed( $notice, $mode = 'user' ) {
		if ( 'user' === $mode ) {
			return get_user_meta( get_current_user_id(), 'wphb-' . $notice . '-dismissed' );
		} else {
			return 'yes' !== get_site_option( 'wphb-notice-' . $notice . '-show' );
		}
	}

	/**
	 * Dismiss a notice.
	 *
	 * @param string $notice  Notice.
	 */
	public function dismiss( $notice ) {
		check_admin_referer( 'wphb-dismiss-notice' );

		$user_notices = array(
			'upgrade-to-pro',
		);

		$options_notices = array(
			'free-deactivated',
			'free-rated',
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

}