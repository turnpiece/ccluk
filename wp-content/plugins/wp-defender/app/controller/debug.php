<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Controller;

use Hammer\Base\Container;
use Hammer\Helper\HTTP_Helper;
use WP_Defender\Controller;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Component\Scanning;

class Debug extends Controller {
	protected $slug = 'wdf-debug';

	public function __construct() {
		if ( HTTP_Helper::retrieveGet( 'page' ) != 'wdf-debug' ) {
			return;
		}
		if ( $this->isInPage() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}

		$this->addAction( 'wp_loaded', 'clearTweaksCache' );
	}

	public function clearTweaksCache() {
		if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			return;
		}

		if ( isset( $_POST['_defnonce'] ) && wp_verify_nonce( $_POST['_defnonce'], 'flush_tweaks_cache' ) ) {
			$model = Settings::instance();
			$model->setDValues( 'head_requests', null );
		}
	}

	public function scripts() {
		wp_enqueue_style( 'defender' );
		wp_enqueue_script( 'wpmudev-sui' );
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Debug", wp_defender()->domain ), esc_html__( "Debug", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			'actionIndex'
		) );
	}

	public function actionIndex() {
		$cache    = Container::instance()->get( 'cache' );
		$scanning = new Scanning();
		$this->render( 'debug', [
			'core'     => $cache->get( Scan_Api::CACHE_CORE, [] ),
			'content'  => $cache->get( Scan_Api::CACHE_CONTENT, [] ),
			'progress' => $scanning->getScanProgress()
		] );
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}
}