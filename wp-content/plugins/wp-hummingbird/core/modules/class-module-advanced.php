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
		return;
	}

	/***************************
	 *
	 * Remove query strings from static assets.
	 *
	 ***************************/

	/**
	 * Parse the src of script/style tags to remove the version query string.
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	public function remove_query_strings( $src ) {
		$parts = preg_split( '/\?ver|\?timestamp/', $src );
		return $parts[0];
	}

	/***************************
	 *
	 * Remove Emoji.
	 *
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
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function remove_emoji_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}

		return array();
	}

	/***************************
	 *
	 * Prefetch DNS.
	 *
	 ***************************/

	/**
	 * Prefetch DNS. Minimum required WordPress version is 4.6.
	 *
	 * @param array  $hints
	 * @param string $relation_type
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

	/***************************
	 *
	 * Database cleanup.
	 *
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
	 * @param string $param Accepts: 'revisions', 'drafts', 'trash', 'spam', 'trash_comment',
	 *                      'expired_transients', 'transients', 'all'.
	 *
	 * @return int|array
	 */
	public static function get_db_count( $type = 'all' ) {
		global $wpdb;

		switch ( $type ) {
			case 'revisions':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_status = 'inherit'" );
				break;
			case 'drafts':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'draft' OR post_status = 'auto-draft'" );
				break;
			case 'trash':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );
				break;
			case 'spam':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
				break;
			case 'trash_comment':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'" );
				break;
			case 'expired_transients':
				$sql = "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\__%%' AND option_value < UNIX_TIMESTAMP()";
				$count = $wpdb->get_var( $sql );
				break;
			case 'transients':
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '%_transient_%'" );
				break;
			case 'all':
			default:
				$query = "
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
					)";

				$count = $wpdb->get_row( $query );
				break;
		}

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
			'left'  => self::get_db_count( 'all' ), // Check for any non-deleted items
		);
	}

	/**
	 * Delete items from the database using a provided query and item type.
	 *
	 * @since 1.8
	 *
	 * @access private
	 * @param  string  $sql   SQL query to fetch items
	 * @param  string  $type  Type of item to fetch
	 *
	 * @return int
	 */
	private function delete( $sql, $type ) {
		global $wpdb;

		if ( 'revisions' === $type || 'drafts' === $type || 'trash' === $type ) {
			$func = 'wp_delete_post';
		} elseif ( 'spam' === $type || 'trash_comment' === $type ) {
			$func = 'wp_delete_comment';
		} else {
			$func = 'delete_option';
		}

		$items = 0;

		$entries = $wpdb->get_col( $sql );
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

}