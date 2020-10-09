<?php

namespace WP_Defender\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Controller;

class Tutorial extends Controller {
	public $slug = 'wdf-tutorial';

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'admin_menu' );
		} else {
			$this->addAction( 'admin_menu', 'admin_menu' );
		}

		if ( $this->isInPage() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}
	}

	public function admin_menu() {
		$cap    = is_multisite() ? 'manage_network_options' : 'manage_options';
		$action = 'actionIndex';
		add_submenu_page( 'wp-defender', esc_html__( 'Tutorials', wp_defender()->domain ),
			esc_html__( 'Tutorials', wp_defender()->domain ), $cap, $this->slug, array(
				&$this,
				$action
			) );
	}

	public function actionIndex() {
		$this->render( 'main' );
	}

	public function scripts() {
		wp_enqueue_style( 'wpmudev-sui' );
		wp_enqueue_style( 'defender' );
		wp_enqueue_media();
		wp_register_script( 'defender-tutorial', wp_defender()->getPluginUrl() . 'assets/app/tutorial.js', array(
			'def-vue',
			'defender',
			'wp-i18n'
		), wp_defender()->version, true );
		wp_localize_script( 'defender-tutorial', 'tutorial', $this->_scriptsData() );
		Utils::instance()->createTranslationJson( 'defender-tutorial' );
		wp_set_script_translations( 'defender-tutorial', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
		wp_enqueue_script( 'defender-tutorial' );
		wp_enqueue_script( 'wpmudev-sui' );
	}

	public function _scriptsData() {
		return array(
			'time_read'       => __( 'min read', wp_defender()->domain ),
			'title_read_link' => __( 'Read article', wp_defender()->domain ),
		);
	}
}