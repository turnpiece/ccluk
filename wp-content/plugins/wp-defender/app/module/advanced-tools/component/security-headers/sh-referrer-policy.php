<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component\Security_Headers;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Security_Header;

class Sh_Referrer_Policy extends Security_Header {
	static $rule_slug = 'sh_referrer_policy';

	public function check() {
		$model = $this->getModel();

		if ( ! $model->sh_referrer_policy ) {
			return false;
		}
		if ( isset( $model->sh_referrer_policy_mode ) && ! empty( $model->sh_referrer_policy_mode ) ) {
			return true;
		}
		$headers = $this->headRequest( network_site_url(), self::$rule_slug );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ) );

			return false;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getMiscData() {
		$model = $this->getModel();

		return array(
			'intro_text' => esc_html__( 'The Referrer-Policy HTTP header tells web-browsers how to handle referrer information that is sent to websites when a user clicks a link that leads to another page or website link. Referrer headers tell website owners inbound visitors came from (like Google Analytics Acquisition Reports), but there are cases where you may want to control or restrict the amount of information present in this header.', wp_defender()->domain ),
			'mode'       => isset( $model->sh_referrer_policy_mode ) ? $model->sh_referrer_policy_mode : 'origin-when-cross-origin',
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
		if ( ! $this->maybeSubmitHeader( 'Referrer-Policy', false ) ) {

			return;
		}

		if ( true === $model->sh_referrer_policy
			&& isset( $model->sh_referrer_policy_mode )
			&& in_array(
				$model->sh_referrer_policy_mode,
				array(
					'no-referrer',
					'no-referrer-when-downgrade',
					'origin',
					'origin-when-cross-origin',
					'same-origin',
					'strict-origin',
					'strict-origin-when-cross-origin',
					'unsafe-url',
				),
				true
			)
		) {
			$headers = 'Referrer-Policy: ' . $model->sh_referrer_policy_mode;
			header( $headers );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( 'Referrer Policy', wp_defender()->domain );
	}
}