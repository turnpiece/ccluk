<?php
/**
 * Exposes the public API.
 *
 * The simplest way to use the API is via an WordPress action `ms_init` that
 * runs some code as soon as the API becomes available:
 *
 *     // Run some code as early as possible.
 *     add_action( 'ms_init', 'my_api_hook' );
 *     function my_api_hook( $api ) {
 *         $memberships = $api->list_memberships();
 *     }
 *
 * **Recommended implementation structure**
 *
 *     class My_Membership2_Implementation {
 *         protected $api = null;
 *
 *         // Function is always executed. Will create 1 Implementation object.
 *         static public function setup() {
 *             static $Inst = null;
 *             if ( null === $Inst ) {
 *                 $Inst = new My_Membership2_Implementation();
 *             }
 *         }
 *
 *         // Function set up the api hook.
 *         private function __construct() {
 *             add_action( 'ms_init', array( $this, 'init' ) );
 *         }
 *
 *         // Function is only run when Membership2 is present + active.
 *         public function init( $api ) {
 *             $this->api = $api;
 *             // The main init code should come here now!
 *         }
 *
 *         // Add other event handlers and helper functions.
 *         // You can use $this->api in other functions to access the API object.
 *     }
 *     My_Membership2_Implementation::setup();
 *
 * ----------------
 *
 * We also add the WordPress filter `ms_active` to check if the plugin is
 * enabled and loaded. As long as this filter returns `false` the API cannot
 * be used:
 *
 *     // Check if the API object is available.
 *     if ( apply_filters( 'ms_active', false ) ) { ... }
 *
 * Different ways to access the M2 API object:
 *
 *     // 1. Our recommendation:
 *     add_action( 'ms_init', 'my_api_hook' );
 *     function my_api_hook( $api ) {
 *     }
 *
 *     // 2. Use a procedural approach. Use in init hook or later!
 *     $api = ms_api();
 *
 *     // 3. Not recommended: Direct access to the `$api` property:
 *     $api = MS_Plugin::$api;
 *
 *     // 4. Not recommended: Singleton access:
 *     $api = MS_Controller_Api::instance();
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Controller
 */
class MS_Controller_Api extends MS_Hooker {

	/**
	 * A reference to the Membership2 settings object.
	 *
	 * @since  1.0.0
	 * @api
	 * @var MS_Model_Settings
	 */
	public $settings = null;

	/**
	 * Stores a list of custom payment gateways.
	 *
	 * @since  1.0.1.0
	 * @internal
	 * @var array
	 */
	protected $gateways = array();

