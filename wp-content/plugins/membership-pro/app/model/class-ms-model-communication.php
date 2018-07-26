<?php
/**
 * Model.
 * @package Membership2
 */

/**
 * Communication model.
 *
 * Persisted by parent class MS_Model_CustomPostType.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Model
 */
class MS_Model_Communication extends MS_Model_CustomPostType {

	/**
	 * Model custom post type.
	 *
	 * Both static and class property are used to handle php 5.2 limitations.
	 *
	 * @since 1.0.0
	 * @var   string $POST_TYPE
	 */
	protected static $POST_TYPE = 'ms_communication';

	/**
	 * Holds a list of all Communication posts in the database.
	 *
	 * @since 1.0.1.0
	 * @var   array $Communication_IDs
	 */
	protected static $Communication_IDs = array();

	/**
	 * Communication types, static reference to loaded child objects.
	 *
	 * @since 1.0.0
	 * @var   array $communications
	 */
	protected static $communications = array();

	/**
	 * Communication type constants.
	 *
	 * @since 1.0.0
	 * @see   $type
	 * @var   string The communication type
	 */
	const COMM_TYPE_REGISTRATION 			= 'type_registration';
	const COMM_TYPE_REGISTRATION_FREE 		= 'type_registration_free';
	const COMM_TYPE_REGISTRATION_VERIFY 	= 'type_registration_verify';
	const COMM_TYPE_SIGNUP 					= 'type_signup';
	const COMM_TYPE_RESETPASSWORD 			= 'type_resetpassword';
	const COMM_TYPE_RENEWED 				= 'renewed';
	const COMM_TYPE_INVOICE 				= 'type_invoice';
	const COMM_TYPE_BEFORE_FINISHES 		= 'type_before_finishes';
	const COMM_TYPE_FINISHED 				= 'type_finished';
	const COMM_TYPE_AFTER_FINISHES 			= 'type_after_finishes';
	const COMM_TYPE_CANCELLED 				= 'type_cancelled';
	const COMM_TYPE_BEFORE_TRIAL_FINISHES 	= 'type_before_trial_finishes';
	const COMM_TYPE_INFO_UPDATE 			= 'type_info_update';
	const COMM_TYPE_CREDIT_CARD_EXPIRE 		= 'type_credit_card_expire';
	const COMM_TYPE_FAILED_PAYMENT 			= 'type_failed_payment';
	const COMM_TYPE_BEFORE_PAYMENT_DUE 		= 'type_before_payment_due';
	const COMM_TYPE_AFTER_PAYMENT_DUE 		= 'type_after_payment_due';

	/**
	 * Communication variable constants.
	 *
	 * These variables are used inside emails and are replaced by variable value.
	 *
	 * @since 1.0.0
	 * @see   comm_vars
	 * @var   string The communication variable name.
	 */
	const COMM_VAR_MS_NAME 					= '%ms-name%';
	const COMM_VAR_MS_DESCRIPTION 			= '%ms-description%';
	const COMM_VAR_MS_INVOICE 				= '%ms-invoice%';
	const COMM_VAR_MS_ACCOUNT_PAGE_URL 		= '%ms-account-page-url%';
	const COMM_VAR_MS_REMAINING_DAYS 		= '%ms-remaining-days%';
	const COMM_VAR_MS_REMAINING_TRIAL_DAYS 	= '%ms-remaining-trial-days%';
	const COMM_VAR_MS_EXPIRY_DATE 			= '%ms-expiry-date%';
	const COMM_VAR_USER_DISPLAY_NAME 		= '%user-display-name%';
	const COMM_VAR_USER_FIRST_NAME 			= '%user-first-name%';
	const COMM_VAR_USER_LAST_NAME 			= '%user-last-name%';
	const COMM_VAR_USERNAME 				= '%username%';
	const COMM_VAR_PASSWORD 				= '%password%';
	const COMM_VAR_RESETURL 				= '%reset-url%';
	const COMM_VAR_BLOG_NAME 				= '%blog-name%';
	const COMM_VAR_BLOG_URL 				= '%blog-url%';
	const COMM_VAR_NET_NAME 				= '%network-name%';
	const COMM_VAR_NET_URL 					= '%network-url%';
	const COMM_VAR_VERIFICATION_URL 		= '%verification-url%';

	/**
	 * Communication type.
	 *
	 * @since 1.0.0
	 * @var   string The communication type.
	 */
	protected $type;

	/**
	 * Email subject.
	 *
	 * @since 1.0.0
	 * @var   string The email subject.
	 */
	protected $subject;

	/**
	 * Email body message.
	 *
	 * @since 1.0.0
	 * @var   string The email body message.
	 */
	protected $message;

	/**
	 * Communication period enabled.
	 *
	 * When the communication has a period to consider.
	 *
	 * @since 1.0.0
	 * @var   bool The period enabled status.
	 */
	protected $period_enabled = false;

	/**
	 * The communication period settings.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected $period = array(
		'period_unit' => 1,
		'period_type' => MS_Helper_Period::PERIOD_TYPE_DAYS,
	);

	/**
	 * Communication enabled status.
	 *
	 * @since 1.0.0
	 * @var   string The communication enabled status.
	 */
	protected $enabled;

	/**
	 * Communication carbon copy enabled.
	 *
	 * @since 1.0.0
	 * @var   string The communication carbon copy enabled status.
	 */
	protected $cc_enabled;

	/**
	 * Communication copied recipient email.
	 *
	 * @since 1.0.0
	 * @var   string The copied recipient email.
	 */
	protected $cc_email;

	/**
	 * Defines a membership_id if this template overrides a default template.
	 *
	 * Default setting is 0, which indicates that the object is the default
	 * template of the specific type.
	 *
	 * @since 1.0.1.0
	 * @var   int
	 */
	protected $membership_id = 0;

	/**
	 * Defines if a membership specific message should be used (true) or the
	 * default communication settings should be used (false).
	 *
	 * Only relevant when $membership_id is set.
	 *
	 * @since 1.0.1.0
	 * @var   bool
	 */
	protected $override = false;

	/**
	 * Communication variables.
	 *
	 * @since 1.0.0
	 * @var   string The communication vars.
	 */
	protected $comm_vars = array();

	/**
	 * Communication queue of emails to send.
	 *
	 * @since  1.0.0
	 * @var string The communication queue.
	 */
	protected $queue = array();

	/**
	 * Communication sent emails queue.
	 *
	 * Keep for a limited history of sent emails.
	 *
	 * @since 1.0.0
	 * @var   string The communication sent queue.
	 */
	protected $sent_queue = array();

