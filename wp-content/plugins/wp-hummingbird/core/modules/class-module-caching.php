<?php

/**
 * Class WP_Hummingbird_Module_Caching
 */
class WP_Hummingbird_Module_Caching extends WP_Hummingbird_Module_Server {

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $transient_slug = 'caching';

	/**
	 * Module status.
	 *
	 * @var array $status
	 */
	public $status;

	/**
	 * Analyze data. Overwrites parent method.
	 *
	 * @param bool $check_api If set to true, the api can be checked.
	 *
	 * @return array
	 */
	public function analize_data( $check_api = false ) {
		$files = array(
			'javascript' => WPHB_DIR_URL . 'core/modules/dummy/dummy-js.js',
			'css'        => WPHB_DIR_URL . 'core/modules/dummy/dummy-style.css',
			'media'      => WPHB_DIR_URL . 'core/modules/dummy/dummy-media.pdf',
			'images'     => WPHB_DIR_URL . 'core/modules/dummy/dummy-image.png',
		);

		$results = array();
		$try_api = false;
		foreach ( $files as $type  => $file ) {

			$cookies = array();
			foreach ( $_COOKIE as $name => $value ) {
				if ( strpos( $name, 'wordpress_' ) > -1 ) {
					$cookies[] = new WP_Http_Cookie( array(
						'name'  => $name,
						'value' => $value,
					));
				}
			}

			$args = array(
				'cookies'   => $cookies,
				'sslverify' => false,
			);

			$result = wp_remote_head( $file, $args );

			$this->logger->log( '----- analyzing headers for ' . $file );
			$this->logger->log( 'args: ' );
			if ( isset( $args['cookies'] ) ) {
				unset( $args['cookies'] );
			}
			$this->logger->log( $args );
			$this->logger->log( 'result: ' );
			$this->logger->log( $result );

			$cache_control = wp_remote_retrieve_header( $result, 'cache-control' );
			$results[ $type ] = false;
			if ( $cache_control ) {
				if ( is_array( $cache_control ) ) {
					// Join the cache control header into a single string.
					$cache_control = join( ' ', $cache_control );
				}
				if ( preg_match( '/max\-age=([0-9]*)/', $cache_control, $matches ) ) {
					if ( isset( $matches[1] ) ) {
						$seconds = absint( $matches[1] );
						$results[ $type ] = $seconds;
					}
				}
			} elseif ( ! $cache_control ) {
				$try_api = true;
			}
		} // End foreach().

		// If tests fail for some reason, we fallback to an API check.
		if ( $try_api && $check_api ) {
			// Get the API results.
			$api = WP_Hummingbird_Utils::get_api();
			$api_results = $api->performance->check_cache();
			$api_results = get_object_vars( $api_results );

			foreach ( $files as $type  => $file ) {
				if ( ! isset( $api_results[ $type ]->response_error ) && ! isset( $api_results[ $type ]->http_request_failed ) && absint( $api_results[ $type ] ) > 0 ) {
					$results[ $type ] = absint( $api_results[ $type ] );
				}
			}
		} // End if().

		do_action( 'wphb_caching_analize_data', $results );

		return $results;
	}

	/**
	 * Apache module loader
	 *
	 * @return bool
	 */
	public static function apache_modules_loaded() {
		//$sapi_name = '';
		$apache_modules = array();
		if ( function_exists( 'php_sapi_name' ) ) {
			//$sapi_name = php_sapi_name();
			$apache_modules = apache_get_modules();
		}

		return in_array( 'mod_expires', $apache_modules, true );

	}

	/**
	 * Get code for Nginx
	 *
	 * @param array $expiry_times Type expiry times (javascript, css...). Used with AJAX call caching_reload_snippet.
	 *
	 * @return string
	 */
	public function get_nginx_code( $expiry_times = array() ) {
		if ( empty( $expiry_times ) ) {
			$options = $this->get_options();
		} else {
			$options = $expiry_times;
		}

		$assets_expiration = explode( '/', $options['expiry_javascript'] );
		$assets_expiration = $assets_expiration[0];
		$css_expiration    = explode( '/', $options['expiry_css'] );
		$css_expiration    = $css_expiration[0];
		$media_expiration  = explode( '/', $options['expiry_media'] );
		$media_expiration  = $media_expiration [0];
		$images_expiration = explode( '/', $options['expiry_images'] );
		$images_expiration = $images_expiration[0];

		$code = '
location ~* \.(txt|xml|js)$ {
    expires %%ASSETS%%;
}

location ~* \.(css)$ {
    expires %%CSS%%;
}

location ~* \.(flv|ico|pdf|avi|mov|ppt|doc|mp3|wmv|wav|mp4|m4v|ogg|webm|aac|eot|ttf|otf|woff|svg)$ {
    expires %%MEDIA%%;
}

location ~* \.(jpg|jpeg|png|gif|swf|webp)$ {
    expires %%IMAGES%%;
}';

		$code = str_replace( '%%MEDIA%%', $media_expiration, $code );
		$code = str_replace( '%%IMAGES%%', $images_expiration, $code );
		$code = str_replace( '%%ASSETS%%', $assets_expiration, $code );
		$code = str_replace( '%%CSS%%', $css_expiration, $code );

		return $code;
	}

