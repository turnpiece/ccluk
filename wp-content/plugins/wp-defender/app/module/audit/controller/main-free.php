<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Controller;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\Log_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Module\Audit\Component\Audit_API;
use WP_Defender\Module\Audit\Component\Audit_Table;
use WP_Defender\Module\Audit\Model\Settings;
use WP_Defender\Vendor\Email_Search;

class Main_Free extends \WP_Defender\Controller {
	protected $slug = 'wdf-logging';

	public function __construct() {
		if ( $this->is_network_activate( wp_defender()->plugin_slug ) ) {
			$this->add_action( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->add_action( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->add_action( 'defender_enqueue_assets', 'scripts', 11 );
		}
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Audit Logging", wp_defender()->domain ), esc_html__( "Audit Logging", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			'actionIndex'
		) );
	}

	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_script( 'wpmudev-sui' );
			wp_enqueue_style( 'wpmudev-sui' );
			//wp_enqueue_script( 'defender' );
			wp_enqueue_style( 'defender' );
		}
	}

	public function actionIndex() {
		$this->renderPartial( 'free' );
	}
}