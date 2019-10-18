<?php

namespace Beehive\Core\Modules\Google_Auth\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\View;
use Beehive\Core\Modules\Google_Analytics;

/**
 * The admin view class of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Settings extends View {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Render modals.
		add_action( 'beehive_add_modals', [ $this, 'account_modals' ] );

		// Show Google auth callback notice.
		add_action( 'beehive_google_auth_completed', [ $this, 'auth_callback_notice' ], 10, 3 );

		// Google re-auth notification.
		add_action( 'network_admin_notices', [ $this, 'reauth_notice' ] );
		add_action( 'network_admin_notices', [ $this, 'setup_notice' ] );

		// Google authentication notifications.
		add_action( 'admin_notices', [ $this, 'reauth_notice' ] );
		add_action( 'admin_notices', [ $this, 'setup_notice' ] );

		// Display Google API setup data.
		add_action( 'beehive_settings_google_settings', [ $this, 'google_account_content' ] );

		// Display Google setup content for modal.
		add_action( 'beehive_onboarding_google_settings', [ $this, 'onboarding_google_account_content' ] );

		// Display re-authentication message.
		add_action( 'beehive_google_setup_notice', [ $this, 'reauth_message' ] );
	}

	/**
	 * Render Google authorization area content.
	 *
	 * This is where we ask for API credentials or login with Google.
	 * If already logged in, we will show available accounts.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function google_account_content() {
		// Is logged in with Google?.
		$logged_in = Google_Auth\Helper::instance()->is_logged_in( $this->is_network() );

		if ( $logged_in ) {
			// Render account template.
			$this->view( 'settings/google/account', [
				'network'       => $this->is_network(),
				'accounts'      => $this->profiles_list( $this->is_network() ),
				'user'          => Google_Auth\Data::instance()->user( $this->is_network() ),
				// Tracking ID.
				'tracking_code' => beehive_analytics()->settings->get( 'code', 'tracking', $this->is_network() ),
			] );
		} else {
			// Google account setup form.
			$this->google_settings();
		}
	}

	/**
	 * Render Google authorization area content for modal.
	 *
	 * This is where we ask for API credentials or login with Google.
	 * If already logged in, we will show available accounts.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function onboarding_google_account_content() {
		$network = $this->is_network();

		// Is logged in with Google?.
		$logged_in = Google_Auth\Helper::instance()->is_logged_in( $network );

		if ( $logged_in ) {
			// Render account template.
			$this->view( 'modals/onboarding/google/account', [
				'network'       => $network,
				'accounts'      => $this->profiles_list( $network ),
				'roles'         => Permission::get_roles(),
				// Tracking ID.
				'tracking_code' => beehive_analytics()->settings->get( 'code', 'tracking', $network ),
			] );
		} else {
			// Render account settings template.
			$this->google_settings( true );
		}
	}

	/**
	 * Google account setup and authentication form.
	 *
	 * Render Google API credentials form or Connect with Google
	 * option.
	 *
	 * @param bool $onboarding Is called in onboarding page?.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function google_settings( $onboarding = false ) {
		// Template base path.
		$base_path = $onboarding ? 'modals/onboarding/google' : 'settings/google';
		// Helper instance.
		$google_helper = Google_Auth\Helper::instance();

		if ( is_multisite() ) {
			// Network admin.
			if ( $this->is_network() ) {
				// Render API settings template for network admin.
				$this->view( "{$base_path}/network-setup", [
					'login_url' => $google_helper->auth_url( true, true ),
				] );
			} else {
				// Logged in network admin.
				$network_logged_in = $google_helper->is_logged_in( true );
				// Plugin active networkwide.
				$networkwide = General::is_networkwide();
				// API creds setup in network admin.
				$network_setup = $google_helper->is_setup( true );

				// Get login url.
				if ( $network_logged_in && $networkwide && $network_setup ) {
					$login_url = $google_helper->auth_url( true, false, true );
				} else {
					$login_url = $google_helper->auth_url( false, true );
				}

				// Render API settings template for subsite admin.
				$this->view( "{$base_path}/subsite-setup", [
					'network_logged_in' => $network_logged_in,
					'networkwide'       => $networkwide,
					'network_setup'     => $network_setup,
					'login_url'         => $login_url,
				] );
			}
		} else {
			// Render API settings template for single site admin.
			$this->view( "{$base_path}/single-setup", [
				'login_url' => $google_helper->auth_url( false, true ),
			] );
		}
	}

	/**
	 * Show re-authentication required notice when required.
	 *
	 * This message will be shown on top of the API setup form.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function reauth_message() {
		// Only when required.
		if ( Google_Auth\Helper::instance()->reauth_required( $this->is_network() ) ) {
			$this->notice(
				esc_html__( 'It appears the connection with your Google Analytics account has been broken. Re-authenticate with Google to continue viewing analytics in your Dashboard.', 'ga_trans' ),
				'error',
				false
			);
		}
	}

	/**
	 * Show re-authentication required notice when required.
	 *
	 * If the access token failed or refresh failed, we may need
	 * to ask the user to reauthenticate with Google.
	 * This message will not be shown in our settings page main tab
	 * because there is another notice within Google connect section.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function reauth_notice() {
		// Only when the user has permission.
		if ( Permission::user_can( 'settings', $this->is_network() ) ) {
			// Continue only when required.
			if ( Google_Auth\Helper::instance()->reauth_required( $this->is_network() ) // Only when reauthentication required.
			     && ( ! General::is_plugin_admin() || Template::current_tab() !== 'general' ) // No need within settings tab.
			     && ! Template::notice_dismissed( 'google_reauth' )
			) {
				?>
				<div class="error notice is-dismissible" data-type="google_reauth">
					<p><?php printf( esc_html__( 'It appears the connection with your Google Analytics account has been broken. Re-authenticate with Google in %s’s settings to continue viewing analytics in your Dashboard.', 'ga_trans' ), esc_attr( General::plugin_name() ) ); ?></p>
					<p>
						<a href="<?php echo esc_url( Template::settings_page( 'general', $this->is_network() ) ); ?>" class="button"><?php esc_html_e( 'Re-Authenticate Account', 'ga_trans' ); ?></a>
					</p>
				</div>
				<?php
				// Enqueue custom admin notice script.
				wp_enqueue_script( 'beehive_admin_notice' );
			}
		}
	}

	/**
	 * Show Google connect notice when user is not logged in.
	 *
	 * Show this only if they did not dismiss.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup_notice() {
		// Only when the user has permission.
		if ( Permission::user_can( 'settings', $this->is_network() ) ) {
			// Continue only when required.
			if ( Google_Auth\Helper::instance()->setup_required( $this->is_network() ) // Only when reauthentication required.
			     && ( ! General::is_plugin_admin() || Template::current_tab() !== 'general' ) // No need within settings tab.
			     && ! Template::notice_dismissed( 'google_setup' )
			) {
				?>
				<div class="error notice is-dismissible" data-type="google_setup">
					<p><?php printf( esc_html__( 'It appears you haven’t finished linking up your Google Analytics account with %s. Finish the setup to view analytics in your Dashboard.', 'ga_trans' ), esc_attr( General::plugin_name() ) ); ?></p>
					<p>
						<a href="<?php echo esc_url( Template::settings_page( 'general', $this->is_network() ) ); ?>" class="button button-primary"><?php esc_html_e( 'Connect Account', 'ga_trans' ); ?></a>
					</p>
				</div>
				<?php
				// Enqueue custom admin notice script.
				wp_enqueue_script( 'beehive_admin_notice' );
			}
		}
	}

	/**
	 * Show success for error notice after authentication.
	 *
	 * @param bool $success Was it success or fail?.
	 * @param bool $default Did we connect using default credentials?.
	 * @param bool $modal   Is this is from modal.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function auth_callback_notice( $success, $default, $modal ) {
		// No need to show alert when it is in modal.
		if ( ! $modal ) {
			// Set the message.
			if ( $success ) {
				$message = __( 'We authorized your account successfully. Choose your website below for displaying analytics.', 'ga_trans' );
			} elseif ( $default ) {
				$message = sprintf(
					__( 'We couldn\'t connect your Google account. Please try reconnecting with the "Connect" button below. Alternately, you can set up a <a href="%s" target="_blank">new API project</a> with Google and use that instead. If you\'re still stuck you can <a href="%s" target="_blank">contact support</a> for assistance.', 'ga_trans' ),
					'https://premium.wpmudev.org/docs/wpmu-dev-plugins/beehive/#set-up-api-project',
					'https://premium.wpmudev.org/get-support/'
				);
			} else {
				$message = sprintf(
					__( 'We couldn\'t authorize your Google account. Please fill in your API information again, or connect with Google using the button below in side tab. If you\'re still stuck, please <a href="%s" target="_blank">contact support</a> for assistance.', 'ga_trans' ),
					'https://premium.wpmudev.org/get-support/'
				);
			}

			/**
			 * Filter to modify the notification message.
			 *
			 * @param string $message Message.
			 * @param bool   $success Is it success or fail?.
			 *
			 * @since 3.2.0
			 */
			$message = apply_filters( 'beehive_auth_callback_notice_message', $message, $success );

			// Show notification.
			add_action( 'beehive_admin_top_notices', function () use ( $message, $success ) {
				$this->notice( $message, $success ? 'success' : 'error' );
			} );
		}
	}

	/**
	 * Add account actions modals if required.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function account_modals() {
		// Continue only when account is ready.
		if ( Google_Auth\Helper::instance()->is_logged_in( $this->is_network() ) ) {
			// Add logout confirmation modal.
			$this->view( 'modals/google/logout' );
			// Add switch profile confirmation modal.
			$this->view( 'modals/google/switch' );
		}
	}

	/**
	 * Get available profiles from current GA account.
	 *
	 * This is a wrapper function to display drodowns in plugin
	 * admin pages.
	 *
	 * @param bool $network Is network wide?.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function profiles_list( $network = false ) {
		$list = [];

		// Get available profiles.
		$profiles = Google_Analytics\Data::instance()->profiles( $network );

		// Get settings.
		$settings = beehive_analytics()->settings->get_options( false, $network );

		// Current website url.
		$current_url = untrailingslashit( get_site_url() );

		foreach ( $profiles as $profile ) {
			// Get profile website url.
			$website_url = untrailingslashit( $profile->getWebsiteUrl() );

			// Perform some extra actions if website url is matching.
			if ( $current_url === $website_url ) {
				// Set tracking ID.
				if ( ! empty( $settings['google']['auto_track'] ) && empty( $settings['tracking']['code'] ) ) {
					$settings['misc']['auto_track'] = $profile->getWebPropertyId();
				}

				// Update account id if website url is matched.
				if ( empty( $settings['google']['account_id'] ) ) {
					$settings['google']['account_id'] = $profile->getId();
				}

				// Update settings.
				beehive_analytics()->settings->update_options( $settings, $network );
			}

			// Profile id as key and url and property id as value.
			$list[ $profile->getId() ] = $profile->getWebsiteUrl() . ' (' . $profile->getName() . ' - ' . $profile->getWebPropertyId() . ')';
		}

		/**
		 * Filter hook to modify available profiles dropdown.
		 *
		 * @param array $profiles Profiles list.
		 * @param bool  $network  Is network level.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_profiles_list', $list, $network );
	}
}