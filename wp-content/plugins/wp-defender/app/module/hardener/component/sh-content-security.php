<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_Content_Security extends Rule {
	static $slug = 'sh-content-security';
	static $service;

	public function getDescription() {

	}

	public function check() {
		return $this->getService()->check();
	}

	/**
	 * @return array
	 */
	public function getMiscData() {
		$settings   = Settings::instance();
		$is_test    = isset( $_COOKIE[ self::$slug . '-testing' ] );
		$is_staging = isset( $_COOKIE[ self::$slug . '-staging' ] );
		if ( $is_test ) {
			$data = $settings->getDValues( Sh_Content_Security_Service::KEY_TEMP_DATA );
		} elseif ( $is_staging ) {
			$data = $settings->getDValues( Sh_Content_Security_Service::KEY_STAGING_DATA );
		} else {
			$data = $settings->getDValues( Sh_Content_Security_Service::KEY_DATA );
		}

		return [
			'is_opened' => $is_test || $is_staging,
			'text'      => [
				///mix stirng with html should be parsed from php
				'base_uri_text'        => esc_html__( "Restricts the URLs which can be used in a document's <base> element. You can read more about this directive", wp_defender()->domain ),
				'base_uri_desc'        => sprintf( __( "Example value for this directive can be <strong>%s</strong>. Press Enter to separate the values.", wp_defender()->domain ), network_site_url() ),
				'child_src_text'       => __( "Defines the valid sources for web workers and nested browsing contexts loaded using elements such as &#x3C;frame&#x3E; and &#x3C;iframe&#x3E;. Note, that Instead of child-src, authors who wish to regulate nested browsing contexts and workers should use the <strong>frame-src</strong> and <strong>worker-src</strong> directives, respectively.", wp_defender()->domain ),
				'child_src_desc'       => sprintf( __( "Example value for this directive can be <strong>%s</strong>. Press Enter to separate the values.", wp_defender()->domain ), network_site_url() ),
				'default_src_text'     => __( "" ),
				'default_src_desc'     => sprintf( __( "Example value for this directive can be <strong>'unsafe-eval'</strong>; <strong>'unsafe-inline'</strong>; <strong>%s</strong>. Press Enter to separate the values.", wp_defender()->domain ), network_site_url() ),
				'font_src_desc'        => __( "Example value for this directive can be <strong>font.example.com.</strong> Press Enter to separate the values.", wp_defender()->domain ),
				'form_action_desc'     => __( "Example value for this directive can ba <strong>'self'</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'frame_ancestors_desc' => __( "Example value for this directive can ba <strong>'self'</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'img_src_desc'         => __( "Example value for this directive can be <strong>'self'</strong>; <strong>img.example.com.</strong> Press Enter to separate the values.", wp_defender()->domain ),
				'media_src_text'       => __( "Specifies valid sources for loading media using the &lt;frame&gt; , &lt;frame&gt; and &lt;frame&gt; elements.", wp_defender()->domain ),
				'media_src_desc'       => __( "Example value for this directive can be <strong>media.example.com</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'object_src_text'      => __( "Specifies valid sources for the &lt;frame&gt;, &lt;frame&gt;, and &lt;frame&gt; elements. You can read more about this directive", wp_defender()->domain ),
				'object_src_desc'      => sprintf( __( "Example value for this directive can be <strong>%s</strong>. Press Enter to separate the values.", wp_defender()->domain ), network_site_url() ),
				'sandbox_text'         => __( "Enables a sandbox for the requested resource similar to the &lt;iframe&gt;, sandbox attribute. You can read more about this directive here.", wp_defender()->domain ),
				'sandbox_desc'         => __( "Example value for this directive can be <strong>allow-forms</strong>; <strong>allow-scripts</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'script_src_desc'      => __( "Example value for this directive can be <strong>'self' js.example.com</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'style_src_desc'       => __( "Example value for this directive can be <strong>'self' css.example.com</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'worker_src_desc'      => __( "Example value for this directive can be <strong>'self'</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'plugin_type_desc'     => __( "Example value for this directive can be <strong>application/pdf</strong>. Press Enter to separate the values.", wp_defender()->domain ),
				'frame_ancestors_text' => esc_html__( "Specifies valid sources for nested browsing contexts loading using elements such as <frame> and <iframe>.You can read more about this directive", wp_defender()->domain ),
			],
			'data'      => [
				'base_uri'               => is_array( $data ) && isset( $data['base_uri'] ) ? $data['base_uri'] : 0,
				'child_src'              => is_array( $data ) && isset( $data['child_src'] ) ? $data['child_src'] : 0,
				'default_src'            => is_array( $data ) && isset( $data['default_src'] ) ? $data['default_src'] : 0,
				'font_src'               => is_array( $data ) && isset( $data['font_src'] ) ? $data['font_src'] : 0,
				'frame_ancestors'        => is_array( $data ) && isset( $data['frame_ancestors'] ) ? $data['frame_ancestors'] : 0,
				'form_action'            => is_array( $data ) && isset( $data['form_action'] ) ? $data['form_action'] : 0,
				'img_src'                => is_array( $data ) && isset( $data['img_src'] ) ? $data['img_src'] : 0,
				'media_src'              => is_array( $data ) && isset( $data['media_src'] ) ? $data['media_src'] : 0,
				'object_src'             => is_array( $data ) && isset( $data['object_src'] ) ? $data['object_src'] : 0,
				'plugin_types'           => is_array( $data ) && isset( $data['plugin_types'] ) ? $data['plugin_types'] : 0,
				'sandbox'                => is_array( $data ) && isset( $data['sandbox'] ) ? $data['sandbox'] : 0,
				'script_src'             => is_array( $data ) && isset( $data['script_src'] ) ? $data['script_src'] : 0,
				'style_src'              => is_array( $data ) && isset( $data['style_src'] ) ? $data['style_src'] : 0,
				'worker_src'             => is_array( $data ) && isset( $data['worker_src'] ) ? $data['worker_src'] : 0,
				'url_ignores'            => is_array( $data ) && isset( $data['url_ignores'] ) ? $data['url_ignores'] : "",
				'base_uri_values'        => is_array( $data ) && isset( $data['base_uri_values'] ) ? $data['base_uri_values'] : [ "'none'" ],
				'child_src_values'       => is_array( $data ) && isset( $data['child_src_values'] ) ? $data['child_src_values'] : [],
				'frame_ancestors_values' => is_array( $data ) && isset( $data['frame_ancestors_values'] ) ? $data['frame_ancestors_values'] : [],
				'default_src_values'     => is_array( $data ) && isset( $data['default_src_values'] ) ? $data['default_src_values'] : [],
				'font_src_values'        => is_array( $data ) && isset( $data['font_src_values'] ) ? $data['font_src_values'] : [],
				'form_action_values'     => is_array( $data ) && isset( $data['form_action_values'] ) ? $data['form_action_values'] : [],
				'img_src_values'         => is_array( $data ) && isset( $data['img_src_values'] ) ? $data['img_src_values'] : [],
				'media_src_values'       => is_array( $data ) && isset( $data['media_src_values'] ) ? $data['media_src_values'] : [],
				'object_src_values'      => is_array( $data ) && isset( $data['object_src_values'] ) ? $data['object_src_values'] : [ "'none'" ],
				'plugin_types_values'    => is_array( $data ) && isset( $data['plugin_types_values'] ) ? $data['plugin_types_values'] : [],
				'sandbox_values'         => is_array( $data ) && isset( $data['sandbox_values'] ) ? $data['sandbox_values'] : [],
				'script_src_values'      => is_array( $data ) && isset( $data['script_src_values'] ) ? $data['script_src_values'] : [
					"'unsafe-inline'",
					"'self'",
					"'unsafe-eval'"
				],
				'style_src_values'       => is_array( $data ) && isset( $data['style_src_values'] ) ? $data['style_src_values'] : [],
				'worker_src_values'      => is_array( $data ) && isset( $data['worker_src_values'] ) ? $data['worker_src_values'] : [],
			]
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "Content-Security-Policy isn't enforced. Your site is at risk of XSS attacks.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced Content-Security-Policy, good job!", wp_defender()->domain );
	}

	public function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'wp_loaded', 'appendHeader', 999 );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		$is_test    = isset( $_COOKIE[ self::$slug . '-testing' ] );
		$is_staging = isset( $_COOKIE[ self::$slug . '-staging' ] );
		if ( $is_test || $is_staging ) {
			$this->addAction( 'admin_enqueue_scripts', 'enqueueCspDebugBar' );
			if ( $is_test ) {
				$this->addAction( 'admin_footer', 'debugBar', 9999 );
			} elseif ( $is_staging ) {
				$this->addAction( 'tweaks_footer', 'stagingNotification', 9999 );
			}
			$this->addAjaxAction( 'defender-csp-debug-cancel', 'cancelDebug' );
			$this->addAjaxAction( 'defender-csp-debug-apply', 'applyDebug' );
			$this->addAjaxAction( 'defender-csp-debug-staging', 'applyStaging' );
		}
	}

	/**
	 * Cancel the debug, redirect to security tweaks page
	 */
	public function cancelDebug() {
		setcookie( self::$slug . '-testing', false, - 1, '', '', false, true );
		$status = $this->check();
		wp_redirect( network_admin_url( 'admin.php?page=wdf-hardener&view=' . ( $status == true ? 'resolved' : 'issues' ) ) );
		exit;
	}

	/**
	 * We applying the debug to a staging parameter before real apply to site
	 */
	public function applyDebug() {
		$settings  = Settings::instance();
		$test_data = $settings->getDValues( Sh_Content_Security_Service::KEY_TEMP_DATA );
		$settings->setDValues( Sh_Content_Security_Service::KEY_STAGING_DATA, $test_data );
		$settings->setDValues( Sh_Content_Security_Service::KEY_TEMP_DATA, null );
		$status = $this->check();
		setcookie( self::$slug . '-testing', false, - 1, '', '', false, true );
		setcookie( self::$slug . '-staging', true, 0, '', '', false, true );
		wp_redirect( network_admin_url( 'admin.php?page=wdf-hardener&view=' . ( $status == true ? 'resolved' : 'issues' ) ) );
		exit;
	}

	public function applyStaging() {
		$settings    = Settings::instance();
		$stagingData = $settings->getDValues( Sh_Content_Security_Service::KEY_STAGING_DATA );
		$settings->setDValues( Sh_Content_Security_Service::KEY_DATA, $stagingData );
		$settings->setDValues( Sh_Content_Security_Service::KEY_STAGING_DATA, null );
		setcookie( self::$slug . '-testing', false, - 1, '', '', false, true );
		setcookie( self::$slug . '-staging', false, - 1, '', '', false, true );
		wp_redirect( network_admin_url( 'admin.php?page=wdf-hardener&view=resolved' ) );
		exit;
	}

	public function enqueueCspDebugBar() {
		wp_enqueue_style( 'defender-csp-debug-bar', wp_defender()->getPluginUrl() . '/assets/css/csp-debug-bar.css' );
	}

	public function debugBar() {
		$this->renderPartial( '/tweaks/csp/debug-bar' );
	}

	public function stagingNotification() {
		$this->renderPartial( '/tweaks/csp/notification-bar' );
	}

	public function revert() {
		$this->getService()->revert();
		Settings::instance()->addToIssues( Sh_Content_Security::$slug );
	}

	/**
	 *
	 */
	public function appendHeader() {
		if ( headers_sent() ) {
			//header already sent, do nothing
			return;
		}

		$settings = Settings::instance();
		$is_test  = isset( $_COOKIE[ self::$slug . '-testing' ] );
		if ( $is_test ) {
			$data = $settings->getDValues( Sh_Content_Security_Service::KEY_TEMP_DATA );
		} else {
			$data = $settings->getDValues( Sh_Content_Security_Service::KEY_DATA );
		}

		if ( ! $this->maybeSubmitHeader( 'Content-Security-Policy', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			//this mean Defender can't override the already output, marked to show notification
			$data['overrideable'] = false;
			$settings->setDValues( Sh_Content_Security_Service::KEY_DATA, $data );

			return;
		}

		if ( is_array( $data ) ) {
			$keys    = [
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
			$headers = [];
			$data    = array_filter( $data );
			foreach ( $keys as $key ) {
				if ( ! isset( $data[ $key ] ) || ( isset( $data[ $key ] ) && $data[ $key ] != 1 ) ) {
					//this rule not enable, move forward
					continue;
				}

				if ( isset( $data[ $key . '_values' ] ) ) {
					$value = $data[ $key . '_values' ];
					$value = implode( ' ', $value );
					/**
					 * with the javascript rules, many place using inline especially in wp-admin, we should check
					 * and append that if it missing so everything wont broke down
					 */
					if ( $key == 'script_src' && ( is_admin() || is_network_admin() ) ) {
						$uris = [
							str_replace( network_site_url(), '', network_admin_url( 'admin.php?page=wdf-hardener' ) ),
							str_replace( network_site_url(), '', network_admin_url( 'admin.php?page=wdf-hardener&view=resolved' ) ),
							str_replace( network_site_url(), '', network_admin_url( 'admin.php?page=wdf-hardener&view=issues' ) ),
							str_replace( network_site_url(), '', network_admin_url( 'admin.php?page=wdf-hardener&view=ignore' ) )
						];
						$uri  = $_SERVER['REQUEST_URI'];
						if ( in_array( $uri, $uris ) && current_user_can( 'manage_options' ) ) {
							//read the data output from wp_localization
							if ( stristr( $value, 'unsafe-inline' ) == false ) {
								$value .= " 'unsafe-inline'";
							}
							/**
							 * underscore require new Function so we need this
							 */
							if ( stristr( $value, 'unsafe-eval' ) == false ) {
								$value .= " 'unsafe-eval'";
							}
							/**
							 * to load the needed js from core
							 */
							if ( stristr( $value, 'self' ) == false ) {
								$value .= " 'self'";
							}
						}
					}

					$headers[] = str_replace( '_', '-', $key ) . ' ' . $value;
				}
			}
			$headers = array_filter($headers);
			if ( empty( $headers ) ) {
				return;
			}
			$headers = implode( '; ', $headers );
			$headers = "Content-Security-Policy: " . $headers;
			header( $headers );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "Content-Security-Policy Security Header", wp_defender()->domain );
	}

	/**
	 * Store a flag that we enable this
	 * @return mixed|void
	 */
	public function process() {
		//calling the service
		$this->getService()->process();
		Settings::instance()->addToResolved( Sh_Content_Security::$slug );
		$scenario = HTTP_Helper::retrievePost( 'scenario' );
		if ( $scenario == 'temp' ) {
//			$status = $this->check();
//			$url    = network_admin_url( 'admin.php?page=wdf-hardener' );
//			if ( $status == true ) {
//				$url = add_query_arg( 'view', 'resolved', $url );
//			} else {
//				$url = add_query_arg( 'view', 'issues', $url );
//			}
			wp_send_json_success( [
				'reload' => 1,
				//'url'    => $url
			] );
		}
	}

	/**
	 * @return Sh_Content_Security_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_Content_Security_Service();
		}

		return self::$service;
	}
}