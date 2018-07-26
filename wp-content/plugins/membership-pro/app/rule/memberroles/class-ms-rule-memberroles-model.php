<?php
/**
 * Membership Member Roles Rule class.
 *
 * Persisted by Membership class.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Model
 */
class MS_Rule_MemberRoles_Model extends MS_Rule {

	/**
	 * Rule type.
	 *
	 * @since  1.0.0
	 *
	 * @var string $rule_type
	 */
	protected $rule_type = MS_Rule_MemberRoles::RULE_ID;

	/**
	 * List of capabilities that are effectively used for the current user
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	static protected $real_caps = array();

	/**
	 * Caches the get_content_array output
	 *
	 * @var array
	 */
	protected $_content_array = null;


	/**
	 * Returns the active flag for a specific rule.
	 * State depends on Add-on
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function is_active() {
		$def = MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_MEMBERCAPS );
		$adv = MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_MEMBERCAPS_ADV );
		return $def && ! $adv;
	}

	/**
	 * Initializes the object as early as possible
	 *
	 * @since  1.0.0
	 */
	public function prepare_obj() {
		$this->_content_array = null;
	}

	/**
	 * Set initial protection.
	 *
	 * @since  1.0.0
	 */
	public function protect_content() {
		parent::protect_content();

		$this->add_filter( 'user_has_cap', 'prepare_caps', 1, 4 );
		$this->add_filter( 'user_has_cap', 'modify_caps', 10, 4 );
	}

	/**
	 * Set initial protection.
	 *
	 * @since  1.0.0
	 */
	public function protect_admin_content() {
		parent::protect_admin_content();

		$this->add_filter( 'user_has_cap', 'prepare_caps', 1, 4 );
		$this->add_filter( 'user_has_cap', 'modify_caps', 10, 4 );
	}

	/**
	 * Verify access to the current content.
	 *
	 * Always returns null since this rule modifies the capabilities of the
	 * current user and does not directly block access to any page.
	 *
	 * @since  1.0.0
	 *
	 * @return bool|null True if has access, false otherwise.
	 *     Null means: Rule not relevant for current page.
	 */
	public function has_access( $id, $admin_has_access = true ) {
		return null;
	}

	/**
	 * Prepares the list of effective capabilities to use
	 *
	 * Relevant Action Hooks:
	 * - user_has_cap
	 *
	 * @since  1.0.0
	 *
	 * @param array   $allcaps An array of all the role's capabilities.
	 * @param array   $caps    Actual capabilities for meta capability.
	 * @param array   $args    Optional parameters passed to has_cap(), typically object ID.
	 */
	public function prepare_caps( $allcaps, $caps, $args, $user ) {
		global $wp_roles;

		if ( isset( self::$real_caps[$user->ID] ) ) {
			// Only run the init code once for each user-ID.
			return $allcaps;
		} else {
			// First get a list of the users default capabilities.
			self::$real_caps[$user->ID] = $allcaps;
		}

		$all_roles = $wp_roles->roles;

		foreach ( $this->rule_value as $role => $state ) {
			if ( ! $state ) { continue; }

			if ( isset( $all_roles[ $role ] )
				&& is_array( $all_roles[ $role ]['capabilities'] )
			) {
				$caps = $all_roles[ $role ]['capabilities'];
			}
			$caps = mslib3()->array->get( $caps );

			// Only add additional capabilities from now on...
			foreach ( $caps as $key => $value ) {
				if ( $value ) { self::$real_caps[$user->ID][$key] = 1; }
			}
		}

		return $allcaps;
	}

