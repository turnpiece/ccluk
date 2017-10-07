<?php
/**
 * Integrates the WPMU Dev PostIndexer plugin:
 * Agm_PostIndexer is the local interface to communicate with the PostIndexer
 * plugin. Some Google Maps plugins will use this class, but not all.
 */

class Agm_PostIndexer {

	public static function has_post_indexer () {
		return (self::has_old_post_indexer() || self::has_new_post_indexer());
	}

	public static function has_old_post_indexer () {
		return function_exists('post_indexer_make_current');
	}

	public static function has_new_post_indexer () {
		return class_exists('postindexermodel');
	}

	public static function get_posts_by_tags ($tags=array()) {
		return self::has_old_post_indexer()
			? self::get_posts_by_tags_old_pi($tags)
			: self::get_posts_by_tags_new_pi($tags)
		;
	}

	public static function get_posts_by_tags_old_pi ($tags=array()) {
		if (!is_array($tags)) return array();
		foreach ($tags as $key=>$val) {
			$tags[$key] = "'" . trim($val) . "'";
		}
		$tags = join(',', $tags);

		global $wpdb;
		$tag_table = $wpdb->base_prefix . 'site_terms';
		$sql = "SELECT term_id FROM {$tag_table} WHERE type='post_tag' AND slug IN ({$tags})";
		$tag_ids = $wpdb->get_results($sql, ARRAY_A);
		if (empty($tag_ids)) return false;

		$post_table = $wpdb->base_prefix . 'site_posts';
		$where = array();
		foreach ($tag_ids as $tag) {
			$where[] = "post_terms LIKE '%|" . $tag['term_id'] . "|%'";
		}
		$where = join (' OR ', $where);
		$sql = "SELECT * FROM {$post_table} WHERE {$where} ORDER BY site_id, blog_id";
		return $wpdb->get_results($sql, ARRAY_A);
	}

	public static function get_posts_by_tags_new_pi ($tags=array()) {
		if (!class_exists('Network_Query')) return array();
		$query = new Network_Query();
		return $query->query(array(
			'tax_query' => array(array(
				'taxonomy' => 'post_tag',
				'field' => 'slug',
				'terms' => $tags,
			)),
		));
	}

	public static function get_post_blog_id ($post) {
		if (self::has_old_post_indexer() && !empty($post['blog_id'])) return $post['blog_id'];
		if (self::has_new_post_indexer() && !empty($post->BLOG_ID)) return $post->BLOG_ID;
		return 0;
	}

	public static function get_post_post_id ($post) {
		if (self::has_old_post_indexer() && !empty($post['post_id'])) return (int)$post['post_id'];
		if (self::has_new_post_indexer() && !empty($post->ID)) return (int)$post->ID;
		return 0;
	}

}
// Post Indexer (http://premium.wpmudev.org/project/post-indexer) integration
define( 'AGM_USE_POST_INDEXER', Agm_PostIndexer::has_post_indexer() );