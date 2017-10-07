<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Rule_Service;

class Protect_Information_Service extends Rule_Service implements IRule_Service {

	/**
	 * @return bool
	 */
	public function check() {
		$cache = WP_Helper::getArrayCache()->get( 'Protect_Information_Service', null );
		if ( $cache === null ) {
			$url    = wp_defender()->getPluginUrl() . 'changelog.txt';
			$status = wp_remote_head( $url, array( 'user-agent' => $_SERVER['HTTP_USER_AGENT'] ) );
			if ( 200 == wp_remote_retrieve_response_code( $status ) ) {
				WP_Helper::getArrayCache()->set( 'Protect_Information_Service', false );
				return false;
			}
			WP_Helper::getArrayCache()->set( 'Protect_Information_Service', true );
			return true;
		} else {
			return $cache;
		}
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$htPath = ABSPATH . '.htaccess';
		if ( ! is_file( $htPath ) ) {
			file_put_contents( $htPath, '', LOCK_EX );
		}
		if ( ! is_writeable( $htPath ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
		}
		$htConfig       = file( $htPath );
		$rules          = array(
			PHP_EOL . '## WP Defender - Prevent information disclosure ##' . PHP_EOL,
			'<FilesMatch "\.(txt|md|exe|sh|bak|inc|pot|po|mo|log|sql)$">' . PHP_EOL .
			'Order allow,deny' . PHP_EOL .
			'Deny from all' . PHP_EOL .
			'</FilesMatch>' . PHP_EOL,
			'<Files robots.txt>' . PHP_EOL .
			'Allow from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'## WP Defender - End ##' . PHP_EOL
		);
		$containsSearch = array_diff( $rules, $htConfig );
		if ( count( $containsSearch ) == 0 || ( count( $containsSearch ) == count( $rules ) ) ) {
			//append this
			$htConfig = array_merge( $htConfig, array( implode( '', $rules ) ) );
			file_put_contents( $htPath, implode( '', $htConfig ), LOCK_EX );
		}

		return true;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function revert() {
		global $is_apache;
		if ( $is_apache ) {
			$htPath = ABSPATH . '.htaccess';
			if ( ! is_writeable( $htPath ) ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE,
					sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
			}
			$htConfig = file_get_contents( $htPath );
			$rules    = array(
				'## WP Defender - Prevent information disclosure ##' . PHP_EOL,
				'<FilesMatch "\.(txt|md|exe|sh|bak|inc|pot|po|mo|log|sql)$">' . PHP_EOL .
				'Order allow,deny' . PHP_EOL .
				'Deny from all' . PHP_EOL .
				'</FilesMatch>' . PHP_EOL,
				'<Files robots.txt>' . PHP_EOL .
				'Allow from all' . PHP_EOL .
				'</Files>' . PHP_EOL,
				'## WP Defender - End ##'
			);
			$rules    = implode( '', $rules );
			$htConfig = str_replace( $rules, '', $htConfig );
			$htConfig = trim( $htConfig );
			file_put_contents( $htPath, $htConfig, LOCK_EX );

			return true;
		} else {
			//Other servers we cant revert
			return new \WP_Error( Error_Code::INVALID, __( "Revert is not possible on your current server", wp_defender()->domain ) );
		}
	}
}