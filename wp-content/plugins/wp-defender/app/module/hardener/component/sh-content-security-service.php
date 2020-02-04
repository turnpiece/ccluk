<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class Sh_Content_Security_Service extends Rule_Service implements IRule_Service {
	//the data we use for real
	const KEY_DATA = 'sh_content_security',
		//this is the data showing in test pharse
		KEY_TEMP_DATA = 'sh_content_security_temp',
		//this is the data queue for approval, passing from test
		KEY_STAGING_DATA = 'sh_content_security_staging';

	/**
	 * @return bool
	 */
	public function check() {
		$is_test    = isset( $_COOKIE[ Sh_Content_Security::$slug . '-testing' ] );
		$is_staging = isset( $_COOKIE[ Sh_Content_Security::$slug . '-staging' ] );

		if ( $is_staging || $is_test ) {
			//this case we are in staging mode, so just return base on the current screen
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY_DATA );
			if ( is_array( $data ) ) {
				$data = array_filter( $data );
			}
			unset( $data['somewhere'] );
			if ( ! ! $data == false || empty( $data ) ) {
				return false;
			}

			$keys = $this->getKeys();
			foreach ( $keys as $key ) {
				if ( isset( $data[ $key ] ) && $data[ $key ] == 1 ) {

					return true;
				}
			}

			return false;
		}

		$response = wp_remote_head( network_site_url() );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$headers = $response['headers'];
		if ( isset( $headers['content-security-policy'] ) ) {
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY_DATA );
			if ( $data === null ) {
				$data['somewhere'] = true;
				$settings->setDValues( self::KEY_DATA, $data );
			}

			return true;
		}

		return false;
	}

	public function getKeys() {
		$keys = [
			'base_uri',
			'child_src',
			'default_src',
			'font_src',
			'frame_ancestors',
			'form_action',
			'img_src',
			'media_src',
			'object_src',
			'plugin_types',
			'sandbox',
			'script_src',
			'style_src',
			'worker_src',
		];

		return $keys;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$params   = [
			'base_uri'               => is_array( $_POST ) && isset( $_POST['base_uri'] ) ? $_POST['base_uri'] : 0,
			'child_src'              => is_array( $_POST ) && isset( $_POST['child_src'] ) ? $_POST['child_src'] : 0,
			'default_src'            => is_array( $_POST ) && isset( $_POST['default_src'] ) ? $_POST['default_src'] : 0,
			'font_src'               => is_array( $_POST ) && isset( $_POST['font_src'] ) ? $_POST['font_src'] : 0,
			'frame_ancestors'        => is_array( $_POST ) && isset( $_POST['frame_ancestors'] ) ? $_POST['frame_ancestors'] : 0,
			'form_action'            => is_array( $_POST ) && isset( $_POST['form_action'] ) ? $_POST['form_action'] : 0,
			'img_src'                => is_array( $_POST ) && isset( $_POST['img_src'] ) ? $_POST['img_src'] : 0,
			'media_src'              => is_array( $_POST ) && isset( $_POST['media_src'] ) ? $_POST['media_src'] : 0,
			'object_src'             => is_array( $_POST ) && isset( $_POST['object_src'] ) ? $_POST['object_src'] : 0,
			'plugin_types'           => is_array( $_POST ) && isset( $_POST['plugin_types'] ) ? $_POST['plugin_types'] : 0,
			'sandbox'                => is_array( $_POST ) && isset( $_POST['sandbox'] ) ? $_POST['sandbox'] : 0,
			'script_src'             => is_array( $_POST ) && isset( $_POST['script_src'] ) ? $_POST['script_src'] : 0,
			'style_src'              => is_array( $_POST ) && isset( $_POST['style_src'] ) ? $_POST['style_src'] : 0,
			'worker_src'             => is_array( $_POST ) && isset( $_POST['worker_src'] ) ? $_POST['worker_src'] : 0,
			'url_ignores'            => is_array( $_POST ) && isset( $_POST['url_ignores'] ) ? $_POST['url_ignores'] : "",
			'base_uri_values'        => is_array( $_POST ) && isset( $_POST['base_uri_values'] ) ? $_POST['base_uri_values'] : [],
			'child_src_values'       => is_array( $_POST ) && isset( $_POST['child_src_values'] ) ? $_POST['child_src_values'] : [],
			'frame_ancestors_values' => is_array( $_POST ) && isset( $_POST['frame_ancestors_values'] ) ? $_POST['frame_ancestors_values'] : [],
			'default_src_values'     => is_array( $_POST ) && isset( $_POST['default_src_values'] ) ? $_POST['default_src_values'] : [],
			'font_src_values'        => is_array( $_POST ) && isset( $_POST['font_src_values'] ) ? $_POST['font_src_values'] : [],
			'form_action_values'     => is_array( $_POST ) && isset( $_POST['form_action_values'] ) ? $_POST['form_action_values'] : [],
			'img_src_values'         => is_array( $_POST ) && isset( $_POST['img_src_values'] ) ? $_POST['img_src_values'] : [],
			'media_src_values'       => is_array( $_POST ) && isset( $_POST['media_src_values'] ) ? $_POST['media_src_values'] : [],
			'object_src_values'      => is_array( $_POST ) && isset( $_POST['object_src_values'] ) ? $_POST['object_src_values'] : [],
			'plugin_types_values'    => is_array( $_POST ) && isset( $_POST['plugin_types_values'] ) ? $_POST['plugin_types_values'] : [],
			'sandbox_values'         => is_array( $_POST ) && isset( $_POST['sandbox_values'] ) ? $_POST['sandbox_values'] : [],
			'script_src_values'      => is_array( $_POST ) && isset( $_POST['script_src_values'] ) ? $_POST['script_src_values'] : [],
			'style_src_values'       => is_array( $_POST ) && isset( $_POST['style_src_values'] ) ? $_POST['style_src_values'] : [],
			'worker_src_values'      => is_array( $_POST ) && isset( $_POST['worker_src_values'] ) ? $_POST['worker_src_values'] : [],
		];
		$scenario = HTTP_Helper::retrievePost( 'scenario' );
		$settings = Settings::instance();
		$data     = $settings->getDValues( self::KEY_DATA );
		foreach ( $params as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = array_map( 'stripslashes', $value );
			}
			if ( is_array( $value ) && empty( $value ) ) {
				//dont save this, and we remove the key of the trigger
				$trigger = str_replace( '_values', '', $key );
				unset( $data[ $trigger ] );
				continue;
			}

			$data[ $key ] = $value;
		}

		if ( $scenario == 'enforce' ) {
			unset( $data['somewhere'] );
		}
		//set enable flag
		if ( $scenario == 'temp' ) {
			/**
			 * If this is temp then it is flaging for test, the flow will be
			 *  - store the flag in cookies
			 *  - reload page
			 *  - show a small banner for cancel, or apply the changes.
			 */
			$settings->setDValues( self::KEY_TEMP_DATA, $data );
			setcookie( Sh_Content_Security::$slug . '-testing', true, 0, '', '', true, true );
			//clear the old staging
			setcookie( Sh_Content_Security::$slug . '-staging', false, - 1, '', '', true, true );
			//a simple flag to use while cookie being set
		} else {
			$settings->setDValues( self::KEY_DATA, $data );
		}
	}

	public function revert() {
		$settings = Settings::instance();
		$settings->setDValues( self::KEY_TEMP_DATA, null );
	}

	public function listen() {

	}
}