<?php
/**
 * Membership BuddyPress Rule class.
 *
 * Persisted by Membership class.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */
class MS_Addon_BuddyPress_Rule_Model extends MS_Rule {

	/**
	 * Rule type.
	 *
	 * @since  1.0.0
	 *
	 * @var string $rule_type
	 */
	protected $rule_type = MS_Addon_BuddyPress_Rule::RULE_ID;

	/**
	 * Rule type.
	 *
	 * @since  1.0.2.6
	 *
	 * @param int $membership_id
	 */
	public function __construct( $membership_id ) {
		parent::__construct( $membership_id );
	}

	/**
	 * Verify access to the current content.
	 *
	 * Related:
	 * A reference of available BuddyPress template tags
	 * https://codex.buddypress.org/developer/template-tag-reference/
	 *
	 * @since  1.0.0
	 *
	 * @param int $id The content post ID to verify access.
	 * @return bool|null True if has access, false otherwise.
	 *     Null means: Rule not relevant for current page.
	 */
	public function has_access( $id, $admin_has_access = true ) {
		global $bp;
		$has_access = null;

		if ( ! function_exists( 'bp_current_component' ) ) {
			return null;
		}

		// General BuddyPress access is either *allowed* or *not denied*
		$component = bp_current_component();

		if ( $component || is_buddypress() ) {
			if ( 'members' == $component || bp_is_user() ) {
				// Member listing or member profile access.
				$has_access = parent::has_access(
					MS_Addon_BuddyPress_Rule::PROTECT_MEMBERS,
					$admin_has_access
				);
			} elseif ( 'messages' == $component ) {
				// Private messaging direct access.
				if ( 'compose' == $bp->current_action ) {
					$has_access = parent::has_access(
						MS_Addon_BuddyPress_Rule::PROTECT_PRIVATE_MSG,
						$admin_has_access
					);
				}
			} else {
				// Check if access to *all* BuddyPress pages is restricted.
				$has_access = parent::has_access(
					MS_Addon_BuddyPress_Rule::PROTECT_ALL,
					$admin_has_access
				);
			}
		}

		if ( 'activate' == bp_current_component() ) {
			$has_access = true;
		}

		return apply_filters(
			'ms_rule_buddypress_has_access',
			$has_access,
			$id,
			$this
		);
	}

	/**
	 * Verify access to the current content overwriting page rule
	 *
	 *
	 * @since  1.0.2.7
	 *
	 * @param bool $has_access If user has access or not
	 * @param int $id The content post ID to verify access.
	 * @param MS_Rule_Page_Model $obj Instance of MS_Rule_Page_Model
	 *
	 * @return bool|null True if has access, false otherwise.
	 *     Null means: Rule not relevant for current page.
	 */
	public function override_ms_rule_page_model_has_access( $has_access, $id, $obj ) {
		if ( is_buddypress() ) {
			// Check if access to *all* BuddyPress pages is restricted
			$has_access = parent::has_access(
				MS_Addon_BuddyPress_Rule::PROTECT_ALL,
				$admin_has_access
			);
		}

		if ( $has_access ) {
			$component = bp_current_component();
			if ( ! empty( $component ) ) {
				if ( 'members' == $component || bp_is_user() ) {
					// Member listing or member profile access.
					$has_access = parent::has_access(
						MS_Addon_BuddyPress_Rule::PROTECT_MEMBERS,
						$admin_has_access
					);
				}
			}
		}

		return apply_filters(
			'ms_rule_buddypress_overwrite_page_has_access',
			$has_access,
			$id,
			$obj
		);
	}

	/**
	 * Set initial protection.
	 *
	 * @since  1.0.0
	 *
	 * @param MS_Model_Relationship $ms_relationship Optional. Not used.
	 */
	public function protect_content( $ms_relationship = false ) {
		parent::protect_content( $ms_relationship );

		$this->add_filter(
			'bp_user_can_create_groups',
			'protect_create_bp_group', 10, 2
		);
		//Added in 1.1.3
		$this->add_filter(
			'ms_rule_page_model_has_access',
			'override_ms_rule_page_model_has_access', 10, 3
		);
		$this->protect_friendship_request();
		$this->protect_private_messaging();
	}

	/**
	 * Protect private messaging.
	 *
	 * @since  1.0.0
	 */
	protected function protect_private_messaging() {
		if ( ! parent::has_access( MS_Addon_BuddyPress_Rule::PROTECT_PRIVATE_MSG ) ) {
			$this->add_filter(
				'bp_get_send_message_button',
				'hide_private_message_button'
			);
		}

		do_action(
			'ms_rule_buddypress_protect_private_messaging',
			$this
		);
	}

	/**
	 * Adds filter to prevent friendship button rendering.
	 *
	 * Related Action Hooks:
	 * - bp_get_send_message_button
	 *
	 * @since  1.0.0
	 *
	 * @param array $button The button settings.
	 * @return bool false to hide button.
	 */
	public function hide_private_message_button( $button ) {
		return apply_filters(
			'ms_rule_buddypress_hide_private_message_button',
			false,
			$button,
			$this
		);
	}

