<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component\Security_Headers;

use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Security_Header;

class Sh_Strict_Transport extends Security_Header {
	static $rule_slug = 'sh_strict_transport';

	/**
	 * Get time in seconds
	 *
	 * @return array
	 */
	private function timeInSeconds() {
		return array(
			'1 hour'   => 1 * 3600,
			'24 hours' => 86400,
			'7 days'   => 7 * 86400,
			'30 days'  => 30 * 86400,
			'3 months' => ( 3 * 30 + 1 ) * 86400,
			'6 months' => ( 6 * 30 + 3 ) * 86400,
			'1 year'   => 365 * 86400,
			'2 years'  => 365 * 2 * 86400,
		);
	}

	/**
	 * Check HTTPS
	 *
	 * @return bool
	 */
	private function isHttps() {
		return isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'];
	}

	public function check() {
		$model = $this->getModel();

		if ( ! $model->sh_strict_transport ) {
			return false;
		}
		//'max-age' directive is required
		if ( ! empty( $model->hsts_cache_duration ) ) {
			return true;
		}
		$headers = $this->headRequest( network_site_url(), self::$rule_slug );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ) );

			return false;
		}

		if ( isset( $headers['strict-transport-security'] ) ) {
			$hsts_cache_duration = '';
			$hsts_preload        = 0;
			$include_subdomain   = 0;
			$header_sts          = is_array( $headers['strict-transport-security'] )
				? $headers['strict-transport-security'][0]
				: $headers['strict-transport-security'];

			$content = explode( ';', $header_sts );
			foreach ( $content as $line ) {
				if ( stristr( $line, 'max-age' ) ) {
					$value   = explode( '=', $line );
					$arr     = $this->timeInSeconds();
					$seconds = isset( $value[1] ) ? (int) $value[1] : 0;
					$closest = null;
					$key     = null;
					foreach ( $arr as $k => $item ) {
						if ( is_null( $closest ) || ( ! is_null( $closest ) && ( abs( $seconds - $closest ) > abs( $item - $seconds ) ) ) ) {
							$closest = $item;
							$key     = $k;
						}
					}
					$hsts_cache_duration = $key;
				} elseif ( stristr( $line, 'preload' ) ) {
					$hsts_preload = 1;
				} elseif ( stristr( $line, 'includeSubDomains' ) ) {
					$include_subdomain = 1;
				}
			}

			if ( ( '' !== $hsts_cache_duration )
				|| ( 0 !== $hsts_preload )
				|| ( 0 !== $include_subdomain )
			) {
				if ( is_null( $model->hsts_preload ) && $hsts_preload ) {
					$model->hsts_preload = $hsts_preload;
				}
				if ( is_null( $model->include_subdomain ) && $include_subdomain ) {
					$model->include_subdomain = $include_subdomain;
				}
				if ( is_null( $model->hsts_cache_duration ) && $hsts_cache_duration ) {
					$model->hsts_cache_duration = $hsts_cache_duration;
				}
				$model->save();
			}

			return true;
		}

		return false;
	}

	public function getMiscData() {
		$model           = $this->getModel();
		$site_url        = network_site_url();
		$domain_data     = Utils::instance()->parseDomain( $site_url );
		$allow_subdomain = false;
		if ( is_array( $domain_data ) && ! isset( $domain_data['subdomain'] ) ) {
			$allow_subdomain = true;
		} elseif ( ! $domain_data && ! is_multisite() ) {
			//case if a single site installs in a folder, e.g. http://example.com/something/folder/
			$allow_subdomain = true;
		} elseif ( ! $domain_data && is_multisite() && is_subdomain_install() && is_main_site() ) {
			//case if it's a main MU site with subdomain install
			$allow_subdomain = true;
		}

		return array(
			'intro_text'          => esc_html__( 'The HTTP Strict-Transport-Security response header (HSTS) lets a web site tell browsers that it should only be accessed using HTTPS, instead of using HTTP. This is extremely important for websites that store and process sensitive information like ECommerce stores and helps prevent Protocol Downgrade and Clickjacking attacks.', wp_defender()->domain ),
			'hsts_preload'        => isset( $model->hsts_preload ) ? $model->hsts_preload : 0,
			'include_subdomain'   => isset( $model->include_subdomain ) ? $model->include_subdomain : 0,
			'hsts_cache_duration' => isset( $model->hsts_cache_duration ) ? $model->hsts_cache_duration : '30 days',
			'allow_subdomain'     => $allow_subdomain,
		);
	}

	public function addHooks() {
		$this->addAction( 'send_headers', 'appendHeader' );
	}

	public function appendHeader() {
		if ( headers_sent() ) {
			return;
		}

		if ( ! $this->maybeSubmitHeader( 'Strict-Transport-Security', false ) ) {
			return;
		}
		$model = $this->getModel();
		//header is ignored by the browser when your site is accessed using HTTP
		if ( true === $model->sh_strict_transport ) {
			$headers = 'Strict-Transport-Security:';
			if ( isset( $model->hsts_cache_duration ) && ! empty( $model->hsts_cache_duration ) ) {
				$arr = $this->timeInSeconds();
				//set default for a week, so RIPs wont waring weak header
				$seconds = isset( $arr[ $model->hsts_cache_duration ] ) ? $arr[ $model->hsts_cache_duration ] : 604800;
				if ( ! is_null( $seconds ) ) {
					$headers .= ' max-age=' . $seconds;
				}
			}

			if ( '1' === (string) $model->include_subdomain ) {
				$headers .= ' ; includeSubDomains';
			}
			if ( '1' === (string) $model->hsts_preload ) {
				$headers .= ' ; preload';
			}

			header( $headers );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( 'Strict Transport', wp_defender()->domain );
	}
}