	/**
	 * Returns the singleton object.
	 *
	 * @since  1.0.1.2
	 * @return MS_Controller_Api
	 */
	static public function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new MS_Controller_Api();
		}

		return $Inst;
	}

	/**
	 * Private constructor: Singleton pattern.
	 *
	 * @since  1.0.0
	 * @internal
	 */
	protected function __construct() {
		$this->settings = MS_Plugin::instance()->settings;

		/**
		 * Simple check to allow other plugins to quickly find out if
		 * Membership2 is loaded and the API was initialized.
		 *
		 * Example:
		 *   if ( apply_filters( 'ms_active', false ) ) { ... }
		 *
		 * @since  1.0.0
		 */
		add_filter( 'ms_active', '__return_true' );

		/**
		 * Make the API controller accessible via static property.
		 *
		 * Example:
		 *   $api = MS_Plugin::$api;
		 *
		 * Alternative:
		 *   $api = apply_filters( 'ms_api', false );
		 *
		 * @since  1.0.0
		 */
		MS_Plugin::set_api( $this );

		/**
		 * Notify other plugins that Membership2 is ready.
		 *
		 * @since  1.0.0
		 */
		do_action( 'ms_init', $this );
	}

	/**
	 * Maintain compatibility with MS_Controller interface.
	 *
	 * @since  1.0.1.2
	 */
	public function admin_init() { }

	/**
	 * Returns either the current member or the member with the specified id.
	 *
	 * If the specified user does not exist then false is returned.
	 *
	 *     // Useful functions of the Member object:
	 *     $member->has_membership( $membership_id )
	 *     $member->get_subscription( $membership_id )
	 *
	 * @since  1.0.0
	 * @api
	 *
	 * @param  int $user_id User_id
	 * @return MS_Model_Member|false The Member model.
	 */
	public function get_member( $user_id ) {
		$user_id 	= absint( $user_id );
		$member 	= MS_Factory::load( 'MS_Model_Member', $user_id );

		if ( ! $member->is_valid() ) {
			$member = false;
		}

		return $member;
	}

	/**
	 * Returns the Member object of the current user.
	 *
	 * @since  1.0.0
	 * @api
	 *
	 * @return MS_Model_Member The Member model.
	 */
	public function get_current_member() {
		$member = MS_Model_Member::get_current_member();

		return $member;
	}

	/**
	 * Returns if the member is admin or not
	 *
	 * @since 1.0.2.8
	 * @api
	 *
	 * @return bool
	 */
	public function is_admin_user( $user_id = null ) {
		if( $user_id == null ) {
			$user_id = get_current_user_id();
		}

		return MS_Model_Member::is_admin_user( $user_id );
	}

	/**
	 * Returns a single membership object.
	 *
	 * Other plugins can store and accuess custom settings for each membership:
	 *
	 *     // Create a custom value in the membership
	 *     $membership->set_custom_data( 'the_key', 'the_value' );
	 *     $membership->save(); // Custom data is now saved to database.
	 *
	 *     // Access and delete the custom value
	 *     $value = $membership->get_custom_data( 'the_key' );
	 *     $membership->delete_custom_data( 'the_key' );
	 *     $membership->save(); // Custom data is now deleted from database.
	 *
	 * @since  1.0.0
	 * @api
	 *
	 * @param  int|string $membership_id The membership-ID or name/slug.
	 * @return MS_Model_Membership The membership object.
	 */
	public function get_membership( $membership_id ) {
		if ( ! is_numeric( $membership_id ) ) {
			$membership_id = $this->get_membership_id( $membership_id );
		}

		$membership = MS_Factory::load( 'MS_Model_Membership', intval( $membership_id ) );

		return $membership;
	}

	/**
	 * Returns the membership-ID that matches the specified Membership name or
	 * slug.
	 *
	 * If multiple memberships have the same name then the one with the lowest
	 * ID (= the oldest) will be returned.
	 *
	 * Name or slug are case-IN-sensitive ('slug' and 'SLUG' are identical)
	 * Wildcards are not allowed, the string must match exactly.
	 *
	 * @since  1.0.1.2
	 * @param  string $name_or_slug The Membership name or slug to search.
	 * @return int|false The membership ID or false.
	 */
	public function get_membership_id( $name_or_slug ) {
		return MS_Model_Membership::get_membership_id( $name_or_slug );
	}

	/**
	 * Returns a single subscription object of the specified user.
	 *
	 * If the user did not subscribe to the given membership then false is
	 * returned.
	 *
	 * Each subscription also offers custom data fields
	 * (see the details in get_membership() for details)
	 *
	 * @since  1.0.0
	 * @api
	 *
	 * @param  int $user_id The user ID.
	 * @param  int|string $membership_id The membership-ID or name/slug.
	 * @return MS_Model_Relationship|false The subscription object.
	 */
	public function get_subscription( $user_id, $membership_id ) {
		$subscription 	= false;
		$membership 	= $this->get_membership( $membership_id );

		$member = MS_Factory::load( 'MS_Model_Member', $user_id );
		if ( $member && $member->has_membership( $membership->id ) ) {
			$subscription = $member->get_subscription( $membership->id );
		}

		return $subscription;
	}

	/**
	 * Add a new subscription for the specified user.
	 *
	 * If the membership is free the subscription instantly is ACTIVE.
	 * Otherwise the subscription is set to PENDING until the user makes the
	 * payment via the M2 checkout page.
	 *
	 * @since  1.0.1.2
	 * @param  int $user_id The User-ID.
	 * @param  int|string $membership_id The membership-ID or name/slug.
	 * @return MS_Model_Relationship|null The new subscription object.
	 */
	public function add_subscription( $user_id, $membership_id ) {
		$subscription 	= false;
		$membership 	= $this->get_membership( $membership_id );

		$member = MS_Factory::load( 'MS_Model_Member', $user_id );
		if ( $member ) {
			$subscription = $member->add_membership( $membership->id, '' );

			// Activate free memberships instantly.
			if ( $membership->is_free() ) {
				$subscription->add_payment( 0, MS_Gateway_Free::ID, 'free' );
			}
		}

		return $subscription;
	}

	/**
	 * Returns a list of all available Memberships.
	 *
	 * @since  1.0.0
	 * @api
	 *
	 * @param  bool $list_all If set to true then also private and internal
	 *         Memberships (e.g. Guest Membership) are included.
	 *         Default is false which returns only memberships that a guest user
	 *         can subscribe to.
	 * @return MS_Model_Membership[] List of all available Memberships.
	 */
	public function list_memberships( $list_all = false ) {
		$args = array(
			'include_base' 	=> false,
			'include_guest' => true,
		);
		$list = MS_Model_Membership::get_memberships( $args );

		if ( ! $list_all ) {
			foreach ( $list as $key => $item ) {
				if ( ! $item->active ) { unset( $list[$key] ); }
				elseif ( $item->private ) { unset( $list[$key] ); }
			}
		}

		return $list;
	}

	/**
	 * Tries to determine the currently displayed membership.
	 *
	 * Detection logic:
	 * 1. If a valid preferred value was specified then this value is used.
	 * 2. Examine REQUEST data and look for membership/subscription/invoice.
	 * 3. Check currently logged in user and use the top-priority subscription.
	 * 4. If no membership could be detected the response value is bool FALSE.
	 *
	 * @since  1.0.1.0
	 * @return false|MS_Model_Membership The detected Membership or false.
	 */
	public function detect_membership() {
		$result = false;

		$membership_id = apply_filters(
			'ms_detect_membership_id',
			false, // Do not suggest/force a membership ID.
			false, // Also check the logged-in users subscriptions.
			true   // Do not return system memberships.
		);
		if ( $membership_id ) {
			$result = MS_Factory::load( 'MS_Model_Membership', $membership_id );
			if ( $result->is_system() ) { $result = false; }
		}

		return apply_filters(
			'ms_detect_membership_result',
			$result,
			$membership_id
		);
	}

	/**
	 * Create your own payment gateway and hook it up with Membership 2 by using
	 * this function!
	 *
	 * Creating your own payment gateway requires good php skills. To get you
	 * started follow these steps:
	 *
	 * 1. Copy the folder "/app/gateway/manual" to the "wp-contents/plugins"
	 *    folder, and name it "membership-mygateway"
	 *
	 * 2. Rename all files inside the "membership-mygateway" and replace the
	 *    term "manual" with "mygateway"
	 *
	 * 3. Edit all files, rename the class names inside the files to
	 *    "_Mygateway" (replacing "_Manual")
	 *
	 * 4. In class MS_Gateway_Mygateway make following changes:
	 *   - Set the value of const ID to "mygateway"
	 *
	 *   - Change the assigned name in function "after_load"
	 *
	 *   - Add a plugin header to the file, e.g.
	 *     /*
	 *      * Plugin name: Membership 2 Mygateway
	 *      * /
	 *
	 *   - Add the following line to the bottom of the file:
	 *     add_action( 'ms_init', 'mygateway_register' );
	 *     function mygateway_register( $api ) {
	 *         $api->register_payment_gateway(
	 *             MS_Gateway_Mygateway::ID,
	 *             'MS_Gateway_Mygateway'
	 *         )
	 *     }
	 *
	 * Now you have created a new plugin that registers a custom payment gateway
	 * for Membership 2! Implementing the payment logic is up to you - you can
	 * get a lot of insight by reviewing the existing payment gateways.
	 *
	 * @since 1.0.1.0
	 * @api
	 *
	 * @param string $id The ID of the new gateway.
	 * @param string $class The Class-name of the new gateway.
	 */
	public function register_payment_gateway( $id, $class ) {
		$this->gateways[$id] = $class;
		$this->add_action( 'ms_model_gateway_register', '_register_gateways' );
	}

	/**
	 * Internal filter callback function that registers custom payment gateways.
	 *
	 * @since  1.0.1.0
	 * @internal
	 *
	 * @param  array $gateways List of payment gateways.
	 * @return array New list of payment gateways.
	 */
	public function _register_gateways( $gateways ) {
		foreach ( $this->gateways as $id => $class ) {
			$gateways[$id] = $class;
		}

		return $gateways;
	}

	/**
	 * Membership2 has a nice integrated debugging feature. This feature can be
	 * helpful for other developers so this API offers a simple way to access
	 * the debugging feature.
	 *
	 * Also note that all membership objects come with the built-in debug
	 * function `$obj->dump()` to quickly analyze the object.
	 *
	 *     // Example of $obj->dump() usage
	 *     $user = MS_Plugin::$api->current_member();
	 *     $user->dump();
	 *
	 * @since  1.0.0
	 * @api
	 *
	 * @param  mixed $data The value to dump to the output stream.
	 */
	public function debug( $data ) {
		mslib3()->debug->enable();
		// Intended debug output, leave it here.
		mslib3()->debug->dump( $data );
	}

}


