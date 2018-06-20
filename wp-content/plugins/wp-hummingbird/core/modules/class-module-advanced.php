<?php
/**
 * Class WP_Hummingbird_Module_Advanced
 *
 * Implements various advanced features of the plugin: removing query strings from static resources,
 * removing the emojis file from rendering on the pages, prefetching dns queries.
 *
 * @package Hummingbird
 *
 * @since 1.8
 */
class WP_Hummingbird_Module_Advanced extends WP_Hummingbird_Module {

	/**
	 * Initializes the module. Always executed even if the module is deactivated.
	 *
	 * Do not use __construct in subclasses, use init() instead
	 */
	public function init() {
		// Remove emoji.
		$remove_emoji = WP_Hummingbird_Settings::get_setting( 'emoji', 'advanced' );
		if ( $remove_emoji ) {
			// Remove styles/scripts.
			$this->remove_emoji();
			// Remove dns prefetch.
			add_filter( 'emoji_svg_url', '__return_false' );
			// Remove from TinyMCE.
			add_filter( 'tiny_mce_plugins', array( $this, 'remove_emoji_tinymce' ) );
		}

		// Process HB cleanup task.
		add_action( 'wphb_hummingbird_cleanup', array( $this, 'hb_cleanup_cron' ) );

		// Everything else is only for frontend.
		if ( is_admin() ) {
			return;
		}

		// Remove query strings from static resources (only on front-end).
		$query_strings_enabled = WP_Hummingbird_Settings::get_setting( 'query_string', 'advanced' );
		if ( $query_strings_enabled ) {
			add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ), 15, 1 );
			add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ), 15, 1 );
		}

		// DNS prefetch.
		add_filter( 'wp_resource_hints', array( $this, 'prefetch_dns' ), 10, 2 );
	}

	/**
	 * Execute the module actions. It must be defined in subclasses.
	 */
	public function run() {}

	/**
	 * Clear the module cache.
	 *
	 * @return mixed
	 */
	public function clear_cache() {
		return true;
	}

	/**
	 * *************************
	 * Remove query strings from static assets.
	 ***************************/

	/**
	 * Parse the src of script/style tags to remove the version query string.
	 *
	 * @param string $src  Script loader source path.
	 *
	 * @return string
	 */
	public function remove_query_strings( $src ) {
		$parts = preg_split( '/\?ver|\?timestamp/', $src );
		return $parts[0];
	}

	/**
	 * *************************
	 * Remove Emoji.
	 ***************************/

	/**
	 * Remove Emoji scripts from WordPress.
	 */
	public function remove_emoji() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	/**
	 * Remove Emoji icons from TinyMCE.
	 *
	 * @param array $plugins  An array of default TinyMCE plugins.
	 *
	 * @return array
	 */
	public function remove_emoji_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}

		return array();
	}

	/**
	 * *************************
	 * Prefetch DNS.
	 ***************************/

	/**
	 * Prefetch DNS. Minimum required WordPress version is 4.6.
	 *
	 * @param array  $hints          URLs to print for resource hints.
	 * @param string $relation_type  The relation type the URLs are printed for, e.g. 'preconnect' or 'prerender'.
	 *
	 * @see https://make.wordpress.org/core/2016/07/06/resource-hints-in-4-6/
	 *
	 * @return array
	 */
	public function prefetch_dns( $hints, $relation_type ) {
		$urls = WP_Hummingbird_Settings::get_setting( 'prefetch', 'advanced' );

		// If not urls set, return default WP hints array.
		if ( ! is_array( $urls ) || empty( $urls ) ) {
			return $hints;
		}

		$urls = array_map( 'esc_url', $urls );

		if ( 'dns-prefetch' === $relation_type ) {
			foreach ( $urls as $url ) {
				$hints[] = $url;
			}
		}

		return $hints;
	}

	/**
	 * *************************
	 * Database cleanup.
	 ***************************/

	/**
	 * Get default fields for database cleanup.
	 *
	 * @return array
	 */
	public static function get_db_fields() {
		return array(
			'revisions' => array(
				'title'   => __( 'Post Revisions', 'wphb' ),
				'tooltip' => __( "Historic versions of your posts and pages. If you don't need to revert to older versions, delete these entries", 'wphb' ),
			),
			'drafts' => array(
				'title'   => __( 'Draft Posts', 'wphb' ),
				'tooltip' => __( 'Auto-saved versions of your posts and pages. If you don’t use drafts you can safely delete these entries', 'wphb' ),
			),
			'trash' => array(
				'title'   => __( 'Trashed Posts', 'wphb' ),
				'tooltip' => __( "Posts or pages you've marked as trash but haven't permanently deleted yet", 'wphb' ),
			),
			'spam' => array(
				'title'   => __( 'Spam Comments', 'wphb' ),
				'tooltip' => __( "Comments marked as spam that haven't been deleted yet", 'wphb' ),
			),
			'trash_comment' => array(
				'title'   => __( 'Trashed Comments', 'wphb' ),
				'tooltip' => __( "Comments you've marked as trash but haven't permanently deleted yet", 'wphb' ),
			),
			'expired_transients' => array(
				'title'   => __( 'Expired Transients', 'wphb' ),
				'tooltip' => __( 'Cached data that themes and plugins have stored, except these ones have expired and can be deleted', 'wphb' ),
			),
			'transients' => array(
				'title'   => __( 'All Transients', 'wphb' ),
				'tooltip' => __( 'Cached data that themes and plugins have stored, but may still be in use. Note: the next page to load could take a bit longer due to WordPress regenerating transients.', 'wphb' ),
			),
		);
	}

	/**
	 * Get data from the database.
	 *
	 * @param string $type Accepts: 'revisions', 'drafts', 'trash', 'spam', 'trash_comment',
	 *                     'expired_transients', 'transients', 'all'.
	 *
	 * @return int|array
	 */
	public static function get_db_count( $type = 'all' ) {
		global $wpdb;

		switch ( $type ) {
			case 'revisions':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_status = 'inherit'" ); // Db call ok; no-cache ok.
				break;
			case 'drafts':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'draft' OR post_status = 'auto-draft'" ); // Db call ok; no-cache ok.
				break;
			case 'trash':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" ); // Db call ok; no-cache ok.
				break;
			case 'spam':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" ); // Db call ok; no-cache ok.
				break;
			case 'trash_comment':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'" ); // Db call ok; no-cache ok.
				break;
			case 'expired_transients':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\__%%' AND option_value < UNIX_TIMESTAMP()" ); // Db call ok; no-cache ok.
				break;
			case 'transients':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'" ); // Db call ok; no-cache ok.
				break;
			case 'all':
			default:
				$count = $wpdb->get_row( "
					SELECT revisions, drafts, trash, spam, trash_comment, expired_transients, transients,
					       sum(revisions+drafts+trash+spam+trash_comment+expired_transients+transients) AS total
					FROM (
					  (SELECT
					    COUNT(CASE WHEN post_type = 'revision' AND post_status = 'inherit' THEN 1 ELSE NULL END) AS revisions,
					    COUNT(CASE WHEN post_status = 'draft' OR post_status = 'auto-draft' THEN 1 ELSE NULL END) AS drafts,
					    COUNT(CASE WHEN post_status = 'trash' THEN 1 ELSE NULL END) AS trash
					  FROM {$wpdb->posts}) as posts,
					  (SELECT
					    COUNT(CASE WHEN comment_approved = 'spam' THEN 1 ELSE NULL END) AS spam,
					    COUNT(CASE WHEN comment_approved = 'trash' THEN 1 ELSE NULL END) AS trash_comment
					  FROM {$wpdb->comments}) as comments,
					  (SELECT
					    COUNT(CASE WHEN option_name LIKE '\_transient\_timeout\__%%' AND option_value < UNIX_TIMESTAMP() THEN 1 ELSE NULL END ) AS expired_transients,
					    COUNT(CASE WHEN option_name LIKE '%_transient_%' THEN 1 ELSE NULL END) AS transients
					  FROM {$wpdb->options}) as options
					)"
				); // Db call ok; no-cache ok.
				break;
		} // End switch().

		return $count;
	}

	/**
	 * Delete database rows.
	 *
	 * @since 1.8
	 *
	 * @param string $type Accepts: 'revisions', 'drafts', 'trash', 'spam', 'trash_comment',
	 *                     'expired_transients', 'transients', 'all'.
	 *
	 * @return array|bool
	 */
	public function delete_db_data( $type ) {
		global $wpdb;

		$sql   = array(
			'revisions'          => "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_status = 'inherit'",
			'drafts'             => "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'draft' OR post_status = 'auto-draft'",
			'trash'              => "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'trash'",
			'spam'               => "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'spam'",
			'trash_comment'      => "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'trash'",
			'expired_transients' => "SELECT option_name FROM {$wpdb->options}
											WHERE option_name LIKE '\_transient\_timeout\__%%' AND option_value < UNIX_TIMESTAMP()",
			'transients'         => "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%_transient_%'",
		);

		if ( ! isset( $sql[ $type ] ) && 'all' !== $type ) {
			return false;
		}

		if ( 'all' === $type ) {
			$items = 0;
			foreach ( $sql as $type => $query ) {
				$items = $items + $this->delete( $query, $type );
			}
		} else {
			$items = $this->delete( $sql[ $type ], $type );
		}

		return array(
			'items' => $items,
			'left'  => self::get_db_count( 'all' ), // Check for any non-deleted items.
		);
	}

	/**
	 * Delete items from the database using a provided query and item type.
	 *
	 * @since 1.8
	 *
	 * @access private
	 * @param  string $sql   SQL query to fetch items.
	 * @param  string $type  Type of item to fetch.
	 *
	 * @return int
	 */
	private function delete( $sql, $type ) {
		global $wpdb;

		$entries = $wpdb->get_col( $sql ); // Db call ok; no-cache ok.

		if ( 'revisions' === $type || 'drafts' === $type || 'trash' === $type ) {
			$func = 'wp_delete_post';
		} elseif ( 'spam' === $type || 'trash_comment' === $type ) {
			$func = 'wp_delete_comment';
		} elseif ( 'expired_transients' === $type && function_exists( 'delete_expired_transients' ) ) {
			delete_expired_transients();
			return count( $entries );
		} else {
			$func = 'delete_option';
		}

		$items = 0;
		foreach ( $entries as $entry ) {
			if ( 'delete_option' === $func ) {
				// No option to force delete in delete_option function.
				$del = call_user_func( $func, $entry );
			} else {
				// Force delete entries (without moving to trash).
				$del = call_user_func( $func, $entry, true );
			}

			if ( null !== $del && ! is_wp_error( $del ) ) {
				$items++;
			}
		}

		return $items;
	}

	/**
	 * *************************
	 * HB cleanup.
	 ***************************/

	/**
	 * Init HB cleanup task.
	 *
	 * @since 1.8.1
	 *
	 * @internal
	 *
	 * @param bool $new_scan  Start a new scan.
	 */
	public static function init_hb_cleanup( $new_scan = true ) {
		// Clean all cron.
		wp_clear_scheduled_hook( 'wphb_hummingbird_cleanup' );

		// Schedule new scan.
		if ( $new_scan ) {
			wp_schedule_single_event( time(), 'wphb_hummingbird_cleanup' );
		}
	}

	/**
	 * Cleanup cron task.
	 *
	 * @since 1.8.1
	 */
	public function hb_cleanup_cron() {
		global $wpdb;

		// Select 100 entries.
		$entries = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'wphb_minify_group' LIMIT 0, 100" ); // db call ok; no-cache ok.

		// Delete them properly.
		foreach ( $entries as $entry ) {
			if ( get_post( $entry ) && 'wphb_minify_group' === get_post_type( $entry ) ) {
				wp_delete_post( $entry, true );
			}
		}

		// Reschedule another batch if any entries left.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wphb_minify_group'" ); // db call ok; no-cache ok.

		if ( 0 < (int) $count ) {
			wp_schedule_single_event( time(), 'wphb_hummingbird_cleanup' );
		} else {
			wp_clear_scheduled_hook( 'wphb_hummingbird_cleanup' );
		}

		return true;
	}

	/**
	 * *************************
	 * System Information.
	 ***************************/

	/**
	 * Get PHP information for System Information.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_php_info() {
		$php_info = array();
		$php_vars = array(
			'max_execution_time',
			'open_basedir',
			'memory_limit',
			'upload_max_filesize',
			'post_max_size',
			'display_errors',
			'log_errors',
			'track_errors',
			'session.auto_start',
			'session.cache_expire',
			'session.cache_limiter',
			'session.cookie_domain',
			'session.cookie_httponly',
			'session.cookie_lifetime',
			'session.cookie_path',
			'session.cookie_secure',
			'session.gc_divisor',
			'session.gc_maxlifetime',
			'session.gc_probability',
			'session.referer_check',
			'session.save_handler',
			'session.save_path',
			'session.serialize_handler',
			'session.use_cookies',
			'session.use_only_cookies',
		);

		$php_info[ __( 'Version' , 'wphb' ) ] = phpversion();
		foreach ( $php_vars as $setting ) {
			$php_info[ $setting ] = ini_get( $setting );
		}
		$levels = array();
		$error_reporting = error_reporting();

		$extension_constants = array(
			'E_ERROR',
			'E_WARNING',
			'E_PARSE',
			'E_NOTICE',
			'E_CORE_ERROR',
			'E_CORE_WARNING',
			'E_COMPILE_ERROR',
			'E_COMPILE_WARNING',
			'E_USER_ERROR',
			'E_USER_WARNING',
			'E_USER_NOTICE',
			'E_STRICT',
			'E_RECOVERABLE_ERROR',
			'E_DEPRECATED',
			'E_USER_DEPRECATED',
			'E_ALL',
		);

		foreach ( $extension_constants as $level ) {
			if ( defined( $level ) ) {
				$c = constant( $level );
				if ( $error_reporting & $c ) {
					$levels[ $c ] = $level;
				}
			}
		}
		$php_info[ __( 'Error Reporting' , 'wphb' ) ] = implode( '<br>', $levels );
		$extensions = get_loaded_extensions();
		natcasesort( $extensions );
		$php_info[ __( 'Extensions' , 'wphb' ) ] = implode( '<br>', $extensions );

		return $php_info;
	}

	/**
	 * Get Database information for System Information.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_db_info() {
		global $wpdb;
		$dump_mysql = array();
		$mysql_vars = array(
			'key_buffer_size'    => true,   // Key cache size limit.
			'max_allowed_packet' => false,  // Individual query size limit.
			'max_connections'    => false,  // Max number of client connections.
			'query_cache_limit'  => true,   // Individual query cache size limit.
			'query_cache_size'   => true,   // Total cache size limit.
			'query_cache_type'   => 'ON',   // Query cache on or off.
		);
		$extra_info = array();
		$variables = $wpdb->get_results( "SHOW VARIABLES WHERE Variable_name IN ( '" . implode( "', '", array_keys( $mysql_vars ) ) . "' )" ); // db call ok; no-cache ok.

		$dbh = $wpdb->dbh;
		if ( is_resource( $dbh ) ) {
			$driver  = 'mysql';
			$version = function_exists( 'mysqli_get_server_info' ) ? mysqli_get_server_info( $dbh ) : mysql_get_server_info( $dbh );
		} elseif ( is_object( $dbh ) ) {
			$driver  = get_class( $dbh );
			if ( method_exists( $dbh, 'db_version' ) ) {
				$version = $dbh->db_version();
			} elseif ( isset( $dbh->server_info ) ) {
				$version = $dbh->server_info;
			} elseif ( isset( $dbh->server_version ) ) {
				$version = $dbh->server_version;
			} else {
				$version = __( 'Unknown', 'wphb' );
			}
			if ( isset( $dbh->client_info ) ) {
				$extra_info['Driver version'] = $dbh->client_info;
			}
			if ( isset( $dbh->host_info ) ) {
				$extra_info['Connection info'] = $dbh->host_info;
			}
		} else {
			$version = $driver = __( 'Unknown', 'wphb' );
		}
		$extra_info['Database'] = $wpdb->dbname;
		$extra_info['Charset'] = $wpdb->charset;
		$extra_info['Collate'] = $wpdb->collate;
		$extra_info['Table Prefix'] = $wpdb->prefix;

		$dump_mysql['Server Version'] = $version;
		$dump_mysql['Driver'] = $driver;
		foreach ( $extra_info as $key => $val ) {
			$dump_mysql[ $key ] = $val;
		}
		foreach ( $mysql_vars as $key => $val ) {
			$dump_mysql[ $key ] = $val;
		}
		foreach ( $variables as $item ) {
			if ( is_numeric( $item->Value ) && ( $item->Value >= ( 1024 * 1024 ) ) ) {
				$val = size_format( $item->Value );
			}
			$dump_mysql[ $item->Variable_name ] = $val;
		}

		return $dump_mysql;
	}

	/**
	 * Get WordPress Installation information for System Information.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_wp_info() {
		global $wp_version;
		$dump_wp = array();
		$wp_consts = array(
			'ABSPATH',
			'WP_CONTENT_DIR',
			'WP_PLUGIN_DIR',
			'WPINC',
			'WP_LANG_DIR',
			'UPLOADBLOGSDIR',
			'UPLOADS',
			'WP_TEMP_DIR',
			'SUNRISE',
			'WP_ALLOW_MULTISITE',
			'MULTISITE',
			'SUBDOMAIN_INSTALL',
			'DOMAIN_CURRENT_SITE',
			'PATH_CURRENT_SITE',
			'SITE_ID_CURRENT_SITE',
			'BLOGID_CURRENT_SITE',
			'BLOG_ID_CURRENT_SITE',
			'COOKIE_DOMAIN',
			'COOKIEPATH',
			'SITECOOKIEPATH',
			'DISABLE_WP_CRON',
			'ALTERNATE_WP_CRON',
			'DISALLOW_FILE_MODS',
			'WP_HTTP_BLOCK_EXTERNAL',
			'WP_ACCESSIBLE_HOSTS',
			'WP_DEBUG',
			'WP_DEBUG_LOG',
			'WP_DEBUG_DISPLAY',
			'ERRORLOGFILE',
			'SCRIPT_DEBUG',
			'WP_LANG',
			'WP_MAX_MEMORY_LIMIT',
			'WP_MEMORY_LIMIT',
			'WPMU_ACCEL_REDIRECT',
			'WPMU_SENDFILE',
		);
		$dump_wp['WordPress Version'] = $wp_version;
		foreach ( $wp_consts as $const ) {
			$dump_wp[ $const ] = self::format_constant( $const );
		}

		return $dump_wp;
	}


	/**
	 * Get server information for System Information.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_server_info() {
		$dump_server = array();
		$server = explode( ' ', wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ); // Input var ok.
		$server = explode( '/', reset( $server ) );

		if ( isset( $server[1] ) ) {
			$server_version = $server[1];
		} else {
			$server_version = 'Unknown';
		}

		$dump_server[ __( 'Software Name', 'wphb' ) ] = $server[0];
		$dump_server[ __( 'Software Version', 'wphb' ) ] = $server_version;
		$dump_server[ __( 'Server IP', 'wphb' ) ] = @$_SERVER['SERVER_ADDR'];
		$dump_server[ __( 'Server Hostname', 'wphb' ) ] = @$_SERVER['SERVER_NAME'];
		$dump_server[ __( 'Server Admin', 'wphb' ) ] = @$_SERVER['SERVER_ADMIN'];
		$dump_server[ __( 'Server local time', 'wphb' ) ] = date( 'Y-m-d H:i:s (\U\T\C P)' );
		$dump_server[ __( 'Operating System', 'wphb' ) ] = @php_uname( 's' );
		$dump_server[ __( 'OS Hostname', 'wphb' ) ] = @php_uname( 'n' );
		$dump_server[ __( 'OS Version', 'wphb' ) ] = @php_uname( 'v' );

		return $dump_server;
	}

	/**
	 * Helper function.
	 *
	 * @since 1.8.2
	 *
	 * @param string $constant  Name of a PHP const.
	 *
	 * @return string
	 */
	public static function format_constant( $constant ) {
		if ( ! defined( $constant ) ) {
			return '<em>' . __( 'undefined', 'wphb' ) . '</em>';
		}

		$val = constant( $constant );
		if ( ! is_bool( $val ) ) {
			return $val;
		} elseif ( ! $val ) {
			return __( 'FALSE', 'wphb' );
		} else {
			return __( 'TRUE', 'wphb' );
		}
	}

}