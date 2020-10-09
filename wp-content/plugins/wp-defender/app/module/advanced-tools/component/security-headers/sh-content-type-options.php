<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component\Security_Headers;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Security_Header;

class Sh_Content_Type_Options extends Security_Header {
	static $rule_slug = 'sh_content_type_options';

	public function check() {
		$model = $this->getModel();

		if ( ! $model->sh_content_type_options ) {
			return false;
		}
		if ( isset( $model->sh_content_type_options_mode ) && 'nosniff' === $model->sh_content_type_options_mode ) {
			return true;
		}

		$headers = $this->headRequest( network_site_url(), self::$rule_slug );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ) );

			return false;
		}
		if ( isset( $headers['x-content-type-options'] ) && is_null( $model->sh_content_type_options_mode ) ) {
			$model->sh_content_type_options_mode = 'nosniff';
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
			'intro_text' => esc_html__( 'The X-Content-Type-Options header is used to protect against MIME sniffing attacks. The most common example of this is when a website allows users to upload content to a website, however the user disguises a particular file type as something else.', wp_defender()->domain ),
			'mode'       => isset( $model->sh_content_type_options_mode ) ? $model->sh_content_type_options_mode : 'nosniff',
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
		if ( ! $this->maybeSubmitHeader( 'X-Content-Type-Options', false ) ) {
			//this mean Defender can't override the already output, marked to show notification

			return;
		}
		if ( true === $model->sh_content_type_options && 'nosniff' === $model->sh_content_type_options_mode ) {
			header( 'X-Content-Type-Options: nosniff' );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( 'X-Content-Type-Options', wp_defender()->domain );
	}
}