	/**
	 * Communication default content type.
	 *
	 * @since 1.0.0
	 * @var   string The communication default content type.
	 */
	protected $content_type = 'text/html';


	/**
	 * Defines if it should be shown to admin
	 *
	 * Only relevant for user specific mails
	 *
	 * @since 1.1.3
	 * @var   bool
	 */
	protected $show_admin_cc = true;

	/**
	 * Don't persist this fields.
	 *
	 * @since 1.0.0
	 * @var   string[] The fields to ignore when persisting.
	 */
	static public $ignore_fields = array(
		'message',
		'name',
		'comm_vars',
	);


	/*
	 *
	 *
	 * -------------------------------------------------------------- COLLECTION
	 */


	/**
	 * Returns the post-type of the current object.
	 *
	 * @since  1.0.0
	 * @return string The post-type name.
	 */
	public static function get_post_type() {
		return parent::_post_type( self::$POST_TYPE );
	}

	/**
	 * Get custom register post type args for this model.
	 *
	 * @since  1.0.0
	 */
	public static function get_register_post_type_args() {
		$args = array(
			'label' 				=> __( 'Membership2 Email Templates', 'membership2' ),
            'exclude_from_search' 	=> true
		);

		return apply_filters(
			'ms_customposttype_register_args',
			$args,
			self::get_post_type()
		);
	}

	/**
	 * Initializes the communications module.
	 *
	 * @since  1.0.1.0
	 */
	public static function init() {
	}

	/**
	 * Get communication types.
	 *
	 * @since  1.0.0
	 *
	 * @return array The communication types.
	 */
	public static function get_communication_types() {
		static $Types = null;

		if ( null === $Types ) {
			if ( MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_AUTO_MSGS_PLUS ) ) {
				$Types = array(
					self::COMM_TYPE_REGISTRATION,
					self::COMM_TYPE_REGISTRATION_FREE,
					self::COMM_TYPE_SIGNUP,
					self::COMM_TYPE_REGISTRATION_VERIFY,
					self::COMM_TYPE_RESETPASSWORD,
					self::COMM_TYPE_RENEWED,
					self::COMM_TYPE_INVOICE,
					self::COMM_TYPE_BEFORE_FINISHES,
					self::COMM_TYPE_FINISHED,
					self::COMM_TYPE_AFTER_FINISHES,
					self::COMM_TYPE_CANCELLED,
					self::COMM_TYPE_BEFORE_TRIAL_FINISHES,
					self::COMM_TYPE_INFO_UPDATE,
					self::COMM_TYPE_CREDIT_CARD_EXPIRE,
					self::COMM_TYPE_FAILED_PAYMENT,
					self::COMM_TYPE_BEFORE_PAYMENT_DUE,
					self::COMM_TYPE_AFTER_PAYMENT_DUE,
				);
			} else {
				$Types = array(
					self::COMM_TYPE_REGISTRATION,
					self::COMM_TYPE_REGISTRATION_VERIFY,
					self::COMM_TYPE_INVOICE,
					self::COMM_TYPE_FINISHED,
					self::COMM_TYPE_CANCELLED,
					self::COMM_TYPE_INFO_UPDATE,
					self::COMM_TYPE_CREDIT_CARD_EXPIRE,
					self::COMM_TYPE_FAILED_PAYMENT,
				);
			}
		}

