<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Setting\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Setting;
use WP_Defender\Module\Setting\Model\Settings;

class Main extends Controller {
	protected $slug = 'wdf-setting';

	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = array(
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		);

		return $behaviors;
	}

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 12 );
		}
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Settings", wp_defender()->domain ),
			esc_html__( "Settings", wp_defender()->domain ), $cap, $this->slug, array(
				&$this,
				'actionIndex'
			) );
	}

	public function actionIndex() {
		$this->render( 'main' );
	}

	/**
	 * Enqueue scripts & styles
	 */
	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_style( 'defender' );

			wp_enqueue_script( 'defender' );
			wp_register_script( 'defender-settings', wp_defender()->getPluginUrl() . 'assets/app/settings.js', [
				'def-vue',
				'defender',
				'wp-i18n'
			], wp_defender()->version, true );
			wp_localize_script( 'defender-settings', 'wdSettings', $this->scriptsData() );
			Utils::instance()->createTranslationJson( 'defender-settings' );
			wp_set_script_translations( 'defender-settings', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
			wp_enqueue_script( 'defender-settings' );
			wp_enqueue_script( 'wpmudev-sui' );
		}
	}

	/**
	 * Export the data we need for front-end
	 * @return array
	 */
	public function scriptsData() {
		if ( ! $this->checkPermission() ) {
			return [];
		}
		$settings = Settings::instance();
		Setting\Component\Backup_Settings::maybeCreateDefaultConfig();
		$configs = Setting\Component\Backup_Settings::getConfigs();

		foreach ( $configs as &$config ) {
			//unset the data as we dont need it
			unset( $config['configs'] );
		}

		return [
			'configs'   => $configs,
			'model'     => [
				'general'       => $settings->exportByKeys( [
					'translate',
					'usage_tracking',
				] ),
				'data'          => $settings->exportByKeys( [
					'uninstall_settings',
					'uninstall_data'
				] ),
				'accessibility' => $settings->exportByKeys( [
					'high_contrast_mode'
				] )
			],
			'nonces'    => [
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
				'resetSettings'  => wp_create_nonce( 'resetSettings' ),
				'newConfig'      => wp_create_nonce( 'newConfig' ),
				'updateConfig'   => wp_create_nonce( 'updateConfig' ),
				'applyConfig'    => wp_create_nonce( 'applyConfig' ),
				'deleteConfig'   => wp_create_nonce( 'deleteConfig' ),
				'downloadConfig' => wp_create_nonce( 'downloadConfig' ),
				'importConfig'   => wp_create_nonce( 'importConfig' ),
			],
			'endpoints' => $this->getAllAvailableEndpoints( Setting::getClassName() ),
			'misc'      => [
				'setting_url' => network_admin_url( is_multisite() ? 'settings.php' : 'options-general.php' )
			]
		];
	}
}