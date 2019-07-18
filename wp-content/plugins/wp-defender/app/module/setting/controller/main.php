<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Setting\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Setting\Model\Settings;

class Main extends Controller {
	protected $slug = 'wdf-setting';
	public $layout = 'layout';

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	public function __construct() {
		if ( $this->is_network_activate( wp_defender()->plugin_slug ) ) {
			$this->add_action( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->add_action( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->add_action( 'defender_enqueue_assets', 'scripts', 12 );
		}

		$this->add_ajax_action( 'saveSettings', 'saveSettings' );
		$this->add_ajax_action( 'wdResetSettings', 'resetSettings' );
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Settings", wp_defender()->domain ), esc_html__( "Settings", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			'actionIndex'
		) );
	}

	/**
	 * a simple router
	 */
	public function actionIndex() {
		$view = HTTP_Helper::retrieve_get( 'view' );
		switch ( $view ) {
			case 'general':
			default:
				$this->viewGeneral();
				break;
			case 'data':
				$this->viewData();
				break;
			case 'accessibility':
				$this->viewAccessibility();
				break;
		}
	}

	protected function viewGeneral() {
		$this->render( 'general', array(
			'settings' => Settings::instance()
		) );
	}

	protected function viewData() {
		$this->render( 'data', array(
			'settings' => Settings::instance()
		) );
	}

	protected function viewAccessibility() {
		$this->render( 'accessibility', array(
			'settings' => Settings::instance()
		) );
	}

	/**
	 * Enqueue scripts & styles
	 */
	public function scripts() {
		if ( $this->isInPage() || $this->isDashboard() ) {
			wp_enqueue_script( 'wpmudev-sui' );
			wp_enqueue_style( 'wpmudev-sui' );

			wp_enqueue_script( 'defender' );
			wp_enqueue_style( 'defender' );
			wp_enqueue_script( 'wd-settings', wp_defender()->getPluginUrl() . 'app/module/setting/js/script.js' );
		}
	}

	/**
	 * Saving settings in admin area
	 */
	public function saveSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieve_post( '_wpnonce' ), 'saveSettings' ) ) {
			return;
		}

		$data    = $_POST;
		$setting = Settings::instance();
		$setting->import( $data );
		$setting->save();

		$res           = array(
			'message' => __( "Your settings have been updated.", wp_defender()->domain )
		);
		$res['reload'] = 1;
		Utils::instance()->submitStatsToDev();
		wp_send_json_success( $res );
	}

	public function resetSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieve_post( '_wpnonce' ), 'wdResetSettings' ) ) {
			return;
		}

		$tweakFixed = \WP_Defender\Module\Hardener\Model\Settings::instance()->getFixed();

		foreach ( $tweakFixed as $rule ) {
			$rule->getService()->revert();
		}

		$cache = \Hammer\Helper\WP_Helper::getCache();
		$cache->delete( 'isActivated' );
		$cache->delete( 'wdf_isActivated' );
		$cache->delete( 'wdfchecksum' );
		$cache->delete( 'cleanchecksum' );

		\WP_Defender\Module\Scan\Model\Settings::instance()->delete();
		if ( class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			\WP_Defender\Module\Audit\Model\Settings::instance()->delete();
		}
		\WP_Defender\Module\Hardener\Model\Settings::instance()->delete();
		\WP_Defender\Module\IP_Lockout\Model\Settings::instance()->delete();
		\WP_Defender\Module\Advanced_Tools\Model\Auth_Settings::instance()->delete();
		\WP_Defender\Module\Advanced_Tools\Model\Mask_Settings::instance()->delete();
		Settings::instance()->delete();
		//clear old stuff
		delete_site_option( 'wp_defender' );
		delete_option( 'wp_defender' );
		delete_option( 'wd_db_version' );
		delete_site_option( 'wd_db_version' );

		$res = array(
			'message' => __( "Your settings have been reset.", wp_defender()->domain )
		);

		Utils::instance()->submitStatsToDev();
		wp_send_json_success( $res );
	}
}