	/**
	 * Get code for Apache
	 *
	 * @param array $expiry_times Type expiry times (javascript, css...). Used with AJAX call caching_reload_snippet.
	 *
	 * @return string
	 */
	public function get_apache_code( $expiry_times = array() ) {
		if ( empty( $expiry_times ) ) {
			$options = $this->get_options();
		} else {
			$options = $expiry_times;
		}

		$assets_expiration = explode( '/', $options['expiry_javascript'] );
		$assets_expiration = $assets_expiration[1];
		$css_expiration    = explode( '/', $options['expiry_css'] );
		$css_expiration    = $css_expiration[1];
		$media_expiration  = explode( '/', $options['expiry_media'] );
		$media_expiration  = $media_expiration [1];
		$images_expiration = explode( '/', $options['expiry_images'] );
		$images_expiration = $images_expiration[1];

		$code = '
<IfModule mod_expires.c>
ExpiresActive On
ExpiresDefault A0

<FilesMatch "\.(txt|xml|js)$">
ExpiresDefault %%ASSETS%%
</FilesMatch>

<FilesMatch "\.(css)$">
ExpiresDefault %%CSS%%
</FilesMatch>

<FilesMatch "\.(flv|ico|pdf|avi|mov|ppt|doc|mp3|wmv|wav|mp4|m4v|ogg|webm|aac|eot|ttf|otf|woff|svg)$">
ExpiresDefault %%MEDIA%%
</FilesMatch>

<FilesMatch "\.(jpg|jpeg|png|gif|swf|webp)$">
ExpiresDefault %%IMAGES%%
</FilesMatch>
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\.(txt|xml|js)$">
   Header set Cache-Control "max-age=%%ASSETS_HEAD%%"
  </FilesMatch>

  <FilesMatch "\.(css)$">
   Header set Cache-Control "max-age=%%CSS_HEAD%%"
  </FilesMatch>

  <FilesMatch "\.(flv|ico|pdf|avi|mov|ppt|doc|mp3|wmv|wav|mp4|m4v|ogg|webm|aac|eot|ttf|otf|woff|svg)$">
   Header set Cache-Control "max-age=%%MEDIA_HEAD%%"
  </FilesMatch>

  <FilesMatch "\.(jpg|jpeg|png|gif|swf|webp)$">
   Header set Cache-Control "max-age=%%IMAGES_HEAD%%"
  </FilesMatch>
</IfModule>';

		$code = str_replace( '%%MEDIA%%', $media_expiration, $code );
		$code = str_replace( '%%IMAGES%%', $images_expiration, $code );
		$code = str_replace( '%%ASSETS%%', $assets_expiration, $code );
		$code = str_replace( '%%CSS%%', $css_expiration, $code );

		$code = str_replace( '%%MEDIA_HEAD%%', ltrim( $media_expiration, 'A' ), $code );
		$code = str_replace( '%%IMAGES_HEAD%%', ltrim( $images_expiration, 'A' ), $code );
		$code = str_replace( '%%ASSETS_HEAD%%', ltrim( $assets_expiration, 'A' ), $code );
		$code = str_replace( '%%CSS_HEAD%%', ltrim( $css_expiration, 'A' ), $code );

		return $code;
	}

	/**
	 * Get code for LightSpeed
	 *
	 * @param array $expiry_times Type expiry times (javascript, css...). Used with AJAX call caching_reload_snippet.
	 *
	 * @return string
	 */
	public function get_litespeed_code( $expiry_times = array() ) {
		return $this->get_apache_code( $expiry_times );
	}

	/**
	 * Get code for IIS
	 *
	 * @return string
	 */
	public function get_iis_code() {
		return '';
	}

	/**
	 * Get code for IIS 7
	 *
	 * @return string
	 */
	public function get_iis_7_code() {
		return '';
	}

}