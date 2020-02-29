<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
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
			$url     = wp_defender()->getPluginUrl() . 'languages/wpdef-default.pot';
			$headers = $this->headRequest( $url, 'Protect Information', strtotime( '+1 day' ) );

			if ( is_wp_error( $headers ) ) {
				Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ), 'tweaks' );

				return false;
			}

			if ( 200 == $headers['response_code'] ) {
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
				sprintf( __( "The file %s is not writable", wp_defender()->domain ), $htPath ) );
		}
		$htConfig       = file( $htPath );
		$htConfig       = array_map( 'trim', $htConfig );
		$rules          = $this->apache_rule();
		$containsSearch = array_diff( array_map( 'trim', $rules ), $htConfig );
		if ( count( $containsSearch ) < count( $rules ) ) {
			//search the wrapper block
			$htContent = file_get_contents( $htPath );
			preg_match( '/## WP Defender(.*?)## WP Defender - End ##/s', $htContent, $matches );
			if ( count( $matches ) ) {
				//remove the whole parts as it partial done
				$htContent = str_replace( $matches[0], '', $htContent );
				$htConfig  = explode( PHP_EOL, $htContent );
				$htConfig  = array_merge( $htConfig, $rules );
				file_put_contents( $htPath, implode( PHP_EOL, $htConfig ), LOCK_EX );
			}
		} elseif ( count( $containsSearch ) == 0 || ( count( $containsSearch ) == count( $rules ) ) ) {
			//append this
			$htConfig = array_merge( $htConfig, $rules );
			file_put_contents( $htPath, implode( PHP_EOL, $htConfig ), LOCK_EX );
		}
		$url = wp_defender()->getPluginUrl() . 'languages/wpdef-default.pot';
		$this->clearHeadRequest( $url );
		if ( ! $this->check() ) {
			wp_send_json_error( [
				'message' => __( "The rules can't apply to your host. This can because of your host doesn't allow for overriding, or you apply for the wrong webserver", wp_defender()->domain )
			] );
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
					sprintf( __( "The file %s is not writable", wp_defender()->domain ), $htPath ) );
			}
			$htConfig = file_get_contents( $htPath );
			$rules    = $this->apache_rule();

			preg_match_all( '/## WP Defender(.*?)## WP Defender - End ##/s', $htConfig, $matches );
			if ( is_array( $matches ) && count( $matches ) > 0 ) {
				$htConfig = str_replace( implode( '', $matches[0] ), '', $htConfig );
			} else {
				$htConfig = str_replace( implode( '', $rules ), '', $htConfig );
			}
			$htConfig = trim( $htConfig );
			file_put_contents( $htPath, $htConfig, LOCK_EX );
			$url = wp_defender()->getPluginUrl() . 'languages/wpdef-default.pot';
			$this->clearHeadRequest( $url );

			return true;
		} else {
			//Other servers we cant revert
			return new \WP_Error( Error_Code::INVALID, __( "Revert is not possible on your current server", wp_defender()->domain ) );
		}
	}

	public function getNginxRules() {
		if ( DIRECTORY_SEPARATOR == '\\' ) {
			//Windows
			$wp_includes = str_replace( ABSPATH, '', WPINC );
			$wp_content  = str_replace( ABSPATH, '', WP_CONTENT_DIR );
		} else {
			$wp_includes = str_replace( $_SERVER['DOCUMENT_ROOT'], '', ABSPATH . WPINC );
			$wp_content  = str_replace( $_SERVER['DOCUMENT_ROOT'], '', WP_CONTENT_DIR );
		}

		$rules = "# Turn off directory indexing
autoindex off;

# Deny access to htaccess and other hidden files
location ~ /\. {
  deny  all;
}

# Deny access to wp-config.php file
location = /wp-config.php {
  deny all;
}

# Deny access to revealing or potentially dangerous files in the /wp-content/ directory (including sub-folders)
location ~* ^$wp_content/.*\.(md|exe|sh|bak|inc|pot|po|mo|log|sql)$ {
  deny all;
}
";

		return $rules;
	}

	/**
	 * Get Apache rule depending on the version
	 *
	 * @return array
	 */
	protected static function apache_rule() {
		$version = Utils::instance()->determineApacheVersion();
		if ( floatval( $version ) >= 2.4 ) {
			$rules = array(
				PHP_EOL . '## WP Defender - Prevent information disclosure ##' . PHP_EOL,
				'<FilesMatch "\.(md|exe|sh|bak|inc|pot|po|mo|log|sql)$">' . PHP_EOL .
				'Require all denied' . PHP_EOL .
				'</FilesMatch>' . PHP_EOL,
				'<Files robots.txt>' . PHP_EOL .
				'Require all granted' . PHP_EOL .
				'</Files>' . PHP_EOL,
				'<Files ads.txt>' . PHP_EOL .
				'Require all granted' . PHP_EOL .
				'</Files>' . PHP_EOL,
				'## WP Defender - End ##'
			);
		} else {
			$rules = array(
				PHP_EOL . '## WP Defender - Prevent information disclosure ##' . PHP_EOL,
				'<FilesMatch "\.(md|exe|sh|bak|inc|pot|po|mo|log|sql)$">' . PHP_EOL .
				'Order allow,deny' . PHP_EOL .
				'Deny from all' . PHP_EOL .
				'</FilesMatch>' . PHP_EOL,
				'<Files robots.txt>' . PHP_EOL .
				'Allow from all' . PHP_EOL .
				'</Files>' . PHP_EOL,
				'<Files ads.txt>' . PHP_EOL .
				'Allow from all' . PHP_EOL .
				'</Files>' . PHP_EOL,
				'## WP Defender - End ##'
			);
		}

		return $rules;
	}
}