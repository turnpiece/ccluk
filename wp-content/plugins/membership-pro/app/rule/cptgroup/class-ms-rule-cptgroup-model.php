<?php
/**
 * Membership Custom Post Type Groups Rule class.
 *
 * Persisted by Membership class.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Model
 */
class MS_Rule_CptGroup_Model extends MS_Rule {

	/**
	 * Rule type.
	 *
	 * @since  1.0.0
	 *
	 * @var string $rule_type
	 */
	protected $rule_type = MS_Rule_CptGroup::RULE_ID;

	/**
	 * Returns the active flag for a specific rule.
	 * State depends on Add-on
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function is_active() {
		return ! MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_CPT_POST_BY_POST );
	}


	/**
	 * Set initial protection.
	 *
	 * @since  1.0.0
	 */
	public function protect_content() {
		/*
		 * Only protect if cpt group.
		 * Protect in list rather than on a single post.
		 */
		if ( ! MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_CPT_POST_BY_POST ) ) {
			parent::protect_content();
			$this->add_action( 'parse_query', 'protect_posts', 98 );
		}
	}

	/**
	 * Adds filter for posts query to remove all protected custom post types.
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Query $query The WP_Query object to filter.
	 */
	public function protect_posts( &$wp_query ) {
		$post_types = $wp_query->get( 'post_type' );

		// There was one case where this was needed...
		if ( empty( $post_types )
			&& ! $wp_query->is_posts_page
			&& isset( $wp_query->queried_object )
			&& isset( $wp_query->queried_object->post_type )
		) {
			$post_types = $wp_query->queried_object->post_type;
		}

		if ( ! empty( $post_types ) // Only protect anything if post-type is known
			&& ! $wp_query->is_singular  // Single pages are protected by `has_access()`
		) {
			$excluded = self::get_excluded_content();
			$final_post_types = array();

			/*
			 * We need an array. WordPress will give us an array, when the
			 * WP_Query did query for multiple post-types at once.
			 * We check each post-type individually!
			 */
			if ( ! is_array( $post_types ) ) {
				$post_types = array( $post_types );
			}

			foreach ( $post_types as $post_type ) {
				$allow = false;

				// Do not protect special "Membership2" or default WordPress content
				if ( in_array( $post_type, $excluded ) ) { $allow = true; }

				// Do not protect if the post-type is published
				elseif ( parent::has_access( $post_type ) ) { $allow = true; }

				if ( $allow )  {
					$final_post_types[] = $post_type;
				}
			}

			if ( empty( $final_post_types ) ) {
				// None of the queried Post Types is allowed.
				$wp_query->query_vars['post__in'] = array( 0 => 0 );
			} else {
				// One or more Post Types can be viewed.
				$wp_query->set( 'post_type', $final_post_types );
			}
		}

		do_action(
			'ms_rule_cptgroup_model_protect_posts',
			$wp_query,
			$this
		);
	}

	/**
	 * Verify access to the current content.
	 *
	 * @since  1.0.0
	 *
	 * @param string $id The content id to verify access.
	 * @return bool|null True if has access, false otherwise.
	 *     Null means: Rule not relevant for current page.
	 */
	public function has_access( $id, $admin_has_access = true ) {
		$has_access = null;

		// Only verify permission if NOT ruled by cpt post by post.
		if ( MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_CPT_POST_BY_POST ) ) {
			return $has_access;
		}

		if ( ! empty( $id ) ) {
			$post = get_post( $id );
		} else {
			$post = get_queried_object();
		}

		$post_type = ! empty( $post->post_type ) ? $post->post_type : '';
		if ( empty( $post_type ) && ! empty( $post->query_var ) ) {
			$post_type = $post->query_var;
		}

		if ( in_array( $post_type, self::get_ms_post_types() ) ) {
			// Always allow access to Membership2 pages.
			$has_access = true;
		} elseif ( in_array( $post_type, self::get_custom_post_types() ) ) {
			// Custom post type
			$has_access = parent::has_access( $post_type, $admin_has_access );
		} else {
			// WordPress core pages are ignored by this rule.
			$has_access = null;
		}

		return apply_filters(
			'ms_rule_cptgroup_model_has_access',
			$has_access,
			$id,
			$this
		);
	}

	/**
	 * Get content to protect.
	 *
	 * @since  1.0.0
	 *
	 * @param string $args Optional. Not used.
	 * @return array The content.
	 */
	public function get_contents( $args = null ) {
		$cpts = self::get_custom_post_types();
		$contents = array();

		$filter = $this->get_exclude_include( $args );
		if ( is_array( $filter->include ) ) {
			$cpts = array_intersect( $cpts, $filter->include );
		} elseif ( is_array( $filter->exclude ) ) {
			$cpts = array_diff( $cpts, $filter->exclude );
		}

		foreach ( $cpts as $key => $content ) {
			$contents[ $key ] 		= new StdClass();
			$contents[ $key ]->id 	= $key;
			$contents[ $key ]->name = $content;
			$contents[ $key ]->type = MS_Rule_CptGroup::RULE_ID;

			$contents[ $key ]->access = $this->get_rule_value( $key );
		}

		return apply_filters(
			'ms_rule_cptgroup_model_get_contents',
			$contents,
			$args,
			$this
		);
	}

	/**
	 * Get the total content count.
	 *
	 * @since  1.0.0
	 *
	 * @param $args The query post args
	 * @return int The total content count.
	 */
	public function get_content_count( $args = null ) {
		$count = 0;
		$items = self::get_custom_post_types();

		$count = count( $items );

		return apply_filters(
			'ms_rule_cptgroup_model_get_content_count',
			$count,
			$args
		);
	}

	/**
	 * Get post types that should not be protected.
	 *
	 * Default WP post types, membership post types
	 *
	 * @since  1.0.0
	 *
	 * @return array The excluded post types.
	 */
	public static function get_excluded_content() {
		$exclude = array_merge(
			array(
				'post',
				'page',
				'attachment',
				'revision',
				'nav_menu_item',
			),
			self::get_ms_post_types()
		);

		return apply_filters(
			'ms_rule_cptgroup_model_get_excluded_content',
			$exclude
		);
	}

	/**
	 * Get post types that are part of this plugin.
	 *
	 * @since  1.0.0
	 *
	 * @return array The plugin core post types.
	 */
	public static function get_ms_post_types() {
		$cpts = array(
			MS_Model_Membership::get_post_type(),
			MS_Model_Invoice::get_post_type(),
			MS_Model_Communication::get_post_type(),
			MS_Model_Relationship::get_post_type(),
			MS_Model_Event::get_post_type(),
		);

		return apply_filters(
			'ms_rule_cptgroup_model_get_ms_post_types',
			$cpts
		);
	}

	/**
	 * Get custom post types.
	 *
	 * Excludes membership plugin and default wp post types.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public static function get_custom_post_types() {
		$args 		= apply_filters( 'ms_rule_cptgroup_model_get_post_types_args',  array(
			'public'   => true,
			'_builtin' => false
		) );
		$cpts 		= get_post_types( $args );
		$excluded 	= self::get_excluded_content();

		return apply_filters(
			'ms_rule_cptgroup_model_get_custom_post_types',
			array_diff( $cpts, $excluded )
		);
	}
}