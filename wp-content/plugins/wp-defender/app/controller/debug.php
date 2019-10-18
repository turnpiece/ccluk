<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Controller;
use WP_Defender\Module\Scan\Component\Scan_Api;

class Debug extends Controller {
	protected $slug = 'wdf-debug';

	public function __construct() {
		if ( HTTP_Helper::retrieveGet( 'page' ) != 'wdf-debug' ) {
			return;
		}

		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}
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
		$this->render( 'debug' );
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