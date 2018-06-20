<?php

/**
 * Class WP_Hummingbird_Module_GZip
 */
class WP_Hummingbird_Module_GZip extends WP_Hummingbird_Module_Server {

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	protected $transient_slug = 'gzip';

	/**
	 * Module status.
	 *
	 * @var array $status
	 */
	public $status;

	/**
	 * Check for Gzip issues.
	 *
	 * @return bool|WP_Error  Return error message, if gzip is enabled, but not for all types.
	 */
	public function check_gzip_issues() {
		// Apache, but htaccess not yet written.
		$htaccess_writable = self::is_htaccess_writable();

		if ( $htaccess_writable && ! self::is_htaccess_written( $this->transient_slug ) ) {
			return false;
		}

		$status = $this->get_analysis_data();

		if ( 3 !== count( $status ) || in_array( false, $status, true ) ) {
			// There must be another plugin/server config that is setting its own gzip stuff.
			$error_message  = '<ul><li>- ' . esc_html__( 'Your server may not have the "deflate" module enabled (mod_deflate for
				Apache, ngx_http_gzip_module for NGINX).', 'wphb' ) . '</li>';
			$error_message .= '<li>- ' . esc_html__( 'Contact your host. If deflate is enabled, ask why all .htaccess or
				nginx.conf compression rules are not being applied.', 'wphb' ) . '</li></ul>';
			$error_message .= '<p>' . sprintf(
				/* translators: %s: support link */
				__( 'If re-checking and restarting does not resolve, please check with your host or <a href="%s" target="_blank">open a support ticket with us</a>.', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'support' )
			) . '</p>';

			return new WP_Error( 'gzip-external-problem', $error_message );
		}
	}

	/**
	 * Analyze data. Overwrites parent method.
	 *
	 * @param bool $check_api If set to true, the api can be checked.
	 *
	 * @return array
	 */
	public function analize_data( $check_api = false ) {
		$files = array(
			'HTML'       => add_query_arg( 'avoid-minify', 'true', get_home_url() ),
			'JavaScript' => WPHB_DIR_URL . 'core/modules/dummy/dummy-js.js',
			'CSS'        => WPHB_DIR_URL . 'core/modules/dummy/dummy-style.css',
		);

		$results = array();
		$try_api = false;
		foreach ( $files as $type  => $file ) {
			// We don't use wp_remote, getting the content-encoding is not working.
			if ( ! class_exists( 'SimplePie' ) ) {
				require_once( ABSPATH . WPINC . '/class-simplepie.php' );
			}

			$headers = array(
				'Content-Type' => 'text/plain',
			);
			$useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';

			$result = new SimplePie_File( $file, 10, 5, $headers, $useragent );

			$headers = $result->headers;
			$results[ $type ] = false;
			if ( ! empty( $headers ) && isset( $headers['content-encoding'] ) && 'gzip' === $headers['content-encoding'] ) {
				$results[ $type ] = true;
			} else {
				$try_api = true;
			}
		}

		// Will only trigger on 're-check status' button click and there are some false values.
		if ( $try_api && $check_api ) {
			// Get the API results.
			$api = WP_Hummingbird_Utils::get_api();
			$api_results = $api->performance->check_gzip();
			$api_results = get_object_vars( $api_results );
			foreach ( $files as $type  => $file ) {
				// If already true, do not overwrite with check.
				if ( true === $results[ $type ] ) {
					continue;
				}

				$index = strtolower( $type );
				if ( ! isset( $api_results[ $index ]->response_error )
					&& ( isset( $api_results[ $index ] ) && true === $api_results[ $index ] )
				) {
					$results[ $type ] = true;
				}
			}
		} // End if().

		return $results;
	}

	/**
	 * Code to use on Nginx servers.
	 *
	 * @return string
	 */
	public function get_nginx_code() {
		return '# Enable Gzip compression
gzip          on;

# Compression level (1-9)
gzip_comp_level     5;

# Don\'t compress anything under 256 bytes
gzip_min_length     256;

# Compress output of these MIME-types
gzip_types
    application/atom+xml
    application/javascript
    application/json
    application/rss+xml
    application/vnd.ms-fontobject
    application/x-font-ttf
    application/x-font-opentype
    application/x-font-truetype
    application/x-javascript
    application/x-web-app-manifest+json
    application/xhtml+xml
    application/xml
    font/eot
    font/opentype
    font/otf
    image/svg+xml
    image/x-icon
    image/vnd.microsoft.icon
    text/css
    text/plain
    text/javascript
    text/x-component;

# Disable gzip for bad browsers
gzip_disable  "MSIE [1-6]\.(?!.*SV1)";';
	}

	/**
	 * Code to use on Apache servers.
	 *
	 * @return string
	 */
	public function get_apache_code() {
		return '<IfModule mod_deflate.c>
	SetOutputFilter DEFLATE
    <IfModule mod_setenvif.c>
        <IfModule mod_headers.c>
            SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
            RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
        </IfModule>
    </IfModule>
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE "application/atom+xml" \
                                      "application/javascript" \
                                      "application/json" \
                                      "application/ld+json" \
                                      "application/manifest+json" \
                                      "application/rdf+xml" \
                                      "application/rss+xml" \
                                      "application/schema+json" \
                                      "application/vnd.geo+json" \
                                      "application/vnd.ms-fontobject" \
                                      "application/x-font-ttf" \
                                      "application/x-font-opentype" \
                                      "application/x-font-truetype" \
                                      "application/x-javascript" \
                                      "application/x-web-app-manifest+json" \
                                      "application/xhtml+xml" \
                                      "application/xml" \
                                      "font/eot" \
                                      "font/opentype" \
                                      "font/otf" \
                                      "image/bmp" \
                                      "image/svg+xml" \
                                      "image/vnd.microsoft.icon" \
                                      "image/x-icon" \
                                      "text/cache-manifest" \
                                      "text/css" \
                                      "text/html" \
                                      "text/javascript" \
                                      "text/plain" \
                                      "text/vcard" \
                                      "text/vnd.rim.location.xloc" \
                                      "text/vtt" \
                                      "text/x-component" \
                                      "text/x-cross-domain-policy" \
                                      "text/xml"

    </IfModule>
    <IfModule mod_mime.c>
        AddEncoding gzip              svgz
    </IfModule>
</IfModule>';
	}

	/**
	 * Code to use on LiteSpeed servers.
	 *
	 * @return string
	 */
	public function get_litespeed_code() {
		return $this->get_apache_code();
	}

	/**
	 * IIS code.
	 *
	 * @return string
	 */
	public function get_iis_code() {
		return '';
	}

	/**
	 * IIS 7 code.
	 *
	 * @return string
	 */
	public function get_iis_7_code() {
		return '';
	}

}