	/**
	 * Protect friendship request.
	 *
	 * @since  1.0.0
	 *
	 */
	protected function protect_friendship_request() {
		if ( ! parent::has_access( MS_Addon_BuddyPress_Rule::PROTECT_FRIENDSHIP ) ) {
			$this->add_filter(
				'bp_get_add_friend_button',
				'hide_add_friend_button'
			);
		}

		do_action(
			'ms_rule_buddypress_protect_friendship_request',
			$this
		);
	}

	/**
	 * Adds filter to prevent friendship button rendering.
	 *
	 * Related Action Hooks:
	 * - bp_get_add_friend_button
	 *
	 * @since  1.0.0
	 *
	 * @param array $button The button settings.
	 * @return array The current button settings.
	 */
	public function hide_add_friend_button( $button ) {
		$this->add_filter( 'bp_get_button', 'prevent_button_rendering' );

		return apply_filters(
			'ms_rule_buddypress_hide_add_friend_button',
			$button,
			$this
		);
	}

	/**
	 * Prevents button rendering.
	 *
	 * Related Action Hooks:
	 * - bp_get_button
	 *
	 * @since  1.0.0
	 *
	 * @return boolean false to prevent button rendering.
	 */
	public function prevent_button_rendering() {
		$this->remove_filter( 'bp_get_button', 'prevent_button_rendering' );

		return apply_filters(
			'ms_rule_buddypress_prevent_button_rendering',
			false,
			$this
		);
	}

	/**
	 * Checks the ability to create groups.
	 *
	 * Related Action Hooks:
	 * - bp_user_can_create_groups
	 *
	 * @since  1.0.0
	 *
	 * @param string $can_create The initial access.
	 * @return string The initial template if current user can create groups, otherwise blocking message.
	 */
	public function protect_create_bp_group( $can_create, $restricted ) {
		$can_create = false;

		if ( parent::has_access( MS_Addon_BuddyPress_Rule::PROTECT_GROUP_CREATION ) ) {
			$can_create = true;
		}

		return apply_filters(
			'ms_rule_buddypress_protect_create_bp_group',
			$can_create,
			$this
		);
	}

	/**
	 * Get content to protect.
	 *
	 * @since  1.0.0
	 *
	 * @param $args Not used, but kept due to method override.
	 * @return array The content eligible to protect by this rule domain.
	 */
	public function get_contents( $args = null ) {
		$contents = array();

		$contents[ MS_Addon_BuddyPress_Rule::PROTECT_ALL ] = (object) array(
			'id' 			=> MS_Addon_BuddyPress_Rule::PROTECT_ALL,
			'name' 			=> __( 'All BuddyPress Pages', 'membership2' ),
			'type' 			=> $this->rule_type,
			'description' 	=> __( 'Protect all BuddyPress pages. This rule can be combined with any of the other rules.', 'membership2' ),
			'access' 		=> $this->get_rule_value( MS_Addon_BuddyPress_Rule::PROTECT_ALL ),
		);

		$contents[ MS_Addon_BuddyPress_Rule::PROTECT_GROUP_CREATION ] = (object) array(
			'id' 			=> MS_Addon_BuddyPress_Rule::PROTECT_GROUP_CREATION,
			'name' 			=> __( 'Group creation', 'membership2' ),
			'type' 			=> $this->rule_type,
			'description' 	=> __( 'Only members can create new groups.', 'membership2' ),
			'access' 		=> $this->get_rule_value( MS_Addon_BuddyPress_Rule::PROTECT_GROUP_CREATION ),
		);

		$contents[ MS_Addon_BuddyPress_Rule::PROTECT_FRIENDSHIP ] = (object) array(
			'id' 			=> MS_Addon_BuddyPress_Rule::PROTECT_FRIENDSHIP,
			'name' 			=> __( 'Friendship request', 'membership2' ),
			'type' 			=> $this->rule_type,
			'description' 	=> __( 'Only allow members to send friendship requests.', 'membership2' ),
			'access' 		=> $this->get_rule_value( MS_Addon_BuddyPress_Rule::PROTECT_FRIENDSHIP ),
		);

		$contents[ MS_Addon_BuddyPress_Rule::PROTECT_PRIVATE_MSG ] = (object) array(
			'id' 			=> MS_Addon_BuddyPress_Rule::PROTECT_PRIVATE_MSG,
			'name' 			=> __( 'Private messaging', 'membership2' ),
			'type' 			=> $this->rule_type,
			'description' 	=> __( 'Only allow members to send private messages.', 'membership2' ),
			'access' 		=> $this->get_rule_value( MS_Addon_BuddyPress_Rule::PROTECT_PRIVATE_MSG ),
		);

		$contents[ MS_Addon_BuddyPress_Rule::PROTECT_MEMBERS ] = (object) array(
			'id' 			=> MS_Addon_BuddyPress_Rule::PROTECT_MEMBERS,
			'name' 			=> __( 'Member listing', 'membership2' ),
			'type' 			=> $this->rule_type,
			'description' 	=> __( 'Only members can see the BuddyPress Member Directory and Member Profiles.', 'membership2' ),
			'access' 		=> $this->get_rule_value( MS_Addon_BuddyPress_Rule::PROTECT_MEMBERS ),
		);

		return apply_filters(
			'ms_rule_buddypress_get_content',
			$contents,
			$this
		);
	}
}