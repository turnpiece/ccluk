<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Setting\Controller;


use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Advanced_Tools\Component\Mask_Api;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Setting;
use WP_Defender\Module\Two_Factor\Model\Auth_Settings;

class Rest extends Controller {
	public function __construct() {
		$namespace = 'wp-defender/v1';
		$namespace .= '/settings';
		$routes    = [
			$namespace . '/updateSettings' => 'updateSettings',
			$namespace . '/resetSettings'  => 'resetSettings',
			$namespace . '/newConfig'      => 'newConfig',
			$namespace . '/deleteConfig'   => 'deleteConfig',
			$namespace . '/updateConfig'   => 'updateConfig',
			$namespace . '/applyConfig'    => 'applyConfig',
			$namespace . '/downloadConfig' => 'downloadConfig',
			$namespace . '/importConfig'   => 'importConfig'
		];
		$this->registerEndpoints( $routes, Setting::getClassName() );
	}

	public function resetSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'resetSettings' ) ) {
			return;
		}

		$hardener_settings = \WP_Defender\Module\Hardener\Model\Settings::instance();
		foreach ( $hardener_settings->getFixed() as $rule ) {
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
		$hardener_settings->delete();
		\WP_Defender\Module\IP_Lockout\Model\Settings::instance()->delete();
		Auth_Settings::instance()->delete();
		\WP_Defender\Module\Advanced_Tools\Model\Mask_Settings::instance()->delete();
		\WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings::instance()->delete();
		\WP_Defender\Module\Setting\Model\Settings::instance()->delete();

		//Disabled  Blacklist Monitor
		if ( ! wp_defender()->isFree && $this->hasMethod( 'toggleStatus' ) ) {
			$this->toggleStatus( null, false );
			delete_site_transient( \WP_Defender\Behavior\Blacklist::CACHE_KEY );
		}
		//clear old stuff
		delete_site_option( 'wp_defender' );
		delete_option( 'wp_defender' );
		delete_option( 'wd_db_version' );
		delete_site_option( 'wd_db_version' );

		delete_site_transient( 'wp_defender_free_is_activated' );
		delete_site_transient( 'wp_defender_is_activated' );
		delete_transient( 'wp_defender_free_is_activated' );
		delete_transient( 'wp_defender_is_activated' );

		delete_site_option( 'wp_defender_free_is_activated' );
		delete_site_option( 'wp_defender_is_activated' );
		delete_option( 'wp_defender_free_is_activated' );
		delete_option( 'wp_defender_is_activated' );

		$res = array(
			'message' => __( "Your settings have been reset.", wp_defender()->domain )
		);

		Utils::instance()->submitStatsToDev();
		wp_send_json_success( $res );
	}

	public function updateSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateSettings' ) ) {
			return;
		}
		$settings = Setting\Model\Settings::instance();
		$data     = stripslashes( $_POST['data'] );
		$data     = json_decode( $data, true );
		$settings->import( $data );
		$settings->save();
		$res = array(
			'message' => __( "Your settings have been updated.", wp_defender()->domain )
		);

		$this->submitStatsToDev();
		wp_send_json_success( $res );
	}

	public function importConfig() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'importConfig' ) ) {
			return;
		}
		$file     = $_FILES['file'];
		$tmp      = $file['tmp_name'];
		$content  = file_get_contents( $tmp );
		$importer = json_decode( $content, true );
		if ( ! is_array( $importer ) ) {
			wp_send_json_error( [
				'message' => __( 'The file is corrupted.', wp_defender()->domain )
			] );
		}

		if ( ! $this->validate_importer( $importer ) ) {
			wp_send_json_error( [
				'message' => __( 'An error occurred while importing the file. Please check your file or upload another file.', wp_defender()->domain )
			] );
		}

		//sanitize the files a bit
		$name    = strip_tags( $importer['name'] );
		$configs = [
			'name'     => $name,
			'immortal' => false
		];

		foreach ( $importer['configs'] as $slug => $module ) {
			$model = Setting\Component\Backup_Settings::moduleToModel( $slug );
			if ( ! is_object( $model ) ) {
				//this cae it is audit on free
				$configs['configs'][ $slug ] = [];
				continue;
			}
			$model->import( $module );
			if ( $slug === 'two_factor' ) {
				/**
				 * Sometime, the custom image broken when import, when that happen, we will fallback
				 * into default image
				 */
				if ( empty( $model->custom_graphic_url ) ) {
					//nothing here, surely it will cause broken, fall back to default
					$model->custom_graphic_url = wp_defender()->getPluginUrl() . 'assets/img/2factor-disabled.svg';
				} else {
					//image should be under wp-content/.., so we catch that part
					if ( preg_match( '/(\/wp-content\/.+)/', $model->custom_graphic_url, $matches ) ) {
						$rel_path = $matches[1];
						$rel_path = ltrim( $rel_path, '/' );
						$abs_path = ABSPATH . $rel_path;
						if ( ! file_exists( $abs_path ) ) {
							//fallback
							$model->custom_graphic_url = wp_defender()->getPluginUrl() . 'assets/img/2factor-disabled.svg';
						} else {
							//should replace with our site url
							$model->custom_graphic_url = get_site_url( null, $rel_path );
						}
					}
				}
			}
			$configs['configs'][ $slug ] = $model->exportByKeys( array_keys( $module ) );
		}
		$configs['description'] = isset( $importer['description'] ) && ! empty( $importer['description'] )
			? sanitize_textarea_field( $importer['description'] )
			: '';
		$tmp                    = Setting\Component\Backup_Settings::parseDataForImport( $configs['configs'] );
		$configs['strings']     = $tmp['strings'];
		$key                    = 'wp_defender_config_import_' . time();
		update_site_option( $key, $configs );
		Setting\Component\Backup_Settings::indexKey( $key );
		wp_send_json_success( [
			'message' => sprintf( __( '<strong>%s</strong> config has been uploaded successfully – you can now apply it to this site.',
				wp_defender()->domain ),
				$name ),
			'configs' => Setting\Component\Backup_Settings::getConfigs()
		] );
	}

	private function validate_importer( $importer ) {
		if ( ! isset( $importer['name'] ) ||
		     ! isset( $importer['configs'] ) || ! isset( $importer['strings'] )
		     || empty( $importer['name'] ) || empty( $importer['strings'] )
		) {
			return false;
		}
		//validate content
		//this is the current data, we use this for verify the schema
		$sample = Setting\Component\Backup_Settings::gatherData();
		foreach ( $importer['configs'] as $slug => $module ) {
			//this is not in the sample, file is invalid
			if ( ! isset( $sample[ $slug ] ) ) {
				return false;
			}
			$keys = array_keys( $sample[ $slug ] );

			$import_keys = array_keys( $module );
			$diff        = array_diff( $import_keys, $keys );
			if ( count( $diff ) ) {
				return false;
			}

			return true;
		}
	}

	public function newConfig() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'newConfig' ) ) {
			return;
		}

		$name = trim( HTTP_Helper::retrievePost( 'name' ) );
		$desc = wp_kses_post( HTTP_Helper::retrievePost( 'desc', '' ) );
		if ( empty( $name ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config name', wp_defender()->domain )
			] );
		}
		$name     = strip_tags( $name );
		$key      = 'wp_defender_config_' . time();
		$settings = Setting\Component\Backup_Settings::parseDataForImport();
		$data     = array_merge( [
			'name'        => $name,
			'immortal'    => false,
			'description' => $desc
		], $settings );
		unset( $data['labels'] );
		if ( update_site_option( $key, $data ) ) {
			Setting\Component\Backup_Settings::indexKey( $key );
			wp_send_json_success( [
				'message' => sprintf(
					__( '<strong>%s</strong> config saved successfully.', wp_defender()->domain ),
					$name
				),
				'configs' => Setting\Component\Backup_Settings::getConfigs()
			] );
		} else {
			wp_send_json_error( [
				'message' => __( 'An error occurred while saving your config. Please try it again.', wp_defender()->domain )
			] );
		}
	}

	public function downloadConfig() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'downloadConfig' ) ) {
			return;
		}

		$key = trim( HTTP_Helper::retrieveGet( 'key' ) );
		if ( empty( $key ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}

		$config = get_site_option( $key );
		if ( false === $config ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}
		$sample = Setting\Component\Backup_Settings::gatherData();
		foreach ( $sample as $slug => $data ) {
			foreach ( $data as $key => $val ) {
				if ( ! isset( $config['configs'][ $slug ][ $key ] ) ) {
					$config['configs'][ $slug ][ $key ] = null;
				}
			}
		}
		$json     = json_encode( $config );
		$filename = 'wp-defender-config-' . sanitize_file_name( $config['name'] ) . '.json';
		header( 'Content-disposition: attachment; filename=' . $filename );
		header( 'Content-type: application/json' );
		echo $json;
		exit;
	}

	public function applyConfig() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'applyConfig' ) ) {
			return;
		}

		$key = trim( HTTP_Helper::retrievePost( 'key' ) );
		if ( empty( $key ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}

		$config = get_site_option( $key );
		if ( false === $config ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}
		Setting\Component\Backup_Settings::makeConfigActive( $key );
		$need_reauth = Setting\Component\Backup_Settings::restoreData( $config['configs'] );
		$message     = sprintf( __( '<strong>%s</strong> config has been applied successfully.',
			wp_defender()->domain ),
			$config['name'] );
		$return      = [];
		if ( $need_reauth ) {
			$login_url = wp_login_url();
			if ( Mask_Settings::instance()->isEnabled() ) {
				$login_url = Mask_Api::getNewLoginUrl();
			}
			$message          .= '<br/>' . sprintf( __( 'Because of some security tweaks get applied, You will now need to <a href="%s"><strong>re-login</strong></a>.<br/>This will auto reload now',
					wp_defender()->domain ), $login_url );
			$return['reload'] = 3;
			$redirect         = urlencode( network_admin_url( 'admin.php?page=wdf-setting&view=configs' ) );
			if ( HTTP_Helper::retrievePost( 'screen' ) === 'dashboard' ) {
				$redirect = urlencode( network_admin_url( 'admin.php?page=wp-defender' ) );
			}
			$return['login_url'] = add_query_arg( 'redirect_to',
				$redirect, $login_url );
		}

		$return['message'] = $message;
		$return['configs'] = Setting\Component\Backup_Settings::getConfigs();

		wp_send_json_success( $return );
	}

	public function updateConfig() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateConfig' ) ) {
			return;
		}

		$name        = trim( HTTP_Helper::retrievePost( 'name' ) );
		$description = trim( HTTP_Helper::retrievePost( 'description' ) );
		$key         = trim( HTTP_Helper::retrievePost( 'key' ) );
		if ( empty( $name ) || empty( $key ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}

		$config = get_site_option( $key );
		if ( false === $config ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}

		$config['name']        = sanitize_text_field( $name );
		$config['description'] = sanitize_textarea_field( $description );
		if ( update_site_option( $key, $config ) ) {
			wp_send_json_success( [
				'message' => sprintf(
					__( '<strong>%s</strong> config saved successfully.', wp_defender()->domain ),
					$name
				),
				'configs' => Setting\Component\Backup_Settings::getConfigs()
			]);
		} else {
			wp_send_json_error( [
				'message' => __( 'An error occurred while saving your config. Please try it again.', wp_defender()->domain )
			] );
		}
	}

	public function deleteConfig() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'deleteConfig' ) ) {
			return;
		}

		$key = trim( HTTP_Helper::retrievePost( 'key' ) );
		if ( empty( $key ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid config', wp_defender()->domain )
			] );
		}
		if ( strpos( $key, 'wp_defender_config' ) === 0 ) {
			delete_site_option( $key );
			wp_send_json_success( [
				'configs' => Setting\Component\Backup_Settings::getConfigs()
			] );
		}
		wp_send_json_error( [
			'message' => __( 'Invalid config', wp_defender()->domain )
		] );
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = array(
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV',
			'blacklist' => wp_defender()->isFree ? '\WP_Defender\Behavior\Blacklist_Free' : '\WP_Defender\Behavior\Blacklist',
		);

		return $behaviors;
	}
}