		return apply_filters(
			'ms_model_communication_get_communication_types',
			$Types
		);
	}

	/**
	 * Get Communication types and respective classes.
	 *
	 * @since  1.0.0
	 *
	 * @return array {
	 *     Return array of $type => $class_name.
	 *
	 *     @type string $type The communication type.
	 *     @type string $class_name The class name of the communication type.
	 * }
	 */
	public static function get_communication_type_classes() {
		static $type_classes;

		if ( empty( $type_classes ) ) {
			$type_classes = array(
				self::COMM_TYPE_REGISTRATION 			=> 'MS_Model_Communication_Registration',
				self::COMM_TYPE_REGISTRATION_FREE 		=> 'MS_Model_Communication_Registration_Free',
				self::COMM_TYPE_REGISTRATION_VERIFY		=> 'MS_Model_Communication_Registration_Verify',
				self::COMM_TYPE_SIGNUP 					=> 'MS_Model_Communication_Signup',
				self::COMM_TYPE_RESETPASSWORD 			=> 'MS_Model_Communication_Resetpass',
				self::COMM_TYPE_RENEWED 				=> 'MS_Model_Communication_Renewed',
				self::COMM_TYPE_INVOICE 				=> 'MS_Model_Communication_Invoice',
				self::COMM_TYPE_BEFORE_FINISHES 		=> 'MS_Model_Communication_Before_Finishes',
				self::COMM_TYPE_FINISHED 				=> 'MS_Model_Communication_Finished',
				self::COMM_TYPE_AFTER_FINISHES 			=> 'MS_Model_Communication_After_Finishes',
				self::COMM_TYPE_CANCELLED 				=> 'MS_Model_Communication_Cancelled',
				self::COMM_TYPE_BEFORE_TRIAL_FINISHES 	=> 'MS_Model_Communication_Before_Trial_Finishes',
				self::COMM_TYPE_INFO_UPDATE 			=> 'MS_Model_Communication_Info_Update',
				self::COMM_TYPE_CREDIT_CARD_EXPIRE 		=> 'MS_Model_Communication_Credit_Card_Expire',
				self::COMM_TYPE_FAILED_PAYMENT 			=> 'MS_Model_Communication_Failed_Payment',
				self::COMM_TYPE_BEFORE_PAYMENT_DUE 		=> 'MS_Model_Communication_Before_Payment_Due',
				self::COMM_TYPE_AFTER_PAYMENT_DUE 		=> 'MS_Model_Communication_After_Payment_Due',
			);
		}

		return apply_filters(
			'ms_model_communication_get_communication_type_classes',
			$type_classes
		);
	}

	/**
	 * Get Communication types and respective titles.
	 *
	 * @since  1.0.0
	 *
	 * @param  mixed $membership Optional. If specified only Comm-Types relevant
	 *               for that membership are returned.
	 * @return array {
	 *     Return array of $type => $title.
	 *
	 *     @type string $type The communication type.
	 *     @type string $title The title of the communication type.
	 * }
	 */
	public static function get_communication_type_titles( $membership = null ) {
		$type_titles = array(
			self::COMM_TYPE_SIGNUP 					=> __( 'Signup - User account created', 'membership2' ),
			self::COMM_TYPE_RESETPASSWORD 			=> __( 'Signup - Forgot Password', 'membership2' ),
			self::COMM_TYPE_REGISTRATION 			=> __( 'Subscription - Completed with payment', 'membership2' ),
			self::COMM_TYPE_REGISTRATION_FREE 		=> __( 'Subscription - Completed (free membership)', 'membership2' ),
			self::COMM_TYPE_REGISTRATION_VERIFY 	=> __( 'Signup - Verify your email', 'membership2' ),
			self::COMM_TYPE_RENEWED 				=> __( 'Subscription - Renewed', 'membership2' ),
			self::COMM_TYPE_BEFORE_FINISHES 		=> __( 'Subscription - Before expires', 'membership2' ),
			self::COMM_TYPE_FINISHED 				=> __( 'Subscription - Expired', 'membership2' ),
			self::COMM_TYPE_AFTER_FINISHES 			=> __( 'Subscription - After expired', 'membership2' ),
			self::COMM_TYPE_CANCELLED 				=> __( 'Subscription - Cancelled', 'membership2' ),
			self::COMM_TYPE_BEFORE_TRIAL_FINISHES 	=> __( 'Subscription - Trial finished', 'membership2' ),
			self::COMM_TYPE_INFO_UPDATE 			=> __( 'Payment - Profile updated', 'membership2' ),
			self::COMM_TYPE_CREDIT_CARD_EXPIRE 		=> __( 'Payment - Credit Card expires', 'membership2' ),
			self::COMM_TYPE_INVOICE 				=> __( 'Payment - Receipt/Invoice', 'membership2' ),
			self::COMM_TYPE_FAILED_PAYMENT 			=> __( 'Payment - Failed', 'membership2' ),
			self::COMM_TYPE_BEFORE_PAYMENT_DUE 		=> __( 'Payment - Before due', 'membership2' ),
			self::COMM_TYPE_AFTER_PAYMENT_DUE 		=> __( 'Payment - After due', 'membership2' ),
		);

		foreach ( $type_titles as $type => $title ) {
			if ( ! self::is_valid_communication_type( $type ) ) {
				unset( $type_titles[ $type ] );
			}
		}

		if ( $membership && is_numeric( $membership ) ) {
			$membership = MS_Factory::load( 'MS_Model_Membership', $membership );
		}

		if ( $membership instanceof MS_Model_Membership ) {
			unset( $type_titles[ self::COMM_TYPE_SIGNUP ] );
			unset( $type_titles[ self::COMM_TYPE_RESETPASSWORD ] );

			if ( ! $membership->has_trial() ) {
				unset( $type_titles[ self::COMM_TYPE_BEFORE_TRIAL_FINISHES ] );
			}

			if ( $membership->is_free() ) {
				unset( $type_titles[ self::COMM_TYPE_REGISTRATION ] );
				unset( $type_titles[ self::COMM_TYPE_INFO_UPDATE ] );
				unset( $type_titles[ self::COMM_TYPE_CREDIT_CARD_EXPIRE ] );
				unset( $type_titles[ self::COMM_TYPE_INVOICE ] );
				unset( $type_titles[ self::COMM_TYPE_FAILED_PAYMENT ] );
				unset( $type_titles[ self::COMM_TYPE_BEFORE_PAYMENT_DUE ] );
				unset( $type_titles[ self::COMM_TYPE_AFTER_PAYMENT_DUE ] );
			} else {
				unset( $type_titles[ self::COMM_TYPE_REGISTRATION_FREE ] );
			}

			if ( MS_Model_Membership::PAYMENT_TYPE_PERMANENT == $membership->payment_type ) {
				unset( $type_titles[ self::COMM_TYPE_BEFORE_FINISHES ] );
				unset( $type_titles[ self::COMM_TYPE_FINISHED ] );
				unset( $type_titles[ self::COMM_TYPE_AFTER_FINISHES ] );
			}
		}

		return apply_filters(
			'ms_model_communication_get_communication_type_titles',
			$type_titles
		);
	}

	/**
	 * Validate communication type.
	 *
	 * @since  1.0.0
	 *
	 * @param string $type The type to validate.
	 * @return bool True if is valid.
	 */
	public static function is_valid_communication_type( $type ) {
		$valid = ! empty( $type )
			&& in_array( $type, self::get_communication_types() );

		return apply_filters(
			'ms_model_communication_is_valid_communication_type',
			$valid,
			$type
		);
	}

	/**
	 * Get count of all pending email messages.
	 *
	 * @since  1.0.1.0
	 * @return int
	 */
	static public function get_queue_count() {
		$count 			= 0;
		$memberships 	= self::get_communication_ids( null );

		foreach ( $memberships as $ids ) {
			foreach ( $ids as $id ) {
				$comm 	= MS_Factory::load( 'MS_Model_Communication', $id );
				$count 	+= count( $comm->queue );
			}
		}

		return apply_filters(
			'ms_model_communication_get_queue_count',
			$count,
			$ids
		);
	}

	/**
	 * Returns a list of communication IDs for the specified membership.
	 *
	 * Possible values:
	 *  null .. All communication IDs are returned.
	 *  0 .. Global communication IDs are returned (defined in Settings page).
	 *  <MembershipID> .. Communication IDs of that membership are returned.
	 *
	 * @since  1.0.1.0
	 * @param  int $membership Indtifies a membership.
	 * @return array List of communication IDs.
	 */
	static protected function get_communication_ids( $membership ) {
		if ( ! isset( self::$Communication_IDs[0] ) ) {
			self::$Communication_IDs = array(
				0 => array(),
			);
			$args = array(
				'post_type' 		=> self::get_post_type(),
				'post_status' 		=> 'any',
				'fields' 			=> 'ids',
				'posts_per_page' 	=> -1,
			);

			MS_Factory::select_blog();
			$query = new WP_Query( $args );
			$items = $query->posts;
			MS_Factory::revert_blog();

			foreach ( $items as $id ) {
				$comm = MS_Factory::load( 'MS_Model_Communication', $id );
				self::$Communication_IDs[ $comm->membership_id ][ $comm->type ] = $id;
			}
		}

		if ( $membership instanceof MS_Model_Membership ) {
			$key = $membership->id;
		} else {
			$key = $membership;
		}

		if ( null === $key ) {
			$result = self::$Communication_IDs;
		} elseif ( isset( self::$Communication_IDs[ $key ] ) ) {
			$result = self::$Communication_IDs[ $key ];
		} else {
			$result = array();
		}

		return $result;
	}

	/**
	 * Retrieve and return all communication types objects.
	 *
	 * @since  1.0.0
	 *
	 * @param  MS_Model_Membership $membership Optional. If defined then we try
	 *         to load overridden messages for that membership with fallback to
	 *         the default messages.
	 * @return MS_Model_Communication[] The communication objects array.
	 */
	public static function get_communications( $membership = null ) {
		$ids 	= self::get_communication_ids( $membership );
		$result = array();

		if ( null === $membership ) {
			// All comm items are requested. Index is counter.
			foreach ( $ids as $sub_list ) {
				foreach ( $sub_list as $type => $id ) {
					$result[] = MS_Factory::load( 'MS_Model_Communication', $id );
				}
			}
		} else {
			// A single membership is requested. Index is comm-type.
			foreach ( $ids as $type => $id ) {
				$result[ $type ] = MS_Factory::load( 'MS_Model_Communication', $id );
			}

			$types = self::get_communication_types();
			foreach ( $types as $type ) {
				if ( ! isset( $result[ $type ] ) ) {
					$result[ $type ] = self::get_communication( $type, $membership );
				}
			}
		}

		return apply_filters(
			'ms_model_communication_get_communications',
			$result,
			$membership
		);
	}

	/**
	 * Get communication type object.
	 *
	 * Load from DB if exists, create a new one if not.
	 *
	 * @since  1.0.0
	 *
	 * @param  string              $type The type of the communication.
	 * @param  MS_Model_Membership $membership Optional. If defined then we try
	 *         to load the overridden template for that membership with fallback
	 *         to the default template.
	 * @param  bool                $no_fallback Optional. Default value is false.
	 *         True: Always return a communication for specified membership_id
	 *         False: Fallback to default message if membership_id does not
	 *         override the requested message.
	 * @return MS_Model_Communication The communication object.
	 */
	public static function get_communication( $type, $membership = null, $no_fallback = false ) {
		$comm 		= null;
		$key 		= 'all';
		$comm_id 	= 0;

		/*
		 * If the Membership specific communication is not defined or it
		 * is configured to use the default communication then fetch the
		 * default communication object!
		 */
		$can_fallback = $membership && ! $no_fallback;

		if ( self::is_valid_communication_type( $type ) ) {
			$membership_id = 0;

			if ( $membership ) {
				if ( $membership instanceof MS_Model_Membership ) {
					$membership_id = $membership->id;
				} elseif ( is_scalar( $membership ) ) {
					$membership_id = $membership;
				}
				if ( $membership_id ) {
					$key = $membership_id;
				}
			}

			if ( empty( self::$Communication_IDs[ $key ] ) ) {
				self::$Communication_IDs[ $key ] = array();
			}

			if ( ! empty( self::$Communication_IDs[ $key ][ $type ] ) ) {
				$comm_id = self::$Communication_IDs[ $key ][ $type ];
			} else {
				$args = array(
					'post_type' 		=> self::get_post_type(),
					'post_status' 		=> 'any',
					'fields' 			=> 'ids',
					'posts_per_page' 	=> 1,
					'post_parent' 		=> $membership_id,
					'meta_query' 		=> array(
							array(
								'key' 		=> 'type',
								'value' 	=> $type,
								'compare' 	=> '=',
							),
					),
				);

				$args = apply_filters(
					'ms_model_communication_get_communications_args',
					$args
				);

				MS_Factory::select_blog();
				$query = new WP_Query( $args );
				$items = $query->posts;
				MS_Factory::revert_blog();

				if ( 1 == count( $items ) ) {
					$comm_id = $items[0];
				}
			}

			$comm_classes 	= self::get_communication_type_classes();
			$comm_class 	= $comm_classes[ $type ];
			if ( $comm_id ) {
				$comm = MS_Factory::load( $comm_class, $comm_id );
			} elseif ( ! $can_fallback ) {
				$comm = MS_Factory::create( $comm_class );
				$comm->reset_to_default();
				$comm->membership_id = $membership_id;
			}

			if ( $comm ) {
				self::$Communication_IDs[ $comm->membership_id ][ $type ] = $comm->id;
			}

			// If no template found or defined then fallback to default template.
			$should_fallback = ! $comm || ! $comm->override;
			if ( $can_fallback && $should_fallback ) {
				$comm = self::get_communication( $type, null );
			}
		}

		return apply_filters(
			'ms_model_communication_get_communication_' . $type,
			$comm,
			$membership,
			$no_fallback
		);
	}


	/*
	 *
	 *
	 * ------------------------------------------------------------- SINGLE ITEM
	 */


	/**
	 * Communication constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->comm_vars = array(
			self::COMM_VAR_MS_NAME 					=> __( 'Subscription: Membership Name', 'membership2' ),
			self::COMM_VAR_MS_DESCRIPTION 			=> __( 'Subscription: Membership Description', 'membership2' ),
			self::COMM_VAR_MS_REMAINING_DAYS 		=> __( 'Subscription: Remaining days', 'membership2' ),
			self::COMM_VAR_MS_REMAINING_TRIAL_DAYS 	=> __( 'Subscription: Remaining trial days', 'membership2' ),
			self::COMM_VAR_MS_EXPIRY_DATE 			=> __( 'Subscription: Expiration date', 'membership2' ),
			self::COMM_VAR_MS_INVOICE 				=> __( 'Subscription: Current Invoice', 'membership2' ),
			self::COMM_VAR_USER_DISPLAY_NAME 		=> __( 'User: Display name', 'membership2' ),
			self::COMM_VAR_USER_FIRST_NAME 			=> __( 'User: First name', 'membership2' ),
			self::COMM_VAR_USER_LAST_NAME 			=> __( 'User: Last name', 'membership2' ),
			self::COMM_VAR_USERNAME 				=> __( 'User: Login name', 'membership2' ),
			self::COMM_VAR_PASSWORD 				=> __( 'User: Password', 'membership2' ),
			self::COMM_VAR_RESETURL 				=> __( 'User: Reset Password URL', 'membership2' ),
			self::COMM_VAR_MS_ACCOUNT_PAGE_URL 		=> __( 'Site: User Account URL', 'membership2' ),
			self::COMM_VAR_BLOG_NAME 				=> __( 'Site: Name', 'membership2' ),
			self::COMM_VAR_BLOG_URL 				=> __( 'Site: URL', 'membership2' ),
			self::COMM_VAR_VERIFICATION_URL			=> __( 'User: Account Verification URL', 'membership2' ),
		);

		$has_membership = true;
		if ( self::COMM_TYPE_SIGNUP == $this->type ) {
			$has_membership = false;
		} else {
			// Password is only available in the Signup email.
			unset( $this->comm_vars[ self::COMM_VAR_PASSWORD ] );
		}

		if ( self::COMM_TYPE_RESETPASSWORD == $this->type ) {
			$has_membership = false;
		} else {
			// Reset-Key is only available in the Forgot Password email.
			unset( $this->comm_vars[ self::COMM_VAR_RESETURL ] );
		}

		if ( ! $has_membership ) {
			// If no membership context is available then remove those variables.
			unset( $this->comm_vars[ self::COMM_VAR_MS_NAME ] );
			unset( $this->comm_vars[ self::COMM_VAR_MS_DESCRIPTION ] );
			unset( $this->comm_vars[ self::COMM_VAR_MS_REMAINING_DAYS ] );
			unset( $this->comm_vars[ self::COMM_VAR_MS_REMAINING_TRIAL_DAYS ] );
			unset( $this->comm_vars[ self::COMM_VAR_MS_EXPIRY_DATE ] );
			unset( $this->comm_vars[ self::COMM_VAR_MS_INVOICE ] );
		}

		if ( is_multisite() ) {
			$this->comm_vars[ self::COMM_VAR_NET_NAME ] = __( 'Network: Name', 'membership2' );
			$this->comm_vars[ self::COMM_VAR_NET_URL ] 	= __( 'Network: URL', 'membership2' );
		}
	}

	/**
	 * Save the current communication item.
	 *
	 * This function allows us easier debugging of communication issues.
	 *
	 * @since  1.0.1.1
	 */
	public function save() {
		parent::save();
	}

	/**
	 * Customize the data that is written to the DB.
	 *
	 * @param  WP_Post $post The post object.
	 * @since  1.0.1.0
	 */
	public function save_post_data( $post ) {
		$post['post_content'] 	= $this->message;
		$post['post_excerpt'] 	= $this->message;
		$post['post_parent'] 	= intval( $this->membership_id );
		return $post;
	}

	/**
	 * Hook process communication actions.
	 *
	 * @param  WP_Post $post The post object.
	 * @since  1.0.1.0
	 */
	public function load_post_data( $post ) {
		$this->message 			= $post->post_content;
		$this->membership_id 	= intval( $post->post_parent );
	}

	/**
	 * Communication default communication.
	 *
	 * To be overridden by children classes creating a new object with the default subject, message, enabled, etc.
	 *
	 * @since  1.0.0
	 */
	public function reset_to_default() {
		do_action(
			'ms_model_communication_reset_to_default',
			$this->type,
			$this
		);
	}

	/**
	 * Returns the title of the communication object.
	 *
	 * @since  1.0.1.0
	 * @return string
	 */
	public function get_title() {
		$result = '';
		$titles = self::get_communication_type_titles();

		if ( isset( $titles[ $this->type ] ) ) {
			$result = $titles[ $this->type ];
		}

		return apply_filters(
			'ms_model_communication_get_title',
			$result
		);
	}

	/**
	 * Get communication description.
	 *
	 * Override it in children classes.
	 *
	 * @since  1.0.0
	 * @return string The description.
	 */
	public function get_description() {
		$description = __( 'Override this description in child class', 'membership2' );

		return apply_filters(
			'ms_model_communication_get_description',
			$description
		);
	}

	/**
	 * Populates the field title/description of the Period before/after field
	 * in the admin settings.
	 *
	 * Override this in child classes to customize the label.
	 *
	 * @since  1.0.0
	 * @param array $field A HTML definition, passed to mslib3()->html->element().
	 */
	public function set_period_name( $field ) {
		$field['title'] = __( 'Period before/after', 'membership2' );

		return $field;
	}

	/**
	 * Process communication.
	 *
	 * Send email and manage queue.
	 *
	 * @uses self::send_message() to send the message.
	 *
	 * @since  1.0.0
	 * @internal Called by MS_Controller_Communication::process_queue()
	 */
	public function process_queue() {
		do_action(
			'ms_model_communication_process_queue_before',
			$this
		);

		/**
		 * Use `define( 'MS_STOP_EMAILS', true );` in wp-config.php to prevent
		 * Membership2 from sending *any* emails to users.
		 * Also any currently enqueued message is removed from the queue
		 *
		 * @since  1.0.0
		 */
		if ( MS_Plugin::get_modifier( 'MS_STOP_EMAILS' ) ) {
			$this->queue = array();
		}

		if ( $this->enabled && ! $this->check_object_lock() && count( $this->queue ) ) {
			$this->set_object_lock();

			// Max emails that are sent in one process call.
			$max_emails_qty = apply_filters(
				'ms_model_communication_process_queue_max_email_qty',
				50
			);
			$count = 0;

			// Email-processing timeout, in seconds.
			$time_limit = apply_filters(
				'ms_model_communication_process_queue_time_limit',
				10
			);
			$start_time = time();

			foreach ( $this->queue as $subscription_id => $timestamp ) {
				// Remove invalid subscription items from queue.
				if ( ! $subscription_id || ! is_numeric( $subscription_id ) ) {
					unset( $this->queue[ $subscription_id ] );
					continue;
				}

				if ( time() > $start_time + $time_limit
					|| ++$count > $max_emails_qty
				) {
					break;
				}

				$subscription = MS_Factory::load( 'MS_Model_Relationship', $subscription_id );
				if ( !$subscription ) {
					//Could also just be a member
					$subscription = MS_Factory::load( 'MS_Model_Member', $subscription_id );
				}

				$this->remove_from_queue( $subscription_id );
				$was_sent = $this->send_message( $subscription );

				if ( ! $was_sent ) {
					if ( $subscription instanceof MS_Model_Relationship ) {
						$msg = sprintf(
							'[error: Communication email failed] comm_type=%s, subscription_id=%s, user_id=%s',
							$this->type,
							$subscription->id,
							$subscription->user_id
						);
					} elseif ( $subscription instanceof MS_Model_Member ) {
						$msg = sprintf(
							'[error: Communication email failed] comm_type=%s, user_id=%s',
							$this->type,
							$subscription->id
						);
					}
					$this->log( $msg );
				}
			}

			$this->save();
			$this->delete_object_lock();
		}

		do_action( 'ms_model_communication_process_queue_after', $this );
	}

	/**
	 * Process Message directly without sending to queue
	 *
	 * @api
	 * @param  MS_Model_Relationship | MS_Model_Member - $subscription The subscription or member to send message to.
	 */
	public function process_message_direct( $subscription ){
		if ( $this->enabled ) {
			if ( $subscription && is_object( $subscription ) ) {

				$was_sent = $this->send_message( $subscription );

				if ( ! $was_sent ) {
					if ( $subscription instanceof MS_Model_Relationship ) {
						$msg = sprintf(
							'[error: Communication email failed] comm_type=%s, subscription_id=%s, user_id=%s',
							$this->type,
							$subscription->id,
							$subscription->user_id
						);
					} elseif ( $subscription instanceof MS_Model_Member ) {
						$msg = sprintf(
							'[error: Communication email failed] comm_type=%s, user_id=%s',
							$this->type,
							$subscription->id
						);
					}
					$this->log( $msg );
				}
			}
		}
	}

	/**
	 * Enqueue a message in the "send queue".
	 *
	 * Action handler hooked up in child classes.
	 *
	 * @since  1.0.0
	 * @api
	 * @param  MS_Model_Event        $event The event object.
	 * @param  MS_Model_Relationship $subscription The subscription to send message to.
	 */
	public function enqueue_messages( $event, $subscription ) {
		do_action( 'ms_model_communication_enqueue_messages_before', $this );

		if ( $this->enabled ) {
			$this->add_to_queue( $subscription->id );
			$this->save();
		}

		do_action( 'ms_model_communication_enqueue_messages_after', $this );
	}

	/**
	 * Process a communication event.
	 *
	 * This is used to execute custom code before or instead of simply enqueuing
	 * the communication.
	 *
	 * Common usage:
	 * - Instantly send the message via $this->send_message()
	 * - Only enqueue message for specific $subscriptions (e.g. free ones)
	 *
	 * @since  1.0.1.0
	 * @param  MS_Model_Event        $event The event object.
	 * @param  MS_Model_Relationship $subscription The subscription object.
	 */
	public function process_communication( $event, $subscription ) {
		// Can be overwritten in the child class for custom actions.
	}

	/**
	 * Add a message in the "send queue".
	 *
	 * @since  1.0.0
	 * @api
	 * @param  int $subscription_id The membership relationship ID to add to queue.
	 */
	public function add_to_queue( $subscription_id ) {
		do_action( 'ms_model_communication_add_to_queue_before', $this );
		/**
		 * Check if cron is enabled
		 *
		 * @since 1.0.4
		 */
		$settings = MS_Factory::load( 'MS_Model_settings' );
		if ( !$settings->enable_cron_use ) {
			$subscription = MS_Factory::load( 'MS_Model_Relationship', $subscription_id );
			if ( $subscription ) {
				$this->process_message_direct( $subscription );
			} else {
				$member = MS_Factory::load( 'MS_Model_Member', $subscription_id );
				if ( $member ) {
					$this->process_message_direct( $member );
				}
			}
		} else {
			/**
			* Documented in process_queue()
			*
			* @since  1.0.0
			*/
			if ( MS_Plugin::get_modifier( 'MS_STOP_EMAILS' ) ) {
				$subscription = MS_Factory::load( 'MS_Model_Relationship', $subscription_id );
				if ( $subscription ) {
					$msg = sprintf(
						'Following Email was not sent: "%s" to user "%s".',
						$this->type,
						$subscription->user_id
					);
				} else {
					$member = MS_Factory::load( 'MS_Model_Member', $subscription_id );
					if ( $member ) {
						$msg = sprintf(
							'Following Email was not sent: "%s" to user "%s".',
							$this->type,
							$member->id
						);
					}
				}
				$this->log( $msg );

				return false;
			}

			$is_enqueued = array_key_exists( $subscription_id, $this->queue );

			if ( $this->enabled && ! $is_enqueued ) {
				$can_add = true;

				/**
				* Check if email enqueuing is limited to prevent duplicate emails.
				*
				* Use setting `define( 'MS_DUPLICATE_EMAIL_HOURS', 24 )` to prevent
				* duplicate emails from being sent for 24 hours.
				*
				* @var int Number of hours
				*/
				$pause_hours = 0;
				if ( defined( 'MS_DUPLICATE_EMAIL_HOURS' ) && is_numeric( MS_DUPLICATE_EMAIL_HOURS ) ) {
					$pause_hours = MS_DUPLICATE_EMAIL_HOURS;
				}
				if ( $pause_hours > 0 ) {
					if ( array_key_exists( $subscription_id, $this->sent_queue ) ) {
						$pause_hours = apply_filters(
							'ms_model_communication_hours_before_resend',
							$pause_hours
						);

						/*
						* The sent_queue is saved in DB and only contains messages
						* from the current Communications object. So
						* $subscription_id defines the email contents and receiver.
						*/
						$sent_date 		= $this->sent_queue[ $subscription_id ];
						$now 			= MS_Helper_Period::current_time();

						$current_delay 	= MS_Helper_Period::subtract_dates(
							$now,
							$sent_date,
							HOUR_IN_SECONDS
						);

						$can_add = $current_delay >= $pause_hours;
					}
				}

				if ( $can_add ) {
					$this->queue[ $subscription_id ] = MS_Helper_Period::current_time();
				}
			}
		}


		do_action( 'ms_model_communication_add_to_queue_after', $this );
	}

	/**
	 * Remove from queue.
	 *
	 * Delete history of sent messages after max is reached.
	 *
	 * @since  1.0.0
	 *
	 * @param int $subscription_id The membership relationship ID to remove from queue.
	 */
	public function remove_from_queue( $subscription_id ) {
		do_action( 'ms_model_communication_remove_from_queue_before', $this );

		$max_history = apply_filters(
			'ms_model_communication_sent_queue_max_history',
			200
		);

		// Delete history.
		if ( count( $this->sent_queue ) > $max_history ) {
			$this->sent_queue = array_slice(
				$this->sent_queue,
				-100,
				$max_history,
				true
			);
		}

		$this->sent_queue[ $subscription_id ] = MS_Helper_Period::current_time();
		unset( $this->queue[ $subscription_id ] );

		do_action( 'ms_model_communication_remove_from_queue_after', $this );
	}

	/**
	 * Send email message.
	 *
	 * THIS IS THE ONLY PLACE IN THE M2 PLUGIN THAT WILL ACTUALLY SEND OUT AN
	 * EMAIL! One exception: If the custom "Reset Password" template is
	 * disabled then `class-ms-controller-dialog.php` will send a default reset
	 * email using a different wp_mail() call.
	 *
	 * Delete history of sent messages after max is reached.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed $reference A reference to identify the member/subscription.
	 * @return bool True if successfully sent email.
	 */
	public function send_message( $reference ) {
		$user_id 		= 0;
		$subscription 	= null;
		$member 		= null;
		$sent 			= false;

		if ( $reference instanceof MS_Model_Relationship ) {
			$user_id 		= $reference->user_id;
			$subscription 	= $reference;
			$member 		= $subscription->get_member();
		} elseif ( $reference instanceof MS_Model_Member ) {
			$user_id 		= $reference->id;
			$member			= $reference;
			$subscription 	= null;
		}

		/**
		 * Documented in process_queue()
		 *
		 * @since  1.0.1.0
		 */
		if ( MS_Plugin::get_modifier( 'MS_STOP_EMAILS' ) ) {
			$msg = sprintf(
				'Following Email was not sent: "%s" to user "%s".',
				$this->type,
				$user_id
			);
			$this->log( $msg );

			return false;
		}

		// Prevent duplicate messages.
		if ( $subscription ) {
			$delay = $subscription->seconds_since_last_email( $this->type );

			/**
			 * Allow other plugins to customize the duration for ignoring
			 * duplicate emails. This is supposed to be a value in seconds,
			 * so 60 will prevent duplicate emails for 1 minute.
			 *
			 * @since  1.0.3.0
			 * @param  int                   $duration     Duration to skip duplicate emails. Default: 1 day.
			 * @param  string                $type         Type of the email that is about to be sent.
			 * @param  MS_Model_Relationship $subscription The subscription.
			 */
			$ignore_duration = (int) apply_filters(
				'ms_communication_send_email_ignore_duration',
				DAY_IN_SECONDS,
				$this->type,
				$subscription
			);

			if ( is_numeric( $delay ) && $delay < $ignore_duration ) {
				$msg = sprintf(
					'Intentionally skip duplicate email: "%s" to user "%s".',
					$this->type,
					$user_id
				);
				$this->log( $msg );
				return false;
			}
		}

		do_action(
			'ms_model_communication_send_message_before',
			$member,
			$subscription,
			$this
		);

		if ( $this->enabled && is_object( $member ) ) {
			if ( ! is_email( $member->email ) ) {
				$msg = sprintf(
					'Invalid user email. User_id: %1$s, email: %2$s',
					$user_id,
					$member->email
				);
				$this->log( $msg );

				return false;
			}

			$comm_vars 		= $this->get_comm_vars( $subscription, $member );
			$headers 		= array();

			// Prepare the message: Replace variables.
			$message 		= str_replace(
				array_keys( $comm_vars ),
				array_values( $comm_vars ),
				stripslashes( $this->message )
			);

			// Prepare the subject: Replace variables and remove HTML tags.
			$subject 		= str_replace(
				array_keys( $comm_vars ),
				array_values( $comm_vars ),
				stripslashes( $this->subject )
			);
			$subject 		= strip_tags( $subject );

			// Set the content-type of the email without using WP hooks.
			$content_type 	= $this->get_mail_content_type();
			$headers[] 		= sprintf(
				'Content-Type: %s; charset="UTF-8"',
				$content_type
			);

			// Pre-process the message according to content-type.
			if ( 'text/html' == $content_type ) {
				$message = wpautop( $message );
				$message = make_clickable( $message );
			} else {
				$message = preg_replace(
					'/\<a .*?href=["\'](.*?)["\'].*?\>(.*?)\<\/a\>/is',
					'$2 [$1]',
					$message
				);
				$message = strip_tags( $message );
			}

			// Prepare the FROM sender details.
			$admin_emails = MS_Model_Member::get_admin_user_emails();
			if ( ! empty( $admin_emails[0] ) ) {
				$headers[] = sprintf(
					'From: %s <%s> ',
					get_option( 'blogname' ),
					$admin_emails[0]
				);
			}

			// Prepare list of recipients.
			$recipients 	= array( $member->email );
			if ( $this->show_admin_cc ) {
				$cc_recipients 	= $this->cc_email;
				if ( $this->cc_enabled && ! empty( $cc_recipients ) ) {
					$recipients[] = $cc_recipients;
				}
			}

			// Final step: Allow customization of all email parts.
			$recipients 	= apply_filters(
				'ms_model_communication_send_message_recipients',
				$recipients,
				$this,
				$subscription
			);
			$message 		= apply_filters(
				'ms_model_communication_send_message_contents',
				$message,
				$this,
				$subscription,
				$content_type
			);
			$subject 		= apply_filters(
				'ms_model_communication_send_message_subject',
				$subject,
				$this,
				$subscription
			);
			$headers 		= apply_filters(
				'ms_model_communication_send_message_headers',
				$headers,
				$this,
				$subscription
			);

			/*
			 * Send the mail!
			 * wp_mail will not throw an error, so no error-suppression/handling
			 * is required here. On error the function response is FALSE.
			 */
			$sent 	= wp_mail( $recipients, $subject, $message, $headers );

			/**
			 * Broadcast that we just send an email to a member.
			 *
			 * @since  1.0.2.7
			 */
			do_action(
				'ms_model_communication_after_send_message',
				$sent,
				$recipients,
				$subject,
				$message,
				$headers,
				$this,
				$subscription
			);

			// Log the outgoing email.
			$msg = sprintf(
				'Sent email [%s] to <%s>: %s',
				$this->type,
				implode( '>, <', $recipients ),
				$sent ? 'OK' : 'ERR'
			);
			$this->log( $msg );
		}

		do_action(
			'ms_model_communication_send_message',
			$member,
			$subscription,
			$this
		);

		return $sent;
	}

	/**
	 * Replace comm_vars with corresponding values.
	 *
	 * @since  1.0.0
	 *
	 * @param MS_Model_Relationship $subscription The membership relationship to send message to.
	 * @param MS_Model_Member       $member The member object to get info from.
	 * @return array {
	 *     Returns array of ( $var_name => $var_replace ).
	 *
	 *     @type string $var_name The variable name to replace.
	 *     @type string $var_replace The variable corresponding replace string.
	 * }
	 */
	public function get_comm_vars( $subscription, $member ) {
		$currency 	= MS_Plugin::instance()->settings->currency . ' ';
		$invoice 	= null;
		$membership = null;

		if ( $subscription && $subscription instanceof MS_Model_Relationship ) {
			// First try to fetch the current invoice.
			$invoice 		= $subscription->get_current_invoice( false );
			$prev_invoice 	= $subscription->get_previous_invoice();

			// If no current invoice exists then fetch the previous invoice.
			if ( empty( $invoice ) ) {
				$invoice = $prev_invoice;
			}

			$membership = $subscription->get_membership();
		}

		$comm_vars = apply_filters(
			'ms_model_communication_comm_vars',
			$this->comm_vars,
			$this->type,
			$member,
			$subscription
		);

		foreach ( $comm_vars as $key => $description ) {
			$var_value = '';

			switch ( $key ) {
				case self::COMM_VAR_BLOG_NAME:
					$var_value = get_option( 'blogname' );
					break;

				case self::COMM_VAR_BLOG_URL:
					$var_value = get_option( 'home' );
					break;

				case self::COMM_VAR_USERNAME:
					$var_value = $member->username;
					break;

				case self::COMM_VAR_PASSWORD:
					/**
					 * $member->password is ONLY available in the same request
					 * when the new user account was created! After this we only
					 * have the encrypted password in the DB, and the plain-text
					 * version will never be available again in code...
					 *
					 * @since 1.0.1.1
					 */
					if ( self::COMM_TYPE_SIGNUP == $this->type ) {
						$var_value = ( !empty( $member->password ) ? $member->password : ( isset( $_POST['password'] ) && !empty( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : ( isset( $_POST['pass1'] ) && !empty( $_POST['pass1']) ? sanitize_text_field( $_POST['pass1'] ) : '' ) ) );
					}
					break;

				case self::COMM_VAR_RESETURL:
					/**
					 * The reset-URL is only available in the password reset
					 * email template. Reason is, that only ONE valid URL can
					 * exist at a time, so if every email would contain a
					 * reset URL it would invalidate all previous reset URLs.
					 *
					 * @since 1.0.2.3
					 */
					if ( self::COMM_TYPE_RESETPASSWORD == $this->type ) {
						$reset 		= $member->new_password_reset_key();
						$var_value 	= $reset->url;
					}
					break;

				case self::COMM_VAR_VERIFICATION_URL:

					/**
					 * The verification-URL is only available in the verification email
					 *
					 * @since 1.1.3
					 */
					if ( self::COMM_TYPE_REGISTRATION_VERIFY == $this->type ) {
						$verify 	= $member->account_verification_key();
						$var_value 	= $verify->url;
					}
					break;

				case self::COMM_VAR_USER_DISPLAY_NAME:
					$var_value = $member->display_name;
					break;

				case self::COMM_VAR_USER_FIRST_NAME:
					$var_value = $member->first_name;
					break;

				case self::COMM_VAR_USER_LAST_NAME:
					$var_value = $member->last_name;
					break;

				case self::COMM_VAR_NET_NAME:
					$var_value = get_site_option( 'site_name' );
					break;

				case self::COMM_VAR_NET_URL:
					$var_value = get_site_option( 'siteurl' );
					break;

				case self::COMM_VAR_MS_ACCOUNT_PAGE_URL:
					$var_value = sprintf(
						'<a href="%s">%s</a>',
						MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_ACCOUNT ),
						__( 'account page', 'membership2' )
					);
					break;

				// Needs: $membership.
				case self::COMM_VAR_MS_NAME:
					if ( $membership && $membership->name ) {
						$var_value = $membership->name;
					}
					break;

				// Needs: $membership.
				case self::COMM_VAR_MS_DESCRIPTION:
					if ( $membership && $membership->description ) {
						$var_value = $membership->get_description();
					}
					break;

				// Needs: $invoice.
				case self::COMM_VAR_MS_INVOICE:
					if ( $invoice ) {
						if ( $invoice->total > 0 || $invoice->uses_trial ) {
							$attr 		= array(
								'post_id' 		=> $invoice->id,
								'pay_button' 	=> 0,
							);
							$scode 		= MS_Factory::load( 'MS_Controller_Shortcode' );
							$var_value 	= $scode->membership_invoice( $attr );
						}
					}
					break;

				// Needs: $subscription.
				case self::COMM_VAR_MS_REMAINING_DAYS:
					if ( $subscription ) {
						$days 		= $subscription->get_remaining_period( 0 );
						$var_value 	= sprintf(
							__( '%s day%s', 'membership2' ),
							$days,
							abs( $days ) > 1 ? 's': ''
						);
					}
					break;

				// Needs: $subscription.
				case self::COMM_VAR_MS_REMAINING_TRIAL_DAYS:
					if ( $subscription ) {
						$days 		= $subscription->get_remaining_trial_period();
						$var_value 	= sprintf(
							__( '%s day%s', 'membership2' ),
							$days,
							abs( $days ) > 1 ? 's': ''
						);
					}
					break;

				// Needs: $subscription.
				case self::COMM_VAR_MS_EXPIRY_DATE:
					if ( $subscription ) {
						$var_value = $subscription->expire_date;
					}
					break;
			}

			$comm_vars[ $key ] = apply_filters(
				'ms_model_communication_send_message_comm_var-' . $key,
				$var_value,
				$this->type,
				$member,
				$subscription,
				$invoice
			);
		}

		return apply_filters(
			'ms_model_communication_get_comm_vars',
			$comm_vars,
			$member
		);
	}

	/**
	 * Get Email content type.
	 *
	 * Eg. text/html, text.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_mail_content_type() {
		$this->content_type = apply_filters(
			'ms_model_communication_set_html_content_type',
			'text/html'
		);

		return $this->content_type;
	}

	/**
	 * Validate specific property before set.
	 *
	 * @since 1.0.0
	 * @param string $property The name of a property to associate.
	 * @param mixed  $value The value of a property.
	 */
	public function __set( $property, $value ) {
		switch ( $property ) {
			case 'type':
				if ( $this->is_valid_communication_type( $value ) ) {
					$this->$property = $value;
				}
				break;

			case 'subject':
				$this->$property = sanitize_text_field( $value );
				break;

			case 'cc_email':
				if ( is_email( $value ) ) {
					$this->$property = $value;
				}
				break;

			case 'enabled':
			case 'cc_enabled':
			case 'override':
				$this->$property = mslib3()->is_true( $value );
				break;

			case 'period':
				$this->$property = $this->validate_period( $value );
				break;

			case 'period_unit':
				$this->period['period_unit'] = $this->validate_period_unit( $value );
				break;

			case 'period_type':
				$this->period['period_type'] = $this->validate_period_type( $value );
				break;

			default:
				if ( property_exists( $this, $property ) ) {
					$this->$property = $value;
				}
				break;
		}

		do_action(
			'ms_model_communication__set_after',
			$property,
			$value,
			$this
		);
	}
};