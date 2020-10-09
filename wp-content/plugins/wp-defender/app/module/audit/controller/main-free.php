<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Controller;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\Log_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Audit\Component\Audit_API;
use WP_Defender\Module\Audit\Model\Settings;

class Main_Free extends \WP_Defender\Controller {
	protected $slug = 'wdf-logging';

	/**
	 * Declaring behaviors
	 * @return array
	 */
	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = [
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		];

		return $behaviors;
	}

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
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
			wp_enqueue_style( 'wpmudev-sui' );
			wp_enqueue_style( 'defender' );

			wp_register_script( 'defender-audit', wp_defender()->getPluginUrl() . 'assets/app/audit.js', array(
				'def-vue',
				'defender',
				'wp-i18n'
			), wp_defender()->version, true );
			Utils::instance()->createTranslationJson( 'defender-audit' );
			wp_set_script_translations( 'defender-audit', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
			wp_localize_script( 'defender-audit', 'auditData', [] );
			wp_enqueue_script( 'defender-audit' );
			wp_enqueue_script( 'wpmudev-sui' );
		}
	}

	public function actionIndex() {
		$this->renderPartial( 'main-free' );
	}
}