<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_Module_Minify_Scanner
 *
 * Minification files scan
 */
class WP_Hummingbird_Module_Minify_Scanner {

	/**
	 * Indicates if a scan is in process
	 *
	 * @var bool
	 */
	private $is_scanning = false;

	/**
	 * Indicates if files scan has finished
	 *
	 * @var bool
	 */
	private $is_scanned = false;

	/**
	 * Indicates the current step being scanned
	 *
	 * @var int
	 */
	private $current_step = 0;

	/**
	 * Options names
	 */

	const IS_SCANNING_SLUG = 'wphb-minification-files-scanning';
	const IS_SCANNED_SLUG = 'wphb-minification-files-scanned';
	const CURRENT_STEP = 'wphb-minification-scan-step';


	/**
	 * Refresh status variables
	 */
	public function refresh_status() {
		$this->is_scanning = get_transient( self::IS_SCANNING_SLUG );
		$this->is_scanned = get_option( self::IS_SCANNED_SLUG );
		$this->current_step = (int) get_option( self::CURRENT_STEP );
	}

	/**
	 * Initializes the scan
	 */
	public function init_scan() {
		set_transient( self::IS_SCANNING_SLUG, true, 60 * 4 ); // 4 minutes max
		delete_option( self::IS_SCANNED_SLUG );
		update_option( self::CURRENT_STEP, 0 );
		$this->refresh_status();
	}

	/**
	 * Mark the scan as finished
	 */
	public function finish_scan() {
		delete_transient( self::IS_SCANNING_SLUG );
		update_option( self::IS_SCANNED_SLUG, true );
		delete_option( self::CURRENT_STEP );
		$this->refresh_status();
	}

	/**
	 * Reset the scan as if it weren't being executed
	 */
	public function reset_scan() {
		delete_transient( self::IS_SCANNING_SLUG );
		delete_option( self::IS_SCANNED_SLUG );
		delete_option( self::CURRENT_STEP );
		$this->refresh_status();
	}

	/**
	 * Update the current step being scanned
	 */
	public function update_current_step( $step ) {
		$step = absint( $step );
		update_option( self::CURRENT_STEP, $step );
		$this->refresh_status();
	}

	/**
	 * Get the current scan step being scanned
	 *
	 * @return mixed
	 */
	public function get_current_scan_step() {
		$this->refresh_status();
		return $this->current_step;
	}

	/**
	 * Return the number of total steps to finish the scan
	 *
	 * @return int
	 */
	public static function get_scan_steps() {
		return count( self::get_scan_urls() );
	}

	/**
	 * Check if a scanning is in process
	 *
	 * @return bool
	 */
	public function is_scanning() {
		$this->refresh_status();
		return $this->is_scanning;
	}

	/**
	 * Check if the scan has finished
	 *
	 * @return bool
	 */
	public function is_files_scanned() {
		$this->refresh_status();
		return $this->is_scanned;
	}

	/**
	 * Get the list of URLs to scan
	 *
	 * @return array
	 */
	public static function get_scan_urls() {
		// Calculate URLs to Check
		$args = array(
			'orderby'        => 'rand',
			'posts_per_page' => '1',
			'ignore_sticky_posts' => true,
			'post_status' => 'publish'
		);

		$urls = array();

		$urls[] = home_url();

		$post_types = get_post_types();
		$post_types = array_diff( $post_types, array( 'attachment', 'nav_menu_item', 'revision' ) );

		foreach ( $post_types as $post_type ) {
			$args['post_type'] = $post_type;
			$posts = get_posts( $args );
			if ( $posts ) {
				$urls[] = get_permalink( $posts[0] );
			}

			$post_type_archive_link = get_post_type_archive_link( $post_type );
			if ( $post_type_archive_link )
				$urls[] = $post_type_archive_link;
		}

		if ( get_option( 'show_on_front' ) && $post = get_post( get_option( 'page_for_posts' ) ) ) {
			$urls[] = get_permalink( $post->ID );
		}

		$urls = array_unique( $urls );

		$urls_list = array();
		// Duplicate every URL 3 times. This will be enough to generate all the files for most of the sites
		for ( $i = 0; $i < 3; $i++ ) {
			$urls_list = array_merge( $urls_list, $urls );
		}

		sort( $urls_list );
		return $urls_list;
	}

	/**
	 * This function send a request to a URL in the site
	 * that will trigger the files collection
	 *
	 * @param string $url
	 *
	 * @return array
	 */
	public static function scan_url( $url ) {
		$cookies = array();
		foreach ( $_COOKIE as $name => $value ) {
			if ( strpos( $name, 'wordpress_' ) > -1 ) {
				$cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
			}

		}

		$result = array();

		$args = array(
			'timeout' => 0.01,
			'cookies' => $cookies,
			'blocking' => false,
			'sslverify' => false
		);
		$result['cookie'] = wp_remote_get( $url, $args );

		// One call logged out
		$args = array(
			'timeout' => 0.01,
			'blocking' => false,
			'sslverify' => false
		);

		$result['no-cookie'] = wp_remote_get( $url, $args );

		return $result;
	}

}