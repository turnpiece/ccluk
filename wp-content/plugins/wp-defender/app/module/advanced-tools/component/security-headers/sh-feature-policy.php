<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component\Security_Headers;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Security_Header;

class Sh_Feature_Policy extends Security_Header {
	static $rule_slug = 'sh_feature_policy';

	public function check() {
		$model = $this->getModel();

		if ( ! $model->sh_feature_policy ) {
			return false;
		}
		if ( isset( $model->sh_feature_policy_mode ) && ! empty( $model->sh_feature_policy_mode ) ) {
			return true;
		}
		$headers = $this->headRequest( network_site_url(), self::$rule_slug );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ) );

			return false;
		}
		if ( isset( $headers['feature-policy'] ) ) {
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
			'intro_text' => esc_html__( 'The Feature-Policy response header provides control over what browser features can be used when web pages are embedded in iframes.', wp_defender()->domain ),
			'mode'       => isset( $model->sh_feature_policy_mode ) ? $model->sh_feature_policy_mode : 'self',
			'values'     => isset( $model->sh_feature_policy_urls ) ? $model->sh_feature_policy_urls : '',
		);
	}

	public function addHooks() {
		$this->addAction( 'send_headers', 'appendHeader' );
		$this->addFilter( 'processing_security_headers', 'filteringHeaders' );
	}

	public function filteringHeaders( $data ) {
		if ( ! isset( $data['sh_feature_policy'] ) ) {
			return $data;
		}
		if ( 'origins' !== $data['sh_feature_policy_mode'] || empty( $data['sh_feature_policy_urls'] ) ) {
			return $data;
		}
		$urls = sanitize_textarea_field( $data['sh_feature_policy_urls'] );
		$urls = explode( PHP_EOL, $urls );
		$urls = array_map( 'trim', $urls );
		foreach ( $urls as $key => $url ) {
			if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
				unset( $urls[ $key ] );
			}
		}
		$data['sh_feature_policy_urls'] = implode( PHP_EOL, $urls );

		return $data;
	}

	public function appendHeader() {
		if ( headers_sent() ) {
			return;
		}
		$model = $this->getModel();
		if ( ! $this->maybeSubmitHeader( 'Feature-Policy', false ) ) {

			return;
		}

		if ( true === $model->sh_feature_policy
			&& isset( $model->sh_feature_policy_mode )
			&& in_array( $model->sh_feature_policy_mode, array( 'self', 'allow', 'origins', 'none' ), true )
		) {
			$headers  = '';
			$features = array(
				'accelerometer',
				'autoplay',
				'camera',
				'encrypted-media',
				'fullscreen',
				'geolocation',
				'gyroscope',
				'magnetometer',
				'microphone',
				'midi',
				'payment',
				'usb',
			);

			switch ( $model->sh_feature_policy_mode ) {
				case 'self':
					array_walk(
						$features,
						function ( &$value, $key ) {
							$value .= " 'self'";
						}
					);
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
					break;
				case 'allow':
					array_walk(
						$features,
						function ( &$value, $key ) {
							$value .= ' *';
						}
					);
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
					break;
				case 'origins':
					if ( isset( $model->sh_feature_policy_urls ) && ! empty( $model->sh_feature_policy_urls ) ) {
						$urls = explode( PHP_EOL, $model->sh_feature_policy_urls );
						$urls = array_map( 'trim', $urls );
						$urls = implode( ' ', $urls );
						array_walk(
							$features,
							function ( &$value, $key ) use ( $urls ) {
								$value .= ' ' . $urls;
							}
						);
						$headers = 'Feature-Policy: ' . implode( '; ', $features );
					}
					break;
				case 'none':
					array_walk(
						$features,
						function ( &$value, $key ) {
							$value .= " 'none'";
						}
					);
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
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
		return __( 'Feature-Policy', wp_defender()->domain );
	}
}