/**
 * TEMPLATE TAG FUNCTIONS.
 */

if ( ! function_exists( 'ms_has_membership' ) ) {
	/**
	 * Template tag: Check if the current user has a specific membership.
	 *
	 * Multiple memberships can be specified by adding more parameters to the
	 * function call.
	 *
	 * Examples:
	 *
	 * <?php if ( ms_has_membership() ) : ?> Current user has *any* membership?
	 *
	 * <?php if ( ms_has_membership(100) ) : ?>  Current user has membership 100?
	 *
	 * <?php if ( ms_has_membership(100,110) ) : ?>  Current user has membership 100 or 110?
	 *
	 * @since  1.0.1.0
	 * @api Template Tag
	 * @param  int $id Optional. Membership-ID to check.
	 *         If no value is specified the function will check if the member
	 *         has any membership at all. Guest/Default memberships are ignored.
	 * @param  int $id2 Optional. You can specify multiple membership-IDs. Just
	 *         add more parameters to the function call.
	 * @return bool True if the current member has any/the specified membership.
	 */
	function ms_has_membership( $id = 0 ) {
		$result 		= false;
		$current_member = MS_Plugin::$api->get_current_member();

		if ( func_num_args() == 0 ) {
			$args = array( 0 ); // ID 0 will check for _any_ membership.
		} else {
			$args = func_get_args();
		}

		// Check all params and return true if the member has any membership.
		foreach ( $args as $check_id ) {
			if ( $current_member->has_membership( $check_id ) ) {
				$result = true;
				break;
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'ms_api' ) ) {
	/**
	 * Procedural way to load the API instance.
	 *
	 * Call this function inside the init hook or later. Using it earlier might
	 * cause problems because other parts of M2 might not be completely
	 * initialized.
	 *
	 * @since  1.0.1.2
	 * @api
	 * @return MS_Controller_Api
	 */
	function ms_api() {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong(
				'ms_api',
				'ms_api() is called before the "init" hook, this is too early!',
				'1.0.1.2'
			);
		}

		return MS_Controller_Api::instance();
	}
}