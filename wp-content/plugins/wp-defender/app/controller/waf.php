<?php

namespace WP_Defender\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Controller;

class Waf extends Controller {
	public $slug = 'wdf-waf';

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'admin_menu' );
		} else {
			$this->addAction( 'admin_menu', 'admin_menu' );
		}

		if ( $this->isInPage() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}
		$this->addAjaxAction( 'wp-defender/v1/waf/recheck', 'recheck' );
	}

	public function admin_menu() {
		$cap    = is_multisite() ? 'manage_network_options' : 'manage_options';
		$action = "actionIndex";
		if ( $this->maybe_show_widget() ) {
			add_submenu_page( 'wp-defender', esc_html__( "WAF", wp_defender()->domain ),
				esc_html__( "WAF", wp_defender()->domain ), $cap, $this->slug, array(
					&$this,
					$action
				) );
		}
	}

	public function actionIndex() {
		$this->render( 'main' );
	}

	public function scripts() {
		wp_enqueue_style( 'wpmudev-sui' );
		wp_enqueue_style( 'defender' );
		wp_enqueue_media();
		wp_register_script( 'defender-waf', wp_defender()->getPluginUrl() . 'assets/app/waf.js', array(
			'def-vue',
			'defender',
			'wp-i18n'
		), wp_defender()->version, true );
		wp_localize_script( 'defender-waf', 'waf', $this->_scriptsData() );
		Utils::instance()->createTranslationJson( 'defender-waf' );
		wp_set_script_translations( 'defender-waf', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
		wp_enqueue_script( 'defender-waf' );
		wp_enqueue_script( 'wpmudev-sui' );
	}

	/**
	 * Checks whether we're on WPMU DEV Hosting
	 *
	 * @return bool
	 */
	public function is_wpmu_hosting() {
		return isset( $_SERVER['WPMUDEV_HOSTED'] ) && ! empty( $_SERVER['WPMUDEV_HOSTED'] );
	}

	public function recheck() {
		if ( ! Utils::instance()->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'recheckWaf' ) ) {
			return;
		}
		delete_site_transient( 'def_waf_status' );
		$data = $this->_scriptsData();
		wp_send_json_success( [
			'waf' => $data['waf'],
		] );
	}

	/**
	 * @param $site_id
	 *
	 * @return bool|mixed
	 */
	public function get_waf_status( $site_id ) {
		$cached = get_site_transient( 'def_waf_status' );
		if ( in_array( $cached, [ 'enabled', 'disabled' ] ) ) {
			return $cached === 'enabled';
		}

		$url = "https://premium.wpmudev.org/api/hub/v1/sites/$site_id/modules/hosting";
		$ret = Utils::instance()->devCall( $url );
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}
		$status = $ret['waf']['is_active'];
		set_site_transient( 'def_waf_status', $status === true ? 'enabled' : 'disabled', 300 );

		return $status;
	}

	public function maybe_show_modal() {
		$show = get_site_option( 'waf_show_new_feature' );
		//hide it if the site is Hosted && Enable Whitelabel
		if ( $this->is_wpmu_hosting() && WPMUDEV::instance()->is_whitelabel_enabled() ) {
			return false;
		}
		if (
			//not hosted on us
			! $this->is_wpmu_hosting()
			//is pro
			&& wp_defender()->isFree == false
			//and enable whitelabel
			&& WPMUDEV::instance()->is_whitelabel_enabled()
		) {
			//hide it
			return false;
		}

		return $show;
	}

	/**
	 * @return bool
	 */
	public function maybe_show_widget() {
		if (
			//not hosted on us
			! $this->is_wpmu_hosting()
			//is pro
			&& wp_defender()->isFree == false
			//and enable whitelabel
			&& WPMUDEV::instance()->is_whitelabel_enabled()
		) {
			//hide it
			return false;
		}

		return true;
	}

	public function _scriptsData() {
		$site_id    = null;
		$waf_status = false;
		if ( class_exists( '\WPMUDEV_Dashboard' ) ) {
			$site_id = \WPMUDEV_Dashboard::$api->get_site_id();
			if ( $this->is_wpmu_hosting() ) {
				$waf_status = $this->get_waf_status( $site_id );
				if ( is_wp_error( $waf_status ) ) {
					//false safe
					$waf_status = false;
				}
			}
		}

		return [
			'site_id'   => $site_id,
			'waf'       => [
				'hosted'            => $this->is_wpmu_hosting(),
				'status'            => $waf_status,
				'maybe_show'        => $this->maybe_show_widget(),
				'whitelabel_enable' => WPMUDEV::instance()->is_whitelabel_enabled(),
			],
			'nonces'    => [
				'recheck' => wp_create_nonce( 'recheckWaf' )
			],
			'endpoints' => [
				'recheck' => 'wp-defender/v1/waf/recheck'
			]
		];
	}
}