	/**
	 * Modify the users capabilities.
	 *
	 * Relevant Action Hooks:
	 * - user_has_cap
	 *
	 * @since  1.0.0
	 *
	 * @param array   $allcaps An array of all the role's capabilities.
	 * @param array   $caps    Actual capabilities for meta capability.
	 * @param array   $args    Optional parameters passed to has_cap(), typically object ID.
	 */
	public function modify_caps( $allcaps, $caps, $args, $user ) {
		if ( ! isset( self::$real_caps[$user->ID] ) ) {
			self::$real_caps[$user->ID] = $allcaps;
		}

		return apply_filters(
			'ms_rule_memberroles_model_modify_caps',
			self::$real_caps[$user->ID],
			$caps,
			$args,
			$user,
			$this
		);
	}

	/**
	 * Get a simple array of capabilties (e.g. for display in select lists)
	 *
	 * @since  1.0.0
	 * @global array $menu
	 *
	 * @return array {
	 *      @type string $id The id.
	 *      @type string $name The name.
	 * }
	 */
	public function get_roles( $args = null ) {
		global $wp_roles;

		if ( null === $this->_content_array ) {
			// User-Roles are only available in Accessible Content tab, so always display all roles.
			$this->_content_array = array();

			$exclude = apply_filters(
				'ms_rule_memberroles_model_exclude',
				array( 'administrator' )
			);

			$all_roles = $wp_roles->roles;

			// Make sure the rule_value only contains valid items.
			$rule_value = array_intersect_key(
				$this->rule_value,
				$all_roles
			);
			$this->rule_value = mslib3()->array->get( $rule_value );

			foreach ( $all_roles as $key => $role ) {
				if ( in_array( $key, $exclude ) ) { continue; }
				$this->_content_array[$key] = $role['name'];
			}

			$this->_content_array = apply_filters(
				'ms_rule_memberroles_model_get_content_array',
				$this->_content_array,
				$this
			);
		}

		$contents = $this->_content_array;

		// Search the shortcode-tag...
		if ( ! empty( $args['s'] ) ) {
			foreach ( $contents as $key => $name ) {
				if ( false === stripos( $name, $args['s'] ) ) {
					unset( $contents[$key] );
				}
			}
		}

		$filter = self::get_exclude_include( $args );
		if ( is_array( $filter->include ) ) {
			$contents = array_intersect_key( $contents, array_flip( $filter->include ) );
		} elseif ( is_array( $filter->exclude ) ) {
			$contents = array_diff_key( $contents, array_flip( $filter->exclude ) );
		}

		// Pagination
		if ( ! empty( $args['posts_per_page'] ) ) {
			$total = $args['posts_per_page'];
			$offset = ! empty( $args['offset'] ) ? $args['offset'] : 0;
			$contents = array_slice( $contents, $offset, $total );
		}

		return $contents;
	}

	/**
	 * Get content to protect. An array of objects is returned.
	 *
	 * @since  1.0.0
	 * @param $args The query post args
	 * @return array The contents array.
	 */
	public function get_contents( $args = null ) {
		$contents = array();
		$roles = $this->get_roles( $args );

		foreach ( $roles as $key => $rolename ) {
			$content = (object) array();

			$content->id = $key;
			$content->title = $rolename;
			$content->name = $rolename;
			$content->post_title = $rolename;
			$content->type = MS_Rule_MemberRoles::RULE_ID;
			$content->access = $this->get_rule_value( $key );

			$contents[ $key ] = $content;
		}

		return apply_filters(
			'ms_rule_memberroles_model_get_contents',
			$contents,
			$args,
			$this
		);
	}

	/**
	 * Get the total content count.
	 * Used in Dashboard to display how many special pages are protected.
	 *
	 * @since  1.0.0
	 *
	 * @param $args The query post args
	 *     @see @link http://codex.wordpress.org/Class_Reference/WP_Query
	 * @return int The total content count.
	 */
	public function get_content_count( $args = null ) {
		$args['posts_per_page'] = 0;
		$args['offset'] = false;
		$count = count( $this->get_contents( $args ) );

		return apply_filters(
			'ms_rule_memberroles_get_content_count',
			$count,
			$args
		);
	}

}