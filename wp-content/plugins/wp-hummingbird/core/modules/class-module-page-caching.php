<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Hummingbird_Module_Page_Caching extends WP_Hummingbird_Module {

	public function run() {
		// In order to save settings,  I believe we should make the following assumptions:
		// - Don't compress pages
		// - Don't cache pages for logged in users
		// - change header for cached pages: Send 304
		// - Use the same cache folder for any situation (wp-content/cache/wp-hummingbird)

		// @TODO
		// - Flush cache when permalinks are changed
		// - Flush if a theme/plugin is activated/deactivated
		// - Partial flush if a post/term/comment is updated
		// - Folder structure based on hostname + permalink structure
		// - Do not cache if there are pending assets for minification
		// - Filter to allow pages to avoid caching
		// - Check that advanced-cache.php is not already in wp-content
		// - Try to define( 'WP_CACHE', true ) in wp-config.php

	}
	public function init() {
		// @ TODO We're for the moment caching in init hook but we'll need to change this later and load from wp-content/advanced-cache.php
//		add_action( 'init', array( $this, 'init_caching' ) );


	}

	/**
	 * Try to avoid WP functoins here (though we need to test)
	 */
	public function init_caching() {
		global $wphb_cache_config;

		if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}

		if ( is_admin() ) {
			return;
		}

		if ( is_user_logged_in() ) {
			return;
		}

		// @TODO maybe include wp-cache-config here? Use a JSON instead of a PHP file?
		$wphb_cache_config = new stdClass();
		$wphb_cache_config->cache_dir = WP_CONTENT_DIR . '/wphb-cache/';

		// Cache if the URL has $_GET params or not
		$wphb_cache_config->cache_with_get_params = true;

		if ( ! defined( 'WPHB_DIR' ) ) {
			// Define Hummingbird page caching module dir
			define( 'WPHB_DIR', dirname( __FILE__ ) . '/' );
		}

		if ( strtoupper( $_SERVER["REQUEST_METHOD"] ) !== 'GET' ) {
			return;
		}

		$request_uri = $_SERVER[ 'REQUEST_URI' ];
		$http_host = htmlentities( $_SERVER[ 'HTTP_HOST' ] );
		$port = isset( $_SERVER[ 'SERVER_PORT' ] ) ? intval( $_SERVER[ 'SERVER_PORT' ] ) : 0;
		$is_multisite = wphb_cache_is_multisite();
		$is_subdomain_install = wphb_cache_is_subdomain_install();
		$do_cache = true;

		// Will define the subfolder in cache for multisites
		$site_slug = '';
		if ( $is_multisite && $is_subdomain_install ) {
			$site_slug = $http_host;
		}
		elseif ( $is_multisite && ! $is_subdomain_install ) {
			// Thanks to WP Super Cache
			$request_uri = str_replace( '..', '', preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', $request_uri ) );
			if( strpos( $request_uri, '/', 1 ) ) {
				$site_slug = $request_uri;
				$site_slug = substr( $site_slug, 0, strpos( $site_slug, '/', 1 ) );
				if ( '/' == substr( $site_slug, - 1 ) ) {
					$site_slug = substr( $site_slug, 0, - 1 );
				}
			}
			$site_slug = str_replace( '/', '', $site_slug );
		}

		if ( $site_slug != '' ) {
			$cache_dir = str_replace( '//', '/', $wphb_cache_config->cache_dir. "sites/" . $site_slug . '/' );
		} else {
			$cache_dir = $wphb_cache_config->cache_dir;
		}

		if ( empty( $_GET ) && ! $wphb_cache_config->cache_with_get_params ) {
			return;
		}

		// @TODO Possibility to avoid caching with a $_GET argument

		if ( ! $do_cache ) {
			return;
		}

		// @TODO Check for object cache???

		// Generate cache hash

		// Remove index.php from query
		$hash = str_replace( '/index.php', '/', $request_uri );

		// Remove any query hash from request URI
		$hash .= preg_replace('/#.*$/', '', $hash );

		// @TODO: Attach also cookies values to generate the hash
		$cookies = '';

		$hash = $site_slug . md5( $http_host . $hash . $port . $cookies );
		$file = $cache_dir . "$hash.php";

		$charset = get_option( 'blog_charset' );

		if ( file_exists( $file ) ) {
			// Get meta from meta file. Meta should contain headers
			$meta = array (
				'headers' =>
					array (
						'Vary' => 'Vary: Cookie',
						'Expires' => 'Expires: Thu, 19 Nov 1981 08:52:00 GMT',
						'Content-Type' => 'Content-Type: text/html; charset=UTF-8',
						'Cache-Control' => 'Cache-Control: no-store, no-cache, must-revalidate',
						'Pragma' => 'Pragma: no-cache',
						'Last-Modified' => 'Last-Modified: Mon, 10 Jul 2017 11:47:05 GMT',
					),
				'uri' => 'local.wordpress.dev/?switched_off=true',
				'blog_id' => 1,
				'post' => 0,
				'key' => 'local.wordpress.dev80/?switched_off=true',
			);

			foreach ($meta[ 'headers' ] as $t => $header) {
				// godaddy fix, via http://blog.gneu.org/2008/05/wp-supercache-on-godaddy/ and http://www.littleredrails.com/blog/2007/09/08/using-wp-cache-on-godaddy-500-error/
				if ( strpos( $header, 'Last-Modified:' ) === false ) {
					header( $header );
				}
			}

			header( 'Hummingbird-Cache: Served' );

			echo @file_get_contents( $file );
		}
		else {
			// Add action to save the file at the end of execution?
		}



	}
}

