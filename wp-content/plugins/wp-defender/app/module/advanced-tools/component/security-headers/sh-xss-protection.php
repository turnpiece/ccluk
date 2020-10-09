<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component\Security_Headers;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Security_Header;

class Sh_XSS_Protection extends Security_Header {
	static $rule_slug = 'sh_xss_protection';

	public function check() {
		$model = $this->getModel();

		if ( ! $model->sh_xss_protection ) {
			return false;
		}
		if ( isset( $model->sh_xss_protection_mode ) && ! empty( $model->sh_xss_protection_mode ) ) {
			return true;
		}
		$headers = $this->headRequest( network_site_url(), self::$rule_slug );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ) );

			return false;
		}

		if ( isset( $headers['x-xss-protection'] ) ) {
			$header_xss_protection = is_array( $headers['x-xss-protection'] )
				? $headers['x-xss-protection'][0]
				: $headers['x-xss-protection'];
			$content               = strtolower( trim( $header_xss_protection ) );
			$content               = explode( ';', $content );
			if ( 1 === count( $content ) ) {
				$model->sh_xss_protection_mode = 'sanitize';
			} else {
				$content                       = explode( '=', $content[1] );
				$model->sh_xss_protection_mode = $content[1];
			}
			$model->save();

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getMiscData() {
		$model = $this->getModel();

		return array(
			'intro_text' => esc_html__( 'The HTTP X-XSS-Protection response header that stops pages from loading when they detect reflected cross-site scripting (XSS) attacks on Chrome, IE and Safari.', wp_defender()->domain ),
			'mode'       => isset( $model->sh_xss_protection_mode ) ? $model->sh_xss_protection_mode : 'sanitize',
		);
	}

	public function addHooks() {
		$this->addAction( 'send_headers', 'appendHeader' );
	}

	public function appendHeader() {
		if ( headers_sent() ) {
			return;
		}
		$model = $this->getModel();
		if ( ! $this->maybeSubmitHeader( 'X-XSS-Protection', false ) ) {

			return;
		}
		if ( true === $model->sh_xss_protection && in_array( $model->sh_xss_protection_mode, array( 'sanitize', 'block', 'none' ), true ) ) {
			$headers = '';
			switch ( $model->sh_xss_protection_mode ) {
				case 'sanitize':
					$headers = 'X-XSS-Protection: 1';
					break;
				case 'block':
					$headers = 'X-XSS-Protection: 1; mode=block';
					break;
				default:
					break;
			}
			if ( strlen( $headers ) > 0 ) {
				header( trim( $headers ) );
			}
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( 'X-XSS-Protection', wp_defender()->domain );
	}
}