<?php
/**
 * Membership Category Rule class.
 *
 * Persisted by Membership class.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */
class MS_Rule_Category_Model extends MS_Rule {

	/**
	 * Rule type.
	 *
	 * @since  1.0.0
	 *
	 * @var string $rule_type
	 */
	protected $rule_type = MS_Rule_Category::RULE_ID;

	static public $All_Categories;

	/**
	 * Protected categories
	 *
	 * @var array
	 */
	static public $protected_categories = array();

	/**
	 * Returns the active flag for a specific rule.
	 * State depends on Add-on
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function is_active() {
		return MS_Model_Addon::is_enabled( MS_Addon_Category::ID );
	}

	/**
	 * Set initial protection.
	 *
	 * @since  1.0.0
	 */
	public function protect_content() {
		parent::protect_content();

		$this->add_action( 'pre_get_posts', 'protect_posts', 98 );
		$this->add_filter( 'get_terms', 'protect_categories', 99, 2 );
		$this->add_filter( 'ms_rule_post_model_has_access', 'override_ms_rule_post_model_has_access', 999, 3 );
	}

	/**
	 * Adds category__in filter for posts query to remove all posts which not
	 * belong to allowed categories.
	 *
	 * Related Filters:
	 * - pre_get_posts
	 *
	 * @since  1.0.0
	 *
	 * @param WP_Query $query The WP_Query object to filter.
	 */
	public function protect_posts( $wp_query ) {
        if( is_category() || is_home() || is_search() ) {

			$post_type = self::get_post_type( $wp_query );


			/**
			 * Only post. Pages dont need protection
			 */
			if ( in_array( $post_type, array( 'post' ) ) ) {
				// This list is already filtered (see the get_terms filter!)
				$contents = get_categories( 'get=all' );
				$categories = array();

				foreach ( $contents as $content ) {
					if ( $this->has_access( $content->term_id, true, true ) ) {
						$categories[] = absint( $content->term_id );
					}
				}

				$wp_query->query_vars['category__in'] = $categories;
			}

			do_action(
				'ms_rule_category_model_protect_posts',
				$wp_query,
				$this
			);
        }
	}

	/**
	 * Filters categories and removes all not accessible categories.
	 *
	 * @since  1.0.0
	 *
	 * @param array $terms The terms array.
	 * @param array $taxonomies The taxonomies array.
	 * @return array Filtered terms array.
	 */
	public function protect_categories( $terms, $taxonomies ) {
		$new_terms = array();

		// Bail - not fetching category taxonomy.
		if ( ! in_array( 'category', $taxonomies ) || in_the_loop() || is_main_query () ) {
			return $terms;
		}

		if ( ! is_array( $terms ) ) {
			$terms = (array) $terms;
		}

		foreach ( $terms as $key => $term ) {
			if ( ! empty( $term->taxonomy ) && 'category' === $term->taxonomy ) {
				if ( $this->has_access( $term->term_id, true, true ) ) {
					$new_terms[ $key ] = $term;
				}
			} else {
				// Taxonomy is no category: Add it so custom taxonomies don't break.
				$new_terms[ $key ] = $term;
			}
		}

		self::$protected_categories = $new_terms;

		return apply_filters(
			'ms_rule_category_model_protect_categories',
			$new_terms
		);
	}

	/**
	 * Verify access to the current content overwriting page rule
	 *
	 *
	 * @since  1.1.3
	 *
	 * @param bool $has_access If user has access or not
	 * @param int $id The content post ID to verify access.
	 * @param MS_Rule_Post_Model $obj Instance of MS_Rule_Post_Model
	 *
	 * @return bool|null True if has access, false otherwise.
	 *     Null means: Rule not relevant for current page.
	 */
	public function override_ms_rule_post_model_has_access( $has_access, $id, $obj ) {
		if ( ! empty( $id )  && $id > 0 ) {
			$post = get_post( $id );
			if ( is_a( $post, 'WP_Post' )
				|| ( ! empty( $post->post_type ) && 'post' == $post->post_type )
			)  {
				$categories = wp_get_post_categories( $id );
				if ( !empty( $categories ) ) {
					foreach ( $categories as $category ) {
						if ( !$this->has_access( $category, true, true ) ) {
							$has_access = false;
						}
					}
				}
			}
		}
		return apply_filters(
			'ms_rule_category_overwrite_page_has_access',
			$has_access,
			$id,
			$obj
		);
	}

	/**
	 * Verify access to the current category or post belonging to a catogory.
	 *
	 * @since  1.0.0
	 *
	 * @param int $id The current post_id.
	 * @return bool|null True if has access, false otherwise.
	 *     Null means: Rule not relevant for current page.
	 */
	public function has_access( $id, $admin_has_access = true, $check_category = false ) {
		$has_access = null;

		$taxonomies = get_object_taxonomies( get_post_type() );

		// Verify post access accordingly to category rules.
		if ( $check_category ) {
			$has_access = parent::has_access( $id, $admin_has_access );
		} elseif ( ! empty( $id )
			|| ( is_single() && in_array( 'category', $taxonomies ) )
		) {
			if ( empty( $id ) ) {
				$id = get_the_ID();
			}

			$categories = wp_get_post_categories( $id );
			foreach ( $categories as $category_id ) {
				$has_access = parent::has_access( $category_id, $admin_has_access );

				if ( $has_access ) {
					break;
				}
			}
		} elseif ( is_category() ) {
			// Category page.
			$category = get_queried_object_id();
			$has_access = parent::has_access( $category, $admin_has_access );
		}

		return apply_filters(
			'ms_rule_category_model_has_access',
			$has_access,
			$id,
            $admin_has_access,
			$this
		);
	}

	/**
	 * Get content to protect.
	 *
	 * @since  1.0.0
	 *
	 * @param string $args The default query args.
	 * @return array The content.
	 */
	public function get_contents( $args = null ) {
		$args = $this->get_query_args( $args );

		$categories = $this->hierarchical_category_tree( $args );
		$cont = array();

		foreach ( $categories as $key => $category ) {
			$category->id = $category->term_id;

			$category->type = MS_Rule_Category::RULE_ID;
			$category->access = $this->get_rule_value( $category->id );

			if ( array_key_exists( $category->id, $this->dripped ) ) {
				$category->delayed_period =
					$this->dripped[ $category->id ]['period_unit'] . ' ' .
					$this->dripped[ $category->id ]['period_type'];
				$category->dripped = $this->dripped[ $category->id ];
			} else {
				$category->delayed_period = '';
			}

			$cont[ $key ] = $category;
		}

		return $cont;
	}


	public function hierarchical_category_tree( $args = array() ) {
		$categories = get_categories( $args );

		if( $categories ) {
			foreach( $categories as $key => $category ) {
				self::$All_Categories[] = $category;
				$args['parent'] = $category->term_id;
				$this->hierarchical_category_tree( $args );
			}
		}

		return self::$All_Categories;
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
		unset( $args['number'] );
		$args = $this->get_query_args( $args );
		$categories = get_categories( $args );

		$count = count( $categories );

		return apply_filters(
			'ms_rule_category_model_get_content_count',
			$count,
			$args
		);
	}

	/**
	 * Get the default query args.
	 *
	 * @since  1.0.0
	 *
	 * @param string $args The query post args.
	 * @return array The parsed args.
	 */
	public function get_query_args( $args = null ) {
		return parent::prepare_query_args( $args, 'get_categories' );
	}

}