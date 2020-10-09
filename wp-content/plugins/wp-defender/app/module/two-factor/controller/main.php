<?php

namespace WP_Defender\Module\Two_Factor\Controller;

use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Two_Factor\Component\Auth_Listener;
use WP_Defender\Module\Two_Factor\Model\Auth_Settings;

class Main extends Controller {
	protected $slug = 'wdf-2fa';

	public function behaviors() {
		$behaviors = array(
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		);
		if ( wp_defender()->isFree == false ) {
			$behaviors['pro'] = '\WP_Defender\Module\IP_Lockout\Behavior\Pro\Reporting';
		}

		return $behaviors;
	}

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}
		$this->addAction( 'defender_enqueue_assets', 'scripts', 12 );
		new Auth_Listener();
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap    = is_multisite() ? 'manage_network_options' : 'manage_options';
		$action = "actionIndex";
		add_submenu_page( 'wp-defender', esc_html__( "2FA", wp_defender()->domain ), esc_html__( "2FA", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			$action
		) );
	}

	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_style( 'wpmudev-sui' );
			wp_enqueue_style( 'defender' );
			wp_enqueue_media();
			wp_register_script( 'defender-2fa', wp_defender()->getPluginUrl() . 'assets/app/two-fa.js', array(
				'def-vue',
				'defender',
				'wp-i18n'
			), wp_defender()->version, true );
			wp_localize_script( 'defender-2fa', 'two_fa', $this->_scriptsData() );
			Utils::instance()->createTranslationJson( 'defender-2fa' );
			wp_set_script_translations( 'defender-2fa', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
			wp_enqueue_script( 'defender-2fa' );
			wp_enqueue_script( 'wpmudev-sui' );
		}
	}

	public function _scriptsData() {
		$query    = new \WP_User_Query( [
			//look over the network
			'blog_id'    => 0,
			'meta_key'   => 'defenderAuthOn',
			'meta_value' => true
		] );
		$settings = Auth_Settings::instance();

		return [
			'misc'      => [
				'all_roles'     => get_editable_roles(),
				'compatibility' => isset( wp_defender()->global['compatibility'] ) && is_array( wp_defender()->global['compatibility'] ) ? wp_defender()->global['compatibility'] : false,
				'total'         => $query->get_total(),
			],
			'model'     => $settings->export(),
			'nonces'    => [
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
				'sendTestEmail'  => wp_create_nonce( 'sendTestEmail' )
			],
			'endpoints' => $this->getAllAvailableEndpoints( \WP_Defender\Module\Two_Factor::getClassName() ),
		];
	}

	public function actionIndex() {
		$this->render( 'main' );
	}
}