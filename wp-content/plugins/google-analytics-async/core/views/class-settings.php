<?php

namespace Beehive\Core\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\View;
use Beehive\Core\Modules\Google_Analytics\Views\Settings as Analytics_Settings;

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
		// Display settings page.
		add_action( 'beehive_settings_page_content', [ $this, 'settings_page_content' ] );

		// Show success message.
		add_action( 'beehive_admin_settings_processed', [ $this, 'updated_message' ] );

		// Render onboarding modals.
		add_action( 'beehive_add_modals', [ $this, 'render_onboarding' ] );
	}

	/**
	 * Render side nav template for the settings page.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function sidenav() {
		// Side nav arguments.
		$args = [
			// Get current tab.
			'current' => Template::current_tab(),
			// Settings side nav.
			'tabs'    => Template::tabs(),
			'network' => $this->is_network(),
		];

		/**
		 * Filter hook to modify sidenav arguments.
		 *
		 * @param array $args Sidenav arguments.
		 *
		 * @since 3.2.0
		 */
		$args = apply_filters( 'beehive_view_sidenav_args', $args );

		// Render settings side nav.
		$this->view( 'settings/common/sidenav', $args );
	}

	/**
	 * Render settings page content for the site.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function settings_page() {
		// Current tab.
		$tab = Template::current_tab();

		// Render settings header.
		$this->view( 'settings/common/header' );

		// Render settings page.
		$this->view( 'settings/settings', [
			'title'    => Template::tabs()[ $tab ],
			'tab'      => $tab,
			'form_url' => Template::settings_page(
				$tab,
				$this->is_network()
			),
		] );

		// Render settings footer.
		$this->view( 'settings/common/footer' );
	}

	/**
	 * Get Beehive menu icon data.
	 *
	 * Get svg image instead of an image url.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		ob_start();
		?>
		<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 292 337.2" style="enable-background:new 0 0 292 337.2;" xml:space="preserve">
			<style type="text/css">
				.st0 {
					fill: #FFFFFF;
				}
			</style>
			<g id="Beehive">
				<polygon class="st0" points="125.4,153.4 177.1,201.3 183,195 145,121.7 	"/>
				<polygon class="st0" points="81,112.3 106.5,135.9 147,70.2 201.6,175.6 290.2,83.2 146,0 0,84.3 0,204.1 	"/>
				<polygon class="st0" points="292,170.4 292,116.8 213.9,199.2 224.6,220.1 	"/>
				<polygon class="st0" points="215.8,258.2 195.3,218.8 178.2,236.9 111.8,175.4 47.1,280 146,337.2 292,252.9 292,201.5 	"/>
				<polygon class="st0" points="93,158 82.8,148.7 0,242.2 0,252.9 25.5,267.6 	"/>
			</g>
		</svg>
		<?php
		$svg = ob_get_clean();

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Render settings page content for the plugin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function settings_page_content() {
		// Common items.
		$args = [
			'network' => $this->is_network(),
		];

		// Current tab.
		$tab = Template::current_tab();

		switch ( $tab ) {
			case 'reports':
				$roles = Permission::get_roles();
				// If subsites can not over write.
				if ( ! Permission::can_overwrite() && General::is_networkwide() ) {
					$selected = (array) beehive_analytics()->settings->get( 'roles', 'permissions', true, [] );
				} else {
					$selected = (array) beehive_analytics()->settings->get( 'roles', 'permissions', $this->is_network(), [] );
				}
				foreach ( $roles as $role => $data ) {
					// Leave administrators please. They are powerful.
					if ( 'administrator' === $role ) {
						continue;
					}

					if ( ! in_array( $role, $selected, true ) ) {
						unset( $roles[ $role ] );
					}
				}

				// Only applicable on single/sub site.
				$args['roles']               = $roles;
				$args['dashboard_tree']      = Analytics_Settings::instance()->dashboard_tree();
				$args['statistics_tree']     = Analytics_Settings::instance()->statistics_tree();
				$args['dashboard_selected']  = beehive_analytics()->settings->get( 'dashboard', 'reports', false, [] );
				$args['statistics_selected'] = beehive_analytics()->settings->get( 'statistics', 'reports', false, [] );
				break;
			case 'permissions':
				$args['roles'] = Permission::get_roles();
				break;
			case 'tracking':
				$args['settings_page']      = Template::settings_page( 'general', $this->is_network() );
				$args['tracking']           = beehive_analytics()->settings->get( 'code', 'tracking' );
				$args['network_tracking']   = beehive_analytics()->settings->get( 'code', 'tracking', true );
				$args['auto_tracking']      = beehive_analytics()->settings->get( 'auto_track', 'google', $this->is_network() );
				$args['auto_tracking_code'] = beehive_analytics()->settings->get( 'auto_track', 'misc', $this->is_network() );
				break;
			default:
				// Get Pro Sites levels.
				$args['ps_levels'] = $this->is_network() ? Permission::get_ps_levels() : false;
				break;
		}

		// Render network settings content.
		$this->view( "settings/tabs/{$tab}", $args );
	}

	/**
	 * Show success notification after settings updated.
	 *
	 * @since 3.2.0
	 */
	public function updated_message() {
		$tab = Template::current_tab();

		// Show updated notice.
		add_action( 'beehive_admin_top_notices', function () use ( $tab ) {
			switch ( $tab ) {
				case 'tracking':
					$this->notice( __( 'Tracking ID updated successfully.', 'ga_trans' ), 'success', true );
					break;
				default:
					$this->notice( __( 'Changes were saved successfully.', 'ga_trans' ), 'success', true );
					break;
			}
		} );
	}

	/**
	 * Add onboarding setup modal template.
	 *
	 * Render setup, finish, tracking screens.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function render_onboarding() {
		// Check if onboarding is already completed.
		$done = beehive_analytics()->settings->get( 'onboarding_done', 'misc', $this->is_network() );

		// Render modal.
		if ( ! $done ) {
			// Render onboarding setup modal.
			$this->view( 'modals/onboarding/setup', [
				'is_logged_in' => Google_Auth\Helper::instance()->is_logged_in( $this->is_network() ),
				'network'      => $this->is_network(),
				'ps_levels'    => $this->is_network() ? Permission::get_ps_levels() : false,
			] );
		}
	}
}