function wphb_cache_set_hooks() {
	add_action( 'template_redirect', 'wp_super_cache_query_vars' );

	// Post ID is received
//	add_action('wp_trash_post', 'wp_cache_post_edit', 0);
//	add_action('publish_post', 'wp_cache_post_edit', 0);
//	add_action('edit_post', 'wp_cache_post_change', 0); // leaving a comment called edit_post
//	add_action('delete_post', 'wp_cache_post_edit', 0);
//	add_action('publish_phone', 'wp_cache_post_edit', 0);
//	// Coment ID is received
//	add_action('trackback_post', 'wp_cache_get_postid_from_comment', 99);
//	add_action('pingback_post', 'wp_cache_get_postid_from_comment', 99);
//	add_action('comment_post', 'wp_cache_get_postid_from_comment', 99);
//	add_action('edit_comment', 'wp_cache_get_postid_from_comment', 99);
//	add_action('wp_set_comment_status', 'wp_cache_get_postid_from_comment', 99, 2);
//	// No post_id is available
//	add_action('switch_theme', 'wp_cache_no_postid', 99);
//	add_action('edit_user_profile_update', 'wp_cache_no_postid', 99);
//	add_action( 'wp_update_nav_menu', 'wp_cache_clear_cache_on_menu' );
//	add_action('wp_cache_gc','wp_cache_gc_cron');
//	add_action( 'clean_post_cache', 'wp_cache_post_edit' );
//	add_filter( 'supercache_filename_str', 'wp_cache_check_mobile' );
//	add_action( 'wp_cache_gc_watcher', 'wp_cache_gc_watcher' );
//	add_action( 'transition_post_status', 'wpsc_post_transition', 10, 3 );
}

function wphb_cache_is_multisite() {
	if ( function_exists( 'is_multisite' ) ) {
		return is_multisite();
	}

	if ( defined( 'WP_ALLOW_MULTISITE' ) && true == WP_ALLOW_MULTISITE ) {
		return true;
	}

	if ( defined( 'SUBDOMAIN_INSTALL' ) || defined( 'VHOST' ) || defined( 'SUNRISE' ) ) {
		return true;
	}

	return false;
}

function wphb_cache_is_subdomain_install() {
	if ( function_exists( 'is_subdomain_install' ) ) {
		return is_subdomain_install();
	}

	if ( defined( 'SUBDOMAIN_INSTALL' ) && true == SUBDOMAIN_INSTALL ) {
		return true;
	}

	return ( defined( 'VHOST' ) && VHOST == 'yes' );
}

function wphb_is_ssl() {
	if ( function_exists( 'is_ssl' ) ) {
		return is_ssl();
	}

	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
			return true;
		}

		if ( '1' == $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}

	return false;
}